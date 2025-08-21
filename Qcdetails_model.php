<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Qcdetails_model (Qcdetails Model)
 * Qcdetails model class to get to handle Qcdetails related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 17 May 2025
 */
class Qcdetails_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function qcdetailsListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_qc_details as BaseTbl');
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
   function qcdetailsListing($searchText, $page, $segment)
{
    $this->db->select('BaseTbl.*, Users.name as createdByName');
    $this->db->from('tbl_qc_details as BaseTbl');
    $this->db->join('tbl_users as Users', 'Users.userId = BaseTbl.createdBy', 'left');
   

    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.franchiseName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    $this->db->order_by('BaseTbl.qcId', 'DESC');
    $this->db->limit($page, $segment);

    $query = $this->db->get();
    return $query->result();
}


    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewQcdetails($qcdetailsInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_qc_details', $qcdetailsInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getQcdetailsInfo($qcId)
    {
        $this->db->select('*');
        $this->db->from('tbl_qc_details');
        $this->db->where('qcId', $qcId);
      //  $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
   
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editQcdetails($qcdetailsInfo, $qcId)
    {
        $this->db->where('qcId', $qcId);
        $this->db->update('tbl_qc_details', $qcdetailsInfo);
        
        return TRUE;
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
}