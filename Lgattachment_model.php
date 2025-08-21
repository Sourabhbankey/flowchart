<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Lgattachment_model (Task Model)
 * Task model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Lgattachment_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function lgattachmentListingCount($searchText)
    {
        $this->db->select('BaseTbl.lgattachmentId, BaseTbl.lgattachmentTitle, BaseTbl.franchiseNumber, BaseTbl.attachmentType, BaseTbl.otherAttachmentType, BaseTbl.lgattachmentS3File, BaseTbl.description,BaseTbl.createdBy, BaseTbl.createdDtm, tbl_users.name as created_by_name');
        $this->db->from('tbl_lgattachment as BaseTbl');
        $this->db->join('tbl_users', 'tbl_users.userId = BaseTbl.createdBy', 'left'); // Ensure join is applied
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.lgattachmentTitle', $searchText);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        return $this->db->count_all_results();
    }
    
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
function lgattachmentListing($searchText, $page, $segment)
{
    $this->db->select('BaseTbl.lgattachmentId, BaseTbl.lgattachmentTitle, BaseTbl.franchiseNumber, BaseTbl.attachmentType,  BaseTbl.otherAttachmentType,BaseTbl.lgattachmentS3File, BaseTbl.createdBy, BaseTbl.description, BaseTbl.createdDtm, tbl_users.name as created_by_name');
    $this->db->from('tbl_lgattachment as BaseTbl');
    $this->db->join('tbl_users', 'tbl_users.userId = BaseTbl.createdBy', 'left');
    if (!empty($searchText)) {
        $this->db->like('BaseTbl.lgattachmentTitle', $searchText);
    }
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($segment, $page);
    return $this->db->get()->result();
}
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewLgattachment($lgattachmentInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_lgattachment', $lgattachmentInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $attachmentId : This is attachment id
     * @return array $result : This is attachment information
     */
    function getLgattachmentInfo($lgattachmentId)
    {
        $this->db->select('tbl_lgattachment.*, tbl_users.name as created_by_name');
        $this->db->from('tbl_lgattachment');
        $this->db->join('tbl_users', 'tbl_users.userId = tbl_lgattachment.createdBy', 'left');
        $this->db->where('tbl_lgattachment.lgattachmentId', $lgattachmentId);
        $this->db->where('tbl_lgattachment.isDeleted', 0);
        return $this->db->get()->row();
    }
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editLgattachment($lgattachmentInfo, $lgattachmentId)
    {
        $this->db->where('lgattachmentId', $lgattachmentId);
        $this->db->update('tbl_lgattachment', $lgattachmentInfo);
        
        return TRUE;
    }

    function getUser()
    {
        /*---Growth-Support--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 15);
        $query = $this->db->get();
        return $query->result();
    }
    
    function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 25);
        $query = $this->db->get();
        return $query->result();
    }

 public function getAllacattachmentRecords() {
    $this->db->select('tbl_lgattachment.*, tbl_users.name as created_by_name');
    $this->db->from('tbl_lgattachment');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_lgattachment.createdBy', 'left');
    $this->db->where('tbl_lgattachment.isDeleted', 0);
    $this->db->order_by('tbl_lgattachment.createdDtm', 'DESC'); // Sort by createdDtm
    $query = $this->db->get();
    return $query->result();
}
   public function getattachmentRecordsByFranchise($franchiseNumber) {
    $this->db->select('tbl_lgattachment.*, tbl_users.name as created_by_name');
    $this->db->from('tbl_lgattachment');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_lgattachment.createdBy', 'left');
    $this->db->where('tbl_lgattachment.franchiseNumber', $franchiseNumber);
    $this->db->where('tbl_lgattachment.isDeleted', 0);
    $this->db->order_by('tbl_lgattachment.createdDtm', 'DESC'); // Sort by createdDtm
    $query = $this->db->get();
    return $query->result();
}
    public function get_count($franchiseFilter = null) {
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_lgattachment');
    }

    public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_lgattachment');
    }

   public function get_data($limit, $start, $franchiseFilter = null)
{
    $this->db->select('tbl_lgattachment.*, tbl_users.name as created_by_name');
    $this->db->from('tbl_lgattachment');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_lgattachment.createdBy', 'left');
    if ($franchiseFilter) {
        $this->db->where('tbl_lgattachment.franchiseNumber', $franchiseFilter);
    }
    $this->db->where('tbl_lgattachment.isDeleted', 0);
    $this->db->order_by('tbl_lgattachment.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    return $this->db->get()->result();
}


   public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
    $this->db->select('tbl_lgattachment.*, tbl_users.name as created_by_name');
    $this->db->from('tbl_lgattachment');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_lgattachment.createdBy', 'left');
    $this->db->where('tbl_lgattachment.franchiseNumber', $franchiseNumber);
    if ($franchiseFilter) {
        $this->db->where('tbl_lgattachment.franchiseNumber', $franchiseFilter);
    }
    $this->db->where('tbl_lgattachment.isDeleted', 0);
    $this->db->order_by('tbl_lgattachment.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
    public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->from('tbl_lgattachment');
        return $this->db->count_all_results();
    }
    
  public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start)
{
    $this->db->select('tbl_lgattachment.*, tbl_users.name as created_by_name');
    $this->db->from('tbl_lgattachment');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_lgattachment.createdBy', 'left');
    $this->db->where('tbl_lgattachment.franchiseNumber', $franchiseNumber);
    $this->db->where('tbl_lgattachment.isDeleted', 0);
    $this->db->order_by('tbl_lgattachment.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    return $this->db->get()->result();
}
    
    public function getTotalTrainingRecordsCount() {
        return $this->db->count_all('tbl_lgattachment');
    }
    
   public function getAllTrainingRecords($limit, $start) {
    $this->db->select('tbl_lgattachment.*, tbl_users.name as created_by_name');
    $this->db->from('tbl_lgattachment');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_lgattachment.createdBy', 'left');
    $this->db->where('tbl_lgattachment.isDeleted', 0);
    $this->db->order_by('tbl_lgattachment.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
    public function getTotalTrainingRecordsCountByRole($roleId) {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->from('tbl_lgattachment');
        return $this->db->count_all_results();
    }

 public function getTrainingRecordsByRole($roleId, $limit, $start) {
    $this->db->select('tbl_lgattachment.*, tbl_users.name as created_by_name');
    $this->db->from('tbl_lgattachment');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_lgattachment.createdBy', 'left');
    $this->db->where('tbl_lgattachment.brspFranchiseAssigned', $roleId);
    $this->db->where('tbl_lgattachment.isDeleted', 0);
    $this->db->order_by('tbl_lgattachment.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
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
        $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber); // Filter by franchise number
        $this->db->where('tbl_branches.isDeleted', 0); // Assuming you only want active records
        return $this->db->get()->result();
    }

    public function insertReply($data)
    {
        $this->db->insert('tbl_lgattachment_replies', $data); 
        return $this->db->insert_id();
    }

   public function getReplies($lgattachmentId)
{
    $this->db->select('r.*, tbl_users.name as addedByName');
    $this->db->from('tbl_lgattachment_replies r');
    $this->db->join('tbl_users', 'tbl_users.userId = r.createdBy', 'left');
    $this->db->where('r.lgattachmentId', $lgattachmentId);
    $this->db->order_by('r.createdDtm', 'DESC'); // Sort replies by createdDtm
    $query = $this->db->get();
    return $query->result();
}
}