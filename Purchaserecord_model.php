<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Purchaserecord_model (Purchaserecord Model)
 * Purchaserecord model class to get to handle Purchaserecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 22 Jun 2024
 */
class Purchaserecord_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
   function purchaserecordListingCount($searchText)
{
    $this->db->select('BaseTbl.purcrecId, BaseTbl.productCode, BaseTbl.productName, BaseTbl.dateOfOrderplaced, BaseTbl.orderQty, BaseTbl.boughtFrom, BaseTbl.purchaseReceived, BaseTbl.receivedQty, BaseTbl.updatedStock, BaseTbl.description, BaseTbl.createdDtm , BaseTbl.purchaseattachment,BaseTbl.boughtFromVenderlist');
    $this->db->from('tbl_purchaserec_mng as BaseTbl');

    if(!empty($searchText)) {
        $likeCriteria = "(BaseTbl.productName LIKE '%".$searchText."%')";
        $this->db->where($likeCriteria);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    $roleId = $this->session->userdata('role');
    if ($roleId == 36) {
        $this->db->where('BaseTbl.boughtFromVenderlist', 'TCD');
    }

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
    function purchaserecordListing($searchText, $page, $segment)
{
    $this->db->select('BaseTbl.purcrecId, BaseTbl.productCode, BaseTbl.productName, BaseTbl.dateOfOrderplaced, BaseTbl.orderQty, BaseTbl.boughtFrom, BaseTbl.purchaseReceived, BaseTbl.receivedQty, BaseTbl.updatedStock, BaseTbl.description, BaseTbl.createdDtm , BaseTbl.purchaseattachment ,BaseTbl.boughtFromVenderlist');
    $this->db->from('tbl_purchaserec_mng as BaseTbl');

    if(!empty($searchText)) {
        $likeCriteria = "(BaseTbl.productName LIKE '%".$searchText."%')";
        $this->db->where($likeCriteria);
    }

    $this->db->where('BaseTbl.isDeleted', 0);

    $roleId = $this->session->userdata('role');
    if ($roleId == 36) {
        $this->db->where('BaseTbl.boughtFromVenderlist', 'TCD');
    }

    $this->db->order_by('BaseTbl.purcrecId', 'DESC');
    $this->db->limit($page, $segment);
    $query = $this->db->get();

    return $query->result();
}

    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewPurchaserecord($purchaserecordInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_purchaserec_mng', $purchaserecordInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getPurchaserecordInfo($purcrecId)
    {
        $this->db->select('purcrecId, productCode, productName, dateOfOrderplaced, orderQty, boughtFrom, purchaseReceived, receivedQty, updatedStock, description ,boughtFromVenderlist');
        $this->db->from('tbl_purchaserec_mng');
        $this->db->where('purcrecId', $purcrecId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editPurchaserecord($purchaserecordInfo, $purcrecId)
    {
        $this->db->where('purcrecId', $purcrecId);
        $this->db->update('tbl_purchaserec_mng', $purchaserecordInfo);
        
        return TRUE;
    }

    
    function updateStock($productCode, $newStock)
{
    $this->db->where('productCode', $productCode);
    $this->db->update('tbl_stock_mng', array('currentStock' => $newStock));
}
function getCurrentStockByProductCode($productCode)
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
/*public function insertReply($data) {
    return $this->db->insert('tbl_purchaserec_mng', $data);
}*/
  public function insertReply($data) {
    return $this->db->insert('tbl_purchaserec_replies', $data);
}
public function getRepliesByTicket($purcrecId) {
    $this->db->select('tbl_purchaserec_replies.*, tbl_users.name as repliedByName');
    $this->db->from('tbl_purchaserec_replies');
    $this->db->join('tbl_users', 'tbl_users.userId = tbl_purchaserec_replies.repliedBy', 'left');
    $this->db->where('tbl_purchaserec_replies.purcrecId', $purcrecId);
    $this->db->order_by('tbl_purchaserec_replies.createdDtm', 'desc'); // latest first
    return $this->db->get()->result();
}
}