<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Coupons_model (Coupons Model)
 * Coupons model class to get to handle Coupons related data 
 * @author : Ashish
 * @version : 1.1
 * @since : 15 Mar 2025
 */
class Coupons_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
  public function couponsListingCount($searchText = '', $franchiseFilter = '', $userRole = null, $franchiseNumber = null, $userId = null)


    {
        $this->db->from('tbl_brcoupons as BaseTbl');

        if (!empty($searchText)) {
            $this->db->like('BaseTbl.couponsTitle', $searchText);
        }

        if (!empty($franchiseFilter)) {
            $this->db->where('BaseTbl.franchiseNumber', $franchiseFilter);
        }

        // Restrict to user's franchise unless admin (1) or superuser (14)
        if (!in_array($userRole, [1, 14, 22])) {
    if ($userRole == 15 && !empty($userId)) {
        $this->db->where('BaseTbl.brspFranchiseAssigned', $userId);
    } elseif (!empty($franchiseNumber)) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
    }
}

        $this->db->where('BaseTbl.isDeleted', 0);

        return $this->db->count_all_results(); // Efficient way to get row count
    }

    /**
     * Get paginated coupons based on filters
     * 
     * @param string $searchText Optional search keyword
     * @param string $franchiseFilter Optional franchise filter
     * @param int $offset Pagination offset
     * @param int $limit Pagination limit
     * @param int|null $userRole User's role ID
     * @param int|null $franchiseNumber User's franchise number
     * @return array List of filtered coupons
     */
  public function couponsListing($searchText = '', $franchiseFilter = '', $offset = 0, $limit = 10, $userRole = null, $franchiseNumber = null, $userId = null)
{
    $this->db->select('*');
    $this->db->from('tbl_brcoupons as BaseTbl');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.couponsTitle', $searchText);
    }

    if (!empty($franchiseFilter)) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseFilter);
    }

    if (!in_array($userRole, [1, 14, 22])) {
        if ($userRole == 15 && !empty($userId)) {
            $this->db->where('BaseTbl.brspFranchiseAssigned', $userId);
        } elseif (!empty($franchiseNumber)) {
            $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
        }
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.coupnsId', 'DESC');
    $this->db->limit($limit, $offset);

    return $this->db->get()->result();
}


public function getFranchiseNumberByCouponsId($coupnsId)
{
    $this->db->select('franchiseNumber');
    $this->db->from('tbl_brcoupons'); // Adjust table name if different
    $this->db->where('coupnsId', $coupnsId); // Adjust column name if different
    $query = $this->db->get();
    return $query->row() ? $query->row()->franchiseNumber : null;
}

    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewcoupons($couponsInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_brcoupons', $couponsInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getCouponsInfo($coupnsId)
    {
        $this->db->select('coupnsId,franchiseNumber,franchiseName,couponsTitle,couponsType,couponsCode,couponsAmount,couponsUses,couponsLimit,couponsEdate,couponsAssignedby,brspFranchiseAssigned, description');
        $this->db->from('tbl_brcoupons');
        $this->db->where('coupnsId', $coupnsId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editCoupons($couponsInfo, $coupnsId)
    {
        $this->db->where('coupnsId', $coupnsId);
        $this->db->update('tbl_brcoupons', $couponsInfo);
        
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