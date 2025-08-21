<?php
defined('BASEPATH') OR exit('No direct script access allowed');

 class Hr_model extends CI_Model {
    
   /* public function get_all_attendance() {
        $this->db->select('tbl_leaves.*, tbl_users.name');
        $this->db->from('tbl_leaves');
        $this->db->join('tbl_users', 'tbl_users.userId = tbl_leaves.userId');
        $query = $this->db->get();
        return $query->result();
    }
*/
    public function update_status($leaveId, $status) {
        $this->db->set('status', $status);
        $this->db->where('leaveId', $leaveId);
        return $this->db->update('tbl_leaves');
    }
     /*public function get_status_counts() {
        $this->db->select('status, COUNT(*) as count');
        $this->db->group_by('status');
        $query = $this->db->get('tbl_leaves');
        return $query->result();
    }*/
    public function get_status_counts($selectedUser = '') {
    $this->db->select('status, COUNT(*) as count');
    $this->db->from('tbl_leaves');

    // Apply user filter if a user is selected
    if (!empty($selectedUser)) {
        $this->db->where('userId', $selectedUser);
    }

    $this->db->group_by('status');
    $query = $this->db->get();

    return $query->result();
}

    
public function get_all_attendance($searchText = '', $status = '', $startDate = '', $endDate = '', $selectedUser = '', $userId, $roleId, $isHR = false) {
    $this->db->select('tbl_leaves.*, assigned_user.name AS assignedToName, tbl_users.name, tbl_users.roleId');
    $this->db->from('tbl_leaves');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_leaves.userId'); // Join for requestor's name
    $this->db->join('tbl_users AS assigned_user', 'assigned_user.userId = tbl_leaves.assignedTo', 'left'); // Join for assigned user's name

    // Apply filters
    if (!empty($status)) {
        $this->db->where('tbl_leaves.status', $status);
    }
    if (!empty($searchText)) {
        $this->db->like('tbl_users.name', $searchText);
    }
   if (!empty($startDate)) {
    $this->db->where('tbl_leaves.start_date >=', $startDate);
}
if (!empty($endDate)) {
    $this->db->where('tbl_leaves.end_date <=', $endDate);
}
    if (!empty($selectedUser)) {
        $this->db->where('tbl_leaves.userId', $selectedUser);
    }

    // **Ensure HR can view all records**
    if (!$isHR) {
        // Non-HR users only see assigned leaves
        $this->db->where('tbl_leaves.assignedTo', $userId);
    }

    // Order by latest records
    $this->db->order_by('tbl_leaves.created_at', 'DESC');

    $query = $this->db->get();
    return $query->result();
}


    public function getLeaveInfo($leaveId) {
    $this->db->select('leaveId, leave_type, start_date, end_date, reason, assignedTo,status, created_at, leaveS3File, replyByHr');
    $this->db->from('tbl_leaves');
    $this->db->where('leaveId', $leaveId);
    $query = $this->db->get();

    if ($query->num_rows() === 0) {
        return null; // No record found
    }

    $row = $query->row();

    // If leaveS3File is not empty, split it into an array; otherwise, assign an empty array
    if (!empty($row->leaveS3File)) {
        $row->files = explode(',', $row->leaveS3File);
    } else {
        $row->files = array();
    }

    return $row;
}

      public function addReply($leaveInfo) {
        $this->db->trans_start();
        $this->db->insert('tbl_leaves', $leaveInfo);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();
        return $insert_id;
    }
    public function updateReply($leaveId, $leaveInfo) 
{
    $this->db->where('leaveId', $leaveId);
    $this->db->set('replyByHr', $leaveInfo['replyByHr']); // Update only this field
    $this->db->update('tbl_leaves');

    return ($this->db->affected_rows() > 0); // Return TRUE if updated
}


    public function get_leave_status($leaveId) {
    $this->db->select('status');
    $this->db->from('tbl_leaves'); // Assuming 'tbl_leaves' is your table name
    $this->db->where('leaveId', $leaveId);
    $query = $this->db->get();
    $result = $query->row();
    return $result ? $result->status : null;
}

public function update_leave_status($leaveId, $status) {
    $this->db->set('status', $status);
    $this->db->where('leaveId', $leaveId);
    return $this->db->update('tbl_leaves');
}
 public function get_leave_count_by_status($status) {
        $this->db->where('status', $status);
        return $this->db->count_all_results('tbl_leaves');
    }
    public function getUsersCount()
{
    return $this->db->count_all("tbl_users");
}
 public function getUsersWithDepartment($limit = 10, $start = 0)
{
    return $this->db->select('tbl_users.userId, tbl_users.name AS username, tbl_users.roleId,tbl_users.uStatus,tbl_users.createdDtm, tbl_roles.role AS department')
                    ->from('tbl_users')
                    ->join('tbl_roles', 'tbl_roles.roleId = tbl_users.roleId', 'left')
                    ->where('tbl_users.roleId !=', 25) // Exclude roleId 25
                    ->order_by('tbl_users.name', 'ASC')
                    ->limit((int)$limit, (int)$start)
                    ->get()
                    ->result();
}


}
?>

