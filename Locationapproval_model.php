<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Locationapproval_model (Locationapproval Model)
 * Locationapproval model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 04 June 2024
 */
class Locationapproval_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function locationapprovalListingCount($searchText)
    {
        $this->db->select('BaseTbl.locationApprovalId, BaseTbl.locationTitle, BaseTbl.gmapLink, BaseTbl.locAddress, BaseTbl.nearestBranch, BaseTbl.nearestBranchDistance, BaseTbl.locApprovalStatus,BaseTbl.locationVideos, BaseTbl.locationImages, BaseTbl.locationGeolocation, BaseTbl.franchiseNumber, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_location_approval as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.gmapLink LIKE '%".$searchText."%')";
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
  public function locationapprovalListing($searchText, $page, $segment) {
    $this->db->select('BaseTbl.locationApprovalId, BaseTbl.locationTitle, BaseTbl.gmapLink, BaseTbl.locAddress, BaseTbl.nearestBranch, BaseTbl.nearestBranchDistance, BaseTbl.locApprovalStatus, BaseTbl.franchiseNumber, BaseTbl.locationVideos, BaseTbl.locationImages, BaseTbl.locationGeolocation, BaseTbl.description, BaseTbl.createdDtm');
    $this->db->from('tbl_location_approval as BaseTbl');
    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.gmapLink LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by creation date, newest first
    $this->db->limit($page, $segment);
    $query = $this->db->get();
    
    $result = $query->result();
    return $result;
}
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewLocationapproval($locationapprovalInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_location_approval', $locationapprovalInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $locationApprovalId : This is training id
     * @return array $result : This is training information
     */
    function getlocationapprovalInfo($locationApprovalId)
    {
        $this->db->select('locationApprovalId, locationTitle, gmapLink, locAddress, nearestBranch, nearestBranchDistance, locApprovalStatus, franchiseNumber, locationGeolocation,locationImages
locationVideos,description');
        $this->db->from('tbl_location_approval');
        $this->db->where('locationApprovalId', $locationApprovalId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editLocationapproval($locationapprovalInfo, $locationApprovalId)
    {
        $this->db->where('locationApprovalId', $locationApprovalId);
        $this->db->update('tbl_location_approval', $locationapprovalInfo);
        
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
   

public function getAllacattachmentRecords() {
        // Fetch all records from tbl_onbord_frm
        $query = $this->db->get('tbl_location_approval');
        return $query->result();
    }

    public function getattachmentRecordsByFranchise($franchiseNumber) {
        // Fetch records from tbl_onbord_frm for the specific franchise
        $this->db->where('franchiseNumber', $franchiseNumber);
        
        $query = $this->db->get('tbl_location_approval');
        return $query->result();
    }

    
    
    
    public function get_count($franchiseFilter = null) {
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_location_approval');
}

public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_location_approval');
}
public function get_data($limit, $start, $franchiseFilter = null) {
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    $query = $this->db->get('tbl_location_approval');
    return $query->result();
}

public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    $query = $this->db->get('tbl_location_approval');
    return $query->result();
}
public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->from('tbl_location_approval');
    return $this->db->count_all_results();
    }
    
    function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
    $this->db->select('locationApprovalId, locationTitle, gmapLink, locAddress, nearestBranch, nearestBranchDistance, locApprovalStatus, franchiseNumber, locationVideos, locationImages, locationGeolocation, description, createdDtm');
    $this->db->from('tbl_location_approval');
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->where('isDeleted', 0);
    $this->db->order_by('createdDtm', 'DESC'); // Sort by creation date, newest first
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
     public function getTotalTrainingRecordsCount() {
    return $this->db->count_all('tbl_location_approval');
    }
    
  public function getAllTrainingRecords($limit, $start) {
    $this->db->select('locationApprovalId, locationTitle, gmapLink, locAddress, nearestBranch, nearestBranchDistance, locApprovalStatus, franchiseNumber, locationVideos, locationImages, locationGeolocation, description, createdDtm');
    $this->db->from('tbl_location_approval');
    $this->db->where('isDeleted', 0);
    $this->db->order_by('createdDtm', 'DESC'); // Sort by creation date, newest first
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
    public function getTotalTrainingRecordsCountByRole($roleId) {
    $this->db->where('brspFranchiseAssigned', $roleId);
    $this->db->from('tbl_location_approval');
    return $this->db->count_all_results();
    }
  public function getTrainingRecordsByRole($roleId, $limit, $start) {
    $this->db->select('locationApprovalId, locationTitle, gmapLink, locAddress, nearestBranch, nearestBranchDistance, locApprovalStatus, franchiseNumber, locationVideos, locationImages, locationGeolocation, description, createdDtm');
    $this->db->from('tbl_location_approval');
    $this->db->where('brspFranchiseAssigned', $roleId);
    $this->db->where('isDeleted', 0);
    $this->db->order_by('createdDtm', 'DESC'); // Sort by creation date, newest first
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

  /*  //ajax to get support manager according to franchise 
public function getManagersByFranchise($franchiseNumber)
{
    $this->db->select('userId, name');
    $this->db->from('tbl_users');  // Make sure to use the correct table
    $this->db->where('franchiseNumber', $franchiseNumber);  // Match the franchise number
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->result();  // Return the list of managers
    } else {
        return null;  // Return null if no managers found
    }
} */


public function getUsersByFranchise($franchiseNumber) {
    $this->db->select('tbl_users.userId, tbl_users.name');
    $this->db->from('tbl_branches');
    $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
    $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber); // Filter by franchise number
    $this->db->where('tbl_branches.isDeleted', 0); // Assuming you only want active records
    return $this->db->get()->result();
}
}