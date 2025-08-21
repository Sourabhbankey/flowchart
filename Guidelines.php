<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Guidelines (Guidelines)
 * Guidelines Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 13 May 2025
 */
class Guidelines extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Guidelines_model', 'glf');
        //$this->load->model('Branches_model', 'bm');
         $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Guidelines';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('guidelines/guidelinesListing');
    }
    
    /**
     * This function is used to load the Support list
     */
    function guidelinesListing()
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
            
            $count = $this->glf->guidelinesListingCount($searchText);

			$returns = $this->paginationCompress ( "guidelinesListing/", $count, 500 );
            
            $data['records'] = $this->glf->guidelinesListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'Guidelines : Guidelines';
            
            $this->loadViews("guidelines/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Guidelines';
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("guidelines/add", $this->global,  NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
     public function addNewGuidelines()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('guidelinesTitle', 'Guidelines Title', 'trim|required|max_length[256]');
            
            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                $guidelinesTitle = $this->security->xss_clean($this->input->post('guidelinesTitle'));
                $publishedDate = $this->security->xss_clean($this->input->post('publishedDate'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $guidelineCategoryType = $this->security->xss_clean($this->input->post('guidelineCategoryType'));

                // Handle S3 file upload
                $s3files = '';
                if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
                    $dir = dirname($_FILES["file"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                    rename($_FILES["file"]["tmp_name"], $destination);
                    $storeFolder = 'attachements';

                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3files = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
                }

                $guidelinesInfo = [
                    'guidelinesTitle' => $guidelinesTitle,
                    'guidelineS3File' => $s3files,
                    'publishedDate' => $publishedDate,
                    'guidelineCategoryType' => $guidelineCategoryType,
                    'description' => $description,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                ];

                $guidelinesId = $this->glf->addNewGuidelines($guidelinesInfo);

                if ($guidelinesId > 0) {
                    // ✅ Send Email to Admin
                    $to = 'dev.edumeta@gmail.com';
                    $subject = "New Guidelines Created - eduMETA THE i-SCHOOL";
                    $message = "Dear Admin,<br><br>";
                    $message .= "A new guideline has been created by {$this->session->userdata('name')}.<br>";
                    $message .= "<strong>Guideline Details:</strong><br>";
                   
                    $message .= "Please visit the portal for more details.<br><br>";
                    $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                    $headers = "From: Edumeta Team <noreply@theischool.com>\r\n";
                    $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if (!mail($to, $subject, $message, $headers)) {
                        log_message('error', 'Failed to send email to ' . $to);
                    }

                    $this->session->set_flashdata('success', 'New Guidelines created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Guidelines creation failed');
                }
                
                redirect('guidelines/guidelinesListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($guidelinesId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($guidelinesId == null)
            {
                redirect('guidelinesGuidelines/guidelinesListing');
            }
            
            $data['guidelinesInfo'] = $this->glf->getguidelinesInfo($guidelinesId);
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Guidelines : Edit Guidelines';
            
            $this->loadViews("guidelines/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
     function editGuidelines()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $guidelinesId = $this->input->post('guidelinesId');
            
            $this->form_validation->set_rules('guidelinesTitle','Guidelines Title','trim|required|max_length[256]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($guidelinesId);
            }
            else
            {
                $guidelinesTitle = $this->security->xss_clean($this->input->post('guidelinesTitle'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $guidelineS3File = $this->security->xss_clean($this->input->post('guidelineS3File'));
                $publishedDate = $this->security->xss_clean($this->input->post('publishedDate'));
                $guidelineCategoryType = $this->security->xss_clean($this->input->post('guidelineCategoryType'));

                // Handle S3 file upload for edit (if a new file is provided)
                $s3files = $guidelineS3File; // Retain existing file unless new one is uploaded
                if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
                    $dir = dirname($_FILES["file"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                    rename($_FILES["file"]["tmp_name"], $destination);
                    $storeFolder = 'attachements';

                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3files = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : $s3files;
                }

                $guidelinesInfo = array(
                    'guidelinesTitle' => $guidelinesTitle,
                    'guidelineS3File' => $s3files,
                    'publishedDate' => $publishedDate,
                    'guidelineCategoryType' => $guidelineCategoryType,
                    'description' => $description,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->glf->editGuidelines($guidelinesInfo, $guidelinesId);
                
                if($result == true)
                {
                    // ✅ Send Email to Admin
                    $to = 'dev.edumeta@gmail.com';
                    $subject = "Guidelines Updated - eduMETA THE i-SCHOOL";
                    $message = "Dear Admin,<br><br>";
                    $message .= "A guideline has been updated by {$this->session->userdata('name')}.<br>";
                    $message .= "<strong>Guideline Details:</strong><br>";
                   
                    $message .= "Please visit the portal for more details.<br><br>";
                    $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                    $headers = "From: Edumeta Team <noreply@theischool.com>\r\n";
                    $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if (!mail($to, $subject, $message, $headers)) {
                        log_message('error', 'Failed to send email to ' . $to);
                    }

                    $this->session->set_flashdata('success', 'Guidelines updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Guidelines updation failed');
                }
                
                redirect('guidelines/guidelinesListing');
            }
        }
    }

}

?>