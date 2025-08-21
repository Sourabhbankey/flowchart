<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Approval_model (Approval Model)
 * Approval model class to handle approval-related data
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Approval_model extends CI_Model
{
    /**
     * Get the approval listing count
     * @param string $searchText : Optional search text
     * @param int $userId : Current user ID
     * @param int $userRole : User role ID
     * @param int $searchUserId : Optional user ID to filter by
     * @return int : Row count
     */
    public function approvalListingCount($searchText, $userId, $userRole, $searchUserId = '')
    {
        $this->db->select('BaseTbl.*');
        $this->db->from('tbl_approval as BaseTbl');

        // Search filters
        if (!empty($searchText)) {
            $this->db->group_start();
            $this->db->like('BaseTbl.approvalTitle', $searchText);
            $this->db->or_like('BaseTbl.description', $searchText);
            $this->db->group_end();
        }

        // Role-based filtering
        if (!in_array($userRole, [1, 14])) {
            $this->db->group_start();
            $this->db->where('BaseTbl.userID', $userId);
            $this->db->or_where('BaseTbl.createdBy', $userId);
            $this->db->group_end();
        }

        // If filtering by another user
        if (!empty($searchUserId)) {
            $this->db->group_start();
            $this->db->where('BaseTbl.userID', $searchUserId);
            $this->db->or_where('BaseTbl.createdBy', $searchUserId);
            $this->db->group_end();
        }
 $this->db->order_by('BaseTbl.approvalId', 'desc');
        return $this->db->count_all_results();
    }

    /**
     * Get approval listing with pagination
     * @param string $searchText : Optional search text
     * @param int $page : Number of records per page
     * @param int $segment : Starting record
     * @param int $userId : Current user ID
     * @param int $userRole : User role ID
     * @param int $searchUserId : Optional user ID to filter by
     * @return array : Approval records
     */
    public function approvalListing($searchText, $page, $segment, $userId, $userRole, $searchUserId = '')
    {
        $this->db->select('BaseTbl.*, userTbl.name as createdByName');
        $this->db->from('tbl_approval as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.createdBy', 'left');

        // Search filters
        if (!empty($searchText)) {
            $this->db->group_start();
            $this->db->like('BaseTbl.approvalTitle', $searchText);
            $this->db->or_like('BaseTbl.description', $searchText);
            $this->db->group_end();
        }

        // Role-based filtering
        if (!in_array($userRole, [1, 14])) {
            $this->db->group_start();
            $this->db->where('BaseTbl.userID', $userId);
            $this->db->or_where('BaseTbl.createdBy', $userId);
            $this->db->group_end();
        }

        // Filter by another user
        if (!empty($searchUserId)) {
            $this->db->group_start();
            $this->db->where('BaseTbl.userID', $searchUserId);
            $this->db->or_where('BaseTbl.createdBy', $searchUserId);
            $this->db->group_end();
        }

        $this->db->order_by('BaseTbl.approvalId', 'desc');

        $this->db->limit($page, $segment);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Add new approval to system
     * @param array $approvalInfo : Approval information to insert
     * @return int : Last inserted ID
     */
    function addNewapproval($approvalInfo)
    {
          $this->db->select('*');
        $this->db->trans_start();
        $this->db->insert('tbl_approval', $approvalInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }

    /**
     * Get approval information by ID
     * @param int $approvalId : Approval ID
     * @return object : Approval information
     */
    function getapprovalInfo($approvalId)
    {
        $this->db->select('*');
        $this->db->from('tbl_approval');
        $this->db->where('approvalId', $approvalId);
        $query = $this->db->get();
        
        return $query->row();
    }

    /**
     * Update approval information
     * @param array $approvalInfo : Updated approval information
     * @param int $approvalId : Approval ID
     * @return bool : TRUE on success
     */
    public function editapproval($approvalInfo, $approvalId)
    {
        $this->db->where('approvalId', $approvalId);
        $this->db->update('tbl_approval', $approvalInfo);
        
        return TRUE;
    }

    /**
     * Get all active users
     * @return array : List of users
     */
    public function getAllUsers()
    {
        $this->db->select('userId, name');
        $this->db->from('tbl_users');
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get users by franchise number
     * @param string $franchiseNumber : Franchise number
     * @return array : List of users
     */
    public function getUsersByFranchise($franchiseNumber)
    {
        $this->db->select('userId, name');
        $this->db->from('tbl_users');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $query = $this->db->get();
        
        error_log("SQL Query: " . $this->db->last_query());
        return $query->result();
    }

    /**
     * Get user name by ID
     * @param int $userId : User ID
     * @return string : User name or 'N/A' if not found
     */
    public function getUserNameById($userId)
    {
        $this->db->select('name');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->name;
        }
        return "N/A";
    }

    /**
     * Get franchise number by user ID
     * @param int $userId : User ID
     * @return string|null : Franchise number or null if not found
     */
    public function getFranchiseNumber($userId)
    {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->franchiseNumber;
        }
        return null;
    }

   public function getRepliesByApproval($approvalId)
{
    $this->db->select('tbl_approval_replies.*, tbl_users.name as repliedByName');
    $this->db->from('tbl_approval_replies');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_approval_replies.repliedBy', 'left');
    $this->db->where('tbl_approval_replies.approvalId', $approvalId);
    $this->db->order_by('tbl_approval_replies.createdDtm', 'desc'); // Latest first
    return $this->db->get()->result();
}

    /**
     * Get all users
     * @return array : List of users
     */
    public function getUser()
    {
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Insert a reply to an approval
     * @param array $replyInfo : Reply information to insert
     * @return bool : TRUE on success
     */
  public function insertReply($replyInfo)
{
    // Validate required fields
    if (empty($replyInfo['approvalId']) || empty($replyInfo['message']) || empty($replyInfo['repliedBy'])) {
       
        return FALSE;
    }

    // Define default fields
    $defaultFields = [
        'msgRead' => '0',
        'status' => 'pending',
        'isDeleted' => 0,
        'createdDtm' => date('Y-m-d H:i:s'),
        'createdBy' => $replyInfo['repliedBy'],
        'updatedBy' => null,
        'updatedDtm' => null
    ];

    // Merge provided $replyInfo with default fields
    $replyInfo = array_merge($defaultFields, $replyInfo);

    // Cast data types
    $replyInfo['approvalId'] = (int)$replyInfo['approvalId'];
    $replyInfo['repliedBy'] = (int)$replyInfo['repliedBy'];
    $replyInfo['createdBy'] = (int)$replyInfo['createdBy'];
    $replyInfo['msgRead'] = (string)$replyInfo['msgRead'];

    try {
        $this->db->trans_start();
        $this->db->insert('tbl_approval_replies', $replyInfo);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
       
            return FALSE;
        }

        return $insert_id > 0 ? $insert_id : FALSE;
    } catch (Exception $e) {
     
        return FALSE;
    }
}
}