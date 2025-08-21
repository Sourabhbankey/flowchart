<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Salesrecord (SalesrecordController)
 * Salesrecord Class to control Salesrecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Approval extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Approval_model', 'app');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Despatch_model', 'dm');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Socialmedia';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('Approval/ApprovalListing');
    }

    /**
     * This function is used to load the salesrecord list
     */
    public function ApprovalListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
        $data['userRole'] = $userRole;

        $searchText = $this->input->get('searchText');
        $searchUserId = $this->input->get('searchUserId');
        $franchiseFilter = $this->input->get('franchiseNumber');

        // 🔹 Pagination Setup
        $this->load->library('pagination');
        $count = $this->app->approvalListingCount($searchText, $userId, $userRole, $searchUserId);
        $returns = $this->paginationCompress("ApprovalListing/", $count, 500);

        // 🔹 Get Data
        $data['records'] = $this->app->approvalListing($searchText, $returns["page"], $returns["segment"], $userId, $userRole, $searchUserId);
        // $data['users'] = $this->smm->getAllUsers();
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

        // 🔹 Load View
        $this->global['pageTitle'] = 'CodeInsect : Social Media';
        $this->loadViews("approval/list", $this->global, $data, NULL);
    }


    /* }*/

    /**
     * This function is used to load the add new form
     */
    function add()

    {
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $data['users'] = $this->app->getUser();
        $this->global['pageTitle'] = 'CodeInsect : Add New followup';
        $this->loadViews("approval/add", $this->global, $data, NULL);
    }



    /**
     * This function is used to add new user to the system
     */

    public function addNewapproval()
    {
        $userId = $this->session->userdata('userId');
        $this->load->library('form_validation');

        // Form validation rules
        $this->form_validation->set_rules('approvalTitle', 'Approval Title', 'trim|required|max_length[256]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');
        $this->form_validation->set_rules('userID', 'Approving Authority', 'trim|required|numeric');
        $this->form_validation->set_rules('approvalStatus', 'Approval Status', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
            return;
        }

        // Get form data
        $approvalTitle = $this->security->xss_clean($this->input->post('approvalTitle'));
        $description = $this->security->xss_clean($this->input->post('description'));
        $userID = $this->security->xss_clean($this->input->post('userID'));
        $approvalStatus = $this->security->xss_clean($this->input->post('approvalStatus'));

        // Handle file upload
        $s3file = '';
        if (isset($_FILES['approvalS3attachment'])) {
            log_message('debug', 'File input received: ' . print_r($_FILES['approvalS3attachment'], true));
        } else {
            log_message('debug', 'No file input received.');
        }

        if (isset($_FILES['approvalS3attachment']) && $_FILES['approvalS3attachment']['error'] == UPLOAD_ERR_OK) {
            try {
                $storeFolder = 'attachments';
                $config['upload_path'] = FCPATH . 'Uploads/temp/';
                $config['allowed_types'] = 'jpg|jpeg|png|pdf|doc|docx';
                $config['max_size'] = 10240; // 10MB max
                $config['file_name'] = time() . '-' . $_FILES['approvalS3attachment']['name'];

                // Ensure the upload directory exists
                if (!is_dir($config['upload_path'])) {
                    if (!mkdir($config['upload_path'], 0775, true)) {
                        log_message('error', 'Failed to create upload directory: ' . $config['upload_path']);
                        $this->session->set_flashdata('error', 'Failed to create upload directory.');
                        $this->add();
                        return;
                    }
                    log_message('debug', 'Created upload directory: ' . $config['upload_path']);
                }

                // Verify directory is writable
                if (!is_writable($config['upload_path'])) {
                    log_message('error', 'Upload directory is not writable: ' . $config['upload_path']);
                    $this->session->set_flashdata('error', 'Upload directory is not writable.');
                    $this->add();
                    return;
                }

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('approvalS3attachment')) {
                    $uploadData = $this->upload->data();
                    $localFile = $uploadData['full_path'];
                    log_message('debug', 'Local file uploaded: ' . $localFile);

                    // Upload to S3
                    $s3Result = $this->s3_upload->upload_file($localFile, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    log_message('debug', 'S3 upload result: ' . print_r($result_arr, true));
                    $s3file = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';

                    // Delete local file after upload
                    @unlink($localFile);
                } else {
                    $error = $this->upload->display_errors();
                    log_message('error', 'File upload failed: ' . $error);
                    $this->session->set_flashdata('error', 'File upload failed: ' . $error);
                    $this->add();
                    return;
                }
            } catch (Exception $e) {
                log_message('error', 'File upload error: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'File upload error: ' . $e->getMessage());
                $this->add();
                return;
            }
        }

        // Prepare approval data
        $approvalInfo = array(
            'approvalTitle' => $approvalTitle,
            'description' => $description,
            'userID' => $userID,
            'createdBy' => $userId,
            'approvalStatus' => $approvalStatus,
            'approvalS3attachment' => $s3file
        );

        log_message('debug', 'Approval info to insert: ' . print_r($approvalInfo, true));

        // Insert the new approval record
        $result = $this->app->addNewapproval($approvalInfo);


        if ($result > 0) {
            // Add notifications for all users
            $this->load->model('Notification_model');

            // Verify if the userID exists in tbl_users
            $userExists = $this->db->select('userId')
                ->from('tbl_users')
                ->where('userId', $userID)
                ->get()
                ->row();

            if (!$userExists) {
                log_message('error', "Invalid Approving Authority user ID: {$userID}");
                $this->session->set_flashdata('error', 'Invalid Approving Authority selected.');
                redirect('approval/approvalListing');
                return;
            }

            // Send notification to the selected Approving Authority
            $notificationMessage = "Approval Confirmation: New Approval confirmation for {$approvalTitle}";
            $notificationResult = $this->nm->add_approval_notification($result, $notificationMessage, $userID);

            if ($notificationResult) {
                log_message('debug', "Notification sent successfully to user {$userID} for approval ID {$result}");
            } else {
                log_message('error', "Failed to send notification to user {$userID} for approval ID {$result}");
            }
            $this->session->set_flashdata('success', 'New approval record created successfully.');
        } else {
            $this->session->set_flashdata('error', 'Approval record creation failed.');
        }

        redirect('approval/approvalListing');
    }
    /**
     * This function is used load salesrecord edit information
     * @param number $salesrecId : Optional : This is salesrecord id
     */
    function edit($approvalId = NULL)
    {
        if ($approvalId == null) {
            redirect('Approval/ApprovalListing');
        }

        $data['approvalInfo'] = $this->app->getapprovalInfo($approvalId);
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $data['users'] = $this->app->getUser();
        // Fetch replies for the approval
        $data['replies'] = $this->app->getRepliesByApproval($approvalId);

        $this->global['pageTitle'] = 'CodeInsect : Edit approval';
        $this->loadViews("approval/edit", $this->global, $data, NULL);
    }
    /*}*/


    /**
     * This function is used to edit the user information
     */
    public function editapproval()
    {
        $this->load->library('form_validation');

        $approvalId = $this->input->post('approvalId');

        $this->form_validation->set_rules('approvalStatus', 'Status', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($approvalId);
            return;
        }

        $approvalStatus = $this->security->xss_clean($this->input->post('approvalStatus'));

        $approvalInfo = array(
            'approvalStatus' => $approvalStatus
        );

        $result = $this->app->editapproval($approvalInfo, $approvalId);

        if ($result) {
            // Load Notification model
            $this->load->model('Notification_model');

            // Fetch the userID associated with the approval record
            $approval = $this->db->select('userID, approvalTitle')
                ->from('tbl_approval') // Replace with your actual table name
                ->where('approvalId', $approvalId)
                ->get()
                ->row();

            if (!$approval) {
                log_message('error', "Approval record not found for ID: {$approvalId}");
                $this->session->set_flashdata('error', 'Approval record not found.');
                redirect('approval/approvalListing');
                return;
            }

            $userID = $approval->userID;
            $approvalTitle = $approval->approvalTitle;

            // Verify if the userID exists in tbl_users
            $userExists = $this->db->select('userId')
                ->from('tbl_users')
                ->where('userId', $userID)
                ->get()
                ->row();

            if (!$userExists) {
                log_message('error', "Invalid Approving Authority user ID: {$userID}");
                $this->session->set_flashdata('error', 'Invalid Approving Authority selected.');
                redirect('approval/approvalListing');
                return;
            }

            // Send notification to the selected Approving Authority
            $notificationMessage = "Approval Confirmation: Updated Approval confirmation for {$approvalTitle}";
            $notificationResult = $this->nm->add_approval_notification($approvalId, $notificationMessage, $userID);

            if ($notificationResult) {
                log_message('debug', "Notification sent successfully to user {$userID} for approval ID {$approvalId}");
            } else {
                log_message('error', "Failed to send notification to user {$userID} for approval ID {$approvalId}");
            }

            $this->session->set_flashdata('success', 'Approval record updated successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update approval record.');
        }

        redirect('approval/approvalListing');
    }
    public function addReply()
    {
        // Get form data
        $approvalId = $this->security->xss_clean($this->input->post('approvalId')); // Corrected to use approvalId
        $message = $this->security->xss_clean($this->input->post('replyMessage'));
        $userId = $this->session->userdata('userId');

        // Validate inputs
        if (empty($approvalId) || empty($message) || empty($userId)) {
            $this->session->set_flashdata('error', 'Required fields are missing or user is not logged in.');
            redirect('approval/edit/' . $approvalId);
        }

        // Handle file upload
        $s3files1 = '';
        if (isset($_FILES['file1']) && $_FILES['file1']['error'] == UPLOAD_ERR_OK) {
            try {
                $storeFolder = 'attachements';
                $config['upload_path'] = './uploads/temp/'; // Temporary local path
                $config['allowed_types'] = 'jpg|jpeg|png|pdf|doc|docx';
                $config['max_size'] = 10240; // 10MB max
                $config['file_name'] = time() . '-' . $_FILES['file1']['name'];

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('file1')) {
                    $uploadData = $this->upload->data();
                    $localFile = $uploadData['full_path'];

                    // Upload to S3
                    $s3Result = $this->s3_upload->upload_file($localFile, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3files1 = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';

                    // Delete local file after upload
                    @unlink($localFile);
                } else {
                    $this->session->set_flashdata('error', 'File upload failed: ' . $this->upload->display_errors());
                    redirect('approval/edit/' . $approvalId);
                }
            } catch (Exception $e) {
                $this->session->set_flashdata('error', 'File upload error: ' . $e->getMessage());
                redirect('approval/edit/' . $approvalId);
            }
        }

        // Prepare reply data
        $data = [
            'approvalId' => $approvalId,
            'message' => $message,
            'repliedBy' => $userId,
            'attachment' => $s3files1,
            'msgRead' => '0',
            'createdDtm' => date('Y-m-d H:i:s'),
            'createdBy' => $userId,
            'isDeleted' => 0,
            'status' => 'pending'
        ];

        // Insert reply
        $result = $this->app->insertReply($data);
        if ($result) {
            $this->session->set_flashdata('success', 'Reply submitted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to submit reply. Please try again.');
        }

        redirect('approval/edit/' . $approvalId);
    }
}
