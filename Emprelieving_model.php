<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Emprelieving_model (Emprelieving Model)
 * Emprelieving model class to get to handle Emprelieving related data 
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 12 May 2025
 */
class Emprelieving_model extends CI_Model
{
    /**
     * This function is used to get the booking listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function emprelievingListingCount($searchText)
    {
        $this->db->select('BaseTbl.emprelievingId, BaseTbl.empName, BaseTbl.department, BaseTbl.communireportmngName, BaseTbl.resigTermidate, BaseTbl.empContactdetails, BaseTbl.empEmailid, BaseTbl.reportmngName, BaseTbl.formal_resignation_or_termination_mail, BaseTbl.acceptance_by_reporting_manager, BaseTbl.notice_period_served, BaseTbl.surrender_of_sim_card, BaseTbl.surrender_of_id_card, BaseTbl.surrender_of_mobile_phones, BaseTbl.surrender_of_laptop, BaseTbl.relieving_letter_issued, BaseTbl.no_dues_certificate_issued, BaseTbl.closure_of_official_mail_id, BaseTbl.surrender_of_all_official_id_and_credentials, BaseTbl.removal_from_all_official_sheets, BaseTbl.handover_of_all_offline_data_or_sheets, BaseTbl.removal_from_all_whatsapp_groups, BaseTbl.closure_of_employee_whatsapp_group, BaseTbl.submission_of_signed_copy_of_relieving_and_other_documents, BaseTbl.clearance_form_reporting_manager, BaseTbl.exit_interview, BaseTbl.final_fnf_mail, BaseTbl.acknowledgment_on_fnf_mail_and_last_salary_issued, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_employeeRelieving as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.empName LIKE '%".$searchText."%')";
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
    function emprelievingListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.emprelievingId, BaseTbl.empName, BaseTbl.department, BaseTbl.communireportmngName, BaseTbl.resigTermidate, BaseTbl.empContactdetails, BaseTbl.empEmailid, BaseTbl.reportmngName, BaseTbl.formal_resignation_or_termination_mail, BaseTbl.acceptance_by_reporting_manager, BaseTbl.notice_period_served, BaseTbl.surrender_of_sim_card, BaseTbl.surrender_of_id_card, BaseTbl.surrender_of_mobile_phones, BaseTbl.surrender_of_laptop, BaseTbl.relieving_letter_issued, BaseTbl.no_dues_certificate_issued, BaseTbl.closure_of_official_mail_id, BaseTbl.surrender_of_all_official_id_and_credentials, BaseTbl.removal_from_all_official_sheets, BaseTbl.handover_of_all_offline_data_or_sheets, BaseTbl.removal_from_all_whatsapp_groups, BaseTbl.closure_of_employee_whatsapp_group, BaseTbl.submission_of_signed_copy_of_relieving_and_other_documents, BaseTbl.clearance_form_reporting_manager, BaseTbl.exit_interview, BaseTbl.final_fnf_mail, BaseTbl.acknowledgment_on_fnf_mail_and_last_salary_issued, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_employeeRelieving as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.empName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.emprelievingId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewEmprelieving($emprelievingInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_employeeRelieving', $emprelievingInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getEmprelievingInfo($emprelievingId)
    {
        $this->db->select('emprelievingId, empName, department, communireportmngName, resigTermidate, empContactdetails, empEmailid, reportmngName, formal_resignation_or_termination_mail, acceptance_by_reporting_manager, notice_period_served, surrender_of_sim_card, surrender_of_id_card, surrender_of_mobile_phones, surrender_of_laptop, relieving_letter_issued, no_dues_certificate_issued, closure_of_official_mail_id, surrender_of_all_official_id_and_credentials, removal_from_all_official_sheets, handover_of_all_offline_data_or_sheets, removal_from_all_whatsapp_groups, closure_of_employee_whatsapp_group, submission_of_signed_copy_of_relieving_and_other_documents, clearance_form_reporting_manager, exit_interview, final_fnf_mail, acknowledgment_on_fnf_mail_and_last_salary_issued, description');
        $this->db->from('tbl_employeeRelieving');
        $this->db->where('emprelievingId', $emprelievingId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the booking information
     * @param array $bookingInfo : This is booking updated information
     * @param number $bookingId : This is booking id
     */
    function editEmprelieving($emprelievingInfo, $emprelievingId)
    {
        $this->db->where('emprelievingId', $emprelievingId);
        $this->db->update('tbl_employeeRelieving', $emprelievingInfo);
        
        return TRUE;
    }
}