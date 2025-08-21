<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Branchinstallationimg_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->table = 'tbl_brinsta_imgvideos'; // Define table name for clarity
    }

    /**
     * Insert a new branch installation record
     */
    public function insert($data)
    {
        if (empty($data)) {
            return FALSE;
        }

        $this->db->insert($this->table, $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        log_message('error', 'Insert failed: ' . $this->db->last_query() . ' - Error: ' . $this->db->_error_message());
        return FALSE;
    }

    /**
     * Get total count of non-deleted records, filtered by brspFranchiseAssigned or franchiseNumber
     */
    public function getTotalRecordsCount($userId, $userRole, $franchiseNumbers = [], $isAdminUser = false)
    {
        $this->db->where('isDeleted', 0);

        if ($isAdminUser) {
            // Admin can see all records, optionally filtered by franchiseNumbers
            if (!empty($franchiseNumbers)) {
                $this->db->where_in('franchiseNumber', $franchiseNumbers);
            }
        } elseif ($userRole == '15' || $userRole == '13') {
            // Specific roles see records assigned to them via brspFranchiseAssigned
            $this->db->where('brspFranchiseAssigned', $userId);
        } else {
            // Non-admin users see records based on their franchise number or brspFranchiseAssigned
            if (!empty($franchiseNumbers)) {
                $this->db->where_in('franchiseNumber', $franchiseNumbers);
            }
            // Additionally filter by brspFranchiseAssigned if applicable
            $this->db->or_where('brspFranchiseAssigned', $userId);
        }

        $count = $this->db->count_all_results($this->table);

        if ($count === 0) {
            log_message('debug', 'No records found: ' . $this->db->last_query());
        }

        return $count;
    }

    /**
     * Get paginated records, filtered by brspFranchiseAssigned or franchiseNumber
     */
    public function getAllRecords($limit, $offset, $userId, $userRole, $franchiseNumbers = [], $isAdminUser = false)
    {
        $this->db->select('tbl_brinsta_imgvideos.*, tbl_users.name as created_by_name');
        $this->db->from($this->table);
        $this->db->join('tbl_users', 'tbl_users.userId = tbl_brinsta_imgvideos.createdBy', 'left');
        $this->db->where('tbl_brinsta_imgvideos.isDeleted', 0);

        if ($isAdminUser) {
            // Admin can see all records, optionally filtered by franchiseNumbers
            if (!empty($franchiseNumbers)) {
                $this->db->where_in('tbl_brinsta_imgvideos.franchiseNumber', $franchiseNumbers);
            }
        } elseif ($userRole == '15' || $userRole == '13') {
            // Specific roles see records assigned to them via brspFranchiseAssigned
            $this->db->where('tbl_brinsta_imgvideos.brspFranchiseAssigned', $userId);
        } else {
            // Non-admin users see records based on their franchise number or brspFranchiseAssigned
            if (!empty($franchiseNumbers)) {
                $this->db->where_in('tbl_brinsta_imgvideos.franchiseNumber', $franchiseNumbers);
            }
            // Additionally filter by brspFranchiseAssigned if applicable
            $this->db->or_where('tbl_brinsta_imgvideos.brspFranchiseAssigned', $userId);
        }

        $this->db->order_by('tbl_brinsta_imgvideos.createdDtm', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();

        if ($query->num_rows() == 0) {
            log_message('debug', 'No records found: ' . $this->db->last_query());
        }

        return $query->result();
    }

    /**
     * Get all non-deleted records
     */
    public function get_all($userId, $userRole, $franchiseNumbers = [], $isAdminUser = false)
    {
        $this->db->select('tbl_brinsta_imgvideos.*, tbl_users.name as created_by_name');
        $this->db->from($this->table);
        $this->db->join('tbl_users', 'tbl_users.userId = tbl_brinsta_imgvideos.createdBy', 'left');
        $this->db->where('tbl_brinsta_imgvideos.isDeleted', 0);

        if ($isAdminUser) {
            if (!empty($franchiseNumbers)) {
                $this->db->where_in('tbl_brinsta_imgvideos.franchiseNumber', $franchiseNumbers);
            }
        } elseif ($userRole == '15' || $userRole == '13') {
            $this->db->where('tbl_brinsta_imgvideos.brspFranchiseAssigned', $userId);
        } else {
            if (!empty($franchiseNumbers)) {
                $this->db->where_in('tbl_brinsta_imgvideos.franchiseNumber', $franchiseNumbers);
            }
            $this->db->or_where('tbl_brinsta_imgvideos.brspFranchiseAssigned', $userId);
        }

        $this->db->order_by('tbl_brinsta_imgvideos.createdDtm', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get a single record by ID
     */
   public function get_by_id($id, $franchiseNumber = null) {
    $this->db->select('*');
    $this->db->from('tbl_brinsta_imgvideos');
    $this->db->where('brimgvideoId', $id);
    if ($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
    }
    return $this->db->get()->row();
}

    /**
     * Get total count of records by role (e.g., Growth Manager)
     */
    public function getTotalRecordsCountByRole($userId)
    {
        $this->db->where('isDeleted', 0);
        $this->db->where('brspFranchiseAssigned', $userId);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Get paginated records for a specific role (e.g., Growth Manager)
     */
    public function getRecordsByRole($userId, $limit, $offset)
    {
        $this->db->select('tbl_brinsta_imgvideos.*, tbl_users.name as created_by_name');
        $this->db->from($this->table);
        $this->db->join('tbl_users', 'tbl_users.userId = tbl_brinsta_imgvideos.createdBy', 'left');
        $this->db->where('tbl_brinsta_imgvideos.isDeleted', 0);
        $this->db->where('tbl_brinsta_imgvideos.brspFranchiseAssigned', $userId);
        $this->db->order_by('tbl_brinsta_imgvideos.createdDtm', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Update a record
     */
    public function update($id, $data)
    {
        if (empty($data) || !$id) {
            return FALSE;
        }

        $this->db->where('brimgvideoId', $id);
        $this->db->update($this->table, $data);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Soft delete a record
     */
    public function delete($id)
    {
        if (!$id) {
            return FALSE;
        }

        $data = [
            'isDeleted' => 1,
            'updatedBy' => $this->session->userdata('userId'),
            'updatedDtm' => date('Y-m-d H:i:s')
        ];

        $this->db->where('brimgvideoId', $id);
        $this->db->update($this->table, $data);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Get franchise number by user ID
     */

     
    public function getFranchiseNumberByUserId($userId)
    {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }


    
}