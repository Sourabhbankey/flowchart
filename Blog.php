<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Blog (Blog)
 * Blog Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Blog extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Blog_model', 'bmm');
        //$this->load->model('Branches_model', 'bm');
         $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Blog';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('blog/blogListing');
    }
    
    /**
     * This function is used to load the Support list
     */
    function blogListing()
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
            
            $count = $this->bmm->blogListingCount($searchText);

			$returns = $this->paginationCompress ( "blogListing/", $count, 500 );
            
            $data['records'] = $this->bmm->blogListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'Meeting : Blog';
            
            $this->loadViews("blog/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Blog';
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("blog/add", $this->global,  NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
   public function addNewBlog()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('blogTitle', 'Blog Title', 'trim|required|max_length[256]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $blogTitle = $this->security->xss_clean($this->input->post('blogTitle'));
            $blogLink = $this->security->xss_clean($this->input->post('blogLink'));
            $publishedDate = $this->security->xss_clean($this->input->post('publishedDate'));
            $publishedPlatformArray = $this->input->post('publishedPlatform');
            $publishedPlatform = $publishedPlatformArray ? implode(',', $this->security->xss_clean($publishedPlatformArray)) : '';

            $description = $this->security->xss_clean($this->input->post('description'));

            // File upload handling
            $s3files = '';
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                rename($_FILES["file"]["tmp_name"], $destination);
                $storeFolder = 'blog_images'; // Adjust folder name as needed

                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();
                $s3files = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
            }

            $blogInfo = [
                'blogTitle' => $blogTitle,
                'blogLink' => $blogLink,
                'publishedDate' => $publishedDate,
                'publishedPlatform' => $publishedPlatform,
                'description' => $description,
                'blogS3Image' => $s3files, // New field for S3 image URL
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
            ];

            $blogId = $this->bmm->addNewBlog($blogInfo);

            if ($blogId > 0) {
                // Send Notification to Admins or Specific Users
                $users = $this->nm->get_all_users(); // Fetch all users
                foreach ($users as $user) {
                    $message = "<strong>Blog:</strong> A new blog has been published: $blogTitle";
                    $this->nm->add_blog_notification($blogId, $message, $user['userId']);
                }

                $this->session->set_flashdata('success', 'New Blog created successfully');
            } else {
                $this->session->set_flashdata('error', 'Blog creation failed');
            }
            
            redirect('blog/blogListing');
        }
    }
}

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($blogId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($blogId == null)
            {
                redirect('blog/blogListing');
            }
            
            $data['blogInfo'] = $this->bmm->getblogInfo($blogId);
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Blogs : Edit Blog';
            
            $this->loadViews("blog/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
 
function editBlog()
{
    if(!$this->hasUpdateAccess())
    {
        $this->loadThis();
    }
    else
    {
        $this->load->library('form_validation');
        $this->load->model('Notification_model', 'nm'); // Load the notification model
        
        $blogId = $this->input->post('blogId');
        
        $this->form_validation->set_rules('blogTitle','Blog Title','trim|required|max_length[256]');
        $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($blogId);
        }
        else
        {
            $blogTitle = $this->security->xss_clean($this->input->post('blogTitle'));
            $description = $this->security->xss_clean($this->input->post('description'));
            /*-new-added-field-*/
            $blogLink = $this->security->xss_clean($this->input->post('blogLink'));
            $publishedDate = $this->security->xss_clean($this->input->post('publishedDate'));
            $publishedPlatformArray = $this->input->post('publishedPlatform');
            $publishedPlatform = '';
            if (is_array($publishedPlatformArray)) {
                $cleaned = array_map([$this->security, 'xss_clean'], $publishedPlatformArray);
                $publishedPlatform = implode(',', $cleaned);
            }
            /*-ENd-added-field-*/
            $blogInfo = array(
                'blogTitle' => $blogTitle,
                'blogLink' => $blogLink,
                'publishedDate' => $publishedDate,
                'publishedPlatform' => $publishedPlatform,
                'description' => $description,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );
            
            $result = $this->bmm->editBlog($blogInfo, $blogId);
            
            if($result == true)
            {
                /*// Notify the user who updated the blog
                $message = "You updated blog: {$blogTitle} (Blog ID: {$blogId})";
                $notificationResult = $this->nm->add_blog_notification($blogId, $message, $this->vendorId);
                if (!$notificationResult) {
                    log_message('error', "Failed to add notification for user {$this->vendorId} on blog ID {$blogId}");
                }*/

                // Notify all users
                $allUsers = $this->nm->get_all_users();
                if (empty($allUsers)) {
                    log_message('error', "No users found for blog ID {$blogId}");
                }
                foreach ($allUsers as $user) {
                    $message = "<strong>Blog updated:</strong> {$blogTitle} (Blog ID: {$blogId})";
                    $notificationResult = $this->nm->add_blog_notification($blogId, $message, $user['userId']);
                    if (!$notificationResult) {
                        log_message('error', "Failed to add notification for user {$user['userId']} on blog ID {$blogId}");
                    }
                }

                // Email notification to admin
                $adminEmail = 'dev.edumeta@gmail.com'; // Replace with actual admin email
                $adminSubject = "Alert - eduMETA THE i-SCHOOL Blog Updated";
                $adminMessage = "A blog has been updated. ";
                $adminMessage .= "Title: {$blogTitle}, Blog ID: {$blogId}. ";
                $adminMessage .= "Please visit the portal for details.";
                $adminHeaders = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                if (!mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders)) {
                    log_message('error', "Failed to send email to {$adminEmail} for blog ID {$blogId}");
                }

                $this->session->set_flashdata('success', 'Blog updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Blog updation failed');
            }
            
            redirect('blog/blogListing');
        }
    }
}
public function incrementOpenedCount()
{
    $blogId = $this->input->post('blogId');

    if ($blogId) {
        $this->load->model('bmm'); // Load your model
        $newCount = $this->bmm->incrementOpenedCount($blogId);

        echo json_encode(['status' => 'success', 'newCount' => $newCount]);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

}

?>