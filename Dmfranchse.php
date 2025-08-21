<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';
/* require_once(APPPATH . 'third_party/razorpay/Razorpay.php');
    use Razorpay\Api\Api;*/

/**
 * Class : Dmfranchse (DmfranchseController)
 * Dmfranchse Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 03 June 2024
 */
class Dmfranchse extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Dmfranchse_model', 'cf');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Dmfranchse';
        $this->load->library("pagination");
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('dmfranchse/dmfranchseListing');
    }

    /**
     * This function is used to load the Dmfranchise list
     */

    public function dmfranchseListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        $franchiseFilter = $this->input->get('franchiseNumber');
        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
        $config = array();
        $config['base_url'] = base_url('dmfranchse/dmfranchseListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        if ($userRole == '14' || $userRole == '1' || $userRole == '18' || $userRole == '20' || $userRole == '28' || $userRole == '29' || $userRole == '31') { // Admin
            if ($franchiseFilter) {
                $config['total_rows'] = $this->cf->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                $data['records'] = $this->cf->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->cf->getTotalTrainingRecordsCount();

                $data['records'] = $this->cf->getAllTrainingRecords($config['per_page'], $page);
            }
        } else if ($userRole == '15') { // Specific roles
            $config['total_rows'] = $this->cf->getTotalTrainingRecordsCountByRole($userId);
            $data['records'] = $this->cf->getTrainingRecordsByRole($userId, $config['per_page'], $page);
        } else {
            $franchiseNumber = $this->cf->getFranchiseNumberByUserId($userId);
            if ($franchiseNumber) {
                if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                    $config['total_rows'] = $this->cf->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->cf->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->cf->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->cf->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                }
            } else {
                $data['records'] = []; // Handle the case where franchise number is not found
            }
        }

        // Initialize pagination
        $serial_no = $page + 1;
        $this->pagination->initialize($config);
        $data["links"] = $this->pagination->create_links();
        $data["start"] = $page + 1;
        $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];
        $data['pagination'] = $this->pagination->create_links();
        $data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $data["serial_no"] = $serial_no;
        $this->loadViews("dmfranchse/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Campaign';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("dmfranchse/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    function addNewDmfranchse()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('dmfranchseTitle', 'Campaign Title', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                $dmfranchseTitle = $this->security->xss_clean($this->input->post('dmfranchseTitle'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $doneBy = $this->security->xss_clean($this->input->post('doneBy'));
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $numOfLeads = $this->security->xss_clean($this->input->post('numOfLeads'));
                $dateOfrequest = $this->security->xss_clean($this->input->post('dateOfrequest'));
                $CampaStartdate = $this->security->xss_clean($this->input->post('CampaStartdate'));
                $CampaEnddate = $this->security->xss_clean($this->input->post('CampaEnddate'));
                $platform = $this->security->xss_clean($this->input->post('platform'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $amount = $this->security->xss_clean($this->input->post('amount'));

                $franchiseNumbers = implode(',', $franchiseNumberArray);

                // File upload handling
                $s3_file_link = [];
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['file']['tmp_name'];
                    $fileName = time() . '-' . $_FILES['file']['name'];
                    $destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

                    if (move_uploaded_file($fileTmpPath, $destination)) {
                        $storeFolder = 'attachments';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();

                        if (!empty($result_arr['ObjectURL'])) {
                            $s3_file_link[] = $result_arr['ObjectURL'];
                        } else {
                            log_message('error', 'S3 upload failed for file: ' . $fileName);
                        }
                    } else {
                        log_message('error', 'Failed to move uploaded file: ' . $fileName);
                    }
                } else {
                    log_message('error', 'File upload error: ' . ($_FILES['file']['error'] ?? 'No file uploaded'));
                }

                $s3files = implode(',', $s3_file_link);

                // 2nd attachment
                $s3_file_links = [];
                if (isset($_FILES['file2']) && $_FILES['file2']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['file2']['tmp_name'];
                    $fileName2 = time() . '-' . $_FILES['file2']['name'];
                    $destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName2;

                    if (move_uploaded_file($fileTmpPath, $destination)) {
                        $storeFolder = 'attachments';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();

                        if (!empty($result_arr['ObjectURL'])) {
                            $s3_file_links[] = $result_arr['ObjectURL'];
                        } else {
                            log_message('error', 'S3 upload failed for file: ' . $fileName2);
                        }
                    } else {
                        log_message('error', 'Failed to move uploaded file: ' . $fileName2);
                    }
                } else {
                    if (isset($_FILES['file2'])) {
                        log_message('error', 'File upload error: ' . $_FILES['file2']['error']);
                    } else {
                        log_message('info', 'No file uploaded for file2.');
                    }
                }

                $s3files2 = implode(',', $s3_file_links);

                // Prepare data for insertion
                $dmfranchiseInfo = array(
                    'dmfranchseTitle' => $dmfranchseTitle,
                    'doneBy' => $doneBy,
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'numOfLeads' => $numOfLeads,
                    'dateOfrequest' => $dateOfrequest,
                    'CampaStartdate' => $CampaStartdate,
                    'CampaEnddate' => $CampaEnddate,
                    'platform' => $platform,
                    'franchiseNumber' => $franchiseNumbers,
                    'description' => $description,
                    'dmattachmentS3file' => $s3files,
                    'dmreceiptattachmentS3file' => $s3files2,
                    'amount' => $amount,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->cf->addNewDmfranchse($dmfranchiseInfo);

                if ($result > 0) {
                    $this->load->model('Notification_model');

                // ✅ Send Notification to Assigned Franchise User
                if (!empty($brspFranchiseAssigned)) {
                    $notificationMessage = "A new Digital Marketing has been assigned to you.";
                    $this->Notification_model->add_dmfranchse_notification($brspFranchiseAssigned, $notificationMessage, $result);
                }

                // ✅ Send Notification to Users mapped with Franchise Numbers
                if (!empty($franchiseNumberArray)) {
                    foreach ($franchiseNumberArray as $franchiseNumber) {
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                        if (!empty($branchDetail)) {
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL Assign New Support Meeting";
                            $message = 'Dear ' . $branchDetail->applicantName . ', ';
                            $message .= 'You have been assigned a new meeting by ' . $this->session->userdata("name") . '. ';
                            $message .= 'Please visit the portal.';
                            $headers = "From: Edumeta Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                            mail($to, $subject, $message, $headers);

                            // ✅ Get User ID mapped with this Franchise Number
                            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                            if (!empty($franchiseUser)) {
                                $notificationMessage = "A Digital Marketing has been assigned.";
                                $this->Notification_model->add_dmfranchse_notification($franchiseUser->userId, $notificationMessage, $result);
                            }
                            // ✅ Notify Admins (roleId = 1, 14)
                $adminUsers = $this->bm->getUsersByRoles([1, 14, 18]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $this->Notification_model->add_dmfranchse_notification($adminUser->userId, "A new Digital Marketing has been added.", $result);
                    }
                }
                        }
                    }
                }
                    $this->session->set_flashdata('success', 'New Digital Marketing Franchise Campaign created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Campaign creation failed');
                }

                redirect('dmfranchse/dmfranchseListing');
            }
        }
    }


    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($dmfranchseId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($dmfranchseId == null) {
                redirect('dmfranchse/dmfranchseListing');
            }

            $data['dmfranchseInfo'] = $this->cf->getDmfranchseInfo($dmfranchseId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Digital Marketing Franchise';

            $this->loadViews("dmfranchse/edit", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to edit the user information
     */
    function editDmfranchse()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $dmfranchseId = $this->input->post('dmfranchseId');

            $this->form_validation->set_rules('dmfranchseTitle', 'Campaign Title', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->edit($dmfranchseId);
            } else {
                $dmfranchseTitle = $this->security->xss_clean($this->input->post('dmfranchseTitle'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $doneBy = $this->security->xss_clean($this->input->post('doneBy'));
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $numOfLeads = $this->security->xss_clean($this->input->post('numOfLeads'));
                $dateOfrequest = $this->security->xss_clean($this->input->post('dateOfrequest'));
                $CampaStartdate = $this->security->xss_clean($this->input->post('CampaStartdate'));
                $CampaEnddate = $this->security->xss_clean($this->input->post('CampaEnddate'));
                $platform = $this->security->xss_clean($this->input->post('platform'));
                $amount = $this->security->xss_clean($this->input->post('amount'));

                // File upload handling for dmattachmentS3file
                $s3_file_link = [];
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['file']['tmp_name'];
                    $fileName = time() . '-' . $_FILES['file']['name'];
                    $destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

                    if (move_uploaded_file($fileTmpPath, $destination)) {
                        $storeFolder = 'attachments';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();

                        if (!empty($result_arr['ObjectURL'])) {
                            $s3_file_link[] = $result_arr['ObjectURL'];
                        } else {
                            log_message('error', 'S3 upload failed for file: ' . $fileName);
                        }
                    } else {
                        log_message('error', 'Failed to move uploaded file: ' . $fileName);
                    }
                }
                $s3files = !empty($s3_file_link) ? implode(',', $s3_file_link) : $this->input->post('existing_dmattachmentS3file') ?? '';

                // File upload handling for dmreceiptattachmentS3file
                $s3_file_link2 = [];
                if (isset($_FILES['file2']) && $_FILES['file2']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['file2']['tmp_name'];
                    $fileName2 = time() . '-' . $_FILES['file2']['name'];
                    $destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName2;

                    if (move_uploaded_file($fileTmpPath, $destination)) {
                        $storeFolder = 'attachments';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();

                        if (!empty($result_arr['ObjectURL'])) {
                            $s3_file_link2[] = $result_arr['ObjectURL'];
                        } else {
                            log_message('error', 'S3 upload failed for file: ' . $fileName2);
                        }
                    } else {
                        log_message('error', 'Failed to move uploaded file: ' . $fileName2);
                    }
                }
                $s3files2 = !empty($s3_file_link2) ? implode(',', $s3_file_link2) : $this->input->post('existing_dmreceiptattachmentS3file') ?? '';

                // Retrieve franchiseNumber from the existing record
                $dmfranchseInfo = $this->cf->getDmfranchseInfo($dmfranchseId);
                $franchiseNumbers = $dmfranchseInfo->franchiseNumber;
                $franchiseNumberArray = explode(',', $franchiseNumbers);

                $dmfranchiseInfo = [
                    'dmfranchseTitle' => $dmfranchseTitle,
                    'doneBy' => $doneBy,
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'numOfLeads' => $numOfLeads,
                    'dateOfrequest' => $dateOfrequest,
                    'CampaStartdate' => $CampaStartdate,
                    'CampaEnddate' => $CampaEnddate,
                    'platform' => $platform,
                    'franchiseNumber' => $franchiseNumbers,
                    'description' => $description,
                    'dmattachmentS3file' => $s3files,
                    'dmreceiptattachmentS3file' => $s3files2,
                    'amount' => $amount,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                ];

                $result = $this->cf->editDmfranchse($dmfranchiseInfo, $dmfranchseId);

                if ($result == true) {
                    $this->load->model('Notification_model');

                // ✅ Send Notification to Assigned Franchise User
               
                if (!empty($brspFranchiseAssigned)) {
                    $notificationMessage = " Campaign has been Updated .";
                    $this->Notification_model->add_dmfranchse_notification($brspFranchiseAssigned, $notificationMessage, $result);
                }
  
               // ✅ Send Notification to Users mapped with Franchise Numbers
                if (!empty($franchiseNumberArray)) {
                    foreach ($franchiseNumberArray as $franchiseNumber) {
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                        if (!empty($branchDetail)) {
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL Assign New Support Meeting";
                            $message = 'Dear ' . $branchDetail->applicantName . ', ';
                            $message .= 'You have been assigned a new meeting by ' . $this->session->userdata("name") . '. ';
                            $message .= 'Please visit the portal.';
                            $headers = "From: Edumeta Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                            mail($to, $subject, $message, $headers);

                            // ✅ Get User ID mapped with this Franchise Number
                            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                            if (!empty($franchiseUser)) {
                                $notificationMessage = "Campaign has been Updated.";
                                $this->Notification_model->add_dmfranchse_notification($franchiseUser->userId, $notificationMessage, $result);
                            }
                            // ✅ Notify Admins (roleId = 1, 14)
                $adminUsers = $this->bm->getUsersByRoles([1, 14,18]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $this->Notification_model->add_dmfranchse_notification($adminUser->userId, "Campaign has been Updated.", $result);
                    }
                }
                        }
                    }
                }
                    $this->session->set_flashdata('success', 'Campaign updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Campaign updation failed');
                }

                redirect('dmfranchse/dmfranchseListing');
            }
        }
    }
    /** Code editor */
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
        $users = $this->cf->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

        // Generate HTML options for the response
        $options = '<option value="0">Select Role</option>';
        foreach ($users as $user) {
            $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
        }

        echo $options; // Output the options as HTML
    }
    public function download_image($filename)
    {
        $this->load->helper('download');

        // Ensure the filename is clean
        $filename = basename(urldecode($filename));

        $s3_url = 'https://support-smsfiles.s3.ap-south-1.amazonaws.com/attachments/' . $filename;

        // Debugging: Print the actual filename
        echo "Downloading file: " . htmlspecialchars($filename) . "<br>";

        // Get file content
        $file_data = @file_get_contents($s3_url);

        if ($file_data === false) {
            die("Error: File not found or permission denied.");
        }

        // Set headers for proper download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($file_data));

        // Output the file
        echo $file_data;
        exit;
    }

    public function verifyPayment()
    {


        echo '1';
        exit;
        /*$payment_id = $this->input->post('razorpay_payment_id');

    if (!$payment_id) {
        show_error('No payment ID received');
    }

    // Include Razorpay PHP SDK
   

    $api_key = 'YOUR_RAZORPAY_KEY';
    $api_secret = 'YOUR_RAZORPAY_SECRET';

    try {
        $api = new Api($api_key, $api_secret);

        // Fetch payment details
        $payment = $api->payment->fetch($payment_id);

        // You can now verify amount, currency, status, etc.
        if ($payment->status == 'captured') {
            // Insert or update payment in your DB
            $data = [
                'payment_id' => $payment_id,
                'amount' => $payment->amount / 100,
                'status' => $payment->status,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('tbl_dmfranchise_payments', $data);

            echo 'success';
        } else {
            echo 'Payment not captured';
        }

    } catch (Exception $e) {
        log_message('error', 'Razorpay Error: ' . $e->getMessage());
        echo 'Verification failed';
    }*/
    }
}
