<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Class_model (Class Model)
 * Class model class to handle Class-related data
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Homework_model extends CI_Model
{
    /**
     * Get the Class listing count
     * @param string $searchText : Optional search text
     * @return number $count : Row count
     */
    function HomeworkListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_homework as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.ClassName LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * Get the Class listing
     * @param string $searchText : Optional search text
     * @param number $page : Pagination offset
     * @param number $segment : Pagination limit
     * @return array $result : Result
     */
    function HomeworkListing($searchText, $page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_homework as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.ClassTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
       // $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.HomeworkId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * Increment the opened count for a Class
     * @param number $ClassId : Class ID
     * @return number|boolean : Updated count or false
     */
    

    
    
    /**
     * Add a new Class
     * @return number $insert_id : Last inserted ID
     */
    function addNewHomework($homeworkInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_homework', $homeworkInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * Get Class information by ID
     * @param number $ClassId : Class ID
     * @return object $result : Class information
     */
    function getHomeworkInfo($homeworkId)
    {
        $this->db->select('*');
        $this->db->from('tbl_homework');
        $this->db->where('homeworkId', $homeworkId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    /**
     * Update Class information
     * @param array $ClassInfo : Updated Class information
     * @param number $ClassId : Class ID
     * @return boolean
     */
    function editHomework($HomeworkInfo, $homeworkId)
    {
        $this->db->where('homeworkId', $homeworkId);
        $this->db->update('tbl_homework', $HomeworkInfo);
        
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
    public function getAllSubjects()
{
    $this->db->select('*');
    $this->db->from('tbl_subject');
    $this->db->where('isDeleted', 0); // Optional: if you use soft delete
    $query = $this->db->get();
    return $query->result();
}
 public function getAllSections()
{
    $this->db->select('*');
    $this->db->from('tbl_classSection');
    $this->db->where('isDeleted', 0); // Optional: if you use soft delete
    $query = $this->db->get();
    return $query->result();
}
 public function getAllClasses()
{
    $this->db->select('*');
    $this->db->from('tbl_class');
    $this->db->where('isDeleted', 0); // Optional: if you use soft delete
    $query = $this->db->get();
    return $query->result();
}
}