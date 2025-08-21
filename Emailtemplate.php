<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Emailtemplate (EmailtemplateController)
 * Emailtemplate Class to control Emailtemplate related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 05 June 2025
 */
class Emailtemplate extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Emailtemplate_model', 'emltemp');
        $this->load->model('Branches_model', 'bm');
        $this->load->library(['upload', 'email']);
        $this->load->helper(['url', 'form']);
        $this->isLoggedIn();
        $this->module = 'Emailtemplate';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('Emailtemplate/EmailtemplateListing');
    }

    /**
     * This function is used to load the Emailtemplate list
     */
    function EmailtemplateListing()
    {

        $searchText = '';
        if (!empty($this->input->post('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
        }
        $data['searchText'] = $searchText;

        $this->load->library('pagination');

        $count = $this->emltemp->EmailtemplateListingCount($searchText);

        $returns = $this->paginationCompress("Emailtemplate/EmailtemplateListing/", $count, 10);

        $data['records'] = $this->emltemp->EmailtemplateListing($searchText, $returns["page"], $returns["segment"]);

        $this->global['pageTitle'] = 'CodeInsect : Emailtemplate';

        $this->loadViews("emailtemplate/list", $this->global, $data, NULL);
    }

    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->global['pageTitle'] = 'CodeInsect : Add New Emailtemplate';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $defaultFranchise = $this->session->userdata('franchiseNumber') ?: '';
        $data['branchEmail'] = $defaultFranchise ? $this->emltemp->getBranchEmail($defaultFranchise) : '';
            $this->loadViews("emailtemplate/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    /*function addNewEmailtemplate()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('email_body','Email Body Content','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $franchiseRaw = $this->input->post('franchiseNumber');
                $franchiseNumber = is_array($franchiseRaw) ? implode(',', 
                $this->security->xss_clean($franchiseRaw)) : $this->security->xss_clean($franchiseRaw);
                $emailTemp_type = $this->security->xss_clean($this->input->post('emailTemp_type'));
                $title = $this->security->xss_clean($this->input->post('title'));
                $to_email = $this->security->xss_clean($this->input->post('to_email'));
                $email_body = $this->security->xss_clean($this->input->post('email_body'));
                $EmailtemplateInfo = array(
                    'franchiseNumber' => $franchiseNumber,
                    'emailTemp_type' => $emailTemp_type,
                    'title'  => $title,
                    'to_email'  => $to_email,
                    'email_body' => $email_body,
                    'isDeleted'  => 0,
                    'createdBy'  => $this->vendorId, // make sure $this->vendorId is set
                    'createdDtm' => date('Y-m-d H:i:s')
                );
                $result = $this->emltemp->addNewEmailtemplate($EmailtemplateInfo);
                if($result > 0) {

                    // Email notification to admin
                    $adminEmail = 'dev.edumeta@gmail.com'; 
                    $adminSubject = "Alert - eduMETA THE i-SCHOOL New Email Template Submitted";
                    $adminMessage = 'A new Emailtemplate details record has been submitted. ';
                    $adminMessage .= 'Franchise Name: ' . $franchiseName . ', Franchise Number: ' . $franchiseNumber . '. ';
                    $adminMessage .= 'Please visit the portal for details.';
                    $adminHeaders = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                    mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);

                    $this->session->set_flashdata('success', 'New Email Template created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Email Template creation failed');
                }
                
                redirect('Emailtemplate/EmailtemplateListing');
            }
        }
    }*/
function addNewEmailtemplate()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis(); // Fixed typo from $this->load()
        return;
    }

    $this->load->library('form_validation');
    $this->form_validation->set_rules('email_body', 'Email Body Content', 'trim|required|max_length[5000]');
    $this->form_validation->set_rules('to_email', 'Recipient Email', 'trim|required');

    if (!$this->form_validation->run()) {
        $this->add();
        return;
    }

    // Sanitize inputs
    $franchiseRaw = $this->input->post('franchiseNumber');
    $franchiseNumber = is_array($franchiseRaw)
        ? implode(',', $this->security->xss_clean($franchiseRaw))
        : $this->security->xss_clean($franchiseRaw);
    $emailTemp_type = $this->security->xss_clean($this->input->post('emailTemp_type'));
    $title = $this->security->xss_clean($this->input->post('title'));
    $to_email = trim($this->security->xss_clean($this->input->post('to_email')));
    $email_body = $this->input->post('email_body'); // Allow HTML

    // Validate user email(s)
    $email_list = array_filter(array_map('trim', explode(',', $to_email)), function ($email) {
        return !empty($email);
    });
    foreach ($email_list as $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', 'Invalid user email address provided: ' . $email);
            redirect('Emailtemplate/add');
            return;
        }
    }

    // Validate admin email
    $adminEmail = 'sourabh.edumeta@gmail.com,dev.edumeta@gmail.com';
    $admin_email_list = array_filter(array_map('trim', explode(',', $adminEmail)), function ($email) {
        return !empty($email);
    });
    foreach ($admin_email_list as $admin_email) {
        if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', 'Invalid admin email address: ' . $admin_email);
            redirect('Emailtemplate/add');
            return;
        }
    }

    $to_email_list = array_unique($email_list);

    $attachment_path = NULL;
    $is_image = FALSE;
    $file_extension = '';

    // Handle file upload
    if (!empty($_FILES['attachment']['name'])) {
        $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/png'];
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_tmp_path = $_FILES['attachment']['tmp_name'];
        $file_name = $_FILES['attachment']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_mime = mime_content_type($file_tmp_path);

        // Validate file size (5MB limit)
        if ($_FILES['attachment']['size'] > 5000000) {
            $this->session->set_flashdata('error', 'File size exceeds 5MB limit.');
            redirect('Emailtemplate/add');
            return;
        }

        // Validate MIME type and extension
        if (!in_array($file_mime, $allowed_mime_types) || !in_array($file_ext, $allowed_extensions)) {
            $this->session->set_flashdata('error', 'Invalid file type. Only PDF, JPG, and PNG files are allowed.');
            redirect('Emailtemplate/add');
            return;
        }

        $is_image = strpos($file_mime, 'image/') === 0;
        $file_extension = $file_ext;

        // Rename and move file to temp path
        $dir = dirname($file_tmp_path);
        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file_name);
        if (!rename($file_tmp_path, $destination)) {
            $this->session->set_flashdata('error', 'Failed to prepare file for upload');
            redirect('Emailtemplate/add');
            return;
        }

        // Upload to S3
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        $s3_file_link = !empty($result_arr['ObjectURL']) ? [$result_arr['ObjectURL']] : [];

        if (file_exists($destination)) {
            unlink($destination);
        }

        if (empty($s3_file_link[0])) {
            $this->session->set_flashdata('error', 'Failed to upload file to S3');
            redirect('Emailtemplate/add');
            return;
        }

        $attachment_path = $s3_file_link[0];
    }

    // Save email template
    $EmailtemplateInfo = [
        'franchiseNumber' => $franchiseNumber,
        'emailTemp_type'  => $emailTemp_type,
        'title'           => $title,
        'to_email'        => $to_email,
        'email_body'      => $email_body,
        'attachment_path' => $attachment_path,
        'isDeleted'       => 0,
        'createdBy'       => $this->vendorId,
        'createdDtm'      => date('Y-m-d H:i:s')
    ];

    $result = $this->emltemp->addNewEmailtemplate($EmailtemplateInfo);

    if ($result <= 0) {
        $this->session->set_flashdata('error', 'Email Template creation failed');
        redirect('Emailtemplate/EmailtemplateListing');
        return;
    }

    // Prepare email
    $boundary = md5(time());
    $headers  = "From: Edumeta Team <noreply@theischool.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Cc: " . implode(',', $admin_email_list) . "\r\n";

    $header_image_url = 'https://support-smsfiles.s3.ap-south-1.amazonaws.com/attachements/1749273909-0fad40216aae708a811667a23dd61f98.jpg';

    if ($attachment_path) {
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

        $message = "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= "<html><body>";
        $message .= "<img src=\"{$header_image_url}\" alt=\"Header Image\" style=\"max-width: 1200px; width: 100%; height: 300px;\"><br>";
        $message .= "<p><strong>Branch Number:</strong> {$franchiseNumber}</p>";
        $message .= "<div>{$email_body}</div>";
        $message .= "</body></html>\r\n";

        // Attachment
        $message .= "--{$boundary}\r\n";
        $file_name = 'attachment_' . time() . '.' . $file_extension;
        $file_content = @file_get_contents($attachment_path);
        if ($file_content === FALSE) {
            $this->session->set_flashdata('error', 'Failed to retrieve attachment from S3.');
            redirect('Emailtemplate/EmailtemplateListing');
            return;
        }
        $attachment = chunk_split(base64_encode($file_content));
        $content_type = $is_image ? $file_mime : 'application/pdf';
        $message .= "Content-Type: {$content_type}; name=\"{$file_name}\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"{$file_name}\"\r\n\r\n";
        $message .= "{$attachment}\r\n";
        $message .= "--{$boundary}--";
    } else {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message = "<html><body>";
        $message .= "<img src=\"{$header_image_url}\" alt=\"Header Image\" style=\"max-width: 600px; width: 100%; height: auto;\"><br>";
        $message .= "<p><strong>Branch Number:</strong> {$franchiseNumber}</p>";
        $message .= "<div>{$email_body}</div>";
        $message .= "</body></html>";
    }

    // Send email
    $to_email_string = implode(',', $to_email_list);
    if (mail($to_email_string, $title, $message, $headers)) {
        $this->session->set_flashdata('success', 'New Email Template created and email sent successfully to ' . $to_email_string . ' with CC to ' . implode(',', $admin_email_list));
    } else {
        $this->session->set_flashdata('error', 'Email sending failed to ' . $to_email_string);
    }

    redirect('Emailtemplate/EmailtemplateListing');
}
    public function fetchBranchEmail()
{
    $franchiseNumber = $this->input->post('franchiseNumber');
    $branchEmail = $this->emltemp->getBranchEmail($franchiseNumber);
    
    echo json_encode(['branchEmail' => $branchEmail]);
}

    function view($emailtempId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($emailtempId == null) {
                redirect('Emailtemplate/EmailtemplateListing');
            }

            $data['EmailtemplateInfo'] = $this->emltemp->getEmailtemplateInfo($emailtempId);

            $this->global['pageTitle'] = 'CodeInsect : View Emailtemplate';

            $this->loadViews("emailtemplate/view", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used load Emailtemplate edit information
     * @param number $emailtempId : Optional : This is Emailtemplate id
     */
    function edit($emailtempId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($emailtempId == null) {
                redirect('Emailtemplate/EmailtemplateListing');
            }

            $data['EmailtemplateInfo'] = $this->emltemp->getEmailtemplateInfo($emailtempId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Event Galllery';

            $this->loadViews("emailtemplate/edit", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to edit the user information
     */
    function editEmailtemplate()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $emailtempId = $this->input->post('emailtempId');

            $this->form_validation->set_rules('email_bodys', 'Email Body Content', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->edit($emailtempId);
            } else {
                $franchiseRaw = $this->input->post('franchiseNumber');
                $franchiseNumber = is_array($franchiseRaw) ? implode(
                    ',',
                    $this->security->xss_clean($franchiseRaw)
                ) : $this->security->xss_clean($franchiseRaw);
                $emailTemp_type = $this->security->xss_clean($this->input->post('emailTemp_type'));
                $title = $this->security->xss_clean($this->input->post('title'));
                $to_email = $this->security->xss_clean($this->input->post('to_email'));
                $email_body = $this->security->xss_clean($this->input->post('email_body'));
                $EmailtemplateInfo = array(
                    'franchiseNumber' => $franchiseNumber,
                    'emailTemp_type' => $emailTemp_type,
                    'title'  => $title,
                    'to_email'  => $to_email,
                    'email_body' => $email_body,
                    'isDeleted'  => 0,
                    'createdBy'  => $this->vendorId, // make sure $this->vendorId is set
                    'createdDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->emltemp->editEmailtemplate($EmailtemplateInfo, $emailtempId);

                if ($result == true) {
                    // Email notification to admin
                    $adminEmail = 'dev.edumeta@gmail.com';
                    $adminSubject = "Alert - eduMETA THE i-SCHOOL Email Template Updated";
                    $adminMessage = 'QC details have been updated. ';
                    $adminMessage .= 'Franchise Name: ' . $franchiseName . ', Franchise Number: ' . $franchiseNumber . '. ';
                    $adminMessage .= 'Please visit the portal for details.';
                    $adminHeaders = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com,";
                    mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);
                    $this->session->set_flashdata('success', 'Email Template updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Email Template updation failed');
                }
                redirect('Emailtemplate/EmailtemplateListing');
            }
        }
    }
}
