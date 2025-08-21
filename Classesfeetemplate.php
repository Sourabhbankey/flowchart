<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Classesfeetemplate
 * Controller for managing classes fee templates
 * @author : [Your Name]
 * @version : 1.0
 * @since : May 2025
 */
class Classesfeetemplate extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Classesfeetemplate_model', 'classfee');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Despatch_model', 'dm');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Classesfeetemplate';
        $this->load->library('pagination');
    }

    public function index()
    {
        redirect('classesfeetemplate/classesfeetemplateListing');
    }

    public function classesfeetemplateListing()
    {
        

        $searchText = $this->security->xss_clean($this->input->post('searchText'));
        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $config = [
            'base_url' => base_url('classesfeetemplate/classesfeetemplateListing'),
            'per_page' => 10,
            'uri_segment' => 3,
            'use_page_numbers' => TRUE,
            'total_rows' => $this->classfee->classesfeetemplateListingCount($searchText),
            'full_tag_open' => '<div class="pagination-links">',
            'full_tag_close' => '</div>',
            'first_link' => 'First',
            'last_link' => 'Last',
            'next_link' => '»',
            'prev_link' => '«',
            'cur_tag_open' => '<strong>',
            'cur_tag_close' => '</strong>',
            'num_tag_open' => '',
            'num_tag_close' => ''
        ];

        if ($searchText) {
            $config['suffix'] = '?searchText=' . urlencode($searchText);
            $config['first_url'] = $config['base_url'] . '?searchText=' . urlencode($searchText);
        } else {
            $config['first_url'] = $config['base_url'];
        }

        $this->pagination->initialize($config);
        $page = $this->uri->segment(3) ?: 1;
        $offset = ($page - 1) * $config['per_page'];

        $data = [
            'feeTemplates' => $this->classfee->classesfeetemplateListing($searchText, $config['per_page'], $offset),
            'pagination' => $this->pagination->create_links(),
            'searchText' => $searchText,
            'branchDetail' => $this->bm->getBranchesFranchiseNumber(),
            'total_records' => $config['total_rows'],
            'start' => $offset + 1,
            'end' => min($offset + $config['per_page'], $config['total_rows']),
            'role' => $role
        ];

        $this->global['pageTitle'] = 'Classes Fee Template Listing';
        $this->loadViews('classesfeetemplate/list', $this->global, $data);
    }

    public function add()
    {
       

        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $data = [
            'branchDetail' => $this->bm->getBranchesFranchiseNumber(),
            'users' => $this->dm->getUser(),
            'classesfeetemplateInfo' => null,
            'role' => $role,
            'franchiseNumber' => $franchiseNumber
        ];

        $this->global['pageTitle'] = 'Add Classes Fee Template';
        $this->loadViews('classesfeetemplate/add', $this->global, $data);
    }

    public function addNewClassesfeetemplate()
    {
        

        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $this->load->library('form_validation');
      
        if ($role != 25) {
            $this->form_validation->set_rules('brspFranchiseAssigned', 'Assigned Growth Manager', 'trim|required|numeric');
        }

        if ($this->form_validation->run() == FALSE) {
            log_message('debug', 'Validation Errors: ' . validation_errors());
            $this->add();
            return;
        }

        $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
        if ($role == 25 && !in_array($franchiseNumber, $franchiseNumberArray)) {
            $this->session->set_flashdata('error', 'You can only create templates for your franchise');
            $this->add();
            return;
        }

        $franchiseNumbers = implode(',', $franchiseNumberArray);
        $classesfeetemplateInfo = [
            'brspFranchiseAssigned' => ($role != 25) ? $this->security->xss_clean($this->input->post('brspFranchiseAssigned')) : null,
            'mission' => $this->security->xss_clean($this->input->post('mission')),
            'franchiseNumber' => $franchiseNumbers,
            'brAddress' => $this->security->xss_clean($this->input->post('brAddress')),
            'branchContacNum' => $this->security->xss_clean($this->input->post('branchContacNum')),
            'formfee' => $this->security->xss_clean($this->input->post('formfee')),
            'admissionfees' => $this->security->xss_clean($this->input->post('admissionfees')),
             'toddlers' => $this->security->xss_clean($this->input->post('toddlers')),
            'playgroup' => $this->security->xss_clean($this->input->post('playgroup')),
             'nursery' => $this->security->xss_clean($this->input->post('nursery')),
            'kg1' => $this->security->xss_clean($this->input->post('kg1')),
            'kg2' => $this->security->xss_clean($this->input->post('kg2')),
             'kitcharges' => $this->security->xss_clean($this->input->post('kitcharges')),
            'offers' => $this->security->xss_clean($this->input->post('offers')),
            'additionalcharges' => $this->security->xss_clean($this->input->post('additionalcharges')),
            'activitycharges' => $this->security->xss_clean($this->input->post('activitycharges')),
            'keyhighlights' => $this->security->xss_clean($this->input->post('keyhighlights')),
            'installment1play' => $this->security->xss_clean($this->input->post('installment1play')),
            'installment2play' => $this->security->xss_clean($this->input->post('installment2play')),
            'installment3play' => $this->security->xss_clean($this->input->post('installment3play')),
            'installment4play' => $this->security->xss_clean($this->input->post('installment4play')),

            'installment1nur' => $this->security->xss_clean($this->input->post('installment1nur')),
            'installment2nur' => $this->security->xss_clean($this->input->post('installment2nur')),
            'installment3nur' => $this->security->xss_clean($this->input->post('installment3nur')),
            'installment4nur' => $this->security->xss_clean($this->input->post('installment4nur')),

            'installment1kg1' => $this->security->xss_clean($this->input->post('installment1kg1')),
            'installment2kg1' => $this->security->xss_clean($this->input->post('installment2kg1')),
            'installment3kg1' => $this->security->xss_clean($this->input->post('installment3kg1')),
            'installment4kg1' => $this->security->xss_clean($this->input->post('installment4kg1')),

            'installment1kg2' => $this->security->xss_clean($this->input->post('installment1kg2')),
            'installment2kg2' => $this->security->xss_clean($this->input->post('installment2kg2')),
            'installment3kg2' => $this->security->xss_clean($this->input->post('installment3kg2')),
            'installment4kg2' => $this->security->xss_clean($this->input->post('installment4kg2')),
            'installmentType' => $this->security->xss_clean($this->input->post('installmentType')),
            'dateof1installment' => $this->security->xss_clean($this->input->post('dateof1installment')),
            'dateof2installment' => $this->security->xss_clean($this->input->post('dateof2installment')),
            'dateof3installment' => $this->security->xss_clean($this->input->post('dateof3installment')),
            'dateof4installment' => $this->security->xss_clean($this->input->post('dateof4installment')),
            'lateFeeCharges' => $this->security->xss_clean($this->input->post('lateFeeCharges')),
            'pointstoremember' => $this->security->xss_clean($this->input->post('pointstoremember')),
            
            'createdBy' => $this->vendorId,
            'createdDtm' => date('Y-m-d H:i:s'),
            'isDeleted' => 0
        ];

        $result = $this->classfee->addNewClassesfeetemplate($classesfeetemplateInfo);

        if ($result) {
             $this->load->model('Notification_model', 'nm');

                    // Send notifications to users with roleId 19, 14, 25
                    $notificationMessage = "<strong>Class Fee template Confirmation:</strong> New Class Fee template confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId',  [1, 14, 15, 21 , 25])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_classfee_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
            $this->session->set_flashdata('success', 'Template created successfully');
        } else {
            $this->session->set_flashdata('error', 'Template creation failed');
        }

        redirect('classesfeetemplate/classesfeetemplateListing');
    }

    /*public function edit($classfeeId = NULL)
    {
        if (!$this->hasUpdateAccess() || !$classfeeId) {
            $this->loadThis();
            return;
        }

        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $template = $this->classfee->getclassesfeetemplateInfo($classfeeId);
        if (!$template) {
            $this->session->set_flashdata('error', 'Fee template not found');
            redirect('classesfeetemplate/classesfeetemplateListing');
        }
        if ($role == 25 && $template->franchiseNumber !== $franchiseNumber) {
            $this->session->set_flashdata('error', 'You can only edit templates for your franchise');
            redirect('classesfeetemplate/classesfeetemplateListing');
        }

        $data = [
            'branchDetail' => $this->bm->getBranchesFranchiseNumber(),
            'users' => $this->dm->getUser(),
            'classesfeetemplateInfo' => $template,
            'role' => $role,
            'franchiseNumber' => $franchiseNumber
        ];

        $this->global['pageTitle'] = 'Edit Classes Fee Template';
        $this->loadViews('classesfeetemplate/add', $this->global, $data);
    }*/
     function edit($classfeeId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($classfeeId == null)
            {
                redirect('scheduledinstallation/scheduledinstallationListing');
            }
            
            $data['classesfeetemplateInfo'] = $this->classfee->getclassesfeetemplateInfo($classfeeId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit ';
            
            $this->loadViews("classesfeetemplate/edit", $this->global, $data, NULL);
        }
    }
    

    public function editClassesfeetemplate()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
            return;
        }

        $classfeeId = $this->input->post('classfeeId');
        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        if ($role == 25) {
            $template = $this->classfee->getclassesfeetemplateInfo($classfeeId);
            if (!$template || $template->franchiseNumber !== $franchiseNumber) {
                $this->session->set_flashdata('error', 'You can only update templates for your franchise');
                redirect('classesfeetemplate/classesfeetemplateListing');
            }
        }

        $this->load->library('form_validation');
       
        $this->form_validation->set_rules('pointstoremember', 'Points to Remember', 'trim|required|max_length[1024]');
        

        if ($this->form_validation->run() == FALSE) {
            $this->edit($classfeeId);
            return;
        }

        $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
        if ($role == 25 && !in_array($franchiseNumber, $franchiseNumberArray)) {
            $this->session->set_flashdata('error', 'You can only update templates for your franchise');
            $this->edit($classfeeId);
            return;
        }

        $franchiseNumbers = implode(',', $franchiseNumberArray);
        $classesfeetemplateInfo = [
            
            'mission' => $this->security->xss_clean($this->input->post('mission')),
            
            'formfee' => $this->security->xss_clean($this->input->post('formfee')),
            'admissionfees' => $this->security->xss_clean($this->input->post('admissionfees')),
            'toddlers' => $this->security->xss_clean($this->input->post('toddlers')),
            'playgroup' => $this->security->xss_clean($this->input->post('playgroup')),
             'nursery' => $this->security->xss_clean($this->input->post('nursery')),
            'kg1' => $this->security->xss_clean($this->input->post('kg1')),
            'kg2' => $this->security->xss_clean($this->input->post('kg2')),
             'kitcharges' => $this->security->xss_clean($this->input->post('kitcharges')),
            
            'offers' => $this->security->xss_clean($this->input->post('offers')),
            'additionalcharges' => $this->security->xss_clean($this->input->post('additionalcharges')),
            'activitycharges' => $this->security->xss_clean($this->input->post('activitycharges')),
            'keyhighlights' => $this->security->xss_clean($this->input->post('keyhighlights')),


             'installment1play' => $this->security->xss_clean($this->input->post('installment1play')),
            'installment2play' => $this->security->xss_clean($this->input->post('installment2play')),
            'installment3play' => $this->security->xss_clean($this->input->post('installment3play')),
            'installment4play' => $this->security->xss_clean($this->input->post('installment4play')),

            'installment1nur' => $this->security->xss_clean($this->input->post('installment1nur')),
            'installment2nur' => $this->security->xss_clean($this->input->post('installment2nur')),
            'installment3nur' => $this->security->xss_clean($this->input->post('installment3nur')),
            'installment4nur' => $this->security->xss_clean($this->input->post('installment4nur')),

            'installment1kg1' => $this->security->xss_clean($this->input->post('installment1kg1')),
            'installment2kg1' => $this->security->xss_clean($this->input->post('installment2kg1')),
            'installment3kg1' => $this->security->xss_clean($this->input->post('installment3kg1')),
            'installment4kg1' => $this->security->xss_clean($this->input->post('installment4kg1')),

            'installment1kg2' => $this->security->xss_clean($this->input->post('installment1kg2')),
            'installment2kg2' => $this->security->xss_clean($this->input->post('installment2kg2')),
            'installment3kg2' => $this->security->xss_clean($this->input->post('installment3kg2')),
            'installment4kg2' => $this->security->xss_clean($this->input->post('installment4kg2')),
            'installmentType' => $this->security->xss_clean($this->input->post('installmentType')),
            'dateof1installment' => $this->security->xss_clean($this->input->post('dateof1installment')),
            'dateof2installment' => $this->security->xss_clean($this->input->post('dateof2installment')),
            'dateof3installment' => $this->security->xss_clean($this->input->post('dateof3installment')),
            'dateof4installment' => $this->security->xss_clean($this->input->post('dateof4installment')),
            'lateFeeCharges' => $this->security->xss_clean($this->input->post('lateFeeCharges')),
            'pointstoremember' => $this->security->xss_clean($this->input->post('pointstoremember')),
            
           
            'updatedBy' => $this->vendorId,
            'updatedDtm' => date('Y-m-d H:i:s')
        ];

        $result = $this->classfee->editclassesfeetemplate($classesfeetemplateInfo, $classfeeId);

        if ($result) {
             
                    // Send notifications to users with roleId 19, 14, 25
                    $notificationMessage = "<strong>Class Fee template Confirmation:</strong> Update Class Fee template confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId',  [1, 14, 15, 21 , 25])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_classfee_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
            $this->session->set_flashdata('success', 'Template updated successfully');
        } else {
            $this->session->set_flashdata('error', 'Template update failed');
        }

        redirect('classesfeetemplate/classesfeetemplateListing');
    }

    public function view($classfeeId = NULL)
    {
        if (!$this->hasListAccess() || !$classfeeId) {
            $this->loadThis();
            return;
        }

        $role = $this->session->userdata('role') ?: 25;
        $franchiseNumber = ($role == 25) ? $this->session->userdata('franchiseNumber') : null;

        $data['classesfeetemplateInfo'] = $this->classfee->getclassesfeetemplateInfo($classfeeId);
        if (!$data['classesfeetemplateInfo']) {
            $this->session->set_flashdata('error', 'Fee template not found');
            redirect('classesfeetemplate/classesfeetemplateListing');
        }

        if ($role == 25 && $data['classesfeetemplateInfo']->franchiseNumber !== $franchiseNumber) {
            $this->session->set_flashdata('error', 'You can only view templates for your franchise');
            redirect('classesfeetemplate/classesfeetemplateListing');
        }

        $this->global['pageTitle'] = 'View Classes Fee Template';
        $this->loadViews('classesfeetemplate/view', $this->global, $data);
    }

    public function fetchAssignedUsers()
    {
        $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
        $users = $this->classfee->getUsersByFranchise($franchiseNumber);

        $options = '<option value="">Select Role</option>';
        foreach ($users as $user) {
            $options .= '<option value="' . htmlspecialchars($user->userId) . '">' . htmlspecialchars($user->name) . '</option>';
        }

        $this->output->set_content_type('text/html')->set_output($options);
    }

    public function fetchFranchiseData()
    {
        $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
        $selectedUserId = $this->security->xss_clean($this->input->post('selectedUserId'));

        $users = $this->classfee->getUsersByFranchise($franchiseNumber);
        $franchiseData = $this->classfee->getFranchiseData($franchiseNumber);

        $response = [
            'status' => 'success',
            'userIds' => '',
            'html' => 'No Growth Managers assigned',
            'franchiseData' => $franchiseData ? [
                'brAddress' => htmlspecialchars($franchiseData->brAddress),
                'branchContacNum' => htmlspecialchars($franchiseData->branchContacNum)
            ] : null
        ];

        if ($users) {
            $userNames = [];
            $userIds = [];
            foreach ($users as $user) {
                $userNames[] = htmlspecialchars($user->name);
                $userIds[] = $user->userId;
            }
            $response['userIds'] = implode(',', $userIds);
            $response['html'] = implode(', ', $userNames);
        }

        if (!$franchiseData) {
            $response['franchiseMessage'] = 'No franchise data found for this franchise';
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}