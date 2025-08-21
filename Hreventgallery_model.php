<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Hreventgallery_model (Hreventgallery Model)
 * Hreventgallery model class to get to handle Hreventgallery related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 12 May 2025
 */
class Hreventgallery_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function hreventgalleryListingCount($searchText)
    {
        $this->db->select('BaseTbl.hreventId, BaseTbl.eventName, BaseTbl.venue, BaseTbl.eventDate, BaseTbl.eventS3attachment, BaseTbl.eventvideoS3attachment, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_hreventgallery as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.eventName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function hreventgalleryListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.hreventId, BaseTbl.eventName, BaseTbl.venue, BaseTbl.eventDate, BaseTbl.eventS3attachment, BaseTbl.eventvideoS3attachment, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_hreventgallery as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.eventName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.hreventId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    

    function hreventgalleryListingPage($searchText, $limit, $offset)
{
    $this->db->select('BaseTbl.hreventId, BaseTbl.eventName, BaseTbl.venue, BaseTbl.eventDate, BaseTbl.eventS3attachment, BaseTbl.eventvideoS3attachment, BaseTbl.description, BaseTbl.createdDtm');
    $this->db->from('tbl_hreventgallery as BaseTbl');
    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.eventName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.hreventId', 'DESC');
    $this->db->limit($limit, $offset); // Use limit and offset correctly
    $query = $this->db->get();

    return $query->result();
}
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewHreventgallery($hreventgalleryInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_hreventgallery', $hreventgalleryInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getHreventgalleryInfo($hreventId)
    {
        $this->db->select('hreventId, eventName, venue, eventDate, eventS3attachment, eventvideoS3attachment, description');
        $this->db->from('tbl_hreventgallery');
        $this->db->where('hreventId', $hreventId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->row_array();
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editHreventgallery($hreventgalleryInfo, $hreventId)
    {
        $this->db->where('hreventId', $hreventId);
        $this->db->update('tbl_hreventgallery', $hreventgalleryInfo);
        
        return TRUE;
    }
}