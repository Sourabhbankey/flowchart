<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Investors_model (Faq Model)
 * Faq model class to get to handle Faq related data 
 * @author : Ashish
 * @version : 1.1
 * @since : 11 Nov 2024
 */
class Faq_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function faqListingCount($searchText)
    {
        $this->db->select('BaseTbl.faqId, BaseTbl.faqTitle, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_faqs as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.faqTitle LIKE '%".$searchText."%')";
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
 public function faqListing($searchText, $offset, $limit)
{
    $this->db->select('BaseTbl.faqId, BaseTbl.faqTitle, BaseTbl.description, BaseTbl.createdDtm');
    $this->db->from('tbl_faqs as BaseTbl');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.faqTitle', $searchText);
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.faqId', 'DESC');

    // Correct order: limit first, then offset
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}


    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewFaq($faqInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_faqs', $faqInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getFaqInfo($faqId)
    {
        $this->db->select('faqId, faqTitle, description');
        $this->db->from('tbl_faqs');
        $this->db->where('faqId', $faqId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editFaq($faqInfo, $faqId)
    {
        $this->db->where('faqId', $faqId);
        $this->db->update('tbl_faqs', $faqInfo);
        
        return TRUE;
    }
}