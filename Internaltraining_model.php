<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Internal_model (Internal Training Model)
 * Internal Training model class to get to handle Internal Training related data 
 * @author : Ashish
 * @version : 1.1
 * @since : 09 May 2025
 */
class Internaltraining_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
   public function internaltrainingListingCount($searchText, $roleId, $userId)
{
    $this->db->select('*');
    $this->db->from('tbl_internaltraining as BaseTbl');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.internaltrainingTitle', $searchText);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    // 👇 Role-based access
    if ($roleId != 14 && $roleId != 1 && $roleId != 19 && $roleId != 21) {
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
 public function internaltrainingListing($searchText, $offset, $limit, $roleId, $userId)
{
    $this->db->select('
        BaseTbl.internaltrainingId,
        BaseTbl.internaltrainingTitle,
        BaseTbl.recordedsession,
        BaseTbl.description,
        BaseTbl.internaltrainingattachment,
        BaseTbl.createdDtm,
        BaseTbl.updatedBy,
        BaseTbl.updatedDtm,
        BaseTbl.createdBy,
        BaseTbl.internaltrainingStatus,
        Creator.name AS createdByName,
        Updater.name AS updatedByName
    ');
    $this->db->from('tbl_internaltraining as BaseTbl');
    $this->db->join('tbl_users as Creator', 'BaseTbl.createdBy = Creator.userId', 'left');
    $this->db->join('tbl_users as Updater', 'BaseTbl.updatedBy = Updater.userId', 'left');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.internaltrainingTitle', $searchText);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    // Role-based filtering
    if ($roleId != 14 && $roleId != 1 && $roleId != 19 && $roleId != 21) {
        $this->db->where('BaseTbl.createdBy', $userId);
    }

    $this->db->order_by('BaseTbl.internaltrainingId', 'DESC');
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}


    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewInternaltraining($internaltrainingInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_internaltraining', $internaltrainingInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getInternaltrainingInfo($internaltrainingId) 
    {
        $this->db->select('*');
        $this->db->from('tbl_internaltraining');
        $this->db->where('internaltrainingId', $internaltrainingId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editInternaltraining($internaltrainingInfo, $internaltrainingId)
    {
        $this->db->where('internaltrainingId', $internaltrainingId);
        $this->db->update('tbl_internaltraining', $internaltrainingInfo);
        
        return TRUE;
    }
   
public function addReply($data) {
    // $data should contain 'internaltrainingId', 'replyText', 'replyAttachment', 'createdBy', 'createdAt'
    $this->db->insert('tbl_internaltraining_replies', $data);
    return $this->db->insert_id();
}



public function getUserInfo($userId) {
        $this->db->select('userId, name');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        return $query->row();
    }
}