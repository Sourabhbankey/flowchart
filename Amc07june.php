<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Amc (DespatchController)
 * Amc Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 08 Jun 2023
 */
class Amc extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Amc_model', 'ay');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->load->library("pagination");
        $this->module = 'Amc';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('amc/amcListing');
    }
    
    /**
     * This function is used to load the task list
     */
    public function amcListing() {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        $franchiseFilter = $this->input->get('franchiseNumber');
        $statusAmc = $this->input->get('statusAmc');
        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
            $statusAmc = '';
        }

        $config = array();
        $config['base_url'] = base_url('amc/amcListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        if (in_array($userRole, ['14', '1', '24','28'])) {
            if ($franchiseFilter) {
                $config['total_rows'] = $this->ay->getTotalTrainingRecordsCountByFranchise($franchiseFilter, $statusAmc);
                $data['records'] = $this->ay->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page, $statusAmc);
            } else {
                $config['total_rows'] = $this->ay->getTotalTrainingRecordsCount($statusAmc);
                $data['records'] = $this->ay->getAllTrainingRecords($config['per_page'], $page, $statusAmc);
            }
        } else if ($userRole == '15' || $userRole == '13') {
            $franchiseNumber = $this->ay->getFranchiseNumberByUserId($userId);

            if ($franchiseNumber) {
                if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                    $config['total_rows'] = $this->ay->getTotalTrainingRecordsCountByFranchise($franchiseFilter, $statusAmc);
                    $data['records'] = $this->ay->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page, $statusAmc);
                } else {
                    $config['total_rows'] = $this->ay->getTotalTrainingRecordsCountByFranchise($franchiseNumber, $statusAmc);
                    $data['records'] = $this->ay->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page, $statusAmc);
                }
            } else {
                $data['records'] = [];
                $config['total_rows'] = 0;
            }
        } else {
            $franchiseNumber = $this->ay->getFranchiseNumberByUserId($userId);

            if ($franchiseNumber) {
                $config['total_rows'] = $this->ay->getTotalTrainingRecordsCountByFranchise($franchiseNumber, $statusAmc);
                $data['records'] = $this->ay->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page, $statusAmc);
            } else {
                $data['records'] = [];
                $config['total_rows'] = 0;
            }
        }

        // Pagination and other values
        $data["serial_no"] = $page + 1;
        $this->pagination->initialize($config);
        $data["links"] = $this->pagination->create_links();
        $data["start"] = $page + 1;
        $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];
        $data['pagination'] = $this->pagination->create_links();
        $data["franchiseFilter"] = $franchiseFilter;
        $data["statusAmc"] = $statusAmc;
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

        $this->loadViews("amc/list", $this->global, $data, NULL);
    }

  
    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $data['users'] = $this->bm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Amc';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();            
            $this->loadViews("amc/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewAmc()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('franchiseName','Branch Name','trim|required|max_length[256]');
            $this->form_validation->set_rules('descAmc','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseName = $this->security->xss_clean($this->input->post('franchiseName'));
                $branchLocation = $this->security->xss_clean($this->input->post('branchLocation'));
                $branchState = $this->security->xss_clean($this->input->post('branchState'));
                $oldAMCdue = $this->security->xss_clean($this->input->post('oldAMCdue'));
                $curAmc = $this->security->xss_clean($this->input->post('curAmc'));
                $totalAmc = $this->security->xss_clean($this->input->post('totalAmc'));
                $statusAmc = $this->security->xss_clean($this->input->post('statusAmc'));
                $branchFranchiseAssigned = $this->security->xss_clean($this->input->post('branchFranchiseAssigned'));
                $brInstallationStatusAMC = $this->security->xss_clean($this->input->post('brInstallationStatusAMC'));
                $dueDateAmc = $this->security->xss_clean($this->input->post('dueDateAmc'));
                /*--New-Added---*/
                $amcYear1 = $this->security->xss_clean($this->input->post('amcYear1'));
                $amcYear1dueAmount = $this->security->xss_clean($this->input->post('amcYear1dueAmount'));
                $amcYear1date = $this->security->xss_clean($this->input->post('amcYear1date'));
                $statusYear1Amc = $this->security->xss_clean($this->input->post('statusYear1Amc'));
                $amcYear2 = $this->security->xss_clean($this->input->post('amcYear2'));
                $amcYear2dueAmount = $this->security->xss_clean($this->input->post('amcYear2dueAmount'));
                $amcYear2date = $this->security->xss_clean($this->input->post('amcYear2date'));
                $statusYear2Amc = $this->security->xss_clean($this->input->post('statusYear2Amc'));
                $amcYear3 = $this->security->xss_clean($this->input->post('amcYear3'));
                $amcYear3dueAmount = $this->security->xss_clean($this->input->post('amcYear3dueAmount'));
                $amcYear3date = $this->security->xss_clean($this->input->post('amcYear3date'));
                $statusYear3Amc = $this->security->xss_clean($this->input->post('statusYear3Amc'));
                $amcYear4 = $this->security->xss_clean($this->input->post('amcYear4'));
                $amcYear4dueAmount = $this->security->xss_clean($this->input->post('amcYear4dueAmount'));
                $amcYear4date = $this->security->xss_clean($this->input->post('amcYear4date'));
                $statusYear4Amc = $this->security->xss_clean($this->input->post('statusYear4Amc'));
                $amcYear5 = $this->security->xss_clean($this->input->post('amcYear5'));
                $amcYear5dueAmount = $this->security->xss_clean($this->input->post('amcYear5dueAmount'));
                $franchiseNumber = implode(',', $franchiseNumberArray);
                $amcYear5date = $this->security->xss_clean($this->input->post('amcYear5date'));
                $statusYear5Amc = $this->security->xss_clean($this->input->post('statusYear5Amc'));
                /*--New Fields for Penalty and Other Charges---*/
                $penaltyCharges = $this->security->xss_clean($this->input->post('penaltyCharges'));
                $penaltyAmount = $this->security->xss_clean($this->input->post('penaltyAmount'));
                $otherChargesTitle = $this->security->xss_clean($this->input->post('otherChargesTitle'));
                $otherChargesAmount = $this->security->xss_clean($this->input->post('otherChargesAmount'));
                /*--End-New-Added---*/
                $descAmc = $this->security->xss_clean($this->input->post('descAmc'));

                // Amc Attachment File 
                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time().'-'.$_FILES["file"]["name"];
                rename($_FILES["file"]["tmp_name"], $destination);
                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();
                $s3_file_link = !empty($result_arr['ObjectURL']) ? [$result_arr['ObjectURL']] : [''];
                $s3files = implode(',', $s3_file_link);

                /*amc 2*/ 
                $dir = dirname($_FILES["file2"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time().'-'.$_FILES["file2"]["name"];
                rename($_FILES["file2"]["tmp_name"], $destination);
                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();
                $s3_file_link2 = !empty($result_arr['ObjectURL']) ? [$result_arr['ObjectURL']] : [''];
                $s3files2 = implode(',', $s3_file_link2);

                /*amc 3*/ 
                $dir = dirname($_FILES["file3"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time().'-'.$_FILES["file3"]["name"];
                rename($_FILES["file3"]["tmp_name"], $destination);
                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();
                $s3_file_link3 = !empty($result_arr['ObjectURL']) ? [$result_arr['ObjectURL']] : [''];
                $s3files3 = implode(',', $s3_file_link3);

                /*amc 4*/ 
                $dir = dirname($_FILES["file4"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time().'-'.$_FILES["file4"]["name"];
                rename($_FILES["file4"]["tmp_name"], $destination);
                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();
                $s3_file_link4 = !empty($result_arr['ObjectURL']) ? [$result_arr['ObjectURL']] : [''];
                $s3files4 = implode(',', $s3_file_link4);

                /*amc 5*/ 
                $dir = dirname($_FILES["file5"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time().'-'.$_FILES["file5"]["name"];
                rename($_FILES["file5"]["tmp_name"], $destination);
                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();
                $s3_file_link5 = !empty($result_arr['ObjectURL']) ? [$result_arr['ObjectURL']] : [''];
                $s3files5 = implode(',', $s3_file_link5);

                $amcInfo = array(
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'franchiseName' => $franchiseName,
                    'franchiseNumber' => $franchiseNumber,
                    'branchLocation' => $branchLocation,
                    'branchState' => $branchState,
                    'oldAMCdue' => $oldAMCdue,
                    'curAmc' => $curAmc,
                    'totalAmc' => $totalAmc,
                    'statusAmc' => $statusAmc,
                    'branchFranchiseAssigned' => $branchFranchiseAssigned,
                    'brInstallationStatusAMC' => $brInstallationStatusAMC,
                    'amcYear1' => $amcYear1,
                    'amcYear1dueAmount' => $amcYear1dueAmount,
                    'amcYear1date' => $amcYear1date,
                    'statusYear1Amc' => $statusYear1Amc,
                    'amcYear2' => $amcYear2,
                    'amcYear2dueAmount' => $amcYear2dueAmount,
                    'amcYear2date' => $amcYear2date,
                    'statusYear2Amc' => $statusYear2Amc,
                    'amcYear3' => $amcYear3,
                    'amcYear3dueAmount' => $amcYear3dueAmount,
                    'amcYear3date' => $amcYear3date,
                    'statusYear3Amc' => $statusYear3Amc,
                    'amcYear4' => $amcYear4,
                    'amcYear4dueAmount' => $amcYear4dueAmount,
                    'amcYear4date' => $amcYear4date,
                    'statusYear4Amc' => $statusYear4Amc,
                    'amcYear5' => $amcYear5,
                    'amcYear5dueAmount' => $amcYear5dueAmount,
                    'amcYear5date' => $amcYear5date,
                    'statusYear5Amc' => $statusYear5Amc,
                    'dueDateAmc' => $dueDateAmc,
                    'descAmc' => $descAmc,
                    'penaltyCharges' => $penaltyCharges,
                    'penaltyAmount' => $penaltyAmount,
                    'otherChargesTitle' => $otherChargesTitle,
                    'otherChargesAmount' => $otherChargesAmount,
                    'amcYear1S3File' => $s3files,
                    'amcYear2S3File' => $s3files2,
                    'amcYear3S3File' => $s3files3,
                    'amcYear4S3File' => $s3files4,
                    'amcYear5S3File' => $s3files5,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );
                
                $result = $this->ay->addNewAmc($amcInfo);
                $this->load->model('Notification_model');

                // ✅ Send Notification to Assigned Franchise User
                if (!empty($brspFranchiseAssigned)) {
                    $notificationMessage = "<strong>AMC:</strong>A new amc has been assigned to you.";
                    $this->Notification_model->add_amc_notification($brspFranchiseAssigned, $notificationMessage, $result);
                }

                // ✅ Get User ID mapped with this Franchise Number
                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                if (!empty($franchiseUser)) {
                    $notificationMessage = "<strong>AMC:</strong>A new amc has been assigned to you.";
                    $this->Notification_model->add_amc_notification($franchiseUser->userId, $notificationMessage, $result);
                }
                // ✅ Notify Admins (roleId = 1, 14)
                $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $this->Notification_model->add_amc_notification($adminUser->userId, "<strong>AMC:</strong>A new amc has been assigned to you.", $result);
                    }
                }

                if($result > 0) {
                    $this->session->set_flashdata('success', 'New AMC created successfully');
                } else {
                    $this->session->set_flashdata('error', 'AMC creation failed');
                }
                
                redirect('amc/amcListing');
            }
        }
    }

    /**
     * This function is used load task edit information
     */
    function edit($amcId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($amcId == null)
            {
                redirect('amc/amcListing');
            }
            $data['amcInfo'] = $this->ay->getAmcInfo($amcId);
            $data['users'] = $this->ay->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Amc'; 
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();    
            $this->loadViews("amc/edit", $this->global, $data, NULL);
        }
    }
    
    function view($amcId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($amcId == null)
            {
                redirect('amc/amcListing');
            }
            $data['amcInfo'] = $this->ay->getAmcInfo($amcId);
            $data['users'] = $this->ay->getUser();
            $this->global['pageTitle'] = 'CodeInsect : View Amc'; 
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();            
            $this->loadViews("amc/view", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to edit the user information
     */
    function editAmc()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $amcId = $this->input->post('amcId');
            
            $this->form_validation->set_rules('descAmc','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($amcId);
            }
            else
            {
                $franchiseName = $this->security->xss_clean($this->input->post('franchiseName'));
                $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $branchLocation = $this->security->xss_clean($this->input->post('branchcityName')); // Updated to match form
                $branchState = $this->security->xss_clean($this->input->post('branchState'));
                $oldAMCdue = $this->security->xss_clean($this->input->post('oldAMCdue'));
                $curAmc = $this->security->xss_clean($this->input->post('amcAmount')); // Updated to match form
                $totalAmc = $this->security->xss_clean($this->input->post('totalAmc'));
                $statusAmc = $this->security->xss_clean($this->input->post('statusAmc'));
                $branchFranchiseAssigned = $this->security->xss_clean($this->input->post('branchFranchiseAssigned'));
                $brInstallationStatusAMC = $this->security->xss_clean($this->input->post('brInstallationStatusAMC'));
                $dueDateAmc = $this->security->xss_clean($this->input->post('dueDateAmc'));
                /*--New-Added---*/
                $amcYear1 = $this->security->xss_clean($this->input->post('amcYear1'));
                $amcYear1dueAmount = $this->security->xss_clean($this->input->post('amcYear1dueAmount'));
                $amcYear1date = $this->security->xss_clean($this->input->post('amcYear1date'));
                $statusYear1Amc = $this->security->xss_clean($this->input->post('statusYear1Amc'));
                $amcYear2 = $this->security->xss_clean($this->input->post('amcYear2'));
                $amcYear2dueAmount = $this->security->xss_clean($this->input->post('amcYear2dueAmount'));
                $amcYear2date = $this->security->xss_clean($this->input->post('amcYear2date'));
                $statusYear2Amc = $this->security->xss_clean($this->input->post('statusYear2Amc'));
                $amcYear3 = $this->security->xss_clean($this->input->post('amcYear3'));
                $amcYear3dueAmount = $this->security->xss_clean($this->input->post('amcYear3dueAmount'));
                $amcYear3date = $this->security->xss_clean($this->input->post('amcYear3date'));
                $statusYear3Amc = $this->security->xss_clean($this->input->post('statusYear3Amc'));
                $amcYear4 = $this->security->xss_clean($this->input->post('amcYear4'));
                $amcYear4dueAmount = $this->security->xss_clean($this->input->post('amcYear4dueAmount'));
                $amcYear4date = $this->security->xss_clean($this->input->post('amcYear4date'));
                $statusYear4Amc = $this->security->xss_clean($this->input->post('statusYear4Amc'));
                $amcYear5 = $this->security->xss_clean($this->input->post('amcYear5'));
                $amcYear5dueAmount = $this->security->xss_clean($this->input->post('amcYear5dueAmount'));
                $amcYear5date = $this->security->xss_clean($this->input->post('amcYear5date'));
                $statusYear5Amc = $this->security->xss_clean($this->input->post('statusYear5Amc'));
                /*--New Fields for Penalty and Other Charges---*/
                $penaltyCharges = $this->security->xss_clean($this->input->post('penaltyCharges'));
                $penaltyAmount = $this->security->xss_clean($this->input->post('penaltyAmount'));
                $otherChargesTitle = $this->security->xss_clean($this->input->post('otherChargesTitle'));
                $otherChargesAmount = $this->security->xss_clean($this->input->post('otherChargesAmount'));
                /*--End-New-Added---*/
                $descAmc = $this->security->xss_clean($this->input->post('descAmc'));

                // Amc Attachment File 
                // File 1
                if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                    $dir = dirname($_FILES["file"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                    rename($_FILES["file"]["tmp_name"], $destination);
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3files = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
                } else {
                    $s3files = '';
                }

                // File 2
                if (isset($_FILES['file2']['tmp_name']) && is_uploaded_file($_FILES['file2']['tmp_name'])) {
                    $dir = dirname($_FILES["file2"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file2"]["name"];
                    rename($_FILES["file2"]["tmp_name"], $destination);
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3files2 = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
                } else {
                    $s3files2 = '';
                }

                // File 3
                if (isset($_FILES['file3']['tmp_name']) && is_uploaded_file($_FILES['file3']['tmp_name'])) {
                    $dir = dirname($_FILES["file3"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file3"]["name"];
                    rename($_FILES["file3"]["tmp_name"], $destination);
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3files3 = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
                } else {
                    $s3files3 = '';
                }

                // File 4
                if (isset($_FILES['file4']['tmp_name']) && is_uploaded_file($_FILES['file4']['tmp_name'])) {
                    $dir = dirname($_FILES["file4"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file4"]["name"];
                    rename($_FILES["file4"]["tmp_name"], $destination);
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3files4 = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
                } else {
                    $s3files4 = '';
                }

                // File 5
                if (isset($_FILES['file5']['tmp_name']) && is_uploaded_file($_FILES['file5']['tmp_name'])) {
                    $dir = dirname($_FILES["file5"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file5"]["name"];
                    rename($_FILES["file5"]["tmp_name"], $destination);
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3files5 = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
                } else {
                    $s3files5 = '';
                }

                $amcInfo = array(
                    'franchiseName' => $franchiseName,
                    'franchiseNumber' => $franchiseNumber,
                    'branchLocation' => $branchLocation,
                    'branchState' => $branchState,
                    'oldAMCdue' => $oldAMCdue,
                    'curAmc' => $curAmc,
                    'totalAmc' => $totalAmc,
                    'statusAmc' => $statusAmc,
                    'branchFranchiseAssigned' => $branchFranchiseAssigned,
                    'brInstallationStatusAMC' => $brInstallationStatusAMC,
                    'amcYear1' => $amcYear1,
                    'amcYear1dueAmount' => $amcYear1dueAmount,
                    'amcYear1date' => $amcYear1date,
                    'statusYear1Amc' => $statusYear1Amc,
                    'amcYear2' => $amcYear2,
                    'amcYear2dueAmount' => $amcYear2dueAmount,
                    'amcYear2date' => $amcYear2date,
                    'statusYear2Amc' => $statusYear2Amc,
                    'amcYear3' => $amcYear3,
                    'amcYear3dueAmount' => $amcYear3dueAmount,
                    'amcYear3date' => $amcYear3date,
                    'statusYear3Amc' => $statusYear3Amc,
                    'amcYear4' => $amcYear4,
                    'amcYear4dueAmount' => $amcYear4dueAmount,
                    'amcYear4date' => $amcYear4date,
                    'statusYear4Amc' => $statusYear4Amc,
                    'amcYear5' => $amcYear5,
                    'amcYear5dueAmount' => $amcYear5dueAmount,
                    'amcYear5date' => $amcYear5date,
                    'statusYear5Amc' => $statusYear5Amc,
                    'dueDateAmc' => $dueDateAmc,
                    'descAmc' => $descAmc,
                    'penaltyCharges' => $penaltyCharges,
                    'penaltyAmount' => $penaltyAmount,
                    'otherChargesTitle' => $otherChargesTitle,
                    'otherChargesAmount' => $otherChargesAmount,
                    'amcYear1S3File' => $s3files,
                    'amcYear2S3File' => $s3files2,
                    'amcYear3S3File' => $s3files3,
                    'amcYear4S3File' => $s3files4,
                    'amcYear5S3File' => $s3files5,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );
                
                $result = $this->ay->editAmc($amcInfo, $amcId);
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Amc updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Amc updation failed');
                }
                
                redirect('amc/amcListing');
            }
        }
    }

    /** Code for CK editor */
    public function upload() {
        if (isset($_FILES['upload'])) {
            $file = $_FILES['upload'];
            $fileName = time() . '_' . $file['name'];
            $uploadPath = 'Uploads/';
            if (move_uploaded_file($file['tmp_name'], $uploadPath . $fileName)) {
                $url = base_url($uploadPath . $fileName);
                $message = 'Image uploaded successfully';
                $callback = $_GET['CKEditorFuncNum'];
                echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($callback, '$url', '$message');</script>";
            } else {
                $message = 'Error while uploading file';
                echo "<script type='text/javascript'>alert('$message');</script>";
            }
        }
    }

    public function fetchAssignedUsers() {
        $franchiseNumber = $this->input->post('franchiseNumber');
        $users = $this->ay->getUsersByFranchise($franchiseNumber);
        $options = '<option value="0">Select Role</option>';
        foreach ($users as $user) {
            $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
        }
        echo $options;
    }
}
?>