<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Employeeofmonth (EmployeeofmonthController)
 * Employeeofmonth Class to control Employeeofmonth related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 12 May 2025
 */
class Employeeofmonth extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Employeeofmonth_model', 'empaward');
        $this->load->model('Task_model', 'tm');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Employeeofmonth';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('employeeofmonth/employeeofmonthListing');
    }

    /**
     * This function is used to load the employeeofmonth list
     */
    function employeeofmonthListing()
    {
        $userRole = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');


        if (!$this->hasListAccess()) {
            $this->loadThis();
        } else {
            $searchText = '';
            if (!empty($this->input->post('searchText'))) {
                $searchText = $this->security->xss_clean($this->input->post('searchText'));
            }
            $data['searchText'] = $searchText;

            $this->load->library('pagination');

            $count = $this->empaward->employeeofmonthListingCount($searchText, $userRole, $userId);

            $returns = $this->paginationCompress("employeeofmonth/employeeofmonthListing/", $count, 10);

            $data['records'] = $this->empaward->employeeofmonthListing($searchText, $returns["page"], $returns["segment"], $userRole, $userId);

            $this->global['pageTitle'] = 'CodeInsect : Employeeofmonth';

            $this->loadViews("employeeofmonth/list", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->global['pageTitle'] = 'CodeInsect : Add New Employeeofmonth';
            $data['users'] = $this->tm->getUser();
            $this->loadViews("employeeofmonth/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    function addNewEmployeeofmonth()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');


            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                $assignedTo = $this->security->xss_clean($this->input->post('assignedTo'));
                $empDeartment = $this->security->xss_clean($this->input->post('empDeartment'));
                $dateofMonths = $this->security->xss_clean($this->input->post('dateofMonths'));
                $description = $this->security->xss_clean($this->input->post('description'));

                $s3_file_link = [];

                if (!empty($_FILES['files']['name'][0])) {
                    foreach ($_FILES['files']['name'] as $key => $name) {
                        $tmpName = $_FILES['files']['tmp_name'][$key];
                        $dir = dirname($tmpName);
                        $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $name;

                        if (move_uploaded_file($tmpName, $destination)) {
                            $storeFolder = 'attachements';

                            $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                            $result_arr = $s3Result->toArray();

                            if (!empty($result_arr['ObjectURL'])) {
                                $s3_file_link[] = $result_arr['ObjectURL'];
                            } else {
                                $s3_file_link[] = '';
                            }
                        }
                    }
                }

                $s3files = implode(',', $s3_file_link);

                $employeeofmonthInfo = array('assignedTo' => $assignedTo, 'empDeartment' => $empDeartment, 'dateofMonths' => $dateofMonths, 'description' => $description, 'empsingleattachment' => $s3files, 'createdBy' => $this->vendorId, 'createdDtm' => date('Y-m-d H:i:s'));

                $result = $this->empaward->addNewEmployeeofmonth($employeeofmonthInfo);
                //print_r($employeeofmonthInfo);exit;
                if ($result > 0) {
                    $notificationMessage = "<strong>Employee of the Month :</strong> Employee of the Month";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where('roleId !=', 25)
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_empofmonths_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Employeeofmonth created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Employeeofmonth creation failed');
                }

                redirect('employeeofmonth/employeeofmonthListing');
            }
        }
    }
    function view($empofmonthsId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($empofmonthsId == null) {
                redirect('employeeofmonth/employeeofmonthListing');
            }

            $data['employeeofmonthInfo'] = $this->empaward->getEmployeeofmonthInfo($empofmonthsId);

            $this->global['pageTitle'] = 'CodeInsect : View Employeeofmonth';

            $this->loadViews("employeeofmonth/view", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used load employeeofmonth edit information
     * @param number $empofmonthsId : Optional : This is employeeofmonth id
     */
    function edit($empofmonthsId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($empofmonthsId == null) {
                redirect('employeeofmonth/employeeofmonthListing');
            }

            $data['employeeofmonthInfo'] = $this->empaward->getEmployeeofmonthInfo($empofmonthsId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Employeeofmonth';

            $this->loadViews("employeeofmonth/edit", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to edit the user information
     */
   function editEmployeeofmonth()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $empofmonthsId = $this->input->post('empofmonthsId');

     
        
        $this->form_validation->set_rules('dateofMonths', 'Date of Month', 'trim|required');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($empofmonthsId);
        } else {
            $assignedTo = $this->security->xss_clean($this->input->post('assignedTo'));
            $empDeartment = $this->security->xss_clean($this->input->post('empDeartment'));
            $dateofMonths = $this->security->xss_clean($this->input->post('dateofMonths'));
            $description = $this->security->xss_clean($this->input->post('description'));

            $s3_file_link = [];
            if (!empty($_FILES['files']['name'][0])) {
                foreach ($_FILES['files']['name'] as $key => $name) {
                    $tmpName = $_FILES['files']['tmp_name'][$key];
                    $dir = dirname($tmpName);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $name;

                    if (move_uploaded_file($tmpName, $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();

                        if (!empty($result_arr['ObjectURL'])) {
                            $s3_file_link[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link[] = '';
                        }
                    }
                }
            }

            $s3files = implode(',', $s3_file_link);

            $employeeofmonthInfo = array(
                'assignedTo' => $assignedTo,
                'empDeartment' => $empDeartment,
                'dateofMonths' => $dateofMonths,
                'description' => $description,
                'empsingleattachment' => $s3files ?: $this->input->post('existing_empsingleattachment'),
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );

            log_message('debug', 'Employeeofmonth Info: ' . print_r($employeeofmonthInfo, true));

            $result = $this->empaward->editEmployeeofmonth($employeeofmonthInfo, $empofmonthsId);

            if ($result) {
                $notificationMessage = "<strong>Employee of the Month:</strong> Employee of the Month Updated";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where('roleId !=', 25)
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $notificationResult = $this->nm->add_empofmonths_notification($empofmonthsId, $notificationMessage, $userId);
                        if (!$notificationResult) {
                            log_message('error', "Failed to add notification for user {$userId} on campaign ID {$empofmonthsId}");
                        }
                    }
                }
                $this->session->set_flashdata('success', 'Employee of the Month updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Employee of the Month update failed');
            }

            redirect('employeeofmonth/employeeofmonthListing');
        }
    }
}
}
