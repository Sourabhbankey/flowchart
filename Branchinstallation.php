<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Branchinstallation (BranchinstallationController)
 * Branchinstallation Class to control Branchinstallation related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 13 June 2024
 */
class Branchinstallation extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Branchinstallation_model', 'brins');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Branchinstallation';
        $this->load->library('pagination');
    }

    /**
     * This is default routing method
     * It routes to default listing ppincode
     */
    public function index()
    {
        redirect('branchinstallation/branchinstallationListing');
    }
    
    /**
     * This function is used to load the Branchinstallation list
     */
   


public function branchinstallationListing() {
     $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
  
         $franchiseFilter = $this->input->get('franchiseNumber');
            if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
            $config = array();
            $config['base_url'] = base_url('branchinstallation/branchinstallationListing');
            $config['per_page'] = 10; 
            $config['uri_segment'] = 3;
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

            if ($userRole == '14' || $userRole == '1'  || $userRole == '17'|| $userRole == '16'|| $userRole == '24' || $userRole == '23') { // Admin
                if ($franchiseFilter) {
                    $config['total_rows'] = $this->brins->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                    $data['records'] = $this->brins->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->brins->getTotalTrainingRecordsCount();
                    
                    $data['records'] = $this->brins->getAllTrainingRecords($config['per_page'], $page);
                }
                 } else if ($userRole == '15'|| $userRole == '13') { // Specific roles
                    $config['total_rows'] = $this->brins->getTotalTrainingRecordsCountByRole($userId);
                    $data['records'] = $this->brins->getTrainingRecordsByRole($userId, $config['per_page'], $page);
                    
                } else { 
                        $franchiseNumber = $this->brins->getFranchiseNumberByUserId($userId);
                        if ($franchiseNumber) {
                            if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                                $config['total_rows'] = $this->brins->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->brins->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                            } else {
                                $config['total_rows'] = $this->brins->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->brins->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                            }
                        } else {
                            $data['records'] = []; // Handle the case where franchise number is not found
                        }
                    }

                        // Initialize pagination
                    $data["serial_no"] = $page + 1;
                    $this->pagination->initialize($config);
                    $data["links"] = $this->pagination->create_links();
                    $data["start"] = $page + 1;
                    $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
                    $data["total_records"] = $config["total_rows"];
                    $data['pagination'] = $this->pagination->create_links();
                    $data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
                    $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
    $this->loadViews("branchinstallation/list", $this->global, $data, NULL);
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
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Branchinstallation';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("branchinstallation/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewBranchinstallation()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            //$this->form_validation->set_rules('branchSetupName','Branch  Name','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {    $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $branchSetupName = $this->security->xss_clean($this->input->post('branchSetupName'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                /*-new-added-field-*/
                $brcompAddress = $this->security->xss_clean($this->input->post('brcompAddress'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $state = $this->security->xss_clean($this->input->post('state'));
                //$birthday = $this->security->xss_clean($this->input->post('birthday'));
                $pincode = $this->security->xss_clean($this->input->post('pincode'));
                $acClearance = $this->security->xss_clean($this->input->post('acClearance'));
                /*--Newfield--*/
                $frCostInvoicenum = $this->security->xss_clean($this->input->post('frCostInvoicenum'));
                $studKitInvoicenum = $this->security->xss_clean($this->input->post('studKitInvoicenum'));
                $brsetupInvoicenum = $this->security->xss_clean($this->input->post('brsetupInvoicenum'));
                $lginvoicenum = $this->security->xss_clean($this->input->post('lginvoicenum'));
                $acRemark = $this->security->xss_clean($this->input->post('acRemark'));
                $lgClearance = $this->security->xss_clean($this->input->post('lgClearance'));
                $lgRemark = $this->security->xss_clean($this->input->post('lgRemark'));
                $infraRemark = $this->security->xss_clean($this->input->post('infraRemark'));

                $numOfStudKit = $this->security->xss_clean($this->input->post('numOfStudKit'));
                $studKitDesc = $this->security->xss_clean($this->input->post('studKitDesc'));
                $additionlOffer = $this->security->xss_clean($this->input->post('additionlOffer'));
                $specialRemark = $this->security->xss_clean($this->input->post('specialRemark'));
                $dateOfDespatch = $this->security->xss_clean($this->input->post('dateOfDespatch'));
                $modeOfDespatch = $this->security->xss_clean($this->input->post('modeOfDespatch'));
                $materialReceivedOn = $this->security->xss_clean($this->input->post('materialReceivedOn'));
                $schInstalldate = $this->security->xss_clean($this->input->post('schInstalldate'));
                $installDate = $this->security->xss_clean($this->input->post('installDate'));
                $instaBrstatus = $this->security->xss_clean($this->input->post('instaBrstatus'));
                $brAddressInstall = $this->security->xss_clean($this->input->post('brAddressInstall'));
                $instaBrstatusDate = $this->security->xss_clean($this->input->post('instaBrstatusDate'));
                $prefInstalldate = $this->security->xss_clean($this->input->post('prefInstalldate'));
                $deliverychallan = $this->security->xss_clean($this->input->post('deliverychallan'));
                $s3files = $this->security->xss_clean($this->input->post('frcostInvoiceS3File'));
			 	$s3files1 = $this->security->xss_clean($this->input->post('studkitInvoiceS3File'));
                $s3files2 = $this->security->xss_clean($this->input->post('brsetupInvoiceS3File'));
                $s3files3 = $this->security->xss_clean($this->input->post('lgchargInvoiceS3File'));
                $s3files4 = $this->security->xss_clean($this->input->post('eBayinstallkitInvoiceS3File'));
                $s3files5 = $this->security->xss_clean($this->input->post('eBaystudKitInvoiceS3File'));
                $s3files6 = $this->security->xss_clean($this->input->post('upDespatchReceiptS3File'));
               
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumbers = implode(',',$franchiseNumberArray);
                  /*Student-kit-upload*/
                  $s3_file_link = [];
$s3_file_link1 = [];
$s3_file_link2 = [];
$s3_file_link3 = [];
$s3_file_link4 = [];
$s3_file_link5 = [];
$s3_file_link6 = [];
                if (isset($_FILES["frcostInvoiceS3File"]) && $_FILES["frcostInvoiceS3File"]["error"] == UPLOAD_ERR_OK) {
    $dir = dirname($_FILES["frcostInvoiceS3File"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["frcostInvoiceS3File"]["name"];
    move_uploaded_file($_FILES["frcostInvoiceS3File"]["tmp_name"], $destination);
    $storeFolder = 'attachements';
    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
    $result_arr = $s3Result->toArray();
    if (!empty($result_arr['ObjectURL'])) {
        $s3_file_link[] = $result_arr['ObjectURL'];
    } else {
        $s3_file_link[] = '';
    }
}
$s3files = implode(',', $s3_file_link);

// Franchise Cost-Attach
if (isset($_FILES["studkitInvoiceS3File"]) && $_FILES["studkitInvoiceS3File"]["error"] == UPLOAD_ERR_OK) {
    $dir1 = dirname($_FILES["studkitInvoiceS3File"]["tmp_name"]);
    $destination1 = $dir1 . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["studkitInvoiceS3File"]["name"];
    move_uploaded_file($_FILES["studkitInvoiceS3File"]["tmp_name"], $destination1);
    $storeFolder1 = 'attachements';
    $s3Result1 = $this->s3_upload->upload_file($destination1, $storeFolder1);
    $result_arr1 = $s3Result1->toArray();
    if (!empty($result_arr1['ObjectURL'])) {
        $s3_file_link1[] = $result_arr1['ObjectURL'];
    } else {
        $s3_file_link1[] = '';
    }
}
$s3files1 = implode(',', $s3_file_link1);

// Branch-Setup
if (isset($_FILES["brsetupInvoiceS3File"]) && $_FILES["brsetupInvoiceS3File"]["error"] == UPLOAD_ERR_OK) {
    $dir2 = dirname($_FILES["brsetupInvoiceS3File"]["tmp_name"]);
    $destination2 = $dir2 . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["brsetupInvoiceS3File"]["name"];
    move_uploaded_file($_FILES["brsetupInvoiceS3File"]["tmp_name"], $destination2);
    $storeFolder2 = 'attachements';
    $s3Result2 = $this->s3_upload->upload_file($destination2, $storeFolder2);
    $result_arr2 = $s3Result2->toArray();
    if (!empty($result_arr2['ObjectURL'])) {
        $s3_file_link2[] = $result_arr2['ObjectURL'];
    } else {
        $s3_file_link2[] = '';
    }
}
$s3files2 = implode(',', $s3_file_link2);

// Legal
if (isset($_FILES["lgchargInvoiceS3File"]) && $_FILES["lgchargInvoiceS3File"]["error"] == UPLOAD_ERR_OK) {
    $dir3 = dirname($_FILES["lgchargInvoiceS3File"]["tmp_name"]);
    $destination3 = $dir3 . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["lgchargInvoiceS3File"]["name"];
    move_uploaded_file($_FILES["lgchargInvoiceS3File"]["tmp_name"], $destination3);
    $storeFolder3 = 'attachements';
    $s3Result3 = $this->s3_upload->upload_file($destination3, $storeFolder3);
    $result_arr3 = $s3Result3->toArray();
    if (!empty($result_arr3['ObjectURL'])) {
        $s3_file_link3[] = $result_arr3['ObjectURL'];
    } else {
        $s3_file_link3[] = '';
    }
}
$s3files3 = implode(',', $s3_file_link3);

// eBay Bill Installation-Kit
if (isset($_FILES["eBayinstallkitInvoiceS3File"]) && $_FILES["eBayinstallkitInvoiceS3File"]["error"] == UPLOAD_ERR_OK) {
    $dir4 = dirname($_FILES["eBayinstallkitInvoiceS3File"]["tmp_name"]);
    $destination4 = $dir4 . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["eBayinstallkitInvoiceS3File"]["name"];
    move_uploaded_file($_FILES["eBayinstallkitInvoiceS3File"]["tmp_name"], $destination4);
    $storeFolder4 = 'attachements';
    $s3Result4 = $this->s3_upload->upload_file($destination4, $storeFolder4);
    $result_arr4 = $s3Result4->toArray();
    if (!empty($result_arr4['ObjectURL'])) {
        $s3_file_link4[] = $result_arr4['ObjectURL'];
    } else {
        $s3_file_link4[] = '';
    }
}
$s3files4 = implode(',', $s3_file_link4);

// eBay Bill Student-Kit
if (isset($_FILES["eBaystudKitInvoiceS3File"]) && $_FILES["eBaystudKitInvoiceS3File"]["error"] == UPLOAD_ERR_OK) {
    $dir5 = dirname($_FILES["eBaystudKitInvoiceS3File"]["tmp_name"]);
    $destination5 = $dir5 . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["eBaystudKitInvoiceS3File"]["name"];
    move_uploaded_file($_FILES["eBaystudKitInvoiceS3File"]["tmp_name"], $destination5);
    $storeFolder5 = 'attachements';
    $s3Result5 = $this->s3_upload->upload_file($destination5, $storeFolder5);
    $result_arr5 = $s3Result5->toArray();
    if (!empty($result_arr5['ObjectURL'])) {
        $s3_file_link5[] = $result_arr5['ObjectURL'];
    } else {
        $s3_file_link5[] = '';
    }
}
$s3files5 = implode(',', $s3_file_link5);

// Dispatch Receipt
if (isset($_FILES["upDespatchReceiptS3File"]) && $_FILES["upDespatchReceiptS3File"]["error"] == UPLOAD_ERR_OK) {
    $dir6 = dirname($_FILES["upDespatchReceiptS3File"]["tmp_name"]);
    $destination6 = $dir6 . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["upDespatchReceiptS3File"]["name"];
    move_uploaded_file($_FILES["upDespatchReceiptS3File"]["tmp_name"], $destination6);
    $storeFolder6 = 'attachements';
    $s3Result6 = $this->s3_upload->upload_file($destination6, $storeFolder6);
    $result_arr6 = $s3Result6->toArray();
    if (!empty($result_arr6['ObjectURL'])) {
        $s3_file_link6[] = $result_arr6['ObjectURL'];
    } else {
        $s3_file_link6[] = '';
    }
}
$s3files6 = implode(',', $s3_file_link6);
								
              
                /*-ENd-added-field-*/
                $branchinstallationInfo = array(
                    'brspFranchiseAssigned'=>$brspFranchiseAssigned,
                    'branchSetupName'=>$branchSetupName,
				'brcompAddress'=>$brcompAddress,
				'franchiseNumber'=>$franchiseNumbers,
				'pincode'=>$pincode, 
				'city'=>$city,
				'state'=>$state, 
				'acClearance'=>$acClearance,
				'frcostInvoiceS3File'=>$s3files,
				'studkitInvoiceS3File'=>$s3files1,
				'brsetupInvoiceS3File'=>$s3files2,
				'lgchargInvoiceS3File'=>$s3files3,
				'eBayinstallkitInvoiceS3File'=>$s3files4,
				'eBaystudKitInvoiceS3File'=>$s3files5,
				'upDespatchReceiptS3File'=>$s3files6,
				'frCostInvoicenum'=>$frCostInvoicenum,
				'studKitInvoicenum'=>$studKitInvoicenum,  
				'brsetupInvoicenum'=>$brsetupInvoicenum,
				'lginvoicenum'=>$lginvoicenum,   
				'lgClearance'=>$lgClearance,
				'lgRemark'=>$lgRemark, 
				'infraRemark'=>$infraRemark,
				'numOfStudKit'=>$numOfStudKit,
				'studKitDesc'=>$studKitDesc,
				'additionlOffer'=>$additionlOffer,
				'specialRemark'=>$specialRemark,
				'dateOfDespatch'=>$dateOfDespatch,
				'modeOfDespatch'=>$modeOfDespatch,
				'materialReceivedOn'=>$materialReceivedOn,
				'schInstalldate'=>$schInstalldate,
				'installDate'=>$installDate,
				'instaBrstatus'=>$instaBrstatus,
				'instaBrstatusDate'=>$instaBrstatusDate,
				'brAddressInstall'=>$brAddressInstall,
				'prefInstalldate'=>$prefInstalldate,
				'description'=>$description, 
                'deliverychallan' =>$deliverychallan,
				'createdBy'=>$this->vendorId, 
				'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->brins->addNewBranchinstallation($branchinstallationInfo);

               
                if($result > 0) {
                     $this->load->model('Notification_model');

                // ✅ Send Notification to Assigned Franchise User
                if (!empty($brspFranchiseAssigned)) {
                    $notificationMessage = "<strong>Branch Installation :</strong>A new branchinstall has been added.";
                    $this->Notification_model->add_branchinstall_notification($brspFranchiseAssigned, $notificationMessage, $result);
                }

                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Branch Setup installation";
                                $messpincode = 'Dear '.$branchDetail->applicantName.' ';
                                //$messpincode = ' '.$description.' ';
                                $messpincode .= 'You have been assigned a new meeting. BY- '.$this->session->userdata("branchSetupName").' ';
                                $messpincode .= 'Please visit the portal.';
                                //$messpincode = ' '.$description.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to,$subject,$messpincode,$headers);
                                          // ✅ Get User ID mapped with this Franchise Number
                            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                            if (!empty($franchiseUser)) {
                                $notificationMessage = "<strong>Branch Installation :</strong>A new branchinstall has been added.";
                                $this->Notification_model->add_branchinstall_notification($franchiseUser->userId, $notificationMessage, $result);
                            }
                            // ✅ Notify Admins (roleId = 1, 14)
                $adminUsers = $this->bm->getUsersByRoles([1, 14,2]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $this->Notification_model->add_branchinstall_notification($adminUser->userId, "<strong>Branch Installation :</strong>A new branchinstall has been added.", $result);
                    }
                }
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'Branch Installation created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Branch Installation creation failed');
                }
                
                redirect('branchinstallation/branchinstallationListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($brsetupid = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($brsetupid == null)
            {
                redirect('branchinstallation/branchinstallationListing');
            }
            
            $data['branchinstallationInfo'] = $this->brins->getBranchinstallationInfo($brsetupid);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Branch installation : Edit Branchinstallation';
            
            $this->loadViews("branchinstallation/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
 function editBranchinstallation()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        $this->load->model('Notification_model'); // Load the notification model

        $brsetupid = $this->input->post('brsetupid');

        // Validation rules
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($brsetupid);
        } else {
            // Sanitize inputs
           $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
            $branchSetupName = $this->security->xss_clean($this->input->post('branchSetupName'));
            $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
            $description = $this->security->xss_clean($this->input->post('description'));
            $brcompAddress = $this->security->xss_clean($this->input->post('brcompAddress'));
            $pincode = $this->security->xss_clean($this->input->post('pincode'));
            $city = $this->security->xss_clean($this->input->post('city'));
            $state = $this->security->xss_clean($this->input->post('state'));
            $acClearance = $this->security->xss_clean($this->input->post('acClearance'));
            $frCostInvoicenum = $this->security->xss_clean($this->input->post('frCostInvoicenum'));
            $studKitInvoicenum = $this->security->xss_clean($this->input->post('studKitInvoicenum'));
            $brsetupInvoicenum = $this->security->xss_clean($this->input->post('brsetupInvoicenum'));
            $lginvoicenum = $this->security->xss_clean($this->input->post('lginvoicenum'));
            $acRemark = $this->security->xss_clean($this->input->post('acRemark'));
            $lgClearance = $this->security->xss_clean($this->input->post('lgClearance'));
            $lgRemark = $this->security->xss_clean($this->input->post('lgRemark'));
            $infraRemark = $this->security->xss_clean($this->input->post('infraRemark'));
            $numOfStudKit = $this->security->xss_clean($this->input->post('numOfStudKit'));
            $studKitDesc = $this->security->xss_clean($this->input->post('studKitDesc'));
            $additionlOffer = $this->security->xss_clean($this->input->post('additionlOffer'));
            $specialRemark = $this->security->xss_clean($this->input->post('specialRemark'));
            $dateOfDespatch = $this->security->xss_clean($this->input->post('dateOfDespatch'));
            $modeOfDespatch = $this->security->xss_clean($this->input->post('modeOfDespatch'));
            $materialReceivedOn = $this->security->xss_clean($this->input->post('materialReceivedOn'));
            $schInstalldate = $this->security->xss_clean($this->input->post('schInstalldate'));
            $installDate = $this->security->xss_clean($this->input->post('installDate'));
            $instaBrstatus = $this->security->xss_clean($this->input->post('instaBrstatus'));
            $brAddressInstall = $this->security->xss_clean($this->input->post('brAddressInstall'));
            $instaBrstatusDate = $this->security->xss_clean($this->input->post('instaBrstatusDate'));
            $prefInstalldate = $this->security->xss_clean($this->input->post('prefInstalldate'));
            $deliverychallan = $this->security->xss_clean($this->input->post('deliverychallan'));

            // Handle file uploads
            $uploadedFiles = [
                'frcostInvoiceS3File' => $this->input->post('existing_frcostInvoiceS3File'),
                'studkitInvoiceS3File' => $this->input->post('existing_studkitInvoiceS3File'),
                'brsetupInvoiceS3File' => $this->input->post('existing_brsetupInvoiceS3File'),
                'lgchargInvoiceS3File' => $this->input->post('existing_lgchargInvoiceS3File'),
                'eBayinstallkitInvoiceS3File' => $this->input->post('existing_eBayinstallkitInvoiceS3File'),
                'eBaystudKitInvoiceS3File' => $this->input->post('existing_eBaystudKitInvoiceS3File'),
                'upDespatchReceiptS3File' => $this->input->post('existing_upDespatchReceiptS3File'),
            ];

            foreach ($uploadedFiles as $field => &$file) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                    $dir = dirname($_FILES[$field]['tmp_name']);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES[$field]['name'];
                    rename($_FILES[$field]['tmp_name'], $destination);
                    $storeFolder = 'attachments';

                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $file = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : $file; // Retain the old file if upload fails
                }
            }

            // Merge all fields
            $franchiseNumbers = implode(',', (array)$franchiseNumberArray);
            $branchinstallationInfo = array_merge([
                'brspFranchiseAssigned' => $brspFranchiseAssigned,
                'branchSetupName' => $branchSetupName,
                'franchiseNumber' => $franchiseNumbers,
                'brcompAddress' => $brcompAddress,
                'pincode' => $pincode,
                'city' => $city,
                'state' => $state,
                'acClearance' => $acClearance,
                'frCostInvoicenum' => $frCostInvoicenum,
                'studKitInvoicenum' => $studKitInvoicenum,
                'brsetupInvoicenum' => $brsetupInvoicenum,
                'lginvoicenum' => $lginvoicenum,
                'lgClearance' => $lgClearance,
                'lgRemark' => $lgRemark,
                'infraRemark' => $infraRemark,
                'acRemark' => $acRemark,
                'numOfStudKit' => $numOfStudKit,
                'studKitDesc' => $studKitDesc,
                'additionlOffer' => $additionlOffer,
                'specialRemark' => $specialRemark,
                'dateOfDespatch' => $dateOfDespatch,
                'modeOfDespatch' => $modeOfDespatch,
                'materialReceivedOn' => $materialReceivedOn,
                'schInstalldate' => $schInstalldate,
                'installDate' => $installDate,
                'instaBrstatus' => $instaBrstatus,
                'instaBrstatusDate' => $instaBrstatusDate,
                'brAddressInstall' => $brAddressInstall,
                'prefInstalldate' => $prefInstalldate,
                'deliverychallan' => $deliverychallan,
                'description' => $description,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s'),
            ], $uploadedFiles);

            // Update the database
            $result = $this->brins->editBranchinstallation($branchinstallationInfo, $brsetupid);

          if ($result == true) {
   

    // ✅ Send Notification to Assigned Franchise User
    if (!empty($brspFranchiseAssigned)) {
        $notificationMessage = "<strong>Branch Installation :</strong>A branch installation has been updated.";
        $this->Notification_model->add_branchinstall_notification($brspFranchiseAssigned, $notificationMessage, $brsetupid);
    }

    // ✅ Notify Franchise Users via Email & Notification
    if (!empty($franchiseNumberArray)) {
        foreach ($franchiseNumberArray as $franchiseNumber) {
            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
           /* if (!empty($branchDetail)) {
                // Send email notification
                $to = $branchDetail->officialEmailID;
                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Branch Setup installation";
                $messpincode = 'Dear ' . $branchDetail->applicantName . ' ';
                $messpincode .= 'You have been assigned a new meeting. BY- ' . $this->session->userdata("branchSetupName") . ' ';
                $messpincode .= 'Please visit the portal.';
                $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                mail($to, $subject, $messpincode, $headers);*/
                 if (!empty($branchDetail)) {
                            // Send email notification
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL Branch Installation Updated";
                            $message = "Dear {$branchDetail->applicantName}, ";
                            $message .= "A branch installation has been updated by {$this->session->userdata('name')}. ";
                            $message .= "Branch Setup Name: {$branchSetupName}, Branch Setup ID: {$brsetupid}, Description: {$description}. ";
                            $message .= "Please visit the portal for details.";
                            $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                            if (!mail($to, $subject, $message, $headers)) {
                                log_message('error', "Failed to send email to {$to} for branch setup ID {$brsetupid}");
                            }

                // Notify mapped franchise user
                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
              /*  print_r($franchiseUser);exit;*/
                if (!empty($franchiseUser)) {
                    $notificationMessage = "<strong>Branch Installation :</strong>A branch installation has been updated.";
                    $this->Notification_model->add_branchinstall_notification($franchiseUser->userId, $notificationMessage, $brsetupid);
                }
            }
        }
    }

    // ✅ Notify Admins (roleId = 1, 14, 2)
    $adminUsers = $this->bm->getUsersByRoles([1, 14, 2]);
    if (!empty($adminUsers)) {
        foreach ($adminUsers as $adminUser) {
            $this->Notification_model->add_branchinstall_notification($adminUser->userId, "<strong>Branch Installation :</strong>A branch installation has been updated.", $brsetupid);
        }
    }

    // ✅ Email Notification to Admin
    $adminEmail = 'dev.edumeta@gmail.com'; // Replace with actual admin email
    $adminSubject = "Alert - eduMETA THE i-SCHOOL Branch Installation Updated";
    $adminMessage = "A branch installation has been updated. ";
    $adminMessage .= "Branch Setup Name: {$branchSetupName}, Branch Setup ID: {$brsetupid}. ";
    $adminMessage .= "Please visit the portal for details.";
    $adminHeaders = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
    mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);

    $this->session->set_flashdata('success', 'Branch installation details updated successfully');
} else {
    $this->session->set_flashdata('error', 'Branch installation details updation failed');
}
redirect('branchinstallation/branchinstallationListing');
        }
    }
}
    public function getBranchDetails()
{
    $franchiseNumber = $this->input->post('franchiseNumber');
    if ($franchiseNumber) {
      //  $this->load->model('BranchModel'); // Replace with your model name
        $branchDetails = $this->brins->getBranchByFranchiseNumber($franchiseNumber);

        if (!empty($branchDetails)) {
            echo json_encode($branchDetails);
        } else {
            echo json_encode([]);
        }
    } else {
        echo json_encode([]);
    }
}
public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Fetch the users based on the franchise number
    $users = $this->brins->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

    // Generate HTML options for the response
    $options = '<option value="0">Select Role</option>';
    foreach ($users as $user) {
        $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
    }

    echo $options; // Output the options as HTML
}

}

?>