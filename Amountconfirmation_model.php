<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Announcement_model (Announcement Model)
 * Announcement model class to get to handle Announcement related data 
 * @author : Ashish Singh
 * @version : 1
 * @since : 24 Jul 2024
 */
class Amountconfirmation_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
function amountconfirmationListingCount($searchText)
{
    $this->db->select('*');
    $this->db->from('tbl_amount_confirmation as BaseTbl');

    // Search filter
    if (!empty($searchText)) {
        $this->db->like('BaseTbl.order_id', $searchText);
    }

    // Role and branch-based filtering
    $roleId = $this->session->userdata('role');
    $franchiseNumber = $this->session->userdata('franchiseNumber');

    if ($roleId == 25) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
    }
    // roleId 1 and 14 can see all data — no filter applied

    return $this->db->count_all_results();
}

    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function amountconfirmationListing($searchText, $page, $segment)
{
    $this->db->select('*');
    $this->db->from('tbl_amount_confirmation as BaseTbl');

    // Search filter
    if (!empty($searchText)) {
        $this->db->like('BaseTbl.order_id', $searchText);
    }

    // Role and branch-based filtering
    $roleId = $this->session->userdata('role');
    $franchiseNumber = $this->session->userdata('franchiseNumber');

    if ($roleId == 25) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
    }

    $this->db->order_by('BaseTbl.amtconfId', 'DESC');
    $this->db->limit($page, $segment);
    $query = $this->db->get();

    return $query->result();
}

    


    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewAmountconfirmation($amountconfirmationInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_amount_confirmation', $amountconfirmationInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getAmountconfirmationInfo($amtconfId)
    {
        $this->db->select('*');
        $this->db->from('tbl_amount_confirmation');
        $this->db->where('amtconfId', $amtconfId);
       
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editAmountconfirmation($amountconfirmationInfo, $amtconfId)
    {
        $this->db->where('amtconfId', $amtconfId);
        $this->db->update('tbl_amount_confirmation', $amountconfirmationInfo);
        
        return TRUE;
    }

  

}