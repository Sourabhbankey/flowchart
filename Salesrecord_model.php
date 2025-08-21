<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Salesrecord_model (Salesrecord Model)
 * Salesrecord model class to get to handle Salesrecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Salesrecord_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
  function salesrecordListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_salesrecord_mng as BaseTbl');
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
    function salesrecordListing($searchText, $offset, $limit)
{
    $this->db->select('*');
    $this->db->from('tbl_salesrecord_mng as BaseTbl');
    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.productName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.salesrecId', 'DESC');
    $this->db->limit($limit, $offset);
    $query = $this->db->get();
    
    return $query->result();
}


    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
	 
	 
	 
    function addNewSalesrecord($salesrecordInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_salesrecord_mng', $salesrecordInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getSalesrecordInfo($salesrecId)
    {
        $this->db->select('*');
        $this->db->from('tbl_salesrecord_mng');
        $this->db->where('salesrecId', $salesrecId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editSalesrecord($salesrecordInfo, $salesrecId)
    {
        $this->db->where('salesrecId', $salesrecId);
        $this->db->update('tbl_salesrecord_mng', $salesrecordInfo);
        
        return TRUE;
    }
	
	
   


	public function get_product_codes() {
        $this->db->select('productCode'); // Select only the productCode field
        $query = $this->db->get('tbl_stock_mng'); // Query the tbl_salesrecord_mng table
        return $query->result_array(); // Return the result as an array
    }
		
        public function get_product_name() {
        $this->db->select('productName'); // Select only the productCode field
        $query = $this->db->get('tbl_stock_mng'); // Query the tbl_salesrecord_mng table
        return $query->result_array(); // Return the result as an array
    }
		
		
		
		
	public function get_product_description($code) {
        $this->db->where('productlistId', $code);
        $query = $this->db->get('tbl_prod_list');
		echo $this->db->last_query();
		return $query->result_array();

		}

public function get_product_names_by_code($productCode) {
        $this->db->select('productName');
        $this->db->from('tbl_stock_mng');
        $this->db->where('productCode', $productCode);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result_array();  // Return an array of product names
        } else {
            return false;
        }
    }


  
 public function updateStock($productCode, $newStock)
{
    $this->db->where('productCode', $productCode);
    $this->db->update('tbl_stock_mng', array('currentStock' => $newStock));
}


public function getCurrentStockByProductCode($productCode)
{
    $this->db->select('currentStock');
    $this->db->from('tbl_stock_mng');
    $this->db->where('productCode', $productCode);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->row(); // Return the row with the current stock
    } else {
        return null; // Return null if no stock record is found
    }
}
/*public function getKitContents($kitCode)
{
    $kits = [
        'nursery' => [
            ['productCode' => 'ES10', 'productName' => 'tables round', 'quantity' => 2],
            ['productCode' => 'ES15', 'productName' => 'mobile', 'quantity' => 3],
        ],
        'pg' => [
            ['productCode' => 'ES10', 'productName' => 'tables round', 'quantity' => 2],
            ['productCode' => 'ES15', 'productName' => 'mobile', 'quantity' => 3],
        ],
        'kit033' => [
            ['productCode' => 'ES10', 'productName' => 'tables round', 'quantity' => 2],
            ['productCode' => 'ES15', 'productName' => 'mobile', 'quantity' => 3],
        ],
    ];

    $kitCode = strtolower($kitCode); // lowercase input for matching

    return isset($kits[$kitCode]) ? $kits[$kitCode] : [];
}*/
public function getKitContents($stockId)
{
    // Make sure $kitId is clean and matches your DB field type
    $stockId = trim($stockId);

    // Query your table for kit items matching the given kitId
    $this->db->select('*');
    $this->db->from('tbl_kit_contents');
    $this->db->where('stockId', $stockId); // use the correct column name here

    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->result_array();
    } else {
        return [];  // no kit contents found for this kitId
    }
}

public function getStockByProductCode($productCode)
{
    $this->db->select('*');
    $this->db->from('tbl_stock_mng');
    $this->db->where('productCode', $productCode);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->row(); // return as object
    } else {
        return false;
    }
}



// Get existing sales record by product code
public function getSalesRecordByProductCode($productCode)
{
    return $this->db->get_where('tbl_salesrecord_mng', ['productCode' => $productCode])->row();
}

// Update existing sales record by product code
public function updateSalesrecordByProductCode($productCode, $data)
{
    $this->db->where('productCode', $productCode);
    return $this->db->update('tbl_salesrecord_mng', $data);
}

}