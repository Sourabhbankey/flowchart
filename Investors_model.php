<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Investors_model (Investors Model)
 * Investors model class to get to handle Investors related data 
 * @author : Kishor Mali
 * @version : 1.5
 * @since : 18 Jun 2022
 */
class Investors_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function investorsListingCount($searchText)
    {
        $this->db->select('BaseTbl.investorsId, BaseTbl.investorsName, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_investors as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.investorsName LIKE '%".$searchText."%')";
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
    function investorsListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.investorsId, BaseTbl.investorsName, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_investors as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.investorsName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.investorsId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewInvestors($investorsInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_investors', $investorsInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getInvestorsInfo($investorsId)
    {
        $this->db->select('investorsId, investorsName, description');
        $this->db->from('tbl_investors');
        $this->db->where('investorsId', $investorsId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editInvestors($investorsInfo, $investorsId)
    {
        $this->db->where('investorsId', $investorsId);
        $this->db->update('tbl_investors', $investorsInfo);
        
        return TRUE;
    }
}