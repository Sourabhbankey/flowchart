<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Onboardingforms (TaskController)
 * Onboardingforms Class to control task related operations.
 * @author : Ashish Singh
 * @version : 1.5
 * @since : 31 May 2024
 */
class Onboardingforms extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Onboardingforms_model', 'ofrm');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->load->library('pagination');
        $this->module = 'Onboardingforms';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('onboardingforms/onboardingformsListing');
    }
    
    /**
     * This function is used to load the Pdc list
     */
 /* 
public function onboardingformsListing() {
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');
    $data['vendorId']   = $this->vendorId;
    $franchiseFilter = $this->input->get('franchiseNumber');
    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = '';
    }
    $config = array();
    $config["base_url"] = base_url() . "onboardingforms/onboardingformsListing";
    $config["per_page"] = 10;
    $config["uri_segment"] = 3;

    if ($userRole == '14' || $userRole == '1' || $userRole == '24') {
        $config["total_rows"] = $this->ofrm->get_count($franchiseFilter);
    } else {
        $franchiseNumber = $this->ofrm->getFranchiseNumberByUserId($userId);
        if ($franchiseNumber) {
            $config["total_rows"] = $this->ofrm->get_count_by_franchise($franchiseNumber, $franchiseFilter);
        } else {
            $config["total_rows"] = 0;
        }
    }
    $this->pagination->initialize($config);
    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

    if ($userRole == '14' || $userRole == '1' || $userRole == '24' || $userRole == '15') {
        $data["records"] = $this->ofrm->get_data($config["per_page"], $page, $franchiseFilter);
    } else {
        if ($franchiseNumber) {
            $data["records"] = $this->ofrm->get_data_by_franchise($franchiseNumber, $config["per_page"], $page, $franchiseFilter);
        } else {
            $data["records"] = [];
        }
    }
     $serial_no = $page + 1;
    $data["links"] = $this->pagination->create_links();
    $data["start"] = $page + 1;
    $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
    $data["total_records"] = $config["total_rows"];
    $data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
    $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
     $data["serial_no"] = $serial_no;
    //print_r($data["records"]);exit;
    $this->loadViews("onboardingforms/list", $this->global, $data, NULL);
}*/


public function onboardingformsListing() {
     $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
  
         $franchiseFilter = $this->input->get('franchiseNumber');
            if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
            $config = array();
            $config['base_url'] = base_url('onboardingforms/onboardingformsListing');
            $config['per_page'] = 10; 
            $config['uri_segment'] = 3;
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

            if ($userRole == '14' || $userRole == '1'|| $userRole == '24') { // Admin
                if ($franchiseFilter) {
                    $config['total_rows'] = $this->ofrm->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                    $data['records'] = $this->ofrm->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->ofrm->getTotalTrainingRecordsCount();
                    
                    $data['records'] = $this->ofrm->getAllTrainingRecords($config['per_page'], $page);
                }
                 } else if ($userRole == '15') { // Specific roles
                    $config['total_rows'] = $this->ofrm->getTotalTrainingRecordsCountByRole($userId);
                    $data['records'] = $this->ofrm->getTrainingRecordsByRole($userId, $config['per_page'], $page);
                    
                } else { 
                        $franchiseNumber = $this->ofrm->getFranchiseNumberByUserId($userId);
                        if ($franchiseNumber) {
                            if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                                $config['total_rows'] = $this->ofrm->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->ofrm->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                            } else {
                                $config['total_rows'] = $this->ofrm->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->ofrm->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
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
    $this->loadViews("onboardingforms/list", $this->global, $data, NULL);
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
              $data['users'] = $this->ofrm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Onboardingform';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("onboardingforms/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewOnboardingforms()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            //$this->form_validation->set_rules('frenrollform','Onboarding Title','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Remark','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {   $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                /*-new-added-field-*/
                $frenrollform = $this->security->xss_clean($this->input->post('frenrollform'));
                $frenrollStatus = $this->security->xss_clean($this->input->post('frenrollStatus'));
                $frFirstform = $this->security->xss_clean($this->input->post('frFirstform'));
                $frFirstformStatus = $this->security->xss_clean($this->input->post('frFirstformStatus'));
                //$attendees = $this->security->xss_clean($this->input->post('attendees'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumbers = implode(',',$franchiseNumberArray);
                $onboardingformsInfo = array('brspFranchiseAssigned'=>$brspFranchiseAssigned,'frenrollform'=>$frenrollform, 'frenrollStatus'=>$frenrollStatus, 'frFirstform'=>$frFirstform, 'frFirstformStatus'=>$frFirstformStatus, 'franchiseNumber'=>$franchiseNumbers, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->ofrm->addNewOnboardingforms($onboardingformsInfo);

                if($result > 0) {
                     $this->load->model('Notification_model');

                // ✅ Send Notification to Assigned Franchise User
                if (!empty($brspFranchiseAssigned)) {
                    $notificationMessage = "Onboard : A new onboard  has been assigned.";
                    $this->Notification_model->add_onboard_notification($brspFranchiseAssigned, $notificationMessage, $result);
                }
                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Onboarding Form";
                                $message = 'Dear '.$branchDetail->applicantName.' ';
                                //$message = ' '.$description.' ';
                                $message .= 'You have been assigned a new Onboarding Form record. BY- '.$this->session->userdata("name").' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$description.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to,$subject,$message,$headers);
                                       // ✅ Get User ID mapped with this Franchise Number
                            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                            if (!empty($franchiseUser)) {
                                $notificationMessage = "Onboard : A new onboard  has been assigned.";
                                $this->Notification_model->add_onboard_notification($franchiseUser->userId, $notificationMessage, $result);
                            }
                            // ✅ Notify Admins (roleId = 1, 14)
                $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $this->Notification_model->add_onboard_notification($adminUser->userId, "Onboard : A new onboard  has been assigned.", $result);
                    }
                }
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Onboarding Form created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Onboarding Form creation failed');
                }
                
                redirect('onboardingforms/onboardingformsListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($onboardingformsId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($onboardingformsId == null)
            {
                redirect('onboardingforms/onboardingformsListing');
            }
            
            $data['onboardingformsInfo'] = $this->ofrm->getonboardingformsInfo($onboardingformsId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Onboardingforms';
            
            $this->loadViews("onboardingforms/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editOnboardingforms()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $onboardingformsId = $this->input->post('onboardingformsId');
            
            //$this->form_validation->set_rules('frenrollform','Onboarding Title','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Remark','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($onboardingformsId);
            }
            else
            {
                $description = $this->security->xss_clean($this->input->post('description'));
                /*-new-added-field-*/
                $frenrollform = $this->security->xss_clean($this->input->post('frenrollform'));
                $frenrollStatus = $this->security->xss_clean($this->input->post('frenrollStatus'));
                $frFirstform = $this->security->xss_clean($this->input->post('frFirstform'));
                $frFirstformStatus = $this->security->xss_clean($this->input->post('frFirstformStatus'));
                $onboardingformsInfo = array('frenrollform'=>$frenrollform, 'frenrollStatus'=>$frenrollStatus, 'frFirstform'=>$frFirstform, 'frFirstformStatus'=>$frFirstformStatus, 'description'=>$description, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                $result = $this->ofrm->editOnboardingforms($onboardingformsInfo, $onboardingformsId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Form updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Form updation failed');
                }
                
                redirect('onboardingforms/onboardingformsListing');
            }
        }
    }

public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Fetch the users based on the franchise number
    $users = $this->ofrm->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

    // Generate HTML options for the response
    $options = '<option value="0">Select Role</option>';
    foreach ($users as $user) {
        $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
    }

    echo $options; // Output the options as HTML
}

}

?>