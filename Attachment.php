<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Task (TaskController)
 * Task Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Attachment extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Attachment_model', 'am');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->load->library('pagination');
        $this->module = 'Attachment';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('attachment/attachmentListing');
    }

    /**
     * This function is used to load the task list
     */
    /** function attachmentListing()
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
            
            $count = $this->am->attachmentListingCount($searchText);

			$returns = $this->paginationCompress ( "attachmentListing/", $count, 10 );
            
            $data['records'] = $this->am->attachmentListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Attachment';
            
            $this->loadViews("attachment/list", $this->global, $data, NULL);
        }
    }
     */

    public function view($attachmentId = NULL)
    {
        if (!$this->hasListAccess()) {
            $this->loadThis();
        } else {
            if ($attachmentId == null) {
                redirect('attachment/attachmentListing');
            }

            $data['attachmentInfo'] = $this->am->getAttachmentInfo($attachmentId);
            if (empty($data['attachmentInfo'])) {
                $this->session->set_flashdata('error', 'Attachment not found');
                redirect('attachment/attachmentListing');
            }

            $this->global['pageTitle'] = 'CodeInsect : View Attachment Image';
            $this->loadViews("attachment/view", $this->global, $data, NULL);
        }
    }



    public function attachmentListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        // Get the franchise filter value
        $franchiseFilter = $this->input->get('franchiseNumber');

        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }

        $config = array();
        $config['base_url'] = base_url('attachment/attachmentListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;

        // Safely handle page number from URI segment
        $page = 0; // Default to 0 if no page is provided or the URI segment is invalid
        $uri_segment = $this->uri->segment(3);
        if ($uri_segment && is_numeric($uri_segment)) {
            $page = (int)$uri_segment;  // Cast to integer
        }

        // Handle role-based record fetching
        if ($userRole == '14' || $userRole == '1' || $userRole == '27' || $userRole == '15' || $userRole == '19') { // Admin
            if ($franchiseFilter) {
                $config['total_rows'] = $this->am->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                $data['records'] = $this->am->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->am->getTotalTrainingRecordsCount();
                $data['records'] = $this->am->getAllTrainingRecords($config['per_page'], $page);
            }
        } else if ($userRole == '15' || $userRole == '23' || $userRole == '27' || $userRole == '23' || $userRole == '19') { // Specific roles
            $config['total_rows'] = $this->am->getTotalTrainingRecordsCountByRole($userId);
            log_message('debug', 'Total Rows (Role Specific): ' . $config['total_rows']); // Debugging line
            $data['records'] = $this->am->getTrainingRecordsByRole($userId, $config['per_page'], $page);
        } else {
            // Fetch records based on franchise number for specific users
            $franchiseNumber = $this->am->getFranchiseNumberByUserId($userId);
            if ($franchiseNumber) {
                if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                    $config['total_rows'] = $this->am->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->am->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->am->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->am->getdmfranchseRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
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

        // Load the view
        $this->loadViews("attachment/list", $this->global, $data, NULL);
    }

    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->global['pageTitle'] = 'CodeInsect : Add New Attachment';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $data['users'] = $this->am->get_users_without_franchise();
            $this->loadViews("attachment/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
  public function addNewAttachment()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('attachmentTitle', 'Attachment Title', 'trim|required|max_length[256]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');
        $this->form_validation->set_rules('franchiseNumber[]', 'Franchise', 'trim|required');
        $this->form_validation->set_rules('linkAttachment', 'Link Attachment', 'trim|valid_url|max_length[512]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
            return;
        }

        $attachmentTitle = $this->security->xss_clean($this->input->post('attachmentTitle'));
        $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
        $description = $this->security->xss_clean($this->input->post('description'));
        $linkAttachment = $this->security->xss_clean($this->input->post('linkAttachment'));

       /* $this->load->library('upload');
        $s3_file_links = [];

        $files = $_FILES['file'];
        $file_count = count($files['name']);

        // Define allowed file types and their size limits (in KB)
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'mp4', 'avi', 'mov', 'cdr'];

        for ($i = 0; $i < $file_count; $i++) {
            if (!empty($files['name'][$i])) {
                $_FILES['file_single']['name'] = $files['name'][$i];
                $_FILES['file_single']['type'] = $files['type'][$i];
                $_FILES['file_single']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['file_single']['error'] = $files['error'][$i];
                $_FILES['file_single']['size'] = $files['size'][$i];

                // Get extension
                $ext = strtolower(pathinfo($_FILES['file_single']['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_extensions)) {
                    $this->session->set_flashdata('error', 'File type not allowed: ' . $ext);
                    redirect('attachment/add');
                }

                $config['upload_path'] = './Uploads/';
                $config['encrypt_name'] = TRUE;
                $config['allowed_types'] = implode('|', $allowed_extensions);

                // Size limit per file type
                if (in_array($ext, ['mp4', 'avi', 'mov'])) {
                    $config['max_size'] = 51200; // 50 MB for videos
                } elseif ($ext === 'cdr') {
                    $config['max_size'] = 10240; // 10 MB for CDR files
                } else {
                    $config['max_size'] = 5120; // 5 MB for images
                }

                $this->upload->initialize($config);

                if ($this->upload->do_upload('file_single')) {
                    $upload_data = $this->upload->data();
                    $destination = $upload_data['full_path'];
                    $storeFolder = 'attachments';

                    // Upload to S3
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    if (!empty($result_arr['ObjectURL'])) {
                        $s3_file_links[] = $result_arr['ObjectURL'];
                    } else {
                        $this->session->set_flashdata('error', 'Failed to upload ' . $files['name'][$i] . ' to S3.');
                        @unlink($destination);
                        redirect('attachment/add');
                    }
                    @unlink($destination);
                } else {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('attachment/add');
                }
            }
        }

        if (empty($s3_file_links)) {
            $this->session->set_flashdata('error', 'No valid files uploaded.');
            redirect('attachment/add');
        }

        $s3files = implode(',', $s3_file_links);*/
         $s3_file_link = [];

if (!empty($_FILES['file']['name'][0])) {
    $files = $_FILES['file'];
    $file_count = count($files['name']);

    for ($i = 0; $i < $file_count; $i++) {
        if (!empty($files['name'][$i])) {
            // Create temporary file structure for each file
            $tmp_name = $files['tmp_name'][$i];
            $original_name = $files['name'][$i];
            $dir = dirname($tmp_name);
            $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $original_name;

            if (rename($tmp_name, $destination)) {
                $storeFolder = 'attachments'; // Ensure spelling is correct

                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();

                if (!empty($result_arr['ObjectURL'])) {
                    $s3_file_link[] = $result_arr['ObjectURL'];
                } else {
                    $s3_file_link[] = '';
                }

                @unlink($destination); // Clean up temp file
            } else {
                $s3_file_link[] = '';
            }
        }
    }
} else {
    $s3_file_link[] = '';
}

// Final S3 URLs as comma-separated string
$s3files = implode(',', array_filter($s3_file_link)); // Optional: remove empty entries

        $franchiseNumbers = implode(',', $franchiseNumberArray);

        $attachmentInfo = array(
            'attachmentTitle' => $attachmentTitle,
            'attachmentType' => 'Mixed', // Mixed because multiple formats allowed
            'franchiseNumber' => $franchiseNumbers,
            'description' => $description,
            'attachmentS3File' => $s3files,
            'linkAttachment' => $linkAttachment ?: NULL,
            'createdBy' => $this->vendorId,
            'createdDtm' => date('Y-m-d H:i:s')
        );

        $result = $this->am->addNewAttachment($attachmentInfo);

        if ($result > 0) {
            // Load Notification_model
            $this->load->model('Notification_model', 'nm');
            $notificationMessage = "<strong>Attachment Confirmation:</strong> New Attachment confirmation";
            $users = $this->db->select('userId')
                ->from('tbl_users')
                ->where_in('roleId', [1, 14, 25, 27])
                ->get()
                ->result_array();

            if (!empty($users)) {
                $userIds = array_column($users, 'userId');
                foreach ($userIds as $userId) {
                    $this->nm->add_attachment_notification($result, $notificationMessage, $userId);
                }
            }

            // Email notification
            foreach ($franchiseNumberArray as $franchiseNumber) {
                $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                if (!empty($branchDetail)) {
                    $to = $branchDetail->officialEmailID;
                    $subject = "Alert - eduMETA THE i-SCHOOL Assign New Attachment";
                    $message = 'Dear ' . $branchDetail->applicantName . ', ';
                    $message .= 'A new attachment has been assigned. By: ' . $this->session->userdata("name") . '. ';
                    $message .= 'Please visit the portal.';
                    $headers = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";

                    @mail($to, $subject, $message, $headers);
                }
            }

            $this->session->set_flashdata('success', 'New Attachment created successfully');
        } else {
            $this->session->set_flashdata('error', 'Attachment creation failed');
        }

        redirect('attachment/attachmentListing');
    }
}




    public function downloadFile($attachmentId = NULL, $fileIndex = NULL)
    {
        if (!$this->hasListAccess()) {
            $this->loadThis();
            return;
        }

        if ($attachmentId == NULL || $fileIndex === NULL) {
            $this->session->set_flashdata('error', 'Invalid attachment or file index');
            redirect('attachment/view/' . $attachmentId);
        }

        $attachmentInfo = $this->am->getAttachmentInfo($attachmentId);
        if (empty($attachmentInfo)) {
            $this->session->set_flashdata('error', 'Attachment not found');
            redirect('attachment/view/' . $attachmentId);
        }

        $file_paths = explode(',', $attachmentInfo->attachmentS3File);
        if (!isset($file_paths[$fileIndex]) || empty($file_paths[$fileIndex])) {
            $this->session->set_flashdata('error', 'File not found');
            redirect('attachment/view/' . $attachmentId);
        }

        $file_url = $file_paths[$fileIndex];
        $file_name = basename($file_url);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $content_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime'
        ];
        $content_type = isset($content_types[$file_extension]) ? $content_types[$file_extension] : 'application/octet-stream';

        try {
            $s3Client = $this->s3_upload->getS3Client();
            $bucket = 's3_file_link'; // Replace with your actual S3 bucket name
            $key = parse_url($file_url, PHP_URL_PATH);
            $key = ltrim($key, '/');

            $result = $s3Client->getObject([
                'Bucket' => $bucket,
                'Key' => $key
            ]);

            header('Content-Type: ' . $content_type);
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Content-Length: ' . $result['ContentLength']);
            echo $result['Body'];
            exit;
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Failed to download file: ' . $e->getMessage());
            redirect('attachment/view/' . $attachmentId);
        }
    }

    /**
     * This function is used load attachment edit information
     * @param number $attachmentId : Optional : This is attachment id
     */
    function edit($attachmentId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($attachmentId == null) {
                redirect('attachment/attachmentListing');
            }

            $data['attachmentInfo'] = $this->am->getAttachmentInfo($attachmentId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->global['pageTitle'] = 'CodeInsect : Edit Attachment';

            $this->loadViews("attachment/edit", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to edit the user information
     */
    function editAttachment()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $attachmentId = $this->input->post('attachmentId');

            $this->form_validation->set_rules('attachmentTitle', 'Attachment Title', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');
            $this->form_validation->set_rules('franchiseNumber[]', 'Franchise', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $this->edit($attachmentId);
            } else {
                $attachmentTitle = $this->security->xss_clean($this->input->post('attachmentTitle'));
                $attachmentType = $this->security->xss_clean($this->input->post('attachmentType'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseNumbers = implode(',', $franchiseNumberArray);

                $attachmentInfo = array(
                    'attachmentTitle' => $attachmentTitle,
                    'description' => $description,
                    'franchiseNumber' => $franchiseNumbers,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->am->editAttachment($attachmentInfo, $attachmentId);

                if ($result == true) {
                    // Load Notification_model
                      $this->load->model('Notification_model', 'nm');

                // Send notifications to users with roleId 19, 14, 25
                $notificationMessage = "<strong>Attachment Confirmation:</strong> Update Attachment confirmation";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [1, 14, 25, 27])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $notificationResult = $this->nm->add_attachment_notification($result, $notificationMessage, $userId);
                        if (!$notificationResult) {
                            log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                        }
                    }
                }
                    // Send email notifications
                    if (!empty($franchiseNumberArray)) {
                        foreach ($franchiseNumberArray as $franchiseNumber) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if (!empty($branchDetail)) {
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Attachment Updated";
                                $message = 'Dear ' . $branchDetail->applicantName . ', ';
                                $message .= 'The ' . $attachmentType . ' attachment "' . $attachmentTitle . '" has been updated. By: ' . $this->session->userdata("name") . '. ';
                                $message .= 'Please visit the portal.';
                                $headers = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                if (!mail($to, $subject, $message, $headers)) {
                                    log_message('error', "Failed to send email to {$to} for attachment ID {$attachmentId}");
                                }
                            }
                        }
                    }

                    $this->session->set_flashdata('success', 'Attachment updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Attachment updation failed');
                }

                redirect('attachment/attachmentListing');
            }
        }
    }
}
