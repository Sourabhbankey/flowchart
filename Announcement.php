<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Announcement (AnnouncementController)
 * Announcement Class to control Announcement related operations.
 * @author : Ashish 
 * @version : 1
 * @since : 24 Jul 2024
 */
class Announcement extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Announcement_model', 'impnoti');
        $this->isLoggedIn();
        $this->module = 'Announcement';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('announcement/announcementListing');
    }
    
    /**
     * This function is used to load the announcement list
     */
   public function announcementListing()
{
    if (!$this->hasListAccess()) {
        $this->loadThis();
    } else {
        // Get search text
        $searchText = '';
        if (!empty($this->input->post('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
        }
        $data['searchText'] = $searchText;
        
        // Load pagination library
        $this->load->library('pagination');
        
        // Get the total number of records for pagination
        $count = $this->impnoti->announcementListingCount($searchText);
        
        // Pagination configuration
        $config = array();
        $config["base_url"] = base_url("announcement/announcementListing");
        $config["total_rows"] = $count;
        $config["per_page"] = 10;
        $config["uri_segment"] = 3; // the page segment is expected at index 3 of the URI
        
        // Initialize pagination
        $this->pagination->initialize($config);

        // Get the current page number
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        // Fetch records for the current page
        $data['records'] = $this->impnoti->announcementListing($searchText, $config['per_page'], $page);

        // Get the pagination links
        $data['pagination'] = $this->pagination->create_links();

        // Record range and total
        $data['start'] = $page + 1;
        $data['end'] = min($page + $config['per_page'], $config['total_rows']);
        $data['total_records'] = $config['total_rows'];

        // Set the page title
        $this->global['pageTitle'] = 'CodeInsect : Announcement';

        // Load the view
        $this->loadViews("announcement/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Announcement';

            $this->loadViews("announcement/add", $this->global, NULL, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
  function addNewAnnouncement()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('announcementName', 'Title', 'trim|required|max_length[500]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[10024]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $announcementName = $this->security->xss_clean($this->input->post('announcementName'));
            $description = $this->security->xss_clean($this->input->post('description'));
            $annattachmentS3File = $this->security->xss_clean($this->input->post('annattachmentS3File'));

            // File upload handling
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                rename($_FILES["file"]["tmp_name"], $destination);
                $storeFolder = 'attachements';

                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();

                $s3files = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
            } else {
                $s3files = '';
            }

            $announcementInfo = array(
                'announcementName' => $announcementName,
                'annattachmentS3File' => $s3files,
                'description' => $description,
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->impnoti->addNewAnnouncement($announcementInfo);
if ($result > 0) {
    $this->session->set_flashdata('success', 'New Announcement created successfully');

    // Send notification
    $this->load->model('Notification_model', 'nm');
    
    // ✅ Get all users (not just admin)
    $allUsers = $this->nm->get_all_users(); 

    foreach ($allUsers as $user) {
        $message = "<strong>New announcement:</strong> " . $announcementName;
        $this->nm->add_announcement_notification($result, $message, $user['userId']); 
    }
} else {
    $this->session->set_flashdata('error', 'Announcement creation failed');
}

redirect('announcement/announcementListing');

    }
}
}

    /**
     * This function is used load announcement edit information
     * @param number $announcementId : Optional : This is announcement id
     */
    function edit($announcementId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($announcementId == null)
            {
                redirect('announcement/announcementListing');
            }
            
            $data['announcementInfo'] = $this->impnoti->getAnnouncementInfo($announcementId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Announcement';
            
            $this->loadViews("announcement/edit", $this->global, $data, NULL);
        }
    }
     function view($announcementId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($announcementId == null)
            {
                redirect('announcement/announcementListing');
            }
            
            $data['announcementInfo'] = $this->impnoti->getAnnouncementInfo($announcementId);

            $this->global['pageTitle'] = 'CodeInsect : View Announcement';
            
            $this->loadViews("announcement/view", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
  function editAnnouncement()
{
    if(!$this->hasUpdateAccess())
    {
        $this->loadThis();
    }
    else
    {
        $this->load->library('form_validation');
        $this->load->model('Notification_model', 'nm'); // Load the notification model
        
        $announcementId = $this->input->post('announcementId');
        
        $this->form_validation->set_rules('announcementName','Title','trim|required|max_length[500]');
        $this->form_validation->set_rules('description','Description','trim|required|max_length[10024]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($announcementId);
        }
        else
        {
            $announcementName = $this->security->xss_clean($this->input->post('announcementName'));
            $description = $this->security->xss_clean($this->input->post('description'));
            
            $announcementInfo = array(
                'announcementName' => $announcementName,
                'description' => $description,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );
            
            $result = $this->impnoti->editAnnouncement($announcementInfo, $announcementId);
            
            if($result == true)
            {
                // Notify the user who updated the announcement
                $message = "You updated announcement: {$announcementName} (Announcement ID: {$announcementId})";
                $notificationResult = $this->nm->add_announcement_notification($announcementId, $message, $this->vendorId);
                if (!$notificationResult) {
                    log_message('error', "Failed to add notification for user {$this->vendorId} on announcement ID {$announcementId}");
                }

                // Notify all users
                $allUsers = $this->nm->get_all_users();
                if (empty($allUsers)) {
                    log_message('error', "No users found for announcement ID {$announcementId}");
                }
                foreach ($allUsers as $user) {
                    $message = "<strong>Announcement updated:</strong> {$announcementName} (Announcement ID: {$announcementId})";
                    $notificationResult = $this->nm->add_announcement_notification($announcementId, $message, $user['userId']);
                    if (!$notificationResult) {
                        log_message('error', "Failed to add notification for user {$user['userId']} on announcement ID {$announcementId}");
                    }
                }

                // Email notification to admin
                $adminEmail = 'dev.edumeta@gmail.com'; // Replace with actual admin email
                $adminSubject = "Alert - eduMETA THE i-SCHOOL Announcement Updated";
                $adminMessage = "An announcement has been updated. ";
                $adminMessage .= "Title: {$announcementName}, Announcement ID: {$announcementId}. ";
                $adminMessage .= "Please visit the portal for details.";
                $adminHeaders = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                if (!mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders)) {
                    log_message('error', "Failed to send email to {$adminEmail} for announcement ID {$announcementId}");
                }

                $this->session->set_flashdata('success', 'Announcement updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Announcement updation failed');
            }
            
            redirect('announcement/announcementListing');
        }
    }
}
}

?>