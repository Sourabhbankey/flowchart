<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Productlist_model (Productlist Model)
 * Productlist model class to get to handle Productlist related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 22 Jun 2024
 */
class Productlist_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function productlistListingCount($searchText)
    {
        $this->db->select('BaseTbl.productlistId, BaseTbl.productCode, BaseTbl.productName, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_prod_list as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.productName LIKE '%".$searchText."%')";
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
    function productlistListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.productlistId, BaseTbl.productCode, BaseTbl.productName, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_prod_list as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.productName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.productlistId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
	 // commented by yashi
    // function addNewProductlist($productlistInfo)
    // {
        // $this->db->trans_start();
        // $this->db->insert('tbl_prod_list', $productlistInfo);
        
        // $insert_id = $this->db->insert_id();
        
        // $this->db->trans_complete();
        
        // return $insert_id;
    // }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getProductlistInfo($productlistId)
    {
        $this->db->select('productlistId, productCode, productName, description');
        $this->db->from('tbl_prod_list');
        $this->db->where('productlistId', $productlistId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editProductlist($productlistInfo, $productlistId)
    {
        $this->db->where('productlistId', $productlistId);
        $this->db->update('tbl_prod_list', $productlistInfo);
        
        return TRUE;
    }
	// code done by yashi
	public function addNewProductlist($data)
	{
    // Insert data into codes table
    $this->db->insert('tbl_prod_list', $data);
    
    // Get the ID of the inserted record
    $insert_id = $this->db->insert_id();
    
    // Generate code as "ES" + primary ID
    $productCode = 'ES' . $insert_id;
    $this->db->where('productlistId', $insert_id);
    $this->db->update('tbl_prod_list', array('productCode' => $productCode));
    
    
    return $insert_id; // Return the ID of the inserted record
	}
    public function getLastProductCode()
{
    $this->db->select('productCode');
    $this->db->from('tbl_prod_list'); // Assuming your table is named 'productlist'
    $this->db->order_by('productCode', 'DESC');
    $this->db->limit(1);
    $query = $this->db->get();
    
    if ($query->num_rows() > 0) {
        return $query->row()->productCode;
    } else {
        return 0; // Return 0 if no products exist
    }
}

	
	// code done by yashi
}