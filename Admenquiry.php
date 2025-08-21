<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Admenquiry (AdmenquiryController)
 * Admenquiry Class to control Admenquiry related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 13 June 2024
 */
class Admenquiry extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admenquiry_model', 'admenq');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Admenquiry';
		$this->load->library('pagination');
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('admenquiry/admenquiryListing');
    }
    
    /**
     * This function is used to load the Admenquiry list
     */


//new code 15 

public function admenquiryListing() {
     $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
  
         $franchiseFilter = $this->input->get('franchiseNumber');
            if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
            $config = array();
            $config['base_url'] = base_url('admenquiry/admenquiryListing');
            $config['per_page'] = 10; 
            $config['uri_segment'] = 3;
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

            if ($userRole == '14' || $userRole == '1'|| $userRole == '23'|| $userRole == '20'|| $userRole == '28') { // Admin
                if ($franchiseFilter) {
                    $config['total_rows'] = $this->admenq->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                    $data['records'] = $this->admenq->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->admenq->getTotalTrainingRecordsCount();
                    
                    $data['records'] = $this->admenq->getAllTrainingRecords($config['per_page'], $page);
                }
                 } else if ($userRole == '15' || $userRole == '13') { // Specific roles
                    $config['total_rows'] = $this->admenq->getTotalTrainingRecordsCountByRole($userId);
                    $data['records'] = $this->admenq->getTrainingRecordsByRole($userId, $config['per_page'], $page);
                    
                } else { 
                        $franchiseNumber = $this->admenq->getFranchiseNumberByUserId($userId);
                        if ($franchiseNumber) {
                            if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                                $config['total_rows'] = $this->admenq->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->admenq->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                            } else {
                                $config['total_rows'] = $this->admenq->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                                $data['records'] = $this->admenq->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
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
    $this->loadViews("admenquiry/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Admenquiry';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("admenquiry/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewAdmenquiry()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('studentName','Student  Name','trim|required|max_length[256]');
            $this->form_validation->set_rules('remark','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {   $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $studentName = $this->security->xss_clean($this->input->post('studentName'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                /*-new-added-field-*/
                $class = $this->security->xss_clean($this->input->post('class'));
                $birthday = $this->security->xss_clean($this->input->post('birthday'));
                $age = $this->security->xss_clean($this->input->post('age'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $state = $this->security->xss_clean($this->input->post('state'));
                $fathername = $this->security->xss_clean($this->input->post('fathername'));
                /*--Newfield--*/
                $fatheremail = $this->security->xss_clean($this->input->post('fatheremail'));
                $fatherMobile_no = $this->security->xss_clean($this->input->post('fatherMobile_no'));
                $mothername = $this->security->xss_clean($this->input->post('mothername'));
                $motheremail = $this->security->xss_clean($this->input->post('motheremail'));
                $motherMobile_no = $this->security->xss_clean($this->input->post('motherMobile_no'));
                $feeOffered = $this->security->xss_clean($this->input->post('feeOffered'));
                $addressResidencial = $this->security->xss_clean($this->input->post('addressResidencial'));
                $addressPerma = $this->security->xss_clean($this->input->post('addressPerma'));
                /*-ENd-added-field-*/
                $remark = $this->security->xss_clean($this->input->post('remark'));
                $how_to_know = $this->security->xss_clean($this->input->post('how_to_know'));
                $enq_status = $this->security->xss_clean($this->input->post('enq_status'));
                $followup1 = $this->security->xss_clean($this->input->post('followup1'));
                $followup2 = $this->security->xss_clean($this->input->post('followup2'));
                $followup3 = $this->security->xss_clean($this->input->post('followup3'));
                $followup4 = $this->security->xss_clean($this->input->post('followup4'));
                $followup5 = $this->security->xss_clean($this->input->post('followup5'));
                $franchiseNumbers = implode(',',$franchiseNumberArray);
                $role = $this->session->userdata('role');
$updatedByField = null;

if (in_array($role, [1, 14, 20])) {
    $updatedByField = 'HO';
} elseif ($role == 25) {
    $updatedByField = $this->session->userdata('franchiseNumber');
}

                $admenquiryInfo = array('brspFranchiseAssigned'=>$brspFranchiseAssigned,'studentName'=>$studentName, 'class'=>$class, 'birthday'=>$birthday, 'age'=>$age, 'city'=>$city, 'state'=>$state, 'fathername'=>$fathername, 'fatheremail'=>$fatheremail, 'fatherMobile_no'=>$fatherMobile_no, 'mothername'=>$mothername, 'motheremail'=>$motheremail, 'motherMobile_no'=>$motherMobile_no, 'feeOffered'=>$feeOffered, 'addressResidencial'=>$addressResidencial, 'addressPerma'=>$addressPerma, 'how_to_know' => $how_to_know,
                    'enq_status' => $enq_status,
                    'followup1' => $followup1,
                    'followup2' => $followup2,
                    'followup3' => $followup3,
                    'followup4' => $followup4,
                    'followup5' => $followup5,'franchiseNumber'=>$franchiseNumbers, 'editBy' => $updatedByField ,'remark'=>$remark, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                
                $result = $this->admenq->addNewAdmenquiry($admenquiryInfo);
//print_r($admenquiryInfo);exit;
                if($result > 0) {
                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Admission enquiry";
                                $message = 'Dear '.$branchDetail->applicantName.' ';
                                //$message = ' '.$remark.' ';
                                $message .= 'You have been assigned a new meeting. BY- '.$this->session->userdata("studentName").' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$remark.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to,$subject,$message,$headers);
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Admission enquiry created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Admission enquiry creation failed');
                }
                
                redirect('admenquiry/admenquiryListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($enqid = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($enqid == null)
            {
                redirect('admenquiry/admenquiryListing');
            }
            
            $data['admenquiryInfo'] = $this->admenq->getAdmenquiryInfo($enqid);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Admission enquiry : Edit Admenquiry';
            
            $this->loadViews("admenquiry/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editAdmenquiry()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $enqid = $this->input->post('enqid');
            
            $this->form_validation->set_rules('studentName','Student Name','trim|required|max_length[256]');
            $this->form_validation->set_rules('remark','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($enqid);
            }
            else
            {
                $studentName = $this->security->xss_clean($this->input->post('studentName'));
                $remark = $this->security->xss_clean($this->input->post('remark'));
                /*-new-added-field-*/
                $class = $this->security->xss_clean($this->input->post('class'));
                $birthday = $this->security->xss_clean($this->input->post('birthday'));
                $age = $this->security->xss_clean($this->input->post('age'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $state = $this->security->xss_clean($this->input->post('state'));
                $fathername = $this->security->xss_clean($this->input->post('fathername'));
                /*-ENd-added-field-*/
                $fatheremail = $this->security->xss_clean($this->input->post('fatheremail'));
                $fatherMobile_no = $this->security->xss_clean($this->input->post('fatherMobile_no'));
                $mothername = $this->security->xss_clean($this->input->post('mothername'));
                $motheremail = $this->security->xss_clean($this->input->post('motheremail'));
                $motherMobile_no = $this->security->xss_clean($this->input->post('motherMobile_no'));
                $feeOffered = $this->security->xss_clean($this->input->post('feeOffered'));
                $addressResidencial = $this->security->xss_clean($this->input->post('addressResidencial'));
                $addressPerma = $this->security->xss_clean($this->input->post('addressPerma'));
                $followup1 = $this->security->xss_clean($this->input->post('followup1'));
                $followup2 = $this->security->xss_clean($this->input->post('followup2'));
                $followup3 = $this->security->xss_clean($this->input->post('followup3'));
                $followup4 = $this->security->xss_clean($this->input->post('followup4'));
                $followup5 = $this->security->xss_clean($this->input->post('followup5'));
                $admenquiryInfo = array('studentName'=>$studentName, 'class'=>$class, 'birthday'=>$birthday, 'age'=>$age, 'city'=>$city, 'state'=>$state, 'fathername'=>$fathername, 'fatheremail'=>$fatheremail, 'fatherMobile_no'=>$fatherMobile_no, 'mothername'=>$mothername, 'motheremail'=>$motheremail, 'motherMobile_no'=>$motherMobile_no, 'feeOffered'=>$feeOffered, 'addressResidencial'=>$addressResidencial, 'addressPerma'=>$addressPerma, 'remark'=>$remark,  'followup1' => $followup1,
    'followup2' => $followup2,
    'followup3' => $followup3,
    'followup4' => $followup4,
    'followup5' => $followup5,'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                $result = $this->admenq->editAdmenquiry($admenquiryInfo, $enqid);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Admission enquiry updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Admission enquiry updation failed');
                }
                
                redirect('admenquiry/admenquiryListing');
            }
        }
    }
        public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Fetch the users based on the franchise number
    $users = $this->admenq->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

    // Generate HTML options for the response
    $options = '<option value="0">Select Role</option>';
    foreach ($users as $user) {
        $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
    }

    echo $options; // Output the options as HTML
}
}

?>