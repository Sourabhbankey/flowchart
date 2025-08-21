<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Dmfranchseho_model (Dmfranchise Model)
 * Dmfranchise model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 03 June 2024
 */
class Dmfranchseho_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function dmfranchsehoListingCount($searchText)
    {
        $this->db->select('BaseTbl.dmfranchsehoId, BaseTbl.dmfranchseTitle,BaseTbl.dmreceiptattachmentS3file, BaseTbl.doneBy, BaseTbl.numOfLeads, BaseTbl.dateOfrequest, BaseTbl.CampaStartdate, BaseTbl.CampaEnddate, BaseTbl.platform, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_digital_ho as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'BaseTbl.brspFranchiseAssigned = userTbl.userId', 'LEFT');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.dmfranchseTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
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
    function dmfranchsehoListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.dmfranchsehoId, BaseTbl.dmfranchseTitle, BaseTbl.doneBy,BaseTbl.dmreceiptattachmentS3file, BaseTbl.numOfLeads, BaseTbl.dateOfrequest, BaseTbl.CampaStartdate, BaseTbl.CampaEnddate, BaseTbl.platform, BaseTbl.description, BaseTbl.createdDtm, BaseTbl.dmattachmentS3file');
        $this->db->from('tbl_digital_ho as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.dmfranchseTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Changed to sort by createdDtm
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewDmfranchseho($dmfranchsehoInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_digital_ho', $dmfranchsehoInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $dmfranchsehoId : This is training id
     * @return array $result : This is training information
     */
    function getDmfranchsehoInfo($dmfranchsehoId)
    {
        $this->db->select('*');
        $this->db->from('tbl_digital_ho');
        $this->db->where('dmfranchsehoId', $dmfranchsehoId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    /**
     * This function is used to update the task information
     * @param array $dmfranchsehoInfo : This is task updated information
     * @param number $dmfranchsehoId : This is task id
     */
    function editDmfranchseho($dmfranchsehoInfo, $dmfranchsehoId)
    {
        $this->db->where('dmfranchsehoId', $dmfranchsehoId);
        $this->db->update('tbl_digital_ho', $dmfranchsehoInfo);
        
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
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 18);
        $query = $this->db->get();
        return $query->result();
    }
   
    function getGrowthuser()
    {
        /*---Admission--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 15);
        $query = $this->db->get();
        return $query->result();
    }
 
    public function getAllacattachmentRecords() {
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $query = $this->db->get('tbl_digital_ho');
        return $query->result();
    }

    public function getattachmentRecordsByFranchise($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $query = $this->db->get('tbl_digital_ho');
        return $query->result();
    }
    
    public function get_count($franchiseFilter = null) {
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_digital_ho');
    }

    public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_digital_ho');
    }
    
    public function get_data($limit, $start, $franchiseFilter = null) {
        $this->db->limit($limit, $start);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $query = $this->db->get('tbl_digital_ho');
        return $query->result();
    }

    public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->limit($limit, $start);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $query = $this->db->get('tbl_digital_ho');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->from('tbl_digital_ho');
        return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_digital_ho');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCount() {
        return $this->db->count_all('tbl_digital_ho');
    }
    
    public function getAllTrainingRecords($limit, $start) {
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_digital_ho');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByRole($roleId) {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->from('tbl_digital_ho');
        return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByRole($roleId, $limit, $start) {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_digital_ho');
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