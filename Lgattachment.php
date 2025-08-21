<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Task (TaskController)
 * Task Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Lgattachment extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Lgattachment_model', 'la');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Lgattachment';
        $this->load->library('pagination');
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('lgattachment/lgattachmentListing');
    }

    /**
     * This function is used to load the task list
     */

    public function lgattachmentListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        $franchiseFilter = $this->input->get('franchiseNumber');
        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
        $config = array();
        $config['base_url'] = base_url('lgattachment/lgattachmentListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        if ($userRole == '14' || $userRole == '1' || $userRole == '24' || $userRole == '28' || $userRole == '29' || $userRole == '31' ) { // Admin
            if ($franchiseFilter) {
                $config['total_rows'] = $this->la->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                $data['records'] = $this->la->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->la->getTotalTrainingRecordsCount();

                $data['records'] = $this->la->getAllTrainingRecords($config['per_page'], $page);
            }
        } else if ($userRole == '15' || $userRole == '13') { // Specific roles
            $config['total_rows'] = $this->la->getTotalTrainingRecordsCountByRole($userId);
            $data['records'] = $this->la->getTrainingRecordsByRole($userId, $config['per_page'], $page);
        } else {
            $franchiseNumber = $this->la->getFranchiseNumberByUserId($userId);
            if ($franchiseNumber) {
                if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                    $config['total_rows'] = $this->la->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->la->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->la->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->la->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
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
        $this->loadViews("lgattachment/list", $this->global, $data, NULL);
    }




    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->global['pageTitle'] = 'CodeInsect : Add New lgattachment';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $data['users'] = $this->la->getUser();
            $this->loadViews("lgattachment/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    function addNewLgattachment()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('lgattachmentTitle', 'Attachment Title', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $lgattachmentTitle = $this->security->xss_clean($this->input->post('lgattachmentTitle'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $attachmentType = $this->security->xss_clean($this->input->post('attachmentType'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $otherAttachmentType = $this->security->xss_clean($this->input->post('otherAttachmentType'));

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
                $franchiseNumbers = implode(',', $franchiseNumberArray);

                $lgattachmentInfo = array(
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'lgattachmentTitle' => $lgattachmentTitle,
                    'franchiseNumber' => $franchiseNumbers,
                    'attachmentType' => $attachmentType,
                    'description' => $description,
                    'otherAttachmentType' => $otherAttachmentType,
                    'lgattachmentS3File' => $s3files,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->la->addNewlgattachment($lgattachmentInfo);

                if ($result > 0) {
                    $this->load->model('Notification_model');

                    // ✅ Send Notification to Assigned Franchise User
                    if (!empty($brspFranchiseAssigned)) {
                        $notificationMessage = "<strong>Legal Attachment:</strong> A new Legal Attachment has been added.";
                        $this->Notification_model->add_legal_notification($brspFranchiseAssigned, $notificationMessage, $result);
                    }

                    // ✅ Notify Franchise User
                    $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumbers);
                    if (!empty($franchiseUser)) {
                        $notificationMessage = "<strong>Legal Attachment:</strong> A new Legal Attachment has been added.";
                        $this->Notification_model->add_legal_notification($franchiseUser->userId, $notificationMessage, $result);
                    }

                    // ✅ Notify Admins (roleId = 1, 14, 24)
                    $adminUsers = $this->bm->getUsersByRoles([1, 14, 24]);
                    if (!empty($adminUsers)) {
                        foreach ($adminUsers as $adminUser) {
                            $notificationMessage = "<strong>Legal Attachment:</strong> A new Legal Attachment has been added.";
                            $this->Notification_model->add_legal_notification($adminUser->userId, $notificationMessage, $result);
                        }
                    }

                    // ✅ Send Email to Admin
                    $to = 'admin@theischool.com'; // Static admin email
                    $subject = "New Legal Attachment Added - eduMETA THE i-SCHOOL";
                    $message = "Dear Admin,<br><br>";
                    $message .= "A new legal attachment has been added by {$this->session->userdata('name')}.<br>";
                    $message .= "<strong>Attachment Details:</strong><br>";
                   
                    $message .= "Please visit the portal for more details.<br><br>";
                    $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                    $headers = "From: eduMETA Team <noreply@theischool.com>\r\n";
                    $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if (!mail($to, $subject, $message, $headers)) {
                        log_message('error', 'Failed to send email to ' . $to);
                    }

                    $this->session->set_flashdata('success', 'New Attachment created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Attachment creation failed');
                }

                redirect('lgattachment/lgattachmentListing');
            }
        }
    }



    /**
     * This function is used load attachment edit information
     * @param number $attachmentId : Optional : This is attachment id
     */
    function edit($lgattachmentId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($lgattachmentId == null) {
                redirect('lgattachment/lgattachmentListing');
            }

            $data['lgattachmentInfo'] = $this->la->getLgattachmentInfo($lgattachmentId);
            $data['replyList'] = $this->la->getReplies($lgattachmentId);
            $this->global['pageTitle'] = 'CodeInsect : Edit lgattachment';

            $this->loadViews("lgattachment/edit", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to edit the user information
     */
   function editLgattachment()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        $this->load->model('Notification_model'); // Load the notification model

        $lgattachmentId = $this->input->post('lgattachmentId');

        $this->form_validation->set_rules('lgattachmentTitle', 'Attachment Title', 'trim|required|max_length[256]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($lgattachmentId);
        } else {
            $lgattachmentTitle = $this->security->xss_clean($this->input->post('lgattachmentTitle'));
            $attachmentType = $this->security->xss_clean($this->input->post('attachmentType'));
            $description = $this->security->xss_clean($this->input->post('description'));

            $lgattachmentInfo = array(
                'lgattachmentTitle' => $lgattachmentTitle,
                'attachmentType' => $attachmentType,
                'description' => $description,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->la->editlgattachment($lgattachmentInfo, $lgattachmentId);

            if ($result == true) {
               /* // Fetch attachment details for notifications
                $attachmentDetails = $this->la->getLgattachmentInfo($lgattachmentId);
                $brspFranchiseAssigned = $attachmentDetails->brspFranchiseAssigned;
                $franchiseNumbers = $attachmentDetails->franchiseNumber;

                // Notify the user who updated the attachment
                $notificationMessage = "<strong>Legal Attachment:</strong> You updated legal attachment <br>'{$lgattachmentTitle}' (ID: {$lgattachmentId}).";
                $this->Notification_model->add_legal_notification($this->vendorId, $notificationMessage, $lgattachmentId);

                // Notify the assigned franchise user
                if (!empty($brspFranchiseAssigned)) {
                    $notificationMessage = "<strong>Legal Attachment:</strong> Legal attachment '{$lgattachmentTitle}' (ID: {$lgattachmentId}) has been updated and assigned to you.";
                    $this->Notification_model->add_legal_notification($brspFranchiseAssigned, $notificationMessage, $lgattachmentId);
                }

                // Notify franchise users
                $franchiseNumbersArray = explode(',', $franchiseNumbers);
                foreach ($franchiseNumbersArray as $franchiseNumber) {
                    $franchiseNumber = trim($franchiseNumber);
                    if (!empty($franchiseNumber)) {
                        $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                        if (!empty($franchiseUser) && !empty($franchiseUser->userId)) {
                            $notificationMessage = "<strong>Legal Attachment:</strong> Legal attachment '{$lgattachmentTitle}' (ID: {$lgattachmentId}) has been updated.";
                            $this->Notification_model->add_legal_notification($franchiseUser->userId, $notificationMessage, $lgattachmentId);
                        }
                    }
                }

                // Notify admins (role IDs 1, 14, 24)
                $adminUsers = $this->bm->getUsersByRoles([1, 14, 24]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $notificationMessage = "<strong>Legal Attachment:</strong> Legal attachment '{$lgattachmentTitle}' (ID: {$lgattachmentId}) has been updated.";
                        $this->Notification_model->add_legal_notification($adminUser->userId, $notificationMessage, $lgattachmentId);
                    }
                }
*/
                  $this->load->model('Notification_model');

                    // ✅ Send Notification to Assigned Franchise User
                    if (!empty($brspFranchiseAssigned)) {
                        $notificationMessage = "<strong>Legal Attachment:</strong> A new Legal Attachment has been added.";
                        $this->Notification_model->add_legal_notification($brspFranchiseAssigned, $notificationMessage, $result);
                    }

                    // ✅ Notify Franchise User
                    $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumbers);
                    if (!empty($franchiseUser)) {
                        $notificationMessage = "<strong>Legal Attachment:</strong> A new Legal Attachment has been added.";
                        $this->Notification_model->add_legal_notification($franchiseUser->userId, $notificationMessage, $result);
                    }

                    // ✅ Notify Admins (roleId = 1, 14, 24)
                    $adminUsers = $this->bm->getUsersByRoles([1, 14, 24]);
                    if (!empty($adminUsers)) {
                        foreach ($adminUsers as $adminUser) {
                            $notificationMessage = "<strong>Legal Attachment:</strong> A new Legal Attachment has been added.";
                            $this->Notification_model->add_legal_notification($adminUser->userId, $notificationMessage, $result);
                        }
                    }
                // Send email to admin
                $to = 'dev.edumeta@gmail.com'; // Static admin email
                $subject = "Legal Attachment Updated - eduMETA THE i-SCHOOL";
                $message = "Dear Admin,<br><br>";
                $message .= "A legal attachment has been updated by {$this->session->userdata('name')}.<br>";
                $message .= "<strong>Attachment Details:</strong><br>";
                $message .= "Please visit the portal for more details.<br><br>";
                $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                $headers = "From: eduMETA Team <noreply@theischool.com>\r\n";
                $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                mail($to, $subject, $message, $headers);

                $this->session->set_flashdata('success', 'Attachment updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Attachment update failed');
            }

            redirect('lgattachment/lgattachmentListing');
        }
    }
}

    public function fetchAssignedUsers()
    {
        $franchiseNumber = $this->input->post('franchiseNumber');

        // Fetch the users based on the franchise number
        $users = $this->la->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

        // Generate HTML options for the response
        $options = '<option value="0">Select Role</option>';
        foreach ($users as $user) {
            $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
        }

        echo $options; // Output the options as HTML
    }
    public function addReply()
    {
        $this->load->model('Lgattachment_model');

        $replyText = $this->input->post('replyText');
        $lgattachmentId = $this->input->post('lgattachmentId');
        $userId = $this->session->userdata('userId'); // assuming login system

        if (!empty($replyText) && !empty($lgattachmentId)) {
            $replyData = array(
                'lgattachmentId' => $lgattachmentId,
                'replyText' => $replyText,
                'createdBy' => $userId,
                'createdDtm' => date('Y-m-d H:i:s')
            );

            $this->Lgattachment_model->insertReply($replyData);
            $this->session->set_flashdata('success', 'Reply added successfully.');
        } else {
            $this->session->set_flashdata('error', 'Please write a reply.');
        }

        redirect('lgattachment/edit/' . $lgattachmentId);
    }
}
