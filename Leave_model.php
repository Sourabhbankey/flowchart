<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leave_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
    }

    public function apply_leave($data) {
        return $this->db->insert('tbl_leaves', $data);
    }

    public function get_leave_applications($leaveId) {
        $query = $this->db->get_where('tbl_leaves', array('leaveId' => $leaveId));
        return $query->result_array();
    }

    /**
     * This function is used to get the leave listing
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function leaveListing($searchText, $page, $segment) {
        $this->db->select('BaseTbl.leaveId, BaseTbl.userId, BaseTbl.leave_type, BaseTbl.start_date, BaseTbl.end_date, BaseTbl.reason, BaseTbl.status, BaseTbl.created_at, BaseTbl.leaveS3File, BaseTbl.replyByHr, BaseTbl.leaveDays, userTbl.name as userName');
        $this->db->from('tbl_leaves as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'BaseTbl.userId = userTbl.userId', 'LEFT');
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.leave_type', $searchText); // Use like() to prevent SQL injection
        }
        $this->db->order_by('BaseTbl.created_at', 'DESC'); // Sort by created_at
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }

    public function getLeaveData($limit, $offset, $start_date = null, $end_date = null) {
        $this->db->select('leaveId, userId, leave_type, start_date, end_date, reason, status, created_at, leaveS3File, replyByHr, leaveDays');
        $this->db->from('tbl_leaves');
        if ($start_date) {
            $this->db->where('start_date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('end_date <=', $end_date);
        }
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getLeaveDataUser($userId, $limit, $offset, $start_date = null, $end_date = null) {
        $this->db->select('leaveId, userId, leave_type, start_date, end_date, reason, status, created_at, leaveS3File, replyByHr, leaveDays');
        $this->db->from('tbl_leaves');
        $this->db->where('userId', $userId);
        if ($start_date) {
            $this->db->where('start_date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('end_date <=', $end_date);
        }
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getLeaveCount($userId = null, $start_date = null, $end_date = null) {
        $this->db->from('tbl_leaves');
        if ($userId !== null) {
            $this->db->where('userId', $userId);
        }
        if ($start_date) {
            $this->db->where('start_date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('end_date <=', $end_date);
        }
        return $this->db->count_all_results();
    }

    public function addNewLeave($leaveInfo) {
        $this->db->trans_start();
        $this->db->insert('tbl_leaves', $leaveInfo);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();
        return $insert_id;
    }

    public function getLeaveInfo($leaveId) {
        $this->db->select('leaveId, leave_type, start_date, end_date, reason, status, created_at, leaveS3File, replyByHr, leaveDays');
        $this->db->from('tbl_leaves');
        $this->db->where('leaveId', $leaveId);
        $query = $this->db->get();
        return $query->num_rows() === 0 ? null : $query->row();
    }

    public function getAllLeaveDataForUser($userId, $start_date = null, $end_date = null) {
        $this->db->select('leaveId, userId, leave_type, start_date, end_date, reason, status, created_at, leaveS3File, replyByHr, leaveDays');
        $this->db->from('tbl_leaves');
        $this->db->where('userId', $userId);
        if ($start_date) {
            $this->db->where('start_date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('end_date <=', $end_date);
        }
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getAllUsers() {
        $this->db->select('userId, name');
        $this->db->from('tbl_users');
        $query = $this->db->get();
        return $query->result();
    }

    public function countUsedLeaves($userId, $leaveType, $start_date = null, $end_date = null) {
        $this->db->select_sum('leaveDays');
        $this->db->where('userId', $userId);
        $this->db->where('leave_type', $leaveType);
        $this->db->where_in('status', ['approved', 'pending']);
        if ($start_date) {
            $this->db->where('start_date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('end_date <=', $end_date);
        }
        $query = $this->db->get('tbl_leaves');
        $result = $query->row();
        return isset($result->leaveDays) ? (float)$result->leaveDays : 0;
    }

    public function updateLeaveStatus($leaveId, $status) {
        return $this->db->where('leaveId', $leaveId)
                        ->update('tbl_leaves', ['status' => $status]);
    }
    public function getLeaveCountByRole($roleId) {
    $this->db->select('leave_count');
    $this->db->from('tbl_hrforms_onboard');
    $this->db->where('roleId', $roleId);
    $this->db->limit(1); // assuming one leave_count per role
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->row()->leave_count;
    }

    return 0; // default if not set
}
}