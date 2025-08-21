<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Qcdetails (QcdetailsController)
 * Qcdetails Class to control Qcdetails related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 12 May 2025
 */
class Offlineqcvisit extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Offlineqcvisit_model', 'ofqccheck');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Offlineqcvisit';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('offlineqcvisit/offlineqcvisitListing');
    }
    
    /**
     * This function is used to load the qcdetails list
     */
    function offlineqcvisitListing()
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
            
            $count = $this->ofqccheck->offlineqcvisitListingCount($searchText);

            $returns = $this->paginationCompress ( "offlineqcvisitListing/", $count, 10 );
            
            $data['records'] = $this->ofqccheck->offlineqcvisitListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Offlineqcvisit';
            
            $this->loadViews("offlineqcvisit/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Offlineqcvisit';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("offlineqcvisit/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewOfflineqcvisit()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('specific_concerns','Specific Concerns','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $franchise_number  = $this->security->xss_clean($this->input->post('franchise_number'));
                $franchise_name  = $this->security->xss_clean($this->input->post('franchise_name'));
                $franchise_owner_name  = $this->security->xss_clean($this->input->post('franchise_owner_name'));
                $branchFranchiseAssigned = $this->security->xss_clean($this->input->post('branchFranchiseAssigned'));

                $location = $this->security->xss_clean($this->input->post('location'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $state = $this->security->xss_clean($this->input->post('state'));
                $growth_manager = $this->security->xss_clean($this->input->post('growth_manager'));
                $date_of_inspection = $this->security->xss_clean($this->input->post('date_of_inspection'));
                $date_of_installation = $this->security->xss_clean($this->input->post('date_of_installation'));
                $qc_officer_1_name = $this->security->xss_clean($this->input->post('qc_officer_1_name'));
                $qc_officer_2_name = $this->security->xss_clean($this->input->post('qc_officer_2_name'));

                $presence_of_owner_admin = $this->security->xss_clean($this->input->post('presence_of_owner_admin'));
                $date_of_qc = $this->security->xss_clean($this->input->post('date_of_qc'));
                $qc_rating = $this->security->xss_clean($this->input->post('qc_rating'));
                $total_strength = $this->security->xss_clean($this->input->post('total_strength'));
                $pg_strength = $this->security->xss_clean($this->input->post('pg_strength'));
                $nursery_strength = $this->security->xss_clean($this->input->post('nursery_strength'));
                $kg1_strength = $this->security->xss_clean($this->input->post('kg1_strength'));
                $kg2_strength = $this->security->xss_clean($this->input->post('kg2_strength'));
                $daycare_strength = $this->security->xss_clean($this->input->post('daycare_strength'));
                $upgrade_opted = $this->security->xss_clean($this->input->post('upgrade_opted'));
                $branding_at_premise = $this->security->xss_clean($this->input->post('branding_at_premise'));

                $school_branding_within_radius = $this->security->xss_clean($this->input->post('school_branding_within_radius'));
                $gesture_of_branch = $this->security->xss_clean($this->input->post('gesture_of_branch'));
                $live_cleanliness = $this->security->xss_clean($this->input->post('live_cleanliness'));
                $furniture_condition = $this->security->xss_clean($this->input->post('furniture_condition'));
                $classroom_decoration = $this->security->xss_clean($this->input->post('classroom_decoration'));
                $rabl_counter = $this->security->xss_clean($this->input->post('rabl_counter'));
                $safety_points = $this->security->xss_clean($this->input->post('safety_points'));
                $camera_status = $this->security->xss_clean($this->input->post('camera_status'));

                $inquiry_book = $this->security->xss_clean($this->input->post('inquiry_book'));
                $register_maintained = $this->security->xss_clean($this->input->post('register_maintained'));
                $branding_backdrop_present = $this->security->xss_clean($this->input->post('branding_backdrop_present'));
                $feedback_owner_admin = $this->security->xss_clean($this->input->post('feedback_owner_admin'));
                $feedback_parents = $this->security->xss_clean($this->input->post('feedback_parents'));
                $feedback_teachers = $this->security->xss_clean($this->input->post('feedback_teachers'));
                $feedback_students = $this->security->xss_clean($this->input->post('feedback_students'));
                $doctor_contact_present = $this->security->xss_clean($this->input->post('doctor_contact_present'));
                $uniform_discipline_staff = $this->security->xss_clean($this->input->post('uniform_discipline_staff'));
                $uniform_discipline_students = $this->security->xss_clean($this->input->post('uniform_discipline_students'));
                $books_copies_check = $this->security->xss_clean($this->input->post('books_copies_check'));

                $additional_books_reference = $this->security->xss_clean($this->input->post('additional_books_reference'));
                $additional_books_note = $this->security->xss_clean($this->input->post('additional_books_note'));
                $premise_overview = $this->security->xss_clean($this->input->post('premise_overview'));
                $additional_programs = $this->security->xss_clean($this->input->post('additional_programs'));
                $specific_concerns = $this->security->xss_clean($this->input->post('specific_concerns'));
                $presence_of_owner_admin_status = $this->security->xss_clean($this->input->post('presence_of_owner_admin_status'));
                $upgrade_strength = $this->security->xss_clean($this->input->post('upgrade_strength'));

                $ofqcdetailsInfo = array(
                    'franchise_number'=>$franchise_number,
                    'franchise_name'=>$franchise_name,
                    'franchise_owner_name' => $franchise_owner_name,
                    'location' => $location,
                    'city' => $city,
                    'state' => $state,
                    'growth_manager' => $growth_manager,
                    'date_of_inspection' => $date_of_inspection,
                    'date_of_installation' => $date_of_installation,
                    'qc_officer_1_name' => $qc_officer_1_name,
                    'qc_officer_2_name' => $qc_officer_2_name,
                    'presence_of_owner_admin' => $presence_of_owner_admin,
                    'date_of_qc' => $date_of_qc,
                    'qc_rating' => $qc_rating,
                    'total_strength' => $total_strength,
                    'pg_strength' => $pg_strength,
                    'nursery_strength' => $nursery_strength,
                    'kg1_strength' => $kg1_strength,
                    'kg2_strength' => $kg2_strength,
                    'daycare_strength' => $daycare_strength,
                    'upgrade_opted' => $upgrade_opted,
                    'branding_at_premise' => $branding_at_premise,
                    'school_branding_within_radius' => $school_branding_within_radius,
                    'gesture_of_branch' => $gesture_of_branch,
                    'live_cleanliness' => $live_cleanliness,
                    'furniture_condition' => $furniture_condition,
                    'classroom_decoration' => $classroom_decoration,
                    'rabl_counter' => $rabl_counter,
                    'safety_points' => $safety_points,
                    'camera_status' => $camera_status,
                    'inquiry_book' => $inquiry_book,
                    'register_maintained' => $register_maintained,
                    'branding_backdrop_present' => $branding_backdrop_present,
                    'feedback_owner_admin' => $feedback_owner_admin,
                    'feedback_parents' => $feedback_parents,
                    'feedback_teachers' => $feedback_teachers,
                    'feedback_students' => $feedback_students,
                    'doctor_contact_present' => $doctor_contact_present,
                    'uniform_discipline_staff' => $uniform_discipline_staff,
                    'uniform_discipline_students' => $uniform_discipline_students,
                    'books_copies_check' => $books_copies_check,
                    'additional_books_reference' => $additional_books_reference,
                    'additional_books_note' => $additional_books_note,
                    'premise_overview' => $premise_overview,
                    'additional_programs' => $additional_programs,
                    'presence_of_owner_admin_status'=>$presence_of_owner_admin_status,
                    'upgrade_strength'=>$upgrade_strength,
                    'specific_concerns' => $specific_concerns,
                    'createdBy'=>$this->vendorId, 
                    'createdDtm'=>date('Y-m-d H:i:s')
                );
                
                $result = $this->ofqccheck->addNewOfflineqcvisit($ofqcdetailsInfo);
                
                if($result > 0) {
                    // ✅ Send Email to Admin
                    $to = 'dev.edumeta@gmail.com'; // Static admin email
                    $subject = "New Offline QC Visit Added - eduMETA THE i-SCHOOL";
                    $message = "Dear Admin,<br><br>";
                    $message .= "A new offline QC visit record has been added by {$this->session->userdata('name')}.<br>";
                    $message .= "<strong>QC Visit Details:</strong><br>";
                 
                    $message .= "Please visit the portal for more details.<br><br>";
                    $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                    $headers = "From: eduMETA Team <noreply@theischool.com>\r\n";
                    $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if (!mail($to, $subject, $message, $headers)) {
                        log_message('error', 'Failed to send email to ' . $to);
                    }

                    $this->session->set_flashdata('success', 'New QC Details created successfully');
                } else {
                    $this->session->set_flashdata('error', 'QC Details creation failed');
                }
                
                redirect('offlineqcvisit/offlineqcvisitListing');
            }
        }
    }
    function view($offlineVisitQcId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($offlineVisitQcId == null)
            {
                redirect('offlineqcvisit/offlineqcvisitListing');
            }
            
            $data['ofqcdetailsInfo'] = $this->ofqccheck->getOfflineqcvisitInfo($offlineVisitQcId);

            $this->global['pageTitle'] = 'CodeInsect : View Qcdetails';
            
            $this->loadViews("offlineqcvisit/view", $this->global, $data, NULL);
        }
    }

    
    /**
     * This function is used load qcdetails edit information
     * @param number $offlineVisitQcId : Optional : This is qcdetails id
     */
    function edit($offlineVisitQcId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($offlineVisitQcId == null)
            {
                redirect('offlineqcvisit/offlineqcvisitListing');
            }
            
            $data['ofqcdetailsInfo'] = $this->ofqccheck->getOfqcdetailsInfo($offlineVisitQcId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Event Galllery';
            
            $this->loadViews("offlineqcvisit/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
  function editOfflineqcvisit()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $offlineVisitQcId = $this->input->post('offlineVisitQcId');
            
            $this->form_validation->set_rules('specific_concerns','Specific Concerns','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($offlineVisitQcId);
            }
            else
            {
                $date_of_inspection = $this->security->xss_clean($this->input->post('date_of_inspection'));
                $date_of_installation = $this->security->xss_clean($this->input->post('date_of_installation'));
                $qc_officer_1_name = $this->security->xss_clean($this->input->post('qc_officer_1_name'));
                $qc_officer_2_name = $this->security->xss_clean($this->input->post('qc_officer_2_name'));

                $presence_of_owner_admin = $this->security->xss_clean($this->input->post('presence_of_owner_admin'));
                $date_of_qc = $this->security->xss_clean($this->input->post('date_of_qc'));
                $qc_rating = $this->security->xss_clean($this->input->post('qc_rating'));
                $total_strength = $this->security->xss_clean($this->input->post('total_strength'));
                $pg_strength = $this->security->xss_clean($this->input->post('pg_strength'));
                $nursery_strength = $this->security->xss_clean($this->input->post('nursery_strength'));
                $kg1_strength = $this->security->xss_clean($this->input->post('kg1_strength'));
                $kg2_strength = $this->security->xss_clean($this->input->post('kg2_strength'));
                $daycare_strength = $this->security->xss_clean($this->input->post('daycare_strength'));
                $upgrade_opted = $this->security->xss_clean($this->input->post('upgrade_opted'));
                $branding_at_premise = $this->security->xss_clean($this->input->post('branding_at_premise'));

                $school_branding_within_radius = $this->security->xss_clean($this->input->post('school_branding_within_radius'));
                $gesture_of_branch = $this->security->xss_clean($this->input->post('gesture_of_branch'));
                $live_cleanliness = $this->security->xss_clean($this->input->post('live_cleanliness'));
                $furniture_condition = $this->security->xss_clean($this->input->post('furniture_condition'));
                $classroom_decoration = $this->security->xss_clean($this->input->post('classroom_decoration'));
                $rabl_counter = $this->security->xss_clean($this->input->post('rabl_counter'));
                $safety_points = $this->security->xss_clean($this->input->post('safety_points'));
                $camera_status = $this->security->xss_clean($this->input->post('camera_status'));

                $inquiry_book = $this->security->xss_clean($this->input->post('inquiry_book'));
                $register_maintained = $this->security->xss_clean($this->input->post('register_maintained'));
                $branding_backdrop_present = $this->security->xss_clean($this->input->post('branding_backdrop_present'));
                $feedback_owner_admin = $this->security->xss_clean($this->input->post('feedback_owner_admin'));
                $feedback_parents = $this->security->xss_clean($this->input->post('feedback_parents'));
                $feedback_teachers = $this->security->xss_clean($this->input->post('feedback_teachers'));
                $feedback_students = $this->security->xss_clean($this->input->post('feedback_students'));
                $doctor_contact_present = $this->security->xss_clean($this->input->post('doctor_contact_present'));
                $uniform_discipline_staff = $this->security->xss_clean($this->input->post('uniform_discipline_staff'));
                $uniform_discipline_students = $this->security->xss_clean($this->input->post('uniform_discipline_students'));
                $books_copies_check = $this->security->xss_clean($this->input->post('books_copies_check'));

                $additional_books_reference = $this->security->xss_clean($this->input->post('additional_books_reference'));
                $additional_books_note = $this->security->xss_clean($this->input->post('additional_books_note'));
                $premise_overview = $this->security->xss_clean($this->input->post('premise_overview'));
                $additional_programs = $this->security->xss_clean($this->input->post('additional_programs'));
                $specific_concerns = $this->security->xss_clean($this->input->post('specific_concerns'));
                $presence_of_owner_admin_status = $this->security->xss_clean($this->input->post('presence_of_owner_admin_status'));
                $upgrade_strength = $this->security->xss_clean($this->input->post('upgrade_strength'));

                $ofqcdetailsInfo = array( 
                    'date_of_inspection' => $date_of_inspection,
                    'date_of_installation' => $date_of_installation,
                    'qc_officer_1_name' => $qc_officer_1_name,
                    'qc_officer_2_name' => $qc_officer_2_name,
                    'presence_of_owner_admin' => $presence_of_owner_admin,
                    'date_of_qc' => $date_of_qc,
                    'qc_rating' => $qc_rating,
                    'total_strength' => $total_strength,
                    'pg_strength' => $pg_strength,
                    'nursery_strength' => $nursery_strength,
                    'kg1_strength' => $kg1_strength,
                    'kg2_strength' => $kg2_strength,
                    'daycare_strength' => $daycare_strength,
                    'upgrade_opted' => $upgrade_opted,
                    'branding_at_premise' => $branding_at_premise,
                    'school_branding_within_radius' => $school_branding_within_radius,
                    'gesture_of_branch' => $gesture_of_branch,
                    'live_cleanliness' => $live_cleanliness,
                    'furniture_condition' => $furniture_condition,
                    'classroom_decoration' => $classroom_decoration,
                    'rabl_counter' => $rabl_counter,
                    'safety_points' => $safety_points,
                    'camera_status' => $camera_status,
                    'inquiry_book' => $inquiry_book,
                    'register_maintained' => $register_maintained,
                    'branding_backdrop_present' => $branding_backdrop_present,
                    'feedback_owner_admin' => $feedback_owner_admin,
                    'feedback_parents' => $feedback_parents,
                    'feedback_teachers' => $feedback_teachers,
                    'feedback_students' => $feedback_students,
                    'doctor_contact_present' => $doctor_contact_present,
                    'uniform_discipline_staff' => $uniform_discipline_staff,
                    'uniform_discipline_students' => $uniform_discipline_students,
                    'books_copies_check' => $books_copies_check,
                    'additional_books_reference' => $additional_books_reference,
                    'additional_books_note' => $additional_books_note,
                    'premise_overview' => $premise_overview,
                    'additional_programs' => $additional_programs,
                    'presence_of_owner_admin_status'=>$presence_of_owner_admin_status,
                    'upgrade_strength'=>$upgrade_strength,
                    'specific_concerns' => $specific_concerns,
                    'updatedBy'=>$this->vendorId, 
                    'updatedDtm'=>date('Y-m-d H:i:s')
                );
                
                $result = $this->ofqccheck->editQcdetails($ofqcdetailsInfo, $offlineVisitQcId);
                
                if($result == true)
                {
                    // ✅ Send Email to Admin
                    $to = 'dev.edumeta@gmail.com'; // Static admin email
                    $subject = "Offline QC Visit Updated - eduMETA THE i-SCHOOL";
                    $message = "Dear Admin,<br><br>";
                    $message .= "An offline QC visit record has been updated by {$this->session->userdata('name')}.<br>";
                    $message .= "<strong>QC Visit Details:</strong><br>";
                    $message .= "Franchise Number: {$this->input->post('franchise_number')}<br>";
                    $message .= "Franchise Name: {$this->input->post('franchise_name')}<br>";
                    $message .= "Date of QC: {$date_of_qc}<br>";
                    $message .= "QC Rating: {$qc_rating}<br>";
                    $message .= "Specific Concerns: {$specific_concerns}<br>";
                    $message .= "Please visit the portal for more details.<br><br>";
                    $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                    $headers = "From: eduMETA Team <noreply@theischool.com>\r\n";
                    $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if (!mail($to, $subject, $message, $headers)) {
                        log_message('error', 'Failed to send email to ' . $to);
                    }

                    $this->session->set_flashdata('success', 'Offline QC Visit updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Offline QC Visit update failed');
                }
                
                redirect('offlineqcvisit/offlineqcvisitListing');
            }
        }
    }



    public function getBranchDetails()
{
    $franchiseNumber = $this->input->post('franchiseNumber');
    if ($franchiseNumber) {
      //  $this->load->model('BranchModel'); // Replace with your model name
        $branchDetails = $this->ofqccheck->getBranchByFranchiseNumber($franchiseNumber);

        if (!empty($branchDetails)) {
            echo json_encode($branchDetails);
        } else {
            echo json_encode([]);
        }
    } else {
        echo json_encode([]);
    }
}
public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Fetch the users based on the franchise number
    $users = $this->ofqccheck->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

    // Generate HTML options for the response
    $options = '<option value="0">Select Role</option>';
    foreach ($users as $user) {
        $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
    }

    echo $options; // Output the options as HTML
}


}

?>