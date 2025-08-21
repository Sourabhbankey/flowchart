<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Announcement (AnnouncementController)
 * Announcement Class to control Announcement related operations.
 * @author : Ashish 
 * @version : 1
 * @since : 24 Jul 2024
 */
class Returnproductdispatch extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Returnproductdispatch_model', 'retdis');
         $this->load->model('role_model', 'rm');
          $this->load->model('Salesrecord_model', 'salrec');
         $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Ticket';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('Returnproductdispatch/ReturnproductdispatchListing');
    }
    
    /**
     * This function is used to load the announcement list
     */
   public function ReturnproductdispatchListing()
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
        $count = $this->retdis->ReturnproductdispatchListingCount($searchText);
        
        // Pagination configuration
        $config = array();
        $config["base_url"] = base_url("Returnproductdispatch/ReturnproductdispatchListing");
        $config["total_rows"] = $count;
        $config["per_page"] = 10;
        $config["uri_segment"] = 3; // the page segment is expected at index 3 of the URI
        
        // Initialize pagination
        $this->pagination->initialize($config);

        // Get the current page number
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        // Fetch records for the current page
      $data['records'] = $this->retdis->ReturnproductdispatchListing($searchText, $config['per_page'], $page);

        // Get the pagination links
        $data['pagination'] = $this->pagination->create_links();

        // Record range and total
        $data['start'] = $page + 1;
        $data['end'] = min($page + $config['per_page'], $config['total_rows']);
        $data['total_records'] = $config['total_rows'];

        // Set the page title
        $this->global['pageTitle'] = 'CodeInsect : Ticket';

        // Load the view
        $this->loadViews("returnproductdispatch/list", $this->global, $data, NULL);
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
 $data['dispatchOrders'] = $this->retdis->getDispatchOrders();
            $this->loadViews("returnproductdispatch/add", $this->global, $data, NULL);
        }
    /*}*/
    
    /**
     * This function is used to add new user to the system
     */
  public function addNewReturnproductdispatch()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            // Define validation rules
            $this->form_validation->set_rules('order_id', 'Order ID', 'trim|required|max_length[50]');
            $this->form_validation->set_rules('product_title', 'Product Title', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('reason', 'Reason', 'trim|required|max_length[10024]');
            $this->form_validation->set_rules('quantity', 'Quantity', 'trim|required|numeric');
            $this->form_validation->set_rules('return_date', 'Return Date', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                        $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                        
                        $order_id = $this->security->xss_clean($this->input->post('order_id'));
                        $product_title = $this->security->xss_clean($this->input->post('product_title'));
                        $reason = $this->security->xss_clean($this->input->post('reason'));
                        $quantity = $this->security->xss_clean($this->input->post('quantity'));
                        $return_date = $this->security->xss_clean($this->input->post('return_date'));
                        $franchiseNumberArray = $this->input->post('franchiseNumber');
$franchiseNumber = is_array($franchiseNumberArray) ? $franchiseNumberArray[0] : $franchiseNumberArray;

$productCode = $this->security->xss_clean($this->input->post('productCode'));
$productName = $this->security->xss_clean($this->input->post('productName'));
                // Handle file upload for file
                $s3_file_link = [];
                if (isset($_FILES["file"]["tmp_name"]) && !empty($_FILES["file"]["tmp_name"])) {
                    $dir = dirname($_FILES["file"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];

                    if (rename($_FILES["file"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        $s3_file_link[] = $result_arr['ObjectURL'] ?? '';
                    } else {
                        $s3_file_link[] = '';
                    }
                } else {
                    $s3_file_link[] = '';
                }
                $s3files = implode(',', $s3_file_link);

                // Handle file upload for file2
                $s3_file_link2 = [];
                if (isset($_FILES["file2"]["tmp_name"]) && !empty($_FILES["file2"]["tmp_name"])) {
                    $dir = dirname($_FILES["file2"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file2"]["name"];

                    if (rename($_FILES["file2"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        $s3_file_link2[] = $result_arr['ObjectURL'] ?? '';
                    } else {
                        $s3_file_link2[] = '';
                    }
                } else {
                    $s3_file_link2[] = '';
                }
                $s3files2 = implode(',', $s3_file_link2);

                $ReturnproductdispatchInfo = array(
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'franchiseNumber' => $franchiseNumber,
                    'productCode' => $productCode,  
                    'productName' => $productName, 
                    'order_id' => $order_id,
                    'product_title' => $product_title,
                    'reason' => $reason,
                    'quantity' => $quantity,
                    'return_date' => $return_date,
                    'returnattachmentS3File' => $s3files,
                    'receiptattachmentS3File' => $s3files2,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->retdis->addNewReturnproductdispatch($ReturnproductdispatchInfo);
   // print_r($ReturnproductdispatchInfo);exit;
                if ($result > 0) {
                

                    // Email notification to admin
                    $adminEmail = 'dev.edumeta@gmail.com'; 
                    $adminSubject = "Alert - eduMETA THE i-SCHOOL New Return Product Request";
                    $adminMessage = 'A new return product request has been created. ';
                    $adminMessage .= 'Product Title: ' . $product_title . ', Order ID: ' . $order_id . '. ';
                    $adminMessage .= 'Please visit the portal for details.';
                    $adminHeaders = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                    mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);

                    $this->session->set_flashdata('success', 'Return product request created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Return product request creation failed');
                }

                redirect('Returnproductdispatch/ReturnproductdispatchListing');
            }
        }
    }

/*}*/

    /**
     * This function is used load announcement edit information
     * @param number $announcementId : Optional : This is announcement id
     */
   function edit($stockprodreturnId = NULL)
{
    if ($stockprodreturnId == null) {
        redirect('Returnproductdispatch/ReturnproductdispatchListing');
    }

    $data['returnproductdispatchInfo'] = $this->retdis->getReturnproductdispatchInfo($stockprodreturnId); // Add this line
$data['codes'] = $this->salrec->get_product_codes();
    $this->global['pageTitle'] = 'CodeInsect : Edit Return Product';
    $this->loadViews("returnproductdispatch/edit", $this->global, $data, NULL); // Pass $data
}
    
    
    
    /**
     * This function is used to edit the user information
     */
     function editReturnproductdispatch()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $stockprodreturnId = $this->input->post('stockprodreturnId');
            
            // Define validation rules
            $this->form_validation->set_rules('status', 'Status', 'trim|required|max_length[50]');
            $this->form_validation->set_rules('receivedQty', 'Received Quantity', 'trim|numeric');
            $this->form_validation->set_rules('receivedProduct', 'Received Product', 'trim|max_length[255]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($stockprodreturnId);
            }
            else
            {
                $status = $this->security->xss_clean($this->input->post('status'));
                $reply = $this->security->xss_clean($this->input->post('reply'));
                $receivedOndate = $this->security->xss_clean($this->input->post('receivedOndate'));
                $receivedQty = $this->security->xss_clean($this->input->post('receivedQty'));
                $receivedProduct = $this->security->xss_clean($this->input->post('receivedProduct'));
                $receivedProductVerification = $this->security->xss_clean($this->input->post('receivedProductVerification'));

                // Handle file upload for file2
                $s3_file_link2 = [];
                if (isset($_FILES["file2"]["tmp_name"]) && !empty($_FILES["file2"]["tmp_name"])) {
                    $dir = dirname($_FILES["file2"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file2"]["name"];

                    if (rename($_FILES["file2"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        $s3_file_link2[] = $result_arr['ObjectURL'] ?? '';
                    } else {
                        $s3_file_link2[] = '';
                    }
                } else {
                    $s3_file_link2[] = '';
                }
                $s3files2 = implode(',', $s3_file_link2);

                $ReturnproductdispatchInfo = array(
                    'status' => $status,
                    'reply' => $reply,
                    'receivedProductVerification' => $receivedProductVerification,
                    'receivedOndate' => $receivedOndate,
                    'receivedQty' => $receivedQty,
                    'receivedProduct' => $receivedProduct,
                    'replyattachment' => $s3files2,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );
                
                $result = $this->retdis->editReturnproductdispatch($ReturnproductdispatchInfo, $stockprodreturnId);

                if($result == true)
                {
                  // Update stock if product is accepted
                  /*  if ($status == 'Approved') {
                        $productCode = $this->input->post('productCode');
                        $quantity = (int)$this->input->post('quantity');

                        $this->db->set('currentStock', 'currentStock + ' . $quantity, FALSE);
                        $this->db->where('productCode', $productCode);
                        $this->db->update('tbl_stock_mng');
                    }*/
                       // ✅ If approved, update currentStock in tbl_stock_mng
         /* if (strtolower($status) == 'Approved') {
                    $this->db->select('productcode, quantity');
                    $this->db->from('tbl_stock_product_returns');
                    $this->db->where('stockprodreturnId', $stockprodreturnId);
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
                }*/
if (strtolower($status) == 'approved') {
    $this->db->select('productCode, quantity');
    $this->db->from('tbl_stock_product_returns');
    $this->db->where('stockprodreturnId', $stockprodreturnId);
    $query = $this->db->get();
    $row = $query->row();

    if ($row) {
        $productCode = $row->productCode;
        $quantity = (int)$row->quantity;

        // Check if productCode exists in tbl_stock_mng
        $this->db->where('productCode', $productCode);
        $stock = $this->db->get('tbl_stock_mng')->row();

        if ($stock) {
            // ✅ Update existing stock
            $this->db->set('currentStock', 'currentStock + ' . $quantity, false);
            $this->db->where('productCode', $productCode);
            $this->db->update('tbl_stock_mng');
        } else {
            // ✅ Insert if product does not exist in stock
            $this->db->insert('tbl_stock_mng', [
                'productCode' => $productCode,
                'currentStock' => $quantity,
                'createdDtm' => date('Y-m-d H:i:s')
            ]);
        }
    }
}



                    // Fetch return product details for email
                    $returnInfo = $this->retdis->getReturnproductdispatchInfo($stockprodreturnId);
                    $product_title = !empty($returnInfo->product_title) ? $returnInfo->product_title : 'Unknown Product';
                    $order_id = !empty($returnInfo->order_id) ? $returnInfo->order_id : 'Unknown Order';

                    // Email notification to admin
                    $adminEmail = 'dev.edumeta@gmail.com'; // Replace with actual admin email
                    $adminSubject = "Alert - eduMETA THE i-SCHOOL Return Product Request Updated";
                    $adminMessage = 'A return product request has been updated. ';
                    $adminMessage .= 'Product Title: ' . $product_title . ', Order ID: ' . $order_id . ', Status: ' . $status . '. ';
                    $adminMessage .= 'Please visit the portal for details.';
                    $adminHeaders = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                    mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);

                    $this->session->set_flashdata('success', 'Return product request updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Return product request updation failed');
                }
                
                redirect('Returnproductdispatch/ReturnproductdispatchListing');
            }
        }
    }
   public function fetchAssignedUsers() {
    $franchiseNumber = $this->input->post('franchiseNumber');

    // Load model and fetch franchise info (adjust model name if needed)
    $this->load->model('despatch_model');
    $managers = $this->despatch_model->getManagersByFranchise($franchiseNumber);
    $franchiseData = $this->despatch_model->getFranchiseDetails($franchiseNumber);

    $options = '<option value="0">Select Role</option>';
    if (!empty($managers)) {
        foreach ($managers as $manager) {
            $options .= '<option value="' . $manager->userId . '">' . $manager->name . '</option>';
        }
    }

    echo json_encode([
        'managerOptions' => $options,
        'franchiseName' => $franchiseData ? $franchiseData->franchiseName : ''
    ]);
}
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

?>