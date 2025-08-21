<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Onboardapplication (OnboardapplicationController)
 * Onboardapplication Class to control onboardapplication related operations.
 * @author : Ashish 
 * @version : 1.5
 * @since : 11 Nov 2024
 */
class Onboardapplication extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('onboardapplication_model', 'onboardapplicationbm');
        $this->load->model('Branches_model', 'bm'); // Load Branches_model for franchise numbers
        $this->isLoggedIn();
        $this->module = 'Onboardapplication';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('onboardapplication/add');
    }
    
    /**
     * This function is used to load the onboardapplication list
     */
    public function onboardapplicationListing()
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

            $config["base_url"] = base_url() . "onboardapplication/onboardapplicationListing";
            $config["per_page"] = 10;
            $config["uri_segment"] = 3;
            $config["total_rows"] = $this->onboardapplicationbm->onboardapplicationListingCount($searchText);
            $config["num_links"] = 2;
            $config["use_page_numbers"] = TRUE;  
            $config["reuse_query_string"] = TRUE;

            $this->pagination->initialize($config);

            $page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 1;
            $offset = ($page - 1) * $config["per_page"];

            $data["records"] = $this->onboardapplicationbm->onboardapplicationListing($searchText, $offset, $config["per_page"]);
            $data["links"] = $this->pagination->create_links();
            $data["start"] = ($config["total_rows"] > 0) ? ($offset + 1) : 0;
            $data["end"] = min($offset + $config["per_page"], $config["total_rows"]);
            $data["total_records"] = $config["total_rows"];

            $this->global['pageTitle'] = 'CodeInsect : Onboard Application';
            $this->loadViews("onboardapplication/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Onboard Application';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber(); // Fetch franchise numbers
            $this->loadViews("onboardapplication/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new onboard application to the system
     */
    function addNewonboardapplication()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('full_name', 'Name', 'trim|required');
            $this->form_validation->set_rules('franchiseNumber[]', 'Franchise Number', 'trim|required'); // Validate franchise number

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                // Fetch input data
                $input_fields = [
                    'full_name', 'email', 'dob', 'comm_address', 'city', 'state', 'pincode', 
                    'gender', 'alternate_contact_no', 'contact_person_number', 'branch_location', 
                    'branch_area', 'current_school_name', 'year_founded', 'current_school_address', 
                    'current_strength', 'total_experience', 'purpose_opening', 'skills_experience', 
                    'current_occupation', 'vision_with_edumeta', 'heard_about_edumeta', 
                    'additional_info', 'franchise_owner', 'org_type', 'franchise_applicant', 
                    'gstin','gsttype' ,'father_name', 'permanent_address', 'father_contact_no', 
                    'branch_full_address', 'spouse_name', 'spouse_contact_no', 'comm_current_address', 
                    'map_location', 'payment_mode', 'amount', 'reference_id', 'payment_remark', 
                    'payment_date', 'proposed_setup_date', 'advertising_plan', 
                    'proposed_inauguration_date', 'declaname', 'sodo', 'decsodoname', 
                    'clientname', 'nominated', 'nomibranch', 'nomidist', 'nomistate'
                ];

                $onboardapplicationInfo = [];
                foreach ($input_fields as $field) {
                    $onboardapplicationInfo[$field] = $this->security->xss_clean($this->input->post($field));
                }

                // Handle franchise numbers
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseNumbers = implode(',', $franchiseNumberArray);
                $onboardapplicationInfo['franchiseNumber'] = $franchiseNumbers;

                // Upload files to S3 and add file paths to the data array
                $onboardapplicationInfo['pan_card_photo_path'] = $this->uploadToS3('file');
                $onboardapplicationInfo['aadhar_front_photo_path'] = $this->uploadToS3('file2');
                $onboardapplicationInfo['aadhar_back_photo_path'] = $this->uploadToS3('file3');
                $onboardapplicationInfo['passport_photo_path'] = $this->uploadToS3('file4');
                $onboardapplicationInfo['payment_screenshot_path'] = $this->uploadToS3('file5');

                $onboardapplicationInfo['created_at'] = date('Y-m-d H:i:s');

                // Save data to the database
                $result = $this->onboardapplicationbm->addNewonboardapplication($onboardapplicationInfo);

                if ($result > 0) {
                    // Optional: Add email notification for franchise branches
                    if (!empty($franchiseNumberArray)) {
                        foreach ($franchiseNumberArray as $franchiseNumber) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if (!empty($branchDetail)) {
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - New Onboard Application Submission";
                                $message = "Dear {$branchDetail->applicantName},\n\n";
                                $message .= "A new onboard application has been submitted by {$onboardapplicationInfo['full_name']}.\n";
                                $message .= "Please visit the portal for details.\n";
                                $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                                mail($to, $subject, $message, $headers);
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'Submitted successfully');
                } else {
                    $this->session->set_flashdata('error', 'Onboard application creation failed');
                }

                redirect('onboardapplication/thankyou');
            }
        }
    }

    /**
     * Uploads file to S3 and returns the file URL.
     */
    private function uploadToS3($file_key)
    {
        if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== 0) {
            return ''; // Return empty if no file uploaded
        }

        $dir = dirname($_FILES[$file_key]['tmp_name']);
        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES[$file_key]['name'];
        rename($_FILES[$file_key]['tmp_name'], $destination);
        $storeFolder = 'attachements';

        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();

        return !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
    }

    /**
     * This function is used to display the Thank You page
     */
    public function thankyou()
    {
        $this->global['pageTitle'] = 'CodeInsect : Thank You';
        $this->loadViews("onboardapplication/thankyou", $this->global, NULL, NULL);
    }

    /**
     * This function is used to load onboard application view information
     */
    function view($frAppFormId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($frAppFormId == null)
            {
                redirect('onboardapplication/onboardapplicationListing');
            }
            
            $data['onboardapplicationInfo'] = $this->onboardapplicationbm->getonboardapplicationInfo($frAppFormId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber(); // Fetch franchise numbers

            $this->global['pageTitle'] = 'CodeInsect : View Onboard Application';
            
            $this->loadViews("onboardapplication/view", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to load onboard application edit information
     */
    function edit($frAppFormId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($frAppFormId == null)
            {
                redirect('onboardapplication/onboardapplicationListing');
            }
            
            $data['onboardapplicationInfo'] = $this->onboardapplicationbm->getonboardapplicationInfo($frAppFormId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber(); // Fetch franchise numbers

            $this->global['pageTitle'] = 'CodeInsect : Edit Onboard Application';
            
            $this->loadViews("onboardapplication/edit", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to edit the onboard application information
     */
   function editonboardapplication()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $frAppFormId = $this->input->post('frAppFormId');
            
            $this->form_validation->set_rules('full_name', 'Name', 'trim|required|max_length[50]');
            $this->form_validation->set_rules('franchiseNumber[]', 'Franchise Number', 'trim|required'); // Validate franchise number
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($frAppFormId);
            }
            else
            {
                // Fetch input data
                $input_fields = [
                    'full_name', 'email', 'dob', 'comm_address', 'city', 'state', 'pincode', 
                    'gender', 'alternate_contact_no', 'contact_person_number', 'branch_location', 
                    'branch_area', 'current_school_name', 'year_founded', 'current_school_address', 
                    'current_strength', 'total_experience', 'purpose_opening', 'skills_experience', 
                    'current_occupation', 'vision_with_edumeta', 'heard_about_edumeta', 
                    'additional_info', 'franchise_owner', 'org_type', 'franchise_applicant', 
                    'gstin','gsttype','father_name', 'permanent_address', 'father_contact_no', 
                    'branch_full_address', 'spouse_name', 'spouse_contact_no', 'comm_current_address', 
                    'map_location', 'payment_mode', 'amount', 'reference_id', 'payment_remark', 
                    'payment_date', 'proposed_setup_date', 'advertising_plan', 
                    'proposed_inauguration_date', 'declaname', 'sodo', 'decsodoname', 
                    'clientname', 'nominated', 'nomibranch', 'nomidist', 'nomistate'
                ];

                $onboardapplicationInfo = [];
                foreach ($input_fields as $field) {
                    $onboardapplicationInfo[$field] = $this->security->xss_clean($this->input->post($field));
                }

                // Handle franchise numbers
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseNumbers = implode(',', $franchiseNumberArray);
                $onboardapplicationInfo['franchiseNumber'] = $franchiseNumbers;

                // Upload files to S3 if new files are provided
                $onboardapplicationInfo['pan_card_photo_path'] = $this->input->post('existing_pan_card_photo_path') ?: $this->uploadToS3('file');
                $onboardapplicationInfo['aadhar_front_photo_path'] = $this->input->post('existing_aadhar_front_photo_path') ?: $this->uploadToS3('file2');
                $onboardapplicationInfo['aadhar_back_photo_path'] = $this->input->post('existing_aadhar_back_photo_path') ?: $this->uploadToS3('file3');
                $onboardapplicationInfo['passport_photo_path'] = $this->input->post('existing_passport_photo_path') ?: $this->uploadToS3('file4');
                $onboardapplicationInfo['payment_screenshot_path'] = $this->input->post('existing_payment_screenshot_path') ?: $this->uploadToS3('file5');

                $onboardapplicationInfo['updatedBy'] = $this->vendorId;
                $onboardapplicationInfo['updatedDtm'] = date('Y-m-d H:i:s');

                $result = $this->onboardapplicationbm->editonboardapplication($onboardapplicationInfo, $frAppFormId);
                
                if($result == true)
                {
                    // ✅ Send Email to Admin
                    $to = 'dev.edumeta@gmail.com'; // Static admin email
                    $subject = "Onboard Application Updated - eduMETA THE i-SCHOOL";
                    $message = "Dear Admin,<br><br>";
                    $message .= "An onboard application has been updated by {$this->session->userdata('name')}.<br>";
                    $message .= "<strong>Application Details:</strong><br>";
                    $message .= "Please visit the portal for more details.<br><br>";
                    $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                    $headers = "From: Edumeta Team <noreply@theischool.com>\r\n";
                    $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if (!mail($to, $subject, $message, $headers)) {
                        log_message('error', 'Failed to send email to ' . $to);
                    }

                    $this->session->set_flashdata('success', 'Onboard application updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Onboard application update failed');
                }
                
                redirect('onboardapplication/onboardapplicationListing');
            }
        }
    }
}
?>