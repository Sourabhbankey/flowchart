<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Support_model (Support Model)
 * Support model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Administrationtraining_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    public function administrationtrainingListingCount($searchText) {
        $this->db->select('*');
        $this->db->from('tbl_administration_training as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.meetingTitle LIKE '%" . $searchText . "%')";
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
  
  public function administrationtrainingListing($franchiseNumber = "", $limit, $offset, $filter = "") {
        $this->db->select('*');
        $this->db->from('tbl_administration_training as BaseTbl');

        if (!empty($filter)) {
            $this->db->like('BaseTbl.franchiseNumber', $filter);
        }

        if (!empty($franchiseNumber)) {
            $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
        }

        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.timeMeeting', 'DESC'); // Sort by creation date, newest first
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        return $query->result();
    }



    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewAdministrationtraining($administrationtrainingInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_administration_training', $administrationtrainingInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
     function getUser()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.isAdmin, userTbl.email');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14]);
        $query = $this->db->get();
        return $query->result();
    }
    /**
     * This function used to get task information by id
     * @param number $adminMeetingId : This is training id
     * @return array $result : This is training information
     */
    function getadministrationtrainingInfo($adminMeetingId)
    {
        $this->db->select('*');
        $this->db->from('tbl_administration_training');
        $this->db->where('adminMeetingId', $adminMeetingId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editAdministrationtraining($administrationtrainingInfo, $adminMeetingId)
    {
        $this->db->where('adminMeetingId', $adminMeetingId);
        $this->db->update('tbl_administration_training', $administrationtrainingInfo);
        
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
    public function getFranchiseNumberByUserId($userId) {
         $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();

        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }
    public function get_users_without_franchise() {
        $this->db->where('franchiseNumber IS NULL OR franchiseNumber =', '');
        $query = $this->db->get('tbl_users'); // Replace 'users' with your actual table name
        return $query->result();
    }
    
    
    public function get_count($franchiseFilter = null) {
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->where('isDeleted', 0);
        return $this->db->count_all_results('tbl_administration_training');
    }

    public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->where('isDeleted', 0);
        return $this->db->count_all_results('tbl_administration_training');
    }

   public function get_data($limit, $start, $franchiseFilter = null) {
        $this->db->select('*');
        $this->db->from('tbl_administration_training');
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->where('isDeleted', 0);
        $this->db->order_by('timeMeeting', 'DESC'); 
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }

   public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
        $this->db->select('*');
        $this->db->from('tbl_administration_training');
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->where('isDeleted', 0);
        $this->db->order_by('timeMeeting', 'DESC'); 
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }
   public function getNamesFromIds($userId) {
        if (empty($userId)) return '';

        $idArray = explode(',', $userId);
        $this->db->select('name');
        $this->db->from('tbl_users');
        $this->db->where_in('userId', $idArray);
        $query = $this->db->get();

        $names = array_column($query->result_array(), 'name');
        return implode(', ', $names);
    }

}