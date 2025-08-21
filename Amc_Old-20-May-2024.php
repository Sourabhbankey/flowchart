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
        $this->isLoggedIn();
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
    function amcListing()
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
            
            $count = $this->ay->amcListingCount($searchText);

			$returns = $this->paginationCompress ( "amcListing/", $count, 500 );
            
            $data['records'] = $this->ay->amcListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Amc';
            
            $this->loadViews("amc/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Amc';

            $this->loadViews("amc/add", $this->global, NULL, NULL);
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
            
            $this->form_validation->set_rules('brnameTitle','Branch Name','trim|required|max_length[256]');
            $this->form_validation->set_rules('descAmc','descAmc','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $franchiseNum = $this->security->xss_clean($this->input->post('franchiseNum'));
                $brnameTitle = $this->security->xss_clean($this->input->post('brnameTitle'));
                $brLocation = $this->security->xss_clean($this->input->post('brLocation'));
                $brState = $this->security->xss_clean($this->input->post('brState'));
                $oldAMCdue = $this->security->xss_clean($this->input->post('oldAMCdue'));
                $curAmc = $this->security->xss_clean($this->input->post('curAmc'));
                $totalAmc = $this->security->xss_clean($this->input->post('totalAmc'));
                $statusAmc = $this->security->xss_clean($this->input->post('statusAmc'));
                $dueDateAmc = $this->security->xss_clean($this->input->post('dueDateAmc'));
                $descAmc = $this->security->xss_clean($this->input->post('descAmc'));
                
                $amcInfo = array('brnameTitle'=>$brnameTitle, 'franchiseNum'=>$franchiseNum, 'brLocation'=>$brLocation, 'brState'=>$brState, 'oldAMCdue'=>$oldAMCdue, 'curAmc'=>$curAmc, 'totalAmc'=>$totalAmc, 'statusAmc'=>$statusAmc, 'dueDateAmc'=>$dueDateAmc, 'descAmc'=>$descAmc, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = @$this->ay->addNewAmc($amcInfo);
                /*$branchDetail = $this->bm->getBranchesInfoByfranchiseNum($franchiseNum);
                if($result > 0) {
                    if(!empty($branchDetail)){
                        $to = $branchDetail->branchEmail;
                        $subject = "Alert - Assign new AMC";
                        $message = 'Dear '.$branchDetail->applicantName.' ';
                        $message .= 'You have been assigned a new despatch. ';
                        $message .= 'Please visit the portal.';
                        $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                        mail($to,$subject,$message,$headers);
                    }
                    $this->session->set_flashdata('success', 'New AMC created successfully');
                } else {
                    $this->session->set_flashdata('error', 'AMC creation failed');
                }*/
                
                redirect('amc/amcListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
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
            $this->loadViews("amc/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editAmc()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $amcId = $this->input->post('amcId');
            
            $this->form_validation->set_rules('brnameTitle','Branch Name','trim|required|max_length[256]');
            $this->form_validation->set_rules('descAmc','descAmc','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($amcId);
            }
            else
            {
                $brnameTitle = $this->security->xss_clean($this->input->post('brnameTitle'));
                $franchiseNum = $this->security->xss_clean($this->input->post('franchiseNum'));
                $brLocation = $this->security->xss_clean($this->input->post('brLocation'));
                $brState = $this->security->xss_clean($this->input->post('brState'));
                $oldAMCdue = $this->security->xss_clean($this->input->post('oldAMCdue'));
                $curAmc = $this->security->xss_clean($this->input->post('curAmc'));
                $totalAmc = $this->security->xss_clean($this->input->post('totalAmc'));
                $statusAmc = $this->security->xss_clean($this->input->post('statusAmc'));
                $dueDateAmc = $this->security->xss_clean($this->input->post('dueDateAmc'));
                $descAmc = $this->security->xss_clean($this->input->post('descAmc'));

                
                $despatchInfo = array('brnameTitle'=>$brnameTitle, 'franchiseNum'=>$franchiseNum, 'brLocation'=>$brLocation, 'brState'=>$brState, 'oldAMCdue'=>$oldAMCdue, 'curAmc'=>$curAmc, 'totalAmc'=>$totalAmc, 'statusAmc'=>$statusAmc, 'dueDateAmc'=>$dueDateAmc, 'descAmc'=>$descAmc, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->ay->editDespatch($amcInfo, $amcId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Amc updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Amc updation failed');
                }
                
                redirect('amc/amcListing');
            }
        }
    }
}

?>