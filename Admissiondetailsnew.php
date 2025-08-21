<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Admissiondetails (AdmissiondetailsController)
 * Admissiondetails Class to control Admissiondetails related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 14 June 2024
 */
class Admissiondetailsnew extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admissiondetailsnew_model', 'admdetnew');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Staff_model', 'stf');
        $this->isLoggedIn();
        $this->load->library('pagination');
        $this->module = 'Admissiondetails';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('admissiondetailsnew/admissiondetailsListing');
    }

    /**
     * This function is used to load the Admissiondetails list
     */


    public function admissiondetailsListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        // Get the franchise filter from the GET request
        $franchiseFilter = $this->input->get('franchiseNumber');
        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }

        // Pagination configuration
        $config = array();
        $config['base_url'] = base_url('admissiondetailsnew/admissiondetailsListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        if ($userRole == '14' || $userRole == '1' || $userRole == '20' || $userRole == '28') { // Admin
            // Admin logic to fetch all records or filter by franchise
            if ($franchiseFilter) {
                $config['total_rows'] = $this->admdetnew->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                $data['records'] = $this->admdetnew->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->admdetnew->getTotalTrainingRecordsCount();
                $data['records'] = $this->admdetnew->getAllTrainingRecords($config['per_page'], $page);
            }
        } else if ($userRole == '15' || $userRole == '13') {
            if ($franchiseFilter) {
                // Use franchise filter
                $config['total_rows'] = $this->admdetnew->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                $data['records'] = $this->admdetnew->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
            } else {
                // Use role-based filter
                $config['total_rows'] = $this->admdetnew->getTotalTrainingRecordsCountByRole($userId);
                $data['records'] = $this->admdetnew->getTrainingRecordsByRole($userId, $config['per_page'], $page);
            }
        } else {
            // Logic for other roles (not Admin, Role 15, or Role 13)
            $franchiseNumber = $this->admdetnew->getFranchiseNumberByUserId($userId);
            if ($franchiseNumber) {
                if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                    $config['total_rows'] = $this->admdetnew->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->admdetnew->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->admdetnew->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->admdetnew->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                }
            } else {
                $data['records'] = []; // Handle the case where franchise number is not found
            }
        }

        // Initialize pagination
        $data["serial_no"] = $page + 1;
        $this->pagination->initialize($config);
        $data["links"] = $this->pagination->create_links();
        $data["start"] = $page + 1;
        $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];
        $data['pagination'] = $this->pagination->create_links();
        $data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

        // Load the view with the data
        $this->loadViews("admissiondetailsnew/list", $this->global, $data, NULL);
    }


    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Admissiondetails Session 2025-26';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $data['users'] = $this->admdetnew->getUser();
            $data['classes'] = $this->admdetnew->getClasses();
          $data['sections'] = $this->admdetnew->getAllSections();
            $this->loadViews("admissiondetailsnew/add", $this->global, $data, NULL);
        }
    }


    public function getAdmissionChartData()
    {
        $year = $this->input->get('year', TRUE);
        $franchiseFilter = $this->input->get('franchiseFilter', TRUE);
        $userRole = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');

        $admissions = array_fill(0, 12, 0); // Initialize array for 12 months

        $this->db->select("MONTH(dateOfAdmission) as month, COUNT(*) as count");
        $this->db->from('tbl_admission_details_2526');
        $this->db->where("YEAR(dateOfAdmission)", $year);

        // Apply franchise filter if provided
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }

        // Role-based restrictions
        if ($userRole == '25') {
            // Franchisee: Only their own franchise
            $franchiseNumber = $this->session->userdata('franchiseNumber');
            $this->db->where('franchiseNumber', $franchiseNumber);
        } elseif ($userRole == '15') {
            // Growth: Only branches assigned to them
            $this->db->where('brspFranchiseAssigned', $userId);
        }

        $this->db->group_by('MONTH(dateOfAdmission)');
        $query = $this->db->get();
        $results = $query->result();

        // Populate the admissions array
        foreach ($results as $row) {
            $monthIndex = (int)$row->month - 1; // Convert to 0-based index
            $admissions[$monthIndex] = (int)$row->count;
        }

        // Return JSON response
        echo json_encode(['admissions' => $admissions]);
    }

    // Fetch franchises for the filter dropdown
    public function getFranchisesForFilter()
    {
        $userRole = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');

        $this->db->select('franchiseNumber');
        $this->db->from('tbl_branches');
        $this->db->where('currentStatus', '1'); // Only active branches

        // Role-based restrictions
        if ($userRole == '25') {
            // Franchisee: Only their own franchise
            $franchiseNumber = $this->session->userdata('franchiseNumber');
            $this->db->where('franchiseNumber', $franchiseNumber);
        } elseif ($userRole == '15') {
            // Growth: Only branches assigned to them
            $this->db->where('branchFranchiseAssigned', $userId);
        }

        $this->db->order_by('franchiseNumber', 'ASC');
        $query = $this->db->get();
        $franchises = $query->result();

        echo json_encode(['franchises' => $franchises]);
    }
    /**
     * This function is used to add new user to the system
     */
    function addNewAdmissiondetails()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('name', 'Student  Name', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('remark', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $name = $this->security->xss_clean($this->input->post('name'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                /*-new-added-field-*/
                $enrollNum = $this->security->xss_clean($this->input->post('enrollNum'));
               $sectionName = $this->security->xss_clean($this->input->post('section_name'));
                $class = $this->security->xss_clean($this->input->post('class_name'));
                $dateOfAdmission = $this->security->xss_clean($this->input->post('dateOfAdmission'));
                $program = $this->security->xss_clean($this->input->post('program'));
                $birthday = $this->security->xss_clean($this->input->post('birthday'));
                $age = $this->security->xss_clean($this->input->post('age'));
                $gender = $this->security->xss_clean($this->input->post('gender'));
                $fathername = $this->security->xss_clean($this->input->post('fathername'));
                $fatheremail = $this->security->xss_clean($this->input->post('fatheremail'));
                $fatherMobile_no = $this->security->xss_clean($this->input->post('fatherMobile_no'));
                $mothername = $this->security->xss_clean($this->input->post('mothername'));
                $motheremail = $this->security->xss_clean($this->input->post('motheremail'));
                $motherMobile_no = $this->security->xss_clean($this->input->post('motherMobile_no'));
                $bloodGroup = $this->security->xss_clean($this->input->post('bloodGroup'));
                $motherTongue = $this->security->xss_clean($this->input->post('motherTongue'));
                $religion = $this->security->xss_clean($this->input->post('religion'));
                $caste = $this->security->xss_clean($this->input->post('caste'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $state = $this->security->xss_clean($this->input->post('state'));
                $address = $this->security->xss_clean($this->input->post('address'));
                $previousSchool = $this->security->xss_clean($this->input->post('previousSchool'));
                /*--Newfield--*/
                $totalFee = $this->security->xss_clean($this->input->post('totalFee'));

                // /new field


                $formFee = $this->security->xss_clean($this->input->post('formFee'));
                $admissionFee = $this->security->xss_clean($this->input->post('admissionFee'));
                $admissionFeedate = $this->security->xss_clean($this->input->post('admissionFeedate'));
                $siblingDiscount = $this->security->xss_clean($this->input->post('siblingDiscount'));
                $lumpsumDiscount = $this->security->xss_clean($this->input->post('lumpsumDiscount'));
                $annualcharges = $this->security->xss_clean($this->input->post('annualcharges'));
                $annualfeepayment = $this->security->xss_clean($this->input->post('annualfeepayment'));
                $annualchargesreceived = $this->security->xss_clean($this->input->post('annualchargesreceived'));
                $netFee = $this->security->xss_clean($this->input->post('netFee'));
                $installment1Due = $this->security->xss_clean($this->input->post('installment1Due'));
                $installment1received = $this->security->xss_clean($this->input->post('installment1received'));
                $installmentpayment1date = $this->security->xss_clean($this->input->post('installmentpayment1date'));
                $installment2due = $this->security->xss_clean($this->input->post('installment2due'));
                $installment2dueon = $this->security->xss_clean($this->input->post('installment2dueon'));
                $installment2amountreceived = $this->security->xss_clean($this->input->post('installment2amountreceived'));
                $installment2paidon = $this->security->xss_clean($this->input->post('installment2paidon'));
                $installment3 = $this->security->xss_clean($this->input->post('installment3'));
                $installment3amountreceived = $this->security->xss_clean($this->input->post('installment3amountreceived'));
                $installment3paidon = $this->security->xss_clean($this->input->post('installment3paidon'));
                $installment4 = $this->security->xss_clean($this->input->post('installment4'));
                $instllment4duedate = $this->security->xss_clean($this->input->post('instllment4duedate'));
                $installment4amountreceived = $this->security->xss_clean($this->input->post('installment4amountreceived'));
                $installment4paidon = $this->security->xss_clean($this->input->post('installment4paidon'));
                $totalamount = $this->security->xss_clean($this->input->post('totalamount'));
                $receivedamt = $this->security->xss_clean($this->input->post('receivedamt'));
                $dueamt = $this->security->xss_clean($this->input->post('dueamt'));
                $schooltransport = $this->security->xss_clean($this->input->post('schooltransport'));
                $kitgivendate = $this->security->xss_clean($this->input->post('kitgivendate'));
                $kitgiven = $this->security->xss_clean($this->input->post('kitgiven'));
                $comment = $this->security->xss_clean($this->input->post('comment'));
                $referencename = $this->security->xss_clean($this->input->post('referencename'));
                $school = $this->security->xss_clean($this->input->post('school'));
                $daycare = $this->security->xss_clean($this->input->post('daycare'));



                /*-ENd-added-field-*/
                $remark = $this->security->xss_clean($this->input->post('remark'));
                $franchiseNumbers = implode(',', $franchiseNumberArray);
                $admissiondetailsInfo = array(
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'name' => $name,
                    'enrollNum' => $enrollNum,
                    'class' => $class,
                    'section' => $sectionName,
                    'dateOfAdmission' => $dateOfAdmission,
                    'program' => $program,
                    'birthday' => $birthday,
                    'age' => $age,
                    'gender' => $gender,
                    'fathername' => $fathername,
                    'fatheremail' => $fatheremail,
                    'fatherMobile_no' => $fatherMobile_no,
                    'mothername' => $mothername,
                    'motheremail' => $motheremail,
                    'motherMobile_no' => $motherMobile_no,
                    'bloodGroup' => $bloodGroup,
                    'motherTongue' => $motherTongue,
                    'religion' => $religion,
                    'caste' => $caste,
                    'city' => $city,
                    'state' => $state,
                    'totalFee' => $totalFee,
                    'address' => $address,
                    'previousSchool' => $previousSchool,
                    'franchiseNumber' => $franchiseNumbers,
                    'remark' => $remark,

                    'formFee' => $formFee,
                    'admissionFee' => $admissionFee,
                    'admissionFeedate' => $admissionFeedate,
                    'siblingDiscount' => $siblingDiscount,
                    'lumpsumDiscount' => $lumpsumDiscount,
                    'annualcharges' => $annualcharges,
                    'annualfeepayment' => $annualfeepayment,
                    'annualchargesreceived' => $annualchargesreceived,
                    'netFee' => $netFee,
                    'installment1Due' => $installment1Due,
                    'installment1received' => $installment1received,
                    'installmentpayment1date' => $installmentpayment1date,
                    'installment2due' => $installment2due,
                    'installment2dueon' => $installment2dueon,
                    'installment2amountreceived' => $installment2amountreceived,
                    'installment2paidon' => $installment2paidon,
                    'installment3' => $installment3,
                    'installment3amountreceived' => $installment3amountreceived,
                    'installment3paidon' => $installment3paidon,
                    'installment4' => $installment4,

                    'instllment4duedate' => $instllment4duedate,
                    'installment4amountreceived' => $installment4amountreceived,
                    'installment4paidon' => $installment4paidon,
                    'totalamount' => $totalamount,
                    'receivedamt' => $receivedamt,
                    'dueamt' => $dueamt,
                    'schooltransport' => $schooltransport,
                    'kitgivendate' => $kitgivendate,
                    'kitgiven' => $kitgiven,
                    'comment' => $comment,

                    'referencename' => $referencename,
                    'school' => $school,
                    'daycare' => $daycare,


                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );

               
                $result = $this->admdetnew->addNewAdmissiondetails($admissiondetailsInfo);
                //print_r($admissiondetailsInfo);exit;
                // var_dump($result); exit;
                if ($result !== false) {
                    $this->load->model('Notification_model');

                    // ✅ Send Notification to Assigned Franchise User
                    if (!empty($brspFranchiseAssigned)) {
                        $notificationMessage = "<strong>Admission</strong> :A new admission has been added.";
                        $this->Notification_model->add_admdetnew_notification($brspFranchiseAssigned, $notificationMessage, $result);
                    }
                    if (!empty($franchiseNumberArray)) {
                        foreach ($franchiseNumberArray as $franchiseNumber) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if (!empty($branchDetail)) {
                                $to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Admission Details";
                                $message = 'Dear ' . $branchDetail->applicantName . ' ';
                                //$message = ' '.$remark.' ';
                                $message .= 'You have been assigned a new meeting. BY- ' . $this->session->userdata("name") . ' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$remark.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to, $subject, $message, $headers);
                                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                                if (!empty($franchiseUser)) {
                                    $notificationMessage = "<strong>Admission</strong> : A new admission has been added.";
                                    $this->Notification_model->add_admdetnew_notification($franchiseUser->userId, $notificationMessage, $result);
                                }
                                // ✅ Notify Admins (roleId = 1, 14)
                                $adminUsers = $this->bm->getUsersByRoles([1, 14, 20]);
                                if (!empty($adminUsers)) {
                                    foreach ($adminUsers as $adminUser) {
                                        $this->Notification_model->add_admdetnew_notification($adminUser->userId, "<strong>Admission</strong> : A new admission has been added.", $result);
                                    }
                                }
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Admission details created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Admission details creation failed');
                }

                redirect('admissiondetailsnew/admissiondetailsListing');
            }
        }
    }


    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    public function fetchStaffByFranchise()
    {
        header('Content-Type: application/json');

        $franchiseNumber = $this->input->post('franchiseNumber', TRUE);

        if (empty($franchiseNumber)) {
            log_message('error', 'fetchStaffByFranchise: Franchise number is empty');
            echo json_encode(['status' => 'error', 'message' => 'Franchise number is required']);
            return;
        }

        try {
            $staff = $this->admdetnew->getStaffByFranchise($franchiseNumber);

            if (!empty($staff)) {
                echo json_encode(['status' => 'success', 'staff' => $staff]);
            } else {
                log_message('error', 'fetchStaffByFranchise: No staff found for franchise ' . $franchiseNumber);
                echo json_encode(['status' => 'error', 'message' => 'No staff found for this franchise']);
            }
        } catch (Exception $e) {
            log_message('error', 'fetchStaffByFranchise: Exception - ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error fetching staff']);
        }
    }



    function edit($admid = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($admid == null) {
                redirect('admissiondetailsnew/admissiondetailsListing');
            }

            $data['admissiondetailsInfo'] = $this->admdetnew->getAdmissiondetailsInfo($admid);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $data['classes'] = $this->admdetnew->getClasses(); 
            $data['sections'] = $this->admdetnew->getAllSections(); 
            $this->global['pageTitle'] = 'Admission enquiry : Edit Admissiondetails';

            $this->loadViews("admissiondetailsnew/edit", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to edit the user information
     */
    function editAdmissiondetails()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $admid = $this->input->post('admid');

            $this->form_validation->set_rules('name', 'Student Name', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('remark', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->edit($admid);
            } else {
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $name = $this->security->xss_clean($this->input->post('name'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $enrollNum = $this->security->xss_clean($this->input->post('enrollNum'));
                $class = $this->security->xss_clean($this->input->post('class'));
                $section = $this->security->xss_clean($this->input->post('section'));
                $dateOfAdmission = $this->security->xss_clean($this->input->post('dateOfAdmission'));
                $program = $this->security->xss_clean($this->input->post('program'));
                $birthday = $this->security->xss_clean($this->input->post('birthday'));
                $age = $this->security->xss_clean($this->input->post('age'));
                $gender = $this->security->xss_clean($this->input->post('gender'));
                $fathername = $this->security->xss_clean($this->input->post('fathername'));
                $fatheremail = $this->security->xss_clean($this->input->post('fatheremail'));
                $fatherMobile_no = $this->security->xss_clean($this->input->post('fatherMobile_no'));
                $mothername = $this->security->xss_clean($this->input->post('mothername'));
                $motheremail = $this->security->xss_clean($this->input->post('motheremail'));
                $motherMobile_no = $this->security->xss_clean($this->input->post('motherMobile_no'));
                $bloodGroup = $this->security->xss_clean($this->input->post('bloodGroup'));
                $motherTongue = $this->security->xss_clean($this->input->post('motherTongue'));
                $religion = $this->security->xss_clean($this->input->post('religion'));
                $caste = $this->security->xss_clean($this->input->post('caste'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $state = $this->security->xss_clean($this->input->post('state'));
                $totalFee = $this->security->xss_clean($this->input->post('totalFee'));
                $address = $this->security->xss_clean($this->input->post('address'));
                $previousSchool = $this->security->xss_clean($this->input->post('previousSchool'));
                $remark = $this->security->xss_clean($this->input->post('remark'));
                $franchiseNumbers = implode(',', $franchiseNumberArray);

                $admissiondetailsInfo = array(
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'name' => $name,
                    'enrollNum' => $enrollNum,
                    'class' => $class,
                    'section' => $section,
                    'dateOfAdmission' => $dateOfAdmission,
                    'program' => $program,
                    'birthday' => $birthday,
                    'age' => $age,
                    'gender' => $gender,
                    'fathername' => $fathername,
                    'fatheremail' => $fatheremail,
                    'fatherMobile_no' => $fatherMobile_no,
                    'mothername' => $mothername,
                    'motheremail' => $motheremail,
                    'motherMobile_no' => $motherMobile_no,
                    'bloodGroup' => $bloodGroup,
                    'motherTongue' => $motherTongue,
                    'religion' => $religion,
                    'caste' => $caste,
                    'city' => $city,
                    'state' => $state,
                    'totalFee' => $totalFee,
                    'address' => $address,
                    'previousSchool' => $previousSchool,
                    'franchiseNumber' => $franchiseNumbers,
                    'remark' => $remark,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->admdetnew->editAdmissiondetails($admissiondetailsInfo, $admid);

                if ($result == true)
                    $this->load->model('Notification_model'); {
                    if (!empty($franchiseNumberArray)) {
                        foreach ($franchiseNumberArray as $franchiseNumber) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if (!empty($branchDetail)) {
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Admission Details Updated";
                                $message = 'Dear ' . $branchDetail->applicantName . ' ';
                                $message .= 'An existing admission has been updated. BY- ' . $this->session->userdata("name") . ' ';
                                $message .= 'Please visit the portal for details.';
                                $headers = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to, $subject, $message, $headers);
                                // Notify assigned franchise user
                                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                                if (!empty($franchiseUser)) {
                                    $notificationMessage = "<strong>Admission</strong> : An existing admission has been updated.";
                                    $this->Notification_model->add_admdetnew_notification($franchiseUser->userId, $notificationMessage, $admid);
                                }
                                // Notify Admins (roleId = 1, 14, 20)
                                $adminUsers = $this->bm->getUsersByRoles([1, 14, 20]);
                                if (!empty($adminUsers)) {
                                    foreach ($adminUsers as $adminUser) {
                                        $this->Notification_model->add_admdetnew_notification($adminUser->userId, "<strong>Admission</strong> : An existing admission has been updated.", $admid);
                                    }
                                }
                            }
                        }

                        // Notify assigned franchise user for brspFranchiseAssigned
                        if (!empty($brspFranchiseAssigned)) {
                            $notificationMessage = "<strong>Admission</strong> : An existing admission has been updated.";
                            $this->Notification_model->add_admdetnew_notification($brspFranchiseAssigned, $notificationMessage, $admid);
                        }
                        $this->session->set_flashdata('success', 'Admission enquiry updated successfully');
                    } else {
                        $this->session->set_flashdata('error', 'Admission enquiry updation failed');
                    }

                    redirect('admissiondetailsnew/admissiondetailsListing');
                }
            }
        }
    }

    public function fetchAssignedUsers()
    {
        $franchiseNumber = $this->input->post('franchiseNumber', TRUE);
        if (empty($franchiseNumber)) {
            echo json_encode(['status' => 'error', 'message' => 'Franchise number is required']);
            return;
        }
        $users = $this->admdetnew->getUsersByFranchise($franchiseNumber);
        if (empty($users)) {
            echo json_encode(['status' => 'success', 'html' => 'No users found for this franchise.', 'userIds' => '']);
            return;
        }
        $userNames = [];
        $userIds = [];
        foreach ($users as $user) {
            $userNames[] = htmlspecialchars($user->name);
            $userIds[] = $user->userId;
        }
        $html = implode(', ', $userNames);
        $userIdsString = implode(',', $userIds);
        echo json_encode(['status' => 'success', 'html' => $html, 'userIds' => $userIdsString]);
    }
    public function admissionList()
    {
        $data['title'] = "Admission Details List";


        // $this->global might be predefined in your controller
        $this->loadViews("admissiondetailsnew/listcount", $this->global, NULL, NULL);
    }
}
