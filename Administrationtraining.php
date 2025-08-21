<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Administrationtraining (AdministrationtrainingController)
 * Administrationtraining Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Administrationtraining extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Administrationtraining_model', 'amt');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Despatch_model', 'dm');
        $this->isLoggedIn();
        $this->module = 'Administrationtraining';
        $this->load->library('pagination');
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('administrationtraining/administrationtrainingListing');
    }

    /**
     * This function is used to load the Support list
     */

    public function administrationtrainingListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
        $data['vendorId'] = $this->vendorId;
        $franchiseFilter = $this->input->get('franchiseNumber');

        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }

        // Pagination Config
        $config = array();
        $config["base_url"] = base_url() . "administrationtraining/administrationtrainingListing";
        $config["per_page"] = 10;
        $config["uri_segment"] = 3;

        $config["total_rows"] = ($userRole == '1' || $userRole == '30' || $userRole == '14')
            ? $this->amt->administrationtrainingListingCount($franchiseFilter)
            : (($franchiseNumber = $this->amt->getFranchiseNumberByUserId($userId))
                ? $this->amt->get_count_by_franchise($franchiseNumber, $franchiseFilter)
                : 0);

        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        if ($userRole == '1' || $userRole == '14') {
            $records = $this->amt->administrationtrainingListing("", $config["per_page"], $page);
        } else {
            if ($franchiseNumber) {
                $records = $this->amt->administrationtrainingListing($franchiseNumber, $config["per_page"], $page, $franchiseFilter);
            } else {
                $records = [];
            }
        }

        // 🧠 Add attendeesHO_names to each record
        foreach ($records as $record) {
            $record->attendeesHO_names = $this->amt->getNamesFromIds($record->attendeesHO);
        }

        $data["records"] = $records;
        $data["links"] = $this->pagination->create_links();
        $data["start"] = $page + 1;
        $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];
        $data["franchiseFilter"] = $franchiseFilter;
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

        $this->loadViews("administrationtraining/list", $this->global, $data, NULL);
    }

    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $data['users'] = $this->amt->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Administration Training';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();


            $this->loadViews("administrationtraining/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    function addNewAdministrationtraining()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('meetingTitle', 'Meeting Title', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $meetingTitle = $this->security->xss_clean($this->input->post('meetingTitle'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                /*-new-added-field-*/
                $attendedByfranchise = $this->security->xss_clean($this->input->post('attendedByfranchise'));
                $adminTrainingFor = $this->security->xss_clean($this->input->post('adminTrainingFor'));
                $dateMeeting = $this->security->xss_clean($this->input->post('dateMeeting'));
                $timeMeeting = $this->security->xss_clean($this->input->post('timeMeeting'));
                $durationMeeting = $this->security->xss_clean($this->input->post('durationMeeting'));
                $trypeofMeeting = $this->security->xss_clean($this->input->post('trypeofMeeting'));
                $attendeesHO = $this->security->xss_clean($this->input->post('attendeesHO'));

                $trainer = $this->security->xss_clean($this->input->post('trainer'));
                /*-ENd-added-field-*/
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumbers = implode(',', $franchiseNumberArray);
                if (is_array($attendeesHO)) {
                    $attendeesHO = implode(',', $attendeesHO);
                } else {
                    $attendeesHO = ''; // Set a default empty string or handle the case
                }

                $administrationtrainingInfo = array('brspFranchiseAssigned' => $brspFranchiseAssigned, 'meetingTitle' => $meetingTitle, 'attendedByfranchise' => $attendedByfranchise, 'adminTrainingFor' => $adminTrainingFor, 'dateMeeting' => $dateMeeting, 'timeMeeting' => $timeMeeting, 'durationMeeting' => $durationMeeting, 'trypeofMeeting' => $trypeofMeeting, 'attendeesHO' => $attendeesHO, 'trainer' => $trainer, 'franchiseNumber' => $franchiseNumbers, 'description' => $description, 'createdBy' => $this->vendorId, 'createdDtm' => date('Y-m-d H:i:s'));

                $result = $this->amt->addNewAdministrationtraining($administrationtrainingInfo);
                //print_r($administrationtrainingInfo);exit;
                if ($result > 0) {
                    $this->load->model('Notification_model');

                    // ✅ Send Notification to Assigned Franchise User
                    if (!empty($brspFranchiseAssigned)) {
                        $notificationMessage = "<strong>Administration training</strong> :A new administration training has been assigned to you.";
                        $this->Notification_model->add_admintrain_notification($brspFranchiseAssigned, $notificationMessage, $result);
                    }

                    if (!empty($franchiseNumberArray)) {
                        foreach ($franchiseNumberArray as $franchiseNumber) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if (!empty($branchDetail)) {
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Administration Training";
                                $message = 'Dear ' . $branchDetail->applicantName . ' ';
                                //$message = ' '.$description.' ';
                                $message .= 'You have been assigned a new meeting. BY- ' . $this->session->userdata("name") . ' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$description.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to, $subject, $message, $headers);
                                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                                if (!empty($franchiseUser)) {
                                    $notificationMessage = "<strong>Administration training</strong> :A new administration training has been assigned to you.";
                                    $this->Notification_model->add_admintrain_notification($franchiseUser->userId, $notificationMessage, $result);
                                }
                                // ✅ Notify Admins (roleId = 1, 14)
                                $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                                if (!empty($adminUsers)) {
                                    foreach ($adminUsers as $adminUser) {
                                        $this->Notification_model->add_admintrain_notification($adminUser->userId, "<strong>Administration training</strong> :A new administration training has been assigned to you.", $result);
                                    }
                                }
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Administration Training created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Administration Training creation failed');
                }

                redirect('administrationtraining/administrationtrainingListing');
            }
        }
    }


    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($adminMeetingId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($adminMeetingId == null) {
                redirect('administrationtraining/administrationtrainingListing');
            }

            $data['administrationtrainingInfo'] = $this->amt->getAdministrationtrainingInfo($adminMeetingId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Meeting : Edit Administrationtraining';

            $this->loadViews("administrationtraining/edit", $this->global, $data, NULL);
        }
    }
    function view($adminMeetingId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($adminMeetingId == null) {
                redirect('administrationtraining/administrationtrainingListing');
            }

            $data['administrationtrainingInfo'] = $this->amt->getAdministrationtrainingInfo($adminMeetingId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Meeting : View Administrationtraining';

            $this->loadViews("administrationtraining/view", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to edit the user information
     */
    function editAdministrationtraining()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $adminMeetingId = $this->input->post('adminMeetingId');

            $this->form_validation->set_rules('meetingTitle', 'Meeting Title', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->edit($adminMeetingId);
            } else {
                $meetingTitle = $this->security->xss_clean($this->input->post('meetingTitle'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $attendedByfranchise = $this->security->xss_clean($this->input->post('attendedByfranchise'));
                $adminTrainingFor = $this->security->xss_clean($this->input->post('adminTrainingFor'));
                $dateMeeting = $this->security->xss_clean($this->input->post('dateMeeting'));
                $timeMeeting = $this->security->xss_clean($this->input->post('timeMeeting'));
                $durationMeeting = $this->security->xss_clean($this->input->post('durationMeeting'));
                $trypeofMeeting = $this->security->xss_clean($this->input->post('trypeofMeeting'));
                $attendeesHO = $this->security->xss_clean($this->input->post('attendeesHO'));
                $trainer = $this->security->xss_clean($this->input->post('trainer'));
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseNumbers = is_array($franchiseNumberArray) ? implode(',', $franchiseNumberArray) : $franchiseNumberArray;
                if (is_array($attendeesHO)) {
                    $attendeesHO = implode(',', $attendeesHO);
                } else {
                    $attendeesHO = '';
                }

                $administrationtrainingInfo = array(
                    'meetingTitle' => $meetingTitle,
                    'attendedByfranchise' => $attendedByfranchise,
                    'adminTrainingFor' => $adminTrainingFor,
                    'dateMeeting' => $dateMeeting,
                    'timeMeeting' => $timeMeeting,
                    'durationMeeting' => $durationMeeting,
                    'trypeofMeeting' => $trypeofMeeting,
                    'attendeesHO' => $attendeesHO,
                    'trainer' => $trainer,
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'franchiseNumber' => $franchiseNumbers,
                    'description' => $description,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->amt->editAdministrationtraining($administrationtrainingInfo, $adminMeetingId);

                if ($result == true) {
                    $this->load->model('Notification_model');

                    // ✅ Send Notification to Assigned Franchise User
                    if (!empty($brspFranchiseAssigned)) {
                        $notificationMessage = "<strong>Administration training</strong> : An administration training has been updated.";
                        $notifyResult = $this->Notification_model->add_admintrain_notification($brspFranchiseAssigned, $notificationMessage, $adminMeetingId);
                    
                    }

                    // ✅ Notify Franchise Users
                    if (!empty($franchiseNumberArray)) {
                        foreach ($franchiseNumberArray as $franchiseNumber) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if (!empty($branchDetail)) {
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Updated Administration Training";
                                $message = 'Dear ' . $branchDetail->applicantName . ', ';
                                $message .= 'An administration training has been updated by ' . $this->session->userdata("name") . '. ';
                                $message .= 'Please visit the portal.';
                                $headers = "From: Edumeta Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                $mailSent = mail($to, $subject, $message, $headers);
                

                                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                                if (!empty($franchiseUser)) {
                                    $notificationMessage = "<strong>Administration training</strong> : An administration training has been updated.";
                                    $notifyResult = $this->Notification_model->add_admintrain_notification($franchiseUser->userId, $notificationMessage, $adminMeetingId);
                                   
                                }
                            }
                        }
                    }

                    // ✅ Notify Admins (roleId = 1, 14)
                    $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                    if (!empty($adminUsers)) {
                        foreach ($adminUsers as $adminUser) {
                            $notificationMessage = "<strong>Administration training</strong> : An administration training has been updated.";
                            $notifyResult = $this->Notification_model->add_admintrain_notification($adminUser->userId, $notificationMessage, $adminMeetingId);
                            log_message('debug', 'Notification for adminUser ' . $adminUser->userId . ': ' . ($notifyResult ? 'Success' : 'Failed'));
                        }
                    }

                    $this->session->set_flashdata('success', 'Administration Training updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Administration Training updation failed');
                }

                redirect('administrationtraining/administrationtrainingListing');
            }
        }
    }



    /** Code for CK editor */
    public function upload()
    {
        if (isset($_FILES['upload'])) {
            $file = $_FILES['upload'];
            $fileName = time() . '_' . $file['name'];
            $uploadPath = 'uploads/';

            if (move_uploaded_file($file['tmp_name'], $uploadPath . $fileName)) {
                $url = base_url($uploadPath . $fileName);
                $message = 'Image uploaded successfully';

                $callback = $_GET['CKEditorFuncNum'];
                echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($callback, '$url', '$message');</script>";
            } else {
                $message = 'Error while uploading file';
                echo "<script type='text/javascript'>alert('$message');</script>";
            }
        }
    }
    public function fetchAssignedUsers()
    {
        $franchiseNumber = $this->input->post('franchiseNumber');

        // Fetch the users based on the franchise number
        $users = $this->dm->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

        // Generate HTML options for the response
        $options = '<option value="0">Select Role</option>';
        foreach ($users as $user) {
            $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
        }

        echo $options; // Output the options as HTML
    }
}
