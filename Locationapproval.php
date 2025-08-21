<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Locationapproval (SuppoLocationapprovalrtController)
 * Locationapproval Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 04 June 2024
 */
class Locationapproval extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Locationapproval_model', 'la');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Locationapproval';
        $this->load->library('pagination');
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('locationapproval/locationapprovalListing');
    }
    
    /**
     * This function is used to load the Support list
     */
  

     public function locationapprovalListing() {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
        $franchiseFilter = $this->input->get('franchiseNumber');
    
        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
    
        $config = array();
        $config['base_url'] = base_url('locationapproval/locationapprovalListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
    
        if (in_array($userRole, [1, 14, 18, 2, 24,13])) { // Admin roles
            if ($franchiseFilter) {
                $config['total_rows'] = $this->la->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                $data['records'] = $this->la->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->la->getTotalTrainingRecordsCount();
                $data['records'] = $this->la->getAllTrainingRecords($config['per_page'], $page);
            }
        } elseif (in_array($userRole, [13, 15])) { // Specific roles
            $config['total_rows'] = $this->la->getTotalTrainingRecordsCountByRole($userId);
            $data['records'] = $this->la->getTrainingRecordsByRole($userId, $config['per_page'], $page);
        } elseif (in_array($userRole, [24, 25])) { // Franchise roles (including role 24)
            $franchiseNumber = $this->la->getFranchiseNumberByUserId($userId);
            if ($franchiseNumber) {
                if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                    $config['total_rows'] = $this->la->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->la->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->la->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->la->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                }
            } else {
                $config['total_rows'] = 0; // Set to 0 if no franchise number is found
                $data['records'] = [];
                $this->session->set_flashdata('error', 'No franchise number associated with your account.');
            }
        } else {
            // Handle unexpected roles
            $config['total_rows'] = 0;
            $data['records'] = [];
            $this->session->set_flashdata('error', 'Access denied: Invalid role.');
        }
    
        // Initialize pagination
        $serial_no = $page + 1;
        $this->pagination->initialize($config);
        $data["links"] = $this->pagination->create_links();
        $data["start"] = $page + 1;
        $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];
        $data['pagination'] = $this->pagination->create_links();
        $data["franchiseFilter"] = $franchiseFilter;
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $data["serial_no"] = $serial_no;
    
        $this->loadViews("locationapproval/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Locationapproval';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("locationapproval/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewLocationapproval()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('locationTitle','City Name','trim|required|max_length[256]');
            //$this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {   $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $locationTitle = $this->security->xss_clean($this->input->post('locationTitle'));
                $gmapLink = $this->security->xss_clean($this->input->post('gmapLink'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                /*-new-added-field-*/
                $locAddress = $this->security->xss_clean($this->input->post('locAddress'));
                $nearestBranch = $this->security->xss_clean($this->input->post('nearestBranch'));
                $nearestBranchDistance = $this->security->xss_clean($this->input->post('nearestBranchDistance'));
                $locApprovalStatus = $this->security->xss_clean($this->input->post('locApprovalStatus'));
                /*-ENd-added-field-*/
                $description = $this->security->xss_clean($this->input->post('description'));
              /*  $franchiseNumbers = implode(',',$franchiseNumberArray);
                $nearestBranchesString = implode(',', $nearestBranch);*/
                $franchiseNumbers = is_array($franchiseNumberArray) ? implode(',', $franchiseNumberArray) : '';
$nearestBranchesString = is_array($nearestBranch) ? implode(',', $nearestBranch) : '';
                $locationGeolocation = $this->security->xss_clean($this->input->post('locationGeolocation'));
if (isset($_FILES['locationImages']['name']) && !empty($_FILES['locationImages']['name'][0])) {
    $s3_file_links = [];

    $countFiles = count($_FILES['locationImages']['name']);
    for ($i = 0; $i < $countFiles; $i++) {
        $_FILES['file_temp']['name']     = $_FILES['locationImages']['name'][$i];
        $_FILES['file_temp']['type']     = $_FILES['locationImages']['type'][$i];
        $_FILES['file_temp']['tmp_name'] = $_FILES['locationImages']['tmp_name'][$i];
        $_FILES['file_temp']['error']    = $_FILES['locationImages']['error'][$i];
        $_FILES['file_temp']['size']     = $_FILES['locationImages']['size'][$i];

        $dir = dirname($_FILES['file_temp']['tmp_name']);
        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES['file_temp']['name'];

        if (rename($_FILES['file_temp']['tmp_name'], $destination)) {
            $storeFolder = 'attachements';
            $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
            $result_arr = $s3Result->toArray();
            $s3_file_links[] = $result_arr['ObjectURL'] ?? '';
        } else {
            $s3_file_links[] = '';
        }
    }

    $locationImagesS3 = implode(',', $s3_file_links);
} else {
    $locationImagesS3 = '';
}


            if (isset($_FILES["file2"]["tmp_name"]) && !empty($_FILES["file2"]["tmp_name"])) {
                $dir = dirname($_FILES["file2"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file2"]["name"];

                if (rename($_FILES["file2"]["tmp_name"], $destination)) {
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                        $s3_file_link2[] = $result_arr['ObjectURL'];
                    } else {
                        $s3_file_link2[] = '';
                    }
                } else {
                    $s3_file_link2[] = '';
                }
            } else {
                $s3_file_link2[] = '';
            }

            $s3files2 = implode(',', $s3_file_link2);



                $locationapprovalInfo = array('brspFranchiseAssigned'=>$brspFranchiseAssigned,'locationTitle'=>$locationTitle, 'gmapLink'=>$gmapLink, 'locAddress'=>$locAddress, 'nearestBranch'=>$nearestBranchesString, 'nearestBranchDistance'=>$nearestBranchDistance, 'locApprovalStatus'=>$locApprovalStatus, 'franchiseNumber'=>$franchiseNumbers, 'description'=>$description, 
                    'createdBy'=>$this->vendorId, 
                   'locationImages' => $locationImagesS3,
                    'locationVideos' => $s3files2,
                    'locationGeolocation' => $locationGeolocation,
                    'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->la->addNewLocationapproval($locationapprovalInfo);
//print_r($locationapprovalInfo);exit;
                if($result > 0) {
                     $this->load->model('Notification_model');

                // ✅ Send Notification to Assigned Franchise User
                if (!empty($brspFranchiseAssigned)) {
                    $notificationMessage = "Location Approval : A new location has been approved.";
                    $this->Notification_model->add_locationapproval_notification($brspFranchiseAssigned, $notificationMessage, $result);
                }

                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Location Approval Status";
                                $message = 'Dear '.$branchDetail->applicantName.' ';
                                //$message = ' '.$description.' ';
                                $message .= 'You have been assigned a new meeting. BY- '.$this->session->userdata("name").' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$description.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to,$subject,$message,$headers);
                                 // ✅ Get User ID mapped with this Franchise Number
                            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                            if (!empty($franchiseUser)) {
                                $notificationMessage = "Location Approval : A new location has been approved.";
                                $this->Notification_model->add_locationapproval_notification($franchiseUser->userId, $notificationMessage, $result);
                            }
                            // ✅ Notify Admins (roleId = 1, 14)
                $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $this->Notification_model->add_locationapproval_notification($adminUser->userId, "Location Approval : A new location has been approved.", $result);
                    }
                }
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Location Approval created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Location Approval creation failed');
                }
                
                redirect('locationapproval/locationapprovalListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($locationApprovalId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($locationApprovalId == null)
            {
                redirect('locationapproval/locationapprovalListing');
            }
            
            $data['locationapprovalInfo'] = $this->la->getlocationapprovalInfo($locationApprovalId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Meeting : Edit Location';
            
            $this->loadViews("locationapproval/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editLocationapproval()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $locationApprovalId = $this->input->post('locationApprovalId');
            
            $this->form_validation->set_rules('locationTitle','City Name','trim|required|max_length[256]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($locationApprovalId);
            }
            else
            {
                $locationTitle = $this->security->xss_clean($this->input->post('locationTitle'));
                $gmapLink = $this->security->xss_clean($this->input->post('gmapLink'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $locAddress = $this->security->xss_clean($this->input->post('locAddress'));
                $nearestBranch = $this->security->xss_clean($this->input->post('nearestBranch'));
                $nearestBranchDistance = $this->security->xss_clean($this->input->post('nearestBranchDistance'));
                $locApprovalStatus = $this->security->xss_clean($this->input->post('locApprovalStatus'));
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseNumbers = is_array($franchiseNumberArray) ? implode(',', $franchiseNumberArray) : '';
                $nearestBranchesString = is_array($nearestBranch) ? implode(',', $nearestBranch) : '';

                $locationapprovalInfo = array(
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'locationTitle' => $locationTitle,
                    'gmapLink' => $gmapLink,
                    'locAddress' => $locAddress,
                    'nearestBranch' => $nearestBranchesString,
                    'nearestBranchDistance' => $nearestBranchDistance,
                    'locApprovalStatus' => $locApprovalStatus,
                    'franchiseNumber' => $franchiseNumbers,
                    'description' => $description,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->la->editLocationapproval($locationapprovalInfo, $locationApprovalId);
                
                if($result == true)
                {
                    if (!empty($brspFranchiseAssigned)) {
                        $notificationMessage = "Location Approval: Location '$locationTitle' has been updated.";
                        $this->nm->add_locationapproval_notification($brspFranchiseAssigned, $notificationMessage, $locationApprovalId);
                    }

                    if (!empty($franchiseNumberArray)) {
                        foreach ($franchiseNumberArray as $franchiseNumber) {
                            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                            if (!empty($franchiseUser)) {
                                $notificationMessage = "Location Approval: Location '$locationTitle' has been updated.";
                                $this->nm->add_locationapproval_notification($franchiseUser->userId, $notificationMessage, $locationApprovalId);
                            }
                        }
                    }

                    $adminUsers = $this->bm->getUsersByRoles([1, 14, 13]);
                    if (!empty($adminUsers)) {
                        foreach ($adminUsers as $adminUser) {
                            $notificationMessage = "Location Approval: Location '$locationTitle' has been updated by {$this->session->userdata('name')}.";
                            $this->nm->add_locationapproval_notification($adminUser->userId, $notificationMessage, $locationApprovalId);
                        }
                    }

                    $this->session->set_flashdata('success', 'Location Approval updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Location Approval updation failed');
                }
                
                redirect('locationapproval/locationapprovalListing');
            }
        }
    }
    public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Fetch the users based on the franchise number
    $users = $this->la->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

    // Generate HTML options for the response
    $options = '<option value="0">Select Role</option>';
    foreach ($users as $user) {
        $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
    }

    echo $options; // Output the options as HTML
}
}

?>