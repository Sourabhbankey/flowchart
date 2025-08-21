<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Classname : Classname (Classname)
 * Classname Classname to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
Class Homework extends BaseController
{
    /**
     * This is default constructor of the Classname
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Homework_model', 'home');
      $this->load->model('Branches_model', 'bm');
         $this->load->model('Notification_model', 'nm');
        
        $this->isLoggedIn();
        $this->module = 'Homework';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('Homework/homeworkListing');
    }
    
    /**
     * This function is used to load the Support list
     */
    function homeworkListing()
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

        $count = $this->home->homeworkListingCount($searchText);

        $returns = $this->paginationCompress("homeworkListing/", $count, 500);

        $data['records'] = $this->home->homeworkListing($searchText, $returns["page"], $returns["segment"]);

        // ✅ Add this block to fix the undefined variable issue
        $data["start"] = $returns["page"] + 1;
        $data["end"] = $returns["page"] + count($data['records']);
        $data["total_records"] = $count;
        $data["links"] = $this->pagination->create_links();

        $this->global['pageTitle'] = 'Meeting : homework';
        $this->loadViews("homework/list", $this->global, $data, NULL);
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
           
            $this->global['pageTitle'] = 'CodeInsubt : Add New homework';
           
             $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            
             $data['subjects'] = $this->home->getAllSubjects();
              $data['sections'] = $this->home->getAllSections();
               $data['classes'] = $this->home->getAllClasses();
         
             $this->loadViews("homework/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    public function addNewHomework()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('homework_description','Description','trim|required|max_length[1024]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
          
           $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
            $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
            $class_name = $this->security->xss_clean($this->input->post('class_name'));
            $section_name = $this->security->xss_clean($this->input->post('section_name'));
            $subject_name = $this->security->xss_clean($this->input->post('subject_name'));
            $date_of_homework = $this->security->xss_clean($this->input->post('date_of_homework'));
            $date_of_submission = $this->security->xss_clean($this->input->post('date_of_submission'));
            $schedule_date = $this->security->xss_clean($this->input->post('schedule_date'));
            $homework_description = $this->security->xss_clean($this->input->post('homework_description'));
            $homeS3attachment_file = $this->security->xss_clean($this->input->post('homeS3attachment_file'));
            $sms_notification = $this->input->post('sms_notification') ? 1 : 0; // checkbox or toggle (boolean)
            $evaluation_date = $this->security->xss_clean($this->input->post('evaluation_date'));
            $evaluated_by = $this->security->xss_clean($this->input->post('evaluated_by'));
              $franchiseNumbers = implode(',',$franchiseNumberArray);



            $homeworkInfo = [
               
                'franchiseNumber' => $franchiseNumbers,
                'brspFranchiseAssigned' => $brspFranchiseAssigned,
                'class_name' => $class_name,
                'section_name' => $section_name,
                'subject_name' => $subject_name,
                'date_of_homework' => $date_of_homework,
                'date_of_submission' => $date_of_submission,
                'schedule_date' => $schedule_date,
                'homework_description' => $homework_description,
                'homeS3attachment_file' => $homeS3attachment_file,
                'sms_notification' => $sms_notification,
                'evaluation_date' => $evaluation_date,
                'evaluated_by' => $evaluated_by,
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
                
            ];

            $homeworkId = $this->home->addNewHomework($homeworkInfo);
//print_r($ClassnameInfo);exit;
            if ($homeworkId > 0) {
              
               $this->session->set_flashdata('success', 'New homework created successfully');
            } else {
                $this->session->set_flashdata('error', 'homework creation failed');
            }
            
            redirect('homework/homeworkListing');
        }
    }
}

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($homeworkId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($homeworkId == null)
            {
                redirect('homework/homeworkListing');
            }
            
            $data['homeworkInfo'] = $this->home->gethomeworkInfo($homeworkId);
           
            $this->global['pageTitle'] = 'Classnames : Edit homework';
            
            $this->loadViews("homework/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
 
function edithomework()
{
    if(!$this->hasUpdateAccess())
    {
        $this->loadThis();
    }
    else
    {
        $this->load->library('form_validation');
        // Load the notification model
        
        $homeworkId = $this->input->post('homeworkId');
        
         $this->form_validation->set_rules('homeworkName', 'homework', 'trim|required|max_length[256]');
        //$this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($homeworkId);
        }
        else
        {
              $homeworkName = $this->security->xss_clean($this->input->post('homeworkName'));
            $homeworkCode = $this->security->xss_clean($this->input->post('homeworkCode'));
            $homeworkType = $this->security->xss_clean($this->input->post('homeworkType'));
              
            /*-ENd-added-field-*/
            $homeworkInfo = array(
                  'homeworkName' => $homeworkName,
                'homeworkCode' => $homeworkCode,
                'homeworkType' => $homeworkType,
               
                
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );
            
            $result = $this->home->edithomework($homeworkInfo, $homeworkId);
            
            if($result == true)
            {
                $this->session->set_flashdata('success', 'homework updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'homework updation failed');
            }
            
            redirect('homework/homeworkListing');
        }
    }
}

public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Load model and fetch franchise info (adjust model name if needed)
    $this->load->model('Homework_model');
    $managers = $this->Homework_model->getManagersByFranchise($franchiseNumber);
    $franchiseData = $this->Homework_model->getFranchiseDetails($franchiseNumber);

    $options = '<option value="0">Select Role</option>';
    if (!empty($managers)) {
        foreach ($managers as $manager) {
            $options .= '<option value="' . $manager->userId . '">' . $manager->name . '</option>';
        }
    }

    echo json_encode([
        'managerOptions' => $options,
        'franchiseName' => $franchiseData ? $franchiseData->franchiseName : ''
    ]);
}
}

?>