<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Empdailyreport (EmpdailyreportController)
 * Empdailyreport Class to control employee daily report related operations.
 * @author : Ashish
 * @version : 1.1
 * @since : 28 May 2024
 * @updated : 17 May 2025
 */
class Empdailyreport extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Empdailyreport_model', 'edr');
        $this->load->library(['form_validation', 'pagination']);
        $this->load->model('Notification_model', 'nm');
        $this->load->model('Branches_model', 'bm');
        $this->load->helper(['form', 'url']);
        $this->isLoggedIn();
        $this->module = 'Empdailyreport';
    }

    public function index()
    {
        redirect('Empdailyreport/dailyReportListing');
    }

    public function dailyReportListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
        $searchText = $this->input->get('searchText', TRUE);

        $config = array();
        $config['base_url'] = base_url('Empdailyreport/dailyReportListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        $config['total_rows'] = $this->edr->dailyReportListingCount($searchText);
        $data['records'] = $this->edr->dailyReportListing($searchText, $config['per_page'], $page);

        $data['serial_no'] = $page + 1;
        $this->pagination->initialize($config);
        $data['links'] = $this->pagination->create_links();
        $data['start'] = $page + 1;
        $data['end'] = min($page + $config['per_page'], $config['total_rows']);
        $data['total_records'] = $config['total_rows'];
        $data['pagination'] = $this->pagination->create_links();
        $data['searchText'] = $searchText;

        // $this->global['pageTitle'] = 'eduMETA : Daily Reports';
        // $this->global['name'] = $this->session->userdata('name');
        // $this->global['is_admin'] = $this->session->userdata('is_admin');

        $this->loadViews('empdailyreport/list', $this->global, $data, NULL);
    }

    public function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $data['users'] = $this->edr->getUsers();

            $this->global['pageTitle'] = 'eduMETA : Add New Daily Report';
            $this->global['role'] = $this->session->userdata('role');
            $this->global['is_admin'] = $this->session->userdata('is_admin');

            $this->loadViews('empdailyreport/add', $this->global, $data, NULL);
        }
    }

 public function addNewEmpdailyreport()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
        return;
    }

    $this->form_validation->set_rules('dailyRepempName', 'Employee Name', 'trim|required|max_length[255]');
    $this->form_validation->set_rules('dailyRepTitle', 'Report Title', 'trim|required|max_length[255]');

    if ($this->form_validation->run() == FALSE) {
        $this->session->set_flashdata('error', validation_errors());
        $this->add();
        return;
    }

    $dailyRepempName = $this->security->xss_clean($this->input->post('dailyRepempName'));
    $dailyRepTitle = $this->security->xss_clean($this->input->post('dailyRepTitle'));
    $dailyempDeartment = $this->security->xss_clean($this->input->post('dailyempDeartment'));
    $description = $this->security->xss_clean($this->input->post('description'));

    $fileUrl = '';
    if (!empty($_FILES['file']['name'])) {
        $dir = dirname($_FILES["file"]["tmp_name"]);
        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
        if (!rename($_FILES["file"]["tmp_name"], $destination)) {
            $this->session->set_flashdata('error', 'Failed to prepare file for upload');
            $this->add();
            return;
        }

        // Upload to S3
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        $fileUrl = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';

        // Clean up local file
        if (file_exists($destination)) {
            unlink($destination);
        }

        if (empty($fileUrl)) {
            $this->session->set_flashdata('error', 'Failed to upload file to S3');
            $this->add();
            return;
        }
    }

    $dailyReportInfo = [
        'dailyRepempName' => $dailyRepempName,
        'dailyRepTitle' => $dailyRepTitle,
        'dailyempDeartment' => $dailyempDeartment,
        'description' => $description,
        // 'fileUrl' => $fileUrl, // Removed to avoid database error
        'isDeleted' => 0,
        'createdBy' => $this->vendorId,
        'createdDtm' => date('Y-m-d H:i:s')
    ];


    $this->db->trans_start();
    $reportId = $this->edr->addNewDailyReport($dailyReportInfo);
    $this->db->trans_complete();

    if ($reportId && $this->db->trans_status() !== FALSE) {
        // ✅ Add notifications
        $this->load->model('Notification_model');
        $notificationMessage = "<strong>Daily Report</strong> : A new daily report titled <strong>$dailyRepTitle</strong> has been submitted.";

        // Notify assigned department user
        if (!empty($dailyempDeartment)) {
            $this->Notification_model->add_empdailyreport_notification($dailyempDeartment, $notificationMessage, $reportId);
        }

        // Notify roleId 1 and 14
        $adminUsers = $this->bm->getUsersByRoles([1, 14]);
        if (!empty($adminUsers)) {
            foreach ($adminUsers as $adminUser) {
                $this->Notification_model->add_empdailyreport_notification($adminUser->userId, $notificationMessage, $reportId);
            }
        }

        $this->session->set_flashdata('success', 'Daily report created successfully');
    } else {
        $this->session->set_flashdata('error', 'Daily report creation failed');
    }

    redirect('Empdailyreport/dailyReportListing');
}



    public function edit($id = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($id == NULL) {
                $this->session->set_flashdata('error', 'Invalid report ID.');
                redirect('Empdailyreport/dailyReportListing');
            }

            $data['report'] = $this->edr->getDailyReportInfo($id);
            if (!$data['report']) {
                $this->session->set_flashdata('error', 'Report not found.');
                redirect('Empdailyreport/dailyReportListing');
            }

            $data['users'] = $this->edr->getUsers();

            $this->global['pageTitle'] = 'eduMETA : Edit Daily Report';
            $this->global['role'] = $this->session->userdata('role');
            $this->global['name'] = $this->session->userdata('name');
            $this->global['is_admin'] = $this->session->userdata('is_admin');

            $this->loadViews('empdailyreport/edit', $this->global, $data, NULL);
        }
    }

    public function update()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $id = $this->input->post('dailyreportId');

            $this->form_validation->set_rules('dailyRepempName', 'Employee Name', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('dailyRepTitle', 'Report Title', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('dailyempDeartment', 'Department', 'trim|max_length[255]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                $this->edit($id);
            } else {
                $dailyRepempName = $this->security->xss_clean($this->input->post('dailyRepempName'));
                $dailyRepTitle = $this->security->xss_clean($this->input->post('dailyRepTitle'));
                $dailyempDeartment = $this->security->xss_clean($this->input->post('dailyempDeartment'));
                $description = $this->security->xss_clean($this->input->post('description'));

                $dailyReportInfo = [
                    'dailyRepempName' => $dailyRepempName,
                    'dailyRepTitle' => $dailyRepTitle,
                    'dailyempDeartment' => $dailyempDeartment,
                    'description' => $description,
                    // 'fileUrl' => $fileUrl, // Requires schema update
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                ];

                $this->db->trans_start();
                $result = $this->edr->editDailyReport($dailyReportInfo, $id);
                $this->db->trans_complete();

                if ($result && $this->db->trans_status() !== FALSE) {

                    $this->session->set_flashdata('success', 'Daily report updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Daily report update failed');
                }

                redirect('Empdailyreport/dailyReportListing');
            }
        }
    }

    public function delete($id = NULL)
    {
        if (!$this->hasDeleteAccess()) {
            $this->loadThis();
        } else {
            if ($id == NULL) {
                $this->session->set_flashdata('error', 'Invalid report ID.');
                redirect('Empdailyreport/dailyReportListing');
            }

            $this->db->trans_start();
            $result = $this->edr->deleteDailyReport($id);
            $this->db->trans_complete();

            if ($result && $this->db->trans_status() !== FALSE) {
                $this->session->set_flashdata('success', 'Daily report deleted successfully');
            } else {
                $this->session->set_flashdata('error', 'Daily report deletion failed');
            }

            redirect('Empdailyreport/dailyReportListing');
        }
    }

   
    public function fetchAssignedUsers()
    {
        $users = $this->edr->getUsers();

        $options = '<option value="0">Select User</option>';
        foreach ($users as $user) {
            $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
        }

        echo $options;
    }
}
?>