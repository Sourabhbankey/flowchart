<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Standardformat_model (Standardformat Model)
 * Standardformat model class to handle standardformat-related data
 * @author : Ashish
 * @version : 1.0
 * @since : 13 May 2025
 */
class Standardformat_model extends CI_Model
{
    /**
     * Get the standardformat listing count
     * @param string $searchText : Optional search text
     * @return number $count : Row count
     */
    function standardformatListingCount($searchText)
    {
        $this->db->select('BaseTbl.standId, BaseTbl.standTitle, BaseTbl.standformatS3File, BaseTbl.publishedDate,, BaseTbl.standCategoryType, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_standformat as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.standTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * Get the blog listing
     * @param string $searchText : Optional search text
     * @param number $page : Pagination offset
     * @param number $segment : Pagination limit
     * @return array $result : Result
     */
    function standardformatListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.standId, BaseTbl.standTitle, BaseTbl.standformatS3File, BaseTbl.publishedDate,, BaseTbl.standCategoryType, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_standformat as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.standTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.standId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * Increment the opened count for a blog
     * @param number $standId : Standardformat ID
     * @return number|boolean : Updated count or false
     */
    /*public function incrementOpenedCount($standId)
        {
            // Increment openedCount by 1
            $this->db->set('openedCount', 'openedCount + 1', FALSE);
            $this->db->where('standId', $standId);
            $this->db->update('tbl_blog'); // Use your table name

            // Get new count to return to frontend
            $this->db->select('openedCount');
            $this->db->from('tbl_blog');
            $this->db->where('standId', $standId);
            $query = $this->db->get()->row();

            return $query ? $query->openedCount : 0;
        }*/

    
    
    /**
     * Get the opened count for a blog
     * @param number $standId : Standardformat ID
     * @return number : The opened count
     */
    /*public function getOpenedCount($standId) {
        $this->db->select('openedCount');
        $this->db->where('standId', $standId);
        $query = $this->db->get('tbl_blog');
        return $query->num_rows() > 0 ? $query->row()->openedCount : 0;
    }
    */
    /**
     * Add a new blog
     * @return number $insert_id : Last inserted ID
     */
    function addNewStandardformat($standardformatInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_standformat', $standardformatInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * Get blog information by ID
     * @param number $standId : Standardformat ID
     * @return object $result : Standardformat information
     */
    function getstandardformatInfo($standId)
    {
        $this->db->select('standId, standTitle, standformatS3File, publishedDate, standCategoryType,description');
        $this->db->from('tbl_standformat');
        $this->db->where('standId', $standId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    /**
     * Update blog information
     * @param array $standardformatInfo : Updated blog information
     * @param number $standId : Standardformat ID
     * @return boolean
     */
    function editStandardformat($standardformatInfo, $standId)
    {
        $this->db->where('standId', $standId);
        $this->db->update('tbl_standformat', $standardformatInfo);
        
        return TRUE;
    }
}