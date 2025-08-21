<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Announcement_model (Announcement Model)
 * Announcement model class to get to handle Announcement related data 
 * @author : Ashish Singh
 * @version : 1
 * @since : 24 Jul 2024
 */
class Announcement_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function announcementListingCount($searchText)
    {
        $this->db->select('BaseTbl.announcementId, BaseTbl.announcementName,BaseTbl.annattachmentS3File, BaseTbl.description,BaseTbl.createdBy, BaseTbl.createdDtm');
        $this->db->from('tbl_announcement as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.announcementName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
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
    function announcementListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.announcementId, BaseTbl.announcementName,BaseTbl.annattachmentS3File, BaseTbl.description,BaseTbl.createdBy, BaseTbl.createdDtm');
        $this->db->from('tbl_announcement as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.announcementName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.announcementId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewAnnouncement($announcementInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_announcement', $announcementInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getAnnouncementInfo($announcementId)
    {
        $this->db->select('announcementId, announcementName, description');
        $this->db->from('tbl_announcement');
        $this->db->where('announcementId', $announcementId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editAnnouncement($announcementInfo, $announcementId)
    {
        $this->db->where('announcementId', $announcementId);
        $this->db->update('tbl_announcement', $announcementInfo);
        
        return TRUE;
    }

    public function get_announcements_with_user_name() {
    $this->db->select('a.*, u.name as createdByName');
    $this->db->from('tbl_announcement a');
    $this->db->join('tbl_users u', 'a.createdBy = u.userId', 'left');  // Join with the users table
    $query = $this->db->get();
    return $query->result();
}
}