<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Dmfranchse (DmfranchseController)
 * Dmfranchse Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 03 June 2024
 */
class Dmfranchseho extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Dmfranchseho_model', 'cf');
        $this->load->model('Branches_model', 'bm');
         $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Dmfranchse';
        $this->load->library("pagination");
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('dmfranchseho/dmfranchsehoListing');
    }
    
    /**
     * This function is used to load the Dmfranchise list
     */
   
    public function dmfranchsehoListing() {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
  
         $franchiseFilter = $this->input->get('franchiseNumber');
            if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
            $config = array();
            $config['base_url'] = base_url('dmfranchseho/dmfranchsehoListing');
            $config['per_page'] = 10; 
            $config['uri_segment'] = 3;
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

            if ($userRole == '14' || $userRole == '1' || $userRole == '18'|| $userRole == '20'|| $userRole == '28' || $userRole == '23' || $userRole == '21'|| $userRole == '29' || $userRole == '31') { // Admin
                if ($franchiseFilter) {
                    $config['total_rows'] = $this->cf->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                    $data['records'] = $this->cf->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->cf->getTotalTrainingRecordsCount();
                    
                    $data['records'] = $this->cf->getAllTrainingRecords($config['per_page'], $page);
                }
                 } else if ($userRole == '15' || $userRole == '13') { // Specific roles
                    $config['total_rows'] = $this->cf->getTotalTrainingRecordsCountByRole($userId);
                    $data['records'] = $this->cf->getTrainingRecordsByRole($userId, $config['per_page'], $page);
                    
                } else { 
                        $franchiseNumber = $this->cf->getFranchiseNumberByUserId($userId);
                        if ($franchiseNumber) {
                            if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                                $config['total_rows'] = $this->cf->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->cf->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                            } else {
                                $config['total_rows'] = $this->cf->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->cf->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                            }
                        } else {
                            $data['records'] = []; // Handle the case where franchise number is not found
                        }
                    }

                        // Initialize pagination
                  $serial_no = $page + 1;
                    $this->pagination->initialize($config);
                    $data["links"] = $this->pagination->create_links();
                    $data["start"] = $page + 1;
                    $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
                    $data["total_records"] = $config["total_rows"];
                    $data['pagination'] = $this->pagination->create_links();
                    $data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
                    $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
                    $data["serial_no"] = $serial_no;
                $this->loadViews("dmfranchseho/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Campaign';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("dmfranchseho/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
  function addNewDmfranchseho()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('dmfranchseTitle', 'Campaign Title', 'trim|required|max_length[256]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required|numeric|greater_than_equal_to[0]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $dmfranchseTitle = $this->security->xss_clean($this->input->post('dmfranchseTitle'));
            $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
            $doneBy = $this->security->xss_clean($this->input->post('doneBy'));
            $numOfLeads = $this->security->xss_clean($this->input->post('numOfLeads'));
            $dateOfrequest = $this->security->xss_clean($this->input->post('dateOfrequest'));
            $CampaStartdate = $this->security->xss_clean($this->input->post('CampaStartdate'));
            $CampaEnddate = $this->security->xss_clean($this->input->post('CampaEnddate'));
            $platform = $this->security->xss_clean($this->input->post('platform'));
            $description = $this->security->xss_clean($this->input->post('description'));
            $amount = $this->security->xss_clean($this->input->post('amount'));

            // File upload handling for Attachment (file)
            $s3_file_link = [];
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['file']['tmp_name'];
                $fileName = time() . '-' . $_FILES['file']['name'];
                $destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

                if (move_uploaded_file($fileTmpPath, $destination)) {
                    $storeFolder = 'attachments';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();

                    if (!empty($result_arr['ObjectURL'])) {
                        $s3_file_link[] = $result_arr['ObjectURL'];
                    } else {
                        log_message('error', 'S3 upload failed for file: ' . $fileName);
                    }
                } else {
                    log_message('error', 'Failed to move uploaded file: ' . $fileName);
                }
            } else {
                log_message('error', 'File upload error: ' . ($_FILES['file']['error'] ?? 'No file uploaded'));
            }
            $s3files = implode(',', $s3_file_link);

            // File upload handling for Receipt Attachment (file2)
            $s3_file_link2 = '';
            if (isset($_FILES['file2']) && $_FILES['file2']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['file2']['tmp_name'];
                $fileName2 = time() . '-' . $_FILES['file2']['name'];
                $destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName2;

                if (move_uploaded_file($fileTmpPath, $destination)) {
                    $storeFolder = 'attachments';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();

                    if (!empty($result_arr['ObjectURL'])) {
                        $s3_file_link2 = $result_arr['ObjectURL'];
                    } else {
                        log_message('error', 'S3 upload failed for file: ' . $fileName2);
                    }
                } else {
                    log_message('error', 'Failed to move uploaded file: ' . $fileName2);
                }
            } else {
                if (isset($_FILES['file2'])) {
                    log_message('error', 'File upload error: ' . $_FILES['file2']['error']);
                } else {
                    log_message('info', 'No file uploaded for file2.');
                }
            }

            // Prepare data for insertion
            $dmfranchisehoInfo = array(
                'dmfranchseTitle' => $dmfranchseTitle,
                'doneBy' => $doneBy,
                'numOfLeads' => $numOfLeads,
                'dateOfrequest' => $dateOfrequest,
                'CampaStartdate' => $CampaStartdate,
                'CampaEnddate' => $CampaEnddate,
                'platform' => $platform,
                'description' => $description,
                'amount' => $amount,
                'dmattachmentS3file' => $s3files,
                'dmreceiptattachmentS3file' => $s3_file_link2,
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->cf->addNewDmfranchseho($dmfranchisehoInfo);

            if ($result > 0) {

                 $this->load->model('Notification_model', 'nm');

                // Send notifications to users with roleId 19, 14, 25
                $notificationMessage = "<strong>Digital Marketing HO Confirmation:</strong> New Digital Marketing HO confirmation";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [1, 14, 25, 18,15])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $notificationResult = $this->nm->add_Dmfranchseho_notification($result, $notificationMessage, $userId);
                        if (!$notificationResult) {
                            log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                        }
                    }
                }


                if (!empty($franchiseNumberArray)) {
                    foreach ($franchiseNumberArray as $franchiseNumber) {
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                        if (!empty($branchDetail)) {
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL Assign New Digital Marketing Franchise Campaign";
                            $message = 'Dear ' . $branchDetail->applicantName . ', ';
                            $message .= 'You have been assigned a new campaign. BY- ' . $this->session->userdata("name") . ' ';
                            $message .= 'Please visit the portal.';
                            $headers = "From: Edumeta Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                            mail($to, $subject, $message, $headers);
                        }
                    }
                }
                $this->session->set_flashdata('success', 'New Digital Marketing Franchise Campaign created successfully');
            } else {
                $this->session->set_flashdata('error', 'Campaign creation failed');
            }

            redirect('dmfranchseho/dmfranchsehoListing');
        }
    }
}
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($dmfranchsehoId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($dmfranchsehoId == null)
            {
                redirect('dmfranchseho/dmfranchsehoListing');
            }
            
            $data['dmfranchsehoInfo'] = $this->cf->getDmfranchsehoInfo($dmfranchsehoId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Digital Marketing Franchise';
            
            $this->loadViews("dmfranchseho/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
  function editDmfranchseho()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        
        $dmfranchsehoId = $this->input->post('dmfranchsehoId');
        
        $this->form_validation->set_rules('dmfranchseTitle', 'Campaign Title', 'trim|required|max_length[256]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required|numeric|greater_than_equal_to[0]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->edit($dmfranchsehoId);
        } else {
            $dmfranchseTitle = $this->security->xss_clean($this->input->post('dmfranchseTitle'));
            $description = $this->security->xss_clean($this->input->post('description'));
            $doneBy = $this->security->xss_clean($this->input->post('doneBy'));
            $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
            $numOfLeads = $this->security->xss_clean($this->input->post('numOfLeads'));
            $dateOfrequest = $this->security->xss_clean($this->input->post('dateOfrequest'));
            $CampaStartdate = $this->security->xss_clean($this->input->post('CampaStartdate'));
            $CampaEnddate = $this->security->xss_clean($this->input->post('CampaEnddate'));
            $platform = $this->security->xss_clean($this->input->post('platform'));
            $amount = $this->security->xss_clean($this->input->post('amount'));

            $s3_file_link = [];
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                rename($_FILES["file"]["tmp_name"], $destination);
                $storeFolder = 'attachments';

                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();
                if (!empty($result_arr['ObjectURL'])) {
                    $s3_file_link[] = $result_arr['ObjectURL'];
                } else {
                    $s3_file_link[] = '';
                }
            }
            $s3files = implode(',', $s3_file_link);

            $dmfranchisehoInfo = array(
                'dmfranchseTitle' => $dmfranchseTitle,
                'doneBy' => $doneBy,
                'brspFranchiseAssigned' => $brspFranchiseAssigned,
                'numOfLeads' => $numOfLeads,
                'dateOfrequest' => $dateOfrequest,
                'CampaStartdate' => $CampaStartdate,
                'CampaEnddate' => $CampaEnddate,
                'platform' => $platform,
                'description' => $description,
                'amount' => $amount,
                'dmattachmentS3file' => $s3files,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->cf->editDmfranchseho($dmfranchisehoInfo, $dmfranchsehoId);
            
            if ($result == true) {
                    $this->load->model('Notification_model', 'nm');

                // Send notifications to users with roleId 19, 14, 25
                $notificationMessage = "<strong>Digital Marketing HO Confirmation:</strong>Update Digital Marketing HO confirmation";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [1, 14, 25, 18,15])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $notificationResult = $this->nm->add_Dmfranchseho_notification($result, $notificationMessage, $userId);
                        if (!$notificationResult) {
                            log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                        }
                    }
                }
                $this->session->set_flashdata('success', 'Campaign updated successfully');

                if (!empty($franchiseNumberArray)) {
                    foreach ($franchiseNumberArray as $franchiseNumber) {
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                        if (!empty($branchDetail)) {
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL Digital Marketing Franchise Campaign Updated";
                            $message = "Dear {$branchDetail->applicantName},\n\n";
                            $message .= "A digital marketing franchise campaign (ID: {$dmfranchsehoId}) has been updated by " . $this->session->userdata("name") . ".\n";
                            $message .= "Please visit the portal for more details.\n";
                            $headers = "From: Edumeta Team<noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com, sourabh.edumta@gmail.com";
                            mail($to, $subject, $message, $headers);
                        }
                    }
                }
            } else {
                $this->session->set_flashdata('error', 'Campaign updation failed');
            }
            
            redirect('dmfranchseho/dmfranchsehoListing');
        }
    }
}


    /** Code editor */
      public function upload() {
        if (isset($_FILES['upload'])) {
            $file = $_FILES['upload'];
            $fileName = time() . '_' . $file['name'];
            $uploadPath = 'uploads/';

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

    // Fetch the users based on the franchise number
    $users = $this->cf->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

    // Generate HTML options for the response
    $options = '<option value="0">Select Role</option>';
    foreach ($users as $user) {
        $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
    }

    echo $options; // Output the options as HTML
}
public function download_image($filename)
{
    $this->load->helper('download');

    // Ensure the filename is clean
    $filename = basename(urldecode($filename));

    $s3_url = 'https://support-smsfiles.s3.ap-south-1.amazonaws.com/attachments/' . $filename;

    // Debugging: Print the actual filename
    echo "Downloading file: " . htmlspecialchars($filename) . "<br>";

    // Get file content
    $file_data = @file_get_contents($s3_url);
    
    if ($file_data === false) {
        die("Error: File not found or permission denied.");
    }

    // Set headers for proper download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($file_data));

    // Output the file
    echo $file_data;
    exit;
}



}

?>