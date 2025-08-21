<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Salesrecord_model (Salesrecord Model)
 * Salesrecord model class to get to handle Salesrecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Clients_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */



/**
 * Fetches client records based on role and search filters
 */
public function clientsListingCount($searchText, $userRole, $userId, $searchUserId = '', $fromDate = '', $toDate = '')
{
    $this->db->select('COUNT(*) as count');
    $this->db->from('tbl_clients_sales as BaseTbl');
    $this->db->join('tbl_users as UserTbl', 'BaseTbl.userId = UserTbl.userId', 'left');

    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.clientname', $searchText);
        $this->db->or_like('BaseTbl.emailid', $searchText);
        $this->db->or_like('BaseTbl.contactno', $searchText);
        $this->db->group_end();
    }

    if (!empty($searchUserId)) {
        $this->db->where('BaseTbl.userId', $searchUserId);
    }

    if (!empty($fromDate)) {
        $this->db->where('BaseTbl.issuedon >=', $fromDate);
    }

    if (!empty($toDate)) {
        $this->db->where('BaseTbl.issuedon <=', $toDate);
    }

    // Fetch users assigned to this Team Lead (role 31)
    $subQuery = "(SELECT userId FROM tbl_clients_sales WHERE teamLeadsales = $userId)";

    if (in_array($userRole, [28, 14,2,1])) {
        // Admin/Manager roles can view all records
    } elseif ($userRole == 31) {  
        // Team Leader (role 31) can see their own data and assigned users
        $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.userId IN $subQuery)", NULL, FALSE);
    } elseif ($userRole == 29) {
        // Users (role 29) can only see their own data
        $this->db->where('BaseTbl.userId', $userId);
    }
   
$this->db->where('BaseTbl.status !=', 'converted leads');
    $this->db->where('BaseTbl.isDeleted', 0);
    $query = $this->db->get();
    return $query->row()->count;
}

public function clientsrecordListing($searchText = '', $limit, $offset, $userRole, $userId, $searchUserId = '', $fromDate = '', $toDate = '')
{
    $this->db->select('BaseTbl.clientId, BaseTbl.issuedon, BaseTbl.firstcall, BaseTbl.clientname, 
                       BaseTbl.contactno, BaseTbl.altercontactno, BaseTbl.emailid, BaseTbl.city, 
                       BaseTbl.location, BaseTbl.lastcall, BaseTbl.nextfollowup, 
                       BaseTbl.status, BaseTbl.description, BaseTbl.description2,BaseTbl.offername,BaseTbl.offercost, BaseTbl.roleId, 
                       UserTbl.name as userName');
    $this->db->from('tbl_clients_sales as BaseTbl');
    $this->db->join('tbl_users as UserTbl', 'BaseTbl.userId = UserTbl.userId', 'left');

    if (!empty($searchText)) {
        $this->db->group_start();
        $this->db->like('BaseTbl.clientname', $searchText);
        $this->db->or_like('BaseTbl.emailid', $searchText);
        $this->db->or_like('BaseTbl.contactno', $searchText);
        $this->db->group_end();
    }

    if (!empty($searchUserId)) {
        $this->db->where('BaseTbl.userId', $searchUserId);
    }

    if (!empty($fromDate)) {
        $this->db->where('BaseTbl.issuedon >=', $fromDate);
    }

    if (!empty($toDate)) {
        $this->db->where('BaseTbl.issuedon <=', $toDate);
    }

    // Fetch users assigned to this Team Lead (role 31)
    $subQuery = "(SELECT userId FROM tbl_clients_sales WHERE teamLeadsales = $userId)";

    if (in_array($userRole, [28, 14])) {
        // Admin/Manager roles can view all records
    } elseif ($userRole == 31) {  
        // Team Leader (role 31) can see their own data and assigned users
        $this->db->where("(BaseTbl.userId = $userId OR BaseTbl.userId IN $subQuery)", NULL, FALSE);
    } elseif ($userRole == 29) {
        // Users (role 29) can only see their own data
        $this->db->where('BaseTbl.userId', $userId);
    }
    
$this->db->where('BaseTbl.status !=', 'converted leads');
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.clientId', 'DESC'); //
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}



	 
public function addNewClientsrecord($clientsrecordInfo)
{
    $this->load->library('session');
    $userId = $this->session->userdata('userId');

    if (empty($userId)) {
        log_message('error', 'Session userId is empty in addNewClientsrecord');
        return false;
    }

    // Force userId assignment
    $clientsrecordInfo['userId'] = $userId;
    unset($clientsrecordInfo['userID']); // Remove incorrect key if exists

    // 🔹 Fetch Team Lead ID for this user
    $this->db->select('teamLeadsales');
    $this->db->from('tbl_users');
    $this->db->where('userId', $userId);
    $query = $this->db->get();
    $teamLeadRow = $query->row();
    $teamLeadId = !empty($teamLeadRow) ? $teamLeadRow->teamLeadsales : null;

    // Insert into tbl_clients_sales
    $this->db->insert('tbl_clients_sales', $clientsrecordInfo);
    $insertId = $this->db->insert_id();

    if ($insertId) {
        $clientsrecordInfo['clientId'] = $insertId;
        $status = !empty($clientsrecordInfo['status']) ? strtolower($clientsrecordInfo['status']) : '';

        // Insert into tbl_results_sales if status is 'Converted Leads'
        if ($status === 'converted leads') {
            $this->db->insert('tbl_results_sales', $clientsrecordInfo);
        }

        // 🔹 Insert into tbl_followup_sales with TeamLeadsales
        if (!empty($clientsrecordInfo['nextfollowup'])) {
            $followupData = $clientsrecordInfo;
            $followupData['teamLeadsales'] = $teamLeadId; // Assign Team Lead ID
            $this->db->insert('tbl_followup_sales', $followupData);
        }

        // Insert into tbl_top10clients_sales if status is 'Hot Lead', 'Interested', or 'Positive Leads'
        if (in_array($status, ['hot lead', 'interested', 'positive leads'])) {
            $this->db->insert('tbl_top10clients_sales', $clientsrecordInfo);
        }
    }

    return $insertId;
}






    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    
      function getClientsrecordInfo($clientId)
    {
        $this->db->select('clientId,issuedon ,firstcall,clientname,contactno,altercontactno,emailid,city,location,lastcall,nextfollowup,status,description,description2,offername,offercost');
        $this->db->from('tbl_clients_sales');
        $this->db->where('clientId', $clientId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
public function editClientsrecord($clientsrecordInfo, $clientId)
{
    // Load session library if not already loaded
    $this->load->library('session');

    // Get the logged-in user ID from session
    $userId = $this->session->userdata('userId');

    // Fetch the current record to check existing status and teamlead
    $this->db->select('status, nextfollowup, teamLeadsales');
    $this->db->from('tbl_clients_sales');
    $this->db->where('clientId', $clientId);
    $query = $this->db->get();
    $currentRecord = $query->row_array();

    // Update the client's record in tbl_clients_sales
    $this->db->where('clientId', $clientId);
    $this->db->update('tbl_clients_sales', $clientsrecordInfo);
    log_message('info', 'Update Query: ' . $this->db->last_query());

    // If status is 'Converted leads', insert into tbl_results_sales
    if (!empty($clientsrecordInfo['status']) && strtolower($clientsrecordInfo['status']) === 'converted leads') {
        $clientsrecordInfo['clientId'] = $clientId;
        $clientsrecordInfo['userId'] = $userId;
        $this->db->insert('tbl_results_sales', $clientsrecordInfo);
        log_message('info', 'Insert into tbl_results_sales: ' . $this->db->last_query());
    }

    // If nextfollowup is set, insert into tbl_followup_sales
    if (!empty($clientsrecordInfo['nextfollowup'])) {
        $clientsrecordInfo['clientId'] = $clientId;
        $clientsrecordInfo['userId'] = $userId;
        $this->db->insert('tbl_followup_sales', $clientsrecordInfo);
        log_message('info', 'Insert into tbl_followup_sales: ' . $this->db->last_query());
    }

    // If status is 'Hot lead', 'Interested', or 'Positive leads', update or insert into tbl_top10clients_sales
    $top10Statuses = ['hot lead', 'interested', 'positive leads'];
    if (!empty($clientsrecordInfo['status']) && in_array(strtolower($clientsrecordInfo['status']), $top10Statuses)) {
        $clientsrecordInfo['clientId'] = $clientId;
        $clientsrecordInfo['userId'] = $userId;

        // Add teamlead if available in existing record
        if (!empty($currentRecord['teamlead'])) {
            $clientsrecordInfo['teamlead'] = $currentRecord['teamlead'];
        }

        // Check if the client already exists in tbl_top10clients_sales
        $this->db->select('clientId');
        $this->db->from('tbl_top10clients_sales');
        $this->db->where('clientId', $clientId);
        $existingRecord = $this->db->get()->row_array();

        if ($existingRecord) {
            // Update the existing record
            $this->db->where('clientId', $clientId);
            $this->db->update('tbl_top10clients_sales', $clientsrecordInfo);
            log_message('info', 'Update tbl_top10clients_sales: ' . $this->db->last_query());
        } else {
            // Insert new record if not exists
            $this->db->insert('tbl_top10clients_sales', $clientsrecordInfo);
            log_message('info', 'Insert into tbl_top10clients_sales: ' . $this->db->last_query());
        }
    }

    return TRUE;
}


public function getClientsByRole($userID, $roleId) {
    $this->db->select('*');
    $this->db->from('tbl_clients_sales');

    if ($roleId == 16) {
        // Team leaders see all users with roleId = 16
        $this->db->where('roleId', 16);
    } else {
        // Normal users see only their data
        $this->db->where('userID', $userID);
    }

    $query = $this->db->get();
    return $query->result();
}

	public function getAllUsers($userId, $userRole)
{
    $this->db->select('u.userId, u.name');
    $this->db->from('tbl_users u');
    $this->db->where('u.isDeleted', 0);

    if ($userRole == 31) { // If logged-in user is a Team Leader
        $this->db->join('tbl_clients_sales cs', 'u.userId = cs.userId', 'inner');
        $this->db->where('cs.teamLeadsales', $userId); // Show only assigned users
        $this->db->group_by('u.userId'); // Ensure unique users
    } elseif ($userRole == 2) { // If Admin/Manager, show all users with specified roles
        $this->db->where_in('u.roleId', [34, 31, 28, 29]);
    }

    $query = $this->db->get();
    return $query->result();
}


public function getUsersByRoles($roles) {
        $this->db->select('userId, name, email');
        $this->db->from('tbl_users');
        $this->db->where_in('roleId', $roles);
        $query = $this->db->get();
        return $query->result();
    }

}