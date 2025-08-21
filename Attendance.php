<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Salesrecord (SalesrecordController)
 * Salesrecord Class to control Salesrecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Attendance extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {  
        parent::__construct();
       $this->load->model('attendance_model', 'attn');
        $this->isLoggedIn();
        $this->module = 'attendance';
         $this->load->library('pagination');
		
		
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('attendance/attendanceListing');
    }
    
    /**
     * This function is used to load the salesrecord list
     */
 public function attendanceListing()
{
    $date = $this->input->get('searchDate') ?: date('Y-m-d');
    $searchUserId = $this->input->get('searchUserId') ?? '';

    $this->load->model('Attendance_model');
    $this->load->model('User_model');

    // Get all users
    $users = $this->attn->getAllUsers();

    // Get attendance data for the selected date
    $attendanceData = $this->Attendance_model->getAttendanceByDate($date, $searchUserId);

    // Map attendance data to users
    foreach ($users as $user) {
        $user->status = '';
        $user->description = '';

        foreach ($attendanceData as $attendance) {
            if ($attendance->userId == $user->userId) {
                $user->status = $attendance->status;
                $user->description = $attendance->description;
                break;
            }
        }
    }

    $data['users'] = $users;
    $data['searchDate'] = $date;
    $data['searchUserId'] = $searchUserId;

    $this->global['pageTitle'] = 'CodeInsect : My Attendance';
    $this->loadViews("attendance/list", $this->global, $data, NULL);
}


   /* }*/

    /**
     * This function is used to load the add new form
     */
    function add()

    {   
	
        /*if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            $this->global['pageTitle'] = 'CodeInsect : Add New Attendance';
			

          // $this->loadViews("salesrecord/add", $this->global, $data);
           $this->loadViews("attendance/add", $this->global, Null, NULL);
        }
		
   /* }*/
    
    /**
     * This function is used to add new user to the system
     */

public function addNewAttendancerecord()
{
    /* if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {*/
        $this->load->library('form_validation');

        
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            // Get form data
           $userId = $this->session->userdata('userId');
        $roleId = $this->session->userdata('role');
            $date   = $this->security->xss_clean($this->input->post('date'));
            $status = $this->security->xss_clean($this->input->post('status'));
          
           
                
            $description = $this->security->xss_clean($this->input->post('description'));

          

            // Create new record array
            $attendancerecordInfo = array(
                 'userId'              => $userId,  // Insert the userId
            'roleId'            => $roleId,
                 'date' => $date, 
                
                 'status' => $status, 
              
                
                'description' => $description
                
            );

            // Insert the new sales record
            $result = $this->attn->addNewattendancerecord($attendancerecordInfo);
//print_r($dailyreportrecordInfo);exit;
            if ($result > 0) {
               
                $this->session->set_flashdata('success', 'New attendance record created successfully ');
            } else {
                $this->session->set_flashdata('error', 'attendance record creation failed');
            }

            redirect('attendance/attendanceListing');
        }
   /* }*/
    
}

    
    /**
     * This function is used load salesrecord edit information
     * @param number $salesrecId : Optional : This is salesrecord id
     */
    function edit($id = NULL)
    {
       /*if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            if($id == null)
            {
                redirect('attendance/attendanceListing');
            }
            
            $data['attendancerecordInfo'] = $this->dr->getattendancerecordInfo($attendanceId );

            $this->global['pageTitle'] = 'CodeInsect : Edit attendance';
            
            $this->loadViews("attendance/edit", $this->global, $data, NULL);
        }
    /*}*/
    
    
    /**
     * This function is used to edit the user information
   /*  */
   
public function saveAttendance()
{
    $userId = $this->session->userdata('userId'); // Logged-in user ID
    $roleId = $this->session->userdata('role');  // Logged-in user role
    $attendanceDate = $this->input->post('attendance_date'); // Selected date
    $attendanceData = $this->input->post('attendance'); // Fetch form data

    if (!empty($attendanceData)) {
        foreach ($attendanceData as $entry) {
            // Skip if status is not set
            if (!isset($entry['status']) || empty($entry['status'])) {
                continue;
            }

            $attendanceRecord = array(
                'userId' => $entry['userId'],
                'roleId' => $roleId,
                'date' => $attendanceDate, // Use selected date
                'status' => $entry['status'], 
                'description' => isset($entry['description']) ? $entry['description'] : NULL
            );

            // Check if attendance record exists for the same date and user
            if ($this->attn->checkAttendanceExists($entry['userId'], $attendanceDate)) {
                // Update existing record
                $this->attn->updateAttendance($entry['userId'], $attendanceDate, $attendanceRecord);
            } else {
                // Insert new record
                $this->attn->addNewAttendanceRecord($attendanceRecord);
            }
        }

        $this->session->set_flashdata('success', 'Attendance recorded successfully.');
    } else {
        $this->session->set_flashdata('error', 'No data received.');
    }

    redirect('attendance/attendanceListing?searchDate=' . $attendanceDate);
}

private function getUsersWithAttendance()
{
    $users = $this->attn->getAllUsers(); // Get all users
    foreach ($users as &$user) {
        $attendance = $this->attn->getAttendanceByUserId($user->userId); // Get attendance record for this user
        if ($attendance) {
            $user->status = $attendance->status;
            $user->description = $attendance->description;
        } else {
            $user->status = ''; // Default (no attendance record)
            $user->description = '';
        }
    }
    return $users;
}
public function viewAttendance($userId)
{
    $userRole = $this->session->userdata('role'); // Get the logged-in user's role

    $monthParam = $this->input->get('month'); // 👈 catch month from URL parameter

    if (empty($monthParam)) {
        $year = date('Y');
        $month = date('m');
    } else {
        $parts = explode('-', $monthParam); // '2025-03' ➔ ['2025', '03']
        if (count($parts) == 2) {
            $year = $parts[0];
            $month = $parts[1];
        } else {
            $year = date('Y');
            $month = date('m');
        }
    }

    // Fetch the attendance records for the selected month
    $data['records'] = $this->attn->getMonthlyAttendanceByUserId($userId, $month, $year);
    $data['user'] = $this->attn->getUserById($userId);

    $data['selectedMonth'] = $monthParam ? $monthParam : date('Y-m'); // For form display etc.

    $this->global['pageTitle'] = 'User Attendance - ' . $data['user']->name;
    $this->loadViews("attendance/view", $this->global, $data, NULL);
}




}

?>