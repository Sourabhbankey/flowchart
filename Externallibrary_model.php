<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Internal_model (Internal Training Model)
 * Internal Training model class to get to handle Internal Training related data 
 * @author : Ashish
 * @version : 1.1
 * @since : 09 May 2025
 */
class Externallibrary_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
  public function externallibraryListingCount($searchText, $roleId, $userId)
{
    $this->db->select('*');
    $this->db->from('tbl_externalBrLibrary as BaseTbl');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.exbrLibraryTitle', $searchText);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    // Role-based access for normal users
    // For role 25, show all Active records without filtering by createdBy
if ($roleId != 14 && $roleId != 1 && $roleId != 19 && $roleId != 21 && $roleId != 25) {
    $this->db->where('BaseTbl.createdBy', $userId);
}

// For role 25, filter by Active status
if ($roleId == 25) {
    $this->db->where('BaseTbl.exbrLibraryStatus', 'Active');  // case-sensitive check!
}

    $query = $this->db->get();
    return $query->num_rows();
}

public function externallibraryListing($searchText, $offset, $limit, $roleId, $userId)
{
    $this->db->select('
        BaseTbl.exbrLibraryId,
        BaseTbl.exbrLibraryTitle,
        BaseTbl.exbrLibraryLink,
        BaseTbl.exbrLibraryPDFS3attachment,
        BaseTbl.exbrLibraryStatus,
        BaseTbl.description,
        BaseTbl.updatedBy,
        BaseTbl.updatedDtm,
        BaseTbl.createdDtm,
        Creator.name AS createdByName,
        Updater.name AS updatedByName
    ');
    $this->db->from('tbl_externalBrLibrary as BaseTbl');
    $this->db->join('tbl_users as Creator', 'BaseTbl.createdBy = Creator.userId', 'left');
    $this->db->join('tbl_users as Updater', 'BaseTbl.updatedBy = Updater.userId', 'left');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.exbrLibraryTitle', $searchText);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    // Role-based filtering for normal users
    // For role 25, show all Active records without filtering by createdBy
if ($roleId != 14 && $roleId != 1 && $roleId != 19 && $roleId != 21 && $roleId != 25) {
    $this->db->where('BaseTbl.createdBy', $userId);
}

// For role 25, filter by Active status
if ($roleId == 25) {
    $this->db->where('BaseTbl.exbrLibraryStatus', 'Active');  // case-sensitive check!
}

    $this->db->order_by('BaseTbl.exbrLibraryId', 'DESC');
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}


    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewExternallibrary($externallibraryInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_externalBrLibrary', $externallibraryInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getExternallibraryInfo($exbrLibraryId) 
    {
        $this->db->select('*');
        $this->db->from('tbl_externalBrLibrary');
        $this->db->where('exbrLibraryId', $exbrLibraryId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editExternallibrary($externallibraryInfo, $exbrLibraryId)
    {
        $this->db->where('exbrLibraryId', $exbrLibraryId);
        $this->db->update('tbl_externalBrLibrary', $externallibraryInfo);
        
        return TRUE;
    }
   

public function getUserInfo($userId) {
        $this->db->select('userId, name');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        return $query->row();
    }
}