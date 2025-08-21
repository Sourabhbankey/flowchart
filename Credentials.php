<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Faq (FaqController)
 * Faq Class to control Faq related operations.
 * @author : Ashish 
 * @version : 1.5
 * @since : 11 Nov 2024
 */
class Credentials extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Credentials_model', 'cred');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Despatch_model', 'dm');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Credentials';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('credentials/credentialsListing');
    }

    /**
     * This function is used to load the faq list
     */
   public function credentialsListing()
{
    $searchText = '';
    $franchiseFilter = '';

    if (!empty($this->input->get('searchText'))) {
        $searchText = $this->security->xss_clean($this->input->get('searchText'));
    }

    if (!empty($this->input->get('franchiseNumber'))) {
        $franchiseFilter = $this->security->xss_clean($this->input->get('franchiseNumber'));
    }

    $userRole = $this->session->userdata('role');
    $franchiseNumber = $this->session->userdata('franchiseNumber');
    $userId = $this->session->userdata('userId'); // ✅ Add this line

    $data['searchText'] = $searchText;
    $data['franchiseFilter'] = $franchiseFilter;

    $this->load->library('pagination');

    $config["base_url"] = base_url("credentials/credentialsListing");
    $config["per_page"] = 10;
    $config["uri_segment"] = 3;
    $config["total_rows"] = $this->cred->credentialsListingCount($searchText, $franchiseFilter, $userRole, $franchiseNumber, $userId);
    $config["num_links"] = 2;
    $config["use_page_numbers"] = TRUE;
    $config["reuse_query_string"] = TRUE;

    $this->pagination->initialize($config);

    $page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 1;
    $offset = ($page - 1) * $config["per_page"]; // ✅ Make sure this line is AFTER $config["per_page"]

    $data["records"] = $this->cred->credentialsListing($searchText, $franchiseFilter, $offset, $config["per_page"], $userRole, $franchiseNumber, $userId);
    $data["links"] = $this->pagination->create_links();
    $data["start"] = ($config["total_rows"] > 0) ? ($offset + 1) : 0;
    $data["end"] = min($offset + $config["per_page"], $config["total_rows"]);
    $data["total_records"] = $config["total_rows"];
    $data["offset"] = $offset; // ✅ Define this AFTER it's calculated

    $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

    $this->global['pageTitle'] = 'CodeInsect : credentials';
    $this->loadViews("credentials/list", $this->global, $data, NULL);
}



    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->global['pageTitle'] = 'CodeInsect : Add New credentials';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $data['users'] = $this->dm->getUser();

            $this->loadViews("credentials/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    function addNewCredentials()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');


            $this->form_validation->set_rules('description', 'Description', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseName = $this->security->xss_clean($this->input->post('franchiseName'));
                $credTitle = $this->security->xss_clean($this->input->post('credTitle'));
                $credType = $this->security->xss_clean($this->input->post('credType'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumbers = implode(',', $franchiseNumberArray);

                $credentialsInfo = array(
                    'franchiseName' => $franchiseName,
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'franchiseNumber' => $franchiseNumbers,
                    'credType' => $credType,
                    'description' => $description,
                    'credTitle' => $credTitle,
                    'description' => $description
                );

                $result = $this->cred->addNewCredentials($credentialsInfo);

                if ($result > 0) {
                    $notificationMessage = "<strong>Credential Created:</strong> New Credential Created confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 25, 18])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_Credentials_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }

                    $this->session->set_flashdata('success', 'New Credentials created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Credentials creation failed');
                }

                redirect('credentials/credentialsListing');
            }
        }
    }
    function view($credId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($credId == null) {
                redirect('credentials/credentialsListing');
            }

            $data['credentialsInfo'] = $this->cred->getcredentialsInfo($credId);

            $this->global['pageTitle'] = 'CodeInsect : View Faq';

            $this->loadViews("credentials/view", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used load faq edit information
     * @param number $faqId : Optional : This is faq id
     */
    function edit($credId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($credId == null) {
                redirect('credentials/credentialsListing');
            }

            $data['credentialsInfo'] = $this->cred->getCredentialsInfo($credId);

            if (empty($data['credentialsInfo'])) {
                $this->session->set_flashdata('error', 'Credentials not found');
                redirect('credentials/credentialsListing');
            }

            $this->global['pageTitle'] = 'Admin Panel : Edit Credentials';
            $this->loadViews("credentials/edit", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to edit the user information
     */
    public function editCredentials()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
            return;
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('description', 'Description', 'trim|required');

        $credId = $this->input->post('credId');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($credId);
            return;
        }

        // Sanitize and collect inputs
        $credentialsInfo = array(
            'credTitle'         => $this->security->xss_clean($this->input->post('credTitle')),
            'credType'          => $this->security->xss_clean($this->input->post('credType')),
            'description'       => $this->security->xss_clean($this->input->post('description')),

            'wcredType2'        => $this->security->xss_clean($this->input->post('wcredType2')),
            'webmId'            => $this->security->xss_clean($this->input->post('webmId')),
            'webmPass'          => $this->security->xss_clean($this->input->post('webmPass')),
            'wDate'             => $this->security->xss_clean($this->input->post('wDate')),

            'esappcredType3'    => $this->security->xss_clean($this->input->post('esappcredType3')),
            'esappUserId'       => $this->security->xss_clean($this->input->post('esappUserId')),
            'esappPass'         => $this->security->xss_clean($this->input->post('esappPass')),
            'esappDate'         => $this->security->xss_clean($this->input->post('esappDate')),

            'shoppicredType4'   => $this->security->xss_clean($this->input->post('shoppicredType4')),
            'shoppiId'          => $this->security->xss_clean($this->input->post('shoppiId')),
            'shoppiPass'        => $this->security->xss_clean($this->input->post('shoppiPass')),
            'shoppiDate'        => $this->security->xss_clean($this->input->post('shoppiDate')),

            'prepcredType5'     => $this->security->xss_clean($this->input->post('prepcredType5')),
            'prepId'            => $this->security->xss_clean($this->input->post('prepId')),
            'prepPass'          => $this->security->xss_clean($this->input->post('prepPass')),
            'prepDate'          => $this->security->xss_clean($this->input->post('prepDate')),

            'webcredType5'      => $this->security->xss_clean($this->input->post('webcredType5')),
            'webLink'           => $this->security->xss_clean($this->input->post('webLink')),
            'webDate'           => $this->security->xss_clean($this->input->post('webDate')),

            'brspFranchiseAssigned' => $this->security->xss_clean($this->input->post('brspFranchiseAssigned'))
        );

        $brspFranchiseAssigned = $credentialsInfo['brspFranchiseAssigned'];

        // Support both single and multiple franchise number input
        $franchiseInput = $this->input->post('franchiseNumberArray') ?? $this->input->post('franchiseNumber');
        $franchiseNumberArray = is_array($franchiseInput) ? $franchiseInput : [$franchiseInput];

        // Save data
        $result = $this->cred->editCredentials($credentialsInfo, $credId);

        if ($result === true) {
            $this->load->model('Notification_model', 'nm');
            $this->load->model('Branch_model', 'bm');

            $notificationMessage = "<strong>Credential Created:</strong> Update Credential Created confirmation";

            // Notify assigned user if any
            if (!empty($brspFranchiseAssigned)) {
                $this->nm->add_Credentials_notification($brspFranchiseAssigned, $notificationMessage, $credId);
            }

            // Notify selected franchises
            foreach ($franchiseNumberArray as $franchiseNumber) {
                if (empty($franchiseNumber)) continue;

                $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                if (!empty($branchDetail)) {
                    // Email
                    $to = $branchDetail->officialEmailID;
                    $subject = "Alert - eduMETA THE i-SCHOOL Credential Updated";
                    $message = "Dear {$branchDetail->applicantName},\n\n";
                    $message .= "Credential record has been updated by " . $this->session->userdata("name") . ".\n";
                    $message .= "Please visit the portal to review the changes.";
                    $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                    mail($to, $subject, $message, $headers);

                    // Notification
                    $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                    if (!empty($franchiseUser)) {
                        $this->nm->add_Credentials_notification($franchiseUser->userId, $notificationMessage, $credId);
                    }
                }
            }

            // Notify admin roles (1, 14, 22)
            $adminUsers = $this->bm->getUsersByRoles([1, 14, 22]);
            foreach ($adminUsers as $adminUser) {
                $this->nm->add_Credentials_notification($adminUser->userId, $notificationMessage, $credId);
            }

            $this->session->set_flashdata('success', 'Credentials updated successfully');
        } else {
            $this->session->set_flashdata('error', 'Credential update failed');
        }

        redirect('credentials/credentialsListing');
    }


    public function fetchAssignedUsers()
    {
        $franchiseNumber = $this->input->post('franchiseNumber');

        if (!empty($franchiseNumber)) {

            $users = $this->dm->getUsersByFranchise($franchiseNumber);

            // Agar users aaye toh options banao
            if (!empty($users)) {
                $options = '';
                foreach ($users as $user) {
                    $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
                }
            } else {
                $options = '<option value="">No Users Found</option>';
            }

            echo $options;
        } else {
            echo '<option value="">Select User</option>';
        }
    }
}
