<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Customdesign_model (Customdesign Model)
 * Customdesign model class to handle custom design related data
 * @author : Ashish
 * @version : 1.0
 * @since : 07 June 2024
 */
class Customdesign_model extends CI_Model
{
    /**
     * This function is used to get the custom design listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    public function customdesignListingCount($searchText)
    {
        $this->db->select('BaseTbl.customdesignId, BaseTbl.designTitle, BaseTbl.createdBy, BaseTbl.attachmentType, BaseTbl.franchiseNumber, BaseTbl.attachmentS3File, BaseTbl.submissionDate, BaseTbl.requirementSpe, BaseTbl.description, BaseTbl.createdDtm, tbl_users.name as created_by_name');
        $this->db->from('tbl_custom_desing as BaseTbl');
        $this->db->join('tbl_users', 'tbl_users.userId = BaseTbl.createdBy', 'left');
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.designTitle', $searchText);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    public function customdesignListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.customdesignId, BaseTbl.designTitle, BaseTbl.createdBy, BaseTbl.attachmentType, BaseTbl.franchiseNumber, BaseTbl.attachmentS3File, BaseTbl.submissionDate, BaseTbl.requirementSpe, BaseTbl.description, BaseTbl.createdDtm, tbl_users.name as created_by_name');
        $this->db->from('tbl_custom_desing as BaseTbl');
        $this->db->join('tbl_users', 'tbl_users.userId = BaseTbl.createdBy', 'left');
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.designTitle', $searchText);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.customdesignId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        return $query->result();
    }
    
    public function addNewCustomdesign($customdesignInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_custom_desing', $customdesignInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    public function getCustomdesignInfo($customdesignId)
    {
        $this->db->select('tbl_custom_desing.customdesignId, tbl_custom_desing.designTitle, tbl_custom_desing.createdBy, tbl_custom_desing.attachmentType, tbl_custom_desing.franchiseNumber, tbl_custom_desing.attachmentS3File, tbl_custom_desing.submissionDate, tbl_custom_desing.requirementSpe, tbl_custom_desing.description, tbl_custom_desing.createdDtm, tbl_users.name as created_by_name');
        $this->db->from('tbl_custom_desing');
        $this->db->join('tbl_users', 'tbl_users.userId = tbl_custom_desing.createdBy', 'left');
        $this->db->where('tbl_custom_desing.customdesignId', $customdesignId);
        $this->db->where('tbl_custom_desing.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    public function editCustomdesign($customdesignInfo, $customdesignId)
    {
        $this->db->where('customdesignId', $customdesignId);
        $this->db->update('tbl_custom_desing', $customdesignInfo);
        
        return TRUE;
    }
    
    public function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 19);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getTotalCustomDesignRecordsCountByFranchise($franchiseNumber, $dateFilters = [])
    {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if (!empty($dateFilters['startDate'])) {
            $this->db->where('DATE(createdDtm) >=', $dateFilters['startDate']);
        }
        if (!empty($dateFilters['endDate'])) {
            $this->db->where('DATE(createdDtm) <=', $dateFilters['endDate']);
        }
        $this->db->where('isDeleted', 0);
        $this->db->from('tbl_custom_desing');
        return $this->db->count_all_results();
    }
    
    public function getCustomDesignRecordsByFranchise($franchiseNumber, $limit, $start, $dateFilters = [])
    {
        $this->db->select('tbl_custom_desing.customdesignId, tbl_custom_desing.designTitle, tbl_custom_desing.createdBy, tbl_custom_desing.attachmentType, tbl_custom_desing.franchiseNumber, tbl_custom_desing.attachmentS3File, tbl_custom_desing.submissionDate, tbl_custom_desing.requirementSpe, tbl_custom_desing.description, tbl_custom_desing.createdDtm, tbl_users.name as created_by_name');
        $this->db->from('tbl_custom_desing');
        $this->db->join('tbl_users', 'tbl_users.userId = tbl_custom_desing.createdBy', 'left');
        $this->db->where('tbl_custom_desing.franchiseNumber', $franchiseNumber);
        if (!empty($dateFilters['startDate'])) {
            $this->db->where('DATE(createdDtm) >=', $dateFilters['startDate']);
        }
        if (!empty($dateFilters['endDate'])) {
            $this->db->where('DATE(createdDtm) <=', $dateFilters['endDate']);
        }
        $this->db->where('tbl_custom_desing.isDeleted', 0);
        $this->db->order_by('tbl_custom_desing.customdesignId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getTotalCustomDesignRecordsCount($dateFilters = [])
    {
        if (!empty($dateFilters['startDate'])) {
            $this->db->where('DATE(createdDtm) >=', $dateFilters['startDate']);
        }
        if (!empty($dateFilters['endDate'])) {
            $this->db->where('DATE(createdDtm) <=', $dateFilters['endDate']);
        }
        $this->db->where('isDeleted', 0);
        $this->db->from('tbl_custom_desing');
        return $this->db->count_all_results();
    }
    
    public function getAllCustomDesignRecords($limit, $start, $dateFilters = [])
    {
        $this->db->select('tbl_custom_desing.customdesignId, tbl_custom_desing.designTitle, tbl_custom_desing.createdBy, tbl_custom_desing.attachmentType, tbl_custom_desing.franchiseNumber, tbl_custom_desing.attachmentS3File, tbl_custom_desing.submissionDate, tbl_custom_desing.requirementSpe, tbl_custom_desing.description, tbl_custom_desing.createdDtm, tbl_users.name as created_by_name');
        $this->db->from('tbl_custom_desing');
        $this->db->join('tbl_users', 'tbl_users.userId = tbl_custom_desing.createdBy', 'left');
        if (!empty($dateFilters['startDate'])) {
            $this->db->where('DATE(createdDtm) >=', $dateFilters['startDate']);
        }
        if (!empty($dateFilters['endDate'])) {
            $this->db->where('DATE(createdDtm) <=', $dateFilters['endDate']);
        }
        $this->db->where('tbl_custom_desing.isDeleted', 0);
        $this->db->order_by('tbl_custom_desing.customdesignId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function getFranchiseNumberByUserId($userId)
    {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }
    
    public function get_users_without_franchise()
    {
        $this->db->where('roleId !=', 25);
        $query = $this->db->get('tbl_users');
        return $query->result();
    }
}