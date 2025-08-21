<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Admissiondetails (AdmissiondetailsController)
 * Admissiondetails Class to control Admissiondetails related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 14 June 2024
 */
class Admissiondetails extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admissiondetails_model', 'admdet');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
		   $this->load->library('pagination');
        $this->module = 'Admissiondetails';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('admissiondetails/admissiondetailsListing');
    }
    
    /**
     * This function is used to load the Admissiondetails list
     */
   /** function admissiondetailsListing()
    {
        if(!$this->hasListAccess())
        {
            $this->loadThis();
        }
        else
        {        
            $searchText = '';
            if(!empty($this->input->post('searchText'))) {
                $searchText = $this->security->xss_clean($this->input->post('searchText'));
            }
            $data['searchText'] = $searchText;
            
            $this->load->library('pagination');
            
            $count = $this->admdet->admissiondetailsListingCount($searchText);

			$returns = $this->paginationCompress ( "admissiondetailsListing/", $count, 500 );
            
            $data['records'] = $this->admdet->admissiondetailsListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'Admission enquiry : Admissiondetails';
            
            $this->loadViews("admissiondetails/list", $this->global, $data, NULL);
        }
    }*/
    
//new code 15 

public function admissiondetailsListing() {
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');

    // Get the franchise filter from the GET request
    $franchiseFilter = $this->input->get('franchiseNumber');
    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = '';
    }

    // Pagination configuration
    $config = array();
    $config['base_url'] = base_url('admissiondetails/admissiondetailsListing');
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;
    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

    if ($userRole == '14' || $userRole == '1' || $userRole == '20') { // Admin
        // Admin logic to fetch all records or filter by franchise
        if ($franchiseFilter) {
            $config['total_rows'] = $this->admdet->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
            $data['records'] = $this->admdet->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
        } else {
            $config['total_rows'] = $this->admdet->getTotalTrainingRecordsCount();
            $data['records'] = $this->admdet->getAllTrainingRecords($config['per_page'], $page);
        }
    } else if ($userRole == '15' || $userRole == '13') { 
     if ($franchiseFilter) {
         // Use franchise filter
         $config['total_rows'] = $this->admdet->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
         $data['records'] = $this->admdet->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
     } else {
         // Use role-based filter
         $config['total_rows'] = $this->admdet->getTotalTrainingRecordsCountByRole($userId);
         $data['records'] = $this->admdet->getTrainingRecordsByRole($userId, $config['per_page'], $page);
     }
 } else { 
        // Logic for other roles (not Admin, Role 15, or Role 13)
        $franchiseNumber = $this->admdet->getFranchiseNumberByUserId($userId);
        if ($franchiseNumber) {
            if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                $config['total_rows'] = $this->admdet->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                $data['records'] = $this->admdet->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->admdet->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                $data['records'] = $this->admdet->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
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

    // Load the view with the data
    $this->loadViews("admissiondetails/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Admissiondetails';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
              $data['users'] = $this->admdet->getUser();
            $this->loadViews("admissiondetails/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewAdmissiondetails()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('name','Student  Name','trim|required|max_length[256]');
            $this->form_validation->set_rules('remark','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {    $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $name = $this->security->xss_clean($this->input->post('name'));
               $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                /*-new-added-field-*/
                $enrollNum = $this->security->xss_clean($this->input->post('enrollNum'));
                $class = $this->security->xss_clean($this->input->post('class'));
                $dateOfAdmission = $this->security->xss_clean($this->input->post('dateOfAdmission'));
                $program = $this->security->xss_clean($this->input->post('program'));
                $birthday = $this->security->xss_clean($this->input->post('birthday'));
                $age = $this->security->xss_clean($this->input->post('age'));
                $gender = $this->security->xss_clean($this->input->post('gender'));
                $fathername = $this->security->xss_clean($this->input->post('fathername'));
                $fatheremail = $this->security->xss_clean($this->input->post('fatheremail'));
                $fatherMobile_no = $this->security->xss_clean($this->input->post('fatherMobile_no'));
                $mothername = $this->security->xss_clean($this->input->post('mothername'));
                $motheremail = $this->security->xss_clean($this->input->post('motheremail'));
                $motherMobile_no = $this->security->xss_clean($this->input->post('motherMobile_no'));
                $bloodGroup = $this->security->xss_clean($this->input->post('bloodGroup'));
                $motherTongue = $this->security->xss_clean($this->input->post('motherTongue'));
                $religion = $this->security->xss_clean($this->input->post('religion'));
                $caste = $this->security->xss_clean($this->input->post('caste'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $state = $this->security->xss_clean($this->input->post('state'));
                $address = $this->security->xss_clean($this->input->post('address'));
                $previousSchool = $this->security->xss_clean($this->input->post('previousSchool'));
                /*--Newfield--*/
                $totalFee = $this->security->xss_clean($this->input->post('totalFee'));
                /*-ENd-added-field-*/
                $remark = $this->security->xss_clean($this->input->post('remark'));
                $franchiseNumbers = implode(',',$franchiseNumberArray);
                $admissiondetailsInfo = array('brspFranchiseAssigned'=>$brspFranchiseAssigned,'name'=>$name, 'enrollNum'=>$enrollNum, 'class'=>$class, 'dateOfAdmission'=>$dateOfAdmission, 'program'=>$program, 'birthday'=>$birthday, 'age'=>$age, 'gender'=>$gender, 'fathername'=>$fathername, 'fatheremail'=>$fatheremail, 'fatherMobile_no'=>$fatherMobile_no, 'mothername'=>$mothername, 'motheremail'=>$motheremail, 'motherMobile_no'=>$motherMobile_no, 'bloodGroup'=>$bloodGroup, 'motherTongue'=>$motherTongue, 'religion'=>$religion, 'caste'=>$caste, 'city'=>$city, 'state'=>$state,  'totalFee'=>$totalFee, 'address'=>$address, 'previousSchool'=>$previousSchool, 'franchiseNumber'=>$franchiseNumbers, 'remark'=>$remark, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->admdet->addNewAdmissiondetails($admissiondetailsInfo);
//print_r($admissiondetailsInfo);exit;
                if($result > 0) {
                    $this->load->model('Notification_model');

                // ✅ Send Notification to Assigned Franchise User
                if (!empty($brspFranchiseAssigned)) {
                    $notificationMessage = "<strong>Admission</strong> :A new admission has been added.";
                    $this->Notification_model->add_admdet_notification($brspFranchiseAssigned, $notificationMessage, $result);
                }
                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Admission Details";
                                $message = 'Dear '.$branchDetail->applicantName.' ';
                                //$message = ' '.$remark.' ';
                                $message .= 'You have been assigned a new meeting. BY- '.$this->session->userdata("name").' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$remark.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to,$subject,$message,$headers);
                                          // ✅ Get User ID mapped with this Franchise Number
                            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                            if (!empty($franchiseUser)) {
                                $notificationMessage = "<strong>Admission</strong> : A new admission has been added.";
                                $this->Notification_model->add_admdet_notification($franchiseUser->userId, $notificationMessage, $result);
                            }
                            // ✅ Notify Admins (roleId = 1, 14)
                $adminUsers = $this->bm->getUsersByRoles([1, 14,20]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $this->Notification_model->add_admdet_notification($adminUser->userId, "<strong>Admission</strong> : A new admission has been added.", $result);
                    }
                }
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Admission details created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Admission details creation failed');
                }
                
                redirect('admissiondetails/admissiondetailsListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($admid = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($admid == null)
            {
                redirect('admissiondetails/admissiondetailsListing');
            }
            
            $data['admissiondetailsInfo'] = $this->admdet->getAdmissiondetailsInfo($admid);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Admission enquiry : Edit Admissiondetails';
            
            $this->loadViews("admissiondetails/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */

function editAdmissiondetails()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        $this->load->model('Notification_model'); // Load the notification model

        $admid = $this->input->post('admid');

        $this->form_validation->set_rules('name', 'Student Name', 'trim|required|max_length[256]');
        $this->form_validation->set_rules('remark', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($admid);
        } else {
            // Sanitize input data
            $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
            $name = $this->security->xss_clean($this->input->post('name'));
            $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
            $enrollNum = $this->security->xss_clean($this->input->post('enrollNum'));
            $class = $this->security->xss_clean($this->input->post('class'));
            $dateOfAdmission = $this->security->xss_clean($this->input->post('dateOfAdmission'));
            $program = $this->security->xss_clean($this->input->post('program'));
            $birthday = $this->security->xss_clean($this->input->post('birthday'));
            $age = $this->security->xss_clean($this->input->post('age'));
            $gender = $this->security->xss_clean($this->input->post('gender'));
            $fathername = $this->security->xss_clean($this->input->post('fathername'));
            $fatheremail = $this->security->xss_clean($this->input->post('fatheremail'));
            $fatherMobile_no = $this->security->xss_clean($this->input->post('fatherMobile_no'));
            $mothername = $this->security->xss_clean($this->input->post('mothername'));
            $motheremail = $this->security->xss_clean($this->input->post('motheremail'));
            $motherMobile_no = $this->security->xss_clean($this->input->post('motherMobile_no'));
            $bloodGroup = $this->security->xss_clean($this->input->post('bloodGroup'));
            $motherTongue = $this->security->xss_clean($this->input->post('motherTongue'));
            $religion = $this->security->xss_clean($this->input->post('religion'));
            $caste = $this->security->xss_clean($this->input->post('caste'));
            $city = $this->security->xss_clean($this->input->post('city'));
            $state = $this->security->xss_clean($this->input->post('state'));
            $totalFee = $this->security->xss_clean($this->input->post('totalFee'));
            $address = $this->security->xss_clean($this->input->post('address'));
            $previousSchool = $this->security->xss_clean($this->input->post('previousSchool'));
            $remark = $this->security->xss_clean($this->input->post('remark'));

            // Prepare data for update
            $franchiseNumbers = implode(',', (array)$franchiseNumberArray);
            $admissiondetailsInfo = array(
                'brspFranchiseAssigned' => $brspFranchiseAssigned,
                'name' => $name,
                'enrollNum' => $enrollNum,
                'class' => $class,
                'dateOfAdmission' => $dateOfAdmission,
                'program' => $program,
                'birthday' => $birthday,
                'age' => $age,
                'gender' => $gender,
                'fathername' => $fathername,
                'fatheremail' => $fatheremail,
                'fatherMobile_no' => $fatherMobile_no,
                'mothername' => $mothername,
                'motheremail' => $motheremail,
                'motherMobile_no' => $motherMobile_no,
                'bloodGroup' => $bloodGroup,
                'motherTongue' => $motherTongue,
                'religion' => $religion,
                'caste' => $caste,
                'city' => $city,
                'state' => $state,
                'totalFee' => $totalFee,
                'address' => $address,
                'previousSchool' => $previousSchool,
                'franchiseNumber' => $franchiseNumbers,
                'remark' => $remark,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->admdet->editAdmissiondetails($admissiondetailsInfo, $admid);

            if ($result == true) {
                // Notify the user who updated the admission
                $message = "You updated admission: {$name} (Admission ID: {$admid})";
                $notificationResult = $this->Notification_model->add_admdet_notification($this->vendorId, $message, $admid);
                if (!$notificationResult) {
                    log_message('error', "Failed to add notification for user {$this->vendorId} on admission ID {$admid}");
                }

                // Notify users associated with the franchise
                if (!empty($franchiseNumberArray)) {
                    foreach ($franchiseNumberArray as $franchiseNumber) {
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                        if (!empty($branchDetail)) {
                            // Send email notification
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL Admission Updated";
                            $message = "Dear {$branchDetail->applicantName}, ";
                            $message .= "An admission has been updated by {$this->session->userdata('name')}. ";
                            $message .= "Student Name: {$name}, Admission ID: {$admid}, Description: {$remark}. ";
                            $message .= "Please visit the portal for details.";
                            $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                            if (!mail($to, $subject, $message, $headers)) {
                                log_message('error', "Failed to send email to {$to} for admission ID {$admid}");
                            }

                            // Get user ID mapped with this franchise number
                            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                            if (!empty($franchiseUser)) {
                                $notificationMessage = "Admission updated: {$name} (Admission ID: {$admid})";
                                $notificationResult = $this->Notification_model->add_admdet_notification($franchiseUser->userId, $notificationMessage, $admid);
                                if (!$notificationResult) {
                                    log_message('error', "Failed to add notification for user {$franchiseUser->userId} on admission ID {$admid}");
                                }
                            }
                        }
                    }
                }

                // Notify admins (role IDs 1, 14, 20)
                $adminUsers = $this->bm->getUsersByRoles([1, 14, 20]);
                if (empty($adminUsers)) {
                    log_message('error', "No admins found for role IDs 1, 14, 20 on admission ID {$admid}");
                }
                foreach ($adminUsers as $adminUser) {
                    $notificationMessage = "Admission updated: {$name} (Admission ID: {$admid})";
                    $notificationResult = $this->Notification_model->add_admdet_notification($adminUser->userId, $notificationMessage, $admid);
                    if (!$notificationResult) {
                        log_message('error', "Failed to add notification for admin {$adminUser->userId} on admission ID {$admid}");
                    }
                }

                // Email notification to admin
                $adminEmail = 'dev.edumeta@gmail.com'; // Replace with actual admin email
                $adminSubject = "Alert - eduMETA THE i-SCHOOL Admission Updated";
                $adminMessage = "An admission has been updated. ";
                $adminMessage .= "Student Name: {$name}, Admission ID: {$admid}. ";
                $adminMessage .= "Please visit the portal for details.";
                $adminHeaders = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                if (!mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders)) {
                    log_message('error', "Failed to send email to {$adminEmail} for admission ID {$admid}");
                }

                $this->session->set_flashdata('success', 'Admission updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Admission enquiry updation failed');
            }

            redirect('admissiondetails/admissiondetailsListing');
        }
    }
}
     public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Fetch the users based on the franchise number
    $users = $this->admdet->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

    // Generate HTML options for the response
    $options = '<option value="0">Select Role</option>';
    foreach ($users as $user) {
        $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
    }

    echo $options; // Output the options as HTML
}
}

?>