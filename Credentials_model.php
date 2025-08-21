<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Investors_model (Faq Model)
 * Faq model class to get to handle Faq related data 
 * @author : Ashish
 * @version : 1.1
 * @since : 11 Nov 2024
 */
class Credentials_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
 public function credentialsListingCount($searchText, $franchiseFilter = '', $userRole = null, $franchiseNumber = null, $userId = null)
{
    $this->db->select('*');
    $this->db->from('tbl_brcreddetails as BaseTbl');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.faqTitle', $searchText);
    }

    if (!empty($franchiseFilter)) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseFilter);
    }

    // Role-based data visibility
   if (!in_array($userRole, [1, 14, 22])) {
    if ($userRole == 15 && !empty($userId)) {
        $this->db->where('BaseTbl.brspFranchiseAssigned', $userId);
    } elseif (!empty($franchiseNumber)) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
    }
}

    $this->db->where('BaseTbl.isDeleted', 0);
    return $this->db->count_all_results();
}

public function credentialsListing($searchText, $franchiseFilter, $offset, $limit, $userRole = null, $franchiseNumber = null, $userId = null)
{
    $this->db->select('*');
    $this->db->from('tbl_brcreddetails as BaseTbl');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.faqTitle', $searchText);
    }

    if (!empty($franchiseFilter)) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseFilter);
    }

    // Role-based data visibility
 if (!in_array($userRole, [1, 14, 22])) {
    if ($userRole == 15 && !empty($userId)) {
        $this->db->where('BaseTbl.brspFranchiseAssigned', $userId);
    } elseif (!empty($franchiseNumber)) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
    }
}

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.credId', 'DESC');
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}
  

public function getAllBranches()
{
    $this->db->select('*');
    $this->db->from('tbl_branches');
    $this->db->where('isDeleted', 0);
      $this->db->where('currentStatus', 'Installed-Active');;
    $this->db->order_by('franchiseName', 'ASC');

    $query = $this->db->get();
    return $query->result();
}
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewcredentials($credentialsInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_brcreddetails', $credentialsInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getCredentialsInfo($credId)
    {
        $this->db->select('*');
        $this->db->from('tbl_brcreddetails');
        $this->db->where('credId', $credId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editCredentials($credentialsInfo, $credId)
    {
        $this->db->where('credId', $credId);
        $this->db->update('tbl_brcreddetails', $credentialsInfo);
        
        return TRUE;
    }
    public function getUserNameById($userId) {
        $this->db->select('name');
        $this->db->from('tbl_users'); // Ensure this is your users table
        $this->db->where('userId', $userId);
        $query = $this->db->get();
    
        if ($query->num_rows() > 0) {
            return $query->row()->name; // Return user name
        }
        return "N/A"; // Default if no user found
    }
}