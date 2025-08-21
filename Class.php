<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Class (Class)
 * Class Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Class extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Class_model', 'cls');
        //$this->load->model('Branches_model', 'bm');
         $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Class';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('Class/ClassListing');
    }
    
    /**
     * This function is used to load the Support list
     */
    function ClassListing()
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
            
            $count = $this->cls->ClassListingCount($searchText);

			$returns = $this->paginationCompress ( "ClassListing/", $count, 500 );
            
            $data['records'] = $this->cls->ClassListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'Meeting : Class';
            
            $this->loadViews("Class/list", $this->global, $data, NULL);
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
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Class';
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("Class/add", $this->global,  NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    public function addNewClass()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('className', 'Class name', 'trim|required|max_length[256]');
        //$this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $className = $this->security->xss_clean($this->input->post('className'));
            $classNumeric = $this->security->xss_clean($this->input->post('classNumeric'));
          

            $ClassInfo = [
                'className' => $className,
                'classNumeric' => $classNumeric,
               
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
            ];

            $classId = $this->cls->addNewClass($ClassInfo);

            if ($classId > 0) {
              
               $this->session->set_flashdata('success', 'New Class created successfully');
            } else {
                $this->session->set_flashdata('error', 'Class creation failed');
            }
            
            redirect('Class/ClassListing');
        }
    }
}

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($classId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($classId == null)
            {
                redirect('Class/ClassListing');
            }
            
            $data['ClassInfo'] = $this->cls->getClassInfo($classId);
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Classs : Edit Class';
            
            $this->loadViews("Class/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
 
function editClass()
{
    if(!$this->hasUpdateAccess())
    {
        $this->loadThis();
    }
    else
    {
        $this->load->library('form_validation');
        // Load the notification model
        
        $classId = $this->input->post('classId');
        
         $this->form_validation->set_rules('className', 'Class name', 'trim|required|max_length[256]');
        //$this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($classId);
        }
        else
        {
             $className = $this->security->xss_clean($this->input->post('className'));
            $classNumeric = $this->security->xss_clean($this->input->post('classNumeric'));
          
            /*-ENd-added-field-*/
            $ClassInfo = array(
                'className' => $className,
                'classNumeric' => $classNumeric,
                
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );
            
            $result = $this->cls->editClass($ClassInfo, $classId);
            
            if($result == true)
            {
                $this->session->set_flashdata('success', 'Class updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Class updation failed');
            }
            
            redirect('Class/ClassListing');
        }
    }
}


}

?>