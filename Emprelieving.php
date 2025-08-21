<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Emprelieving (EmprelievingController)
 * Emprelieving Class to control Emprelieving related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 17 May 2025
 */
class Emprelieving extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Emprelieving_model', 'emprel');
        $this->isLoggedIn();
        $this->module = 'Emprelieving';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('emprelieving/emprelievingListing');
    }
    
    /**
     * This function is used to load the emprelieving list
     */
    function emprelievingListing()
    {
        if(!$this->hasListAccess())
        {
            $this->loadThis();
        }
        else
        {
            $searchText = '';
            if(!empty($this->input->post('searchText'))) {
                $searchText = $this->security->xss_clean($this->input->post('searchText'));
            }
            $data['searchText'] = $searchText;
            
            $this->load->library('pagination');
            
            $count = $this->emprel->emprelievingListingCount($searchText);

            $returns = $this->paginationCompress ( "emprelievingListing/", $count, 10 );
            
            $data['records'] = $this->emprel->emprelievingListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Emprelieving';
            
            $this->loadViews("emprelieving/list", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->global['pageTitle'] = 'CodeInsect : Add New Emprelieving';

            $this->loadViews("emprelieving/add", $this->global, NULL, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewEmprelieving()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('empName','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $empName = $this->security->xss_clean($this->input->post('empName'));
                $empEmailid = $this->security->xss_clean($this->input->post('empEmailid'));
                $reportmngName = $this->security->xss_clean($this->input->post('reportmngName'));
                $department = $this->security->xss_clean($this->input->post('department'));
                $communireportmngName = $this->security->xss_clean($this->input->post('communireportmngName'));
                $resigTermidate = $this->security->xss_clean($this->input->post('resigTermidate'));
                $empContactdetails = $this->security->xss_clean($this->input->post('empContactdetails'));
                $formal_resignation_or_termination_mail = $this->security->xss_clean($this->input->post('formal_resignation_or_termination_mail'));
                $acceptance_by_reporting_manager = $this->security->xss_clean($this->input->post('acceptance_by_reporting_manager'));
                $notice_period_served = $this->security->xss_clean($this->input->post('notice_period_served'));
                $surrender_of_sim_card = $this->security->xss_clean($this->input->post('surrender_of_sim_card'));
                $surrender_of_id_card = $this->security->xss_clean($this->input->post('surrender_of_id_card'));
                $surrender_of_mobile_phones = $this->security->xss_clean($this->input->post('surrender_of_mobile_phones'));
                $surrender_of_laptop = $this->security->xss_clean($this->input->post('surrender_of_laptop'));
                $relieving_letter_issued = $this->security->xss_clean($this->input->post('relieving_letter_issued'));
                $no_dues_certificate_issued = $this->security->xss_clean($this->input->post('no_dues_certificate_issued'));
                $closure_of_official_mail_id = $this->security->xss_clean($this->input->post('closure_of_official_mail_id'));
                $surrender_of_all_official_id_and_credentials = $this->security->xss_clean($this->input->post('surrender_of_all_official_id_and_credentials'));
                $removal_from_all_official_sheets = $this->security->xss_clean($this->input->post('removal_from_all_official_sheets'));
                $handover_of_all_offline_data_or_sheets = $this->security->xss_clean($this->input->post('handover_of_all_offline_data_or_sheets'));
                $removal_from_all_whatsapp_groups = $this->security->xss_clean($this->input->post('removal_from_all_whatsapp_groups'));
                $closure_of_employee_whatsapp_group = $this->security->xss_clean($this->input->post('closure_of_employee_whatsapp_group'));
                $submission_of_signed_copy_of_relieving_and_other_documents = $this->security->xss_clean($this->input->post('submission_of_signed_copy_of_relieving_and_other_documents'));
                $clearance_form_reporting_manager = $this->security->xss_clean($this->input->post('clearance_form_reporting_manager'));
                $exit_interview = $this->security->xss_clean($this->input->post('exit_interview'));
                $final_fnf_mail = $this->security->xss_clean($this->input->post('final_fnf_mail'));
                $acknowledgment_on_fnf_mail_and_last_salary_issued = $this->security->xss_clean($this->input->post('acknowledgment_on_fnf_mail_and_last_salary_issued'));
                $description = $this->security->xss_clean($this->input->post('description'));




                
                $emprelievingInfo = array('empName'=>$empName, 'empEmailid'=>$empEmailid, 'reportmngName'=>$reportmngName, 'department'=>$department, 'communireportmngName'=>$communireportmngName, 'resigTermidate'=>$resigTermidate, 'empContactdetails'=>$empContactdetails, 'empEmailid'=>$empEmailid,'reportmngName'=>$reportmngName, 'formal_resignation_or_termination_mail'=>$formal_resignation_or_termination_mail, 'acceptance_by_reporting_manager'=>$acceptance_by_reporting_manager, 'notice_period_served'=>$notice_period_served, 'surrender_of_sim_card'=>$surrender_of_sim_card, 'surrender_of_id_card'=>$surrender_of_id_card, 'surrender_of_mobile_phones'=>$surrender_of_mobile_phones, 'surrender_of_laptop'=>$surrender_of_laptop, 'relieving_letter_issued'=>$relieving_letter_issued, 'no_dues_certificate_issued'=>$no_dues_certificate_issued, 'closure_of_official_mail_id'=>$closure_of_official_mail_id, 'surrender_of_all_official_id_and_credentials'=>$surrender_of_all_official_id_and_credentials, 'removal_from_all_official_sheets'=>$removal_from_all_official_sheets, 'handover_of_all_offline_data_or_sheets'=>$handover_of_all_offline_data_or_sheets, 'removal_from_all_whatsapp_groups'=>$removal_from_all_whatsapp_groups, 'closure_of_employee_whatsapp_group'=>$closure_of_employee_whatsapp_group, 'submission_of_signed_copy_of_relieving_and_other_documents'=>$submission_of_signed_copy_of_relieving_and_other_documents, 'clearance_form_reporting_manager'=>$clearance_form_reporting_manager, 'exit_interview'=>$exit_interview, 'final_fnf_mail'=>$final_fnf_mail, 'acknowledgment_on_fnf_mail_and_last_salary_issued'=>$acknowledgment_on_fnf_mail_and_last_salary_issued, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->emprel->addNewEmprelieving($emprelievingInfo);
                
                if($result > 0) {
                    $this->session->set_flashdata('success', 'New Employee Relieving created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Employee Relieving creation failed');
                }
                
                redirect('emprelieving/emprelievingListing');
            }
        }
    }
    function view($emprelievingId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($emprelievingId == null)
            {
                redirect('emprelieving/emprelievingListing');
            }
            
            $data['emprelievingInfo'] = $this->emprel->getEmprelievingInfo($emprelievingId);

            $this->global['pageTitle'] = 'CodeInsect : View Emprelieving';
            
            $this->loadViews("emprelieving/view", $this->global, $data, NULL);
        }
    }

    
    /**
     * This function is used load emprelieving edit information
     * @param number $emprelievingId : Optional : This is emprelieving id
     */
    function edit($emprelievingId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($emprelievingId == null)
            {
                redirect('emprelieving/emprelievingListing');
            }
            
            $data['emprelievingInfo'] = $this->emprel->getEmprelievingInfo($emprelievingId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Employee Relieving';
            
            $this->loadViews("emprelieving/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editEmprelieving()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $emprelievingId = $this->input->post('emprelievingId');
            
            $this->form_validation->set_rules('empName','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($emprelievingId);
            }
            else
            {
                $empName = $this->security->xss_clean($this->input->post('empName'));
                $empEmailid = $this->security->xss_clean($this->input->post('empEmailid'));
                $reportmngName = $this->security->xss_clean($this->input->post('reportmngName'));
                $department = $this->security->xss_clean($this->input->post('department'));
                $communireportmngName = $this->security->xss_clean($this->input->post('communireportmngName'));
                $resigTermidate = $this->security->xss_clean($this->input->post('resigTermidate'));
                $empContactdetails = $this->security->xss_clean($this->input->post('empContactdetails'));
                $formal_resignation_or_termination_mail = $this->security->xss_clean($this->input->post('formal_resignation_or_termination_mail'));
                $acceptance_by_reporting_manager = $this->security->xss_clean($this->input->post('acceptance_by_reporting_manager'));
                $notice_period_served = $this->security->xss_clean($this->input->post('notice_period_served'));
                $surrender_of_sim_card = $this->security->xss_clean($this->input->post('surrender_of_sim_card'));
                $surrender_of_id_card = $this->security->xss_clean($this->input->post('surrender_of_id_card'));
                $surrender_of_mobile_phones = $this->security->xss_clean($this->input->post('surrender_of_mobile_phones'));
                $surrender_of_laptop = $this->security->xss_clean($this->input->post('surrender_of_laptop'));
                $relieving_letter_issued = $this->security->xss_clean($this->input->post('relieving_letter_issued'));
                $no_dues_certificate_issued = $this->security->xss_clean($this->input->post('no_dues_certificate_issued'));
                $closure_of_official_mail_id = $this->security->xss_clean($this->input->post('closure_of_official_mail_id'));
                $surrender_of_all_official_id_and_credentials = $this->security->xss_clean($this->input->post('surrender_of_all_official_id_and_credentials'));
                $removal_from_all_official_sheets = $this->security->xss_clean($this->input->post('removal_from_all_official_sheets'));
                $handover_of_all_offline_data_or_sheets = $this->security->xss_clean($this->input->post('handover_of_all_offline_data_or_sheets'));
                $removal_from_all_whatsapp_groups = $this->security->xss_clean($this->input->post('removal_from_all_whatsapp_groups'));
                $closure_of_employee_whatsapp_group = $this->security->xss_clean($this->input->post('closure_of_employee_whatsapp_group'));
                $submission_of_signed_copy_of_relieving_and_other_documents = $this->security->xss_clean($this->input->post('submission_of_signed_copy_of_relieving_and_other_documents'));
                $clearance_form_reporting_manager = $this->security->xss_clean($this->input->post('clearance_form_reporting_manager'));
                $exit_interview = $this->security->xss_clean($this->input->post('exit_interview'));
                $final_fnf_mail = $this->security->xss_clean($this->input->post('final_fnf_mail'));
                $acknowledgment_on_fnf_mail_and_last_salary_issued = $this->security->xss_clean($this->input->post('acknowledgment_on_fnf_mail_and_last_salary_issued'));
                $description = $this->security->xss_clean($this->input->post('description'));
                
                $emprelievingInfo = array('empName'=>$empName, 'empEmailid'=>$empEmailid, 'reportmngName'=>$reportmngName, 'department'=>$department, 'communireportmngName'=>$communireportmngName, 'resigTermidate'=>$resigTermidate, 'empContactdetails'=>$empContactdetails, 'formal_resignation_or_termination_mail'=>$formal_resignation_or_termination_mail, 'acceptance_by_reporting_manager'=>$acceptance_by_reporting_manager, 'notice_period_served'=>$notice_period_served, 'surrender_of_sim_card'=>$surrender_of_sim_card, 'surrender_of_id_card'=>$surrender_of_id_card, 'surrender_of_mobile_phones'=>$surrender_of_mobile_phones, 'surrender_of_laptop'=>$surrender_of_laptop, 'relieving_letter_issued'=>$relieving_letter_issued, 'no_dues_certificate_issued'=>$no_dues_certificate_issued, 'closure_of_official_mail_id'=>$closure_of_official_mail_id, 'surrender_of_all_official_id_and_credentials'=>$surrender_of_all_official_id_and_credentials, 'removal_from_all_official_sheets'=>$removal_from_all_official_sheets, 'handover_of_all_offline_data_or_sheets'=>$handover_of_all_offline_data_or_sheets, 'removal_from_all_whatsapp_groups'=>$removal_from_all_whatsapp_groups, 'closure_of_employee_whatsapp_group'=>$closure_of_employee_whatsapp_group, 'submission_of_signed_copy_of_relieving_and_other_documents'=>$submission_of_signed_copy_of_relieving_and_other_documents, 'clearance_form_reporting_manager'=>$clearance_form_reporting_manager, 'exit_interview'=>$exit_interview, 'final_fnf_mail'=>$final_fnf_mail, 'acknowledgment_on_fnf_mail_and_last_salary_issued'=>$acknowledgment_on_fnf_mail_and_last_salary_issued, 'description'=>$description, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->emprel->editEmprelieving($emprelievingInfo, $emprelievingId);
                //print_r($emprelievingInfo);exit;
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Employee Relieving updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Employee Relieving updation failed');
                }
                
                redirect('emprelieving/emprelievingListing');
            }
        }
    }
}

?>