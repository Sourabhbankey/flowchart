<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Customdesign (CustomdesignController)
 * Customdesign Class to control custom design related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Customdesign extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customdesign_model', 'cdm');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->load->library('pagination');
        $this->module = 'Customdesign';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('customdesign/customdesignListing');
    }

    /**
     * This function is used to load the custom design list
     */
    public function customdesignListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        // Get filter parameters
        $franchiseFilter = $this->input->get('franchiseNumber');
        $startDate = $this->input->get('startDate');
        $endDate = $this->input->get('endDate');
        $resetFilter = $this->input->get('resetFilter');

        // Reset filters if requested
        if ($resetFilter == '1') {
            $franchiseFilter = '';
            $startDate = '';
            $endDate = '';
        }

        // Prepare date filters
        $dateFilters = [];
        if (!empty($startDate)) {
            $dateFilters['startDate'] = $startDate;
        }
        if (!empty($endDate)) {
            $dateFilters['endDate'] = $endDate;
        }

        // Pagination configuration
        $config = array();
        $config['base_url'] = base_url('customdesign/customdesignListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        // Admin and Specific Roles Can See Everything
        if (in_array($userRole, ['14', '1', '19' ,'15'])) {
            if (!empty($franchiseFilter)) {
                $config['total_rows'] = $this->cdm->getTotalCustomDesignRecordsCountByFranchise($franchiseFilter, $dateFilters);
                $data['records'] = $this->cdm->getCustomDesignRecordsByFranchise($franchiseFilter, $config['per_page'], $page, $dateFilters);
            } else {
                $config['total_rows'] = $this->cdm->getTotalCustomDesignRecordsCount($dateFilters);
                $data['records'] = $this->cdm->getAllCustomDesignRecords($config['per_page'], $page, $dateFilters);
            }
        }
        // Franchise Users Should See Their Own Data
        else {
            $franchiseNumber = $this->cdm->getFranchiseNumberByUserId($userId);

            if (!empty($franchiseNumber)) {
                $config['total_rows'] = $this->cdm->getTotalCustomDesignRecordsCountByFranchise($franchiseNumber, $dateFilters);
                $data['records'] = $this->cdm->getCustomDesignRecordsByFranchise($franchiseNumber, $config['per_page'], $page, $dateFilters);
            } else {
                $data['records'] = []; // No franchise assigned
            }
        }

        // Initialize pagination
        $this->pagination->initialize($config);
        $data['links'] = $this->pagination->create_links();
        $data['start'] = $page + 1;
        $data['end'] = min($page + $config['per_page'], $config['total_rows']);
        $data['total_records'] = $config['total_rows'];
        $data['franchiseFilter'] = $franchiseFilter;
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $data['is_admin'] = in_array($userRole, ['1']) ? 1 : 0; // Set is_admin for view
        $data['role'] = $userRole; // Pass role for view logic

        // Load view
        $this->loadViews('customdesign/list', $this->global, $data, NULL);
    }

    /**
     * This function is used to load the add new custom design form
     */
    public function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->global['pageTitle'] = 'CodeInsect : Add New Custom Design';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews('customdesign/add', $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new custom design to the system
     */
  public function addNewCustomdesign()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('designTitle', 'Design Title', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('attachmentType', 'Attachment Type', 'trim|required');
            $this->form_validation->set_rules('requirementSpe', 'Requirement', 'trim|required|max_length[1024]');
            $this->form_validation->set_rules('description', 'Description', 'trim|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
                return;
            }

            $designTitle = $this->security->xss_clean($this->input->post('designTitle'));
            $attachmentType = $this->security->xss_clean($this->input->post('attachmentType'));
            $requirementSpe = $this->security->xss_clean($this->input->post('requirementSpe'));
            $description = $this->security->xss_clean($this->input->post('description'));
            $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));

            $s3_file_links = [];

            if (!empty($_FILES['file']['name'][0])) {
                $files = $_FILES['file'];
                $file_count = count($files['name']);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'mp4', 'avi', 'mov', 'cdr'];

                for ($i = 0; $i < $file_count; $i++) {
                    if (!empty($files['name'][$i])) {
                        $tmp_name = $files['tmp_name'][$i];
                        $original_name = $files['name'][$i];
                        $dir = dirname($tmp_name);
                        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $original_name;

                        if (rename($tmp_name, $destination)) {
                            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                            if (!in_array($ext, $allowed_extensions)) {
                                $this->session->set_flashdata('error', 'File type not allowed: ' . $ext);
                                @unlink($destination);
                                redirect('customdesign/add');
                            }

                            $storeFolder = 'customdesigns';
                            $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                            $result_arr = $s3Result->toArray();

                            if (!empty($result_arr['ObjectURL'])) {
                                $s3_file_links[] = $result_arr['ObjectURL'];
                            } else {
                                $this->session->set_flashdata('error', 'Failed to upload ' . $files['name'][$i] . ' to S3.');
                                @unlink($destination);
                                redirect('customdesign/add');
                            }
                            @unlink($destination);
                        } else {
                            $this->session->set_flashdata('error', 'Failed to process file: ' . $files['name'][$i]);
                            redirect('customdesign/add');
                        }
                    }
                }
            }

            if (empty($s3_file_links)) {
                $this->session->set_flashdata('error', 'No valid files uploaded.');
                redirect('customdesign/add');
            }

            $s3files = implode(',', $s3_file_links);

            $customdesignInfo = array(
                'designTitle' => $designTitle,
                'attachmentType' => $attachmentType,
                'franchiseNumber' => $franchiseNumber,
                'requirementSpe' => $requirementSpe,
                'description' => $description ?: NULL,
                'attachmentS3File' => $s3files,
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->cdm->addNewCustomdesign($customdesignInfo);

            if ($result > 0) {
                // Load Notification_model
                $this->load->model('Notification_model', 'nm');
                $notificationMessage = "<strong>Custom Design Confirmation:</strong> New Custom Design request created";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [1, 14, 25, 27])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $this->nm->add_custom_design_notification($result, $notificationMessage, $userId);
                    }
                }

                // Email notification
                $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                if (!empty($branchDetail)) {
                    $to = $branchDetail->officialEmailID;
                    $subject = "Alert - eduMETA THE i-SCHOOL New Custom Design Request";
                    $message = 'Dear ' . $branchDetail->applicantName . ', ';
                    $message .= 'A new custom design request has been created. By: ' . $this->session->userdata("name") . '. ';
                    $message .= 'Please visit the portal.';
                    $headers = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";

                    @mail($to, $subject, $message, $headers);
                }

                $this->session->set_flashdata('success', 'New Custom Design request created successfully');
            } else {
                $this->session->set_flashdata('error', 'Custom Design request creation failed');
            }

            redirect('customdesign/customdesignListing');
        }
    }


     public function view($customdesignId = NULL)
    {
        if (!$this->hasListAccess()) {
            $this->loadThis();
        } else {
            if ($customdesignId == NULL) {
                $this->session->set_flashdata('error', 'Custom design not found');
                redirect('customdesign/customdesignListing');
            }

            $data['customdesignInfo'] = $this->cdm->getCustomdesignInfo($customdesignId);
            if (empty($data['customdesignInfo'])) {
                $this->session->set_flashdata('error', 'Custom design not found');
                redirect('customdesign/customdesignListing');
            }

            $this->global['pageTitle'] = 'CodeInsect : View Custom Design';
            $this->loadViews('customdesign/view', $this->global, $data, NULL);
        }
    }
    /**
     * This function is used to load custom design edit information
     * @param number $customdesignId : Optional : This is custom design id
     */
    public function edit($customdesignId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($customdesignId == null) {
                redirect('customdesign/customdesignListing');
            }

            $data['customdesignInfo'] = $this->cdm->getCustomdesignInfo($customdesignId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->global['pageTitle'] = 'CodeInsect : Edit Custom Design';

            $this->loadViews('customdesign/edit', $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to edit the custom design information
     */
   public function editCustomdesign()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $customdesignId = $this->input->post('customdesignId');

        $this->form_validation->set_rules('designTitle', 'Design Title', 'trim|required|max_length[256]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($customdesignId);
        } else {
            $designTitle = $this->security->xss_clean($this->input->post('designTitle'));
            $attachmentType = $this->security->xss_clean($this->input->post('attachmentType'));
            $requirementSpe = $this->security->xss_clean($this->input->post('requirementSpe'));
            $submissionDate = $this->security->xss_clean($this->input->post('submissionDate'));
            $description = $this->security->xss_clean($this->input->post('description'));

            // Handle file upload to S3
            $s3_file_link = [];
            if (!empty($_FILES['file']['name'])) {
                $dir = dirname($_FILES['file']['tmp_name']);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES['file']['name'];
                rename($_FILES['file']['tmp_name'], $destination);

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
                $s3files = $this->input->post('existing_attachmentS3File') ?? ''; // Retain existing file if no new file uploaded
            }

            $customdesignInfo = [
                'designTitle' => $designTitle,
                'attachmentType' => $attachmentType,
                'requirementSpe' => $requirementSpe,
                'submissionDate' => $submissionDate,
                'description' => $description,
                'attachmentS3File' => $s3files,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            ];

            $result = $this->cdm->editCustomdesign($customdesignInfo, $customdesignId);

            if ($result == true) {
                // Send notifications to users with roleId = 19 and 14
                $this->load->model('Notification_model');
                $notificationMessage = "<strong>Custom Design :</strong> Custom design ID {$customdesignId} has been updated.";

                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [19, 14, 25])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $this->Notification_model->add_custom_design_notification($customdesignId, $notificationMessage, $userId);
                    }
                }

                // Send email notification to the branch
                $customdesignInfo = $this->cdm->getCustomdesignInfo($customdesignId);
                $franchiseNumber = $customdesignInfo->franchiseNumber;
                $franchiseNumberArray = [$franchiseNumber];

                foreach ($franchiseNumberArray as $franchiseNumber) {
                    $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                    if (!empty($branchDetail)) {
                        $to = $branchDetail->officialEmailID;
                        $subject = 'Alert - eduMETA THE i-SCHOOL Custom Design Updated';
                        $message = "Dear {$branchDetail->applicantName},\n\n";
                        $message .= "A custom design (ID: {$customdesignId}) has been updated by " . $this->session->userdata('name') . ".\n";
                        $message .= "Please visit the portal to review the changes.\n\n";
                        $headers = 'From: Edumeta Team<noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com, sourabh.edumeta@gmail.com';
                        mail($to, $subject, $message, $headers);
                    }
                }

                $this->session->set_flashdata('success', 'Custom Design updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Custom Design updation failed');
            }

            redirect('customdesign/customdesignListing');
        }
    }
}
}
?>