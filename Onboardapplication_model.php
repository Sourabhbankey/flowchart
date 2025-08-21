<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Investors_model (onboardapplication Model)
 * onboardapplication model class to get to handle onboardapplication related data 
 * @author : Ashish
 * @version : 1.1
 * @since : 11 Nov 2024
 */
class Onboardapplication_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function onboardapplicationListingCount($searchText)
    {
        $this->db->select('BaseTbl.frAppFormId,BaseTbl.franchiseNumber, BaseTbl.full_name, BaseTbl.email, BaseTbl.dob,BaseTbl.comm_address, BaseTbl.city, BaseTbl.state, BaseTbl.pincode,BaseTbl.gender, BaseTbl.alternate_contact_no, BaseTbl.contact_person_number, BaseTbl.branch_location,BaseTbl.branch_area, BaseTbl.current_school_name, BaseTbl.year_founded, BaseTbl.current_school_address,BaseTbl.current_strength, BaseTbl.total_experience, BaseTbl.purpose_opening, BaseTbl.skills_experience,BaseTbl.current_occupation, BaseTbl.vision_with_edumeta, BaseTbl.heard_about_edumeta, BaseTbl.additional_info,BaseTbl.franchise_owner, BaseTbl.org_type, BaseTbl.franchise_applicant, BaseTbl.gstin,BaseTbl.gsttype,BaseTbl.father_name, BaseTbl.permanent_address, BaseTbl.father_contact_no, BaseTbl.branch_full_address,BaseTbl.spouse_name, BaseTbl.spouse_contact_no, BaseTbl.comm_current_address, BaseTbl.map_location,BaseTbl.payment_mode, BaseTbl.amount, BaseTbl.reference_id, BaseTbl.payment_remark,BaseTbl.payment_date, BaseTbl.pan_card_photo_path, BaseTbl.aadhar_front_photo_path, BaseTbl.aadhar_back_photo_path,BaseTbl.passport_photo_path, BaseTbl.passport_photo_path, BaseTbl.payment_screenshot_path, BaseTbl.proposed_setup_date,BaseTbl.advertising_plan, BaseTbl.proposed_inauguration_date, BaseTbl.declaname, BaseTbl.sodo,BaseTbl.decsodoname, BaseTbl.nominated, BaseTbl.clientname, BaseTbl.nomibranch,BaseTbl.nomidist, BaseTbl.nomistate, BaseTbl.created_at');
    $this->db->from('tbl_franch_applicationform as BaseTbl');
        
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.full_name LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
  
 
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
 public function onboardapplicationListing($searchText, $offset, $limit)
{
    $this->db->select('BaseTbl.frAppFormId,BaseTbl.franchiseNumber, BaseTbl.full_name, BaseTbl.email, BaseTbl.dob,BaseTbl.comm_address, BaseTbl.city, BaseTbl.state, BaseTbl.pincode,BaseTbl.gender, BaseTbl.alternate_contact_no, BaseTbl.contact_person_number, BaseTbl.branch_location,BaseTbl.branch_area, BaseTbl.current_school_name, BaseTbl.year_founded, BaseTbl.current_school_address,BaseTbl.current_strength, BaseTbl.total_experience, BaseTbl.purpose_opening, BaseTbl.skills_experience,BaseTbl.current_occupation, BaseTbl.vision_with_edumeta, BaseTbl.heard_about_edumeta, BaseTbl.additional_info,BaseTbl.franchise_owner, BaseTbl.org_type, BaseTbl.franchise_applicant, BaseTbl.gstin,BaseTbl.gsttype,BaseTbl.father_name, BaseTbl.permanent_address, BaseTbl.father_contact_no, BaseTbl.branch_full_address,BaseTbl.spouse_name, BaseTbl.spouse_contact_no, BaseTbl.comm_current_address, BaseTbl.map_location,BaseTbl.payment_mode, BaseTbl.amount, BaseTbl.reference_id, BaseTbl.payment_remark,BaseTbl.payment_date, BaseTbl.pan_card_photo_path, BaseTbl.aadhar_front_photo_path, BaseTbl.aadhar_back_photo_path,BaseTbl.passport_photo_path, BaseTbl.passport_photo_path, BaseTbl.payment_screenshot_path, BaseTbl.proposed_setup_date,BaseTbl.advertising_plan, BaseTbl.proposed_inauguration_date, BaseTbl.declaname, BaseTbl.sodo,BaseTbl.decsodoname, BaseTbl.nominated, BaseTbl.clientname, BaseTbl.nomibranch,BaseTbl.nomidist, BaseTbl.nomistate, BaseTbl.created_at');
    $this->db->from('tbl_franch_applicationform as BaseTbl');

    if (!empty($searchText)) {
        $this->db->like('BaseTbl.full_name', $searchText);
    }

    $this->db->where('BaseTbl.isDeleted', 0);
    $this->db->order_by('BaseTbl.frAppFormId', 'DESC');

    // Correct order: limit first, then offset
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}


    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewonboardapplication($onboardapplicationInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_franch_applicationform', $onboardapplicationInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getonboardapplicationInfo($frAppFormId)
    {
        $this->db->select('*');
        $this->db->from('tbl_franch_applicationform');
        $this->db->where('frAppFormId', $frAppFormId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editonboardapplication($onboardapplicationInfo, $frAppFormId)
    {
        $this->db->where('frAppFormId', $frAppFormId);
        $this->db->update('tbl_franch_applicationform', $onboardapplicationInfo);
        
        return TRUE;
    }
} 