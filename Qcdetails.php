<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Qcdetails (QcdetailsController)
 * Qcdetails Class to control Qcdetails related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 12 May 2025
 */
class Qcdetails extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Qcdetails_model', 'qccheck');
        $this->load->model('Branches_model', 'bm');
         $this->load->model('Notification_model');
        $this->isLoggedIn();
        $this->module = 'Qcdetails';
    }

    public function index()
    {
        redirect('qcdetails/qcdetailsListing');
    }
    
    public function qcdetailsListing()
    {
        if(!$this->hasListAccess())
        {
            $this->loadThis();
        }
        else
        {
            $searchText = $this->security->xss_clean($this->input->post('searchText') ?? '');
            $data['searchText'] = $searchText;
            
            $this->load->library('pagination');
            
            $count = $this->qccheck->qcdetailsListingCount($searchText);
            $returns = $this->paginationCompress("qcdetailsListing/", $count, 10);
            
            $data['records'] = $this->qccheck->qcdetailsListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect: Qcdetails';
            $this->loadViews("qcdetails/list", $this->global, $data, NULL);
        }
    }

    public function add()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->global['pageTitle'] = 'CodeInsect: Add New Qcdetails';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("qcdetails/add", $this->global, $data, NULL);
        }
    }
    
  public function addNewQcdetails()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $this->load->model('Notification_model');

            $franchiseName = $this->security->xss_clean($this->input->post('franchiseName'));
            $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
            $branchLocation = $this->security->xss_clean($this->input->post('branchLocation'));
            $branchFranchiseAssigned = $this->security->xss_clean($this->input->post('branchFranchiseAssigned'));
            $date_of_inspection = $this->security->xss_clean($this->input->post('date_of_inspection'));
            $date_of_installation = $this->security->xss_clean($this->input->post('date_of_installation'));
            $attended_by_owner = $this->security->xss_clean($this->input->post('attended_by_owner'));
            $attended_by_admin = $this->security->xss_clean($this->input->post('attended_by_admin'));
            $strength_playgroup = $this->security->xss_clean($this->input->post('strength_playgroup'));
            $strength_nursery = $this->security->xss_clean($this->input->post('strength_nursery'));
            $strength_kg1 = $this->security->xss_clean($this->input->post('strength_kg1'));
            $strength_kg2 = $this->security->xss_clean($this->input->post('strength_kg2'));
            $day_care = $this->security->xss_clean($this->input->post('day_care'));
            $evening_class = $this->security->xss_clean($this->input->post('evening_class'));
            $classes_running_till = $this->security->xss_clean($this->input->post('classes_running_till'));
            $front_branding = $this->security->xss_clean($this->input->post('front_branding'));
            $status = $this->security->xss_clean($this->input->post('status'));
            $uniform_condition_ok = $this->security->xss_clean($this->input->post('uniform_condition_ok'));
            $uniform_condition_remarks = $this->security->xss_clean($this->input->post('uniform_condition_remarks'));
            $books_curriculum_ok = $this->security->xss_clean($this->input->post('books_curriculum_ok'));
            $other_inappropriate_points = $this->security->xss_clean($this->input->post('other_inappropriate_points'));
            $meeting_minutes_client = $this->security->xss_clean($this->input->post('meeting_minutes_client'));

            // File Uploads
            $s3_file_link = [];
            $s3_file_link1 = [];

            // First file input (file[])
            if (isset($_FILES['file']) && !empty($_FILES['file']['name'][0])) {
                foreach ($_FILES['file']['name'] as $key => $name) {
                    if (is_uploaded_file($_FILES['file']['tmp_name'][$key])) {
                        $tmpName = $_FILES['file']['tmp_name'][$key];
                        $dir = dirname($tmpName);
                        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . basename($name);
                        if (move_uploaded_file($tmpName, $destination)) {
                            $storeFolder = 'attachements';
                            $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                            $result_arr = $s3Result->toArray();
                            $s3_file_link[] = $result_arr['ObjectURL'] ?? '';
                        } else {
                            $s3_file_link[] = '';
                        }
                    }
                }
            } else {
                $s3_file_link[] = '';
            }

            // Second file input (file1[])
            if (isset($_FILES['file1']) && !empty($_FILES['file1']['name'][0])) {
                foreach ($_FILES['file1']['name'] as $key => $name) {
                    if (is_uploaded_file($_FILES['file1']['tmp_name'][$key])) {
                        $tmpName = $_FILES['file1']['tmp_name'][$key];
                        $dir = dirname($tmpName);
                        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . basename($name);
                        if (move_uploaded_file($tmpName, $destination)) {
                            $storeFolder = 'attachements';
                            $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                            $result_arr = $s3Result->toArray();
                            $s3_file_link1[] = $result_arr['ObjectURL'] ?? '';
                        } else {
                            $s3_file_link1[] = '';
                        }
                    }
                }
            } else {
                $s3_file_link1[] = '';
            }

            $s3files = implode(',', $s3_file_link);
            $s3files1 = implode(',', $s3_file_link1);

            $qcdetailsInfo = [
                'franchiseName' => $franchiseName,
                'franchiseNumber' => $franchiseNumber,
                'branchLocation' => $branchLocation,
                'branchFranchiseAssigned' => $branchFranchiseAssigned,
                'date_of_inspection' => $date_of_inspection,
                'date_of_installation' => $date_of_installation,
                'attended_by_owner' => $attended_by_owner,
                'attended_by_admin' => $attended_by_admin,
                'strength_playgroup' => $strength_playgroup,
                'strength_nursery' => $strength_nursery,
                'strength_kg1' => $strength_kg1,
                'strength_kg2' => $strength_kg2,
                'day_care' => $day_care,
                'evening_class' => $evening_class,
                'classes_running_till' => $classes_running_till,
                'front_branding' => $front_branding,
                'status' => $status,
                'uniform_condition_ok' => $uniform_condition_ok,
                'uniform_condition_remarks' => $uniform_condition_remarks,
                'books_curriculum_ok' => $books_curriculum_ok,
                'other_inappropriate_points' => $other_inappropriate_points,
                'meeting_minutes_client' => $meeting_minutes_client,
                'qcattachmentS3File' => $s3files,
                'qcscrnshotattachmentS3File' => $s3files1,
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
            ];

            $result = $this->qccheck->addNewQcdetails($qcdetailsInfo);

            if ($result > 0) {
                // Notify Assigned Franchise User
                if (!empty($branchFranchiseAssigned)) {
                    $notificationMessage = "<strong>QC Details</strong>: A new QC record has been assigned to you.";
                    $this->Notification_model->add_qc_notification($branchFranchiseAssigned, $notificationMessage, $result);
                }

                // Notify all franchise-related users and admins
                $franchiseNumberArray = is_array($franchiseNumber) ? $franchiseNumber : explode(',', $franchiseNumber);
                if (!empty($franchiseNumberArray)) {
                    foreach ($franchiseNumberArray as $franchiseNum) {
                        $franchiseNum = trim($franchiseNum);
                        if (empty($franchiseNum)) continue;

                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNum);
                        if (!empty($branchDetail)) {
                            // Send Email
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL: New QC Details Submitted";
                            $message = 'Dear ' . $branchDetail->applicantName . ', ';
                            $message .= 'New QC details have been added. BY - ' . $this->session->userdata("name") . '. ';
                            $message .= 'Please visit the portal.';
                            $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                            mail($to, $subject, $message, $headers);

                            // Notify Franchise User
                            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNum);
                            if (!empty($franchiseUser)) {
                                $notificationMessage = "<strong>QC Details</strong>: A new QC record has been assigned to you.";
                                $this->Notification_model->add_qc_notification($franchiseUser->userId, $notificationMessage, $result);
                            }

                            // Notify Admins
                            $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                            if (!empty($adminUsers)) {
                                foreach ($adminUsers as $adminUser) {
                                    $this->Notification_model->add_qc_notification($adminUser->userId, "<strong>QC Details</strong>: A new QC record has been submitted.", $result);
                                }
                            }
                        }
                    }
                }

                $this->session->set_flashdata('success', 'New QC Details created successfully');
            } else {
                $this->session->set_flashdata('error', 'QC Details creation failed');
            }

            redirect('qcdetails/qcdetailsListing');
        }
    }
}

    
    public function view($qcId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($qcId == null)
            {
                redirect('qcdetails/qcdetailsListing');
            }
            
            $data['qcdetailsInfo'] = $this->qccheck->getQcdetailsInfo($qcId);
            $this->global['pageTitle'] = 'CodeInsect: View Qcdetails';
            $this->loadViews("qcdetails/view", $this->global, $data, NULL);
        }
    }

    public function edit($qcId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($qcId == null)
            {
                redirect('qcdetails/qcdetailsListing');
            }
            
            $data['qcdetailsInfo'] = $this->qccheck->getQcdetailsInfo($qcId);
            $this->global['pageTitle'] = 'CodeInsect: Edit QC Details';
            $this->loadViews("qcdetails/edit", $this->global, $data, NULL);
        }
    }
    
   public function editQcdetails()
    {
        if (!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            $qcId = $this->input->post('qcId');
            $this->form_validation->set_rules('meeting_minutes_client', 'Minutes of meeting', 'trim|required|max_length[1024]');
            
            if ($this->form_validation->run() == FALSE)
            {
                $this->edit($qcId);
            }
            else
            {
                $franchiseName = $this->security->xss_clean($this->input->post('franchiseName'));
                $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $branchLocation = $this->security->xss_clean($this->input->post('branchLocation'));
                $branchFranchiseAssigned = $this->security->xss_clean($this->input->post('branchFranchiseAssigned'));
                $date_of_inspection = $this->security->xss_clean($this->input->post('date_of_inspection'));
                $date_of_installation = $this->security->xss_clean($this->input->post('date_of_installation'));
                $attended_by_owner = $this->security->xss_clean($this->input->post('attended_by_owner'));
                $attended_by_admin = $this->security->xss_clean($this->input->post('attended_by_admin'));
                $strength_playgroup = $this->security->xss_clean($this->input->post('strength_playgroup'));
                $strength_nursery = $this->security->xss_clean($this->input->post('strength_nursery'));
                $strength_kg1 = $this->security->xss_clean($this->input->post('strength_kg1'));
                $strength_kg2 = $this->security->xss_clean($this->input->post('strength_kg2'));
                $day_care = $this->security->xss_clean($this->input->post('day_care'));
                $evening_class = $this->security->xss_clean($this->input->post('evening_class'));
                $classes_running_till = $this->security->xss_clean($this->input->post('classes_running_till'));
                $front_branding = $this->security->xss_clean($this->input->post('front_branding'));
                $status = $this->security->xss_clean($this->input->post('status'));
                $uniform_condition_ok = $this->security->xss_clean($this->input->post('uniform_condition_ok'));
                $uniform_condition_remarks = $this->security->xss_clean($this->input->post('uniform_condition_remarks'));
                $books_curriculum_ok = $this->security->xss_clean($this->input->post('books_curriculum_ok'));
                $other_inappropriate_points = $this->security->xss_clean($this->input->post('other_inappropriate_points'));
                $meeting_minutes_client = $this->security->xss_clean($this->input->post('meeting_minutes_client'));

                $s3_file_link = [];
                $s3_file_link1 = [];

                if (isset($_FILES['file']) && !empty($_FILES['file']['name'][0])) {
                    foreach ($_FILES['file']['name'] as $key => $name) {
                        if (is_uploaded_file($_FILES['file']['tmp_name'][$key])) {
                            $tmpName = $_FILES['file']['tmp_name'][$key];
                            $dir = dirname($tmpName);
                            $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . basename($name);

                            if (move_uploaded_file($tmpName, $destination)) {
                                $storeFolder = 'attachements';
                                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                                $result_arr = $s3Result->toArray();
                                $s3_file_link[] = $result_arr['ObjectURL'] ?? '';
                            } else {
                                $s3_file_link[] = '';
                            }
                        }
                    }
                } else {
                    $s3_file_link[] = '';
                }

                $s3files = implode(',', $s3_file_link);

                if (isset($_FILES['file1']) && !empty($_FILES['file1']['name'][0])) {
                    foreach ($_FILES['file1']['name'] as $key => $name) {
                        if (is_uploaded_file($_FILES['file1']['tmp_name'][$key])) {
                            $tmpName = $_FILES['file1']['tmp_name'][$key];
                            $dir = dirname($tmpName);
                            $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . basename($name);

                            if (move_uploaded_file($tmpName, $destination)) {
                                $storeFolder = 'attachements';
                                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                                $result_arr = $s3Result->toArray();
                                $s3_file_link1[] = $result_arr['ObjectURL'] ?? '';
                            } else {
                                $s3_file_link1[] = '';
                            }
                        }
                    }
                } else {
                    $s3_file_link1[] = '';
                }

                $s3files1 = implode(',', $s3_file_link1);

                $qcdetailsInfo = array(
                    'franchiseName' => $franchiseName,
                    'franchiseNumber' => $franchiseNumber,
                    'branchLocation' => $branchLocation,
                    'branchFranchiseAssigned' => $branchFranchiseAssigned,
                    'date_of_inspection' => $date_of_inspection,
                    'date_of_installation' => $date_of_installation,
                    'attended_by_owner' => $attended_by_owner,
                    'attended_by_admin' => $attended_by_admin,
                    'strength_playgroup' => $strength_playgroup,
                    'strength_nursery' => $strength_nursery,
                    'strength_kg1' => $strength_kg1,
                    'strength_kg2' => $strength_kg2,
                    'day_care' => $day_care,
                    'evening_class' => $evening_class,
                    'classes_running_till' => $classes_running_till,
                    'front_branding' => $front_branding,
                    'status' => $status,
                    'uniform_condition_ok' => $uniform_condition_ok,
                    'uniform_condition_remarks' => $uniform_condition_remarks,
                    'books_curriculum_ok' => $books_curriculum_ok,
                    'other_inappropriate_points' => $other_inappropriate_points,
                    'meeting_minutes_client' => $meeting_minutes_client,
                    'qcattachmentS3File' => $s3files,
                    'qcscrnshotattachmentS3File' => $s3files1,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );
                
                $result = $this->qccheck->editQcdetails($qcdetailsInfo, $qcId);
                
               if ($result > 0) {
    // Notify Assigned Franchise User
    if (!empty($branchFranchiseAssigned)) {
        $notificationMessage = "<strong>QC Details</strong>: QC record has been updated.";
        $this->Notification_model->add_qc_notification($branchFranchiseAssigned, $notificationMessage, $qcId);
    }

    // Notify all franchise-related users and admins
    $franchiseNumberArray = is_array($franchiseNumber) ? $franchiseNumber : explode(',', $franchiseNumber);
    if (!empty($franchiseNumberArray)) {
        foreach ($franchiseNumberArray as $franchiseNum) {
            $franchiseNum = trim($franchiseNum);
            if (empty($franchiseNum)) continue;

            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNum);
            if (!empty($branchDetail)) {
                // Send Email
                $to = $branchDetail->officialEmailID;
                $subject = "Alert - eduMETA THE i-SCHOOL: QC Details Updated";
                $message = 'Dear ' . $branchDetail->applicantName . ', ';
                $message .= 'QC details have been updated by ' . $this->session->userdata("name") . '. ';
                $message .= 'Please visit the portal.';
                $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                mail($to, $subject, $message, $headers);

                // Notify Franchise User
                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNum);
                if (!empty($franchiseUser)) {
                    $notificationMessage = "<strong>QC Details</strong>: QC record has been updated.";
                    $this->Notification_model->add_qc_notification($franchiseUser->userId, $notificationMessage, $qcId);
                }

                // Notify Admins
                $adminUsers = $this->bm->getUsersByRoles([1, 14]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $this->Notification_model->add_qc_notification($adminUser->userId, "<strong>QC Details</strong>: QC record has been updated.", $qcId);
                    }
                }
            }
        }
    }

    $this->session->set_flashdata('success', 'QC Details updated successfully.');
} else {
    $this->session->set_flashdata('error', 'QC Details update failed.');
}

                
                redirect('qcdetails/qcdetailsListing');
            }
        }
    }

    public function fetchAssignedUsers()
    {
        $franchiseNumber = $this->input->post('franchiseNumber');
        $managers = $this->qccheck->getManagersByFranchise($franchiseNumber);
        $franchiseData = $this->qccheck->getFranchiseDetails($franchiseNumber);

        $options = '<option value="0">Select Role</option>';
        if (!empty($managers)) {
            foreach ($managers as $manager) {
                $options .= "<option value='{$manager->userId}'>{$manager->name}</option>";
            }
        }

        echo json_encode([
            'managerOptions' => $options,
            'franchiseName' => $franchiseData ? $franchiseData->franchiseName : '',
            'branchLocAddressPremise' => $franchiseData ? $franchiseData->branchLocAddressPremise : ''
        ]);
    }
}