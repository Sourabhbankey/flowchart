<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Salesrecord_model (Salesrecord Model)
 * Salesrecord model class to get to handle Salesrecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Attendance_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
  function attendanceListingCount($searchText, $userId, $userRole, $searchUserId = '')
{
    $this->db->select('BaseTbl.id');
    $this->db->from('tbl_attendance as BaseTbl');
    $this->db->join('tbl_users as U', 'BaseTbl.userId = U.userId', 'left');

    // 🔍 Fix search condition
    if (!empty($searchText)) {
        $this->db->like('U.name', $searchText);
    }

    if (!empty($searchUserId)) {
        $this->db->where('BaseTbl.userId', $searchUserId);
    }

    // 🔹 Role-Based Filtering
    if (!in_array($userRole, [14, 28, 1])) { 
        if ($userRole == 29) {
            $this->db->where('BaseTbl.userId', $userId);
        } elseif ($userRole == 31) {
            $this->db->where("(BaseTbl.userId = $userId OR U.roleId = 29)");
        } else {
            $this->db->where('BaseTbl.userId', $userId);
        }
    }

    return $this->db->count_all_results();
}

function attendancerecordListing($searchText, $page, $segment, $userId, $userRole, $searchUserId = '')
{
    $this->db->select('BaseTbl.id, BaseTbl.date, BaseTbl.status, BaseTbl.description, U.name as userName');
    $this->db->from('tbl_attendance as BaseTbl');
    $this->db->join('tbl_users as U', 'BaseTbl.userId = U.userId', 'left');

    // 🔍 Fix search condition
    if (!empty($searchText)) {
        $this->db->like('U.name', $searchText);
    }

    if (!empty($searchUserId)) {
        $this->db->where('BaseTbl.userId', $searchUserId);
    }

    // 🔹 Role-Based Filtering
    if (!in_array($userRole, [14, 28, 1])) { 
        if ($userRole == 29) {
            $this->db->where('BaseTbl.userId', $userId);
        } elseif ($userRole == 31) {
            $this->db->where("(BaseTbl.userId = $userId OR U.roleId = 29)");
        } else {
            $this->db->where('BaseTbl.userId', $userId);
        }
    }

    $this->db->order_by('BaseTbl.id', 'DESC');
    $this->db->limit($page, $segment);
    
    $query = $this->db->get();

    // Debugging
   // echo $this->db->last_query(); exit; 

    return $query->result();
}

    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
	 
	 public function addNewAttendanceRecord($attendanceRecord)
{
    $this->db->insert('tbl_attendance', $attendanceRecord);
    return $this->db->insert_id();
}
	 
    
    public function getAttendanceByDate($date)
{
    $this->db->select('*');
    $this->db->from('tbl_attendance');
    $this->db->where('date', $date);

    $query = $this->db->get();
    return $query->result();
}

    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
   
	
	public function editattendancerecord($attendancerecordInfo, $id)
{
    $this->db->where('id', $id);
    $this->db->update('tbl_attendance', $attendancerecordInfo);
    
    // Print last executed query
    /*echo $this->db->last_query();
    exit;*/
     return TRUE;
    
}
public function getAllUsers()
{
    $this->db->select('userId, name');
    $this->db->from('tbl_users');
    $this->db->where('isDeleted', 0);
    $this->db->where_in('roleId', [34, 31, 28, 29]); // Filtering specific roles
    $query = $this->db->get();
    return $query->result();
}


public function getAttendanceByUserId($userId)
{
    $this->db->select('status, description');
    $this->db->from('tbl_attendance');
    $this->db->where('userId', $userId);
    $this->db->where('date', date('Y-m-d')); // Fetch today's attendance
    $query = $this->db->get();
    return $query->row();
}
public function getMonthlyAttendanceByUserId($userId)
{
    $this->db->select('date, status, description');
    $this->db->from('tbl_attendance');
    $this->db->where('userId', $userId);
    $this->db->where('MONTH(date)', date('m')); // Current month
    $this->db->where('YEAR(date)', date('Y')); // Current year
    $this->db->order_by('date', 'ASC');

    $query = $this->db->get();
    return $query->result();
}

public function getUserById($userId)
{
    $this->db->select('name');
    $this->db->from('tbl_users');
    $this->db->where('userId', $userId);
    $query = $this->db->get();
    return $query->row();
}

public function saveDailyAttendance($attendanceData)
{
    foreach ($attendanceData as $userId => $data) {
        // Check if attendance already exists for today
        $this->db->select('id');
        $this->db->from('tbl_attendance');
        $this->db->where('userId', $userId);
        $this->db->where('date', date('Y-m-d')); // Check today's date
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            // If attendance exists, update it
            $this->db->where('userId', $userId);
            $this->db->where('date', date('Y-m-d'));
            $this->db->update('tbl_attendance', [
                'status' => $data['status'],
                'description' => $data['description']
            ]);
        } else {
            // If no attendance exists for today, insert new record
            $this->db->insert('tbl_attendance', [
                'userId' => $userId,
                'date' => date('Y-m-d'),
                'status' => $data['status'],
                'description' => $data['description']
            ]);
        }
    }
    }
    public function checkAttendanceExists($userId, $date)
{
    $this->db->where('userId', $userId);
    $this->db->where('date', $date);
    return $this->db->count_all_results('tbl_attendance') > 0;
}

public function insertAttendance($data)
{
    $this->db->insert('tbl_attendance', $data);
}

public function updateAttendance($userId, $date, $data)
{
    $this->db->where('userId', $userId);
    $this->db->where('date', $date);
    $this->db->update('tbl_attendance', $data);
}
}



   


	

