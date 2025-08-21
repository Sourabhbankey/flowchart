<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/BaseController.php';
class Leave extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->load->model('Leave_model', 'lve');
        $this->load->model('Task_model', 'tm');
         $this->load->model('Branches_model', 'bm');
         $this->load->model('Hronboard_model', 'hron');
         $this->load->helper(array('form', 'url'));
         $this->load->library('form_validation');
          $this->isLoggedIn();
    }

    public function index() {
        //$data['leave'] = $this->lve->getLeaveData();
        //$data['name'] = $this->session->userdata('name'); 
        redirect('leave/leaveListing');
    }

public function leaveListing() {
    $userId = $this->session->userdata('userId');
    $roleId = $this->session->userdata('roleId');

    $this->load->library('pagination');
    $this->load->model('Hron_model', 'hron'); // Load the HR Onboard model

    $config = array();
    $config['base_url'] = base_url('leave/leaveListing');
    $config['per_page'] = 10;

    // Get date filters from GET parameters
    $start_date = $this->input->get('start_date', TRUE);
    $end_date = $this->input->get('end_date', TRUE);

    // Preserve query string for pagination links
    $config['reuse_query_string'] = TRUE;

    // Get total leave count with filters
    if ($roleId == 1 || $roleId == 2) {
        $config['total_rows'] = $this->lve->getLeaveCount(null, $start_date, $end_date);
    } else {
        $config['total_rows'] = $this->lve->getLeaveCount($userId, $start_date, $end_date);
    }

    // Fetch all leave data for the user (for calculations)
    $allLeaveData = $this->lve->getAllLeaveDataForUser($userId, $start_date, $end_date);

    $totalAppliedDays = 0;
    $totalRejectedDays = 0;

    $usedCasual = 0;
    $usedSick = 0;
    $usedAnnual = 0;

    foreach ($allLeaveData as $userLeave) {
        $leaveDays = isset($userLeave['leaveDays']) ? floatval($userLeave['leaveDays']) : 0;

        // If not rejected
        if (!in_array($userLeave['status'], ['rejected_manager', 'rejected_hr', 'Cancelled'])) {
            $totalAppliedDays += $leaveDays;

            // Track by leave type
            if ($userLeave['leave_type'] == 'Casual Leave') {
                $usedCasual += $leaveDays;
            } elseif ($userLeave['leave_type'] == 'Sick Leave') {
                $usedSick += $leaveDays;
            } elseif ($userLeave['leave_type'] == 'Annual Leave') {
                $usedAnnual += $leaveDays;
            }
        } else {
            $totalRejectedDays += $leaveDays;
        }
    }

    // ✅ Fetch totalLeaves (leave_count) from hronboard based on role
    /*$totalLeaves = $this->lve->getLeaveCountByRole($roleId);
    if (!$totalLeaves) {
        $totalLeaves = 0; // Fallback default
    }*/
    $loggedInUserId = $this->session->userdata('userId');

// Get the roleId of the logged-in user
$this->db->select('roleId');
$this->db->from('tbl_users');
$this->db->where('userId', $loggedInUserId);
$query = $this->db->get();
$user = $query->row();

$totalLeaves = 0; // Default

if (!empty($user)) {
    $roleId = $user->roleId;

    // Get leave count from tbl_hrforms_onboard for this role
    $this->db->select('leave_count');
    $this->db->from('tbl_hrforms_onboard');
    $this->db->where('roleId', $roleId);
    $query = $this->db->get();
    $result = $query->row();

    if (!empty($result)) {
        $totalLeaves = $result->leave_count;
    }
    //print_r($totalLeaves);exit;
}

    // Default Limits for type-wise display (Optional - update as needed)
    $casualLeaveLimit = 5;
    $sickLeaveLimit = 3;
    $annualLeaveLimit = 5;

    $remainingCasual = max(0, $casualLeaveLimit - $usedCasual);
    $remainingSick = max(0, $sickLeaveLimit - $usedSick);
    $remainingAnnual = max(0, $annualLeaveLimit - $usedAnnual);

    // Pagination logic
    $this->pagination->initialize($config);
    $page = $this->uri->segment(3, 0);

    // Fetch paginated leave data with filters
    if ($roleId == 1 || $roleId == 2) {
        $leaveData = $this->lve->getLeaveData($config['per_page'], $page, $start_date, $end_date);
    } else {
        $leaveData = $this->lve->getLeaveDataUser($userId, $config['per_page'], $page, $start_date, $end_date);
    }

    $startRecord = $page + 1;
    $endRecord = min($page + $config['per_page'], $config['total_rows']);

    $data = array(
        "serial_no" => $startRecord,
        'leaveData' => $leaveData,
        'pagination' => $this->pagination->create_links(),
        'startRecord' => $startRecord,
        'endRecord' => $endRecord,
        'totalRecords' => $config['total_rows'],
        'totalAppliedDays' => $totalAppliedDays,
        'totalRejectedDays' => $totalRejectedDays,
        'totalLeaves' => $totalLeaves,
        'remainingLeaves' => max(0, $totalLeaves - $totalAppliedDays),
        'remainingCasual' => $remainingCasual,
        'remainingSick' => $remainingSick,
        'remainingAnnual' => $remainingAnnual
    );

    $this->global['pageTitle'] = 'Leave Listing';
    $this->loadViews("leave/list", array_merge($this->global, $data), NULL, NULL);
}




    function add()
    {
      $data['users'] = $this->lve->getAllUsers();
            $this->global['pageTitle'] = 'CodeInsect : Add New Leave';

            $this->loadViews("leave/add", $this->global, $data, NULL);
        
    }

public function addNewLeave()
{       
    $userId = $this->session->userdata('userId');
    $roleId = $this->session->userdata('role');

    $this->load->library('form_validation');
         
    $this->form_validation->set_rules('leave_type', 'Leave Type', 'trim|required|max_length[50]');
    $this->form_validation->set_rules('reason', 'Reason', 'trim|required|max_length[1024]');
    $this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');
    $this->form_validation->set_rules('end_date', 'End Date', 'trim|required');
     $this->form_validation->set_rules('assignedTo', 'Reporting Manager', 'trim|required');

    if ($this->form_validation->run() == FALSE) {
        $this->add();
    } else {
        $leave_type = $this->security->xss_clean($this->input->post('leave_type'));
        $reason = $this->security->xss_clean($this->input->post('reason'));
        $start_date = $this->security->xss_clean($this->input->post('start_date'));
        $end_date = $this->security->xss_clean($this->input->post('end_date'));
        $assignedTo = $this->security->xss_clean($this->input->post('assignedTo'));

        // Calculate leave days
        if ($leave_type == "Half Day") {
            $leaveDays = 0.5;
            $end_date = $start_date;
        } else {
            $startDateObj = new DateTime($start_date);
            $endDateObj = new DateTime($end_date);
            $leaveDays = $endDateObj->diff($startDateObj)->days + 1;
        }

        // 🛑 Casual Leave Limit Check
        if ($leave_type == "Casual Leave") {
            $casualLeaveLimit = 5;
            $usedCasualLeaves = $this->lve->countUsedLeaves($userId, 'Casual Leave');

            if (($usedCasualLeaves + $leaveDays) > $casualLeaveLimit) {
                $this->session->set_flashdata('error', 'You have exceeded your Casual Leave limit.');
                redirect('leave/add');
                return;
            }
        }

        // 🔼 Handle File Uploads to S3
        $s3_file_links = [];

        if (!empty($_FILES["file"]["name"][0])) {  
            for ($i = 0; $i < count($_FILES["file"]["name"]); $i++) {
                $tempFile = $_FILES["file"]["tmp_name"][$i];
                $fileName = $_FILES["file"]["name"][$i];
                $destination = dirname($tempFile) . DIRECTORY_SEPARATOR . time() . '-' . $fileName;

                if (move_uploaded_file($tempFile, $destination)) {
                    $storeFolder = 'attachments';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();

                    if (!empty($result_arr['ObjectURL'])) {
                        $s3_file_links[] = $result_arr['ObjectURL'];
                    }
                }
            }
        }

        $s3files = !empty($s3_file_links) ? implode(',', $s3_file_links) : null;

        // ➕ Prepare leave data
        $leaveInfo = array(
            'userId'      => $userId,
            'roleId'      => $roleId,
            'leave_type'  => $leave_type,
            'reason'      => $reason,
            'start_date'  => $start_date,
            'end_date'    => $end_date,
            'assignedTo'  => $assignedTo,
            'status'      => 'pending',
            'leaveDays'   => $leaveDays,
            'leaveS3File' => $s3files
        );

        // 🔄 Insert leave
        $result = $this->lve->addNewLeave($leaveInfo);

        if ($result > 0) {
            $this->load->model('Notification_model');

            // Notification to assigned user
            if (!empty($assignedTo)) {
                $notificationMessage = "<strong>Leave:</strong> Leave Request.";
                $this->Notification_model->add_leave_notification($assignedTo, $notificationMessage, $result);
            }

            // Notification to admin users (roles: 1, 14)
            $adminUsers = $this->bm->getUsersByRoles([1, 14]);
            if (!empty($adminUsers)) {
                foreach ($adminUsers as $adminUser) {
                    $this->Notification_model->add_leave_notification($adminUser->userId, "<strong>Leave:</strong> Leave Request.", $result);
                }
            }

            // Notification to HR (role 26)
            $hrUsers = $this->Notification_model->getUsersByRole(26);
            if (!empty($hrUsers)) {
                foreach ($hrUsers as $hrUser) {
                    $this->Notification_model->add_leave_notification($hrUser['userId'], "<strong>Leave:</strong> Leave Request.", $result);
                }
            }

            $this->session->set_flashdata('success', 'New Leave created successfully');
        } else {
            $this->session->set_flashdata('error', 'Leave creation failed');
        }

        redirect('leave/leaveListing');
    }
}



//for view secdtion

function view($id = NULL)
{
    
        if ($id == null) {
            redirect('leave/leaveListing');
        }

        // Get leave info
        $data['leaveInfo'] = $this->lve->getLeaveInfo($id);

        // Check if leaveInfo is null
        if ($data['leaveInfo'] === null) {
            // Redirect or show an error message
            redirect('leave/leaveListing'); // or handle as needed
        }

        // Continue only if leaveInfo is valid
        $this->global['pageTitle'] = 'CodeInsect : Edit Leave';
        $this->loadViews("leave/view", $this->global, $data, NULL);
    
}


public function cancelLeave()
{
    $leaveId = $this->input->post('leaveId');
    if ($leaveId) {
        $this->load->model('Leave_model');
        $this->Leave_model->updateLeaveStatus($leaveId, 'Cancelled');
        $this->session->set_flashdata('success', 'Leave cancelled successfully.');
    } else {
        $this->session->set_flashdata('error', 'Leave ID missing.');
    }
    redirect('leave/leaveListing');
}

}