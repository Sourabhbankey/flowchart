<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Task (TaskController)
 * Task Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Acattachment extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Acattachment_model', 'at');
        $this->isLoggedIn();
        $this->module = 'Acattachment';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('acattachment/acattachmentListing');
    }
    
    /**
     * This function is used to load the task list
     */
    function acattachmentListing()
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
            
            $count = $this->at->acattachmentListingCount($searchText);

			$returns = $this->paginationCompress ( "acattachmentListing/", $count, 10 );
            
            $data['records'] = $this->at->acattachmentListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Acattachment';
            
            $this->loadViews("acattachment/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Acattachment';

            $this->loadViews("acattachment/add", $this->global, NULL, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewAcattachment()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('acattachmentTitle','Attachment Title','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $acattachmentTitle = $this->security->xss_clean($this->input->post('acattachmentTitle'));
                $description = $this->security->xss_clean($this->input->post('description'));
//                 $filesCount = count($_FILES['files']['name']);
//                             for($i = 0; $i < $filesCount; $i++){
//                                $_FILES['file']['name']     = $_FILES['files']['name'][$i];
//
//                                 $_FILES['file']['type']     = $_FILES['files']['type'][$i];
//                                 $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$i];
//                                 $_FILES['file']['error']    = $_FILES['files']['error'][$i];
//                                 $_FILES['file']['size']     = $_FILES['files']['size'][$i];

                                $dir = dirname($_FILES["file"]["tmp_name"]);
                                $destination = $dir . DIRECTORY_SEPARATOR . time().'-'.$_FILES["file"]["name"];
                                rename($_FILES["file"]["tmp_name"], $destination);
                                $storeFolder = 'attachements';

                                $s3Result = $this->s3_upload->upload_file($destination,$storeFolder);
                                $result_arr = $s3Result->toArray();
                                if(!empty($result_arr['ObjectURL'])) {
                                      $s3_file_link[] = $result_arr['ObjectURL'];
                                } else {
                                    $s3_file_link[] = '';
                                }

//                             }
                            $s3files = implode(',', $s3_file_link);
                $acattachmentInfo = array('acattachmentTitle'=>$acattachmentTitle, 'description'=>$description, 'acattachmentS3File'=>$s3files, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                $result = $this->at->addNewAcattachment($acattachmentInfo);
                
                if($result > 0) {
                    $this->session->set_flashdata('success', 'New Attachment created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Attachment creation failed');
                }
                
                redirect('acattachment/acattachmentListing');
            }
        }
    }

    
    /**
     * This function is used load attachment edit information
     * @param number $attachmentId : Optional : This is attachment id
     */
    function edit($acattachmentId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($attachmentId == null)
            {
                redirect('acattachment/acattachmentListing');
            }
            
            $data['acattachmentInfo'] = $this->at->getAcattachmentInfo($acattachmentId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Acattachment';
            
            $this->loadViews("acattachment/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editAcattachment()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $attachmentId = $this->input->post('acattachmentId');
            
            $this->form_validation->set_rules('acattachmentTitle','Attachment Title','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($attachmentId);
            }
            else
            {
                $acattachmentTitle = $this->security->xss_clean($this->input->post('acattachmentTitle'));
                $description = $this->security->xss_clean($this->input->post('description'));
                
                $acattachmentInfo = array('acattachmentTitle'=>$attachmentTitle, 'description'=>$description, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->at->editAttachment($acattachmentInfo, $acattachmentId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Attachment updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Attachment updation failed');
                }
                
                redirect('acattachment/acattachmentListing');
            }
        }
    }
}

?>