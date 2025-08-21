<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Pdc_model (Pdc Model)
 * Pdc model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 31 May 2024
 */
class Pdc_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function pdcListingCount($searchText)
    {
        $this->db->select('BaseTbl.pdcId, BaseTbl.franchiseNumber, BaseTbl.brspFranchiseAssigned, BaseTbl.pdcNumber, BaseTbl.dateOfpdcSubmission, BaseTbl.pdcTitle,BaseTbl.dateOfclearance, BaseTbl.statusOfPDc, BaseTbl.pdcAttach, BaseTbl.pdcAmount, BaseTbl.description, BaseTbl.cancellationreason, BaseTbl.createdDtm');
        $this->db->from('tbl_pdc as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'BaseTbl.brspFranchiseAssigned = userTbl.userId', 'LEFT');
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.pdcNumber', $searchText);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function pdcListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.pdcId, BaseTbl.franchiseNumber, BaseTbl.brspFranchiseAssigned, BaseTbl.pdcTitle, BaseTbl.pdcNumber, BaseTbl.dateOfpdcSubmission, BaseTbl.dateOfclearance, BaseTbl.statusOfPDc, BaseTbl.pdcAttach, BaseTbl.pdcAmount, BaseTbl.description, BaseTbl.cancellationreason, BaseTbl.createdDtm, BaseTbl.updatedDtm, userTbl.name as assigned_user_name');
        $this->db->from('tbl_pdc as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'BaseTbl.brspFranchiseAssigned = userTbl.userId', 'LEFT');
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.pdcNumber', $searchText);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.pdcId', 'DESC');
        $this->db->limit($segment, $page);
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewPdc($pdcInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_pdc', $pdcInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $pdcId : This is pdc id
     * @return array $result : This is pdc information
     */
    function getPdcInfo($pdcId)
    {
        $this->db->select('pdcId, franchiseNumber, brspFranchiseAssigned, pdcNumber,pdcTitle, dateOfpdcSubmission, dateOfclearance, statusOfPDc, pdcAttach, pdcAmount, description, cancellationreason');
        $this->db->from('tbl_pdc');
        $this->db->where('pdcId', $pdcId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    public function getFranchiseNumberByPdcId($pdcId)
{
    $this->db->select('franchiseNumber');
    $this->db->from('tbl_pdc'); // Adjust table name if different
    $this->db->where('pdcId', $pdcId); // Adjust column name if different
    $query = $this->db->get();
    return $query->row() ? $query->row()->franchiseNumber : null;
}
    /**
     * This function is used to update the task information
     * @param array $pdcInfo : This is pdc updated information
     * @param number $pdcId : This is pdc id
     */
    function editPdc($pdcInfo, $pdcId)
    {
        $this->db->where('pdcId', $pdcId);
        $this->db->update('tbl_pdc', $pdcInfo);
        
        return TRUE;
    }
    
    /**
     * This function is used to get the franchise information
     * @return array $result : This is result of the query
     */
    function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId,userTbl.pdcTitle, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 25);
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * This function is used to get the user information
     * @return array $result : This is result of the query
     */
    function getUser()
    {
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 15);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getAllacattachmentRecords()
    {
        $this->db->order_by('pdcId', 'DESC');
        $query = $this->db->get('tbl_pdc');
        return $query->result();
    }

    public function getattachmentRecordsByFranchise($franchiseNumber)
    {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->order_by('pdcId', 'DESC');
        $query = $this->db->get('tbl_pdc');
        return $query->result();
    }
    
    public function get_count($franchiseFilter = null)
    {
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_pdc');
    }

    public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null)
    {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_pdc');
    }
    
    public function get_data($limit, $start, $franchiseFilter = null)
    {
        $this->db->select('tbl_pdc.*, tbl_users.name as assigned_user_name');
        $this->db->from('tbl_pdc');
        $this->db->join('tbl_users', 'tbl_pdc.brspFranchiseAssigned = tbl_users.userId', 'LEFT');
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('pdcId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }

    public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null)
    {
        $this->db->select('tbl_pdc.*, tbl_users.name as assigned_user_name');
        $this->db->from('tbl_pdc');
        $this->db->join('tbl_users', 'tbl_pdc.brspFranchiseAssigned = tbl_users.userId', 'LEFT');
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('pdcId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByFranchise($franchiseNumber)
    {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $query = $this->db->get('tbl_pdc');
        return $query->num_rows();
    }
    
    public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $offset)
    {
        $this->db->select('tbl_pdc.*, tbl_users.name as assigned_user_name');
        $this->db->from('tbl_pdc');
        $this->db->join('tbl_users', 'tbl_pdc.brspFranchiseAssigned = tbl_users.userId', 'LEFT');
       $this->db->where('tbl_pdc.franchiseNumber', $franchiseNumber);
        $this->db->order_by('pdcId', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCount()
    {
        return $this->db->count_all('tbl_pdc');
    }
    
    public function getAllTrainingRecords($limit, $start)
    {
        $this->db->select('tbl_pdc.*, tbl_users.name as assigned_user_name');
        $this->db->from('tbl_pdc');
        $this->db->join('tbl_users', 'tbl_pdc.brspFranchiseAssigned = tbl_users.userId', 'LEFT');
        $this->db->order_by('pdcId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByRole($roleId)
    {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->from('tbl_pdc');
        return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByRole($roleId, $limit, $start)
    {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->order_by('pdcId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_pdc');
        return $query->result();
    }
    
    public function getFranchiseNumberByUserId($userId)
    {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }
    
    public function getUsersByFranchise($franchiseNumber)
    {
        $this->db->select('tbl_users.userId, tbl_users.name');
        $this->db->from('tbl_branches');
        $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
        $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber);
        $this->db->where('tbl_branches.isDeleted', 0);
        return $this->db->get()->result();
    }
}