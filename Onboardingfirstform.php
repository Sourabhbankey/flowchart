<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Onboardingfirstform (OnboardingfirstformController)
 * Onboardingfirstform Class to control Onboardingfirstform related operations.
 * @author : Ashish 
 * @version : 1.5
 * @since : 11 Nov 2024
 */
class Onboardingfirstform extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Onboardingfirstform_model', 'onboardfirst');
        $this->load->model('Branches_model', 'bm'); // Load Branches_model for franchise numbers
        $this->isLoggedIn();
        $this->module = 'Onboardingfirstform'; // Changed module name to match class
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('onboardingfirstform/add');
    }
    
    /**
     * This function is used to load the onboardingfirstform list
     */
    public function onboardingfirstformListing()
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

            $config["base_url"] = base_url() . "onboardingfirstform/onboardingfirstformListing";
            $config["per_page"] = 10;
            $config["uri_segment"] = 3;
            $config["total_rows"] = $this->onboardfirst->onboardingfirstformListingCount($searchText);
            $config["num_links"] = 2;
            $config["use_page_numbers"] = TRUE;  
            $config["reuse_query_string"] = TRUE;

            $this->pagination->initialize($config);

            $page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 1;
            $offset = ($page - 1) * $config["per_page"];

            $data["records"] = $this->onboardfirst->onboardingfirstformListing($searchText, $offset, $config["per_page"]);
            $data["links"] = $this->pagination->create_links();
            $data["start"] = ($config["total_rows"] > 0) ? ($offset + 1) : 0;
            $data["end"] = min($offset + $config["per_page"], $config["total_rows"]);
            $data["total_records"] = $config["total_rows"];

            $this->global['pageTitle'] = 'CodeInsect : First Form';
            $this->loadViews("onboardingfirstform/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Onboarding Form';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber(); // Fetch franchise numbers
            $this->loadViews("onboardingfirstform/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new onboarding form to the system
     */
    function addNewOnboardingfirstform()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('full_name','Full Name','trim|required');
            $this->form_validation->set_rules('franchiseNumber[]','Franchise Number','trim|required'); // Add validation for franchise number

            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $email = $this->security->xss_clean($this->input->post('email'));
                $contact = $this->security->xss_clean($this->input->post('contact'));
                $gender = $this->security->xss_clean($this->input->post('gender'));
                $full_name = $this->security->xss_clean($this->input->post('full_name'));
                $alternate_contact = $this->security->xss_clean($this->input->post('alternate_contact'));
                $dob = $this->security->xss_clean($this->input->post('dob'));
                $communication_address = $this->security->xss_clean($this->input->post('communication_address'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $state = $this->security->xss_clean($this->input->post('state'));
                $pincode = $this->security->xss_clean($this->input->post('pincode'));
                $pan_card_no = $this->security->xss_clean($this->input->post('pan_card_no'));
                $aadhar_card = $this->security->xss_clean($this->input->post('aadhar_card'));
                $nationality = $this->security->xss_clean($this->input->post('nationality'));
                $permanent_address = $this->security->xss_clean($this->input->post('permanent_address'));
                $pcity = $this->security->xss_clean($this->input->post('pcity'));
                $pstate = $this->security->xss_clean($this->input->post('pstate'));
                $ppincode = $this->security->xss_clean($this->input->post('ppincode'));
                $marital_status = $this->security->xss_clean($this->input->post('marital_status'));
                $spouse = $this->security->xss_clean($this->input->post('spouse'));
                $number_of_children = $this->security->xss_clean($this->input->post('number_of_children'));
                $highest_education = $this->security->xss_clean($this->input->post('highest_education'));
                $qualifications = $this->security->xss_clean($this->input->post('qualifications'));
                $university = $this->security->xss_clean($this->input->post('university'));
                $year_of_qualification = $this->security->xss_clean($this->input->post('year_of_qualification'));
                $certificate_course_award = $this->security->xss_clean($this->input->post('certificate_course_award'));
                $year_received = $this->security->xss_clean($this->input->post('year_received'));
                $awarded_by = $this->security->xss_clean($this->input->post('awarded_by'));
                $english_spoken = $this->security->xss_clean($this->input->post('english_spoken'));
                $other_skills = $this->security->xss_clean($this->input->post('other_skills'));
                $mathematics_proficiency = $this->security->xss_clean($this->input->post('mathematics_proficiency'));
                $english_written_proficiency = $this->security->xss_clean($this->input->post('english_written_proficiency'));
                $current_employer_name = $this->security->xss_clean($this->input->post('current_employer_name'));
                $current_position = $this->security->xss_clean($this->input->post('current_position'));
                $current_date_joined = $this->security->xss_clean($this->input->post('current_date_joined'));
                $current_business_address = $this->security->xss_clean($this->input->post('current_business_address'));
                $current_monthly_income = $this->security->xss_clean($this->input->post('current_monthly_income'));
                $previous_employer_name = $this->security->xss_clean($this->input->post('previous_employer_name'));
                $previous_monthly_income = $this->security->xss_clean($this->input->post('previous_monthly_income'));
                $previous_date_joined = $this->security->xss_clean($this->input->post('previous_date_joined'));
                $previous_business_address = $this->security->xss_clean($this->input->post('previous_business_address'));
                $previous_last_position = $this->security->xss_clean($this->input->post('previous_last_position'));
                $previous_date_left = $this->security->xss_clean($this->input->post('previous_date_left'));
                $previous_reasons_for_leaving = $this->security->xss_clean($this->input->post('previous_reasons_for_leaving'));
                $particular_select = $this->security->xss_clean($this->input->post('particular_select'));
                $father_full_name = $this->security->xss_clean($this->input->post('father_full_name'));
                $father_pan_card = $this->security->xss_clean($this->input->post('father_pan_card'));
                $father_nationality = $this->security->xss_clean($this->input->post('father_nationality'));
                $father_aadhar_card = $this->security->xss_clean($this->input->post('father_aadhar_card'));
                $father_mobile = $this->security->xss_clean($this->input->post('father_mobile'));
                $father_position = $this->security->xss_clean($this->input->post('father_position'));
                $father_organization = $this->security->xss_clean($this->input->post('father_organization'));
                $father_business_address = $this->security->xss_clean($this->input->post('father_business_address'));
                $father_monthly_income = $this->security->xss_clean($this->input->post('father_monthly_income'));
                $spouse_dob = $this->security->xss_clean($this->input->post('spouse_dob'));
                $spouse_position = $this->security->xss_clean($this->input->post('spouse_position'));
                $spouse_organization = $this->security->xss_clean($this->input->post('spouse_organization'));
                $spouse_business_address = $this->security->xss_clean($this->input->post('spouse_business_address'));
                $spouse_monthly_income = $this->security->xss_clean($this->input->post('spouse_monthly_income'));
                $average_monthly_income = $this->security->xss_clean($this->input->post('average_monthly_income'));
                $applied_before = $this->security->xss_clean($this->input->post('applied_before'));
                $application_details = $this->security->xss_clean($this->input->post('application_details'));
                $ideal_centre_reason = $this->security->xss_clean($this->input->post('ideal_centre_reason'));
                $source_info = $this->security->xss_clean($this->input->post('source_info'));
                $current_centre = $this->security->xss_clean($this->input->post('current_centre'));
                $reason_for_applying = $this->security->xss_clean($this->input->post('reason_for_applying'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber')); // Get franchise numbers
                $franchiseNumbers = implode(',', $franchiseNumberArray); // Convert array to comma-separated string

                // File upload for Aadhar
                if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
                    $dir = dirname($_FILES["file"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                    rename($_FILES["file"]["tmp_name"], $destination);
                    $storeFolder = 'attachements';

                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();

                    if (!empty($result_arr['ObjectURL'])) {
                        $s3_file_link[] = $result_arr['ObjectURL'];
                    } else {
                        $s3_file_link[] = '';
                    }

                    $s3files = implode(',', $s3_file_link);
                } else {
                    $s3files = '';
                }

                // File upload for PAN
                if (isset($_FILES["file2"]) && $_FILES["file2"]["error"] == 0) {
                    $dir = dirname($_FILES["file2"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file2"]["name"];
                    rename($_FILES["file2"]["tmp_name"], $destination);
                    $storeFolder = 'attachements';

                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();

                    if (!empty($result_arr['ObjectURL'])) {
                        $s3_file_link2[] = $result_arr['ObjectURL'];
                    } else {
                        $s3_file_link2[] = '';
                    }

                    $s3files2 = implode(',', $s3_file_link2);
                } else {
                    $s3files2 = '';
                }

                $onboardingfirstformInfo = array(
                    'email' => $email, 
                    'contact' => $contact,
                    'gender' => $gender,
                    'full_name' => $full_name, 
                    'alternate_contact' => $alternate_contact,
                    'dob' => $dob,
                    'communication_address' => $communication_address, 
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode, 
                    'pan_card_no' => $pan_card_no,
                    'aadhar_card' => $aadhar_card,
                    'nationality' => $nationality, 
                    'permanent_address' => $permanent_address,
                    'pcity' => $pcity,
                    'pstate' => $pstate, 
                    'ppincode' => $ppincode,
                    'marital_status' => $marital_status,
                    'spouse' => $spouse, 
                    'number_of_children' => $number_of_children,
                    'highest_education' => $highest_education,
                    'qualifications' => $qualifications, 
                    'university' => $university,
                    'year_of_qualification' => $year_of_qualification,
                    'certificate_course_award' => $certificate_course_award, 
                    'year_received' => $year_received,
                    'awarded_by' => $awarded_by,
                    'english_spoken' => $english_spoken, 
                    'other_skills' => $other_skills,
                    'mathematics_proficiency' => $mathematics_proficiency,
                    'english_written_proficiency' => $english_written_proficiency, 
                    'current_employer_name' => $current_employer_name,
                    'current_position' => $current_position,
                    'current_date_joined' => $current_date_joined, 
                    'current_business_address' => $current_business_address,
                    'current_monthly_income' => $current_monthly_income,
                    'previous_employer_name' => $previous_employer_name, 
                    'previous_monthly_income' => $previous_monthly_income,
                    'previous_date_joined' => $previous_date_joined,
                    'previous_business_address' => $previous_business_address, 
                    'previous_last_position' => $previous_last_position,
                    'previous_date_left' => $previous_date_left,
                    'previous_reasons_for_leaving' => $previous_reasons_for_leaving, 
                    'particular_select' => $particular_select,
                    'father_full_name' => $father_full_name,
                    'father_pan_card' => $father_pan_card, 
                    'father_nationality' => $father_nationality,
                    'father_aadhar_card' => $father_aadhar_card,
                    'father_mobile' => $father_mobile, 
                    'father_position' => $father_position,
                    'father_organization' => $father_organization,
                    'father_business_address' => $father_business_address, 
                    'father_monthly_income' => $father_monthly_income,
                    'spouse_dob' => $spouse_dob,
                    'spouse_position' => $spouse_position,
                    'spouse_organization' => $spouse_organization,
                    'spouse_business_address' => $spouse_business_address,
                    'spouse_monthly_income' => $spouse_monthly_income,
                    'average_monthly_income' => $average_monthly_income,
                    'applied_before' => $applied_before,
                    'application_details' => $application_details,
                    'ideal_centre_reason' => $ideal_centre_reason,
                    'source_info' => $source_info,
                    'current_centre' => $current_centre,
                    'reason_for_applying' => $reason_for_applying,
                    'aadhar_file' => $s3files,
                    'pan_file' => $s3files2,
                    'franchiseNumber' => $franchiseNumbers, // Add franchise number to the data array
                    'created_at' => date('Y-m-d H:i:s')
                );
                
                $result = $this->onboardfirst->addNewOnboardingfirstform($onboardingfirstformInfo);
                
                if ($result > 0) {
                    // Email notification to franchise
                    $franchiseNumber = $franchiseNumbers;
                    if (!empty($franchiseNumber)) {
                        $franchiseNumberArray = explode(',', $franchiseNumber);
                        foreach ($franchiseNumberArray as $franchiseNum) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNum);
                            if (!empty($branchDetail)) {
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL New Onboarding Form Submitted";
                                $message = 'Dear ' . $branchDetail->applicantName . ', ';
                                $message .= 'A new onboarding form has been submitted by ' . $full_name . '. ';
                                $message .= 'Please visit the portal for details.';
                                $headers = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to, $subject, $message, $headers);
                            }
                        }
                    }

                    // Email notification to admin
                    $adminEmail = 'admin@example.com'; // Replace with actual admin email
                    $adminSubject = "Alert - eduMETA THE i-SCHOOL New Onboarding Form Submitted";
                    $adminMessage = 'A new onboarding form has been submitted by ' . $full_name . '. ';
                    $adminMessage .= 'Franchise Numbers: ' . $franchiseNumbers . '. ';
                    $adminMessage .= 'Please visit the portal for details.';
                    $adminHeaders = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                    mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);

                    $this->session->set_flashdata('success', 'Submitted successfully');
                    log_message('debug', 'Redirecting to thankyou page');
                    redirect('onboardingfirstform/thankyou');
                } else {
                    $this->session->set_flashdata('error', 'Submission failed');
                    redirect('onboardingfirstform/add');
                }
            }
        }
    }
   
    public function thankyou()
    {
        $this->global['pageTitle'] = 'CodeInsect : Thank You';
        $this->loadViews("onboardingfirstform/thankyou", $this->global, NULL, NULL);
    }

    /**
     * This function is used to load onboardingfirstform edit information
     * @param number $onboardingfirstformId : Optional : This is onboardingfirstform id
     */
    function edit($onboardingfirstformId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($onboardingfirstformId == null)
            {
                redirect('onboardingfirstform/onboardingfirstformListing');
            }
            
            $data['onboardingfirstformInfo'] = $this->onboardfirst->getonboardingfirstformInfo($onboardingfirstformId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber(); // Fetch franchise numbers for edit form

            $this->global['pageTitle'] = 'CodeInsect : Edit Onboarding Form';
            
            $this->loadViews("onboardingfirstform/edit", $this->global, $data, NULL);
        }
    }

    function view($onboardingfirstformId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($onboardingfirstformId == null)
            {
                redirect('onboardingfirstform/onboardingfirstformListing');
            }

            $data['onboardingfirstformInfo'] = $this->onboardfirst->getonboardingfirstformInfo($onboardingfirstformId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber(); // Fetch franchise numbers for view

            $this->global['pageTitle'] = 'CodeInsect : View Onboarding Form';

            $this->loadViews("onboardingfirstform/view", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to edit the onboardingfirstform information
     */
   /**
 * This function is used to edit the onboardingfirstform information
 */
function editOnboardingfirstform()
{
    if(!$this->hasUpdateAccess())
    {
        $this->loadThis();
    }
    else
    {
        $this->load->library('form_validation');
        
        $onboardingfirstformId = $this->input->post('onboardingfirstformId');
        
        $this->form_validation->set_rules('full_name','Full Name','trim|required|max_length[50]');
        $this->form_validation->set_rules('franchiseNumber[]','Franchise Number','trim|required');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($onboardingfirstformId);
        }
        else
        {
            $email = $this->security->xss_clean($this->input->post('email'));
            $contact = $this->security->xss_clean($this->input->post('contact'));
            $gender = $this->security->xss_clean($this->input->post('gender'));
            $full_name = $this->security->xss_clean($this->input->post('full_name'));
            $alternate_contact = $this->security->xss_clean($this->input->post('alternate_contact'));
            $dob = $this->security->xss_clean($this->input->post('dob'));
            $communication_address = $this->security->xss_clean($this->input->post('communication_address'));
            $city = $this->security->xss_clean($this->input->post('city'));
            $state = $this->security->xss_clean($this->input->post('state'));
            $pincode = $this->security->xss_clean($this->input->post('pincode'));
            $pan_card_no = $this->security->xss_clean($this->input->post('pan_card_no'));
            $aadhar_card = $this->security->xss_clean($this->input->post('aadhar_card'));
            $nationality = $this->security->xss_clean($this->input->post('nationality'));
            $permanent_address = $this->security->xss_clean($this->input->post('permanent_address'));
            $pcity = $this->security->xss_clean($this->input->post('pcity'));
            $pstate = $this->security->xss_clean($this->input->post('pstate'));
            $ppincode = $this->security->xss_clean($this->input->post('ppincode'));
            $marital_status = $this->security->xss_clean($this->input->post('marital_status'));
            $spouse = $this->security->xss_clean($this->input->post('spouse'));
            $number_of_children = $this->security->xss_clean($this->input->post('number_of_children'));
            $highest_education = $this->security->xss_clean($this->input->post('highest_education'));
            $qualifications = $this->security->xss_clean($this->input->post('qualifications'));
            $university = $this->security->xss_clean($this->input->post('university'));
            $year_of_qualification = $this->security->xss_clean($this->input->post('year_of_qualification'));
            $certificate_course_award = $this->security->xss_clean($this->input->post('certificate_course_award'));
            $year_received = $this->security->xss_clean($this->input->post('year_received'));
            $awarded_by = $this->security->xss_clean($this->input->post('awarded_by'));
            $english_spoken = $this->security->xss_clean($this->input->post('english_spoken'));
            $other_skills = $this->security->xss_clean($this->input->post('other_skills'));
            $mathematics_proficiency = $this->security->xss_clean($this->input->post('mathematics_proficiency'));
            $english_written_proficiency = $this->security->xss_clean($this->input->post('english_written_proficiency'));
            $current_employer_name = $this->security->xss_clean($this->input->post('current_employer_name'));
            $current_position = $this->security->xss_clean($this->input->post('current_position'));
            $current_date_joined = $this->security->xss_clean($this->input->post('current_date_joined'));
            $current_business_address = $this->security->xss_clean($this->input->post('current_business_address'));
            $current_monthly_income = $this->security->xss_clean($this->input->post('current_monthly_income'));
            $previous_employer_name = $this->security->xss_clean($this->input->post('previous_employer_name'));
            $previous_monthly_income = $this->security->xss_clean($this->input->post('previous_monthly_income'));
            $previous_date_joined = $this->security->xss_clean($this->input->post('previous_date_joined'));
            $previous_business_address = $this->security->xss_clean($this->input->post('previous_business_address'));
            $previous_last_position = $this->security->xss_clean($this->input->post('previous_last_position'));
            $previous_date_left = $this->security->xss_clean($this->input->post('previous_date_left'));
            $previous_reasons_for_leaving = $this->security->xss_clean($this->input->post('previous_reasons_for_leaving'));
            $particular_select = $this->security->xss_clean($this->input->post('particular_select'));
            $father_full_name = $this->security->xss_clean($this->input->post('father_full_name'));
            $father_pan_card = $this->security->xss_clean($this->input->post('father_pan_card'));
            $father_nationality = $this->security->xss_clean($this->input->post('father_nationality'));
            $father_aadhar_card = $this->security->xss_clean($this->input->post('father_aadhar_card'));
            $father_mobile = $this->security->xss_clean($this->input->post('father_mobile'));
            $father_position = $this->security->xss_clean($this->input->post('father_position'));
            $father_organization = $this->security->xss_clean($this->input->post('father_organization'));
            $father_business_address = $this->security->xss_clean($this->input->post('father_business_address'));
            $father_monthly_income = $this->security->xss_clean($this->input->post('father_monthly_income'));
            $spouse_dob = $this->security->xss_clean($this->input->post('spouse_dob'));
            $spouse_position = $this->security->xss_clean($this->input->post('spouse_position'));
            $spouse_organization = $this->security->xss_clean($this->input->post('spouse_organization'));
            $spouse_business_address = $this->security->xss_clean($this->input->post('spouse_business_address'));
            $spouse_monthly_income = $this->security->xss_clean($this->input->post('spouse_monthly_income'));
            $average_monthly_income = $this->security->xss_clean($this->input->post('average_monthly_income'));
            $applied_before = $this->security->xss_clean($this->input->post('applied_before'));
            $application_details = $this->security->xss_clean($this->input->post('application_details'));
            $ideal_centre_reason = $this->security->xss_clean($this->input->post('ideal_centre_reason'));
            $source_info = $this->security->xss_clean($this->input->post('source_info'));
            $current_centre = $this->security->xss_clean($this->input->post('current_centre'));
            $reason_for_applying = $this->security->xss_clean($this->input->post('reason_for_applying'));
            $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
            $franchiseNumbers = implode(',', $franchiseNumberArray);
            
            $onboardingfirstformInfo = array(
                'email' => $email, 
                'contact' => $contact,
                'gender' => $gender,
                'full_name' => $full_name, 
                'alternate_contact' => $alternate_contact,
                'dob' => $dob,
                'communication_address' => $communication_address, 
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode, 
                'pan_card_no' => $pan_card_no,
                'aadhar_card' => $aadhar_card,
                'nationality' => $nationality, 
                'permanent_address' => $permanent_address,
                'pcity' => $pcity,
                'pstate' => $pstate, 
                'ppincode' => $ppincode,
                'marital_status' => $marital_status,
                'spouse' => $spouse, 
                'number_of_children' => $number_of_children,
                'highest_education' => $highest_education,
                'qualifications' => $qualifications, 
                'university' => $university,
                'year_of_qualification' => $year_of_qualification,
                'certificate_course_award' => $certificate_course_award, 
                'year_received' => $year_received,
                'awarded_by' => $awarded_by,
                'english_spoken' => $english_spoken, 
                'other_skills' => $other_skills,
                'mathematics_proficiency' => $mathematics_proficiency,
                'english_written_proficiency' => $english_written_proficiency, 
                'current_employer_name' => $current_employer_name,
                'current_position' => $current_position,
                'current_date_joined' => $current_date_joined, 
                'current_business_address' => $current_business_address,
                'current_monthly_income' => $current_monthly_income,
                'previous_employer_name' => $previous_employer_name, 
                'previous_monthly_income' => $previous_monthly_income,
                'previous_date_joined' => $previous_date_joined,
                'previous_business_address' => $previous_business_address, 
                'previous_last_position' => $previous_last_position,
                'previous_date_left' => $previous_date_left,
                'previous_reasons_for_leaving' => $previous_reasons_for_leaving, 
                'particular_select' => $particular_select,
                'father_full_name' => $father_full_name,
                'father_pan_card' => $father_pan_card, 
                'father_nationality' => $father_nationality,
                'father_aadhar_card' => $father_aadhar_card,
                'father_mobile' => $father_mobile, 
                'father_position' => $father_position,
                'father_organization' => $father_organization,
                'father_business_address' => $father_business_address, 
                'father_monthly_income' => $father_monthly_income,
                'spouse_dob' => $spouse_dob,
                'spouse_position' => $spouse_position,
                'spouse_organization' => $spouse_organization,
                'spouse_business_address' => $spouse_business_address,
                'spouse_monthly_income' => $spouse_monthly_income,
                'average_monthly_income' => $average_monthly_income,
                'applied_before' => $applied_before,
                'application_details' => $application_details,
                'ideal_centre_reason' => $ideal_centre_reason,
                'source_info' => $source_info,
                'current_centre' => $current_centre,
                'reason_for_applying' => $reason_for_applying,
                'franchiseNumber' => $franchiseNumbers,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );
            
            $result = $this->onboardfirst->editOnboardingfirstform($onboardingfirstformInfo, $onboardingfirstformId);
            
            if($result == true)
            {
                // Email notification
                $franchiseNumber = $franchiseNumbers; // Already fetched from form input
                if (!empty($franchiseNumber)) {
                    $franchiseNumberArray = explode(',', $franchiseNumber);
                    foreach ($franchiseNumberArray as $franchiseNum) {
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNum);
                        if (!empty($branchDetail)) {
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL Onboarding Form Updated";
                            $message = 'Dear ' . $branchDetail->applicantName . ', ';
                            $message .= 'Onboarding form information has been updated. BY- ' . $this->session->userdata("name") . ' ';
                            $message .= 'Please visit the portal for details.';
                            $headers = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                            mail($to, $subject, $message, $headers);
                        }
                    }
                }

                $this->session->set_flashdata('success', 'Onboarding form updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Onboarding form update failed');
            }
            
            redirect('onboardingfirstform/onboardingfirstformListing');
        }
    }
}
}
?>