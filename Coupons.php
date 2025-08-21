<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Coupons (CouponsController)
 * Coupons Class to control Coupons related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 15 M ar 2025
 */
class Coupons extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Coupons_model', 'brdisc');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Coupons';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('coupons/couponsListing');
    }

    /**
     * This function is used to load the Coupons list
     */
    public function couponsListing()
    {


        $searchText = '';
        $franchiseFilter = '';

        if (!empty($this->input->get('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->get('searchText'));
        }
        if (!empty($this->input->get('franchiseNumber'))) {
            $franchiseFilter = $this->security->xss_clean($this->input->get('franchiseNumber'));
        }

        // Get user role and franchise number
        $userRole = $this->session->userdata('role');
        $franchiseNumber = $this->session->userdata('franchiseNumber');

        $data['searchText'] = $searchText;
        $data['franchiseFilter'] = $franchiseFilter;

        $this->load->library('pagination');

        $config["base_url"] = base_url("coupons/couponsListing");
        $config["per_page"] = 10;
        $config["uri_segment"] = 3;
        $config["total_rows"] = $this->brdisc->couponsListingCount($searchText, $franchiseFilter, $userRole, $franchiseNumber);
        $config["num_links"] = 2;
        $config["use_page_numbers"] = TRUE;
        $config["reuse_query_string"] = TRUE;

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config["per_page"];

        $data["records"] = $this->brdisc->couponsListing($searchText, $franchiseFilter, $offset, $config["per_page"], $userRole, $franchiseNumber);
        $data["links"] = $this->pagination->create_links();
        $data["start"] = ($config["total_rows"] > 0) ? ($offset + 1) : 0;
        $data["end"] = min($offset + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $data["offset"] = $offset;
        $this->global['pageTitle'] = 'CodeInsect : coupons';
        $this->loadViews("coupons/list", $this->global, $data, NULL);
    }



    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            //$data['users'] = $this->brdisc->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Coupons';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("coupons/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    function addNewCoupons()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('couponsTitle', 'Coupons Title', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseName = $this->security->xss_clean($this->input->post('franchiseName'));
                $couponsTitle = $this->security->xss_clean($this->input->post('couponsTitle'));
                $couponsType = $this->security->xss_clean($this->input->post('couponsType'));
                $couponsCode = $this->security->xss_clean($this->input->post('couponsCode'));
                $couponsAmount = $this->security->xss_clean($this->input->post('couponsAmount'));
                $couponsUses = $this->security->xss_clean($this->input->post('couponsUses'));
                $couponsLimit = $this->security->xss_clean($this->input->post('couponsLimit'));
                $couponsEdate = $this->security->xss_clean($this->input->post('couponsEdate'));
                $couponsAssignedby = $this->security->xss_clean($this->input->post('couponsAssignedby'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $franchiseNumbers = implode(',', $franchiseNumberArray);
                $couponsInfo = array(
                    'franchiseName' => $franchiseName,
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'franchiseNumber' => $franchiseNumbers,
                    'couponsTitle' => $couponsTitle,
                    'couponsType' => $couponsType,
                    'couponsCode' => $couponsCode,
                    'couponsAmount' => $couponsAmount,
                    'couponsUses' => $couponsUses,
                    'couponsLimit' => $couponsLimit,
                    'couponsEdate' => $couponsEdate,
                    'couponsAssignedby' => $couponsAssignedby,
                    'description' => $description,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->brdisc->addNewCoupons($couponsInfo);
                if ($result > 0) {
                    $this->load->model('Notification_model', 'nm');

                    // Send notifications to users with roleId 19, 14, 25
                    $notificationMessage = "<strong>Client Confirmation:</strong> Assign New Coupons confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 22, 25])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_coupns_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
                    if (!empty($franchiseNumberArray)) {
                        foreach ($franchiseNumberArray as $franchiseNumber) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if (!empty($branchDetail)) {
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Coupons";
                                $message = 'Dear ' . $branchDetail->applicantName . ' ';
                                //$message = ' '.$description.' ';
                                $message .= 'You have been assigned a new coupons. BY- ' . $this->session->userdata("name") . ' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$description.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com, sourabh.edumeta@gmail.com";
                                mail($to, $subject, $message, $headers);
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Coupons created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Coupons creation failed');
                }

                redirect('coupons/couponsListing');
            }
        }
    }


    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($coupnsId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($coupnsId == null) {
                redirect('coupons/couponsListing');
            }

            $data['couponsInfo'] = $this->brdisc->getCouponsInfo($coupnsId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->brdisc->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Coupons';

            $this->loadViews("coupons/edit", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to edit the user information
     */
    function editCoupons()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $coupnsId = $this->input->post('coupnsId');

            $this->form_validation->set_rules('couponsTitle', 'Coupons Title', 'trim|required|max_length[256]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->edit($coupnsId);
            } else {
                $couponsTitle = $this->security->xss_clean($this->input->post('couponsTitle'));
                $couponsType = $this->security->xss_clean($this->input->post('couponsType'));
                $couponsCode = $this->security->xss_clean($this->input->post('couponsCode'));
                $couponsAmount = $this->security->xss_clean($this->input->post('couponsAmount'));
                $couponsUses = $this->security->xss_clean($this->input->post('couponsUses'));
                $couponsLimit = $this->security->xss_clean($this->input->post('couponsLimit'));
                $couponsEdate = $this->security->xss_clean($this->input->post('couponsEdate'));
                $couponsAssignedby = $this->security->xss_clean($this->input->post('couponsAssignedby'));
                $description = $this->security->xss_clean($this->input->post('description'));

                $couponsInfo = array(
                    'couponsTitle' => $couponsTitle,
                    'couponsType' => $couponsType,
                    'couponsCode' => $couponsCode,
                    'couponsAmount' => $couponsAmount,
                    'couponsUses' => $couponsUses,
                    'couponsLimit' => $couponsLimit,
                    'couponsEdate' => $couponsEdate,
                    'couponsAssignedby' => $couponsAssignedby,
                    'description' => $description,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->brdisc->editCoupons($couponsInfo, $coupnsId);


                if ($result == true) {
                     $this->load->model('Notification_model', 'nm');

                    // Send notifications to users with roleId 19, 14, 25
                    $notificationMessage = "<strong>Client Confirmation:</strong> Update Coupons confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 22, 25])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_coupns_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
                    // Email notification
                    $franchiseNumber = $this->brdisc->getFranchiseNumberByCouponsId($coupnsId);
                    if (!empty($franchiseNumber)) {
                        $franchiseNumberArray = explode(',', $franchiseNumber);
                        foreach ($franchiseNumberArray as $franchiseNum) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNum);
                            if (!empty($branchDetail)) {
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Coupons Updated";
                                $message = 'Dear ' . $branchDetail->applicantName . ', ';
                                $message .= 'Coupon information has been updated. BY- ' . $this->session->userdata("name") . ' ';
                                $message .= 'Please visit the portal for details.';
                                $headers = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com,sourabh.edumeta@gmail.com";
                                mail($to, $subject, $message, $headers);
                            }
                        }
                    }

                    $this->session->set_flashdata('success', 'Coupons updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Coupons updation failed');
                }

                redirect('coupons/couponsListing');
            }
        }
    }
}
