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
        $this->db->select('BaseTbl.*, userTbl.name as franchiseAssignedName');
        $this->db->from('tbl_amc as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.branchFranchiseAssigned', 'left');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.franchiseName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.createdDtm', 'DESC');
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the Amc listing
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function amcListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.*, userTbl.name as franchiseAssignedName');
        $this->db->from('tbl_amc as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.branchFranchiseAssigned', 'left');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.franchiseName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.amcId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        return $query->result();
    }

    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewAmc($amcInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_amc', $amcInfo);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $amcId : This is amc id
     * @return object $result : This is amc information
     */
    function getAmcInfo($amcId)
    {
        $this->db->select('amcId, franchiseName, franchiseNumber, branchcityName, branchState, penaltyCharges, penaltyAmount, otherChargesTitle, otherChargesAmount, oldAMCdue, amcAmount, totalAmc, statusAmc, branchFranchiseAssigned, currentStatus, dueDateAmc, amcYear1, amcYear1dueAmount, amcYear1date, statusYear1Amc, amcYear2, amcYear2dueAmount, amcYear2date, statusYear2Amc, amcYear3, amcYear3dueAmount, amcYear3date, statusYear3Amc, amcYear4, amcYear4dueAmount, amcYear4date, statusYear4Amc, amcYear5, amcYear5dueAmount, amcYear5date, statusYear5Amc, descAmc, amcYear1S3File, amcYear2S3File, amcYear3S3File, amcYear4S3File, amcYear5S3File');
        $this->db->from('tbl_amc');
        $this->db->where('amcId', $amcId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    /**
     * This function is used to update the task information
     * @param array $amcInfo : This is amc updated information
     * @param number $amcId : This is amc id
     * @return bool $result : TRUE on success
     */
    function editAmc($amcInfo, $amcId)
    {
        $this->db->where('amcId', $amcId);
        $this->db->update('tbl_amc', $amcInfo);
        
        return TRUE;
    }
    
    /**
     * This function is used to get the user information
     * @return array $result : This is result of the query
     */
    function getUser()
    {
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1, 14, 2]);
        $this->db->where('userTbl.roleId', 15);
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * This function is used to get all attachment records
     * @return array $result : This is result of the query
     */
  public function getAllacattachmentRecords() {
    $this->db->select('BaseTbl.*, userTbl.name as franchiseAssignedName');
    $this->db->from('tbl_amc as BaseTbl');
    $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.branchFranchiseAssigned', 'left');
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by createdDtm
    $query = $this->db->get();
    return $query->result();
}
    /**
     * This function is used to get attachment records by franchise
     * @param string $franchiseNumber : This is franchise number
     * @return array $result : This is result of the query
     */
   public function getattachmentRecordsByFranchise($franchiseNumber) {
    $this->db->select('BaseTbl.*, userTbl.name as franchiseAssignedName');
    $this->db->from('tbl_amc as BaseTbl');
    $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.branchFranchiseAssigned', 'left');
    $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by createdDtm
    $query = $this->db->get();
    return $query->result();
}
    
    /**
     * This function is used to get count of AMC records
     * @param string $franchiseFilter : Optional franchise number filter
     * @return number $count : This is row count
     */
    public function get_count($franchiseFilter = null) {
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_amc');
    }

    /**
     * This function is used to get count of AMC records by franchise
     * @param string $franchiseNumber : Franchise number
     * @param string $franchiseFilter : Optional franchise number filter
     * @return number $count : This is row count
     */
    public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results('tbl_amc');
    }
    
    /**
     * This function is used to get AMC data with pagination
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @param string $franchiseFilter : Optional franchise number filter
     * @return array $result : This is result
     */
  public function get_data($limit, $start, $franchiseFilter = null) {
    $this->db->select('BaseTbl.*, userTbl.name as franchiseAssignedName');
    $this->db->from('tbl_amc as BaseTbl');
    $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.branchFranchiseAssigned', 'left');
    if ($franchiseFilter) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseFilter);
    }
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}

    /**
     * This function is used to get AMC data by franchise with pagination
     * @param string $franchiseNumber : Franchise number
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @param string $franchiseFilter : Optional franchise number filter
     * @return array $result : This is result
     */
   public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
    $this->db->select('BaseTbl.*, userTbl.name as franchiseAssignedName');
    $this->db->from('tbl_amc as BaseTbl');
    $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.branchFranchiseAssigned', 'left');
    $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
    if ($franchiseFilter) {
        $this->db->where('BaseTbl.franchiseNumber', $franchiseFilter);
    }
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
    
    /**
     * This function is used to get total count of training records by franchise
     * @param string $franchiseNumber : Franchise number
     * @param string $statusAmc : Optional AMC status filter
     * @return number $count : This is row count
     */
    public function getTotalTrainingRecordsCountByFranchise($franchiseNumber, $statusAmc = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->where('currentStatus', 'Installed-Active');
        if ($statusAmc) {
            $this->db->where('statusAmc', $statusAmc);
        }
        return $this->db->count_all_results('tbl_amc');
    }

    /**
     * This function is used to get training records by franchise
     * @param string $franchiseNumber : Franchise number
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @param string $statusAmc : Optional AMC status filter
     * @return array $result : This is result
     */
   public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start, $statusAmc = null) {
    $this->db->select('BaseTbl.*, userTbl.name as franchiseAssignedName');
    $this->db->from('tbl_amc as BaseTbl');
    $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.branchFranchiseAssigned', 'left');
    $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
    $this->db->where('BaseTbl.currentStatus', 'Installed-Active');
    if ($statusAmc) {
        $this->db->where('BaseTbl.statusAmc', $statusAmc);
    }
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
    /**
     * This function is used to get total count of training records
     * @param string $statusAmc : Optional AMC status filter
     * @return number $count : This is row count
     */
    public function getTotalTrainingRecordsCount($statusAmc = null) {
        $this->db->from('tbl_amc');
        $this->db->where('currentStatus', 'Installed-Active');
        if ($statusAmc) {
            $this->db->where('statusAmc', $statusAmc);
        }
        return $this->db->count_all_results();
    }

    /**
     * This function is used to get all training records
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @param string $statusAmc : Optional AMC status filter
     * @param number $roleId : Optional role ID filter
     * @param number $userId : Optional user ID filter
     * @return array $result : This is result
     */
   public function getAllTrainingRecords($limit, $start, $statusAmc = null, $roleId = null, $userId = null)
{
    $this->db->select('a.*, u.name as franchise_name');
    $this->db->from('tbl_amc a');
    $this->db->join('tbl_users u', 'u.userId = a.branchFranchiseAssigned', 'left');
    $this->db->where('a.currentStatus', 'Installed-Active');
    if ($statusAmc) {
        $this->db->where('a.statusAmc', $statusAmc);
    }
    if ($roleId == 14 || $roleId == 1 || $roleId == 2) {
        // Admin roles - show all
    } elseif ($roleId == 15) {
        $this->db->where('a.branchFranchiseAssigned', $userId);
    }
    $this->db->where('a.isDeleted', 0);
    $this->db->order_by('a.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}

    /**
     * This function is used to get total count of training records by role
     * @param number $roleId : Role ID
     * @return number $count : This is row count
     */
    public function getTotalTrainingRecordsCountByRole($roleId) {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->from('tbl_amc');
        return $this->db->count_all_results();
    }

    /**
     * This function is used to get training records by role
     * @param number $roleId : Role ID
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @return array $result : This is result
     */
  public function getTrainingRecordsByRole($roleId, $limit, $start) {
    $this->db->select('BaseTbl.*, userTbl.name as franchiseAssignedName');
    $this->db->from('tbl_amc as BaseTbl');
    $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.branchFranchiseAssigned', 'left');
    $this->db->where('BaseTbl.brspFranchiseAssigned', $roleId);
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
    /**
     * This function is used to get franchise number by user ID
     * @param number $userId : User ID
     * @return string $franchiseNumber : Franchise number
     */
    public function getFranchiseNumberByUserId($userId) {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }

    /**
     * This function is used to get users by franchise
     * @param string $franchiseNumber : Franchise number
     * @return array $result : This is result of the query
     */
    public function getUsersByFranchise($franchiseNumber) {
        $this->db->select('tbl_users.userId, tbl_users.name');
        $this->db->from('tbl_branches');
        $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
        $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber);
        $this->db->where('tbl_branches.isDeleted', 0);
        return $this->db->get()->result();
    }
    
    /**
     * This function is used to get total count of inactive AMC records
     * @param string $statusAmc : Optional AMC status filter
     * @param number $roleId : Optional role ID filter
     * @param number $userId : Optional user ID filter
     * @return number $count : This is row count
     */
    public function getTotalInactiveAmcCount($statusAmc = null, $roleId = null, $userId = null) {
        $this->db->from('tbl_amc a');
        $this->db->join('tbl_users u', 'u.userId = a.branchFranchiseAssigned', 'left');
        $this->db->group_start()
                 ->where('a.currentStatus !=', 'Installed-Active')
                 ->or_where('a.currentStatus', null)
                 ->group_end();
        if ($statusAmc) {
            $this->db->where('a.statusAmc', $statusAmc);
        }
        if (!in_array($roleId, [1, 2, 14])) {
            $this->db->where('a.branchFranchiseAssigned', $userId);
        }
        return $this->db->count_all_results();
    }

    /**
     * This function is used to get inactive AMC records
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @param string $statusAmc : Optional AMC status filter
     * @param number $roleId : Optional role ID filter
     * @param number $userId : Optional user ID filter
     * @return array $result : This is result
     */
  public function getAllInactiveAmcRecords($limit, $start, $statusAmc = null, $roleId = null, $userId = null) {
    $this->db->select('a.*, u.name as franchise_name');
    $this->db->from('tbl_amc a');
    $this->db->join('tbl_users u', 'u.userId = a.branchFranchiseAssigned', 'left');
    $this->db->group_start()
             ->where('a.currentStatus !=', 'Installed-Active')
             ->or_where('a.currentStatus', null)
             ->group_end();
    if ($statusAmc) {
        $this->db->where('a.statusAmc', $statusAmc);
    }
    if (!in_array($roleId, [1, 2, 14])) {
        $this->db->where('a.branchFranchiseAssigned', $userId);
    }
    $this->db->where('a.isDeleted', 0);
    $this->db->order_by('a.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}

    /**
     * This function is used to get total count of inactive AMC records by franchise
     * @param string $franchiseNumber : Franchise number
     * @param string $statusAmc : Optional AMC status filter
     * @return number $count : This is row count
     */
    public function getTotalInactiveAmcCountByFranchise($franchiseNumber, $statusAmc = null) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->group_start()
                 ->where('currentStatus !=', 'Installed-Active')
                 ->or_where('currentStatus', null)
                 ->group_end();
        if ($statusAmc) {
            $this->db->where('statusAmc', $statusAmc);
        }
        return $this->db->count_all_results('tbl_amc');
    }

    /**
     * This function is used to get inactive AMC records by franchise
     * @param string $franchiseNumber : Franchise number
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @param string $statusAmc : Optional AMC status filter
     * @return array $result : This is result
     */
 public function getInactiveAmcRecordsByFranchise($franchiseNumber, $limit, $start, $statusAmc = null) {
    $this->db->select('BaseTbl.*, userTbl.name as franchiseAssignedName');
    $this->db->from('tbl_amc as BaseTbl');
    $this->db->join('tbl_users as userTbl', 'userTbl.userId = BaseTbl.branchFranchiseAssigned', 'left');
    $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
    $this->db->group_start()
             ->where('BaseTbl.currentStatus !=', 'Installed-Active')
             ->or_where('BaseTbl.currentStatus', null)
             ->group_end();
    if ($statusAmc) {
        $this->db->where('BaseTbl.statusAmc', $statusAmc);
    }
    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.createdDtm', 'DESC'); // Sort by createdDtm
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
}
?>