<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/BaseController.php';
class Hr extends BaseController {

    public function __construct() {
        parent::__construct();
  $this->load->model('Hr_model', 'h');
         $this->load->helper(array('form', 'url'));
         $this->load->library('form_validation');
          $this->load->model('Task_model', 'tm');
          $this->isLoggedIn();
    }

    public function index() {
        
        redirect('hr/hrListing');
    }

  
    
public function hrListing() {
    $userId = $this->session->userdata('userId');
    $roleId = $this->session->userdata('role');

    $searchText = $this->input->post('searchText', true);
    $statusFilter = $this->input->post('statusFilter');
    $startDate = $this->input->post('start_date');
    $endDate = $this->input->post('end_date');
    $selectedUser = $this->input->post('userFilter');

    $data['searchText'] = $searchText;
    $data['statusFilter'] = $statusFilter;
    $data['start_date'] = $startDate;
    $data['end_date'] = $endDate;
    $data['selectedUser'] = $selectedUser;

    // **Check if HR user is logged in**
    $isHR = ($roleId == 26 || $roleId == 1 || $roleId == 14);

    // Fetch leave records
    $data['attendance_records'] = $this->h->get_all_attendance($searchText, $statusFilter, $startDate, $endDate, $selectedUser, $userId, $roleId, $isHR);

    // Fetch status counts based on selected user
    $data['status_counts'] = $this->h->get_status_counts($selectedUser);
    
   /* print_r($data['status_counts']);
exit;*/
    $data['users'] = $this->tm->getUser();
 $data['usersdept'] = $this->h->getUsersWithDepartment();  
    $this->global['pageTitle'] = 'Leave Management';
    $this->loadViews("hr/list", array_merge($this->global, $data), NULL, NULL);
}

public function usersWithDepartment()
{
    // Load model if not already loaded
    $this->load->model('h'); 

    // Fetch users with their departments
    $data['usersdept'] = $this->h->getUsersWithDepartment();

    // Load the view and pass data (Modify 'users_department_view' as per your actual view file)
    $this->load->view('users_department_view', $data);
}
   /* public function update_status($leaveId, $status) {
        $result = $this->h->update_status($leaveId, $status);
        if ($result) {
            $this->session->set_flashdata('message', 'Status updated successfully');
        } else {
            $this->session->set_flashdata('message', 'Failed to update status');
        }
        redirect('hr/hrListing');
    }
*/
    public function userdeptListing()
{
    $this->load->library('pagination'); // Load Pagination Library

    $config = array();
    $config["base_url"] = base_url("hr/userdeptListing");
    $config["total_rows"] = $this->h->getUsersCount(); // Function to get total users count
    $config["per_page"] = 10; // Number of records per page
    $config["uri_segment"] = 3; // URL segment for pagination

   

    $this->pagination->initialize($config);

    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
    $data["users"] = $this->h->getUsersWithDepartment($config["per_page"], $page); // Fetch paginated data

    $data["links"] = $this->pagination->create_links();

    $this->loadViews("hr/listuser", $this->global, $data, NULL);
}

   public function update_status($leaveId, $status) {
    // Define valid statuses and workflow
    $validStatuses = ['approved_manager', 'rejected_manager', 'approved_hr', 'rejected_hr'];

    if (!in_array($status, $validStatuses)) {
        show_error('Invalid status');
    }

    // If everything is fine, update the status
    $result = $this->h->update_leave_status($leaveId, $status);

    if ($result) {
        $this->session->set_flashdata('success', 'Leave status updated successfully');
    } else {
        $this->session->set_flashdata('error', 'Failed to update leave status');
    }

    redirect('hr/hrListing');
}

 
 public function view($id = NULL) {
    // Redirect if the ID is null
    if ($id == null) {
        redirect('hr/hrListing');
    }

    // Get leave info
    $data['leaveInfo'] = $this->h->getLeaveInfo($id);

    // Check if leaveInfo is null
    if ($data['leaveInfo'] === null) {
        redirect('hr/hrListing'); // or handle as needed
    }

    // Set the page title and load the view
    $this->global['pageTitle'] = 'Leave Details';
    $this->loadViews("hr/view", $this->global, $data, NULL);
}
public function addReply() 
{       
    $userId = $this->session->userdata('userId');
    $leaveId = $this->input->post('leaveId'); // Get leave ID from form

    $replyByHr = $this->security->xss_clean($this->input->post('replyByHr'));

    $leaveInfo = array(
        'userId'    => $userId,
        'replyByHr' => $replyByHr
    );

    if (!empty($leaveId)) {
        // Update existing leave record
        $result = $this->h->updateReply($leaveId, $leaveInfo);
        $message = ($result) ? 'Leave reply updated successfully' : 'Failed to update leave reply';
    } else {
        $message = 'Invalid request, leave ID missing.';
    }

    $this->session->set_flashdata($result ? 'success' : 'error', $message);
    redirect('hr/hrListing');
}

public function download_image()
{
    $fileUrl = $this->input->get('file'); // Get file URL

    if (!$fileUrl) {
        show_error("No file specified.", 400);
    }

    $fileName = basename($fileUrl);

    // Use cURL to fetch file
    $ch = curl_init($fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $fileContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($fileContent === false || $httpCode != 200) {
        show_error("Error: Unable to download file. HTTP Code: $httpCode", 404);
    }

    // Load the download helper
    $this->load->helper('download');

    // Force download
    force_download($fileName, $fileContent);
}



   
}
?>
