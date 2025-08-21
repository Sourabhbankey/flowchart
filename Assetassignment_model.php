<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Assetassignment_model (Assetassignment Model)
 * Assetassignment model class to handle asset assignment related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 13 May 2025
 */
class Assetassignment_model extends CI_Model
{
    /**
     * This function is used to get the asset assignment listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    /*function assetassignmentListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_assets_assign as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.employeeName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }*/
   function assetassignmentListingCount($searchText)
{
    $roleId = $this->session->userdata('role');
    $userId = $this->session->userdata('userId');

    $this->db->select('*');
    $this->db->from('tbl_assets_assign as BaseTbl');

    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.employeeName LIKE '%" . $this->db->escape_like_str($searchText) . "%' OR BaseTbl.assetsTitle1 LIKE '%" . $this->db->escape_like_str($searchText) . "%')";
        $this->db->where($likeCriteria);
    }

    if (!in_array($roleId, [1, 14])) {
        $this->db->where('BaseTbl.userID', $userId);
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $query = $this->db->get();

    return $query->num_rows();
}
    
    /**
     * This function is used to get the asset assignment listing data
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
  /* function assetassignmentListing($searchText, $page, $segment)
{
    $this->db->select('BaseTbl.*, Users.name as userName');
    $this->db->from('tbl_assets_assign as BaseTbl');
    $this->db->join('tbl_users as Users', 'Users.userId = BaseTbl.userID', 'left');

    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.employeeName LIKE '%".$searchText."%')";
        $this->db->where($likeCriteria);
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.assetsId', 'DESC');
    $this->db->limit($page, $segment);

    $query = $this->db->get();
    return $query->result();
}*/

function assetassignmentListing($searchText, $page, $segment)
{
    $roleId = $this->session->userdata('role');
    $userId = $this->session->userdata('userId');

    $this->db->select('BaseTbl.*, Users.name as userName');
    $this->db->from('tbl_assets_assign as BaseTbl');
    $this->db->join('tbl_users as Users', 'Users.userId = BaseTbl.userID', 'left');

    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.employeeName LIKE '%" . $this->db->escape_like_str($searchText) . "%' OR BaseTbl.assetsTitle1 LIKE '%" . $this->db->escape_like_str($searchText) . "%')";
        $this->db->where($likeCriteria);
    }

    if (!in_array($roleId, [1, 14])) {
        $this->db->where('BaseTbl.userID', $userId);
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.assetsId', 'DESC');
    $this->db->limit($page, $segment);

    $query = $this->db->get();
    return $query->result();
}
    
    /**
     * This function is used to add new asset assignment to system
     * @param array $assetassignmentInfo : This is asset assignment information
     * @return number $insert_id : This is last inserted id
     */
    function addNewAssetassignment($assetassignmentInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_assets_assign', $assetassignmentInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get asset assignment information by id
     * @param number $assetsId : This is asset assignment id
     * @return array $result : This is asset assignment information
     */
    function getAssetassignmentInfo($assetsId)
    {
        $this->db->select('*');
        $this->db->from('tbl_assets_assign');
        $this->db->where('assetsId', $assetsId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
       function getUser()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.isAdmin, userTbl.email');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14]);
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * This function is used to update asset assignment information
     * @param array $assetassignmentInfo : This is asset assignment updated information
     * @param number $assetsId : This is asset assignment id
     * @return boolean : Returns TRUE on success, FALSE on failure
     */
   function editAssetassignment($assetassignmentInfo, $assetsId)
{
    $this->db->where('assetsId', $assetsId);
    $this->db->update('tbl_assets_assign', $assetassignmentInfo);

    // Print the last executed query
    //echo $this->db->last_query(); exit; // Stops execution after printing

    return $this->db->affected_rows() > 0;
}

}
?>
