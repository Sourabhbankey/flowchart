<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Announcement (AnnouncementController)
 * Announcement Class to control Announcement related operations.
 * @author : Ashish 
 * @version : 1
 * @since : 24 Jul 2024
 */
class DepartmentLeave extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('DepartmentLeave_model', 'dept');
         $this->load->model('role_model', 'rm');
         $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Ticket';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('departmentleave/departmentleaveListing');
    }
    
    /**
     * This function is used to load the announcement list
     */
   public function departmentleaveListing()
{
   /* if (!$this->hasListAccess()) {
        $this->loadThis();
    } else {*/
        // Get search text
        $searchText = '';
        if (!empty($this->input->post('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
        }
        $data['searchText'] = $searchText;
        
        // Load pagination library
        $this->load->library('pagination');
        
        // Get the total number of records for pagination
        $count = $this->dept->departmentleaveListingCount($searchText);
        
        // Pagination configuration
        $config = array();
        $config["base_url"] = base_url("departmentleave/departmentleaveListing");
        $config["total_rows"] = $count;
        $config["per_page"] = 10;
        $config["uri_segment"] = 3; // the page segment is expected at index 3 of the URI
        
        // Initialize pagination
        $this->pagination->initialize($config);

        // Get the current page number
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        // Fetch records for the current page
      $data['records'] = $this->dept->departmentleaveListing($searchText, $config['per_page'], $page);

        // Get the pagination links
        $data['pagination'] = $this->pagination->create_links();

        // Record range and total
        $data['start'] = $page + 1;
        $data['end'] = min($page + $config['per_page'], $config['total_rows']);
        $data['total_records'] = $config['total_rows'];

        // Set the page title
        $this->global['pageTitle'] = 'CodeInsect : Ticket';

        // Load the view
        $this->loadViews("departmentleave/list", $this->global, $data, NULL);
    }
/*}*/


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
            $this->global['pageTitle'] = 'CodeInsect : Add New Department Leave';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("departmentleave/add", $this->global, $data, NULL);
        }
    /*}*/
    
    /**
     * This function is used to add new user to the system
     */
 public function addNewDepartmentLeave()
{
    $this->load->library('form_validation');
    $this->form_validation->set_rules('department_name', 'Department', 'trim|required|max_length[10024]');

    if ($this->form_validation->run() == FALSE) {
        $this->add();
    } else {
        $department_name = $this->security->xss_clean($this->input->post('department_name'));
        $cl = $this->security->xss_clean($this->input->post('cl'));
        $sl = $this->security->xss_clean($this->input->post('sl'));
        $el = $this->security->xss_clean($this->input->post('el'));
       

        $departmentLeaveInfo = array(
             'department_name' => $this->input->post('department_name'),
                'cl' => $this->input->post('cl'),
                'sl' => $this->input->post('sl'),
                'el' => $this->input->post('el'),
                'createdBy' => $this->session->userdata('userId'),
                'createdDtm' => date('Y-m-d H:i:s'),
                'isDeleted' => 0
        );

        $result = $this->dept->addNewDepartmentLeave($departmentLeaveInfo);
        if ($result > 0) {
            $this->session->set_flashdata('success', 'Created successfully');
        } else {
            $this->session->set_flashdata('error', 'Creation failed');
        }

        redirect('departmentLeave/departmentleaveListing');
    }
}

/*}*/

    /**
     * This function is used load announcement edit information
     * @param number $announcementId : Optional : This is announcement id
     */
   function edit($departleaveId = NULL)
{
    if ($departleaveId == null) {
        redirect('departmentLeave/departmentLeaveListing');
    }

    $data['departmentLeaveInfo'] = $this->ret->getDepartmentLeaveInfo($departleaveId); // Add this line

    $this->global['pageTitle'] = 'CodeInsect : Edit Return Product';
    $this->loadViews("departmentLeave/edit", $this->global, $data, NULL); // Pass $data
}
    
    
    
    /**
     * This function is used to edit the user information
     */
    function editDepartmentLeave()
    {
       /* if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            $this->load->library('form_validation');
            
            $departleaveId = $this->input->post('departleaveId');
            
            
            $this->form_validation->set_rules('status','status','trim|required|max_length[10024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($departleaveId);
            }
            else
            {
               
                $status = $this->security->xss_clean($this->input->post('status'));
                 $reply = $this->security->xss_clean($this->input->post('reply'));

 $departmentLeaveInfo = array(
             'department_name' => $this->input->post('department_name'),
                'cl' => $this->input->post('cl'),
                'sl' => $this->input->post('sl'),
                'el' => $this->input->post('el'),
                'createdBy' => $this->session->userdata('userId'),
                'createdDtm' => date('Y-m-d H:i:s'),
                'updatedBy'=>$this->vendorId, 
                'updatedDtm'=>date('Y-m-d H:i:s'),
                'isDeleted' => 0
        );
                
                $result = $this->dept->editreturnproduct($departmentLeaveInfo, $departleaveId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'ticket updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'ticket updation failed');
                }
                
                redirect('departmentLeave/departmentLeaveListing');
            }
        }
   /* }*/
   

}

?>