<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Admenquiry_model (Support Model)
 * Support model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Admenquiry_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function admenquiryListingCount($searchText)
    {
        $this->db->select('BaseTbl.enqid, BaseTbl.studentName, BaseTbl.class, BaseTbl.birthday, BaseTbl.age, BaseTbl.city,  BaseTbl.state, BaseTbl.fathername, BaseTbl.fatheremail, BaseTbl.fatherMobile_no, BaseTbl.mothername, BaseTbl.motheremail, BaseTbl.motherMobile_no, BaseTbl.feeOffered, BaseTbl.addressResidencial, BaseTbl.addressPerma, BaseTbl.franchiseNumber, BaseTbl.remark, BaseTbl.createdDtm');
        $this->db->from('tbl_admission_enquiry as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.studentName LIKE '%".$searchText."%')";
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
    function admenquiryListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.enqid, BaseTbl.studentName, BaseTbl.class, BaseTbl.birthday, BaseTbl.age, BaseTbl.city,  BaseTbl.state, BaseTbl.fathername, BaseTbl.fatheremail, BaseTbl.fatherMobile_no, BaseTbl.mothername, BaseTbl.motheremail, BaseTbl.motherMobile_no, BaseTbl.feeOffered, BaseTbl.addressResidencial, BaseTbl.addressPerma, BaseTbl.franchiseNumber, BaseTbl.remark, BaseTbl.createdDtm');
        $this->db->from('tbl_admission_enquiry as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.studentName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.enqid', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewAdmenquiry($staffInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_admission_enquiry', $staffInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get task information by id
     * @param number $enqid : This is training id
     * @return array $result : This is training information
     */
    function getadmenquiryInfo($enqid)
    {
        $this->db->select('*');
        $this->db->from('tbl_admission_enquiry');
        $this->db->where('enqid', $enqid);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editAdmenquiry($staffInfo, $enqid)
    {
        $this->db->where('enqid', $enqid);
        $this->db->update('tbl_admission_enquiry', $staffInfo);
        
        return TRUE;
    }
    /**
     * This function is used to get the user  information
     * @return array $result : This is result of the query
     */
    function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.studentName, userTbl.roleId, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
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
        $query = $this->db->get('tbl_admission_enquiry');
        return $query->result();
    }

    public function getattachmentRecordsByFranchise($franchiseNumber) {
        // Fetch records from tbl_onbord_frm for the specific franchise
        $this->db->where('franchiseNumber', $franchiseNumber);
        
        $query = $this->db->get('tbl_admission_enquiry');
        return $query->result();
    }

    
    
    
    public function get_count($franchiseFilter = null) {
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_admission_enquiry');
}

public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_admission_enquiry');
}
public function get_data($limit, $start, $franchiseFilter = null) {
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    $query = $this->db->get('tbl_admission_enquiry');
    return $query->result();
}

public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    $query = $this->db->get('tbl_admission_enquiry');
    return $query->result();
}
public function getTotalTrainingRecordsCountByFranchise($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->where('isDeleted', 0);
        $this->db->from('tbl_admission_enquiry');
        return $this->db->count_all_results();
    }
    
public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start) {
    $this->db->select('enqid, studentName, class, birthday, age, city, state, fathername, fatheremail, fatherMobile_no, mothername, motheremail, motherMobile_no, feeOffered, addressResidencial, addressPerma, franchiseNumber, remark, createdDtm, editBy');
    $this->db->from('tbl_admission_enquiry');
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->where('isDeleted', 0);
    $this->db->order_by('createdDtm', 'DESC');
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
    
   public function getTotalTrainingRecordsCount() {
        $this->db->from('tbl_admission_enquiry');
        $this->db->where('isDeleted', 0);
        return $this->db->count_all_results();
    }
    
 public function getAllTrainingRecords($limit, $start) {
    $this->db->select('enqid, studentName, class, birthday, age, city, state, fathername, fatheremail, fatherMobile_no, mothername, motheremail, motherMobile_no, feeOffered, addressResidencial, addressPerma, franchiseNumber, remark, createdDtm, editBy');
    $this->db->from('tbl_admission_enquiry');
    $this->db->where('isDeleted', 0);
    $this->db->order_by('createdDtm', 'DESC');
    $this->db->limit($limit, $start);
    $query = $this->db->get();
    return $query->result();
}
   public function getTotalTrainingRecordsCountByRole($roleId) {
        $this->db->where('brspFranchiseAssigned', $roleId);
        $this->db->where('isDeleted', 0);
        $this->db->from('tbl_admission_enquiry');
        return $this->db->count_all_results();
    }
  public function getTrainingRecordsByRole($roleId, $limit, $start) {
    $this->db->select('enqid, studentName, class, birthday, age, city, state, fathername, fatheremail, fatherMobile_no, mothername, motheremail, motherMobile_no, feeOffered, addressResidencial, addressPerma, franchiseNumber, remark, createdDtm, editBy');
    $this->db->from('tbl_admission_enquiry');
    $this->db->where('brspFranchiseAssigned', $roleId);
    $this->db->where('isDeleted', 0);
    $this->db->order_by('createdDtm', 'DESC');
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
        $this->db->select('userId, name');
        $this->db->from('users');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $query = $this->db->get();
        return $query->result();
    }
}