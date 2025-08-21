<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Onboardingfirstform_model (Onboarding First Form Model)
 * Model class to handle onboarding first form related data
 * @author : Ashish
 * @version : 1.1
 * @since : 11 Nov 2024
 */
class Onboardingfirstform_model extends CI_Model
{
    /**
     * Get the onboarding form listing count
     * @param string $searchText : Optional search text
     * @return number $count : Row count
     */
    public function onboardingfirstformListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_onboardfirstformdata as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.full_name LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * Get the onboarding form listing with pagination
     * @param string $searchText : Optional search text
     * @param number $offset : Pagination offset
     * @param number $limit : Number of records per page
     * @return array $result : Result of the query
     */
    public function onboardingfirstformListing($searchText, $offset, $limit)
    {
        $this->db->select('BaseTbl.onboardFirstFormId, BaseTbl.full_name, BaseTbl.email, BaseTbl.contact, BaseTbl.franchiseNumber, BaseTbl.gender, BaseTbl.alternate_contact, BaseTbl.dob, BaseTbl.communication_address, BaseTbl.city, BaseTbl.state, BaseTbl.pincode, BaseTbl.pan_card_no, BaseTbl.aadhar_card, BaseTbl.nationality, BaseTbl.permanent_address, BaseTbl.pcity, BaseTbl.pstate, BaseTbl.ppincode, BaseTbl.marital_status, BaseTbl.spouse, BaseTbl.number_of_children, BaseTbl.highest_education, BaseTbl.qualifications, BaseTbl.university, BaseTbl.year_of_qualification, BaseTbl.certificate_course_award, BaseTbl.year_received, BaseTbl.awarded_by, BaseTbl.english_spoken, BaseTbl.other_skills, BaseTbl.mathematics_proficiency, BaseTbl.english_written_proficiency, BaseTbl.current_employer_name, BaseTbl.current_position, BaseTbl.current_date_joined, BaseTbl.current_business_address, BaseTbl.current_monthly_income, BaseTbl.previous_employer_name, BaseTbl.previous_monthly_income, BaseTbl.previous_date_joined, BaseTbl.previous_business_address, BaseTbl.previous_last_position, BaseTbl.previous_date_left, BaseTbl.previous_reasons_for_leaving, BaseTbl.particular_select, BaseTbl.father_full_name, BaseTbl.father_pan_card, BaseTbl.father_nationality, BaseTbl.father_aadhar_card, BaseTbl.father_mobile, BaseTbl.father_position, BaseTbl.father_organization, BaseTbl.father_business_address, BaseTbl.father_monthly_income, BaseTbl.spouse_dob, BaseTbl.spouse_position, BaseTbl.spouse_organization, BaseTbl.spouse_business_address, BaseTbl.spouse_monthly_income, BaseTbl.average_monthly_income, BaseTbl.applied_before, BaseTbl.application_details, BaseTbl.ideal_centre_reason, BaseTbl.source_info, BaseTbl.current_centre, BaseTbl.reason_for_applying, BaseTbl.aadhar_file, BaseTbl.pan_file, BaseTbl.created_at');
        $this->db->from('tbl_onboardfirstformdata as BaseTbl');

        if (!empty($searchText)) {
            $this->db->like('BaseTbl.full_name', $searchText);
        } 

        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.created_at', 'DESC'); // Sort by creation date, newest first
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Add a new onboarding form to the system
     * @param array $onboardingfirstformInfo : Form data to insert
     * @return number $insert_id : Last inserted ID
     */
    public function addNewOnboardingfirstform($onboardingfirstformInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_onboardfirstformdata', $onboardingfirstformInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * Get onboarding form information by ID
     * @param number $onboardFirstFormId : Form ID
     * @return object $result : Form information
     */
    public function getOnboardingfirstformInfo($onboardFirstFormId)
    {
        $this->db->select('*');
        $this->db->from('tbl_onboardfirstformdata');
        $this->db->where('onboardFirstFormId', $onboardFirstFormId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();

        return $query->row();
    }
    
    /**
     * Update onboarding form information
     * @param array $onboardingfirstformInfo : Updated form information
     * @param number $onboardFirstFormId : Form ID
     * @return boolean : TRUE on success
     */
    public function editOnboardingfirstform($onboardingfirstformInfo, $onboardFirstFormId)
    {
        $this->db->where('onboardFirstFormId', $onboardFirstFormId);
        $this->db->update('tbl_onboardfirstformdata', $onboardingfirstformInfo);
        
        return TRUE;
    }
}