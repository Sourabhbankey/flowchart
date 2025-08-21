<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Freegift (TaskController)
 * Freegift Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 20 Jun 2024
 */
class Freegift extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Freegift_model', 'frgift');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Freegift';
        $this->load->library("pagination");
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('freegift/freegiftListing');
    }
    
    /**
     * This function is used to load the Freegift list
     */

public function freegiftListing() {
     $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
  
         $franchiseFilter = $this->input->get('franchiseNumber');
            if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
            $config = array();
            $config['base_url'] = base_url('freegift/freegiftListing');
            $config['per_page'] = 10; 
            $config['uri_segment'] = 3;
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

            if ($userRole == '14' || $userRole == '1'|| $userRole == '23') { // Admin
                if ($franchiseFilter) {
                    $config['total_rows'] = $this->frgift->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                    $data['records'] = $this->frgift->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->frgift->getTotalTrainingRecordsCount();
                    
                    $data['records'] = $this->frgift->getAllTrainingRecords($config['per_page'], $page);
                }
                 } else if ($userRole == '15') { // Specific roles
                    $config['total_rows'] = $this->frgift->getTotalTrainingRecordsCountByRole($userId);
                    $data['records'] = $this->frgift->getTrainingRecordsByRole($userId, $config['per_page'], $page);
                    
                } else { 
                        $franchiseNumber = $this->frgift->getFranchiseNumberByUserId($userId);
                        if ($franchiseNumber) {
                            if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                                $config['total_rows'] = $this->frgift->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->frgift->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                            } else {
                                $config['total_rows'] = $this->frgift->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->frgift->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
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
    $this->loadViews("freegift/list", $this->global, $data, NULL);
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
            $data['users'] = $this->frgift->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Freegift';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("freegift/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewFreegift()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('giftTitle','Gift Title','trim|required|max_length[256]');
         //   $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {     $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $giftTitle = $this->security->xss_clean($this->input->post('giftTitle'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                /*-new-added-field-*/
                $approvedBy = $this->security->xss_clean($this->input->post('approvedBy'));
                $dateOfDespatch = $this->security->xss_clean($this->input->post('dateOfDespatch'));
                $modeOfDespatch = $this->security->xss_clean($this->input->post('modeOfDespatch'));
                $dateDelevery = $this->security->xss_clean($this->input->post('dateDelevery'));
                $delStatus = $this->security->xss_clean($this->input->post('delStatus'));
                /*-ENd-added-field-*/
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumbers = implode(',',$franchiseNumberArray);
         if (isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
    $dir = sys_get_temp_dir(); // Use system temporary directory
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';

        // Upload to S3
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();

        if (!empty($result_arr['ObjectURL'])) {
            $s3_file_link[] = $result_arr['ObjectURL'];
        } else {
            $s3_file_link[] = '';
        }

        // Combine S3 file links
        $s3files = implode(',', $s3_file_link);
    } else {
        $this->session->set_flashdata('error', 'File move failed. Please try again.');
    }
} else {
    $this->session->set_flashdata('error', 'No file uploaded or file upload error.');
}

                $freegiftInfo = array('brspFranchiseAssigned'=>$brspFranchiseAssigned,'giftTitle'=>$giftTitle, 'approvedBy'=>$approvedBy, 'dateOfDespatch'=>$dateOfDespatch, 'modeOfDespatch'=>$modeOfDespatch, 'dateDelevery'=>$dateDelevery, 'franchiseNumber'=>$franchiseNumbers, 'delStatus'=>$delStatus, 'snapshotDespS3File'=>$s3files, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->frgift->addNewFreegift($freegiftInfo);

                if($result > 0) {
                       $this->load->model('Notification_model', 'nm');
    
    // ✅ Get all users (not just admin)
    $allUsers = $this->nm->get_all_users(); 

    foreach ($allUsers as $user) {
        $message = "<strong>Freegift:</strong>New Freegift";
        $this->nm->add_freegift_notification($result, $message, $user['userId']); 
    }
                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Freegift";
                                $message = 'Dear '.$branchDetail->applicantName.' ';
                                //$message = ' '.$description.' ';
                                $message .= 'You have been assigned a new freegift. BY- '.$this->session->userdata("name").' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$description.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to,$subject,$message,$headers);
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Freegift created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Freegift creation failed');
                }
                
                redirect('freegift/freegiftListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($freegiftId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($freegiftId == null)
            {
                redirect('freegift/freegiftListing');
            }
            
            $data['freegiftInfo'] = $this->frgift->getFreegiftInfo($freegiftId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Freegift';
            
            $this->loadViews("freegift/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
   function editFreegift()
{
    if(!$this->hasUpdateAccess())
    {
        $this->loadThis();
    }
    else
    {
        $this->load->library('form_validation');
        $this->load->model('Notification_model', 'nm'); // Load the notification model
        
        $freegiftId = $this->input->post('freegiftId');
        
        $this->form_validation->set_rules('giftTitle','Freegift Title','trim|required|max_length[256]');
        $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($freegiftId);
        }
        else
        {
            $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
            $giftTitle = $this->security->xss_clean($this->input->post('giftTitle'));
            $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
            $description = $this->security->xss_clean($this->input->post('description'));
            $approvedBy = $this->security->xss_clean($this->input->post('approvedBy'));
            $dateOfDespatch = $this->security->xss_clean($this->input->post('dateOfDespatch'));
            $modeOfDespatch = $this->security->xss_clean($this->input->post('modeOfDespatch'));
            $dateDelevery = $this->security->xss_clean($this->input->post('dateDelevery'));
            $delStatus = $this->security->xss_clean($this->input->post('delStatus'));

            // Handle file upload if a new file is provided
            $s3files = $this->input->post('existing_snapshotDespS3File');
            if (isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
                $dir = sys_get_temp_dir();
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $destination)) {
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3files = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : $s3files;
                }
            }

            $franchiseNumbers = implode(',', (array)$franchiseNumberArray);
            $freegiftInfo = array(
                'brspFranchiseAssigned' => $brspFranchiseAssigned,
                'giftTitle' => $giftTitle,
                'franchiseNumber' => $franchiseNumbers,
                'approvedBy' => $approvedBy,
                'dateOfDespatch' => $dateOfDespatch,
                'modeOfDespatch' => $modeOfDespatch,
                'dateDelevery' => $dateDelevery,
                'delStatus' => $delStatus,
                'snapshotDespS3File' => $s3files,
                'description' => $description,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );
            
            $result = $this->frgift->editFreegift($freegiftInfo, $freegiftId);
            
            if($result == true)
            {
                // Notify the user who updated the freegift
                $message = "You updated freegift: {$giftTitle} (Freegift ID: {$freegiftId})";
                $notificationResult = $this->nm->add_freegift_notification($freegiftId, $message, $this->vendorId);
                if (!$notificationResult) {
                    log_message('error', "Failed to add notification for user {$this->vendorId} on freegift ID {$freegiftId}");
                }

            

                // Notify admins (role IDs 1, 14, 23)
                $adminUsers = $this->bm->getUsersByRoles([1, 14, 23]);
                if (empty($adminUsers)) {
                    log_message('error', "No admins found for role IDs 1, 14, 23 on freegift ID {$freegiftId}");
                }
                foreach ($adminUsers as $adminUser) {
                    $notificationMessage = "Freegift updated: {$giftTitle} (Freegift ID: {$freegiftId})";
                    $notificationResult = $this->nm->add_freegift_notification($freegiftId, $notificationMessage, $adminUser->userId);
                    if (!$notificationResult) {
                        log_message('error', "Failed to add notification for admin {$adminUser->userId} on freegift ID {$freegiftId}");
                    }
                }

                // Email notification to admin
                $adminEmail = 'dev.edumeta@gmail.com'; // Replace with actual admin email
                $adminSubject = "Alert - eduMETA THE i-SCHOOL Freegift Updated";
                $adminMessage = "A freegift has been updated. ";
                $adminMessage .= "Gift Title: {$giftTitle}, Freegift ID: {$freegiftId}. ";
                $adminMessage .= "Please visit the portal for details.";
                $adminHeaders = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                if (!mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders)) {
                    log_message('error', "Failed to send email to {$adminEmail} for freegift ID {$freegiftId}");
                }

                $this->session->set_flashdata('success', 'Freegift updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Freegift updation failed');
            }
            
            redirect('freegift/freegiftListing');
        }
    }
}
     public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Fetch the users based on the franchise number
    $users = $this->frgift->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

    // Generate HTML options for the response
    $options = '<option value="0">Select Role</option>';
    foreach ($users as $user) {
        $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
    }

    echo $options; // Output the options as HTML
}
}

?>