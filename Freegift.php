<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Freegift (TaskController)
 * Freegift Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 20 Jun 2024
 */
class Freegift extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Freegift_model', 'frgift');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Freegift';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('freegift/freegiftListing');
    }
    
    /**
     * This function is used to load the Freegift list
     */
    function freegiftListing()
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
            
            $count = $this->frgift->freegiftListingCount($searchText);

			$returns = $this->paginationCompress ( "freegiftListing/", $count, 500 );
            
            $data['records'] = $this->frgift->freegiftListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Freegift';
            
            $this->loadViews("freegift/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Freegift';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("freegift/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewFreegift()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('giftTitle','Gift Title','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $giftTitle = $this->security->xss_clean($this->input->post('giftTitle'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                /*-new-added-field-*/
                $approvedBy = $this->security->xss_clean($this->input->post('approvedBy'));
                $dateOfDespatch = $this->security->xss_clean($this->input->post('dateOfDespatch'));
                $modeOfDespatch = $this->security->xss_clean($this->input->post('modeOfDespatch'));
                $dateDelevery = $this->security->xss_clean($this->input->post('dateDelevery'));
                $delStatus = $this->security->xss_clean($this->input->post('delStatus'));
                /*-ENd-added-field-*/
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumbers = implode(',',$franchiseNumberArray);
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
                                
                            //}
                                $s3files = implode(',', $s3_file_link);
                $freegiftInfo = array('giftTitle'=>$giftTitle, 'approvedBy'=>$approvedBy, 'dateOfDespatch'=>$dateOfDespatch, 'modeOfDespatch'=>$modeOfDespatch, 'dateDelevery'=>$dateDelevery, 'franchiseNumber'=>$franchiseNumbers, 'delStatus'=>$delStatus, 'snapshotDespS3File'=>$s3files, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->frgift->addNewFreegift($freegiftInfo);

                if($result > 0) {
                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Freegift";
                                $message = 'Dear '.$branchDetail->applicantName.' ';
                                //$message = ' '.$description.' ';
                                $message .= 'You have been assigned a new freegift. BY- '.$this->session->userdata("name").' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$description.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to,$subject,$message,$headers);
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Freegift created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Freegift creation failed');
                }
                
                redirect('freegift/freegiftListing');
            }
        }
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($freegiftId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($freegiftId == null)
            {
                redirect('freegift/freegiftListing');
            }
            
            $data['freegiftInfo'] = $this->frgift->getFreegiftInfo($freegiftId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Freegift';
            
            $this->loadViews("freegift/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editFreegift()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $freegiftId = $this->input->post('freegiftId');
            
            $this->form_validation->set_rules('giftTitle','Freegift Title','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($freegiftId);
            }
            else
            {
                $giftTitle = $this->security->xss_clean($this->input->post('giftTitle'));
                $description = $this->security->xss_clean($this->input->post('description'));
                /*-new-added-field-*/
                $approvedBy = $this->security->xss_clean($this->input->post('approvedBy'));
                $dateOfDespatch = $this->security->xss_clean($this->input->post('dateOfDespatch'));
                $modeOfDespatch = $this->security->xss_clean($this->input->post('modeOfDespatch'));
                $dateDelevery = $this->security->xss_clean($this->input->post('dateDelevery'));
                $delStatus = $this->security->xss_clean($this->input->post('delStatus'));
                /*-ENd-added-field-*/
                $freegiftInfo = array('giftTitle'=>$giftTitle, 'approvedBy'=>$approvedBy, 'dateOfDespatch'=>$dateOfDespatch, 'modeOfDespatch'=>$modeOfDespatch, 'dateDelevery'=>$dateDelevery, 'delStatus'=>$delStatus, 'description'=>$description, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                $result = $this->frgift->editFreegift($freegiftInfo, $freegiftId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Freegift updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Freegift updation failed');
                }
                
                redirect('freegift/freegiftListing');
            }
        }
    }
}

?>