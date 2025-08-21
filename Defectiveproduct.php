<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Defective product (DefectiveproductController)
 * Defective product Class to control Defective product related operations.
 * @author : Ashish 
 * @version : 1
 * @since : 10 May 2025
 */
class Defectiveproduct extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Defectiveproduct_model', 'deft');
        $this->load->model('Salesrecord_model', 'salrec');
        $this->load->model('role_model', 'rm');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Ticket';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('defectiveproduct/defectiveproductListing');
    }

    /**
     * This function is used to load the announcement list
     */
    public function defectiveproductListing()
    {
        /* if (!$this->hasListAccess()) {
        $this->loadThis();
    } else {*/
        // Get search text
        $searchText = '';
        if (!empty($this->input->post('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
        }
        $data['searchText'] = $searchText;

        // Load pagination library
        $this->load->library('pagination');

        // Get the total number of records for pagination
        $count = $this->deft->defectiveproductListingCount($searchText);

        // Pagination configuration
        $config = array();
        $config["base_url"] = base_url("defectiveproduct/defectiveproductListing");
        $config["total_rows"] = $count;
        $config["per_page"] = 10;
        $config["uri_segment"] = 3; // the page segment is expected at index 3 of the URI

        // Initialize pagination
        $this->pagination->initialize($config);

        // Get the current page number
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        // Fetch records for the current page
        $data['records'] = $this->deft->defectiveproductListing($searchText, $config['per_page'], $page);

        // Get the pagination links
        $data['pagination'] = $this->pagination->create_links();

        // Record range and total
        $data['start'] = $page + 1;
        $data['end'] = min($page + $config['per_page'], $config['total_rows']);
        $data['total_records'] = $config['total_rows'];

        // Set the page title
        $this->global['pageTitle'] = 'CodeInsect : Ticket';

        // Load the view
        $this->loadViews("defectiveproduct/list", $this->global, $data, NULL);
    }
    /*}*/


    /**
     * This function is used to load the add new form
     */
    function add()
    {
        /*if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
        $this->global['pageTitle'] = 'CodeInsect : Add New ticket';
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $data['codes'] = $this->salrec->get_product_codes();
        $this->loadViews("defectiveproduct/add", $this->global, $data, NULL);
    }
    /*}*/

    /**
     * This function is used to add new user to the system
     */
    public function addNewDefectiveproduct()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('reason', 'Reason', 'trim|required|max_length[10024]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $productcode = $this->security->xss_clean($this->input->post('productcode'));
            $product_title = $this->security->xss_clean($this->input->post('product_title'));
            $reason = $this->security->xss_clean($this->input->post('reason'));
            $quantity = $this->security->xss_clean($this->input->post('quantity'));
            $defective_date = $this->security->xss_clean($this->input->post('defective_date'));
            $franchiseNumber = $this->session->userdata('franchiseNumber');
            $defective_from = $this->security->xss_clean($this->input->post('defective_from'));
            $otherSource = $this->security->xss_clean($this->input->post('otherSource'));

            /* $roleId = $this->session->userdata('role');
        if ($roleId == 25) {
            $franchiseNumber = $this->session->userdata('franchiseNumber');
        } else {
          $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
            $franchiseNumber = implode(',',$franchiseNumberArray);
        }*/

            if (isset($_FILES["file"]["tmp_name"]) && !empty($_FILES["file"]["tmp_name"])) {
                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];

                if (rename($_FILES["file"]["tmp_name"], $destination)) {
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                        $s3_file_link[] = $result_arr['ObjectURL'];
                    } else {
                        $s3_file_link[] = '';
                    }
                } else {
                    $s3_file_link[] = '';
                }
            } else {
                $s3_file_link[] = '';
            }

            $s3files = implode(',', $s3_file_link);

            if (isset($_FILES["file2"]["tmp_name"]) && !empty($_FILES["file2"]["tmp_name"])) {
                $dir = dirname($_FILES["file2"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file2"]["name"];

                if (rename($_FILES["file2"]["tmp_name"], $destination)) {
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                        $s3_file_link2[] = $result_arr['ObjectURL'];
                    } else {
                        $s3_file_link2[] = '';
                    }
                } else {
                    $s3_file_link2[] = '';
                }
            } else {
                $s3_file_link2[] = '';
            }

            $s3files2 = implode(',', $s3_file_link2);
            $defectiveproductInfo = array(
                'productcode' => $productcode,
                'product_title' => $product_title,
                'reason' => $reason,
                'quantity' => $quantity,
                'defective_date' => $defective_date,
                'franchiseNumber' => $franchiseNumber,
                'returnattachmentS3File' => $s3files,
                'receiptattachmentS3File' => $s3files2,
                'defective_from' => $defective_from,
                'otherSource' => $otherSource,
                'createdDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->deft->addNewDefectiveproduct($defectiveproductInfo);
            if ($result > 0) {

               
                  $this->load->model('Notification_model', 'nm');

                // Send notifications to users with roleId 19, 14, 25
                $notificationMessage = "<strong>Defective Product Confirmation:</strong> New Defective Product confirmation";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [1, 14, 25, 23 ])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $notificationResult = $this->nm->add_proddefective_notification($result, $notificationMessage, $userId);
                        if (!$notificationResult) {
                            log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                        }
                    }
                }


                $this->session->set_flashdata('success', 'Created successfully');
            } else {
                $this->session->set_flashdata('error', 'Creation failed');
            }

            redirect('defectiveproduct/defectiveproductListing');
        }
    }

    /*}*/

    /**
     * This function is used load announcement edit information
     * @param number $announcementId : Optional : This is announcement id
     */
    function edit($proddefectiveId = NULL)
    {
        if ($proddefectiveId == null) {
            redirect('defectiveproduct/defectiveproductListing');
        }

        $data['defectiveproductInfo'] = $this->deft->getDefectiveproductInfo($proddefectiveId); // Add this line

        $this->global['pageTitle'] = 'CodeInsect : Edit Defective Product';
        $this->loadViews("defectiveproduct/edit", $this->global, $data, NULL); // Pass $data
    }



    /**
     * This function is used to edit the user information
     */
    function editDefectiveproduct()
    {
        $this->load->library('form_validation');

        $proddefectiveId = $this->input->post('proddefectiveId');
        $this->form_validation->set_rules('status', 'status', 'trim|required|max_length[10024]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($proddefectiveId);
        } else {
            $status = $this->security->xss_clean($this->input->post('status'));
            $description = $this->security->xss_clean($this->input->post('description'));

            if (isset($_FILES["file2"]["tmp_name"]) && !empty($_FILES["file2"]["tmp_name"])) {
                $dir = dirname($_FILES["file2"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file2"]["name"];

                if (rename($_FILES["file2"]["tmp_name"], $destination)) {
                    $storeFolder = 'attachements';
                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();
                    $s3_file_link2[] = isset($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
                } else {
                    $s3_file_link2[] = '';
                }
            } else {
                $s3_file_link2[] = '';
            }

            $s3files2 = implode(',', $s3_file_link2);

            $defectiveproductInfo = array(
                'status' => $status,
                'description' => $description,
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s'),
                'replyattachment' => $s3files2
            );

            $result = $this->deft->editdefectiveproduct($defectiveproductInfo, $proddefectiveId);

            if ($result == true) {
                  $this->load->model('Notification_model', 'nm');

                // Send notifications to users with roleId 19, 14, 25
                $notificationMessage = "<strong>Defective Product Confirmation:</strong> Update Client confirmation";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [1, 14, 25, 23 ])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $notificationResult = $this->nm->add_proddefective_notification($result, $notificationMessage, $userId);
                        if (!$notificationResult) {
                            log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                        }
                    }
                }


                // ✅ If approved, update currentStock in tbl_stock_mng
                if (strtolower($status) == 'approve') {
                    $this->db->select('productcode, quantity');
                    $this->db->from('tbl_product_defective');
                    $this->db->where('proddefectiveId', $proddefectiveId);
                    $query = $this->db->get();
                    $row = $query->row();

                    if ($row) {
                        $productCode = $row->productcode;
                        $quantity = (int)$row->quantity;

                        $this->db->set('currentStock', 'currentStock + ' . $quantity, false);
                        $this->db->where('productCode', $productCode);
                        $this->db->update('tbl_stock_mng');

                        // 👇 This prints the actual query being executed
                        // echo $this->db->last_query();
                        // exit;
                    }
                }


                $this->session->set_flashdata('success', 'Defective product updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Defective product updation failed');
            }

            redirect('defectiveproduct/defectiveproductListing');
        }
    }

    /* }*/
    public function get_product_names()
    {
        // Get the productCode from the AJAX request
        $productCode = $this->input->post('productCode');

        // Fetch product names using the model
        $productNames = $this->salrec->get_product_names_by_code($productCode);

        if ($productNames) {
            $options = '<option value="">Select Product Name</option>';
            foreach ($productNames as $name) {
                $options .= '<option value="' . $name['productName'] . '">' . $name['productName'] . '</option>';
            }
            echo $options;
        } else {
            echo '<option value="">No Product Names Found</option>';
        }
    }
}
