<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Salesrecord_model (Salesrecord Model)
 * Salesrecord model class to get to handle Salesrecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class socialmedia_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
public function socialmediaListingCount($searchText, $userId, $userRole, $searchUserId = '', $franchiseNumber = '') {
    $this->db->select('BaseTbl.*');
    $this->db->from('tbl_social_media as BaseTbl');
    $this->db->join('tbl_branches as B', 'B.franchiseNumber = BaseTbl.franchiseNumber', 'left');

    // 🔍 Search filter
    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.clientName', $searchText);
        $this->db->or_like('BaseTbl.emailid', $searchText);
        $this->db->or_like('BaseTbl.contactno', $searchText);
        $this->db->group_end();
    }

    // 🔍 Optional user filter
    if (!empty($searchUserId)) {
        $this->db->where('BaseTbl.userId', $searchUserId);
    }

    // 🔐 Role-based access
    if (!in_array($userRole, [1, 14, 33])) {
        if (!empty($franchiseNumber)) {
            $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
        }
    }

    // ✅ Only from installed-active branches
    $this->db->where('B.currentStatus', 'Installed-Active');

    $this->db->where('BaseTbl.isDeleted', 0);
    return $this->db->count_all_results();
}


public function socialmediarecordListing($searchText, $page, $segment, $userId, $userRole, $searchUserId, $franchiseNumber) {
    $this->db->select('BaseTbl.*');
    $this->db->from('tbl_social_media as BaseTbl');
    $this->db->join('tbl_branches as B', 'B.franchiseNumber = BaseTbl.franchiseNumber', 'left');

    // 🔍 Search
    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.fbpageName', $searchText);
        $this->db->or_like('BaseTbl.instapageName', $searchText);
        $this->db->group_end();
    }

    // 🔍 Optional user filter
    if (!empty($searchUserId)) {
        $this->db->where('BaseTbl.userId', $searchUserId);
    }

    // 🔐 Role-based access
    if (!in_array($userRole, [1, 14, 33])) {
        if (!empty($franchiseNumber)) {
            $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
        }
    }

    // ✅ Only from installed-active branches
    $this->db->where('B.currentStatus', 'Installed-Active');

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->limit($page, $segment);

    $query = $this->db->get();
    return $query->result();
}




    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
	 
	 
	 
    function addNewsocialmediarecord($socialmediarecordInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_social_media', $socialmediarecordInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    
      function getsocialmediarecordInfo($socialId)
    {
    $this->db->select('*');
        $this->db->from('tbl_social_media');
        $this->db->where('socialId', $socialId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
   /* function editfollowuprecord($FollowuprecordInfo, $followupId)
    {
        $this->db->where('followupId', $followupId);
        $this->db->update('tbl_followup_sales', $FollowuprecordInfo);
        
        return TRUE;
    }*/
	
	public function editsocialmediarecord($socialmediarecordInfo, $socialId)
{
    $this->db->where('socialId', $socialId);
    $this->db->update('tbl_social_media', $socialmediarecordInfo);
    
    // Print last executed query
    /*echo $this->db->last_query();
    exit;*/
     return TRUE;
    
}
public function getAllUsers()
{
    $this->db->select('userId, name');
    $this->db->from('tbl_users');
    $this->db->where('isDeleted', 0);
    $query = $this->db->get();
    return $query->result();
}


public function getUsersByFranchise($franchiseNumber) {
    $this->db->select('userId, name');
    $this->db->from('tbl_users');
    $this->db->where('franchiseNumber', $franchiseNumber);
    $query = $this->db->get();
    
    // Debugging: Print query and data
    error_log("SQL Query: " . $this->db->last_query());
    return $query->result();
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

	public function getFranchiseNumber($userId) {
    $this->db->select('franchiseNumber');
    $this->db->from('tbl_users'); // Assuming franchiseNumber is in tbl_users
    $this->db->where('userId', $userId);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->row()->franchiseNumber; // Return the franchise number
    } else {
        return null; 
    }
}


}