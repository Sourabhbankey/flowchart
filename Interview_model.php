<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Staff_model (Support Model)
 * Support model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Interview_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function InterviewListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_interviewDetails as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.name LIKE '%".$searchText."%')";
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
    function InterviewListing($searchText, $page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_interviewDetails as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.name LIKE '%".$searchText."%')";
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
    function addNewInterview($InterviewInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_interviewDetails', $InterviewInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $staffid : This is training id
     * @return array $result : This is training information
     */
    function getInterviewInfo($interviewId)
    {
        $this->db->select('*');
        $this->db->from('tbl_interviewDetails');
        $this->db->where('interviewId', $interviewId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    public function getStaffByFranchise($franchiseNumber) {
        $this->db->select('*');
        $this->db->from('tbl_interviewDetails'); // Replace with your actual staff table name
        $this->db->where('franchiseNumber', $franchiseNumber);
        $query = $this->db->get();
    
        log_message('debug', 'SQL Query: ' . $this->db->last_query()); // Log query
    
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editInterview($InterviewInfo, $interviewId)
    {
        $this->db->where('interviewId', $interviewId);
        $this->db->update('tbl_interviewDetails', $InterviewInfo);
        
        return TRUE;
    }
    /**
     * This function is used to get the user  information
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
	 // code done by yashi
	///code done by yashi 15 nov

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

public function getAllacattachmentRecords() {
        // Fetch all records from tbl_onbord_frm
        $query = $this->db->get('tbl_interviewDetails');
        return $query->result();
    }

    public function getattachmentRecordsByFranchise($franchiseNumber) {
        // Fetch records from tbl_onbord_frm for the specific franchise
        $this->db->where('franchiseNumber', $franchiseNumber);
        
        $query = $this->db->get('tbl_interviewDetails');
        return $query->result();
    }

    
    
    
    public function get_count($franchiseFilter = null) {
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_interviewDetails');
}

public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_interviewDetails');
}
public function get_data($limit, $start, $franchiseFilter = null) {
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    $query = $this->db->get('tbl_interviewDetails');
    return $query->result();
}

public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    $query = $this->db->get('tbl_interviewDetails');
    return $query->result();
}
public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->from('tbl_interviewDetails');
    return $this->db->count_all_results();
    }
    
     public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->limit($limit, $start);
    $query = $this->db->get('tbl_interviewDetails');
    return $query->result();
    }
    
     public function getTotalTrainingRecordsCount() {
    return $this->db->count_all('tbl_interviewDetails');
    }
    
   public function getAllTrainingRecords($limit, $start) {
    $this->db->select('
        tbl_interviewDetails.*, 
        created_by_user.name as createdByName, 
        updated_by_user.name as updatedByName
    ');
    $this->db->from('tbl_interviewDetails');
    $this->db->join('tbl_users as created_by_user', 'created_by_user.userId = tbl_interviewDetails.createdBy', 'left');
    $this->db->join('tbl_users as updated_by_user', 'updated_by_user.userId = tbl_interviewDetails.updatedBy', 'left');
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}


    public function getTotalTrainingRecordsCountByRole($roleId) {
    $this->db->where('brspFranchiseAssigned', $roleId);
    $this->db->from('tbl_interviewDetails');
    return $this->db->count_all_results();
    }
    public function getTrainingRecordsByRole($roleId, $limit, $start) {
    $this->db->where('brspFranchiseAssigned', $roleId);
    $this->db->limit($limit, $start);
    $query = $this->db->get('tbl_interviewDetails');
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
	//ends here 
}