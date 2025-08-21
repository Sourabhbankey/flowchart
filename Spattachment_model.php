<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Task_model (Task Model)
 * Task model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Spattachment_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function spattachmentListingCount($searchText)
    {
        $this->db->select('BaseTbl.spattachmentId, BaseTbl.spattachmentTitle, BaseTbl.spattachmentS3File, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_spattachment as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.spattachmentTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
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
    function spattachmentListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.spattachmentId, BaseTbl.spattachmentTitle, BaseTbl.spattachmentS3File, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_spattachment as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.spattachmentTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.spattachmentId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewSpattachment($spattachmentInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_spattachment', $spattachmentInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $attachmentId : This is attachment id
     * @return array $result : This is attachment information
     */
    function getSpattachmentInfo($spattachmentId)
    {
        $this->db->select('spattachmentId, spattachmentTitle, spattachmentS3File, description');
        $this->db->from('tbl_spattachment');
        $this->db->where('spattachmentId', $spattachmentId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editSpattachment($spattachmentInfo, $spattachmentId)
    {
        $this->db->where('spattachmentId', $spattachmentId);
        $this->db->update('tbl_spattachment', $spattachmentInfo);
        
        return TRUE;
    }
	
	public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->from('tbl_spattachment');
    return $this->db->count_all_results();
	}
	
	public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->limit($limit, $start);
    $query = $this->db->get('tbl_spattachment');
    return $query->result();
	}
	public function getTotalTrainingRecordsCount() {
    return $this->db->count_all('tbl_spattachment');
	}
	public function getAllTrainingRecords($limit, $start) {
    $this->db->limit($limit, $start);
    $query = $this->db->get('tbl_spattachment');
    return $query->result();
	}
	public function getTotalTrainingRecordsCountByRole($roleId) {
    $this->db->where('assignedTo', $roleId);
    $this->db->from('tbl_spattachment');
    return $this->db->count_all_results();
	}
	public function getTrainingRecordsByRole($roleId, $limit, $start) {
    $this->db->where('assignedTo', $roleId);
    $this->db->limit($limit, $start);
    $query = $this->db->get('tbl_spattachment');
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
	public function getdmfranchseRecordsByFranchise($franchiseNumber) {
        $this->db->where("FIND_IN_SET('$franchiseNumber', franchiseNumber) >", 0);
	
        $query = $this->db->get('tbl_spattachment');
        return $query->result();
    }
}