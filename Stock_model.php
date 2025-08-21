<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Stock_model (Stock Model)
 * Stock model class to get to handle Stock related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 22 Jun 2024
 */
class Stock_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function stockListingCount($searchText)
    {
        $this->db->select('BaseTbl.stockId, BaseTbl.productCode, BaseTbl.productName, BaseTbl.openingStock, BaseTbl.currentStock, BaseTbl.prodQuantity, BaseTbl.description,BaseTbl.openingStockDate, BaseTbl.createdDtm,BaseTbl.productType ,BaseTbl.stockattachment');
        $this->db->from('tbl_stock_mng as BaseTbl');
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
   /* function stockListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.stockId, BaseTbl.productCode, BaseTbl.productName, BaseTbl.openingStock, BaseTbl.currentStock, BaseTbl.prodQuantity, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_stock_mng as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.productName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.stockId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }*/
    function stockListing($searchText, $productCode, $productName, $limit, $offset)
{
    $this->db->select('BaseTbl.stockId, BaseTbl.productCode, BaseTbl.productName, BaseTbl.openingStock, BaseTbl.currentStock,BaseTbl.openingStockDate, BaseTbl.prodQuantity, BaseTbl.description, BaseTbl.createdDtm,BaseTbl.productType,BaseTbl.stockattachment');
    $this->db->from('tbl_stock_mng as BaseTbl');
    
    // Apply search filter for productName
    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.productName LIKE '%" . $this->db->escape_like_str($searchText) . "%')";
        $this->db->where($likeCriteria);
    }

    // Apply filters for productCode and productName
    if (!empty($productCode)) {
        $this->db->where('BaseTbl.productCode', $productCode);
    }
    if (!empty($productName)) {
        $this->db->where('BaseTbl.productName', $productName);
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.stockId', 'DESC');
    $this->db->limit($limit, $offset);  // Correct pagination limit and offset
    $query = $this->db->get();

    // Check if the query is successful and return results
    if ($query->num_rows() > 0) {
        return $query->result();
    }
    return [];
}

    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
   function addNewStock($stockInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_stock_mng', $stockInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
   /* public function addNewStock($stockInfo)
    {
    // Insert data into codes table
    $this->db->insert('tbl_stock_mng', $stockInfo);
    
    // Get the ID of the inserted record
    $insert_id = $this->db->insert_id();
    
    // Generate code as "ES" + primary ID
    $productCode = 'ES' . $insert_id;
    $this->db->where('stockId', $insert_id);
    $this->db->update('tbl_stock_mng', array('productCode' => $productCode));
    
    
    return $insert_id; // Return the ID of the inserted record
    }*/
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getStockInfo($stockId)
    {
        $this->db->select('*');
        $this->db->from('tbl_stock_mng');
        $this->db->where('stockId', $stockId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editStock($stockInfo, $stockId)
    {
        $this->db->where('stockId', $stockId);
        $this->db->update('tbl_stock_mng', $stockInfo);
        
        return TRUE;
    }
    public function get_product_codes() {
        $this->db->select('productCode'); // Select only the productCode field
        $query = $this->db->get('tbl_prod_list'); // Query the tbl_salesrecord_mng table
        return $query->result_array(); // Return the result as an array
    }
        
        public function get_product_name() {
        $this->db->select('productName'); // Select only the productCode field
        $query = $this->db->get('tbl_prod_list'); // Query the tbl_salesrecord_mng table
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
        $this->db->from('tbl_prod_list');
        $this->db->where('productCode', $productCode);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result_array();  // Return an array of product names
        } else {
            return false;
        }
    }

    public function getCurrentStockByProductCode($productCode)
{
    $this->db->select('currentStock');
    $this->db->from('tbl_stock_mng');
    $this->db->where('productCode', $productCode);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        $result = $query->row();
        return $result->currentStock;
    } else {
        return 0; // In case the product code does not exist or no stock is found
    }
}
  public function getStockByProductCode($productCode)
    {
        $this->db->where('productCode', $productCode);
        $query = $this->db->get('tbl_stock_mng'); // Assuming 'stock' is your table name
        return $query->row(); // Return a single row
    }
  // Function to get unique Product Codes for the dropdown
function getProductCodes()
{
    $this->db->select('productCode');
    $this->db->from('tbl_stock_mng');
    $this->db->where('isDeleted', 0);
    $query = $this->db->get();
    return $query->result_array();
}

// Function to get unique Product Names for the dropdown
function getProductNames()
{
    $this->db->select('productName');
    $this->db->from('tbl_stock_mng');
    $this->db->where('isDeleted', 0);
    $query = $this->db->get();
    return $query->result_array();
}
public function stocksampleListingCount($searchText = '')
{
    $this->db->select('COUNT(*) as count');
    $this->db->from('tbl_stock_mng');
    $this->db->where('isDeleted', 0);
    $this->db->where('productType', 'sample');

    if (!empty($searchText)) {
        $likeCriteria = "(productCode LIKE '%" . $searchText . "%' OR productName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    $query = $this->db->get();
    return $query->row()->count;
}
public function stocksampleListing($searchText = '', $page = 0, $segment = 0)
{
    $this->db->select('*');
    $this->db->from('tbl_stock_mng');
    $this->db->where('isDeleted', 0);
    $this->db->where('productType', 'sample'); // <- Only sample kit products

    if (!empty($searchText)) {
        $likeCriteria = "(productCode LIKE '%" . $searchText . "%' OR productName LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }

    $this->db->limit($page, $segment);
    $query = $this->db->get();

    return $query->result();
}

}