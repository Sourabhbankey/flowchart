<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Daycarefeetemplate
 * Controller for managing daycare fee templates
 * @author : [Your Name]
 * @version : 1.0
 * @since : May 2025
 */
class Daycarefeetemplate extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Daycarefeetemplate_model', 'dft');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Daycarefeetemplate';
        $this->load->library('pagination');
    }

    public function index()
    {
        redirect('daycarefeetemplate/daycarefeetemplatelisting');
    }

   public function daycareFeeTemplateListing()
{
    if (!$this->hasListAccess()) {
        $this->loadThis();
        return;
    }

    $userId = $this->session->userdata('userId');
    $role = $this->session->userdata('role') ?: 25;
    $searchText = $this->security->xss_clean($this->input->post('searchText') ?? '');
    $franchiseFilter = $this->security->xss_clean($this->input->get('franchiseNumber') ?? '');

    // Reset filter if requested (only for non-role 15 or 25 users)
    if ($this->input->get('resetFilter') == '1' && !in_array($role, [15, 25])) {
        $franchiseFilter = '';
    }

    // Role-based franchise handling
    $rolesAdmin = ['1', '14', '21', '23', '33'];
    $rolesOther = ['13', '15'];
    $rolesRestricted = ['25'];

    $queryFranchiseNumber = null;
    $franchiseNumbers = [];
    $data = [];

    if (in_array($role, $rolesAdmin)) {
        // Admins can filter by franchise or see all
        $queryFranchiseNumber = $franchiseFilter ?: null;
    } elseif (in_array($role, $rolesOther)) {
        // Roles 13 and 15 can see multiple assigned franchises
        $assignedFranchises = $this->dft->getAssignedFranchisesByUserId($userId);
        if (!empty($assignedFranchises)) {
            $franchiseNumbers = array_column($assignedFranchises, 'franchiseNumber');
            if ($franchiseFilter && in_array($franchiseFilter, $franchiseNumbers)) {
                $queryFranchiseNumber = $franchiseFilter;
            } else {
                $queryFranchiseNumber = $franchiseNumbers; // Use all assigned franchises
            }
        } else {
            $this->session->set_flashdata('error', 'No franchises assigned to your account.');
            $data = [
                'feeTemplates' => [],
                'pagination' => '',
                'searchText' => $searchText,
                'franchiseFilter' => '',
                'branchDetail' => [],
                'total_records' => 0,
                'start' => 0,
                'end' => 0,
                'role' => $role
            ];
            $this->global['pageTitle'] = 'Daycare Fee Template Listing';
            $this->loadViews('daycarefeetemplate/list', $this->global, $data);
            return;
        }
    } elseif (in_array($role, $rolesRestricted)) {
        // Role 25 (Growth Managers) restricted to single franchise
        $queryFranchiseNumber = $this->dft->getFranchiseNumberByUserId($userId);
        if (!$queryFranchiseNumber) {
            $this->session->set_flashdata('error', "No franchise assigned to your account (User ID: $userId, Role: $role). Please contact the administrator.");
            $data = [
                'feeTemplates' => [],
                'pagination' => '',
                'searchText' => $searchText,
                'franchiseFilter' => '',
                'branchDetail' => [],
                'total_records' => 0,
                'start' => 0,
                'end' => 0,
                'role' => $role
            ];
            $this->global['pageTitle'] = 'Daycare Fee Template Listing';
            $this->loadViews('daycarefeetemplate/list', $this->global, $data);
            return;
        }
        $franchiseNumbers = [$queryFranchiseNumber]; // Single franchise
    } else {
        // Default: No access
        $this->session->set_flashdata('error', 'You do not have access to view fee templates.');
        $data = [
            'feeTemplates' => [],
            'pagination' => '',
            'searchText' => $searchText,
            'franchiseFilter' => '',
            'branchDetail' => [],
            'total_records' => 0,
            'start' => 0,
            'end' => 0,
            'role' => $role
        ];
        $this->global['pageTitle'] = 'Daycare Fee Template Listing';
        $this->loadViews('daycarefeetemplate/list', $this->global, $data);
        return;
    }

    // Pagination configuration
    $config = [
        'base_url' => base_url('daycarefeetemplate/daycarefeetemplatelisting'),
        'per_page' => 10,
        'uri_segment' => 3,
        'use_page_numbers' => TRUE,
        'full_tag_open' => '<div class="pagination-links">',
        'full_tag_close' => '</div>',
        'first_link' => 'First',
        'last_link' => 'Last',
        'next_link' => '»',
        'prev_link' => '«',
        'cur_tag_open' => '<strong>',
        'cur_tag_close' => '</strong>',
        'num_tag_open' => '',
        'num_tag_close' => ''
    ];

    // Ensure page is at least 1 to avoid negative offset
    $page = max(1, (int) $this->uri->segment(3));
    $offset = ($page - 1) * $config['per_page'];

    // Adjust total_rows and records based on franchise handling
    if (is_array($queryFranchiseNumber)) {
        $config['total_rows'] = $this->dft->daycareFeeTemplateListingCountByFranchises($searchText, $queryFranchiseNumber);
        $data['feeTemplates'] = $this->dft->daycareFeeTemplateListingByFranchises($searchText, $config['per_page'], $offset, $queryFranchiseNumber);
        $data['franchiseFilter'] = '';
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber($queryFranchiseNumber);
    } else {
        $config['total_rows'] = $this->dft->daycareFeeTemplateListingCount($searchText, $role, $queryFranchiseNumber);
        $data['feeTemplates'] = $this->dft->daycareFeeTemplateListing($searchText, $config['per_page'], $offset, $role, $queryFranchiseNumber);
        $data['franchiseFilter'] = $queryFranchiseNumber ?: $franchiseFilter;
        $data['branchDetail'] = $queryFranchiseNumber ? $this->bm->getBranchesFranchiseNumber($queryFranchiseNumber) : $this->bm->getBranchesFranchiseNumber();
    }

    // Persist search and franchise filter in pagination links (only for non-role 15 or 25 users)
    $query_string = [];
    if ($searchText) {
        $query_string['searchText'] = urlencode($searchText);
    }
    if ($franchiseFilter && !in_array($role, [15, 25])) {
        $query_string['franchiseNumber'] = urlencode($franchiseFilter);
    }
    if ($query_string) {
        $config['suffix'] = '?' . http_build_query($query_string);
        $config['first_url'] = $config['base_url'] . '?' . http_build_query($query_string);
    } else {
        $config['first_url'] = $config['base_url'];
    }

    $this->pagination->initialize($config);

    $data += [
        'pagination' => $this->pagination->create_links(),
        'searchText' => $searchText,
        'total_records' => $config['total_rows'],
        'start' => $offset + 1,
        'end' => min($offset + $config['per_page'], $config['total_rows']),
        'role' => $role
    ];

    $this->global['pageTitle'] = 'Daycare Fee Template Listing';
    $this->loadViews('daycarefeetemplate/list', $this->global, $data);
}


    public function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
            return;
        }

        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $data = [
            'franchises' => ($role == 25) ? [] : $this->dft->getFranchises(),
            'feeTemplateInfo' => null,
            'role' => $role,
            'franchiseNumber' => $franchiseNumber
        ];

        $this->global['pageTitle'] = 'Add Daycare Fee Template';
        $this->loadViews('daycarefeetemplate/add', $this->global, $data);
    }

 public function addDaycareFeeTemplate()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
            return;
        }

        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $this->load->library('form_validation');
        $this->form_validation->set_rules('franchiseNumber', 'Franchise Number', 'trim|required');
        $this->form_validation->set_rules('brAddress', 'Branch Address', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('branchContacNum', 'Contact Number', 'trim|required|max_length[255]');
        /*$this->form_validation->set_rules('ageGroupEarlyYears', '1 Hour Fee', 'trim|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('earlyYearsDays_operation', '4 Hours Fee', 'trim|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('earlyYearsHourse', '6 Hours Fee', 'trim|numeric|greater_than_equal_to[0]');*/
        if ($role != 25) {
            $this->form_validation->set_rules('branchFranchiseAssigned', 'Assigned Growth Manager', 'trim|required|numeric');
        }

        if ($this->form_validation->run() == FALSE) {
            log_message('debug', 'Validation Errors: ' . validation_errors());
            $this->add();
            return;
        }

        $inputFranchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
        if ($role == 25 && $inputFranchiseNumber !== $franchiseNumber) {
            $this->session->set_flashdata('error', 'You can only create templates for your franchise');
            $this->add();
            return;
        }

        $feeTemplateInfo = [
            'franchiseNumber' => $inputFranchiseNumber,
            'ageGroupEarlyYears' => $this->security->xss_clean($this->input->post('ageGroupEarlyYears')) ?: 0,
            'earlyYearsDays_operation' => $this->security->xss_clean($this->input->post('earlyYearsDays_operation')) ?: 0,
            'earlyYearsHourse' => $this->security->xss_clean($this->input->post('earlyYearsHourse')) ?: 0,
            'earlyYearsFeeMonthly' => $this->security->xss_clean($this->input->post('earlyYearsFeeMonthly')) ?: 0,
            'earlyYearsFeeHourly' => $this->security->xss_clean($this->input->post('earlyYearsFeeHourly')) ?: 0,
            'ageGroupJuniors' => $this->security->xss_clean($this->input->post('ageGroupJuniors')) ?: 0,
            'juniorsDays_operation' => $this->security->xss_clean($this->input->post('juniorsDays_operation')) ?: 0,
            'juniorsHourse' => $this->security->xss_clean($this->input->post('juniorsHourse')) ?: 0,
            'juniorsFeeMonthly' => $this->security->xss_clean($this->input->post('juniorsFeeMonthly')),
            'juniorsFeeHourly' => $this->security->xss_clean($this->input->post('juniorsFeeHourly')),
            'brAddress' => $this->security->xss_clean($this->input->post('brAddress')),
            'branchContacNum' => $this->security->xss_clean($this->input->post('branchContacNum')),
            'branchFranchiseAssigned' => ($role != 25) ? $this->security->xss_clean($this->input->post('branchFranchiseAssigned')) : null,
            'description' => $this->security->xss_clean($this->input->post('description')),
            'createdBy' => $this->vendorId,
            'createdDtm' => date('Y-m-d H:i:s'),
            'isDeleted' => 0
        ];

        $result = $this->dft->addNewDaycareFeeTemplate($feeTemplateInfo);

        if ($result) {

            $this->load->model('Notification_model', 'nm');
    
            $notificationMessage = "<strong>Day Care Template Confirmation:</strong> New Day Care Template confirmation";
            // 1. Users with specific roles
            $roleUsers = $this->db->select('userId')
                ->from('tbl_users')
                ->where_in('roleId', [1, 14])
                ->where('isDeleted', 0)
                ->get()
                ->result_array();

            // 2. Users with the same franchise number
            $franchiseUsers = $this->db->select('userId')
                ->from('tbl_users')
                ->where('franchiseNumber', $inputFranchiseNumber)
                ->where('isDeleted', 0)
                ->get()
                ->result_array();


            $assignedTo = [];
            if (!empty($branchFranchiseAssigned)) {
                if ($this->db->select('branchFranchiseAssigned', 'tbl_users')) {
                    $assignedTo = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where('branchFranchiseAssigned', $branchFranchiseAssigned)
                        ->where('isDeleted', 0)
                        ->get()
                        ->result_array();
                }
            }

        
            // Merge and remove duplicates
            $allUsers = array_unique(array_merge(
                array_column($roleUsers, 'userId'),
                array_column($franchiseUsers, 'userId'),
                array_column($assignedTo, 'userId')
            ));

            foreach ($allUsers as $userId) {
                $notificationResult = $this->nm->add_dcfeetemp_notification($result, $notificationMessage, $userId);
                if (!$notificationResult) {
                    log_message('error', "Failed to add notification for user {$userId} on template ID {$result}");
                }
            }

            $this->session->set_flashdata('success', 'Template created successfully');

            // Send email notification
            if (!empty($franchiseNumberArray)) {
                foreach ($franchiseNumberArray as $franchiseNumber) {
                    $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                    if (!empty($branchDetail)) {
                        $to = $branchDetail->officialEmailID;
                        $subject = "Alert - eduMETA THE i-SCHOOL New Daycare Fee Template";
                        $message = "Dear {$branchDetail->applicantName},\n";
                        $message .= "A new daycare fee template has been created for your franchise ({$franchiseNumber}).\n";
                        $message .= "<a href=\"" . base_url('daycarefeetemplate/daycarefeetemplatelisting') . "\">Click Now For More Detail</a>";
                        $message .= "Please visit the portal for more details.";
                        $headers = "From: Edumeta Team <noreply@theischool.com>\r\n" .
                            "BCC: dev.edumeta@gmail.com";
                        mail($to, $subject, $message, $headers);
                    }
                }
            }
        } else {
            $this->session->set_flashdata('error', 'Template creation failed');
        }

        redirect('daycarefeetemplate/daycarefeetemplatelisting');
    }


    public function edit($dcfeetempId = NULL)
    {
        if (!$this->hasUpdateAccess() || !$dcfeetempId) {
            $this->loadThis();
            return;
        }

        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $template = $this->dft->getDaycareFeeTemplateInfo($dcfeetempId);
        if (!$template) {
            $this->session->set_flashdata('error', 'Fee template not found');
            redirect('daycarefeetemplate/daycarefeetemplatelisting');
        }
        if ($role == 25 && $template->franchiseNumber !== $franchiseNumber) {
            $this->session->set_flashdata('error', 'You can only edit templates for your franchise');
            redirect('daycarefeetemplate/daycarefeetemplatelisting');
        }

        $data = [
            'franchises' => ($role == 25) ? [] : $this->dft->getFranchises(),
            'feeTemplateInfo' => $template,
            'role' => $role,
            'franchiseNumber' => $franchiseNumber
        ];

        if ($data['feeTemplateInfo'] && $data['feeTemplateInfo']->createdBy) {
            $this->db->select('name');
            $this->db->from('tbl_users');
            $this->db->where('userId', $data['feeTemplateInfo']->createdBy);
            $query = $this->db->get();
            $user = $query->row();
            $data['createdByName'] = $user ? $user->name : 'Unknown';
        }

        $this->global['pageTitle'] = 'Edit Daycare Fee Template';
        $this->loadViews('daycarefeetemplate/add', $this->global, $data);
    }

    public function updateDaycareFeeTemplate()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
            return;
        }

        $dcfeetempId = $this->input->post('dcfeetempId');
        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        if ($role == 25) {
            $template = $this->dft->getDaycareFeeTemplateInfo($dcfeetempId);
            if (!$template || $template->franchiseNumber !== $franchiseNumber) {
                $this->session->set_flashdata('error', 'You can only update templates dominion your franchise');
                redirect('daycarefeetemplate/daycarefeetemplatelisting');
            }
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('franchiseNumber', 'Franchise Number', 'trim|required');
        $this->form_validation->set_rules('brAddress', 'Branch Address', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('branchContacNum', 'Contact Number', 'trim|required|max_length[255]');
        //$this->form_validation->set_rules('ageGroupEarlyYears', '1 Hour Fee', 'trim|numeric|greater_than_equal_to[0]');
        if ($role != 25) {
            $this->form_validation->set_rules('branchFranchiseAssigned', 'Assigned Growth Manager', 'trim|required|numeric');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->edit($dcfeetempId);
            return;
        }

        $inputFranchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
        if ($role == 25 && $inputFranchiseNumber !== $franchiseNumber) {
            $this->session->set_flashdata('error', 'You can only update templates for your franchise');
            $this->edit($dcfeetempId);
            return;
        }

        $feeTemplateInfo = [
            'franchiseNumber' => $inputFranchiseNumber,
            'ageGroupEarlyYears' => $this->security->xss_clean($this->input->post('ageGroupEarlyYears')) ?: 0,
            'earlyYearsDays_operation' => $this->security->xss_clean($this->input->post('earlyYearsDays_operation')) ?: 0,
            'earlyYearsHourse' => $this->security->xss_clean($this->input->post('earlyYearsHourse')) ?: 0,
            'earlyYearsFeeMonthly' => $this->security->xss_clean($this->input->post('earlyYearsFeeMonthly')) ?: 0,
            'earlyYearsFeeHourly' => $this->security->xss_clean($this->input->post('earlyYearsFeeHourly')) ?: 0,
            'ageGroupJuniors' => $this->security->xss_clean($this->input->post('ageGroupJuniors')) ?: 0,
            'juniorsDays_operation' => $this->security->xss_clean($this->input->post('juniorsDays_operation')) ?: 0,
            'juniorsHourse' => $this->security->xss_clean($this->input->post('juniorsHourse')) ?: 0,
            'juniorsFeeMonthly' => $this->security->xss_clean($this->input->post('juniorsFeeMonthly')),
            'juniorsFeeHourly' => $this->security->xss_clean($this->input->post('juniorsFeeHourly')),
            'brAddress' => $this->security->xss_clean($this->input->post('brAddress')),
            'branchContacNum' => $this->security->xss_clean($this->input->post('branchContacNum')),
            'branchFranchiseAssigned' => ($role != 25) ? $this->security->xss_clean($this->input->post('branchFranchiseAssigned')) : null,
            'description' => $this->security->xss_clean($this->input->post('description')),
            'updatedBy' => $this->vendorId,
            'updatedDtm' => date('Y-m-d H:i:s')
        ];

        $result = $this->dft->editDaycareFeeTemplate($feeTemplateInfo, $dcfeetempId);

        if ($result) {

            $this->load->model('Notification_model', 'nm');

            // Send notifications to users with roleId 19, 14, 25
            $notificationMessage = "<strong>Day Care Templete Confirmation:</strong> Update Day Care Templete confirmation";
            $users = $this->db->select('userId')
                ->from('tbl_users')
                ->where_in('roleId', [1, 14, 15, 21, 25])
                ->get()
                ->result_array();

            if (!empty($users)) {
                $userIds = array_column($users, 'userId');
                foreach ($userIds as $userId) {
                    $notificationResult = $this->nm->add_dcfeetemp_notification($result, $notificationMessage, $userId);
                    if (!$notificationResult) {
                        log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                    }
                }
            }

            $this->session->set_flashdata('success', 'Template updated successfully');
        } else {
            $this->session->set_flashdata('error', 'Template update failed');
        }

        redirect('daycarefeetemplate/daycarefeetemplatelisting');
    }

    public function viewDaycareFeeTemplate($dcfeetempId = NULL)
    {
        if (!$this->hasListAccess() || !$dcfeetempId) {
            $this->loadThis();
            return;
        }

        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $data['feeTemplateInfo'] = $this->dft->getDaycareFeeTemplateInfo($dcfeetempId);
        if (!$data['feeTemplateInfo']) {
            $this->session->set_flashdata('error', 'Fee template not found');
            redirect('daycarefeetemplate/daycarefeetemplatelisting');
        }

        if ($role == 25 && $data['feeTemplateInfo']->franchiseNumber !== $franchiseNumber) {
            $this->session->set_flashdata('error', 'You can only view templates for your franchise');
            redirect('daycarefeetemplate/daycarefeetemplatelisting');
        }

        if ($data['feeTemplateInfo']->branchFranchiseAssigned) {
            $users = $this->dft->getUsersByFranchise($data['feeTemplateInfo']->franchiseNumber);
            foreach ($users as $user) {
                if ($user->userId == $data['feeTemplateInfo']->branchFranchiseAssigned) {
                    $data['feeTemplateInfo']->assignedUserName = $user->name;
                    break;
                }
            }
        } else {
            $data['feeTemplateInfo']->assignedUserName = 'None';
        }

        $this->global['pageTitle'] = 'View Daycare Fee Template';
        $this->loadViews('daycarefeetemplate/view', $this->global, $data);
    }

    public function printDaycareFeeTemplate($dcfeetempId = NULL)
    {
        if (!$this->hasListAccess() || !$dcfeetempId) {
            $this->loadThis();
            return;
        }

        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $data['feeTemplateInfo'] = $this->dft->getDaycareFeeTemplateInfo($dcfeetempId);
        if (!$data['feeTemplateInfo']) {
            $this->session->set_flashdata('error', 'Fee template not found');
            redirect('daycarefeetemplate/daycarefeetemplatelisting');
        }

        if ($role == 25 && $data['feeTemplateInfo']->franchiseNumber !== $franchiseNumber) {
            $this->session->set_flashdata('error', 'You can only print templates for your franchise');
            redirect('daycarefeetemplate/daycarefeetemplatelisting');
        }

        $this->global['pageTitle'] = 'Print Daycare Fee Template';
        $this->load->view('daycarefeetemplate/print', $data);
    }

    public function deleteDaycareFeeTemplate()
    {
        if (!$this->hasDeleteAccess()) {
            echo json_encode(['status' => 'error', 'message' => 'No permission to delete']);
            return;
        }

        $dcfeetempId = $this->input->post('dcfeetempId');
        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        if ($role == 25) {
            $template = $this->dft->getDaycareFeeTemplateInfo($dcfeetempId);
            if (!$template || $template->franchiseNumber !== $franchiseNumber) {
                echo json_encode(['status' => 'error', 'message' => 'You can only delete templates for your franchise']);
                return;
            }
        }

        $result = $this->dft->deleteDaycareFeeTemplate($dcfeetempId);
        echo json_encode($result ? ['status' => 'success', 'message' => 'Template deleted successfully'] : ['status' => 'error', 'message' => 'Template deletion failed']);
    }

    public function fetchAssignedUsers()
    {
        $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
        $selectedUserId = $this->security->xss_clean($this->input->post('selectedUserId'));
        $users = $this->dft->getUsersByFranchise($franchiseNumber);
        $userNames = [];
        $userIds = [];
        $selectedUserName = '';

        if ($users) {
            foreach ($users as $user) {
                $userNames[] = htmlspecialchars($user->name);
                $userIds[] = $user->userId;
                if ($selectedUserId && $user->userId == $selectedUserId) {
                    $selectedUserName = htmlspecialchars($user->name);
                }
            }
            $response = [
                'status' => 'success',
                'html' => $selectedUserId ? $selectedUserName : implode(', ', $userNames),
                'userIds' => implode(',', $userIds)
            ];
        } else {
            $response = [
                'status' => 'success',
                'html' => 'No Growth Managers assigned',
                'userIds' => '',
                'message' => 'No Growth Managers found for this franchise'
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function fetchFranchiseData()
    {
        $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
        $selectedUserId = $this->security->xss_clean($this->input->post('selectedUserId'));

        $users = $this->dft->getUsersByFranchise($franchiseNumber);
        $franchiseData = $this->dft->getFranchiseData($franchiseNumber);

        $response = [
            'status' => 'success',
            'userIds' => '',
            'html' => 'No Growth Managers assigned',
            'franchiseData' => $franchiseData ? [
                'brAddress' => htmlspecialchars($franchiseData->brAddress),
                'branchContacNum' => htmlspecialchars($franchiseData->branchContacNum)
            ] : null
        ];

        if ($users) {
            $userNames = [];
            $userIds = [];
            foreach ($users as $user) {
                $userNames[] = htmlspecialchars($user->name);
                $userIds[] = $user->userId;
            }
            $response['userIds'] = implode(',', $userIds);
            $response['html'] = implode(', ', $userNames);
        }

        if (!$franchiseData) {
            $response['franchiseMessage'] = 'No franchise data found for this franchise';
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
