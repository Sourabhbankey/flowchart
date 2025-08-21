<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Investors_model (Faq Model)
 * Faq model class to get to handle Faq related data 
 * @author : Ashish
 * @version : 1.1
 * @since : 11 Nov 2024
 */
class Internaldesign_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
   public function internaldesignListingCount($searchText, $roleId, $userId)
{
    $this->db->select('*');
    $this->db->from('tbl_internaldesign as BaseTbl');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.internaldesignTitle', $searchText);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    // 👇 Role-based access
    if ($roleId != 14 && $roleId != 1 && $roleId != 19) {
        $this->db->where('BaseTbl.createdBy', $userId);
    }

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
 public function internaldesignListing($searchText, $offset, $limit, $roleId, $userId)
{
    $this->db->select('BaseTbl.internaldesignId, BaseTbl.internaldesignTitle, BaseTbl.description, BaseTbl.internaldesignattachment, BaseTbl.createdDtm, BaseTbl.createdBy,BaseTbl.designStatus, Users.name AS createdByName');
    $this->db->from('tbl_internaldesign as BaseTbl');
    $this->db->join('tbl_users as Users', 'BaseTbl.createdBy = Users.userId', 'left');  // Join to get the user name
 $this->db->order_by('BaseTbl.createdDtm', 'DESC');
    if (!empty($searchText)) {
        $this->db->like('BaseTbl.internaldesignTitle', $searchText);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    // Role-based filtering
    if ($roleId != 14 && $roleId != 1 && $roleId != 19) {
        $this->db->where('BaseTbl.createdBy', $userId);
    }

   
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}

    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewInternaldesign($internaldesignInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_internaldesign', $internaldesignInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getInternaldesignInfo($internaldesignId) 
    {
        $this->db->select('*');
        $this->db->from('tbl_internaldesign');
        $this->db->where('internaldesignId', $internaldesignId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editInternaldesign($internaldesignInfo, $internaldesignId)
    {
        $this->db->where('internaldesignId', $internaldesignId);
        $this->db->update('tbl_internaldesign', $internaldesignInfo);
        
        return TRUE;
    }
    /*public function getAllUsers()
{
    $this->db->select('userId, name'); // Add more fields if needed
    $this->db->from('tbl_users');
    $this->db->where('isDeleted', 0); // Optional, to exclude deleted users
    return $this->db->get()->result();
}*/
public function addReply($data) {
    // $data should contain 'internaldesignId', 'replyText', 'replyAttachment', 'createdBy', 'createdAt'
    $this->db->insert('tbl_internaldesign_replies', $data);
     $this->db->order_by('BaseTbl.createdDtm', 'DESC');
    return $this->db->insert_id();
}

/*public function getReplies($internaldesignId) {
    $this->db->select('*');
    $this->db->from('tbl_internaldesign_replies');
    $this->db->where('internaldesignId', $internaldesignId);
    $this->db->order_by('createdDtm', 'ASC'); // Chronological order
    $query = $this->db->get();
    return $query->result();
}*/
public function getReplies($internaldesignId) {
    $this->db->select('r.*, u.name AS username');
    $this->db->from('tbl_internaldesign_replies AS r');
    $this->db->join('tbl_users AS u', 'u.userId = r.createdBy', 'left');
    $this->db->where('r.internaldesignId', $internaldesignId);
    $this->db->order_by('r.createdDtm', 'DESC');
    $query = $this->db->get();
    return $query->result();
}

public function getUserInfo($userId) {
        $this->db->select('userId, name');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        return $query->row();
    }
}