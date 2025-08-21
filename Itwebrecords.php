<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Salesrecord (SalesrecordController)
 * Salesrecord Class to control Salesrecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Itwebrecords extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {  
        parent::__construct();
       $this->load->model('Itwebrecords_model', 'it');
        $this->isLoggedIn();
        $this->module = 'it';
		
		
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('itwebrecords/itwebrecordsListing');
    }
    
    /**
     * This function is used to load the salesrecord list
     */
 function itwebrecordsListing()
{
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');

    $searchText = '';
    if (!empty($this->input->post('searchText'))) {
        $searchText = $this->security->xss_clean($this->input->post('searchText'));
    }
    $data['searchText'] = $searchText;

    $this->load->library('pagination');

    // Pass user filtering condition
    $count = $this->it->itwebrecordsListingCount($searchText, $userId, $userRole);

    $returns = $this->paginationCompress("it/", $count, 500);

    // Pass role-based filtering
    $data['records'] = $this->it->itwebrecordsListing($searchText, $returns["page"], $returns["segment"], $userId, $userRole);

    $this->global['pageTitle'] = 'CodeInsect : My Followup';

    $this->loadViews("itwebrecords/list", $this->global, $data, NULL);
}

   /* }*/

    /**
     * This function is used to load the add new form
     */
    function add()

    {   
	
        /*if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            $this->global['pageTitle'] = 'CodeInsect : Add New It';
			

          // $this->loadViews("salesrecord/add", $this->global, $data);
           $this->loadViews("itwebrecords/add", $this->global, Null, NULL);
        }
		
   /* }*/
    
    /**
     * This function is used to add new user to the system
     */

public function addNewItwebrecords()
{
    /* if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {*/
        $this->load->library('form_validation');

        
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            // Get form data
           $userId = $this->session->userdata('userId');
       // $roleId = $this->session->userdata('role');
            $brNumbr   = $this->security->xss_clean($this->input->post('brNumbr'));
            $brName = $this->security->xss_clean($this->input->post('brName'));
            $brOnboardMgr  = $this->security->xss_clean($this->input->post('brOnboardMgr'));
            $brGrowthMgr  = $this->security->xss_clean($this->input->post('brGrowthMgr'));
             $brCity  = $this->security->xss_clean($this->input->post('brCity'));
              $brStatus  = $this->security->xss_clean($this->input->post('brStatus'));
               $webCreationStatus  = $this->security->xss_clean($this->input->post('webCreationStatus'));
                $websiteLink  = $this->security->xss_clean($this->input->post('websiteLink'));
                $websharingdate  = $this->security->xss_clean($this->input->post('websharingdate'));
                $edumetaAppdate  = $this->security->xss_clean($this->input->post('edumetaAppdate'));
               
                
            $description = $this->security->xss_clean($this->input->post('description'));

          

            // Create new record array
            $ItwebrecordsInfo = array(
                 'userId'              => $userId,  // Insert the userId
           // 'roleId'            => $roleId,
            
                
                'brNumbr' => $brNumbr, 
                'brName' => $brName, 
                'brOnboardMgr' => $brOnboardMgr,
                'brGrowthMgr' => $brGrowthMgr,
                'brCity' => $brCity,
                'brStatus' => $brStatus,
                'webCreationStatus' => $webCreationStatus, 
                'websiteLink' => $websiteLink,
                'websharingdate' => $websharingdate,
                'edumetaAppdate' => $edumetaAppdate,
                'description' => $description
                
            );

            // Insert the new sales record
            $result = $this->it->addNewItwebrecords($ItwebrecordsInfo);
//print_r($ItwebrecordsInfo);exit;
            if ($result > 0) {
               
                $this->session->set_flashdata('success', 'New It record created successfully ');
            } else {
                $this->session->set_flashdata('error', 'It record creation failed');
            }

            redirect('Itwebrecords/itwebrecordsListing');
        }
   /* }*/
    
}

    
    /**
     * This function is used load salesrecord edit information
     * @param number $salesrecId : Optional : This is salesrecord id
     */
    function edit($webrecordId = NULL)
    {
       /*if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            if($webrecordId == null)
            {
                redirect('itwebrecords/itwebrecordsListing');
            }
            
            $data['itwebrecordsInfo'] = $this->it->getitwebrecordsInfo($webrecordId );

            $this->global['pageTitle'] = 'CodeInsect : Edit it';
            
            $this->loadViews("itwebrecords/edit", $this->global, $data, NULL);
        }
    /*}*/
    
    
    /**
     * This function is used to edit the user information
     */
    function edititwebrecords()
    {
       /* if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            $this->load->library('form_validation');
            
            $webrecordId = $this->input->post('webrecordId');
            
         
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($webrecordId );
            }
            else
            { 
        
                $brNumbr   = $this->security->xss_clean($this->input->post('brNumbr'));
                $brName = $this->security->xss_clean($this->input->post('brName'));
                $brOnboardMgr  = $this->security->xss_clean($this->input->post('brOnboardMgr'));
                $brGrowthMgr  = $this->security->xss_clean($this->input->post('brGrowthMgr'));
                 $brCity  = $this->security->xss_clean($this->input->post('brCity'));
                  $brStatus  = $this->security->xss_clean($this->input->post('brStatus'));
                   $webCreationStatus  = $this->security->xss_clean($this->input->post('webCreationStatus'));
                    $websiteLink  = $this->security->xss_clean($this->input->post('websiteLink'));
                    $websharingdate  = $this->security->xss_clean($this->input->post('websharingdate'));
                    $edumetaAppdate  = $this->security->xss_clean($this->input->post('edumetaAppdate'));
                   
                    
                $description = $this->security->xss_clean($this->input->post('description'));
               $itwebrecordsInfo = array(
                

                
                 'brNumbr' => $brNumbr, 
                'brName' => $brName, 
                'brOnboardMgr' => $brOnboardMgr,
                'brGrowthMgr' => $brGrowthMgr,
                'brCity' => $brCity,
                'brStatus' => $brStatus,
                'webCreationStatus' => $webCreationStatus, 
                'websiteLink' => $websiteLink, 
                'websharingdate' => $websharingdate, 
                'edumetaAppdate' => $edumetaAppdate,
                'description' => $description
                
            );
               //print_r($followuprecordInfo);EXIT; 
                $result = $this->it->edititwebrecords($itwebrecordsInfo, 
                    $webrecordId );
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'it updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'it updation failed');
                }
                
                redirect('itwebrecords/itwebrecordsListing');
            }
        }
    /*}*/
	



}

?>