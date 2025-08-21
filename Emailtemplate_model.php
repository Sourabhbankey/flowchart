<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Emailtemplate_model (Emailtemplate Model)
 * Emailtemplate model class to get to handle Emailtemplate related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 05 June 2025
 */
class Emailtemplate_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
 function EmailtemplateListingCount($searchText)
{
    $this->db->select('*');
    $this->db->from('tbl_email_templates as BaseTbl');

    // Search filter
    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.franchiseName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    // Role-based filter
    $roleId = $this->session->userdata('role');
    $franchiseNumber = $this->session->userdata('franchiseNumber');

    if ($roleId == '25') {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
        $this->db->where('BaseTbl.status', 'Active');
    }

    $query = $this->db->get();
    return $query->num_rows();
}

function EmailtemplateListing($searchText, $page, $segment)
{
    $this->db->select('BaseTbl.*, Users.name as createdByName');
    $this->db->from('tbl_email_templates as BaseTbl');
    $this->db->join('tbl_users as Users', 'Users.userId = BaseTbl.createdBy', 'left');

    // Search filter
    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.franchiseName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    // Role-based filter
    $roleId = $this->session->userdata('role');
    $franchiseNumber = $this->session->userdata('franchiseNumber');

    if ($roleId == '25') {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
        $this->db->where('BaseTbl.status', 'Active');
    }

    $this->db->order_by('BaseTbl.emailtempId', 'DESC');
    $this->db->limit($page, $segment);

    $query = $this->db->get();
    return $query->result();
}



    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewEmailtemplate($EmailtemplateInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_email_templates', $EmailtemplateInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getEmailtemplateInfo($emailtempId)
    {
        $this->db->select('*');
        $this->db->from('tbl_email_templates');
        $this->db->where('emailtempId', $emailtempId);
      //  $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editEmailtemplate($EmailtemplateInfo, $emailtempId)
    {
        $this->db->where('emailtempId', $emailtempId);
        $this->db->update('tbl_email_templates', $EmailtemplateInfo);
        
        return TRUE;
    }
     public function getManagersByFranchise($franchiseNumber)
    {
        return $this->db->select('u.userId, u.name')
                        ->from('tbl_users as u')
                        ->join('tbl_branches as b', 'b.branchFranchiseAssigned = u.userId')
                        ->where('b.franchiseNumber', $franchiseNumber)
                        ->get()
                        ->result();
    }
public function getBranchEmail($franchiseNumber)
{
    $this->db->select('branchEmail');
    $this->db->from('tbl_branches');
    $this->db->where('franchiseNumber', $franchiseNumber);
    $query = $this->db->get();
    
    $result = $query->row();
    return $result ? $result->branchEmail : null;
}
    /**
     * This function is used to get franchise details
     * @param string $franchiseNumber : This is franchise number
     * @return object $result : This is franchise information
     */
    public function getFranchiseDetails($franchiseNumber)
    {
        return $this->db->select('*')
                        ->from('tbl_branches as f')
                        ->where('f.franchiseNumber', $franchiseNumber)
                        ->get()
                        ->row();
    }
}