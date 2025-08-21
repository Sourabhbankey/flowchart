<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Class_model (Class Model)
 * Class model class to handle Class-related data
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Classname_model extends CI_Model
{
    /**
     * Get the Class listing count
     * @param string $searchText : Optional search text
     * @return number $count : Row count
     */
    function ClassnameListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_class as BaseTbl');
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
    function ClassnameListing($searchText, $page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_class as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.ClassTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.ClassId', 'DESC');
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
    function addNewClassname($ClassInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_class', $ClassInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * Get Class information by ID
     * @param number $ClassId : Class ID
     * @return object $result : Class information
     */
    function getClassnameInfo($classId)
    {
        $this->db->select('*');
        $this->db->from('tbl_class');
        $this->db->where('classId', $classId);
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
    function editClassname($ClassnameInfo, $classId)
    {
        $this->db->where('classId', $classId);
        $this->db->update('tbl_class', $ClassnameInfo);
        
        return TRUE;
    }
}