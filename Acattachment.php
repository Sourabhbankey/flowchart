<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Task (TaskController)
 * Task Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Acattachment extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Acattachment_model', 'at');
		  $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
		 $this->load->library('pagination');
        $this->module = 'Acattachment';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('acattachment/acattachmentListing');
    }
    
    /**
     * This function is used to load the task list
 */
 
 //code done by yashi
	public function acattachmentListing() {
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');
  
	$franchiseFilter = $this->input->get('franchiseNumber');
    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = '';
    }
    $config = array();
    $config['base_url'] = base_url('acattachment/acattachmentListing');
    $config['per_page'] = 10; 
    $config['uri_segment'] = 3;
	$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

    if ($userRole == '14' || $userRole == '1'|| $userRole == '16'|| $userRole == '23' || $userRole == '28' || $userRole == '29' || $userRole == '31') { // Admin
        if ($franchiseFilter) {
            $config['total_rows'] = $this->at->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
			$data['records'] = $this->at->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
        } else {
            $config['total_rows'] = $this->at->getTotalTrainingRecordsCount();
            
            $data['records'] = $this->at->getAllTrainingRecords($config['per_page'], $page);
        }
     } else if ($userRole == '15' || $userRole == '13') { // Specific roles
    if ($franchiseFilter) {
        $config['total_rows'] = $this->at->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
        $data['records'] = $this->at->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
    } else {
        $config['total_rows'] = $this->at->getTotalTrainingRecordsCountByRole($userId);
        $data['records'] = $this->at->getTrainingRecordsByRole($userId, $config['per_page'], $page);
    }
} else { 
        $franchiseNumber = $this->at->getFranchiseNumberByUserId($userId);
        if ($franchiseNumber) {
            if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                $config['total_rows'] = $this->at->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
				$data['records'] = $this->at->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->at->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                $data['records'] = $this->at->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
            }
        } else {
            $data['records'] = []; // Handle the case where franchise number is not found
        }
    }

    // Initialize pagination
    $this->pagination->initialize($config);
	$data["links"] = $this->pagination->create_links();
    $data["start"] = $page + 1;
    $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
    $data["total_records"] = $config["total_rows"];
    $data['pagination'] = $this->pagination->create_links();
	$data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
	$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
    $this->loadViews("acattachment/list", $this->global, $data, NULL);
}
//ends here

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
            $this->global['pageTitle'] = 'CodeInsect : Add New Acattachment';
			$data['users'] = $this->at->get_users_without_franchise();
		$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();	
            $this->loadViews("acattachment/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
  public function addNewAcattachment()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
        return;
    }

    $this->load->library('form_validation');
    $this->form_validation->set_rules('acattachmentTitle', 'Attachment Title', 'trim|required|max_length[256]');
    $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

    if ($this->form_validation->run() == FALSE) {
        $this->add();
        return;
    }

    $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
    $acattachmentTitle = $this->security->xss_clean($this->input->post('acattachmentTitle'));
    $assignedBy = $this->vendorId;
    $dtOfInvoice = $this->security->xss_clean($this->input->post('dtOfInvoice'));
    $description = $this->security->xss_clean($this->input->post('description'));
    $assignedTo = $this->input->post('assignedTo');
    $byvrlOtherPosts = $this->security->xss_clean($this->input->post('byvrlOtherPosts'));
    $month = $this->security->xss_clean($this->input->post('month'));

    if (!is_array($franchiseNumberArray)) {
        $franchiseNumberArray = !empty($franchiseNumberArray) ? [$franchiseNumberArray] : [];
    }

    $franchiseNumbers = implode(',', $franchiseNumberArray);

    // Handle file upload
    $s3_file_link = [];
    if (!empty($_FILES["file"]["tmp_name"])) {
        $dir = dirname($_FILES["file"]["tmp_name"]);
        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
        if (rename($_FILES["file"]["tmp_name"], $destination)) {
            $storeFolder = 'attachements';
            $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
            $result_arr = $s3Result->toArray();
            $s3_file_link[] = $result_arr['ObjectURL'] ?? '';
        }
    }

    $s3files = implode(',', $s3_file_link);

    $acattachmentInfo = array(
        'acattachmentTitle' => $acattachmentTitle,
        'franchiseNumber' => $franchiseNumbers,
        'dtOfInvoice' => $dtOfInvoice,
        'description' => $description,
        'month' => $month,
        'acattachmentS3File' => $s3files,
        'vrlpostattachmentS3File' => $s3files,
        'byvrlOtherPosts' => $byvrlOtherPosts,
        'assignedTo' => $assignedTo,
        'assignedBy' => $assignedBy,
        'createdBy' => $this->vendorId,
        'createdDtm' => date('Y-m-d H:i:s')
    );

    $result = $this->at->addNewAcattachment($acattachmentInfo);

    $this->load->model('Notification_model');

    if ($result > 0) {
        $notificationMessage = "<strong>Account Attachment</strong>: A new attachment titled '{$acattachmentTitle}' has been created.";

        // ✅ Assigned User Notification
        if (!empty($assignedTo)) {
            $this->Notification_model->add_acattachment_notification($assignedTo, $notificationMessage, $result);
        }

        // ✅ Franchise User + Branch Email Notifications
        foreach ($franchiseNumberArray as $franchiseNumber) {
            $franchiseNumber = trim($franchiseNumber);

            // Franchise User Notification
            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
            if (!empty($franchiseUser)) {
                $this->Notification_model->add_acattachment_notification($franchiseUser->userId, $notificationMessage, $result);
            }

            // Branch Email
            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
            if (!empty($branchDetail)) {
                $to = $branchDetail->officialEmailID;
                $subject = "Alert - eduMETA THE i-SCHOOL: New Attachment Assigned";
                $message = "Dear {$branchDetail->applicantName},\n\n";
                $message .= "A new attachment titled '{$acattachmentTitle}' has been assigned to you by {$this->session->userdata('name')}.\n";
                $message .= "Description: {$description}\n";
                $message .= "Please visit the portal to view the details.\n\n";
                $message .= "Best regards,\nEduMETA Team";

                $headers = "From: EduMETA Team <noreply@theischool.com>\r\n";
                $headers .= "BCC: dev.edumeta@gmail.com\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                mail($to, $subject, $message, $headers);
            }
        }

        // ✅ Admin Notifications (roles 1 and 14)
        $adminUsers = $this->bm->getUsersByRoles([1, 14]);
        if (!empty($adminUsers)) {
            foreach ($adminUsers as $adminUser) {
                $this->Notification_model->add_acattachment_notification($adminUser->userId, $notificationMessage, $result);
            }
        }

        $this->session->set_flashdata('success', 'New Attachment created successfully');
    } else {
        $this->session->set_flashdata('error', 'Attachment creation failed');
    }

    redirect('acattachment/acattachmentListing');
}


    
    /**
     * This function is used load attachment edit information
     * @param number $attachmentId : Optional : This is attachment id
     */
    function edit($acattachmentId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($acattachmentId == null)
            {
                redirect('acattachment/acattachmentListing');
            }
            
            $data['acattachmentInfo'] = $this->at->getAcattachmentInfo($acattachmentId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Acattachment';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
			// $data['selectedFranchiseNumber'] = $this->bm->getSelectedFranchiseNumber($branchesId);
            $this->loadViews("acattachment/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
public function editAcattachment($acattachmentId = NULL)
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
        return;
    }

    $this->load->library('form_validation');
    $this->load->model('Notification_model');

    $this->form_validation->set_rules('acattachmentTitle', 'Attachment Title', 'trim|required|max_length[256]');
    $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

    if ($this->form_validation->run() == FALSE) {
        $this->edit($acattachmentId);
        return;
    }

    $acattachmentId     = $this->security->xss_clean($this->input->post('acattachmentId'));
    $acattachmentTitle  = $this->security->xss_clean($this->input->post('acattachmentTitle'));
    $dtOfInvoice        = $this->security->xss_clean($this->input->post('dtOfInvoice'));
    $description        = $this->security->xss_clean($this->input->post('description'));
    $assignedTo         = $this->security->xss_clean($this->input->post('assignedTo'));
    $byvrlOtherPosts    = $this->security->xss_clean($this->input->post('byvrlOtherPosts'));
    $month              = $this->security->xss_clean($this->input->post('month'));
    $existingFile       = $this->security->xss_clean($this->input->post('existing_acattachmentS3File'));

    // Ensure franchiseNumber is an array
    $rawFranchise = $this->input->post('franchiseNumber');
    $franchiseNumberArray = is_array($rawFranchise) ? $this->security->xss_clean($rawFranchise) : [$this->security->xss_clean($rawFranchise)];

    // S3 file upload
    $s3_file_link = [];
    if (!empty($_FILES["file"]["tmp_name"])) {
        $dir = dirname($_FILES["file"]["tmp_name"]);
        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
        if (rename($_FILES["file"]["tmp_name"], $destination)) {
            $storeFolder = 'attachments';
            $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
            $result_arr = $s3Result->toArray();
            $s3_file_link[] = $result_arr['ObjectURL'] ?? '';
        }
    }

    $s3files = !empty($s3_file_link) ? implode(',', $s3_file_link) : $existingFile;

    // Prepare update array
    $acattachmentInfo = [
        'acattachmentTitle' => $acattachmentTitle,
        'dtOfInvoice' => $dtOfInvoice,
        'description' => $description,
        'month' => $month,
        'acattachmentS3File' => $s3files,
        'vrlpostattachmentS3File' => $s3files,
        'byvrlOtherPosts' => $byvrlOtherPosts,
        'assignedTo' => $assignedTo,
        'updatedBy' => $this->vendorId,
        'updatedDtm' => date('Y-m-d H:i:s')
    ];

    $result = $this->at->editAcattachment($acattachmentInfo, $acattachmentId);

    if ($result) {
        $notificationMessage = "<strong>Account Attachment</strong>: An attachment has been updated.";

        // ✅ 1. Notify Assigned User
        if (!empty($assignedTo)) {
            $this->Notification_model->add_acattachment_notification($assignedTo, $notificationMessage, $acattachmentId);
        }

        // ✅ 2. Notify Franchise Users & Send Email
        foreach ($franchiseNumberArray as $franchiseNumber) {
            $franchiseNumber = trim($franchiseNumber);

            // Email
            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
            if (!empty($branchDetail)) {
                $to = $branchDetail->officialEmailID;
                $subject = "Alert - eduMETA THE i-SCHOOL: Attachment Updated";
                $message = "Dear {$branchDetail->applicantName},\n\n";
                $message .= "An attachment titled '{$acattachmentTitle}' has been updated by {$this->session->userdata('name')}.\n";
                $message .= "Description: {$description}\n";
                $message .= "Please visit the portal to view the updated details.\n\n";
                $message .= "Best regards,\nEduMETA Team";

                $headers = "From: EduMETA Team <noreply@theischool.com>\r\n";
                $headers .= "BCC: dev.edumeta@gmail.com\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                mail($to, $subject, $message, $headers);
            }

            // Franchise User Notification
            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
            if (!empty($franchiseUser)) {
                $this->Notification_model->add_acattachment_notification($franchiseUser->userId, $notificationMessage, $acattachmentId);
            }
        }

        // ✅ 3. Notify Admins (roles: 1, 14, 16)
        $adminUsers = $this->bm->getUsersByRoles([1, 14, 16]);
        if (!empty($adminUsers)) {
            foreach ($adminUsers as $adminUser) {
                $this->Notification_model->add_acattachment_notification($adminUser->userId, $notificationMessage, $acattachmentId);
            }
        }

        $this->session->set_flashdata('success', 'Attachment updated successfully');
    } else {
        $this->session->set_flashdata('error', 'Attachment update failed');
    }

    redirect('acattachment/acattachmentListing');
}



    public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Fetch the users based on the franchise number
    $users = $this->at->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

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
    $s3_url = 'https://support-smsfiles.s3.amazonaws.com/' . $filename;
    $file_data = file_get_contents($s3_url);
    if ($file_data) {
        force_download($s3_url, $file_data);
    } else {
        echo "File not found!";
    }
}
}

?>