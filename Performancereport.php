<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Salesrecord (SalesrecordController)
 * Salesrecord Class to control Salesrecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Performancereport extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {  
        parent::__construct();
       $this->load->model('Performancereport_model', 'per');
        $this->isLoggedIn();
        $this->module = 'Performancereport';
		
		
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('performancereport/PerformancereportListing');
    }
    
    /**
     * This function is used to load the salesrecord list
     */
 public function performancereportListing()
{
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');

    $searchText = $this->input->post('searchText', true);
    $performerName = $this->input->post('performerName', true); 
    $monthFilter = $this->input->post('monthFilter', true); // Get selected month

    $data['searchText'] = $searchText;
    $data['performerName'] = $performerName;
    $data['monthFilter'] = $monthFilter; // Save the filter value to keep selection

    $this->load->library('pagination');

    $count = $this->per->performancereportListingCount($searchText, $userId, $userRole, $performerName, $monthFilter);

    $config = array();
    $config["base_url"] = base_url() . "performancereport/performancereportListing";
    $config["total_rows"] = $count;
    $config["per_page"] = 10; 
    $config["uri_segment"] = 3;
    $config['num_links'] = 2;
    
    $this->pagination->initialize($config);

    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

    $data['records'] = $this->per->performancereportrecordListing($searchText, $config["per_page"], $page, $userId, $userRole, $performerName, $monthFilter);

    $data["pagination"] = $this->pagination->create_links();
    $data['users'] = $this->per->getAllUsers();
    $data['start_record'] = $page + 1;
    $data['end_record'] = min($page + $config["per_page"], $count);
    $data['total_records'] = $count;

    $this->global['pageTitle'] = 'Performance Report Management';
    $this->loadViews("performancereport/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New followup';
			 $data['users'] = $this->per->getAllUsers();
         $data['months'] = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

          // $this->loadViews("salesrecord/add", $this->global, $data);
           $this->loadViews("performancereport/add", $this->global, $data, NULL);
        }
		
    /*}*/
    
    /**
     * This function is used to add new user to the system
     */

public function addNewPerformancereportrecord()
{
    $this->load->library('form_validation');

    $this->form_validation->set_rules('performannceCount', 'Performance Count', 'trim|required|max_length[1024]');

    if ($this->form_validation->run() == FALSE) {
        $this->add();
    } else {
        // Get performer name from form input
        $performerName = $this->security->xss_clean($this->input->post('performerName'));

        // Fetch performer details from tbl_users
        $this->db->select('userId, roleId, teamLeadsales');
        $this->db->from('tbl_users');
        $this->db->where('name', $performerName); // Assuming 'name' column stores performerName
        $query = $this->db->get();
        $performerData = $query->row();

        if (!$performerData) {
            $this->session->set_flashdata('error', 'Performer not found in the system');
            redirect('performancereport/performancereportListing');
            return;
        }

        // Get performer details
        $performerId = $performerData->userId;
        $performerRole = $performerData->roleId;
        $teamLeadsales = $performerData->teamLeadsales;

        // Get form data
        $performancereportrecordInfo = array(
            'userId'              => $performerId,  // Insert performer's userId
            'roleId'              => $performerRole,  // Insert performer's roleId
            'teamLeadsales'       => $teamLeadsales,  // Insert performer's team lead ID
            'performerName'       => $performerName,
            'performannceMonths'  => $this->security->xss_clean($this->input->post('performannceMonths')),
            'performannceCount'   => $this->security->xss_clean($this->input->post('performannceCount')),
            'description'         => $this->security->xss_clean($this->input->post('description'))
        );

        $result = $this->per->addNewperformancereportrecord($performancereportrecordInfo);

        if ($result > 0) {
            $this->session->set_flashdata('success', 'New Performance report record created successfully');
        } else {
            $this->session->set_flashdata('error', 'Performance report record creation failed');
        }

        redirect('performancereport/performancereportListing');
    }
}


    
    /**
     * This function is used load salesrecord edit information
     * @param number $salesrecId : Optional : This is salesrecord id
     */
    function edit($performannceId = NULL)
    {
       /*if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            if($performannceId == null)
            {
                redirect('performancereport/performancereportListing');
            }
            
            $data['performancereportrecordInfo'] = $this->per->getPerformancereportrecordInfo($performannceId);
$data['users'] = $this->per->getAllUsers();
         $data['months'] = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
            $this->global['pageTitle'] = 'CodeInsect : Edit top10clients';
            
            $this->loadViews("performancereport/edit", $this->global, $data, NULL);
        }
    /*}*/
    
    
    /**
     * This function is used to edit the user information
     */
    function editPerformancereportrecord()
    {
       /* if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            $this->load->library('form_validation');
            
            $performannceId = $this->input->post('performannceId');
            
         
            $this->form_validation->set_rules('performannceCount','Performannce Count','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($performannceId);
            }
            else
            { 
        
               
              $performerName   = $this->security->xss_clean($this->input->post('performerName'));
            $performannceMonths = $this->security->xss_clean($this->input->post('performannceMonths'));
            $performannceCount  = $this->security->xss_clean($this->input->post('performannceCount'));
            $description  = $this->security->xss_clean($this->input->post('description'));
               $performancereportrecordInfo = array(
                
                  'performerName' => $performerName, 
                
                'performannceMonths' => $performannceMonths,
                 'performannceCount' => $performannceCount, 
                'description' => $description
                
            );
               //print_r($followuprecordInfo);EXIT; 
                $result = $this->per->editPerformancereportrecord($performancereportrecordInfo, 
                    $performannceId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'top10client updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'top10client updation failed');
                }
                
                redirect('performancereport/performancereportListing');
            }
        }
    /*}*/
	


public function monthlyPerformance()
{
    $monthFilter = $this->input->post('monthFilter') ?? date('F'); // Default to current month
    $userRole = $this->session->userdata('role');
    $userId = $this->session->userdata('userId');

    // Get user performance summary
    $data['records'] = $this->per->getUserPerformanceSummary($monthFilter, $userId, $userRole);

    $data['monthFilter'] = $monthFilter;

    // Load view
    $this->global['pageTitle'] = 'Performance Report - Monthly View';
     $this->loadViews("performancereport/view", $this->global, $data, NULL);
}




}



?>