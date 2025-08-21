<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Salesrecord_model (Salesrecord Model)
 * Salesrecord model class to get to handle Salesrecord related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Itwebrecords_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
  function itwebrecordsListingCount($searchText, $userId, $userRole,$searchUserId = '')
{
    $this->db->select('BaseTbl.webrecordId');
    $this->db->from('tbl_webrecords_it as BaseTbl');
    $this->db->join('tbl_users as U', 'BaseTbl.userId = U.userId', 'left');

    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.clientName LIKE '%".$searchText."%' OR BaseTbl.emailid LIKE '%".$searchText."%' OR BaseTbl.contactno LIKE '%".$searchText."%')";
        $this->db->where($likeCriteria);
    }
    if (!empty($searchUserId)) {
        $this->db->where('BaseTbl.userId', $searchUserId);
    }

    
if (!in_array($userRole, [1, 14, 22]) && !empty($franchiseNumber)) {
            $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
        }
    $this->db->where('BaseTbl.isDeleted', 0);
    return $this->db->count_all_results();
}

function itwebrecordsListing($searchText, $page, $segment, $userId, $userRole,$searchUserId = '')
{
    $this->db->select('BaseTbl.webrecordId, BaseTbl.brNumbr, BaseTbl.brName, BaseTbl.brOnboardMgr, 
                       BaseTbl.brGrowthMgr, BaseTbl.brCity, BaseTbl.brStatus, BaseTbl.webCreationStatus, 
                       BaseTbl.websiteLink,BaseTbl.websharingdate,BaseTbl.edumetaAppdate, BaseTbl.description, U.name as userName');
    $this->db->from('tbl_webrecords_it as BaseTbl');
    $this->db->join('tbl_users as U', 'BaseTbl.userId = U.userId', 'left');

    if (!empty($searchText)) {
        $likeCriteria = "(BaseTbl.clientName LIKE '%".$searchText."%' OR BaseTbl.emailid LIKE '%".$searchText."%' OR BaseTbl.contactno LIKE '%".$searchText."%')";
        $this->db->where($likeCriteria);
    }
if (!in_array($userRole, [1, 14, 22]) && !empty($franchiseNumber)) {
            $this->db->where('BaseTbl.franchiseNumber', $franchiseNumber);
        }

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.webrecordId', 'DESC');
    $this->db->limit($page, $segment);
    
    $query = $this->db->get();
    return $query->result();
}

    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
	 
	 
	 
    function addNewitwebrecords($itwebrecordsInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_webrecords_it', $itwebrecordsInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    
      function getitwebrecordsInfo($webrecordId)
    {
    $this->db->select('webrecordId,brNumbr,brName,brOnboardMgr,brGrowthMgr,brCity,brStatus,webCreationStatus,websiteLink,websharingdate,edumetaAppdate,description');
        $this->db->from('tbl_webrecords_it');
        $this->db->where('webrecordId', $webrecordId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
   /* function editfollowuprecord($FollowuprecordInfo, $followupId)
    {
        $this->db->where('followupId', $followupId);
        $this->db->update('tbl_followup_sales', $FollowuprecordInfo);
        
        return TRUE;
    }*/
	
	public function edititwebrecords($itwebrecordsInfo, $webrecordId)
{
    $this->db->where('webrecordId', $webrecordId);
    $this->db->update('tbl_webrecords_it', $itwebrecordsInfo);
    
    // Print last executed query
    /*echo $this->db->last_query();
    exit;*/
     return TRUE;
    
}
public function getAllUsers()
{
    $this->db->select('userId, name');
    $this->db->from('tbl_users');
    $this->db->where('isDeleted', 0);
    $query = $this->db->get();
    return $query->result();
}




   


	

}