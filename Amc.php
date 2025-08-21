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
           $this->load->model('Notification_model', 'nm');
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
/*public function amcListing()
{
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');

    $franchiseFilter = $this->input->get('franchiseNumber');
    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = '';
    }

    $config = array();
    $config['base_url'] = base_url('amc/amcListing');
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;
    $config['page_query_string'] = true;
    $config['reuse_query_string'] = true;
    $page = ($this->input->get('per_page')) ? $this->input->get('per_page') : 0;

    // For Admin roles
    if (in_array($userRole, ['1', '14', '16', '23', '29', '31'])) {
        if ($franchiseFilter) {
            $config['total_rows'] = $this->ay->getTotalAmcRecordsCountByFranchise($franchiseFilter);
            $data['records'] = $this->ay->getAmcRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
        } else {
            $config['total_rows'] = $this->ay->getTotalTrainingRecordsCount();
            $data['records'] = $this->ay->getAllTrainingRecords($config['per_page'], $page, $franchiseFilter);
        }
    }
    // For role 15: show records only for assigned branches
    elseif ($userRole == '15') {
        $assignedBranchIds = $this->ay->getAssignedBranchIds($userId);
        if (!empty($assignedBranchIds)) {
            $config['total_rows'] = $this->ay->getTotalAmcRecordsCountByAssignedBranches($assignedBranchIds, $franchiseFilter);
            $data['records'] = $this->ay->getAmcRecordsByAssignedBranches($assignedBranchIds, $config['per_page'], $page, $franchiseFilter);
        } else {
            $data['records'] = [];
            $config['total_rows'] = 0;
        }
    }
    // For franchise role
    else {
        $franchiseNumber = $this->ay->getFranchiseNumberByUserId($userId);
        if ($franchiseNumber) {
            $config['total_rows'] = $this->ay->getTotalAmcRecordsCountByFranchise($franchiseNumber);
            $data['records'] = $this->ay->getAmcRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
        } else {
            $data['records'] = [];
            $config['total_rows'] = 0;
        }
    }

    $this->pagination->initialize($config);

    $data["serial_no"] = $page + 1;
    $data["links"] = $this->pagination->create_links();
    $data["start"] = $page + 1;
    $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
    $data["total_records"] = $config["total_rows"];
    $data['pagination'] = $this->pagination->create_links();
    $data["franchiseFilter"] = $franchiseFilter;
    $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

    $this->loadViews("amc/list", $this->global, $data, NULL);
}
*/
public function amcListing() {
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');
    
    // Get franchise filter from query string
    $franchiseFilter = $this->input->get('franchiseNumber');
     $statusAmc = $this->input->get('statusAmc');
    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = '';
        $statusAmc = '';     // Reset filter
    }
    
    // Pagination configuration
   $config = array();
$config['base_url'] = base_url('amc/amcListing');
$config['per_page'] = 10; 
$config['uri_segment'] = 3;
$config['page_query_string'] = true; // Enable query string
$config['reuse_query_string'] = true; // Preserve query string parameters
$page = ($this->input->get('per_page')) ? $this->input->get('per_page') : 0;

if ($userRole == '14' || $userRole == '1' || $userRole == '23' || $userRole == '16'|| $userRole == '29' || $userRole == '31') { 
    // Admin roles
    if ($franchiseFilter) {
        $config['total_rows'] = $this->ay->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
        $data['records'] = $this->ay->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
    } else {
        $config['total_rows'] = $this->ay->getTotalTrainingRecordsCount();
        $data['records'] = $this->ay->getAllTrainingRecords($config['per_page'], $page,$franchiseFilter);
    }
} elseif ($userRole == '15') { 
    // Specific roles
    $config['total_rows'] = $this->ay->getTotalTrainingRecordsCountByRole($userId,$franchiseFilter);
    $data['records'] = $this->ay->getTrainingRecordsByRole($userId, $config['per_page'], $page, $franchiseFilter);
} else { 
    // Franchise-specific logic
    $franchiseNumber = $this->ay->getFranchiseNumberByUserId($userId);
    if ($franchiseNumber) {
        if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
            $config['total_rows'] = $this->ay->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
            $data['records'] = $this->ay->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
        } else {
            $config['total_rows'] = $this->ay->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
            $data['records'] = $this->ay->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
        }
    } else {
        $data['records'] = []; // Handle case where franchise number is not found
    }
}

// Initialize pagination
$this->pagination->initialize($config);

$data["serial_no"] = $page + 1;
$data["links"] = $this->pagination->create_links();
$data["start"] = $page + 1;
$data["end"] = min($page + $config["per_page"], $config["total_rows"]);
$data["total_records"] = $config["total_rows"];
$data['pagination'] = $this->pagination->create_links();
 $data["statusAmc"] = $statusAmc;
$data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

$this->loadViews("amc/list", $this->global, $data, NULL);
}


    public function amcInactiveListing() {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        $franchiseFilter = $this->input->get('franchiseNumber');
        $statusAmc = $this->input->get('statusAmc');
        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
            $statusAmc = '';
        }

        $config = array();
        $config['base_url'] = base_url('amc/amcInactiveListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        if (in_array($userRole, ['14', '1', '23', '16','28'])) {
            if ($franchiseFilter) {
                $config['total_rows'] = $this->ay->getTotalInactiveAmcCountByFranchise($franchiseFilter, $statusAmc);
                $data['records'] = $this->ay->getInactiveAmcRecordsByFranchise($franchiseFilter, $config['per_page'], $page, $statusAmc);
            } else {
                $config['total_rows'] = $this->ay->getTotalInactiveAmcCount($statusAmc);
                $data['records'] = $this->ay->getAllInactiveAmcRecords($config['per_page'], $page, $statusAmc);
            }
        } else {
            $franchiseNumber = $this->ay->getFranchiseNumberByUserId($userId);

            if ($franchiseNumber) {
                $config['total_rows'] = $this->ay->getTotalInactiveAmcCountByFranchise($franchiseNumber, $statusAmc);
                $data['records'] = $this->ay->getInactiveAmcRecordsByFranchise($franchiseNumber, $config['per_page'], $page, $statusAmc);
            } else {
                $data['records'] = [];
                $config['total_rows'] = 0;
            }
        }

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

        $this->loadViews("amc/listinactive", $this->global, $data, NULL);
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
                $penaltyCharges = $this->security->xss_clean($this->input->post('penaltyCharges'));
                $penaltyAmount = $this->security->xss_clean($this->input->post('penaltyAmount'));
                $otherChargesTitle = $this->security->xss_clean($this->input->post('otherChargesTitle'));
                $otherChargesAmount = $this->security->xss_clean($this->input->post('otherChargesAmount'));
                $descAmc = $this->security->xss_clean($this->input->post('descAmc'));
                // Handle AMC Years and File Uploads
                $amcYears = [];
                for ($i = 1; $i <= 10; $i++) {
                    $paidKey = "amcPaid{$i}";
                    $yearKey = "amcYear{$i}";
                    $amountKey = "{$yearKey}dueAmount";
                    $dateKey = "{$yearKey}date";
                    $statusKey = "statusYear{$i}Amc";
                    $fileKey = "file" . ($i == 1 ? '' : $i);

                    $paid = $this->security->xss_clean($this->input->post($paidKey));
                    $year = $this->security->xss_clean($this->input->post($yearKey));
                    $dueAmount = $this->security->xss_clean($this->input->post($amountKey));
                    $date = $this->security->xss_clean($this->input->post($dateKey));
                    $status = $this->security->xss_clean($this->input->post($statusKey));

                    if (empty($year) && empty($dueAmount) && empty($date) && empty($status)) continue;

                    $s3File = '';
                    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]["error"] == 0) {
                        $dir = dirname($_FILES[$fileKey]["tmp_name"]);
                        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES[$fileKey]["name"];
                        rename($_FILES[$fileKey]["tmp_name"], $destination);
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        $s3File = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
                    }

                    $amcYears[$i] = [
                        "amcPaid{$i}" => $paid,
                        "amcYear{$i}" => $year,
                        "amcYear{$i}dueAmount" => $dueAmount,
                        "amcYear{$i}date" => $date,
                        "statusYear{$i}Amc" => $status,
                        "amcYear{$i}S3File" => $s3File
                    ];
                }

                $amcInfo = [
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'franchiseName' => $franchiseName,
                    'franchiseNumber' => is_array($franchiseNumberArray) ? implode(',', $franchiseNumberArray) : $franchiseNumberArray,
                    'branchLocation' => $branchLocation,
                    'branchState' => $branchState,
                    'oldAMCdue' => $oldAMCdue,
                    'curAmc' => $curAmc,
                    'totalAmc' => $totalAmc,
                    'statusAmc' => $statusAmc,
                    'branchFranchiseAssigned' => $branchFranchiseAssigned,
                    'brInstallationStatusAMC' => $brInstallationStatusAMC,
                    'dueDateAmc' => $dueDateAmc,
                    'descAmc' => $descAmc,
                    'penaltyCharges' => $penaltyCharges,
                    'penaltyAmount' => $penaltyAmount,
                    'otherChargesTitle' => $otherChargesTitle,
                    'otherChargesAmount' => $otherChargesAmount,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                ];

                // Merge all AMC year data
                foreach ($amcYears as $yearData) {
                    $amcInfo = array_merge($amcInfo, $yearData);
                }


                
                $result = $this->ay->addNewAmc($amcInfo);
                $this->load->model('Notification_model');

                if ($result > 0) {

                    //   $allUsers = $this->nm->get_all_users();
                    // foreach ($allUsers as $user) {
                    //     $message = "<strong>Campaign:</strong> New amc Create '{$franchiseName}' (Campaign ID: {$result})";
                    //     $notificationResult = $this->nm->add_amc_notification($result, $message, $user['userId']);
                    //     if (!$notificationResult) {
                    //         log_message('error', "Failed to add notification for user {$user['userId']} on campaign ID {$result}");
                    //     }
                    // }
                    // ✅ Send Email to Admin
                    $to = 'dev.edumeta@gmail.com';
                    $subject = "New AMC Created - eduMETA THE i-SCHOOL";
                    $message = "Dear Admin,<br><br>";
                    $message .= "A new AMC has been created by {$this->session->userdata('name')}.<br>";
                    $message .= "<strong>AMC Details:</strong><br>";
                    
                    $message .= "Please visit the portal for more details.<br><br>";
                    $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                    $headers = "From: Edumeta Team <noreply@theischool.com>\r\n";
                    $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if (!mail($to, $subject, $message, $headers)) {
                        log_message('error', 'Failed to send email to ' . $to);
                    }

                    // ✅ Send Notification to Assigned Franchise User
                    if (!empty($brspFranchiseAssigned)) {
                        $notificationMessage = "<strong>AMC:</strong> A new AMC has been assigned to you.";
                        $this->Notification_model->add_amc_notification($brspFranchiseAssigned, $notificationMessage, $result);
                    }

                    // ✅ Get User ID mapped with this Franchise Number
                    $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                    if (!empty($franchiseUser)) {
                        $notificationMessage = "<strong>AMC:</strong> A new AMC has been assigned to you.";
                        $this->Notification_model->add_amc_notification($franchiseUser->userId, $notificationMessage, $result);
                    }

                    // ✅ Notify Admins (roleId = 1, 14)
                    $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                    if (!empty($adminUsers)) {
                        foreach ($adminUsers as $adminUser) {
                            $this->Notification_model->add_amc_notification($adminUser->userId, "<strong>AMC:</strong> A new AMC has been assigned to you.", $result);
                        }
                    }

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
     * This function is used to edit the AMC information
    */
public function editAmc()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
        return;
    }

    $this->load->library('form_validation');
    $this->load->model('Notification_model');

    $amcId = $this->input->post('amcId');
    if (empty($amcId)) {
        $this->session->set_flashdata('error', 'Invalid AMC ID');
        redirect('amc/amcListing');
        return;
    }

    $this->form_validation->set_rules('descAmc', 'Description', 'trim|required|max_length[1024]');
    if ($this->form_validation->run() == FALSE) {
        $this->edit($amcId);
        return;
    }

    // Basic Fields
    $franchiseName = $this->security->xss_clean($this->input->post('franchiseName'));
    $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
    $branchLocation = $this->security->xss_clean($this->input->post('branchLocation'));
    $branchState = $this->security->xss_clean($this->input->post('branchState'));
    $oldAMCdue = $this->security->xss_clean($this->input->post('oldAMCdue'));
    $curAmc = $this->security->xss_clean($this->input->post('amcAmount'));
    $totalAmc = $this->security->xss_clean($this->input->post('totalAmc'));
    $statusAmc = $this->security->xss_clean($this->input->post('statusAmc'));
    $branchFranchiseAssigned = $this->security->xss_clean($this->input->post('branchFranchiseAssigned'));
    $brInstallationStatusAMC = $this->security->xss_clean($this->input->post('brInstallationStatusAMC'));
    $dueDateAmc = $this->security->xss_clean($this->input->post('dueDateAmc'));
    $penaltyCharges = $this->security->xss_clean($this->input->post('penaltyCharges'));
    $penaltyAmount = $this->security->xss_clean($this->input->post('penaltyAmount'));
    $otherChargesTitle = $this->security->xss_clean($this->input->post('otherChargesTitle'));
    $otherChargesAmount = $this->security->xss_clean($this->input->post('otherChargesAmount'));
    $descAmc = $this->security->xss_clean($this->input->post('descAmc'));

    // Base Info
    $amcInfo = [
        'franchiseName'             => $franchiseName,
        'franchiseNumber'           => $franchiseNumber,
        'branchLocation'            => $branchLocation,
        'branchState'               => $branchState,
        'oldAMCdue'                 => $oldAMCdue,
        'curAmc'                    => $curAmc,
        'totalAmc'                  => $totalAmc,
        'statusAmc'                 => $statusAmc,
        'branchFranchiseAssigned'   => $branchFranchiseAssigned,
        'brInstallationStatusAMC'   => $brInstallationStatusAMC,
        'dueDateAmc'                => $dueDateAmc,
        'descAmc'                   => $descAmc,
        'penaltyCharges'            => $penaltyCharges,
        'penaltyAmount'             => $penaltyAmount,
        'otherChargesTitle'         => $otherChargesTitle,
        'otherChargesAmount'        => $otherChargesAmount,
        'updatedBy'                 => $this->vendorId,
        'updatedDtm'                => date('Y-m-d H:i:s')
    ];

    // Get existing AMC record
    $existingAmc = $this->ay->getAmcInfo($amcId);

    // Loop through years
    for ($i = 1; $i <= 10; $i++) {
        $amcPaid = $this->security->xss_clean($this->input->post("amcPaid{$i}"));
        $amcYear = $this->security->xss_clean($this->input->post("amcYear{$i}"));
        $dueAmount = $this->security->xss_clean($this->input->post("amcYear{$i}dueAmount"));
        $amcDate = $this->security->xss_clean($this->input->post("amcYear{$i}date"));
        $statusAmcYear = $this->security->xss_clean($this->input->post("statusYear{$i}Amc"));

        // Check for file upload
        $amcS3File = $existingAmc->{"amcYear{$i}S3File"} ?? '';
        if (!empty($_FILES["file{$i}"]['name']) && $_FILES["file{$i}"]["error"] == 0) {
            $tmpFilePath = $_FILES["file{$i}"]["tmp_name"];
            $filename = time() . '-' . basename($_FILES["file{$i}"]["name"]);
            $s3Key = 'attachements/' . $filename;
            $uploadResult = $this->s3_upload->upload_file($tmpFilePath, $s3Key);
            if ($uploadResult) {
                $amcS3File = 'https://support-smsfiles.s3.ap-south-1.amazonaws.com/' . $s3Key;
            }
        }

        // Preserve previous data if not updated
        $amcInfo["amcPaid{$i}"] = !empty($amcPaid) ? $amcPaid : $existingAmc->{"amcPaid{$i}"};
        $amcInfo["amcYear{$i}"] = !empty($amcYear) ? $amcYear : $existingAmc->{"amcYear{$i}"};
        $amcInfo["amcYear{$i}dueAmount"] = !empty($dueAmount) ? $dueAmount : $existingAmc->{"amcYear{$i}dueAmount"};
        $amcInfo["amcYear{$i}date"] = !empty($amcDate) ? $amcDate : $existingAmc->{"amcYear{$i}date"};
        $amcInfo["statusYear{$i}Amc"] = !empty($statusAmcYear) ? $statusAmcYear : $existingAmc->{"statusYear{$i}Amc"};
        $amcInfo["amcYear{$i}S3File"] = $amcS3File;
    }

    $result = $this->ay->editAmc($amcInfo, $amcId);

    if ($result) {
        $this->Notification_model->add_amc_notification($this->vendorId, "You updated AMC: {$franchiseName} (AMC ID: {$amcId}, Status: {$statusAmc})", $amcId);

        $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
        if (!empty($franchiseUser)) {
            $this->Notification_model->add_amc_notification($franchiseUser->userId, "AMC updated: {$franchiseName} (AMC ID: {$amcId}, Status: {$statusAmc})", $amcId);
        }

        if (!empty($existingAmc->brspFranchiseAssigned)) {
            $this->Notification_model->add_amc_notification($existingAmc->brspFranchiseAssigned, "AMC updated: {$franchiseName} (AMC ID: {$amcId}, Status: {$statusAmc})", $amcId);
        }

        $adminUsers = $this->bm->getUsersByRoles([1, 14]);
        foreach ($adminUsers as $admin) {
            $this->Notification_model->add_amc_notification($admin->userId, "AMC updated: {$franchiseName} (AMC ID: {$amcId}, Status: {$statusAmc})", $amcId);
        }

        // Email
        $subject = "Alert - eduMETA THE i-SCHOOL AMC Updated";
        $message = "An AMC has been updated. Franchise: {$franchiseName}, AMC ID: {$amcId}, Status: {$statusAmc}. Please visit the portal.";
        $headers = "From: Edumeta Team <noreply@theischool.com>\r\n";
        $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        mail('dev.edumeta@gmail.com', $subject, $message, $headers);

        $this->session->set_flashdata('success', 'AMC updated successfully');
    } else {
        $this->session->set_flashdata('error', 'AMC updation failed');
    }

    redirect('amc/amcListing');
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