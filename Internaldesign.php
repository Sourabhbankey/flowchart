<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Faq (FaqController)
 * Faq Class to control Faq related operations.
 * @author : Ashish 
 * @version : 1.5
 * @since : 11 Nov 2024
 */
class Internaldesign extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('internaldesign_model', 'inter');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Internaldesign';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('internaldesign/internaldesignListing');
    }
    
    /**
     * This function is used to load the faq list
     */
public function internaldesignListing()
{
/*    if (!$this->hasListAccess()) {
        $this->loadThis();
    } else {*/
        $searchText = '';
        if (!empty($this->input->post('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
        }
        $data['searchText'] = $searchText;

        // ✅ Get role and user ID from session
        $roleId = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');

        $this->load->library('pagination');

        $config["base_url"] = base_url() . "internaldesign/internaldesignListing";
        $config["per_page"] = 10;
        $config["uri_segment"] = 3;

        // ✅ Pass roleId and userId to model method
        $config["total_rows"] = $this->inter->internaldesignListingCount($searchText, $roleId, $userId);

        $config["num_links"] = 2;
        $config["use_page_numbers"] = TRUE;
        $config["reuse_query_string"] = TRUE;

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config["per_page"];

        // ✅ Pass roleId and userId to model method
        $data["records"] = $this->inter->internaldesignListing($searchText, $offset, $config["per_page"], $roleId, $userId);
        $data["links"] = $this->pagination->create_links();
        $data["start"] = ($config["total_rows"] > 0) ? ($offset + 1) : 0;
        $data["end"] = min($offset + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];

        $this->global['pageTitle'] = 'CodeInsect : Internal Design List';
        $this->loadViews("internaldesign/list", $this->global, $data, NULL);
    }
/*}*/



    /**
     * This function is used to load the add new form
     */
    function add()
    {
       /* if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            $this->global['pageTitle'] = 'CodeInsect : Add New TODO';
/*$data['users'] = $this->inter->getAllUsers();*/
            $this->loadViews("internaldesign/add", $this->global, NULL, NULL);
        }
   /* }*/
    
    /**
     * This function is used to add new user to the system
     */
   function addNewInternaldesign()
{
    /*if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {*/
        $this->load->library('form_validation');

        $this->form_validation->set_rules('internaldesignTitle', 'Title', 'trim|required');
        $this->form_validation->set_rules('description', 'Description', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $internaldesignTitle = $this->security->xss_clean($this->input->post('internaldesignTitle'));
            $description = $this->security->xss_clean($this->input->post('description'));
             $designStatus = $this->security->xss_clean($this->input->post('designStatus')) ?: 'Pending';
                            if (isset($_FILES['file']) && is_uploaded_file($_FILES["file"]["tmp_name"])) {
    $dir = dirname($_FILES["file"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
    
    if (rename($_FILES["file"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        $s3_file_link[] = $result_arr['ObjectURL'] ?? '';
    } else {
        $s3_file_link[] = '';
    }
} else {
    $s3_file_link[] = '';
}

            $internaldesignInfo = array(
                'internaldesignTitle' => $internaldesignTitle,
                'description' => $description,
                'createdBy' => $this->vendorId,
                'internaldesignattachment' => implode(',', $s3_file_link),
                 'designStatus' => $designStatus,
                'createdDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->inter->addNewInternaldesign($internaldesignInfo);
//print_r($internaldesignInfo);exit;
            if ($result) {

                $notificationMessage = "<strong>Internal Design Confirmation:</strong> New Internal Design confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where('roleId !=', 25)
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_internaldesign_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }

                $this->session->set_flashdata('success', 'New internaldesignTitle created successfully');
            } else {
                $this->session->set_flashdata('error', 'internaldesignTitle creation failed');
            }

            redirect('internaldesign/internaldesignListing');
        }
    }
/*}*/

    function view($internaldesignId = NULL)
    {
       /* if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            if($faqId == null)
            {
                redirect('internaldesign/internaldesignListing');
            }
            
            $data['internaldesignListingInfo'] = $this->inter->getinternaldesignListingInfo($internaldesignListingId);

            $this->global['pageTitle'] = 'CodeInsect : View Faq';
            
            $this->loadViews("internaldesign/view", $this->global, $data, NULL);
        }
  /*  }*/

    
    /**
     * This function is used load faq edit information
     * @param number $faqId : Optional : This is faq id
     */
   function edit($internaldesignId = NULL) {
    if($internaldesignId == null) {
        redirect('internaldesign/internaldesignListing');
    }
    
    $data['internaldesignInfo'] = $this->inter->getInternaldesignInfo($internaldesignId);
    // Fetch replies for this internal design
    $data['replies'] = $this->inter->getReplies($internaldesignId);
    
    $this->global['pageTitle'] = 'CodeInsect : Edit Internal Design';
    $this->loadViews("internaldesign/edit", $this->global, $data, NULL);
}

    /*}*/
    
    
    /**
     * This function is used to edit the user information
     */
    function editinternaldesign()
{
    $this->load->library('form_validation');

    $internaldesignId = $this->input->post('internaldesignId');

    $this->form_validation->set_rules('internaldesignTitle', 'Title', 'trim|required|max_length[50]');
    $this->form_validation->set_rules('description', 'Description', 'trim|required');

    if ($this->form_validation->run() == FALSE) {
        $this->edit($internaldesignId);
    } else {
        $internaldesignTitle = $this->security->xss_clean($this->input->post('internaldesignTitle'));
        $description = $this->security->xss_clean($this->input->post('description'));

        // ✅ New: Get design status from POST or default to Pending
        $designStatus = $this->security->xss_clean($this->input->post('designStatus')) ?: 'Pending';

        // ✅ Updated array to include designStatus
        $internaldesignInfo = array(
            'internaldesignTitle' => $internaldesignTitle,
            'description' => $description,
            'designStatus' => $designStatus,
            'updatedBy' => $this->vendorId,
            'updatedDtm' => date('Y-m-d H:i:s')
        );

        $result = $this->inter->editinternaldesign($internaldesignInfo, $internaldesignId);

        if ($result == true) {

               $notificationMessage = "<strong>Internal Design Confirmation:</strong> Update Internal Design confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where('roleId !=', 25)
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_internaldesign_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }


            $this->session->set_flashdata('success', 'Internal Design updated successfully');
        } else {
            $this->session->set_flashdata('error', 'Internal Design update failed');
        }

        redirect('internaldesign/internaldesignListing');
    }
}

   /* }*/
public function addReply() {
    // Get internal design id, reply text, and current user id
    $internaldesignId = $this->input->post('internaldesignId');
    $replyText = $this->input->post('replyText');
    $userId = $this->session->userdata('userId'); // adjust as needed

    $replyAttachment = '';
    if (!empty($_FILES['replyAttachment']['name'])) {
        // Get the directory and set a new name using timestamp
        $dir = dirname($_FILES["replyAttachment"]["tmp_name"]);
        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["replyAttachment"]["name"];
        
        // Move the uploaded file to the destination path
        rename($_FILES["replyAttachment"]["tmp_name"], $destination);
        $storeFolder = 'attachements'; // S3 folder name
        
        // Upload file to S3
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (!empty($result_arr['ObjectURL'])) {
            $replyAttachment = $result_arr['ObjectURL']; // Save the S3 URL as attachment
        } else {
            $replyAttachment = '';
        }
        
        // Optionally, delete the local file after successful S3 upload
        @unlink($destination);
    }

    // Build data array for the reply
    $replyData = array(
        'internaldesignId' => $internaldesignId,
        'replyText'        => $replyText,
        'replyAttachment'  => $replyAttachment,
        'createdBy'        => $userId,
        'createdDtm'        => date('Y-m-d H:i:s')
    );

    // Insert reply into database via the model
    $this->inter->addReply($replyData);
//print_r($replyData); exit;
    $this->session->set_flashdata('success', 'Reply added successfully!');
    redirect('internaldesign/edit/' . $internaldesignId);
}

public function downloadFile()
{
    $fileUrl = $this->input->get('file');
    $filename = basename($fileUrl);

    $fileContent = file_get_contents($fileUrl);
    if ($fileContent !== false) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Content-Length: ' . strlen($fileContent));
        echo $fileContent;
        exit;
    } else {
        show_error("Unable to download the file.", 500);
    }
}


}
?>