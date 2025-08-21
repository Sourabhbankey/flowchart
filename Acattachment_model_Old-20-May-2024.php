<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Task_model (Task Model)
 * Task model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Acattachment_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function acattachmentListingCount($searchText)
    {
        $this->db->select('BaseTbl.acattachmentId, BaseTbl.acattachmentTitle, BaseTbl.acattachmentS3File, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_acattachment as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.acattachmentTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function acattachmentListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.acattachmentId, BaseTbl.acattachmentTitle, BaseTbl.acattachmentS3File, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_acattachment as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.acattachmentTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.acattachmentId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewAcattachment($acattachmentInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_acattachment', $acattachmentInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $attachmentId : This is attachment id
     * @return array $result : This is attachment information
     */
    function getAcattachmentInfo($acattachmentId)
    {
        $this->db->select('acattachmentId, acattachmentTitle, acattachmentS3File, description');
        $this->db->from('tbl_acattachment');
        $this->db->where('acattachmentId', $acattachmentId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editAcattachment($acattachmentInfo, $acattachmentId)
    {
        $this->db->where('acattachmentId', $acattachmentId);
        $this->db->update('tbl_acattachment', $acattachmentInfo);
        
        return TRUE;
    }
}