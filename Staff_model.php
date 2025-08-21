<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Staff_model (Support Model)
 * Support model class to handle staff-related data
 * @author : Ashish
 * @version : 1.1
 * @since : 28 May 2024
 * @updated : 17 May 2025
 */
class Staff_model extends CI_Model
{
    /**
     * Get the staff listing count
     * @param string $searchText : Optional search text
     * @return number $count : Row count
     */
    function staffListingCount($searchText)
    {
        $this->db->from('tbl_staff_details as BaseTbl');
        if (!empty($searchText)) {
            $searchText = $this->db->escape_like_str($searchText);
            $this->db->where("(BaseTbl.name LIKE '%$searchText%')");
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }

    /**
     * Get staff listing with pagination
     * @param string $searchText : Optional search text
     * @param number $page : Pagination limit
     * @param number $segment : Pagination offset
     * @return array $result : Result set
     */
    function staffListing($searchText, $page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_staff_details as BaseTbl');
        if (!empty($searchText)) {
            $searchText = $this->db->escape_like_str($searchText);
            $this->db->where("(BaseTbl.name LIKE '%$searchText%')");
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Latest records on top
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'staffListing query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Add new staff to system
     * @param array $staffInfo : Staff information
     * @return number $insert_id : Last inserted ID
     */
    function addNewStaff($staffInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_staff_details', $staffInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'addNewStaff failed: ' . $this->db->_error_message());
            return 0;
        }
        
        return $insert_id;
    }
    
    /**
     * Get staff information by ID
     * @param number $staffid : Staff ID
     * @return object $result : Staff information
     */
    function getstaffInfo($staffid)
    {
        $this->db->select('*');
        $this->db->from('tbl_staff_details');
        $this->db->where('staffid', $staffid);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'getstaffInfo query failed: ' . $this->db->_error_message());
            return null;
        }
        
        return $query->row();
    }
    
    /**
     * Get staff by franchise number
     * @param string $franchiseNumber : Franchise number
     * @return array : Staff records
     */
    public function getStaffByFranchise($franchiseNumber)
    {
        $this->db->select('staffid, name');
        $this->db->from('tbl_staff_details');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'getStaffByFranchise query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Update staff information
     * @param array $staffInfo : Updated staff information
     * @param number $staffid : Staff ID
     * @return bool : True on success
     */
    function editStaff($staffInfo, $staffid)
    {
        $this->db->where('staffid', $staffid);
        $result = $this->db->update('tbl_staff_details', $staffInfo);
        
        if (!$result) {
            log_message('error', 'editStaff failed: ' . $this->db->_error_message());
            return false;
        }
        
        return true;
    }
    
    /**
     * Get franchise information
     * @return array $result : Franchise records
     */
    function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 25);
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'getFranchise query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get user information for Growth-Support
     * @return array $result : User records
     */
    function getUser()
    {
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1, 14, 2]);
        $this->db->where('userTbl.roleId', 15);
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'getUser query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get all staff records
     * @return array : Staff records
     */
    public function getAllacattachmentRecords()
    {
        $this->db->order_by('createdDtm', 'DESC'); // Latest records on top
        $query = $this->db->get('tbl_staff_details');
        
        if (!$query) {
            log_message('error', 'getAllacattachmentRecords query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get staff records by franchise
     * @param string $franchiseNumber : Franchise number
     * @return array : Staff records
     */
    public function getattachmentRecordsByFranchise($franchiseNumber)
    {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->order_by('createdDtm', 'DESC'); // Latest records on top
        $query = $this->db->get('tbl_staff_details');
        
        if (!$query) {
            log_message('error', 'getattachmentRecordsByFranchise query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get total staff 
     * @param string|null $franchiseFilter : Optional franchise filter
     * @return number : Row count
     */
    public function get_count($franchiseFilter = null)
    {
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_staff_details');
    }
    
    /**
     * Get staff count by franchise
     * @param string $franchiseNumber : Franchise number
     * @param string|null $franchiseFilter : Optional franchise filter
     * @return number : Row count
     */
    public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null)
    {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_staff_details');
    }
    
    /**
     * Get paginated staff data
     * @param number $limit : Number of records
     * @param number $start : Offset
     * @param string|null $franchiseFilter : Optional franchise filter
     * @return array : Staff records
     */
    public function get_data($limit, $start, $franchiseFilter = null)
    {
        $this->db->limit($limit, $start);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('createdDtm', 'DESC'); // Latest records on top
        $query = $this->db->get('tbl_staff_details');
        
        if (!$query) {
            log_message('error', 'get_data query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get paginated staff data by franchise
     * @param string $franchiseNumber : Franchise number
     * @param number $limit : Number of records
     * @param number $start : Offset
     * @param string|null $franchiseFilter : Optional franchise filter
     * @return array : Staff records
     */
    public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null)
    {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->limit($limit, $start);
        $this->db->order_by('createdDtm', 'DESC'); // Latest records on top
        $query = $this->db->get('tbl_staff_details');
        
        if (!$query) {
            log_message('error', 'get_data_by_franchise query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get total staff records count by franchise
     * @param string $franchiseNumber : Franchise number
     * @return number : Row count
     */
    public function getTotalTrainingRecordsCountByFranchise($franchiseNumber)
    {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->from('tbl_staff_details');
        return $this->db->count_all_results();
    }
    
    /**
     * Get paginated staff records by franchise
     * @param string $franchiseNumber : Franchise number
     * @param number $limit : Number of records
     * @param number $start : Offset
     * @return array : Staff records
     */
    public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start)
    {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->limit($limit, $start);
        $this->db->order_by('createdDtm', 'DESC'); // Latest records on top
        $query = $this->db->get('tbl_staff_details');
        
        if (!$query) {
            log_message('error', 'getTrainingRecordsByFranchise query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get total staff records count
     * @return number : Row count
     */
    public function getTotalTrainingRecordsCount()
    {
        return $this->db->count_all('tbl_staff_details');
    }
    
    /**
     * Get all paginated staff records
     * @param number $limit : Number of records
     * @param number $start : Offset
     * @return array : Staff records
     */
    public function getAllTrainingRecords($limit, $start)
    {
        $this->db->limit($limit, $start);
        $this->db->order_by('createdDtm', 'DESC'); // Latest records on top
        $query = $this->db->get('tbl_staff_details');
        
        if (!$query) {
            log_message('error', 'getAllTrainingRecords query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get total staff records count by role
     * @param number $roleId : Role ID
     * @return number : Row count
     */
    public function getTotalTrainingRecordsCountByRole($roleId)
    {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->from('tbl_staff_details');
        return $this->db->count_all_results();
    }
    
    /**
     * Get paginated staff records by role
     * @param number $roleId : Role ID
     * @param number $limit : Number of records
     * @param number $start : Offset
     * @return array : Staff records
     */
    public function getTrainingRecordsByRole($roleId, $limit, $start)
    {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->limit($limit, $start);
        $this->db->order_by('createdDtm', 'DESC'); // Latest records on top
        $query = $this->db->get('tbl_staff_details');
        
        if (!$query) {
            log_message('error', 'getTrainingRecordsByRole query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get franchise number by user ID
     * @param number $userId : User ID
     * @return string|null : Franchise number or null
     */
    public function getFranchiseNumberByUserId($userId)
    {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'getFranchiseNumberByUserId query failed: ' . $this->db->_error_message());
            return null;
        }
        
        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }
    
    /**
     * Get users by franchise number
     * @param string $franchiseNumber : Franchise number
     * @return array : User records
     */
    public function getUsersByFranchise($franchiseNumber)
    {
        $this->db->select('userId, name');
        $this->db->from('tbl_users');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'getUsersByFranchise query failed: ' . $this->db->_error_message());
            return [];
        }
        
        return $query->result();
    }
    
    /**
     * Get staff banking details by ID
     * @param number $staffid : Staff ID
     * @return object : Banking information
     */
    public function getStaffBankDetails($staffid)
    {
        $this->db->select('staffid, bankname, ifsc, accountnumber, cheque');
        $this->db->from('tbl_staff_details');
        $this->db->where('staffid', $staffid);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'getStaffBankDetails query failed: ' . $this->db->_error_message());
            return null;
        }
        
        return $query->row();
    }
}