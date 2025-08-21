<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Task_model (Task Model)
 * Task model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Acattachment_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function acattachmentListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_acattachment as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.acattachmentTitle LIKE '%".$searchText."%')";
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
    function acattachmentListing($searchText, $page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_acattachment as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.acattachmentTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.createdDtm', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    

    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewAcattachment($acattachmentInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_acattachment', $acattachmentInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $attachmentId : This is attachment id
     * @return array $result : This is attachment information
     */
    function getAcattachmentInfo($acattachmentId)
    {
        $this->db->select('*');
        $this->db->from('tbl_acattachment');
        $this->db->where('acattachmentId', $acattachmentId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
   public function editAcattachment($acattachmentInfo, $acattachmentId)
{
    $this->db->where('acattachmentId', $acattachmentId);
    $this->db->update('tbl_acattachment', $acattachmentInfo);

    // Debugging: Print the last query executed
   /* echo $this->db->last_query();
    exit; // stop execution to see the printed query*/

    return TRUE;
}
	//code done by yashi
	public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->from('tbl_acattachment');
    return $this->db->count_all_results();
	}
	
	public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->order_by('createdDtm', 'DESC'); 
    $this->db->limit($limit, $start);
    $query = $this->db->get('tbl_acattachment');
    return $query->result();
	}
	
	public function getTotalTrainingRecordsCount() {
    return $this->db->count_all('tbl_acattachment');
	}
	
	public function getAllTrainingRecords($limit, $start) {
        $this->db->order_by('createdDtm', 'DESC'); 
    $this->db->limit($limit, $start);
    $query = $this->db->get('tbl_acattachment');
    return $query->result();
	}
	public function getTotalTrainingRecordsCountByRole($roleId) {
    $this->db->where('assignedTo', $roleId);
    $this->db->from('tbl_acattachment');
    return $this->db->count_all_results();
	}
	


	public function getTrainingRecordsByRole($roleId, $limit, $start) {
    $this->db->where('assignedTo', $roleId);
    $this->db->order_by('createdDtm', 'DESC');
    $this->db->limit($limit, $start);
    $query = $this->db->get('tbl_acattachment');
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
	
    public function getUsersByFranchise($franchiseNumber) {
    $this->db->select('tbl_users.userId, tbl_users.name');
    $this->db->from('tbl_branches');
    $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
    $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber); // Filter by franchise number
    $this->db->where('tbl_branches.isDeleted', 0); // Assuming you only want active records
    return $this->db->get()->result();
}
}


	

	

	

	

	

	

	