<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Blog_model (Blog Model)
 * Blog model class to handle blog-related data
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Blog_model extends CI_Model
{
    /**
     * Get the blog listing count
     * @param string $searchText : Optional search text
     * @return number $count : Row count
     */
    function blogListingCount($searchText)
    {
        $this->db->select('BaseTbl.blogId, BaseTbl.blogTitle, BaseTbl.blogLink, BaseTbl.publishedDate, BaseTbl.blogS3Image, BaseTbl.publishedPlatform, BaseTbl.description, BaseTbl.createdDtm, BaseTbl.openedCount');
        $this->db->from('tbl_blog as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.blogTitle LIKE '%" . $searchText . "%')";
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
    function blogListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.blogId, BaseTbl.blogTitle, BaseTbl.blogLink, BaseTbl.publishedDate, BaseTbl.publishedPlatform, BaseTbl.blogS3Image, BaseTbl.description, BaseTbl.createdDtm, BaseTbl.openedCount');
        $this->db->from('tbl_blog as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.blogTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.blogId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * Increment the opened count for a blog
     * @param number $blogId : Blog ID
     * @return number|boolean : Updated count or false
     */
    public function incrementOpenedCount($blogId)
        {
            // Increment openedCount by 1
            $this->db->set('openedCount', 'openedCount + 1', FALSE);
            $this->db->where('blogId', $blogId);
            $this->db->update('tbl_blog'); // Use your table name

            // Get new count to return to frontend
            $this->db->select('openedCount');
            $this->db->from('tbl_blog');
            $this->db->where('blogId', $blogId);
            $query = $this->db->get()->row();

            return $query ? $query->openedCount : 0;
        }

    
    
    /**
     * Get the opened count for a blog
     * @param number $blogId : Blog ID
     * @return number : The opened count
     */
    public function getOpenedCount($blogId) {
        $this->db->select('openedCount');
        $this->db->where('blogId', $blogId);
        $query = $this->db->get('tbl_blog');
        return $query->num_rows() > 0 ? $query->row()->openedCount : 0;
    }
    
    /**
     * Add a new blog
     * @return number $insert_id : Last inserted ID
     */
    function addNewBlog($blogInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_blog', $blogInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * Get blog information by ID
     * @param number $blogId : Blog ID
     * @return object $result : Blog information
     */
    function getblogInfo($blogId)
    {
        $this->db->select('blogId, blogTitle, blogLink, publishedDate, blogS3Image, publishedPlatform, description, openedCount');
        $this->db->from('tbl_blog');
        $this->db->where('blogId', $blogId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    /**
     * Update blog information
     * @param array $blogInfo : Updated blog information
     * @param number $blogId : Blog ID
     * @return boolean
     */
    function editBlog($blogInfo, $blogId)
    {
        $this->db->where('blogId', $blogId);
        $this->db->update('tbl_blog', $blogInfo);
        
        return TRUE;
    }
}