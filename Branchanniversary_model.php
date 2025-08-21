<?php
// File: application/models/Branchanniversary_model.php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class : Branchanniversary_model (Branch Anniversary Model)
 * Model class to handle branch anniversary related data
 * @author : [Your Name]
 * @version : 1.0
 * @since : [Current Date]
 */
class Branchanniversary_model extends CI_Model
{
    /**
     * This function is used to get the upcoming anniversaries listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function anniversaryListingCount($searchText = '')
    {
        $this->db->select('franchiseNumber, branchAnniversaryDate, applicantName');
        $this->db->from('tbl_branches as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.franchiseNumber LIKE '%".$searchText."%'
                            OR BaseTbl.applicantName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.branchAnniversaryDate IS NOT NULL');
        $this->db->where('BaseTbl.franchiseNumber IS NOT NULL');
        $this->db->where('BaseTbl.applicantName IS NOT NULL');
        $this->db->where('MONTH(BaseTbl.branchAnniversaryDate)', date('m'));
        $this->db->where('DAY(BaseTbl.branchAnniversaryDate) >=', date('d'));
        $this->db->where('BaseTbl.isDeleted', 0); // Assuming isDeleted field exists
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the upcoming anniversaries listing
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination limit
     * @param number $segment : This is pagination offset
     * @return array $result : This is result
     */
    function anniversaryListing($searchText = '', $page, $segment)
    {
        $this->db->select('franchiseNumber, branchAnniversaryDate, applicantName');
        $this->db->from('tbl_branches as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.franchiseNumber LIKE '%".$searchText."%'
                            OR BaseTbl.applicantName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.branchAnniversaryDate IS NOT NULL');
        $this->db->where('BaseTbl.franchiseNumber IS NOT NULL');
        $this->db->where('BaseTbl.applicantName IS NOT NULL');
        $this->db->where('MONTH(BaseTbl.branchAnniversaryDate)', date('m'));
        $this->db->where('DAY(BaseTbl.branchAnniversaryDate) >=', date('d'));
        $this->db->where('BaseTbl.isDeleted', 0); // Assuming isDeleted field exists
        $this->db->order_by('DAY(BaseTbl.branchAnniversaryDate)', 'ASC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();
        return $result;
    }
}