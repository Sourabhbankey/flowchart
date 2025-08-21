<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Admissiondetails_model (Support Model)
 * Support model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Admissiondetailsnew25_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function admissiondetailsListingCount($searchText)
    {
        $this->db->select('BaseTbl.admid, BaseTbl.name, BaseTbl.enrollNum, BaseTbl.class, BaseTbl.dateOfAdmission, BaseTbl.program, BaseTbl.birthday, BaseTbl.age, BaseTbl.gender, BaseTbl.fathername, BaseTbl.fatheremail, BaseTbl.fatherMobile_no, BaseTbl.mothername, BaseTbl.motheremail, BaseTbl.motherMobile_no, BaseTbl.bloodGroup, BaseTbl.motherTongue, BaseTbl.religion, BaseTbl.caste, BaseTbl.city,  BaseTbl.state, BaseTbl.totalFee, BaseTbl.address, BaseTbl.previousSchool, BaseTbl.franchiseNumber, BaseTbl.remark, BaseTbl.createdDtm');
        $this->db->from('tbl_admission_details_2526 as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.name LIKE '%".$searchText."%')";
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
    function admissiondetailsListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.admid, BaseTbl.name, BaseTbl.enrollNum, BaseTbl.class, BaseTbl.dateOfAdmission, BaseTbl.program, BaseTbl.birthday, BaseTbl.age, BaseTbl.gender, BaseTbl.fathername, BaseTbl.fatheremail, BaseTbl.fatherMobile_no, BaseTbl.mothername, BaseTbl.motheremail, BaseTbl.motherMobile_no, BaseTbl.bloodGroup, BaseTbl.motherTongue, BaseTbl.religion, BaseTbl.caste, BaseTbl.city,  BaseTbl.state, BaseTbl.totalFee, BaseTbl.address, BaseTbl.previousSchool, BaseTbl.franchiseNumber, BaseTbl.remark, BaseTbl.createdDtm');
        $this->db->from('tbl_admission_details_2526 as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.name LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.admid', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
  public function addNewAdmissiondetails($admissiondetailsInfo)
{
    $this->db->insert('tbl_admission_details_2526', $admissiondetailsInfo);

    if ($this->db->affected_rows() > 0) {
        return $this->db->insert_id(); // ✅ Return last inserted ID
    } else {
        return false; // ❌ Return false if insertion failed
    }
}



    /**
     * This function used to get task information by id
     * @param number $admid : This is training id
     * @return array $result : This is training information
     */
    function getadmissiondetailsInfo($admid)
    {
        $this->db->select('admid, name, enrollNum, class, dateOfAdmission, program, birthday, age, gender, fathername, fatheremail, fatherMobile_no, mothername, motheremail, motherMobile_no, bloodGroup, motherTongue, religion, caste, city, state, totalFee, address, previousSchool, franchiseNumber, remark');
        $this->db->from('tbl_admission_details_2526');
        $this->db->where('admid', $admid);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    public function getStaffByFranchise($franchiseNumber) {
        $this->db->select('staffid, name');
        $this->db->from('tbl_staff_details');
        $this->db->where('franchiseNumber', trim($franchiseNumber)); // Trim to avoid whitespace
        // $this->db->where('isDeleted', 0); // Uncomment if table has isDeleted
        // $this->db->where('status', 1); // Uncomment if table has status
        $query = $this->db->get();
    
        log_message('debug', 'getStaffByFranchise Query: ' . $this->db->last_query());
    
        return $query->num_rows() > 0 ? $query->result() : [];
    }
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editAdmissiondetails($staffInfo, $admid)
    {
        $this->db->where('admid', $admid);
        $this->db->update('tbl_admission_details_2526', $staffInfo);
        
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
	
///code done by yashi 15 nov

 function getUser()
    {
        /*---Growth-Support--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 15);
        $query = $this->db->get();
        return $query->result();
    }

public function getAllacattachmentRecords() {
        // Fetch all records from tbl_onbord_frm
        $query = $this->db->get('tbl_admission_details_2526');
        return $query->result();
    }

    public function getattachmentRecordsByFranchise($franchiseNumber) {
        // Fetch records from tbl_onbord_frm for the specific franchise
        $this->db->where('franchiseNumber', $franchiseNumber);
        
        $query = $this->db->get('tbl_admission_details_2526');
        return $query->result();
    }

    
    
    
    public function get_count($franchiseFilter = null) {
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_admission_details_2526');
}

public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_admission_details_2526');
}
public function get_data($limit, $start, $franchiseFilter = null) {
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
     $this->db->order_by('admid', 'DESC');
    $query = $this->db->get('tbl_admission_details_2526');
    
    return $query->result();
}

public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
     $this->db->order_by('admid', 'DESC');
    $query = $this->db->get('tbl_admission_details_2526');
    
    return $query->result();
}
public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
    $this->db->where('franchiseNumber', $franchiseNumber);
      $this->db->order_by('admid', 'DESC');
    $this->db->from('tbl_admission_details_2526');
   
    return $this->db->count_all_results();
    }
    
     public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->limit($limit, $start);
     $this->db->order_by('admid', 'DESC');
    $query = $this->db->get('tbl_admission_details_2526');
    
    return $query->result();
    }
    
     public function getTotalTrainingRecordsCount() {
    return $this->db->count_all('tbl_admission_details_2526');
     $this->db->order_by('admid', 'DESC');
    }
    
     public function getAllTrainingRecords($limit, $start) {
    $this->db->limit($limit, $start);
     $this->db->order_by('admid', 'DESC');
    $query = $this->db->get('tbl_admission_details_2526');
    
    return $query->result();
    }
    public function getTotalTrainingRecordsCountByRole($roleId) {
    $this->db->where('brspFranchiseAssigned', $roleId);
    $this->db->from('tbl_admission_details_2526');
     $this->db->order_by('admid', 'DESC');
    return $this->db->count_all_results();
    }
    public function getTrainingRecordsByRole($roleId, $limit, $start) {
    $this->db->where('brspFranchiseAssigned', $roleId);
    $this->db->limit($limit, $start);
    $this->db->order_by('admid', 'DESC');
    $query = $this->db->get('tbl_admission_details_2526');
   //  $this->db->order_by('admid', 'DESC');
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


// sourabh code 12-04-2025

    public function getUsersByFranchise($franchiseNumber) {
        $this->db->select('tbl_users.userId, tbl_users.name');
        $this->db->from('tbl_branches');
        $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
        $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber);
        $this->db->where('tbl_branches.isDeleted', 0);
        $query = $this->db->get();
    
        // Log query for debugging
        log_message('debug', 'getUsersByFranchise Query: ' . $this->db->last_query());
    
        return $query->result();
    }

    public function getTotalAdmissionsByFranchiseAll($franchiseFilter = '') {
    $this->db->select('franchiseNumber, class, COUNT(*) as admission_count');
    $this->db->from('tbl_admission_details_2526');

    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }

    $this->db->group_by(['franchiseNumber', 'class']);
    $query = $this->db->get();

    $result = $query->result_array();

    $data = [];

    foreach ($result as $row) {
        $franchise = $row['franchiseNumber'];
        $class = strtoupper($row['class']); // Match your keys like KG1, NURSERY etc.
        $count = $row['admission_count'];

        if (!isset($data[$franchise])) {
            $data[$franchise] = [
               'total' => 0,
                'Play Group' => 0,
                'Nursery' => 0,
                'KG-1' => 0,
                'KG-2' => 0,
                '1st' => 0,
                '2nd' => 0,
                '3rd' => 0,
                '4th' => 0,
                '5th' => 0,
                '6th' => 0,
                '7th' => 0,
                '8th' => 0,
                '9th' => 0,
                '10th' => 0,
                '11th' => 0,
                '12th' => 0,
            ];
        }

        $data[$franchise][$class] = $count;
        $data[$franchise]['total'] += $count;
    }

    return $data;
}
public function getAllAdmissionsForSummary($franchiseFilter = '') {
    $this->db->select('franchiseNumber, class, COUNT(*) as totalCount');
    $this->db->from('tbl_admission_details_2526');

    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }

    $this->db->group_by(['franchiseNumber', 'class']);
    $query = $this->db->get();
    return $query->result();
}
}