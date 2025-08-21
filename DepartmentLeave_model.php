<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Announcement_model (Announcement Model)
 * Announcement model class to get to handle Announcement related data 
 * @author : Ashish Singh
 * @version : 1
 * @since : 24 Jul 2024
 */
class DepartmentLeave_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
function departmentLeaveListingCount($searchText)
{
    $this->db->select('*');
    $this->db->from('tbl_department_leave_settings as BaseTbl');

    // Search filter
    if (!empty($searchText)) {
        $this->db->like('BaseTbl.order_id', $searchText);
    }

    // Role and branch-based filtering
  

    return $this->db->count_all_results();
}

    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function departmentLeaveListing($searchText, $page, $segment)
{
    $this->db->select('*');
    $this->db->from('tbl_department_leave_settings as BaseTbl');

    // Search filter
    if (!empty($searchText)) {
        $this->db->like('BaseTbl.departleaveId', $searchText);
    }

    // Role and branch-based filtering
   

    $this->db->order_by('BaseTbl.departleaveId', 'DESC');
    $this->db->limit($page, $segment);
    $query = $this->db->get();

    return $query->result();
}

    


    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewDepartmentLeave($departmentLeaveInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_department_leave_settings', $departmentLeaveInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getDepartmentLeaveInfo($departleaveId)
    {
        $this->db->select('*');
        $this->db->from('tbl_department_leave_settings');
        $this->db->where('departleaveId', $departleaveId);
       
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editDepartmentLeave($departmentLeaveInfo, $departleaveId)
    {
        $this->db->where('departleaveId', $departleaveId);
        $this->db->update('tbl_department_leave_settings', $returnproductInfo);
        
        return TRUE;
    }

  

}