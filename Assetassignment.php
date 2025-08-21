<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Assetassignment (AssetassignmentController)
 * Assetassignment Class to handle asset assignments.
 * @author : Ashish
 * @version : 1.0
 * @since : 13 May 2025
 */
class Assetassignment extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Assetassignment_model', 'assetModel');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Assetassignment';
    }

    public function index()
    {
        redirect('assetassignment/assetassignmentListing');
    }

    public function assetassignmentListing()
    {
        if (!$this->hasListAccess()) {
            $this->loadThis();
        } else {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));

            // Pagination configuration
            $this->load->library('pagination');
            $config = [
                'base_url' => base_url('assetassignment/assetassignmentListing'),
                'per_page' => 10,
                'uri_segment' => 3,
                'use_page_numbers' => TRUE,
                'total_rows' => $this->assetModel->assetassignmentListingCount($searchText),
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

            // Persist search in pagination links
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
                'records' => $this->assetModel->assetassignmentListing($searchText, $config['per_page'], $offset),
                'pagination' => $this->pagination->create_links(),
                'searchText' => $searchText,
                'total_records' => $config['total_rows'],
                'start' => $offset + 1,
                'end' => min($offset + $config['per_page'], $config['total_rows'])
            ];

            $this->global['pageTitle'] = 'CodeInsect : Asset Assignments';
            $this->loadViews("assetassignment/list", $this->global, $data, NULL);
        }
    }
    public function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->global['pageTitle'] = 'CodeInsect : Add New Asset Assignment';
            $data['users'] = $this->assetModel->getUser();

            $this->loadViews("assetassignment/add", $this->global, $data, NULL);
        }
    }

    public function addNewAssetassignment()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('assetsId', 'Assets Name', 'trim|max_length[100]');
            // $this->form_validation->set_rules('userID','User Name','trim|max_length[100]');
            $this->form_validation->set_rules('assetsTitle1', 'Assign Title');
            $this->form_validation->set_rules('Status', 'Status', 'trim|max_length[1024]');
            $this->form_validation->set_rules('BrandName', 'Brand Name', 'trim|max_length[1024]');
            $this->form_validation->set_rules('assignDate', 'Assign Date', 'trim|max_length[1024]');

            //          $this->form_validation->set_rules('assetsTitle2','Assign Title');
            // $this->form_validation->set_rules('Status2','Status','trim|max_length[1024]');
            //     $this->form_validation->set_rules('BrandName2','Brand Name','trim|max_length[1024]');
            //         $this->form_validation->set_rules('assignDate2','Assign Date','trim|max_length[1024]');

            //          $this->form_validation->set_rules('assetsTitle3','Assign Title');
            // $this->form_validation->set_rules('Status3','Status','trim|max_length[1024]');
            //     $this->form_validation->set_rules('BrandName3','Brand Name','trim|max_length[1024]');
            //         $this->form_validation->set_rules('assignDate3','Assign Date','trim|max_length[1024]');

            //          $this->form_validation->set_rules('assetsTitle4','Assign Title');
            // $this->form_validation->set_rules('Status4','Status','trim|max_length[1024]');
            //     $this->form_validation->set_rules('BrandName4','Brand Name','trim|max_length[1024]');
            //         $this->form_validation->set_rules('assignDate4','Assign Date','trim|max_length[1024]');

            //          $this->form_validation->set_rules('assetsTitle5','Assign Title');
            // $this->form_validation->set_rules('Status5','Status','trim|max_length[1024]');
            //     $this->form_validation->set_rules('BrandName5','Brand Name','trim|max_length[1024]');
            //         $this->form_validation->set_rules('assignDate5','Assign Date','trim|max_length[1024]');

            //          $this->form_validation->set_rules('assetsTitle6','Assign Title');
            // $this->form_validation->set_rules('Status6','Status','trim|max_length[1024]');
            //     $this->form_validation->set_rules('BrandName6','Brand Name','trim|max_length[1024]');
            //         $this->form_validation->set_rules('assignDate6','Assign Date','trim|max_length[1024]');

            //          $this->form_validation->set_rules('assetsTitle7','Assign Title');
            // $this->form_validation->set_rules('Status7','Status','trim|max_length[1024]');
            //     $this->form_validation->set_rules('BrandName7','Brand Name','trim|max_length[1024]');
            //         $this->form_validation->set_rules('assignDate7','Assign Date','trim|max_length[1024]');

            //          $this->form_validation->set_rules('assetsTitle8','Assign Title');
            // $this->form_validation->set_rules('Status8','Status','trim|max_length[1024]');
            //     $this->form_validation->set_rules('BrandName8','Brand Name','trim|max_length[1024]');
            //         $this->form_validation->set_rules('assignDate8','Assign Date','trim|max_length[1024]');

            //          $this->form_validation->set_rules('assetsTitle9','Assign Title');
            // $this->form_validation->set_rules('Status9','Status','trim|max_length[1024]');
            //     $this->form_validation->set_rules('BrandName9','Brand Name','trim|max_length[1024]');
            //         $this->form_validation->set_rules('assignDate9','Assign Date','trim|max_length[1024]');

            $this->form_validation->set_rules('description', 'Description', 'trim|max_length[1024]');



            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                /* $assetsId = $this->security->xss_clean($this->input->post('assetsId'));*/
                $userID = $this->security->xss_clean($this->input->post('userID'));
                $assetsTitle1 = $this->security->xss_clean($this->input->post('assetsTitle1'));
                $Status = $this->security->xss_clean($this->input->post('Status'));
                $BrandName = $this->security->xss_clean($this->input->post('BrandName'));
                $assignDate = $this->security->xss_clean($this->input->post('assignDate'));
                $assetsTitle2 = $this->security->xss_clean($this->input->post('assetsTitle2'));
                $Status2 = $this->security->xss_clean($this->input->post('Status2'));
                $BrandName2 = $this->security->xss_clean($this->input->post('BrandName2'));
                $assignDate2 = $this->security->xss_clean($this->input->post('assignDate2'));
                $assetsTitle3 = $this->security->xss_clean($this->input->post('assetsTitle3'));
                $Status3 = $this->security->xss_clean($this->input->post('Status3'));
                $BrandName3 = $this->security->xss_clean($this->input->post('BrandName3'));
                $assignDate3 = $this->security->xss_clean($this->input->post('assignDate3'));
                $assetsTitle4 = $this->security->xss_clean($this->input->post('assetsTitle4'));
                $Status4 = $this->security->xss_clean($this->input->post('Status4'));
                $BrandName4 = $this->security->xss_clean($this->input->post('BrandName4'));
                $assignDate4 = $this->security->xss_clean($this->input->post('assignDate4'));
                $assetsTitle5 = $this->security->xss_clean($this->input->post('assetsTitle5'));
                $Status5 = $this->security->xss_clean($this->input->post('Status5'));
                $BrandName5 = $this->security->xss_clean($this->input->post('BrandName5'));
                $assignDate5 = $this->security->xss_clean($this->input->post('assignDate5'));

                $assetsTitle6 = $this->security->xss_clean($this->input->post('assetsTitle6'));
                $Status6 = $this->security->xss_clean($this->input->post('Status6'));
                $BrandName6 = $this->security->xss_clean($this->input->post('BrandName6'));
                $assignDate6 = $this->security->xss_clean($this->input->post('assignDate6'));
                $assetsTitle7 = $this->security->xss_clean($this->input->post('assetsTitle7'));
                $Status7 = $this->security->xss_clean($this->input->post('Status7'));
                $BrandName7 = $this->security->xss_clean($this->input->post('BrandName7'));
                $assignDate7 = $this->security->xss_clean($this->input->post('assignDate7'));
                $assetsTitle8 = $this->security->xss_clean($this->input->post('assetsTitle8'));
                $Status8 = $this->security->xss_clean($this->input->post('Status8'));
                $BrandName8 = $this->security->xss_clean($this->input->post('BrandName8'));
                $assignDate8 = $this->security->xss_clean($this->input->post('assignDate8'));
                $assetsTitle9 = $this->security->xss_clean($this->input->post('assetsTitle9'));
                $Status9 = $this->security->xss_clean($this->input->post('Status9'));
                $BrandName9 = $this->security->xss_clean($this->input->post('BrandName9'));
                $assignDate9 = $this->security->xss_clean($this->input->post('assignDate9'));
                $description = $this->security->xss_clean($this->input->post('description'));

                $assetInfo = array(
                    /*  'assetsId'=>$assetsId,*/
                    'userID' => $userID,
                    'assetsTitle1' => $assetsTitle1,
                    'Status' => $Status,
                    'BrandName' => $BrandName,
                    'assignDate' => $assignDate,

                    'assetsTitle2' => $assetsTitle2,
                    'Status2' => $Status2,
                    'BrandName2' => $BrandName2,
                    'assignDate2' => $assignDate2,
                    'assetsTitle3' => $assetsTitle3,
                    'assetsTitle3' => $assetsTitle3,
                    'Status3' => $Status3,
                    'BrandName3' => $BrandName3,
                    'assignDate3' => $assignDate3,
                    'assetsTitle4' => $assetsTitle4,
                    'Status4' => $Status4,
                    'BrandName4' => $BrandName4,
                    'assignDate4' => $assignDate4,
                    'assetsTitle5' => $assetsTitle5,
                    'Status5' => $Status5,
                    'BrandName5' => $BrandName5,
                    'assignDate5' => $assignDate5,
                    'assetsTitle6' => $assetsTitle6,
                    'Status6' => $Status6,
                    'BrandName6' => $BrandName6,
                    'assignDate6' => $assignDate6,
                    'assetsTitle7' => $assetsTitle7,
                    'Status7' => $Status7,
                    'BrandName7' => $BrandName7,
                    'assignDate7' => $assignDate7,
                    'assetsTitle8' => $assetsTitle8,
                    'Status8' => $Status8,
                    'BrandName8' => $BrandName8,
                    'assignDate8' => $assignDate8,
                    'assetsTitle9' => $assetsTitle9,
                    'Status9' => $Status9,
                    'BrandName9' => $BrandName9,
                    'assignDate9' => $assignDate9,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s'),
                    'description' => $description,
                );

                $result = $this->assetModel->addNewAssetassignment($assetInfo);
                //print_r($assetInfo);exit;
                if ($result > 0) {
                     $this->load->model('Notification_model', 'nm');

                    // Send notifications to users with roleId 19, 14, 25
                    $notificationMessage = "<strong>Assesst Assignment  Confirmation:</strong> New Assesst Assignment confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 26])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_assetassignment_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }

                    $this->session->set_flashdata('success', 'New asset assigned successfully');
                } else {
                    $this->session->set_flashdata('error', 'Asset assignment failed');
                }
                redirect('assetassignment/assetassignmentListing');
            }
        }
    }

    public function view($assetsId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($assetsId == null) {
                redirect('assetassignment/assetassignmentListing');
            }

            $data['assetInfo'] = $this->assetModel->getAssetassignmentInfo($assetsId);

            $this->global['pageTitle'] = 'CodeInsect : View Asset Assignment';
            $this->loadViews("assetassignment/view", $this->global, $data, NULL);
        }
    }

    public function edit($assetsId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($assetsId == null) {
                redirect('assetassignment/assetassignmentListing');
            }

            $data['assetInfo'] = $this->assetModel->getAssetassignmentInfo($assetsId);
            $data['users'] = $this->assetModel->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Asset Assignment';
            $this->loadViews("assetassignment/edit", $this->global, $data, NULL);
        }
    }

    public function editAssetassignment()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');
            $assetsId = $this->input->post('assetsId');

            $this->form_validation->set_rules('description', 'Description', 'trim|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->edit($assetsId);
            } else {
                /*$assetsId = $this->security->xss_clean($this->input->post('assetsId'));*/
                $userID = $this->security->xss_clean($this->input->post('userID'));
                $assetsTitle1 = $this->security->xss_clean($this->input->post('assetsTitle1'));
                $Status = $this->security->xss_clean($this->input->post('Status'));
                $BrandName = $this->security->xss_clean($this->input->post('BrandName'));
                $assignDate = $this->security->xss_clean($this->input->post('assignDate'));

                $assetsTitle2 = $this->security->xss_clean($this->input->post('assetsTitle2'));
                $Status2 = $this->security->xss_clean($this->input->post('Status2'));
                $BrandName2 = $this->security->xss_clean($this->input->post('BrandName2'));
                $assignDate2 = $this->security->xss_clean($this->input->post('assignDate2'));

                $assetsTitle3 = $this->security->xss_clean($this->input->post('assetsTitle3'));
                $Status3 = $this->security->xss_clean($this->input->post('Status3'));
                $BrandName3 = $this->security->xss_clean($this->input->post('BrandName3'));
                $assignDate3 = $this->security->xss_clean($this->input->post('assignDate3'));

                $assetsTitle4 = $this->security->xss_clean($this->input->post('assetsTitle4'));
                $Status4 = $this->security->xss_clean($this->input->post('Status4'));
                $BrandName4 = $this->security->xss_clean($this->input->post('BrandName4'));
                $assignDate4 = $this->security->xss_clean($this->input->post('assignDate4'));

                $assetsTitle5 = $this->security->xss_clean($this->input->post('assetsTitle5'));
                $Status5 = $this->security->xss_clean($this->input->post('Status5'));
                $BrandName5 = $this->security->xss_clean($this->input->post('BrandName5'));
                $assignDate5 = $this->security->xss_clean($this->input->post('assignDate5'));


                $assetsTitle6 = $this->security->xss_clean($this->input->post('assetsTitle6'));
                $Status6 = $this->security->xss_clean($this->input->post('Status6'));
                $BrandName6 = $this->security->xss_clean($this->input->post('BrandName6'));
                $assignDate6 = $this->security->xss_clean($this->input->post('assignDate6'));

                $assetsTitle7 = $this->security->xss_clean($this->input->post('assetsTitle7'));
                $Status7 = $this->security->xss_clean($this->input->post('Status7'));
                $BrandName7 = $this->security->xss_clean($this->input->post('BrandName7'));
                $assignDate7 = $this->security->xss_clean($this->input->post('assignDate7'));

                $assetsTitle8 = $this->security->xss_clean($this->input->post('assetsTitle8'));
                $Status8 = $this->security->xss_clean($this->input->post('Status8'));
                $BrandName8 = $this->security->xss_clean($this->input->post('BrandName8'));
                $assignDate8 = $this->security->xss_clean($this->input->post('assignDate8'));

                $assetsTitle9 = $this->security->xss_clean($this->input->post('assetsTitle9'));
                $Status9 = $this->security->xss_clean($this->input->post('Status9'));
                $BrandName9 = $this->security->xss_clean($this->input->post('BrandName9'));
                $assignDate9 = $this->security->xss_clean($this->input->post('assignDate9'));

                $description = $this->security->xss_clean($this->input->post('description'));




                $assetInfo = array(
                    'userID' => $userID,
                    'assetsTitle1' => $assetsTitle1,
                    'Status' => $Status,
                    'BrandName' => $BrandName,
                    'assignDate' => $assignDate,

                    'assetsTitle2' => $assetsTitle2,
                    'Status2' => $Status2,
                    'BrandName2' => $BrandName2,
                    'assignDate2' => $assignDate2,

                    'assetsTitle3' => $assetsTitle3,
                    'Status3' => $Status3,
                    'BrandName3' => $BrandName3,
                    'assignDate3' => $assignDate3,

                    'assetsTitle4' => $assetsTitle4,
                    'Status4' => $Status4,
                    'BrandName4' => $BrandName4,
                    'assignDate4' => $assignDate4,

                    'assetsTitle5' => $assetsTitle5,
                    'Status5' => $Status5,
                    'BrandName5' => $BrandName5,
                    'assignDate5' => $assignDate5,

                    'assetsTitle6' => $assetsTitle6,
                    'Status6' => $Status6,
                    'BrandName6' => $BrandName6,
                    'assignDate6' => $assignDate6,

                    'assetsTitle7' => $assetsTitle7,
                    'Status7' => $Status7,
                    'BrandName7' => $BrandName7,
                    'assignDate7' => $assignDate7,

                    'assetsTitle8' => $assetsTitle8,
                    'Status8' => $Status8,
                    'BrandName8' => $BrandName8,
                    'assignDate8' => $assignDate8,

                    'assetsTitle9' => $assetsTitle9,
                    'Status9' => $Status9,
                    'BrandName9' => $BrandName9,
                    'assignDate9' => $assignDate9,
                    'description' => $description,
                );

                $result = $this->assetModel->editAssetassignment($assetInfo, $assetsId);
                //print_r($assetInfo);exit;
                if ($result == true) {
                    if ($result > 0) {
                         $this->load->model('Notification_model', 'nm');

                    // Send notifications to users with roleId 19, 14, 25
                    $notificationMessage = "<strong>Assesst Assignment  Confirmation:</strong> Update Assesst Assignment confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 26])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_assetassignment_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
                        $this->session->set_flashdata('success', 'Asset assignment updated successfully');
                    } else {
                        $this->session->set_flashdata('error', 'Asset assignment update failed');
                    }

                    redirect('assetassignment/assetassignmentListing');
                }
            }
        }
    }
}
