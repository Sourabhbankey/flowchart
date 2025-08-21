<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Guidelines_model (Guidelines Model)
 * Guidelines model class to handle guidelines-related data
 * @author : Ashish
 * @version : 1.0
 * @since : 13 May 2025
 */
class Guidelines_model extends CI_Model
{
    /**
     * Get the guidelines listing count
     * @param string $searchText : Optional search text
     * @return number $count : Row count
     */
    function guidelinesListingCount($searchText)
    {
        $this->db->select('BaseTbl.guidelinesId, BaseTbl.guidelinesTitle, BaseTbl.guidelineS3File, BaseTbl.publishedDate,BaseTbl.guidelineCategoryType, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_guidelines as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.guidelinesTitle LIKE '%" . $searchText . "%')";
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
    function guidelinesListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.guidelinesId, BaseTbl.guidelinesTitle, BaseTbl.guidelineS3File, BaseTbl.publishedDate,,BaseTbl.guidelineCategoryType, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_guidelines as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.guidelinesTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.guidelinesId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * Increment the opened count for a blog
     * @param number $guidelinesId : Guidelines ID
     * @return number|boolean : Updated count or false
     */
    /*public function incrementOpenedCount($guidelinesId)
        {
            // Increment openedCount by 1
            $this->db->set('openedCount', 'openedCount + 1', FALSE);
            $this->db->where('guidelinesId', $guidelinesId);
            $this->db->update('tbl_blog'); // Use your table name

            // Get new count to return to frontend
            $this->db->select('openedCount');
            $this->db->from('tbl_blog');
            $this->db->where('guidelinesId', $guidelinesId);
            $query = $this->db->get()->row();

            return $query ? $query->openedCount : 0;
        }*/

    
    
    /**
     * Get the opened count for a blog
     * @param number $guidelinesId : Guidelines ID
     * @return number : The opened count
     */
    /*public function getOpenedCount($guidelinesId) {
        $this->db->select('openedCount');
        $this->db->where('guidelinesId', $guidelinesId);
        $query = $this->db->get('tbl_blog');
        return $query->num_rows() > 0 ? $query->row()->openedCount : 0;
    }
    */
    /**
     * Add a new blog
     * @return number $insert_id : Last inserted ID
     */
    function addNewGuidelines($guidelinesInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_guidelines', $guidelinesInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * Get blog information by ID
     * @param number $guidelinesId : Guidelines ID
     * @return object $result : Guidelines information
     */
    function getguidelinesInfo($guidelinesId)
    {
        $this->db->select('guidelinesId, guidelinesTitle, guidelineS3File, publishedDate,guidelineCategoryType, description');
        $this->db->from('tbl_guidelines');
        $this->db->where('guidelinesId', $guidelinesId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    /**
     * Update blog information
     * @param array $guidelinesInfo : Updated blog information
     * @param number $guidelinesId : Guidelines ID
     * @return boolean
     */
    function editGuidelines($guidelinesInfo, $guidelinesId)
    {
        $this->db->where('guidelinesId', $guidelinesId);
        $this->db->update('tbl_guidelines', $guidelinesInfo);
        
        return TRUE;
    }
}