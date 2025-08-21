<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Internal Training (InternaltrainingController)
 * Internal Training Class to control Internal Training related operations.
 * @author : Ashish 
 * @version : 1.5
 * @since : 09 May 2025
 */
class Internaltraining extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('internaltraining_model', 'inter');
        $this->isLoggedIn();
        $this->module = 'Internaltraining';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('internaltraining/internaltrainingListing');
    }
    
    /**
     * This function is used to load the faq list
     */
public function internaltrainingListing()
{
/*    if (!$this->hasListAccess()) {
        $this->loadThis();
    } else {*/
        $searchText = '';
        if (!empty($this->input->post('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
        }
        $data['searchText'] = $searchText;

        //  Get role and user ID from session
        $roleId = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');

        $this->load->library('pagination');

        $config["base_url"] = base_url() . "internaltraining/internaltrainingListing";
        $config["per_page"] = 10;
        $config["uri_segment"] = 3;

        //  Pass roleId and userId to model method
        $config["total_rows"] = $this->inter->internaltrainingListingCount($searchText, $roleId, $userId);

        $config["num_links"] = 2;
        $config["use_page_numbers"] = TRUE;
        $config["reuse_query_string"] = TRUE;

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config["per_page"];

        //  Pass roleId and userId to model method
        $data["records"] = $this->inter->internaltrainingListing($searchText, $offset, $config["per_page"], $roleId, $userId);
        $data["links"] = $this->pagination->create_links();
        $data["start"] = ($config["total_rows"] > 0) ? ($offset + 1) : 0;
        $data["end"] = min($offset + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];

        $this->global['pageTitle'] = 'CodeInsect : Internal training List';
        $this->loadViews("internaltraining/list", $this->global, $data, NULL);
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
            $this->loadViews("internaltraining/add", $this->global, NULL, NULL);
        }
   /* }*/
    
    /**
     * This function is used to add new user to the system
     */
   function addNewInternaltraining()
{
    /*if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {*/
        $this->load->library('form_validation');

        $this->form_validation->set_rules('internaltrainingTitle', 'Title', 'trim|required');
        $this->form_validation->set_rules('description', 'Description', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $internaltrainingTitle = $this->security->xss_clean($this->input->post('internaltrainingTitle'));
             $recordedsession = $this->security->xss_clean($this->input->post('recordedsession'));
            $description = $this->security->xss_clean($this->input->post('description'));
            $internaltrainingStatus = $this->security->xss_clean($this->input->post('internaltrainingStatus')) ?: 'Pending';
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
                         $s3files = implode(',', $s3_file_link);

            $internaltrainingInfo = array(
                'internaltrainingTitle' => $internaltrainingTitle,
                'description' => $description,
                'createdBy' => $this->vendorId,
                'internaltrainingattachment'=>$s3files,
                 'internaltrainingStatus' => $internaltrainingStatus,
                 'recordedsession' => $recordedsession,
                'createdDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->inter->addNewInternaltraining($internaltrainingInfo);
//print_r($internaltrainingInfo);exit;
            if ($result) {
                $this->session->set_flashdata('success', 'New internaltrainingTitle created successfully');
            } else {
                $this->session->set_flashdata('error', 'internaltrainingTitle creation failed');
            }

            redirect('internaltraining/internaltrainingListing');
        }
    }
/*}*/

    function view($internaltrainingId = NULL)
    {
       /* if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            if($faqId == null)
            {
                redirect('internaltraining/internaltrainingListing');
            }
            
            $data['internaltrainingListingInfo'] = $this->inter->getinternaltrainingListingInfo($internaltrainingListingId);

            $this->global['pageTitle'] = 'CodeInsect : View Faq';
            
            $this->loadViews("internaltraining/view", $this->global, $data, NULL);
        }
  /*  }*/

    
    /**
     * This function is used load faq edit information
     * @param number $faqId : Optional : This is faq id
     */
   function edit($internaltrainingId = NULL) {
    if($internaltrainingId == null) {
        redirect('internaltraining/internaltrainingListing');
    }
    
    $data['internaltrainingInfo'] = $this->inter->getInternaltrainingInfo($internaltrainingId);
    // Fetch replies for this internal training
    //$data['replies'] = $this->inter->getReplies($internaltrainingId);
    
    $this->global['pageTitle'] = 'CodeInsect : Edit Internal training';
    $this->loadViews("internaltraining/edit", $this->global, $data, NULL);
}

    /*}*/
    
    
    /**
     * This function is used to edit the user information
     */
    function editinternaltraining()
{
    $this->load->library('form_validation');

    $internaltrainingId = $this->input->post('internaltrainingId');

    $this->form_validation->set_rules('internaltrainingTitle', 'Title', 'trim|required|max_length[50]');
    $this->form_validation->set_rules('description', 'Description', 'trim|required');

    if ($this->form_validation->run() == FALSE) {
        $this->edit($internaltrainingId);
    } else {
        $internaltrainingTitle = $this->security->xss_clean($this->input->post('internaltrainingTitle'));
        $description = $this->security->xss_clean($this->input->post('description'));
    $recordedsession = $this->security->xss_clean($this->input->post('recordedsession'));
        // New: Get training status from POST or default to Pending
        $internaltrainingStatus = $this->security->xss_clean($this->input->post('internaltrainingStatus')) ?: 'InActive';

        //  Updated array to include trainingStatus
        $internaltrainingInfo = array(
            'internaltrainingTitle' => $internaltrainingTitle,
            'description' => $description,
            'internaltrainingStatus' => $internaltrainingStatus,
            'updatedBy' => $this->vendorId,
             'recordedsession' => $recordedsession,
            'updatedDtm' => date('Y-m-d H:i:s')
        );

        $result = $this->inter->editinternaltraining($internaltrainingInfo, $internaltrainingId);
//print_r($internaltrainingInfo);exit;
        if ($result == true) {
            $this->session->set_flashdata('success', 'Internal training updated successfully');
        } else {
            $this->session->set_flashdata('error', 'Internal training update failed');
        }

        redirect('internaltraining/internaltrainingListing');
    }
}

   /* }*/
public function addReply() {
    // Get internal training id, reply text, and current user id
    $internaltrainingId = $this->input->post('internaltrainingId');
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
        'internaltrainingId' => $internaltrainingId,
        'replyText'        => $replyText,
        'replyAttachment'  => $replyAttachment,
        'createdBy'        => $userId,
        'createdDtm'        => date('Y-m-d H:i:s')
    );

    // Insert reply into database via the model
    $this->inter->addReply($replyData);
//print_r($replyData); exit;
    $this->session->set_flashdata('success', 'Reply added successfully!');
    redirect('internaltraining/edit/' . $internaltrainingId);
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