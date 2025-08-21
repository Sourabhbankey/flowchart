<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Branchinstallation_model (Support Model)
 * Support model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Branchinstallation_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function branchinstallationListingCount($searchText)
    {
     /*   $this->db->select('BaseTbl.brsetupid, BaseTbl.branchSetupName, BaseTbl.brcompAddress, BaseTbl.acClearance, BaseTbl.pincode, BaseTbl.city, BaseTbl.state, BaseTbl.frcostInvoiceS3File, BaseTbl.frCostInvoicenum, BaseTbl.studkitInvoiceS3File, BaseTbl.studKitInvoicenum, BaseTbl.brsetupInvoiceS3File, BaseTbl.brsetupInvoicenum, BaseTbl.lgchargInvoiceS3File, BaseTbl.lginvoicenum, BaseTbl.eBayinstallkitInvoiceS3File, BaseTbl.eBaystudKitInvoiceS3File, BaseTbl.acRemark, BaseTbl.lgClearance, BaseTbl.lgRemark, BaseTbl.infraRemark, BaseTbl.numOfStudKit, BaseTbl.studKitDesc, BaseTbl.additionlOffer, BaseTbl.specialRemark, BaseTbl.dateOfDespatch, BaseTbl.modeOfDespatch, BaseTbl.materialReceivedOn, BaseTbl.schInstalldate, BaseTbl.installDate, BaseTbl.instaBrstatus, BaseTbl.instaBrstatusDate, BaseTbl.brAddressInstall, BaseTbl.prefInstalldate, BaseTbl.franchiseNumber, BaseTbl.description, BaseTbl.upDespatchReceiptS3File, BaseTbl.createdDtm,BaseTbl.deliverychallan');*/
      $this->db->select('*');
        $this->db->from('tbl_branch_setup_install as BaseTbl');
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.branchSetupName', $searchText); // Use like() to prevent SQL injection
        }
        $this->db->order_by('BaseTbl.createdDtm', 'DESC');
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the task listing
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function branchinstallationListing($searchText, $page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_branch_setup_install as BaseTbl');
        if (!empty($searchText)) {
            $this->db->like('BaseTbl.branchSetupName', $searchText); // Use like() to prevent SQL injection
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.createdDtm', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewBranchinstallation($branchinstallationInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_branch_setup_install', $branchinstallationInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $brsetupid : This is training id
     * @return array $result : This is training information
     */
    function getbranchinstallationInfo($brsetupid)
    {
        $this->db->select('*');
        $this->db->from('tbl_branch_setup_install');
        $this->db->where('brsetupid', $brsetupid);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    /**
     * This function is used to update the task information
     * @param array $branchinstallationInfo : This is task updated information
     * @param number $brsetupid : This is task id
     */
    function editBranchinstallation($branchinstallationInfo, $brsetupid)
    {
        $this->db->where('brsetupid', $brsetupid);
        $this->db->update('tbl_branch_setup_install', $branchinstallationInfo);
        
        return TRUE;
    }
    
    /**
     * This function is used to get the user information
     * @return array $result : This is result of the query
     */
    function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 25);
        $query = $this->db->get();
        return $query->result();
    }

    public function getAllacattachmentRecords() {
       $this->db->order_by('createdDtm', 'DESC');
        $query = $this->db->get('tbl_branch_setup_install');
        return $query->result();
    }

    public function getattachmentRecordsByFranchise($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
       $this->db->order_by('createdDtm', 'DESC');
        $query = $this->db->get('tbl_branch_setup_install');
        return $query->result();
    }
    
    public function get_count($franchiseFilter = null) {
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_branch_setup_install');
    }

    public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('createdDtm', 'DESC');
        return $this->db->count_all_results('tbl_branch_setup_install');
    }
    
    public function get_data($limit, $start, $franchiseFilter = null) {
        $this->db->limit($limit, $start);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
       $this->db->order_by('createdDtm', 'DESC');
        $query = $this->db->get('tbl_branch_setup_install');
        return $query->result();
    }

    public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->limit($limit, $start);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        
        $this->db->order_by('createdDtm', 'DESC'); 
        $query = $this->db->get('tbl_branch_setup_install');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->from('tbl_branch_setup_install');
        return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
        $this->db->where('franchiseNumber', $franchiseNumber);
       $this->db->order_by('createdDtm', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_branch_setup_install');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCount() {
        return $this->db->count_all('tbl_branch_setup_install');
    }
    
    public function getAllTrainingRecords($limit, $start) {
        $this->db->order_by('createdDtm', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_branch_setup_install');
        return $query->result();
    }
    
    public function getTotalTrainingRecordsCountByRole($roleId) {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->from('tbl_branch_setup_install');
        return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByRole($roleId, $limit, $start) {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->order_by('createdDtm', 'DESC'); 
        $this->db->limit($limit, $start);
        $query = $this->db->get('tbl_branch_setup_install');
        return $query->result();
    }
    
    public function getFranchiseNumberByUserId($userId) {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }
    
    public function getBranchByFranchiseNumber($franchiseNumber) {
        $this->db->select('franchiseName as branchSetupName, branchAddress as brcompAddress, branchcityName as city, branchState as state');
        $this->db->from('tbl_branches');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null;
        }
    }
    
    public function getUsersByFranchise($franchiseNumber) {
        $this->db->select('tbl_users.userId, tbl_users.name');
        $this->db->from('tbl_branches');
        $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
        $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber);
        $this->db->where('tbl_branches.isDeleted', 0);
        return $this->db->get()->result();
    }
}