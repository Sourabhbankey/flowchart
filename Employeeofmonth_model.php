<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Employeeofmonth_model (Employeeofmonth Model)
 * Employeeofmonth model class to get to handle Employeeofmonth related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 12 May 2025
 */
class Employeeofmonth_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
  function employeeofmonthListingCount($searchText, $userRole, $userId)
{
    $userRole = $this->session->userdata('role');
       $userId = $this->session->userdata('userId');

    $this->db->select('*');
    $this->db->from('tbl_empofmonths as BaseTbl');

    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.empName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    /*// Apply filter for non-admin roles
    if ($userRole != 1 && $userRole != 14) {
        $this->db->where('BaseTbl.assignedTo', $userId);
    }*/

    return $this->db->count_all_results();
}

   
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
   function employeeofmonthListing($searchText, $page, $segment, $userRole, $userId)
{ 
    $userRole = $this->session->userdata('role');
    $userId = $this->session->userdata('userId');

    $this->db->select('BaseTbl.*, Users.name as assignedUserName');
    $this->db->from('tbl_empofmonths as BaseTbl');
    $this->db->join('tbl_users as Users', 'Users.userId = BaseTbl.assignedTo', 'left');

    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.empName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

   /* if ($userRole != 1 && $userRole != 14) {
        $this->db->where('BaseTbl.assignedTo', $userId);
    }*/

    $this->db->order_by('BaseTbl.empofmonthsId', 'DESC');
    $this->db->limit($page, $segment);

    $query = $this->db->get();
    return $query->result();
}


    

    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewEmployeeofmonth($employeeofmonthInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_empofmonths', $employeeofmonthInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getEmployeeofmonthInfo($empofmonthsId)
    {
        $this->db->select('*');
        $this->db->from('tbl_empofmonths');
        $this->db->where('empofmonthsId', $empofmonthsId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editEmployeeofmonth($employeeofmonthInfo, $empofmonthsId)
    {
        $this->db->where('empofmonthsId', $empofmonthsId);
        $this->db->update('tbl_empofmonths', $employeeofmonthInfo);
        
        return TRUE;
    }
}