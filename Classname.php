<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Classname : Classname (Classname)
 * Classname Classname to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
Class Classname extends BaseController
{
    /**
     * This is default constructor of the Classname
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Classname_model', 'cls');
        //$this->load->model('Branches_model', 'bm');
         $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Classname';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('Classname/ClassnameListing');
    }
    
    /**
     * This function is used to load the Support list
     */
    function ClassnameListing()
{
    if (!$this->hasListAccess()) {
        $this->loadThis();
    } else {
        $searchText = '';
        if (!empty($this->input->post('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
        }
        $data['searchText'] = $searchText;

        $this->load->library('pagination');

        $count = $this->cls->ClassnameListingCount($searchText);

        $returns = $this->paginationCompress("ClassnameListing/", $count, 500);

        $data['records'] = $this->cls->ClassnameListing($searchText, $returns["page"], $returns["segment"]);

        // ✅ Add this block to fix the undefined variable issue
        $data["start"] = $returns["page"] + 1;
        $data["end"] = $returns["page"] + count($data['records']);
        $data["total_records"] = $count;
        $data["links"] = $this->pagination->create_links();

        $this->global['pageTitle'] = 'Meeting : Classname';
        $this->loadViews("classname/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Classname';
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("classname/add", $this->global,  NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    public function addNewClassname()
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
          

            $ClassnameInfo = [
                'className' => $className,
                'classNumeric' => $classNumeric,
               
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
            ];

            $classId = $this->cls->addNewClassname($ClassnameInfo);
//print_r($ClassnameInfo);exit;
            if ($classId > 0) {
              
               $this->session->set_flashdata('success', 'New Classname created successfully');
            } else {
                $this->session->set_flashdata('error', 'Classname creation failed');
            }
            
            redirect('Classname/ClassnameListing');
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
                redirect('Classname/ClassnameListing');
            }
            
            $data['ClassnameInfo'] = $this->cls->getClassnameInfo($classId);
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Classnames : Edit Classname';
            
            $this->loadViews("classname/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
 
function editClassname()
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
            $ClassnameInfo = array(
                 'className' => $className,
                'classNumeric' => $classNumeric,
               
                
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );
            
            $result = $this->cls->editClassname($ClassnameInfo, $classId);
            
            if($result == true)
            {
                $this->session->set_flashdata('success', 'Classname updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Classname updation failed');
            }
            
            redirect('Classname/ClassnameListing');
        }
    }
}


}

?>