<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Salesrecord_model (Salesrecord Model)
 * Salesrecord model class to get to handle Salesrecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Performancereport_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
 /* function performancereportListingCount($searchText, $userId, $userRole)
{
    $this->db->select('BaseTbl.performannceId');
    $this->db->from('tbl_performannce_sales as BaseTbl');
    $this->db->join('tbl_users as U', 'BaseTbl.userId = U.userId', 'left'); // Ensuring correct user mapping
        if (!in_array($userRole, [14, 28, 1])) { 
        //  Role 29: Only their own records
        if ($userRole == 29) {
            $this->db->where('BaseTbl.userId', $userId);
        }
        //  Role 31: Their own records + records of users with roleId 29
        elseif ($userRole == 31) {
            $this->db->where("(BaseTbl.userId = $userId OR U.roleId = 29)");
        }
        // Default: Only their own records
        else {
            $this->db->where('BaseTbl.userId', $userId);
        }
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    return $this->db->count_all_results();
}
*/
function performancereportListingCount($searchText, $userId, $userRole, $performerName = '', $monthFilter = '')
{
    $this->db->select('COUNT(*) as count');
    $this->db->from('tbl_performannce_sales as BaseTbl');
    $this->db->join('tbl_users as U', 'BaseTbl.userId = U.userId', 'left');

    // Subquery to get users assigned to the Team Lead (role 31)
    $subQuery = "(SELECT userId FROM tbl_performannce_sales WHERE teamLeadsales = $userId)";

    if (in_array($userRole, [14, 28, 1, 2])) {
        // Admin/Manager roles can view all records
    } elseif ($userRole == 31) {
        // Team Leader (role 31) can see their own data and assigned users
        $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.userId IN $subQuery)", NULL, FALSE);
    } elseif ($userRole == 29) {
        // Users (role 29) can only see their own data
        $this->db->where('BaseTbl.userId', $userId);
    }

    if (!empty($performerName)) {
        $this->db->where('BaseTbl.performerName', $performerName);
    }

    if (!empty($monthFilter)) {
        $this->db->where('BaseTbl.performannceMonths', $monthFilter);
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $query = $this->db->get();
    return $query->row()->count;
}



    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
	 
	function performancereportrecordListing($searchText, $limit, $start, $userId, $userRole, $performerName = '', $monthFilter = '')
{
    $this->db->select('BaseTbl.performannceId, BaseTbl.performannceMonths, BaseTbl.performerName, 
                        BaseTbl.performannceCount, BaseTbl.description');
    $this->db->from('tbl_performannce_sales as BaseTbl');
    $this->db->join('tbl_users as U', 'BaseTbl.userId = U.userId', 'left');

    // Subquery to get users assigned to the Team Lead (role 31)
    $subQuery = "(SELECT userId FROM tbl_performannce_sales WHERE teamLeadsales = $userId)";

    if (in_array($userRole, [14, 28, 1, 2])) {
        // Admin/Manager roles can view all records
    } elseif ($userRole == 31) {
        // Team Leader (role 31) can see their own data and assigned users
        $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.userId IN $subQuery)", NULL, FALSE);
    } elseif ($userRole == 29) {
        // Users (role 29) can only see their own data
        $this->db->where('BaseTbl.userId', $userId);
    }

    if (!empty($performerName)) {
        $this->db->where('BaseTbl.performerName', $performerName);
    }

    if (!empty($monthFilter)) {
        $this->db->where('BaseTbl.performannceMonths', $monthFilter);
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.performannceId', 'DESC');
    $this->db->limit($limit, $start);

    $query = $this->db->get();
    return $query->result();
}


 
	 
    function addNewPerformancereportrecord($performancereportrecordInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_performannce_sales', $performancereportrecordInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    
      function getPerformancereportrecordInfo($performannceId)
    {
    $this->db->select('performannceId,performerName,performannceMonths,performannceCount,description');
        $this->db->from('tbl_performannce_sales');
        $this->db->where('performannceId', $performannceId);
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
	
	public function editPerformancereportrecord($performancereportrecordInfo, $performannceId)
{
    $this->db->where('performannceId', $performannceId);
    $this->db->update('tbl_performannce_sales', $performancereportrecordInfo);
    
    // Print last executed query
    /*echo $this->db->last_query();
    exit;*/
     return TRUE;
    
}





   public function getAllUsers()
{
    $this->db->select('userId, name');  
    $this->db->from('tbl_users');
    $this->db->where_in('roleId', [29, 31]);  // Filter users with roleId = 29 or 31
    $query = $this->db->get();
    return $query->result();
}


 public function getPerformanceData($performerName = null)
{
    $this->db->select('*');
    $this->db->from('tbl_performannce_sales'); // Ensure table name is correct

    if (!empty($performerName)) {
        $this->db->where('performerName', $performerName);
    }

    $query = $this->db->get();
    return $query->result();
}

	 public function getPerformanceByMonth($month, $userId, $userRole)
    {
        $this->db->select('performannceId, performerName, performannceMonths, performannceCount');
        $this->db->from('tbl_performannce_sales');
        $this->db->where('performannceMonths', $month);

        // Role-based filtering
        if ($userRole == 29) { // Normal user
            $this->db->where('userId', $userId);
        } elseif ($userRole == 31) { // Team Leader sees their data and team
            $this->db->where("(userId = $userId OR roleId = 29)");
        } elseif ($userRole == 14) { // Admin sees everything
            // No additional filter needed
        } else {
            $this->db->where('userId', $userId);
        }

        $this->db->order_by('performannceId', 'DESC');
        return $this->db->get()->result();
    }

    public function calculateAveragePerformance($month, $userId, $userRole)
    {
        $this->db->select_avg('performannceCount', 'averageCount');
        $this->db->from('tbl_performannce_sales');
        $this->db->where('performannceMonths', $month);

        // Apply the same role-based filtering
        if ($userRole == 29) {
            $this->db->where('userId', $userId);
        } elseif ($userRole == 31) {
            $this->db->where("(userId = $userId OR roleId = 29)");
        }

        $query = $this->db->get();
        return $query->row()->averageCount ?? 0;
    }
    public function calculateTotalPerformance($month, $userId, $userRole)
{
    $this->db->select_sum('performannceCount', 'totalCount');
    $this->db->from('tbl_performannce_sales');
    $this->db->where('performannceMonths', $month);

    // Apply role-based filtering
    if ($userRole == 29) {
        $this->db->where('userId', $userId);
    } elseif ($userRole == 31) {
        $this->db->where("(userId = $userId OR roleId = 29)");
    }

    $query = $this->db->get();
    return $query->row()->totalCount ?? 0;
}
public function getUserPerformanceSummary($month, $userId, $userRole)
{
    $this->db->select('userId, performerName, performannceMonths, SUM(performannceCount) as totalPerformance, AVG(performannceCount) as averagePerformance');
    $this->db->from('tbl_performannce_sales');
    $this->db->where('performannceMonths', $month);
    $this->db->group_by('userId, performerName, performannceMonths');

    // Apply role-based filtering
    if ($userRole == 29) {
        $this->db->where('userId', $userId);
    } elseif ($userRole == 31) {
        $this->db->where("(userId = $userId OR roleId = 29)");
    }

    $query = $this->db->get();
    return $query->result();
}


}