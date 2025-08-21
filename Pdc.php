<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Pdc (TaskController)
 * Pdc Class to control task related operations.
 * @author : Ashish Singh
 * @version : 1.5
 * @since : 31 May 2024
 */
class Pdc extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pdc_model', 'pd');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->load->library('pagination');
        $this->module = 'Pdc';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('pdc/pdcListing');
    }
    
    /**
     * This function is used to load the Pdc list
     */
    public function pdcListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
        $franchiseFilter = $this->input->get('franchiseNumber');
        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
        $config = array();
        $config['base_url'] = base_url('pdc/pdcListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        if ($userRole == '14' || $userRole == '1' || $userRole == '16' || $userRole == '24' || $userRole == '13') {
            if ($franchiseFilter) {
                $config['total_rows'] = $this->pd->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                $data['records'] = $this->pd->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->pd->getTotalTrainingRecordsCount();
                $data['records'] = $this->pd->getAllTrainingRecords($config['per_page'], $page);
            }
        } else if ($userRole == '15') {
            $config['total_rows'] = $this->pd->getTotalTrainingRecordsCountByRole($userId);
            $data['records'] = $this->pd->getTrainingRecordsByRole($userId, $config['per_page'], $page);
        } else {
            $franchiseNumber = $this->pd->getFranchiseNumberByUserId($userId);
            if ($franchiseNumber) {
                if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                    $config['total_rows'] = $this->pd->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->pd->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->pd->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->pd->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                }
            } else {
                $data['records'] = [];
            }
        }

        $serial_no = $page + 1;
        $this->pagination->initialize($config);
        $data["links"] = $this->pagination->

create_links();
        $data["start"] = $page + 1;
        $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];
        $data['pagination'] = $this->pagination->create_links();
        $data["franchiseFilter"] = $franchiseFilter;
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $data["serial_no"] = $serial_no;
        $this->loadViews("pdc/list", $this->global, $data, NULL);
    }

    function add()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $data['users'] = $this->bm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Pdc';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("pdc/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewPdc()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('pdcAmount','PDC Amount','trim|required|max_length[256]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            $this->form_validation->set_rules('statusOfPDc','Status of PDC','trim|required|max_length[1024]');
            $this->form_validation->set_rules('cancellationReason','Cancellation Reason','trim|callback_validate_cancellation_reason');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $pdcNumber = $this->security->xss_clean($this->input->post('pdcNumber'));
                $dateOfpdcSubmission = $this->security->xss_clean($this->input->post('dateOfpdcSubmission'));
                $dateOfclearance = $this->security->xss_clean($this->input->post('dateOfclearance'));
                $statusOfPDc = $this->security->xss_clean($this->input->post('statusOfPDc'));
                $pdcAmount = $this->security->xss_clean($this->input->post('pdcAmount'));
                $dateofCheck = $this->security->xss_clean($this->input->post('dateofCheck'));
                $bankName = $this->security->xss_clean($this->input->post('bankName'));
                $cancellationReason = $this->security->xss_clean($this->input->post('cancellationReason'));
                $description = $this->security->xss_clean($this->input->post('description'));
                 $pdcTitle = $this->security->xss_clean($this->input->post('pdcTitle'));
                $franchiseNumbers = implode(',',$franchiseNumberArray);

                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time().'-'.$_FILES["file"]["name"];
                rename($_FILES["file"]["tmp_name"], $destination);
                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination,$storeFolder);
                $result_arr = $s3Result->toArray();
                $s3_file_link = !empty($result_arr['ObjectURL']) ? [$result_arr['ObjectURL']] : [''];
                $s3files = implode(',', $s3_file_link);

                $pdcInfo = array(
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'pdcNumber' => $pdcNumber,
                    'dateOfpdcSubmission' => $dateOfpdcSubmission,
                    'dateOfclearance' => $dateOfclearance,
                    'statusOfPDc' => $statusOfPDc,
                    'pdcAttach' => $s3files,
                    'pdcAmount' => $pdcAmount,
                    'franchiseNumber' => $franchiseNumbers,
                    'description' => $description,
                    'dateofCheck' => $dateofCheck,
                    'bankName' => $bankName,
                    'pdcTitle' => $pdcTitle,
                    'cancellationReason' => $cancellationReason,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );
                
                $result = $this->pd->addNewPdc($pdcInfo);
//print_r($pdcInfo);exit;
              if ($result > 0) {
    $this->load->model('Notification_model');

    // ✅ Notify Assigned BRSP Franchise User
    if (!empty($brspFranchiseAssigned)) {
        $notificationMessage = "<strong>PDC Assigned</strong>: A new PDC has been assigned to you.";
        if ($statusOfPDc === 'Cancellation') {
            $notificationMessage .= " Reason: $cancellationReason";
        }
        $this->Notification_model->add_pdc_notification($brspFranchiseAssigned, $notificationMessage, $result);
    }

    // ✅ Notify Each Franchise Branch + User + Admins
    if (!empty($franchiseNumberArray)) {
        foreach ($franchiseNumberArray as $franchiseNumber) {
            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
            if (!empty($branchDetail)) {
                // 1. Send Email to Branch
                $to = $branchDetail->officialEmailID;
                $subject = "Alert - eduMETA THE i-SCHOOL Assign New PDC";
                $message = "Dear {$branchDetail->applicantName}, ";
                $message .= "You have been assigned a new PDC. By: {$this->session->userdata('name')}. ";
                if ($statusOfPDc === 'Cancellation') {
                    $message .= "Reason: $cancellationReason. ";
                }
                $message .= "Please visit the portal.";
                $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                mail($to, $subject, $message, $headers);

                // 2. Notify Franchise User
                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                if (!empty($franchiseUser)) {
                    $notificationMessage = "<strong>PDC Assigned</strong>: A new PDC has been assigned to you.";
                    if ($statusOfPDc === 'Cancellation') {
                        $notificationMessage .= " Reason: $cancellationReason";
                    }
                    $this->Notification_model->add_pdc_notification($franchiseUser->userId, $notificationMessage, $result);
                }

                // 3. Notify Admin Users (roleId 1, 14)
                $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                foreach ($adminUsers as $adminUser) {
                    $adminNotification = "<strong>PDC Notification</strong>: New PDC assigned for Franchise #$franchiseNumber.";
                    if ($statusOfPDc === 'Cancellation') {
                        $adminNotification .= " Cancellation Reason: $cancellationReason";
                    }
                    $this->Notification_model->add_pdc_notification($adminUser->userId, $adminNotification, $result);
                }
            }
        }
    }
                    $this->session->set_flashdata('success', 'New PDC created successfully');
                } else {
                    $this->session->set_flashdata('error', 'PDC creation failed');
                }
                
                redirect('pdc/pdcListing');
            }
        }
    }

    /**
     * Custom validation for cancellation reason
     */
    public function validate_cancellation_reason($str)
    {
        $statusOfPDc = $this->input->post('statusOfPDc');
        if ($statusOfPDc === 'Cancellation' && empty($str)) {
            $this->form_validation->set_message('validate_cancellation_reason', 'The Cancellation Reason field is required when Status is Cancellation.');
            return FALSE;
        }
        return TRUE;
    }

    /**
     * This function is used to load task edit information
     */
    function edit($pdcId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($pdcId == null)
            {
                redirect('pdc/pdcListing');
            }
            
            $data['pdcInfo'] = $this->pd->getPdcInfo($pdcId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->global['pageTitle'] = 'CodeInsect : Edit Pdc';
            
            $this->loadViews("pdc/edit", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to edit the user information
     */
 public function editPdc()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $pdcId = $this->input->post('pdcId');

        $this->form_validation->set_rules('pdcAmount', 'PDC Amount', 'trim|required|max_length[256]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');
        $this->form_validation->set_rules('statusOfPDc', 'Status of PDC', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($pdcId);
        } else {
            $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
            $pdcNumber = $this->security->xss_clean($this->input->post('pdcNumber'));
            $dateOfpdcSubmission = $this->security->xss_clean($this->input->post('dateOfpdcSubmission'));
            $dateOfclearance = $this->security->xss_clean($this->input->post('dateOfclearance'));
            $statusOfPDc = $this->security->xss_clean($this->input->post('statusOfPDc'));
            $pdcAmount = $this->security->xss_clean($this->input->post('pdcAmount'));
            $dateofCheck = $this->security->xss_clean($this->input->post('dateofCheck'));
            $bankName = $this->security->xss_clean($this->input->post('bankName'));
            $cancellationReason = $this->security->xss_clean($this->input->post('cancellationReason'));
            $description = $this->security->xss_clean($this->input->post('description'));
            $pdcTitle = $this->security->xss_clean($this->input->post('pdcTitle'));

            $pdcInfo = array(
                'brspFranchiseAssigned' => $brspFranchiseAssigned,
                'pdcNumber' => $pdcNumber,
                'dateOfpdcSubmission' => $dateOfpdcSubmission,
                'dateOfclearance' => $dateOfclearance,
                'statusOfPDc' => $statusOfPDc,
                'pdcAmount' => $pdcAmount,
                'dateofCheck' => $dateofCheck,
                'bankName' => $bankName,
                'cancellationReason' => $cancellationReason,
                'description' => $description,
                'pdcTitle' => $pdcTitle,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->pd->editPdc($pdcInfo, $pdcId);

            if ($result == true) {
                $this->load->model('Notification_model');

                // ✅ Notify Assigned BRSP Franchise User
                if (!empty($brspFranchiseAssigned)) {
                    $notificationMessage = "<strong>PDC Updated</strong>: A PDC record has been updated for you.";
                    if ($statusOfPDc === 'Cancellation') {
                        $notificationMessage .= " Reason: $cancellationReason";
                    }
                    $this->Notification_model->add_pdc_notification($brspFranchiseAssigned, $notificationMessage, $pdcId);
                }

                // ✅ Get Franchise Number(s)
                $franchiseNumber = $this->pd->getFranchiseNumberByPdcId($pdcId);
                if (!empty($franchiseNumber)) {
                    $franchiseNumberArray = explode(',', $franchiseNumber);

                    foreach ($franchiseNumberArray as $franchiseNum) {
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNum);
                        if (!empty($branchDetail)) {
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL PDC Record Updated";
                            $message = "Dear {$branchDetail->applicantName}, ";
                            $message .= "PDC record has been updated. By: {$this->session->userdata('name')}. ";
                            if ($statusOfPDc === 'Cancellation') {
                                $message .= "Reason for cancellation: $cancellationReason. ";
                            }
                            $message .= "Please visit the portal for details.";
                            $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                            mail($to, $subject, $message, $headers);
                        }

                        // 2. Notify Franchise User
                        $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNum);
                        if (!empty($franchiseUser)) {
                            $notificationMessage = "<strong>PDC Updated</strong>: A PDC record has been updated.";
                            if ($statusOfPDc === 'Cancellation') {
                                $notificationMessage .= " Reason: $cancellationReason";
                            }
                            $this->Notification_model->add_pdc_notification($franchiseUser->userId, $notificationMessage, $pdcId);
                        }

                        // 3. Notify Admin Users (roleId 1, 14)
                        $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                        foreach ($adminUsers as $adminUser) {
                            $adminNotification = "<strong>PDC Notification</strong>: PDC updated for Franchise #$franchiseNum.";
                            if ($statusOfPDc === 'Cancellation') {
                                $adminNotification .= " Cancellation Reason: $cancellationReason";
                            }
                            $this->Notification_model->add_pdc_notification($adminUser->userId, $adminNotification, $pdcId);
                        }
                    }
                }

                $this->session->set_flashdata('success', 'PDC updated successfully');
            } else {
                $this->session->set_flashdata('error', 'PDC updation failed');
            }

            redirect('pdc/pdcListing');
        }
    }
}


    public function fetchAssignedUsers()
    {
        $franchiseNumber = $this->input->post('franchiseNumber');
        $users = $this->pd->getUsersByFranchise($franchiseNumber);
        $options = '<option value="0">Select Role</option>';
        foreach ($users as $user) {
            $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
        }
        echo $options;
    }
}