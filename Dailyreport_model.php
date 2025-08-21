<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Salesrecord_model (Salesrecord Model)
 * Salesrecord model class to get to handle Salesrecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Dailyreport_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
  function dailyreportListingCount($searchText, $userId, $userRole, $searchUserId = '', $fromDate = '', $toDate = '')
{
    $this->db->select('COUNT(*) as count');
    $this->db->from('tbl_dailyreport_sales as BaseTbl');
    $this->db->join('tbl_users as U', 'BaseTbl.userId = U.userId', 'left');

    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.clientName', $searchText);
        $this->db->or_like('BaseTbl.emailid', $searchText);
        $this->db->or_like('BaseTbl.contactno', $searchText);
        $this->db->group_end();
    }

    if (!empty($searchUserId)) {
        $this->db->where('BaseTbl.userId', $searchUserId);
    }

    if (!empty($fromDate)) {
        $this->db->where('BaseTbl.date >=', $fromDate);
    }

    if (!empty($toDate)) {
        $this->db->where('BaseTbl.date <=', $toDate);
    }

    // 🔹 Role-Based Filtering (Using Team Leader's Assigned Users)
    $subQuery = "(SELECT userId FROM tbl_users WHERE teamLeadsales = $userId)";

if (in_array($userRole, [28, 14, 2, 1])) {
    // Admin/Manager roles can view all records
} elseif ($userRole == 31) {  
    // Team Leader (role 31) can see their own data and assigned users' data
    $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.userId IN $subQuery)", NULL, FALSE);
} elseif ($userRole == 29) {
    // Users (role 29) can only see their own data
    $this->db->where('BaseTbl.userId', $userId);
}
    $this->db->where('BaseTbl.isDeleted', 0);
    
    $query = $this->db->get();
    return $query->row()->count;
}


function dailyreportrecordListing($searchText, $page, $segment, $userId, $userRole, $searchUserId = '', $fromDate = '', $toDate = '')
{
    $this->db->select('BaseTbl.dailyreportId, BaseTbl.date, BaseTbl.nooffreshcalls, 
                       BaseTbl.nooftotalconnectedcalls, BaseTbl.noofoldfollowups, 
                       BaseTbl.noofrecordingshared, BaseTbl.prospects, BaseTbl.converted, 
                       BaseTbl.talktime, BaseTbl.description,BaseTbl.virtualmeetings, U.name as userName');
    $this->db->from('tbl_dailyreport_sales as BaseTbl');
    $this->db->join('tbl_users as U', 'BaseTbl.userId = U.userId', 'left');

    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.clientName', $searchText);
        $this->db->or_like('BaseTbl.emailid', $searchText);
        $this->db->or_like('BaseTbl.contactno', $searchText);
        $this->db->group_end();
    }

    if (!empty($searchUserId)) {
        $this->db->where('BaseTbl.userId', $searchUserId);
    }

    if (!empty($fromDate)) {
        $this->db->where('BaseTbl.date >=', $fromDate);
    }

    if (!empty($toDate)) {
        $this->db->where('BaseTbl.date <=', $toDate);
    }

    // 🔹 Role-Based Filtering (Using Team Leader's Assigned Users)
    $subQuery = "(SELECT userId FROM tbl_users WHERE teamLeadsales = $userId)";

if (in_array($userRole, [28, 14, 2, 1])) {
    // Admin/Manager roles can view all records
} elseif ($userRole == 31) {  
    // Team Leader (role 31) can see their own data and assigned users' data
    $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.userId IN $subQuery)", NULL, FALSE);
} elseif ($userRole == 29) {
    // Users (role 29) can only see their own data
    $this->db->where('BaseTbl.userId', $userId);
}

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.dailyreportId', 'DESC');
    $this->db->limit($page, $segment);
    
    $query = $this->db->get();
    return $query->result();
}


    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
	 
	 
	 
    function addNewdailyreportrecord($dailyreportrecordInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_dailyreport_sales', $dailyreportrecordInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    
      function getdailyreportrecordInfo($dailyreportId)
    {
    $this->db->select('*');
        $this->db->from('tbl_dailyreport_sales');
        $this->db->where('dailyreportId', $dailyreportId);
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
	
	public function editdailyreportrecord($dailyreportrecordInfo, $dailyreportId)
{
    $this->db->where('dailyreportId', $dailyreportId);
    $this->db->update('tbl_dailyreport_sales', $dailyreportrecordInfo);
    
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
    $this->db->where_in('roleId', [34, 31, 28, 29]); 
    $query = $this->db->get();
    return $query->result();
}



public function getTalkTimeStats($month, $year)
{
    $this->db->select('u.name, SUM(d.talktime) as total_talktime, AVG(d.talktime) as avg_talktime');
    $this->db->from('tbl_dailyreport_sales d');
    $this->db->join('tbl_users u', 'u.userId = d.userId', 'inner');
    $this->db->where('u.isDeleted', 0);
    $this->db->where('MONTH(d.date)', $month); // Filter by month
    $this->db->where('YEAR(d.date)', $year); // Filter by year
    $this->db->group_by('d.userId');
    $this->db->order_by('total_talktime', 'DESC'); // Order by highest talk time

    $query = $this->db->get();
    return $query->result();
}


   


	

}