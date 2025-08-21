<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Salesrecord_model (Salesrecord Model)
 * Salesrecord model class to get to handle Salesrecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Followup_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
function followupListingCount($searchText, $fromDate, $toDate, $userRole, $userId, $userFilter = null)
{
    $this->db->select('COUNT(*) as count');
    $this->db->from('tbl_followup_sales as BaseTbl');
    $this->db->join('tbl_users as UserTbl', 'BaseTbl.userId = UserTbl.userId', 'left');

    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.clientname', $searchText);
        $this->db->or_like('BaseTbl.emailid', $searchText);
        $this->db->or_like('BaseTbl.contactno', $searchText);
        $this->db->group_end();
    }

    if (!empty($fromDate) && !empty($toDate)) {
        $this->db->where('BaseTbl.issuedon >=', $fromDate);
        $this->db->where('BaseTbl.issuedon <=', $toDate);
    }

    if (!empty($userFilter)) {
        $this->db->where('BaseTbl.userId', $userFilter);
    }

    // Fetch users assigned to this Team Lead (role 31)
    $subQuery = "(SELECT userId FROM tbl_followup_sales WHERE teamLeadsales = $userId)";

    if (in_array($userRole, [1, 2, 28, 14])) {
        // Role 1 & 2 (Admins & Managers) see all records
    } elseif ($userRole == 31) {  
        // Team Leader (role 31) sees own and assigned users' data
        $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.userId IN $subQuery OR BaseTbl.salesTeamassign = $userId)", NULL, FALSE);
    } elseif ($userRole == 29) {
        // Sales Users (role 29) see only their own and assigned records
        $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.salesTeamassign = $userId)", NULL, FALSE);
    }

    $this->db->where('BaseTbl.status !=', 'converted leads');
    $this->db->where('BaseTbl.isDeleted', 0);

    $query = $this->db->get();
    return $query->row()->count;
}

function followuprecordListing($searchText, $limit, $offset, $userRole, $userId, $fromDate = '', $toDate = '', $userFilter = null)
{
    $this->db->select('BaseTbl.followupId, BaseTbl.issuedon, BaseTbl.firstcall, BaseTbl.clientname, 
                       BaseTbl.contactno, BaseTbl.altercontactno, BaseTbl.emailid, BaseTbl.city, 
                       BaseTbl.location, BaseTbl.lastcall, BaseTbl.nextfollowup, 
                       BaseTbl.status, BaseTbl.description, BaseTbl.description2, 
                       UserTbl.name as userName, BaseTbl.salesTeamassign,BaseTbl.finalfranchisecost,BaseTbl.agreementtenure,BaseTbl.amountreceived,BaseTbl.initialkitsoffered,BaseTbl.duedatefinalpayment,BaseTbl.premisestatus,BaseTbl.expectedinstallationdate,BaseTbl.additionaloffer');
    $this->db->from('tbl_followup_sales as BaseTbl');
    $this->db->join('tbl_users as UserTbl', 'BaseTbl.userId = UserTbl.userId', 'left');

    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.clientname', $searchText);
        $this->db->or_like('BaseTbl.emailid', $searchText);
        $this->db->or_like('BaseTbl.contactno', $searchText);
        $this->db->group_end();
    }

    if (!empty($fromDate) && !empty($toDate)) {
        $this->db->where('BaseTbl.nextfollowup >=', $fromDate);
        $this->db->where('BaseTbl.nextfollowup <=', $toDate);
    }

    if (!empty($userFilter)) {
        $this->db->where('BaseTbl.userId', $userFilter);
    }

    // Fetch users assigned to this Team Lead (role 31)
    $subQuery = "(SELECT userId FROM tbl_followup_sales WHERE teamLeadsales = $userId)";

    if (in_array($userRole, [1, 2, 28, 14])) {
        // Role 1 & 2 (Admins & Managers) see all records
    } elseif ($userRole == 31) {  
        // Team Leader (role 31) sees own and assigned users' data
        $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.userId IN $subQuery OR BaseTbl.salesTeamassign = $userId)", NULL, FALSE);
    } elseif ($userRole == 29) {
        // Sales Users (role 29) see only their own and assigned records
        $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.salesTeamassign = $userId)", NULL, FALSE);
    }

    $this->db->where('BaseTbl.status !=', 'converted leads');
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.followupId', 'DESC');
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}

    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
	 
	 
	 
    function addNewFollowuprecord($followuprecordInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_followup_sales', $followuprecordInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    
      function getFollowuprecordInfo($followupId)
    {
    $this->db->select('*');
        $this->db->from('tbl_followup_sales');
        $this->db->where('followupId', $followupId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
  
public function editFollowupRecord($FollowuprecordInfo, $followupId)
{
    $this->load->library('session');
    $userId = $this->session->userdata('userId');

    // Fetch the existing follow-up record
    $this->db->select('*');
    $this->db->from('tbl_followup_sales');
    $this->db->where('followupId', $followupId);
    $query = $this->db->get();
    $existingRecord = $query->row_array();

    if (!$existingRecord) {
        return FALSE; // No record found
    }

    $oldStatus = strtolower($existingRecord['status']);
    $newStatus = isset($FollowuprecordInfo['status']) ? strtolower($FollowuprecordInfo['status']) : '';

    // **Update the follow-up record**
    $this->db->where('followupId', $followupId);
    $this->db->update('tbl_followup_sales', $FollowuprecordInfo);
    log_message('info', 'Update Query: ' . $this->db->last_query());

    // **Check if status is "Hot lead", "Interested", or "Positive leads"**
    $top10Statuses = ['hot lead', 'interested', 'positive leads'];
    if (in_array($newStatus, $top10Statuses)) {
        $clientId = $existingRecord['clientId'];

        // Prepare data for insertion/updation
        $finalData = array_merge($existingRecord, $FollowuprecordInfo);
        $finalData['clientId'] = $clientId;
        $finalData['followupId'] = $followupId;
        $finalData['userId'] = $userId; // Explicitly set userId

        // Remove duplicate key if exists
        unset($finalData['userID']);

        // Ensure the correct teamlead is included
        if (!empty($existingRecord['teamlead'])) {
            $finalData['teamlead'] = $existingRecord['teamlead'];
        }

        // **Check if the client already exists in tbl_top10clients_sales**
        $this->db->select('clientId');
        $this->db->from('tbl_top10clients_sales');
        $this->db->where('clientId', $clientId);
        $this->db->where('followupId', $followupId);
        $existingTop10Record = $this->db->get()->row_array();

        if ($existingTop10Record) {
            // **Update existing record**
            $this->db->where('clientId', $clientId);
            $this->db->where('followupId', $followupId);
            $this->db->update('tbl_top10clients_sales', $finalData);
            log_message('info', 'Updated tbl_top10clients_sales: ' . $this->db->last_query());
        } else {
            // **Insert new record if not exists**
            $this->db->insert('tbl_top10clients_sales', $finalData);
            log_message('info', 'Inserted into tbl_top10clients_sales: ' . $this->db->last_query());
        }
    }

    // **Handle Status Change: If Converted Leads, move to tbl_results_sales**
    if ($newStatus === 'converted leads' && $oldStatus !== 'converted leads') {
        $finalData = array_merge($existingRecord, $FollowuprecordInfo);
        $finalData['followupId'] = $followupId;
        $finalData['userId'] = $userId; // Explicitly set userId

        // Remove duplicate key if exists
        unset($finalData['userID']);

        $this->db->insert('tbl_results_sales', $finalData);
        log_message('info', 'Inserted into tbl_results_sales: ' . $this->db->last_query());

        // **If inserted successfully, delete from tbl_followup_sales**
        if ($this->db->affected_rows() > 0) {
            $this->db->where('followupId', $followupId);
            $this->db->delete('tbl_followup_sales');
            log_message('info', 'Deleted from tbl_followup_sales: ' . $this->db->last_query());
        }
    } elseif ($newStatus === 'not interested') {
        // **Delete record if status is "Not Interested"**
        $this->db->where('followupId', $followupId);
        $this->db->delete('tbl_followup_sales');
        log_message('info', 'Deleted from tbl_followup_sales (Not Interested): ' . $this->db->last_query());

        return TRUE; // Exit early
    }

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

public function getUsersByRole($roleIds)
{
    $this->db->select('userId, name');  // Select only necessary fields
    $this->db->from('tbl_users');
    $this->db->where_in('roleId', $roleIds);
    $query = $this->db->get();
    return $query->result_array();
}
	

}