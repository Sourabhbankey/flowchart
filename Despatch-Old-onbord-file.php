<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Despatch (DespatchController)
 * Despatch Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 08 Jun 2023
  */
class Despatch extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Despatch_model', 'dm');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Despatch';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('despatch/despatchListing');
    }
    
    /**
     * This function is used to load the task list
     */
    function despatchListing()
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
            
            $count = $this->dm->despatchListingCount($searchText);

			$returns = $this->paginationCompress ( "despatchListing/", $count, 500 );
            
            $data['records'] = $this->dm->despatchListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Despatch';
            
            $this->loadViews("despatch/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Despatch';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$this->loadViews("despatch/add", $this->global, NULL, NULL);
            $this->loadViews("despatch/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewDespatch()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('despatchTitle','Despatch Title','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $despatchTitle = $this->security->xss_clean($this->input->post('despatchTitle'));
                $AcInvoicenumDesp = $this->security->xss_clean($this->input->post('AcInvoicenumDesp'));
                $dir = dirname($_FILES["file"]["tmp_name"]);
                                $destination = $dir . DIRECTORY_SEPARATOR . time().'-'.$_FILES["file"]["name"];
                                rename($_FILES["file"]["tmp_name"], $destination);
                                $storeFolder = 'attachements';

                                $s3Result = $this->s3_upload->upload_file($destination,$storeFolder);
                                $result_arr = $s3Result->toArray();
                                if(!empty($result_arr['ObjectURL'])) {
                                      $s3_file_link[] = $result_arr['ObjectURL'];
                                } else {
                                    $s3_file_link[] = '';
                                }

//                             }
                            $s3files = implode(',', $s3_file_link);
                //$franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $orderNumber = $this->security->xss_clean($this->input->post('orderNumber'));
                $modeOforder = $this->security->xss_clean($this->input->post('modeOforder'));
                $transportCourior = $this->security->xss_clean($this->input->post('transportCourior'));
                $emailconfirmDispatchPOD = $this->security->xss_clean($this->input->post('emailconfirmDispatchPOD'));
                $podNumber = $this->security->xss_clean($this->input->post('podNumber'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumbers = implode(',',$franchiseNumberArray);
                $despatchInfo = array('AcInvoicenumDesp'=>$AcInvoicenumDesp, 'acattachmentInvoiceS3File'=>$s3files, 'despatchTitle'=>$despatchTitle, 'franchiseNumber'=>$franchiseNumbers, 'orderNumber'=>$orderNumber, 'modeOforder'=>$modeOforder, 'transportCourior'=>$transportCourior, 'emailconfirmDispatchPOD'=>$emailconfirmDispatchPOD, 'podNumber'=>$podNumber, 'acattachmentVRLS3File'=>$s3files, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->dm->addNewDespatch($despatchInfo);
                if($result > 0) {
                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Despatch";
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
                    $this->session->set_flashdata('success', 'New Despatch created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Despatch creation failed');
                }
                
                redirect('despatch/despatchListing');
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
                redirect('despatch/despatchListing');
            }
            $data['despatchInfo'] = $this->dm->getDespatchInfo($despatchId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $data['users'] = $this->dm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Despatch';            
            $this->loadViews("despatch/edit", $this->global, $data, NULL);
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
                $AcInvoicenumDesp = $this->security->xss_clean($this->input->post('AcInvoicenumDesp'));
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