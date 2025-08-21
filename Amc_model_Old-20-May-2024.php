<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Amc_model (Amc Model)
 * Amc model class to get to handle Amc related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 08 June 2023
 */
class Amc_model extends CI_Model
{
    /**
     * This function is used to get the Amc listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function amcListingCount($searchText)
    {
        $this->db->select('BaseTbl.amcId, BaseTbl.brnameTitle, BaseTbl.franchiseNum, BaseTbl.brLocation, BaseTbl.brState, BaseTbl.oldAMCdue, BaseTbl.curAmc, BaseTbl.totalAmc, BaseTbl.statusAmc, BaseTbl.dueDateAmc, BaseTbl.descAmc, BaseTbl.createdDtm');
        $this->db->from('tbl_amc as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.brnameTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the Amc listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function amcListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.amcId, BaseTbl.brnameTitle, BaseTbl.franchiseNum, BaseTbl.brLocation, BaseTbl.brState, BaseTbl.oldAMCdue, BaseTbl.curAmc, BaseTbl.totalAmc, BaseTbl.statusAmc, BaseTbl.dueDateAmc, BaseTbl.descAmc, BaseTbl.createdDtm');
        $this->db->from('tbl_amc as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.brnameTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.amcId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewAmc($amcInfo)
    {
        $this->db->trans_start();
        //$this->db->set($data);
        $this->db->insert('tbl_amc', $amcInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $trainingId : This is training id
     * @return array $result : This is training information
     */
    function getAmcInfo($amcId)
    {
        $this->db->select('amcId, brnameTitle, franchiseNum, brLocation, brState, oldAMCdue, curAmc, totalAmc, statusAmc, dueDateAmc, descAmc');
        $this->db->from('tbl_amc');
        $this->db->where('amcId', $amcId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editAmc($amcInfo, $amcId)
    {
        $this->db->where('amcId', $amcId);
        $this->db->update('tbl_amc', $amcInfo);
        
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
}