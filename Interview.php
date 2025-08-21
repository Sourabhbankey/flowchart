<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Staff (StaffController)
 * Staff Class to control Staff related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 13 June 2024
 */
class Interview extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Interview_model', 'inte');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->load->library('pagination');
        $this->module = 'Interview';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('interview/interviewListing');
    }

    /**
     * This function is used to load the Staff list
     */

    //code done by yashi

    public function interviewListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        $franchiseFilter = $this->input->get('franchiseNumber');
        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
        $config = array();
        $config['base_url'] = base_url('interview/interviewListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        if ($userRole == '14' || $userRole == '1' || $userRole == '23' || $userRole == '20' || $userRole == '21' || $userRole == '28' || $userRole == '26') { // Admin
            if ($franchiseFilter) {
                $config['total_rows'] = $this->inte->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                $data['records'] = $this->inte->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->inte->getTotalTrainingRecordsCount();

                $data['records'] = $this->inte->getAllTrainingRecords($config['per_page'], $page);
            }
        } else if ($userRole == '15' || $userRole == '13') { // Specific roles
            $config['total_rows'] = $this->inte->getTotalTrainingRecordsCountByRole($userId);
            $data['records'] = $this->stf->getTrainingRecordsByRole($userId, $config['per_page'], $page);
        } else {
            $franchiseNumber = $this->inte->getFranchiseNumberByUserId($userId);
            if ($franchiseNumber) {
                if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                    $config['total_rows'] = $this->inte->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->inte->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->inte->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->inte->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
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
        $this->loadViews("interview/list", $this->global, $data, NULL);
    }

    //ends here
    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Staff';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

            $this->loadViews("interview/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    function addNewInterview()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('candidate_name', 'First Name', 'trim|required|max_length[256]');


            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                // Personal details 
                $date_of_interview = $this->security->xss_clean($this->input->post('date_of_interview'));
                $mode_of_interview = $this->security->xss_clean($this->input->post('mode_of_interview'));
                $candidate_name = $this->security->xss_clean($this->input->post('candidate_name'));
                $mobile_number = $this->security->xss_clean($this->input->post('mobile_number'));
                $second_number = $this->security->xss_clean($this->input->post('second_number'));
                $email = $this->security->xss_clean($this->input->post('email'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $highest_qualification = $this->security->xss_clean($this->input->post('highest_qualification'));
                $total_experience = $this->security->xss_clean($this->input->post('total_experience'));
                $previous_organization = $this->security->xss_clean($this->input->post('previous_organization'));
                $experience_in_previous_org = $this->security->xss_clean($this->input->post('experience_in_previous_org'));
                $cv_received = $this->security->xss_clean($this->input->post('cv_received'));
                $salary_expectations = $this->security->xss_clean($this->input->post('salary_expectations'));
                $last_salary_drawn = $this->security->xss_clean($this->input->post('last_salary_drawn'));
                $iq_rating = $this->security->xss_clean($this->input->post('iq_rating'));
                $communication_rating = $this->security->xss_clean($this->input->post('communication_rating'));
                $confidence_rating = $this->security->xss_clean($this->input->post('confidence_rating'));
                $overall_rating = $this->security->xss_clean($this->input->post('overall_rating'));
                $age = $this->security->xss_clean($this->input->post('age'));
                $marital_status = $this->security->xss_clean($this->input->post('marital_status'));
                $remarks = $this->security->xss_clean($this->input->post('remarks'));
                $second_round_on = $this->security->xss_clean($this->input->post('second_round_on'));
                $selected = $this->security->xss_clean($this->input->post('selected'));
                $salary_offered = $this->security->xss_clean($this->input->post('salary_offered'));
                $resumeS3attachment = $this->security->xss_clean($this->input->post('resumeS3attachment'));
                $working_hours = $this->security->xss_clean($this->input->post('working_hours'));


                // Upload Documents Files

                if (isset($_FILES["file"]["tmp_name"]) && !empty($_FILES["file"]["tmp_name"])) {
                    $dir = dirname($_FILES["file"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                    if (rename($_FILES["file"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link[] = '';
                        }
                    } else {
                        $s3_file_link[] = '';
                    }
                } else {
                    $s3_file_link[] = '';
                }
                $s3files = implode(',', $s3_file_link);



                // Insert data into interviewInfo array
                $interviewInfo = array(
                    'date_of_interview' => $date_of_interview,
                    'mode_of_interview' => $mode_of_interview,
                    'candidate_name' => $candidate_name,
                    'mobile_number' => $mobile_number,
                    'second_number' => $second_number,
                    'email' => $email,
                    'city' => $city,
                    'highest_qualification' => $highest_qualification,
                    'total_experience' => $total_experience,
                    'previous_organization' => $previous_organization,
                    'experience_in_previous_org' => $experience_in_previous_org,
                    'cv_received' => $cv_received,
                    'salary_expectations' => $salary_expectations,
                    'last_salary_drawn' => $last_salary_drawn,
                    'iq_rating' => $iq_rating,
                    'communication_rating' => $communication_rating,
                    'confidence_rating' => $confidence_rating,
                    'overall_rating' => $overall_rating,
                    'age' => $age,
                    'marital_status' => $marital_status,
                    'remarks' => $remarks,
                    'second_round_on' => $second_round_on,
                    'selected' => $selected,
                    'salary_offered' => $salary_offered,
                    'resumeS3attachment' => $s3files,
                    'working_hours' => $working_hours,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );


                $result = $this->inte->addNewInterview($interviewInfo);
                //print_r($interviewInfo);exit;


                if ($result > 0) {

                    $franchiseNumber = $this->inte->getFranchiseNumberByUserId($this->vendorId);
                    if ($franchiseNumber) {
                        $branchDetail = $this->bm->getBranchesInfoByFranchiseNumber($franchiseNumber);
                        if (!empty($branchDetail) && !empty($branchDetail->officialEmailID)) {
                            $to = $branchDetail->officialEmailID;
                            $subject = "New Interview Scheduled - eduMETA THE i-SCHOOL";
                            $message = "Dear {$branchDetail->applicantName},<br><br>";
                            $message .= "A new interview has been scheduled by {$this->session->userdata('name')}.<br>";
                            $message .= "<strong>Candidate Details:</strong><br>";
                            $message .= "Candidate Name: {$interviewInfo['candidate_name']}<br>";
                            $message .= "Date of Interview: {$interviewInfo['date_of_interview']}<br>";
                            $message .= "Mode of Interview: {$interviewInfo['mode_of_interview']}<br>";
                            $message .= "Please visit the portal for more details.<br><br>";
                            $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                            $headers = "From: eduMETA Team <noreply@theischool.com>\r\n";
                            $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                            $headers .= "MIME-Version: 1.0\r\n";
                            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                            if (!mail($to, $subject, $message, $headers)) {
                                log_message('error', 'Failed to send email to ' . $to);
                            }
                        }
                    }


                    $this->session->set_flashdata('success', 'Interview details added successfully');
                } else {
                    $this->session->set_flashdata('error', 'Interview creation failed');
                }

                redirect('interview/interviewListing');
            }
        }
    }


    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($interviewId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($interviewId == null) {
                redirect('interview/interviewListing');
            }

            $data['interviewInfo'] = $this->inte->getInterviewInfo($interviewId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Meeting : Edit Staff';

            $this->loadViews("interview/edit", $this->global, $data, NULL);
        }
    }

    function view($interviewId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($interviewId == null) {
                redirect('interview/interviewListing');
            }

            $data['interviewInfo'] = $this->inte->getInterviewInfo($interviewId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Meeting : Edit Staff';

            $this->loadViews("interview/view", $this->global, $data, NULL);
        }
    }
    /**
     * This function is used to edit the user information
     */
  function editInterview()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $interviewId = $this->input->post('interviewId');

        $this->form_validation->set_rules('candidate_name', 'Name', 'trim|required|max_length[256]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($interviewId);
        } else {
            $date_of_interview = $this->security->xss_clean($this->input->post('date_of_interview'));
            $mode_of_interview = $this->security->xss_clean($this->input->post('mode_of_interview'));
            $candidate_name = $this->security->xss_clean($this->input->post('candidate_name'));
            $mobile_number = $this->security->xss_clean($this->input->post('mobile_number'));
            $second_number = $this->security->xss_clean($this->input->post('second_number'));
            $email = $this->security->xss_clean($this->input->post('email'));
            $city = $this->security->xss_clean($this->input->post('city'));
            $highest_qualification = $this->security->xss_clean($this->input->post('highest_qualification'));
            $total_experience = $this->security->xss_clean($this->input->post('total_experience'));
            $previous_organization = $this->security->xss_clean($this->input->post('previous_organization'));
            $experience_in_previous_org = $this->security->xss_clean($this->input->post('experience_in_previous_org'));
            $cv_received = $this->security->xss_clean($this->input->post('cv_received'));
            $salary_expectations = $this->security->xss_clean($this->input->post('salary_expectations'));
            $last_salary_drawn = $this->security->xss_clean($this->input->post('last_salary_drawn'));
            $iq_rating = $this->security->xss_clean($this->input->post('iq_rating'));
            $communication_rating = $this->security->xss_clean($this->input->post('communication_rating'));
            $confidence_rating = $this->security->xss_clean($this->input->post('confidence_rating'));
            $overall_rating = $this->security->xss_clean($this->input->post('overall_rating'));
            $age = $this->security->xss_clean($this->input->post('age'));
            $marital_status = $this->security->xss_clean($this->input->post('marital_status'));
            $remarks = $this->security->xss_clean($this->input->post('remarks'));
            $second_round_on = $this->security->xss_clean($this->input->post('second_round_on'));
            $selected = $this->security->xss_clean($this->input->post('selected'));
            $salary_offered = $this->security->xss_clean($this->input->post('salary_offered'));
            $resumeS3attachment = $this->security->xss_clean($this->input->post('resumeS3attachment'));
            $working_hours = $this->security->xss_clean($this->input->post('working_hours'));

            // Upload Documents Files
            if (isset($_FILES["file"]["tmp_name"]) && !empty($_FILES["file"]["tmp_name"])) {
                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                if (rename($_FILES["file"]["tmp_name"], $destination)) {
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                        $s3_file_link[] = $result_arr['ObjectURL'];
                    } else {
                        $s3_file_link[] = '';
                    }
                } else {
                    $s3_file_link[] = '';
                }
            } else {
                $s3_file_link[] = '';
            }
            $s3files = implode(',', $s3_file_link);

            // Insert data into interviewInfo array
            $interviewInfo = array(
                'date_of_interview' => $date_of_interview,
                'mode_of_interview' => $mode_of_interview,
                'candidate_name' => $candidate_name,
                'mobile_number' => $mobile_number,
                'second_number' => $second_number,
                'email' => $email,
                'city' => $city,
                'highest_qualification' => $highest_qualification,
                'total_experience' => $total_experience,
                'previous_organization' => $previous_organization,
                'experience_in_previous_org' => $experience_in_previous_org,
                'cv_received' => $cv_received,
                'salary_expectations' => $salary_expectations,
                'last_salary_drawn' => $last_salary_drawn,
                'iq_rating' => $iq_rating,
                'communication_rating' => $communication_rating,
                'confidence_rating' => $confidence_rating,
                'overall_rating' => $overall_rating,
                'age' => $age,
                'marital_status' => $marital_status,
                'remarks' => $remarks,
                'second_round_on' => $second_round_on,
                'selected' => $selected,
                'salary_offered' => $salary_offered,
                'resumeS3attachment' => $s3files ?: $resumeS3attachment, // Use new file if uploaded, else retain old
                'working_hours' => $working_hours,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->inte->editInterview($interviewInfo, $interviewId);

            if ($result == true) {
                // Send email notification
                $franchiseNumber = $this->inte->getFranchiseNumberByUserId($this->vendorId);
                if ($franchiseNumber) {
                    $branchDetail = $this->bm->getBranchesInfoByFranchiseNumber($franchiseNumber);
                    if (!empty($branchDetail) && !empty($branchDetail->officialEmailID)) {
                        $to = $branchDetail->officialEmailID;
                        $subject = "Interview Details Updated - eduMETA THE i-SCHOOL";
                        $message = "Dear {$branchDetail->applicantName},<br><br>";
                        $message .= "The interview details for the following candidate have been updated by {$this->session->userdata('name')}.<br>";
                        $message .= "<strong>Candidate Details:</strong><br>";
                        $message .= "Candidate Name: {$interviewInfo['candidate_name']}<br>";
                        $message .= "Date of Interview: {$interviewInfo['date_of_interview']}<br>";
                        $message .= "Mode of Interview: {$interviewInfo['mode_of_interview']}<br>";
                        $message .= "Please visit the portal for more details.<br><br>";
                        $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                        $headers = "From: eduMETA Team <noreply@theischool.com>\r\n";
                        $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                        $headers .= "MIME-Version: 1.0\r\n";
                        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                        if (!mail($to, $subject, $message, $headers)) {
                            log_message('error', 'Failed to send email to ' . $to);
                        }
                    }
                }

                $this->session->set_flashdata('success', 'Interview updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Interview update failed');
            }

            redirect('interview/interviewListing');
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
        $users = $this->stf->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

        // Generate HTML options for the response
        $options = '<option value="0">Select Role</option>';
        foreach ($users as $user) {
            $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
        }

        echo $options; // Output the options as HTML
    }
    public function download_s3_file()
    {
        $fileUrl = $this->input->get('url'); // Get file URL from query string

        if ($fileUrl) {
            // Set headers to force download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($fileUrl) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            readfile($fileUrl); // Read from S3 URL directly
            exit;
        } else {
            echo "Invalid download URL.";
        }
    }
}
