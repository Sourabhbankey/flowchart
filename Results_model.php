<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Salesrecord_model (Salesrecord Model)
 * Salesrecord model class to get to handle Salesrecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Results_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
 function resultsListingCount($searchText, $userRole, $userId, $fromDate = '', $toDate = '', $userFilter = '')
{
    $this->db->select('COUNT(*) as count');
    $this->db->from('tbl_results_sales as BaseTbl');
    $this->db->join('tbl_users as UserTbl', 'BaseTbl.userId = UserTbl.userId', 'left');

    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.clientname', $searchText);
        $this->db->or_like('BaseTbl.contactno', $searchText);
        $this->db->or_like('BaseTbl.emailid', $searchText);
        $this->db->group_end();
    }

    if (!empty($fromDate) && !empty($toDate)) {
        $this->db->where("DATE(BaseTbl.issuedon) >=", date('Y-m-d', strtotime($fromDate)));
        $this->db->where("DATE(BaseTbl.issuedon) <=", date('Y-m-d', strtotime($toDate)));
    }

    if (!empty($userFilter)) {
        $this->db->where('BaseTbl.userId', $userFilter);
    } else {
        if (in_array($userRole, [28,14,2,1])) {
            // Role 14 can see all records (no filtering needed)
        } elseif ($userRole == 31) {
            // Team Leader (31) can see their own records and assigned users
            $this->db->where("(BaseTbl.userId = $userId OR UserTbl.teamLeadsales = $userId)");
        } elseif ($userRole == 29) {
            // Users (29) can only see their own data
            $this->db->where('BaseTbl.userId', $userId);
        } else {
            // Default case (if any)
            $this->db->where('BaseTbl.userId', $userId);
        }
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    $query = $this->db->get();
    return $query->row()->count;
}

function resultsListing($searchText, $offset, $limit, $userRole, $userId, $fromDate = '', $toDate = '', $userFilter = '')
{
    $this->db->select('BaseTbl.resultsId, BaseTbl.issuedon, BaseTbl.firstcall, BaseTbl.clientname, 
                       BaseTbl.contactno, BaseTbl.altercontactno, BaseTbl.emailid, BaseTbl.city, 
                       BaseTbl.location, BaseTbl.lastcall, BaseTbl.nextfollowup, 
                       BaseTbl.status, BaseTbl.description, BaseTbl.incentivereceived, BaseTbl.description2, 
                       BaseTbl.is_edited, UserTbl.name as userName');
    $this->db->from('tbl_results_sales as BaseTbl');
    $this->db->join('tbl_users as UserTbl', 'BaseTbl.userId = UserTbl.userId', 'left');

    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.clientname', $searchText);
        $this->db->or_like('BaseTbl.contactno', $searchText);
        $this->db->or_like('BaseTbl.emailid', $searchText);
        $this->db->group_end();
    }

    if (!empty($fromDate) && !empty($toDate)) {
        $this->db->where("DATE(BaseTbl.issuedon) >=", date('Y-m-d', strtotime($fromDate)));
        $this->db->where("DATE(BaseTbl.issuedon) <=", date('Y-m-d', strtotime($toDate)));
    }

    if (!empty($userFilter)) {
        $this->db->where('BaseTbl.userId', $userFilter);
    } else {
       if (in_array($userRole, [28,14,2,1])) {
            // Role 14 can see all records (no filtering needed)
        } elseif ($userRole == 31) {
            // Team Leader (31) can see their own records and assigned users
            $this->db->where("(BaseTbl.userId = $userId OR UserTbl.teamLeadsales = $userId)");
        } elseif ($userRole == 29) {
            // Users (29) can only see their own data
            $this->db->where('BaseTbl.userId', $userId);
        } else {
            // Default case (if any)
            $this->db->where('BaseTbl.userId', $userId);
        }
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.resultsId', 'DESC');
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}

    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
	 
	 
	 
    function addNewResultsrecord($resultsrecordInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_results_sales', $resultsrecordInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    
      function getResultsrecordInfo($resultsId)
    {
        $this->db->select('resultsId,issuedon ,firstcall,clientname,contactno,altercontactno,emailid,city,location,lastcall,nextfollowup,status,description,description2,finalfranchisecost,amountreceived,initialkitsoffered,premisestatus,expectedinstallationdate,additionaloffer,is_edited,incentivereceived,incentivereceivedSTL,offername');
        $this->db->from('tbl_results_sales');
        $this->db->where('resultsId', $resultsId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    function getResultsincentiverecordInfo($resultsId)
    {
        $this->db->select('resultsId,incentivereceived,incentivereceivedSTL,userId,offername');
        $this->db->from('tbl_incentive');
        $this->db->where('resultsId', $resultsId);
       // $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editResultsrecord($resultsrecordInfo, $resultsId)
    {
        $this->db->where('resultsId', $resultsId);
        $this->db->update('tbl_results_sales', $resultsrecordInfo);
        
        return TRUE;
    }
	
	
   public function getUserIdByResultsId($resultsId) {
    $this->db->select('userId');
    $this->db->from('tbl_results_sales');
    $this->db->where('resultsId', $resultsId);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->row()->userId;
    }
    return null;
}
public function addIncentiveRecord($data) {
    $this->db->insert('tbl_incentive', $data);
    return $this->db->insert_id();
}


public function updateIncentiveRecord($data, $resultsId)
{
    $this->db->where('resultsId', $resultsId);
    return $this->db->update('tbl_incentive', $data);
} 
	
public function getIncentiveByResultsId($resultsId)  
{  
    $this->db->select('tbl_users.name AS userName, tbl_incentive.userId, SUM(tbl_incentive.incentivereceived) AS totalIncentive');  
    $this->db->from('tbl_incentive');  
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_incentive.userId', 'left');  
    $this->db->where('tbl_incentive.resultsId', $resultsId);  
    $this->db->group_by('tbl_incentive.userId'); // ✅ Group by userId to sum incentives  
    $query = $this->db->get();  

    return $query->result_array(); // Return as an array  
}

public function getTotalIncentives()
{
    $this->db->select('tbl_users.name AS userName, tbl_incentive.userId, SUM(CAST(tbl_incentive.incentivereceived AS DECIMAL(10,2))) AS totalIncentive');
    $this->db->from('tbl_incentive');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_incentive.userId', 'left');
    $this->db->group_by('tbl_incentive.userId'); // ✅ Group by userId to sum across all resultsId
    $query = $this->db->get();

    return $query->result_array(); // ✅ Return array of users with their total incentive
}
public function getTotalIncentivesByUser($userId)
{
    $this->db->select('tbl_users.name AS userName, tbl_incentive.userId, SUM(tbl_incentive.incentivereceived) AS totalIncentive');
    $this->db->from('tbl_incentive');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_incentive.userId', 'left');
    $this->db->where('tbl_incentive.userId', $userId); // ✅ Show only logged-in user's incentives
    $this->db->group_by('tbl_incentive.userId'); 

    $query = $this->db->get();
    return $query->result_array(); 
}
/*public function getAllMonthlyIncentives()
{
    $this->db->select("tbl_users.name, 
                       SUM(tbl_incentive.incentivereceived) as total_incentive, 
                       DATE_FORMAT(tbl_incentive.created_at, '%Y-%m') as incentive_month");
    $this->db->from("tbl_incentive");
    $this->db->join("tbl_users", "tbl_users.userId = tbl_incentive.userId", "left");
    $this->db->group_by("tbl_users.name, incentive_month");
    $this->db->order_by("incentive_month", "DESC");

    $query = $this->db->get();
    return $query->result_array();
}


*/

public function getMonthlyIncentives()
{
    $this->db->select("tbl_incentive.userId, tbl_users.name, SUM(tbl_incentive.incentivereceived) as total_incentive, DATE_FORMAT(tbl_incentive.created_at, '%Y-%m') as incentive_month");
    $this->db->from("tbl_incentive");
    $this->db->join("tbl_users", "tbl_users.userId = tbl_incentive.userId", "left"); // ✅ Join users table
    $this->db->group_by("tbl_incentive.userId, incentive_month");
    $this->db->order_by("incentive_month", "DESC");

    $query = $this->db->get();
    return $query->result_array();
}
/*public function getAllMonthlyIncentives($month = null, $userId = null)
{
    $this->db->select("
        tbl_users.userId, 
        tbl_users.name AS userName, 
        tl.name AS team_lead_name, 
        DATE_FORMAT(tbl_incentive.created_at, '%Y-%m') AS incentive_month,
        SUM(tbl_incentive.incentivereceived) AS total_incentive
    ");
    $this->db->from("tbl_incentive");
    $this->db->join("tbl_users", "tbl_users.userId = tbl_incentive.userId", "left");
    $this->db->join("tbl_clients_sales", "tbl_clients_sales.userId = tbl_incentive.userId", "left");
    $this->db->join("tbl_users AS tl", "tbl_clients_sales.teamLeadsales = tl.userId", "left");

    if (!empty($month)) {
        $this->db->where("DATE_FORMAT(tbl_incentive.created_at, '%Y-%m') =", $month);
    }

    if (!empty($userId)) {
        $this->db->where("tbl_incentive.userId", $userId);
    }

    // Remove `clientname` from GROUP BY to ensure correct aggregation
    $this->db->group_by("tbl_users.userId, tbl_users.name, tl.name, incentive_month");
    $this->db->order_by("incentive_month", "DESC");

    $query = $this->db->get();
    return $query->result_array();
}

*/
public function getAllMonthlyIncentives($month = null, $userIds = [])
{
    $this->db->select("
        tbl_users.userId, 
        tbl_users.name AS userName, 
        tl.name AS team_lead_name, 
        DATE_FORMAT(tbl_incentive.created_at, '%Y-%m') AS incentive_month,
        SUM(tbl_incentive.incentivereceived) AS total_incentive_sales,
        SUM(tbl_incentive.incentivereceivedSTL) AS total_incentive_tl
    ");
    $this->db->from("tbl_incentive");
    $this->db->join("tbl_users", "tbl_users.userId = tbl_incentive.userId", "left");
    $this->db->join("tbl_clients_sales", "tbl_clients_sales.userId = tbl_incentive.userId", "left");
    $this->db->join("tbl_users AS tl", "tbl_clients_sales.teamLeadsales = tl.userId", "left");

    // Filter by month if provided
    if (!empty($month)) {
        $this->db->where("DATE_FORMAT(tbl_incentive.created_at, '%Y-%m') =", $month);
    }

    // Filter by user IDs (supports multiple users for Role 31)
    if (!empty($userIds)) {
        $this->db->where_in("tbl_incentive.userId", $userIds);
    }

    // Group by user, team lead, and month
    $this->db->group_by("tbl_users.userId, tbl_users.name, tl.name, incentive_month");
    $this->db->order_by("incentive_month", "DESC");

    $query = $this->db->get();
    return $query->result_array();
}

public function getAllUsers()
{
    $this->db->select("userId, name");
    $this->db->from("tbl_users");
    $this->db->where_in("roleId", [29, 31]); // Fetch only users with roleId 29 or 31
    $this->db->order_by("name", "ASC"); // Sort users alphabetically

    $query = $this->db->get();
    return $query->result_array();
}
public function getAssignedUsers($teamLeaderId)
{
    $this->db->select("userId");
    $this->db->from("tbl_users");
    $this->db->where("roleId", 29); // Role 29 wale users
    $this->db->where("teamLeadsales", $teamLeaderId); // Jo is Team Leader ke assigned hain
    $query = $this->db->get();
    return $query->result_array();
}

}