<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Admission (AdmissionController)
 * Admission Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 08 Jun 2023
  */
class Admission extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admission_model', 'am');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Admission';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('admission/admissionListing');
    }
    
    /**
     * This function is used to load the task list
     */
    function admissionListing()
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
            
            $count = $this->am->despatchListingCount($searchText);

			$returns = $this->paginationCompress ( "admissionListing/", $count, 500 );
            
            $data['records'] = $this->am->despatchListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Admission';
            
            $this->loadViews("admission/list", $this->global, $data, NULL);
        }
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
            //$data['users'] = $this->dm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Admission';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$this->loadViews("admission/add", $this->global, NULL, NULL);
            $this->loadViews("admission/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewAdmission()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('admissionTitle','Admission Title','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $admissionTitle = $this->security->xss_clean($this->input->post('admissionTitle'));
                //$franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $orderNumber = $this->security->xss_clean($this->input->post('orderNumber'));
                $modeOforder = $this->security->xss_clean($this->input->post('modeOforder'));
                $transportCourior = $this->security->xss_clean($this->input->post('transportCourior'));
                $emailconfirmDispatchPOD = $this->security->xss_clean($this->input->post('emailconfirmDispatchPOD'));
                $podNumber = $this->security->xss_clean($this->input->post('podNumber'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumbers = implode(',',$franchiseNumberArray);
                $despatchInfo = array('despatchTitle'=>$despatchTitle, 'franchiseNumber'=>$franchiseNumbers, 'orderNumber'=>$orderNumber, 'modeOforder'=>$modeOforder, 'transportCourior'=>$transportCourior, 'emailconfirmDispatchPOD'=>$emailconfirmDispatchPOD, 'podNumber'=>$podNumber, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->am->addNewAdmission($admissionInfo);
                if($result > 0) {
                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Admission";
                                $message = 'Dear '.$branchDetail->applicantName.' ';
                                //$message = ' '.$description.' ';
                                $message .= 'You have been assigned a new training. BY- '.$this->session->userdata("name").' ';
                                $message .= 'Please visit the portal.';
                                $message = ' '.$description.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to,$subject,$message,$headers);
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Admission created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Admission creation failed');
                }
                
                redirect('admission/admissionListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($despatchId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($despatchId == null)
            {
                redirect('admission/admissionListing');
            }
            $data['admissionInfo'] = $this->am->getDespatchInfo($admissionId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $data['users'] = $this->dm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Despatch';            
            $this->loadViews("admission/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editDespatch()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $despatchId = $this->input->post('despatchId');
            
            $this->form_validation->set_rules('despatchTitle','Despatch Title','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($despatchId);
            }
            else
            {
                $despatchTitle = $this->security->xss_clean($this->input->post('despatchTitle'));
                //$franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $orderNumber = $this->security->xss_clean($this->input->post('orderNumber'));
                $modeOforder = $this->security->xss_clean($this->input->post('modeOforder'));
                $transportCourior = $this->security->xss_clean($this->input->post('transportCourior'));
                $emailconfirmDispatchPOD = $this->security->xss_clean($this->input->post('emailconfirmDispatchPOD'));
                $podNumber = $this->security->xss_clean($this->input->post('podNumber'));
                $description = $this->security->xss_clean($this->input->post('description'));

                
                $despatchInfo = array('despatchTitle'=>$despatchTitle, 'franchiseNumber'=>$franchiseNumbers, 'orderNumber'=>$orderNumber, 'modeOforder'=>$modeOforder, 'transportCourior'=>$transportCourior, 'emailconfirmDispatchPOD'=>$emailconfirmDispatchPOD, 'podNumber'=>$podNumber, 'description'=>$description, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->dm->editDespatch($despatchInfo, $despatchId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Despatch updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Despatch updation failed');
                }
                
                redirect('despatch/despatchListing');
            }
        }
    }
}

?>