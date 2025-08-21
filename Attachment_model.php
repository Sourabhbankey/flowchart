<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Attachment_model (Task Model)
 * Task model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Attachment_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function attachmentListingCount($searchText)
    {
        $this->db->select('BaseTbl.attachmentId, BaseTbl.attachmentTitle, BaseTbl.linkAttachment, BaseTbl.attachmentType, BaseTbl.franchiseNumber, BaseTbl.attachmentS3File, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_attachment as BaseTbl');
        if(!empty($searchText)) {
            $this->db->like('BaseTbl.attachmentTitle', $searchText); // Use like() to prevent SQL injection
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
    function attachmentListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.attachmentId, BaseTbl.attachmentTitle, BaseTbl.linkAttachment, BaseTbl.attachmentType, BaseTbl.franchiseNumber, BaseTbl.attachmentS3File, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_attachment as BaseTbl');
        if(!empty($searchText)) {
            $this->db->like('BaseTbl.attachmentTitle', $searchText); // Use like() to prevent SQL injection
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
    function addNewAttachment($attachmentInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_attachment', $attachmentInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $attachmentId : This is attachment id
     * @return array $result : This is attachment information
     */
    function getAttachmentInfo($attachmentId)
    {
        $this->db->select('attachmentId, attachmentTitle, attachmentType, linkAttachment, franchiseNumber, attachmentS3File, description');
        $this->db->from('tbl_attachment');
        $this->db->where('attachmentId', $attachmentId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    /**
     * This function is used to update the task information
     * @param array $attachmentInfo : This is task updated information
     * @param number $attachmentId : This is task id
     */
    function editAttachment($attachmentInfo, $attachmentId)
    {
        $this->db->where('attachmentId', $attachmentId);
        $this->db->update('tbl_attachment', $attachmentInfo);
        
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
        $this->db->where('userTbl.roleId', 19);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->from('tbl_attachment');
        return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_attachment');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCount() {
        return $this->db->count_all('tbl_attachment');
    }
    
    public function getAllTrainingRecords($limit, $start) {
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_attachment');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByRole($roleId) {
        $this->db->where('assignedTo', $roleId);
        $this->db->from('tbl_attachment');
        return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByRole($roleId, $limit, $start) {
        $this->db->where('assignedTo', $roleId);
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_attachment');
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
    
    public function get_users_without_franchise() {
        $this->db->where('roleId !=', 25);
        $query = $this->db->get('tbl_users'); 
        return $query->result();
    }
    
    public function getdmfranchseRecordsByFranchise($franchiseNumber, $limit, $start) {
        $this->db->where("FIND_IN_SET('$franchiseNumber', franchiseNumber) >", 0);
        $this->db->order_by('createdDtm', 'DESC'); // Add sorting
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_attachment');
        return $query->result();
    }
}