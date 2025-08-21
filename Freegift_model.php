<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Freegift_model (Freegift Model)
 * Freegift model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 20 Jun 2024
 */
class Freegift_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function freegiftListingCount($searchText)
    {
        $this->db->select('BaseTbl.freegiftId, BaseTbl.giftTitle, BaseTbl.approvedBy, BaseTbl.dateOfDespatch, BaseTbl.modeOfDespatch, BaseTbl.dateDelevery, BaseTbl.delStatus, BaseTbl.franchiseNumber, BaseTbl.snapshotDespS3File, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_freegift_HO as BaseTbl');
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.giftTitle', $searchText); // Use like() to prevent SQL injection
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the task listing
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function freegiftListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.freegiftId, BaseTbl.giftTitle, BaseTbl.approvedBy, BaseTbl.dateOfDespatch, BaseTbl.modeOfDespatch, BaseTbl.dateDelevery, BaseTbl.delStatus, BaseTbl.franchiseNumber, BaseTbl.snapshotDespS3File, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_freegift_HO as BaseTbl');
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.giftTitle', $searchText); // Use like() to prevent SQL injection
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by createdDtm
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewFreegift($freegiftInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_freegift_HO', $freegiftInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $freegiftId : This is training id
     * @return array $result : This is training information
     */
    function getFreegiftInfo($freegiftId)
    {
        $this->db->select('freegiftId, giftTitle, approvedBy, dateOfDespatch, modeOfDespatch, dateDelevery, delStatus, franchiseNumber, snapshotDespS3File, description');
        $this->db->from('tbl_freegift_HO');
        $this->db->where('freegiftId', $freegiftId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    /**
     * This function is used to update the task information
     * @param array $freegiftInfo : This is task updated information
     * @param number $freegiftId : This is task id
     */
    function editFreegift($freegiftInfo, $freegiftId)
    {
        $this->db->where('freegiftId', $freegiftId);
        $this->db->update('tbl_freegift_HO', $freegiftInfo);
        
        return TRUE;
    }
    
    /**
     * This function is used to get the user information
     * @return array $result : This is result of the query
     */
    function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 25);
        $query = $this->db->get();
        return $query->result();
    }
    
    function getUser()
    {
        /*---Growth-Support--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getAllacattachmentRecords() {
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $query = $this->db->get('tbl_freegift_HO');
        return $query->result();
    }
    
    public function getattachmentRecordsByFranchise($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $query = $this->db->get('tbl_freegift_HO');
        return $query->result();
    }
    
    public function get_count($franchiseFilter = null) {
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_freegift_HO');
    }
    
    public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_freegift_HO');
    }
    
    public function get_data($limit, $start, $franchiseFilter = null) {
        $this->db->limit($limit, $start);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $query = $this->db->get('tbl_freegift_HO');
        return $query->result();
    }
    
    public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->limit($limit, $start);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $query = $this->db->get('tbl_freegift_HO');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->from('tbl_freegift_HO');
        return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_freegift_HO');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCount() {
        return $this->db->count_all('tbl_freegift_HO');
    }
    
    public function getAllTrainingRecords($limit, $start) {
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_freegift_HO');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByRole($roleId) {
        $this->db->group_start(); // Open grouping for OR condition
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->or_where('approvedBy', $roleId);
        $this->db->group_end(); // Close grouping
        $this->db->from('tbl_freegift_HO');
        return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByRole($roleId, $limit, $start) {
        $this->db->group_start(); // Open grouping for OR condition
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->or_where('approvedBy', $roleId);
        $this->db->group_end(); // Close grouping
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_freegift_HO');
        return $query->result();
    }
    
    public function getFranchiseNumberByUserId($userId) {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }
    
    public function getUsersByFranchise($franchiseNumber) {
        $this->db->select('tbl_users.userId, tbl_users.name');
        $this->db->from('tbl_branches');
        $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
        $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber);
        $this->db->where('tbl_branches.isDeleted', 0);
        return $this->db->get()->result();
    }
}