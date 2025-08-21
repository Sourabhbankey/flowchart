<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Faq (FaqController)
 * Faq Class to control Faq related operations.
 * @author : Ashish 
 * @version : 1.5
 * @since : 11 Nov 2024
 */
class Faq extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Faq_model', 'faqbm');
         $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Faq';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('faq/faqListing');
    }
    
    /**
     * This function is used to load the faq list
     */
public function faqListing()
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

        $config["base_url"] = base_url() . "faq/faqListing";
        $config["per_page"] = 10;
        $config["uri_segment"] = 3;
        $config["total_rows"] = $this->faqbm->faqListingCount($searchText);
        $config["num_links"] = 2;
        $config["use_page_numbers"] = TRUE;  
        $config["reuse_query_string"] = TRUE;

       

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config["per_page"];

        $data["records"] = $this->faqbm->faqListing($searchText, $offset, $config["per_page"]);
        $data["links"] = $this->pagination->create_links();
        $data["start"] = ($config["total_rows"] > 0) ? ($offset + 1) : 0;
        $data["end"] = min($offset + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];

        $this->global['pageTitle'] = 'CodeInsect : FAQ';
        $this->loadViews("faq/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Faq';

            $this->loadViews("faq/add", $this->global, NULL, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewFaq()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('faqTitle','Title','trim|required');
            $this->form_validation->set_rules('description','Description','trim|required');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $faqTitle = $this->security->xss_clean($this->input->post('faqTitle'));
                $description = $this->security->xss_clean($this->input->post('description'));
                
                $faqInfo = array('faqTitle'=>$faqTitle, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->faqbm->addNewFaq($faqInfo);
                
                if($result > 0) {
                      // Send notifications to users with roleId 19, 14, 25
                    $notificationMessage = "<strong>FAQ Confirmation:</strong> New FAQ confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId =', [25])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_faq_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Faq created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Faq creation failed');
                }
                
                redirect('faq/faqListing');
            }
        }
    }
    function view($faqId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($faqId == null)
            {
                redirect('faq/faqListing');
            }
            
            $data['faqInfo'] = $this->faqbm->getFaqInfo($faqId);

            $this->global['pageTitle'] = 'CodeInsect : View Faq';
            
            $this->loadViews("faq/view", $this->global, $data, NULL);
        }
    }

    
    /**
     * This function is used load faq edit information
     * @param number $faqId : Optional : This is faq id
     */
    function edit($faqId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($faqId == null)
            {
                redirect('faq/faqListing');
            }
            
            $data['faqInfo'] = $this->faqbm->getFaqInfo($faqId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Faq';
            
            $this->loadViews("faq/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
   function editFaq()
{
    if(!$this->hasUpdateAccess())
    {
        $this->loadThis();
    }
    else
    {
        $this->load->library('form_validation');
        $this->load->model('Notification_model', 'nm'); // Load the notification model
        
        $faqId = $this->input->post('faqId');
        
        $this->form_validation->set_rules('faqTitle','Title','trim|required|max_length[50]');
        $this->form_validation->set_rules('description','Description','trim|required');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($faqId);
        }
        else
        {
            $faqTitle = $this->security->xss_clean($this->input->post('faqTitle'));
            $description = $this->security->xss_clean($this->input->post('description'));
            
            $faqInfo = array(
                'faqTitle' => $faqTitle,
                'description' => $description,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );
            
            $result = $this->faqbm->editFaq($faqInfo, $faqId);
            
            if($result == true)
            {
                // Notify the user who updated the FAQ
                $this->load->model('Notification_model', 'nm');

                    // Send notifications to users with roleId 19, 14, 25
                    $notificationMessage = "<strong>FAQ Confirmation:</strong> Update FAQ confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_faq_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }


                // Email notification to admin
                $adminEmail = 'dev.edumeta@gmail.com'; // Replace with actual admin email
                $adminSubject = "Alert - eduMETA THE i-SCHOOL FAQ Updated";
                $adminMessage = "An FAQ has been updated. ";
                $adminMessage .= "Title: {$faqTitle}, FAQ ID: {$faqId}. ";
                $adminMessage .= "Please visit the portal for details.";
                $adminHeaders = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                if (!mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders)) {
                    log_message('error', "Failed to send email to {$adminEmail} for FAQ ID {$faqId}");
                }

                $this->session->set_flashdata('success', 'Faq updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Faq updation failed');
            }
            
            redirect('faq/faqListing');
        }
    }
}
}

?>