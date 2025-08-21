<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Classesfeetemplate_model (Classes Fee Template Model)
 * Model class to handle classes fee template related data
 * @author : [Your Name]
 * @version : 1.0
 * @since : May 2025
 */
class Classesfeetemplate_model extends CI_Model
{
    /**
     * Get the count of classes fee templates
     * @param string $searchText : Optional search text
     * @return number $count : Row count
     */
  /* function classesfeetemplateListingCount($searchText)
{
    $roleId = $this->session->userdata('role');
    $userId = $this->session->userdata('userId');
    $franchiseNumber = $this->session->userdata('franchiseNumber');

    $this->db->select('*');
    $this->db->from('tbl_classesfeetemplate');

    if (!empty($searchText)) {
        $likeCriteria = "(franchiseNumber LIKE '%" . $searchText . "%' OR brAddress LIKE '%" . $searchText . "%' OR branchContacNum LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    // Show only logged-in franchise's data (unless admin)
    if (!in_array($roleId, [1, 2, 14,13,15])) {
        $this->db->where('franchiseNumber', $franchiseNumber);
    }

    $this->db->where('isDeleted', 0);
    $query = $this->db->get();

    return $query->num_rows();
}*/
function classesfeetemplateListingCount($searchText)
{
    $roleId = $this->session->userdata('role');
    $userId = $this->session->userdata('userId');
    $franchiseNumber = $this->session->userdata('franchiseNumber');

    $this->db->select('*');
    $this->db->from('tbl_classesfeetemplate');

    if (!empty($searchText)) {
        $likeCriteria = "(franchiseNumber LIKE '%" . $searchText . "%' OR brAddress LIKE '%" . $searchText . "%' OR branchContacNum LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    if (in_array($roleId, [15])) {
        // BRSP role: show only data where brspFranchiseAssigned = current user
        $this->db->where('brspFranchiseAssigned', $userId);
    } elseif (!in_array($roleId, [1, 2, 14, 13])) {
        // Regular franchise user
        $this->db->where('franchiseNumber', $franchiseNumber);
    }

    $this->db->where('isDeleted', 0);
    return $this->db->count_all_results();
}

  /*function classesfeetemplateListing($searchText, $page, $segment)
{
    $roleId = $this->session->userdata('role');
    $userId = $this->session->userdata('userId');
    $franchiseNumber = $this->session->userdata('franchiseNumber');

    $this->db->select('cft.*, u.name as createdByName, u2.name as assignedToName');
    $this->db->from('tbl_classesfeetemplate cft');
    $this->db->join('tbl_users u', 'u.userId = cft.createdBy', 'left');
    $this->db->join('tbl_users u2', 'u2.userId = cft.brspFranchiseAssigned', 'left');

    if (!empty($searchText)) {
        $likeCriteria = "(cft.franchiseNumber LIKE '%" . $searchText . "%' OR cft.brAddress LIKE '%" . $searchText . "%' OR cft.branchContacNum LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    // Show only logged-in franchise's data (unless admin)
    if (!in_array($roleId, [1, 2, 14,13,15])) {
        $this->db->where('cft.franchiseNumber', $franchiseNumber);
    }

    $this->db->where('cft.isDeleted', 0);
    $this->db->order_by('cft.createdDtm', 'DESC');
    $this->db->limit($page, $segment);
    $query = $this->db->get();

    return $query->result();
}

*/
    function classesfeetemplateListing($searchText, $page, $segment)
{
    $roleId = $this->session->userdata('role');
    $userId = $this->session->userdata('userId');
    $franchiseNumber = $this->session->userdata('franchiseNumber');

    $this->db->select('cft.*, u.name as createdByName, u2.name as assignedToName');
    $this->db->from('tbl_classesfeetemplate cft');
    $this->db->join('tbl_users u', 'u.userId = cft.createdBy', 'left');
    $this->db->join('tbl_users u2', 'u2.userId = cft.brspFranchiseAssigned', 'left');

    if (!empty($searchText)) {
        $likeCriteria = "(cft.franchiseNumber LIKE '%" . $searchText . "%' OR cft.brAddress LIKE '%" . $searchText . "%' OR cft.branchContacNum LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    if (in_array($roleId, [15])) {
        // BRSP: Only records assigned to this user
        $this->db->where('cft.brspFranchiseAssigned', $userId);
    } elseif (!in_array($roleId, [1, 2, 14, 13])) {
        // Regular franchise users
        $this->db->where('cft.franchiseNumber', $franchiseNumber);
    }

    $this->db->where('cft.isDeleted', 0);
    $this->db->order_by('cft.createdDtm', 'DESC');
    $this->db->limit($page, $segment);

    $query = $this->db->get();
    return $query->result();
}

    /**
     * Add new classes fee template
     * @return number $insert_id : Last inserted ID
     */
    function addNewClassesfeetemplate($classesfeetemplateInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_classesfeetemplate', $classesfeetemplateInfo);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * Get classes fee template information by ID
     * @param number $classfeeId : Fee template ID
     * @return array $result : Fee template information
     */
    function getclassesfeetemplateInfo($classfeeId)
    {
        $this->db->select('t.*, creator.name as createdByName, assigned.name as assignedUserName');
        $this->db->from('tbl_classesfeetemplate t');
        $this->db->join('tbl_users creator', 'creator.userId = t.createdBy', 'left');
        $this->db->join('tbl_users assigned', 'assigned.userId = t.brspFranchiseAssigned', 'left');
        $this->db->where('t.classfeeId', $classfeeId);
        $this->db->where('t.isDeleted', 0);
        $query = $this->db->get();
        return $query->row();
    }
    
    /**
     * Update classes fee template information
     * @param array $classesfeetemplateInfo : Updated information
     * @param number $classfeeId : Fee template ID
     */
    function editclassesfeetemplate($classesfeetemplateInfo, $classfeeId)
    {
        $this->db->where('classfeeId', $classfeeId);
        $this->db->update('tbl_classesfeetemplate', $classesfeetemplateInfo);
        return TRUE;
    }
    
    /**
     * Get franchise numbers from branches
     * @return array $result : List of franchises
     */
    function getFranchises()
    {
        $this->db->select('franchiseNumber, applicantName');
        $this->db->from('tbl_branches');
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * Get users by franchise number
     * @param string $franchiseNumber : Franchise number
     * @return array $result : List of users
     */
    public function getUsersByFranchise($franchiseNumber)
    {
        $this->db->select('tbl_users.userId, tbl_users.name');
        $this->db->from('tbl_branches');
        $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
        $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber);
        $this->db->where('tbl_branches.isDeleted', 0);
        return $this->db->get()->result();
    }
    
    /**
     * Get user name by ID
     * @param number $userId : User ID
     * @return string $name : User name
     */
    public function getUserNameById($userId)
    {
        if (!$userId) {
            return 'None';
        }
        $this->db->select('name');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->name : 'Unknown';
    }
    
    /**
     * Get franchise data (address and contact number) by franchise number
     * @param string $franchiseNumber : Franchise number
     * @return object $result : Franchise data
     */
    function getFranchiseData($franchiseNumber)
    {
        $this->db->select('branchAddress AS brAddress, mobile AS branchContacNum');
        $this->db->from('tbl_branches');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->row();
    }
}