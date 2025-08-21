<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Dmfranchise_model (Dmfranchise Model)
 * Dmfranchise model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 03 June 2024
 */
class Dmfranchse_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function dmfranchseListingCount($searchText)
    {
        $this->db->select('BaseTbl.dmfranchseId, BaseTbl.dmfranchseTitle, BaseTbl.doneBy, BaseTbl.brspFranchiseAssigned, BaseTbl.numOfLeads, BaseTbl.dateOfrequest, BaseTbl.CampaStartdate, BaseTbl.CampaEnddate, BaseTbl.platform, BaseTbl.franchiseNumber, BaseTbl.description, BaseTbl.createdDtm , BaseTbl.dmreceiptattachmentS3file,BaseTbl.amount');
        $this->db->from('tbl_dmfranchse as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'BaseTbl.brspFranchiseAssigned = userTbl.userId', 'LEFT');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.dmfranchseTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    public function dmfranchseListing($searchText, $page, $segment) {
    $this->db->select('BaseTbl.dmfranchseId, BaseTbl.dmfranchseTitle, BaseTbl.doneBy, BaseTbl.brspFranchiseAssigned, BaseTbl.numOfLeads, BaseTbl.dateOfrequest, BaseTbl.CampaStartdate, BaseTbl.CampaEnddate, BaseTbl.platform, BaseTbl.franchiseNumber, BaseTbl.description, BaseTbl.createdDtm, BaseTbl.dmattachmentS3file , BaseTbl.dmreceiptattachmentS3file,BaseTbl.amount');
    $this->db->from('tbl_dmfranchse as BaseTbl');
    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.dmfranchseTitle LIKE '%" . $searchText . "%')";
        $this->db->where($likeCriteria);
    }
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by creation date, newest first
    $this->db->limit($page, $segment);
    $query = $this->db->get();
    
    $result = $query->result();
    return $result;
}
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewDmfranchse($dmfranchseInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_dmfranchse', $dmfranchseInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $dmfranchseId : This is training id
     * @return array $result : This is training information
     */
    function getDmfranchseInfo($dmfranchseId)
    {
        $this->db->select('dmfranchseId, dmfranchseTitle, doneBy, brspFranchiseAssigned, numOfLeads, dateOfrequest, CampaStartdate, CampaEnddate, platform, franchiseNumber, description, platform, dmattachmentS3file ,dmreceiptattachmentS3file,amount');
        $this->db->from('tbl_dmfranchse');
        $this->db->where('dmfranchseId', $dmfranchseId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editDmfranchse($dmfranchseInfo, $dmfranchseId)
    {
        $this->db->where('dmfranchseId', $dmfranchseId);
        $this->db->update('tbl_dmfranchse', $dmfranchseInfo);
        
        return TRUE;
    }
    /**
     * This function is used to get the user  information
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
    function getUser()
    {
        /*---Growth-Support--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 18);
        $query = $this->db->get();
        return $query->result();
    }
    /*New-code-03-Jul-2024*/
 
function getGrowthuser()
    {
        /*---Admission--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
       $this->db->where('userTbl.roleId', 15);
        $query = $this->db->get();
        return $query->result();
    }
 

public function getAllacattachmentRecords() {
        // Fetch all records from tbl_onbord_frm
        $query = $this->db->get('tbl_dmfranchse');
        return $query->result();
    }

    public function getattachmentRecordsByFranchise($franchiseNumber) {
        // Fetch records from tbl_onbord_frm for the specific franchise
        $this->db->where('franchiseNumber', $franchiseNumber);
        
        $query = $this->db->get('tbl_dmfranchse');
        return $query->result();
    }

    
    
    
    public function get_count($franchiseFilter = null) {
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_dmfranchse');
}

public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_dmfranchse');
}
public function get_data($limit, $start, $franchiseFilter = null) {
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    $query = $this->db->get('tbl_dmfranchse');
    return $query->result();
}

public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    $query = $this->db->get('tbl_dmfranchse');
    return $query->result();
}
public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->from('tbl_dmfranchse');
    return $this->db->count_all_results();
    }
    
    public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
    $this->db->select('dmfranchseId, dmfranchseTitle, doneBy, brspFranchiseAssigned, numOfLeads, dateOfrequest, CampaStartdate, CampaEnddate, platform, franchiseNumber, description, createdDtm, dmattachmentS3file,dmreceiptattachmentS3file,amount');
    $this->db->from('tbl_dmfranchse');
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->where('isDeleted', 0);
    $this->db->order_by('createdDtm', 'DESC'); // Sort by creation date, newest first
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
    
     public function getTotalTrainingRecordsCount() {
    return $this->db->count_all('tbl_dmfranchse');
    }
    
    public function getAllTrainingRecords($limit, $start) {
    $this->db->select('dmfranchseId, dmfranchseTitle, doneBy, brspFranchiseAssigned, numOfLeads, dateOfrequest, CampaStartdate, CampaEnddate, platform, franchiseNumber, description, createdDtm, dmattachmentS3file,dmreceiptattachmentS3file,amount');
    $this->db->from('tbl_dmfranchse');
    $this->db->where('isDeleted', 0);
    $this->db->order_by('createdDtm', 'DESC'); // Sort by creation date, newest first
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
    public function getTotalTrainingRecordsCountByRole($roleId) {
    $this->db->where('brspFranchiseAssigned', $roleId);
    $this->db->from('tbl_dmfranchse');
    return $this->db->count_all_results();
    }
   public function getTrainingRecordsByRole($roleId, $limit, $start) {
    $this->db->select('dmfranchseId, dmfranchseTitle, doneBy, brspFranchiseAssigned, numOfLeads, dateOfrequest, CampaStartdate, CampaEnddate, platform, franchiseNumber, description, createdDtm, dmattachmentS3file,dmreceiptattachmentS3file,amount');
    $this->db->from('tbl_dmfranchse');
    $this->db->where('brspFranchiseAssigned', $roleId);
    $this->db->where('isDeleted', 0);
    $this->db->order_by('createdDtm', 'DESC'); // Sort by creation date, newest first
    $this->db->limit($limit, $start);
    $query = $this->db->get();
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



public function getUsersByFranchise($franchiseNumber) {
    $this->db->select('tbl_users.userId, tbl_users.name');
    $this->db->from('tbl_branches');
    $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
    $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber); // Filter by franchise number
    $this->db->where('tbl_branches.isDeleted', 0); // Assuming you only want active records
    return $this->db->get()->result();
}


}