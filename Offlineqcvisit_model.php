<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Qcdetails_model (Qcdetails Model)
 * Qcdetails model class to get to handle Qcdetails related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 17 May 2025
 */
class Offlineqcvisit_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function offlineqcvisitListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_offlineqc_visit_inspection as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.franchiseName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
       // $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
 function offlineqcvisitListing($searchText, $page, $segment)
{
    $this->db->select('BaseTbl.*, 
                       CreatedByUser.name as createdByName, 
                       GrowthManagerUser.name as growthManagerName');
    $this->db->from('tbl_offlineqc_visit_inspection as BaseTbl');
    
    // Join for createdBy user
    $this->db->join('tbl_users as CreatedByUser', 'CreatedByUser.userId = BaseTbl.createdBy', 'left');

    // Join for growth manager user
    $this->db->join('tbl_users as GrowthManagerUser', 'GrowthManagerUser.userId = BaseTbl.growth_manager', 'left');

    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.franchiseName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    $this->db->order_by('BaseTbl.offlineVisitQcId', 'DESC');
    $this->db->limit($page, $segment);

    $query = $this->db->get();
    return $query->result();
}

    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewOfflineqcvisit($ofqcdetailsInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_offlineqc_visit_inspection', $ofqcdetailsInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
   /* function getOfqcdetailsInfo($offlineVisitQcId)
    {
        $this->db->select('*');
        $this->db->from('offlineqc_visit_inspection');
        $this->db->where('offlineVisitQcId', $offlineVisitQcId);
      //  $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }*/
    public function getOfqcdetailsInfo($offlineVisitQcId)
{
    $this->db->select('BaseTbl.*, GrowthManagerUser.name as growthManagerName');
    $this->db->from('tbl_offlineqc_visit_inspection as BaseTbl');
    $this->db->join('tbl_users as GrowthManagerUser', 'GrowthManagerUser.userId = BaseTbl.growth_manager', 'left');
    $this->db->where('BaseTbl.offlineVisitQcId', $offlineVisitQcId);
    
    $query = $this->db->get();
    return $query->row();
}
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
function editQcdetails($qcdetailsInfo, $offlineVisitQcId)
{
    $this->db->where('offlineVisitQcId', $offlineVisitQcId);
    $result = $this->db->update('tbl_offlineqc_visit_inspection', $qcdetailsInfo);

    // Print the last executed query and stop execution
   /* echo $this->db->last_query();
    exit;  // stop script here*/

    // The below code will not run because of exit above
    if (!$result) {
        $error = $this->db->error();
        log_message('error', 'Update failed: ' . print_r($error, true));
    }

    return $result;
}

     public function getManagersByFranchise($franchiseNumber)
    {
        return $this->db->select('u.userId, u.name')
                        ->from('tbl_users as u')
                        ->join('tbl_branches as b', 'b.branchFranchiseAssigned = u.userId')
                        ->where('b.franchiseNumber', $franchiseNumber)
                        ->get()
                        ->result();
    }

    /**
     * This function is used to get franchise details
     * @param string $franchiseNumber : This is franchise number
     * @return object $result : This is franchise information
     */
    public function getFranchiseDetails($franchiseNumber)
    {
        return $this->db->select('*')
                        ->from('tbl_branches as f')
                        ->where('f.franchiseNumber', $franchiseNumber)
                        ->get()
                        ->row();
    }
     public function getBranchByFranchiseNumber($franchiseNumber) {
        $this->db->select('franchiseName as branchSetupName, branchAddress as brcompAddress, branchcityName as city, branchState as state');
        $this->db->from('tbl_branches');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null;
        }
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