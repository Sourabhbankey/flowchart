<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Brholiday_model (Brholiday Model)
 * Brholiday model class to get to handle Brholiday related data 
 * @author : Ashish Singh
 * @version : 1
 * @since : 20 Mar 2025
 */
class Brholiday_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function brholidayListingCount($searchText)
    {
        $this->db->select('BaseTbl.holidayId, BaseTbl.holidayTitle, BaseTbl.holidayDay, BaseTbl.holidayFromdate, BaseTbl.holidayTodate, BaseTbl.description,BaseTbl.createdBy, BaseTbl.createdDtm');
        $this->db->from('tbl_holidaylist as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.holidayTitle LIKE '%".$searchText."%')";
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
    function brholidayListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.holidayId, BaseTbl.holidayTitle, BaseTbl.holidayDay, BaseTbl.holidayFromdate, BaseTbl.holidayTodate, BaseTbl.description, BaseTbl.createdBy, BaseTbl.createdDtm');
        $this->db->from('tbl_holidaylist as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.holidayTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.holidayId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewBrholiday($brholidayInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_holidaylist', $brholidayInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getBrholidayInfo($holidayId)
    {
        $this->db->select('holidayId, holidayTitle, holidayDay, holidayFromdate, holidayTodate, description');
        $this->db->from('tbl_holidaylist');
        $this->db->where('holidayId', $holidayId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editBrholiday($brholidayInfo, $holidayId)
    {
        $this->db->where('holidayId', $holidayId);
        $this->db->update('tbl_holidaylist', $brholidayInfo);
        
        return TRUE;
    }

    /*public function get_announcements_with_user_name() {
    $this->db->select('a.*, u.name as createdByName');
    $this->db->from('tbl_holidaylist a');
    $this->db->join('tbl_users u', 'a.createdBy = u.userId', 'left');  // Join with the users table
    $query = $this->db->get();
    return $query->result();
}*/
}