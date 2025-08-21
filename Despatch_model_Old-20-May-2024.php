<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Despatch_model (Despatch Model)
 * Despatch model class to get to handle despatch related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 08 June 2023
 */
class Despatch_model extends CI_Model
{
    /**
     * This function is used to get the despatch listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function despatchListingCount($searchText)
    {
        $this->db->select('BaseTbl.despatchId, BaseTbl.despatchTitle, BaseTbl.franchiseNumber, BaseTbl.orderNumber, BaseTbl.modeOforder, BaseTbl.transportCourior, BaseTbl.emailconfirmDispatchPOD, BaseTbl.podNumber, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_despatch as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.despatchTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the despatch listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function despatchListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.despatchId, BaseTbl.despatchTitle, BaseTbl.franchiseNumber, BaseTbl.orderNumber, BaseTbl.modeOforder, BaseTbl.transportCourior, BaseTbl.emailconfirmDispatchPOD, BaseTbl.podNumber, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_despatch as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.despatchTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.despatchId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewDespatch($despatchInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_despatch', $despatchInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $trainingId : This is training id
     * @return array $result : This is training information
     */
    function getDespatchInfo($despatchId)
    {
        $this->db->select('despatchId, despatchTitle, description, franchiseNumber, orderNumber, modeOforder, transportCourior, emailconfirmDispatchPOD, podNumber,');
        $this->db->from('tbl_despatch');
        $this->db->where('despatchId', $despatchId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editDespatch($despatchInfo, $despatchId)
    {
        $this->db->where('despatchId', $despatchId);
        $this->db->update('tbl_despatch', $despatchInfo);
        
        return TRUE;
    }
    /**
     * This function is used to get the user  information
     * @return array $result : This is result of the query
     */
    function getUser()
    {
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14]);
        $query = $this->db->get();
        return $query->result();
    }
    function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 25);
        $query = $this->db->get();
        return $query->result();
    }
}