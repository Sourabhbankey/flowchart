<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Despatch (DespatchController)
 * Despatch Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 08 Jun 2023
  */
class Despatch extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Despatch_model', 'dm');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Despatch';
        $this->load->library("pagination");
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
     public function index()
    {
        redirect('despatch/despatchListing');
    }
    
    /**
     * This function is used to load the task list
     */
 
/*public function despatchListing() {
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');

    $franchiseFilter = $this->input->get('franchiseNumber');
    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = '';
    }

    $from_date = $this->input->get('from_date', TRUE);
    $to_date = $this->input->get('to_date', TRUE);
    $search_query = $this->input->get('search', TRUE);

    $consumerKey = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579';
    $consumerSecret = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';

    // Pagination setup
    $config = array();
    $config['base_url'] = base_url('despatch/despatchListing');
    $config['per_page'] = 100;
    $config['page_query_string'] = true;
    $config['reuse_query_string'] = true;
    $page = ($this->input->get('per_page')) ? $this->input->get('per_page') : 1;

    $apiUrl = "https://shop.theischool.com/wp-json/wc/v3/orders?page={$page}&per_page={$config['per_page']}";
    if ($search_query) $apiUrl .= "&search=" . urlencode($search_query);
    if ($from_date) $apiUrl .= "&after=" . urlencode($from_date . 'T00:00:00');
    if ($to_date) $apiUrl .= "&before=" . urlencode($to_date . 'T23:59:59');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ":" . $consumerSecret);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    curl_close($ch);

    preg_match('/X-WP-TotalPages: (\d+)/i', $header, $matches);
    $totalPages = $matches[1] ?? 1;

    preg_match('/X-WP-Total: (\d+)/i', $header, $matchesTotal);
    $totalOrders = $matchesTotal[1] ?? 0;

    $orders = json_decode($body);
    $filteredOrders = [];
//print_r($orders);exit;
 if (!empty($orders)) {
    foreach ($orders as $order) {
        //  Always insert first to ensure it exists in DB
        $this->dm->insertDespatchIfNotExists($order);

        //  Now fetch matching despatch row from DB
        $localDespatch = $this->dm->getDespatchByOrderId($order->id);
        if ($localDespatch) {
            $order->despatchId = $localDespatch->despatchId;
        } else {
            $order->despatchId = null;
        }

        //  Apply franchise filter AFTER assigning despatchId
        if ($franchiseFilter == '' || ($order->billing->company ?? '') == $franchiseFilter) {
            $filteredOrders[] = $order;
        }
    }
}



    $config['total_rows'] = $totalOrders;
    $this->pagination->initialize($config);

    $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

    $data['records'] = $filteredOrders;

    $data['pagination'] = $this->pagination->create_links();
    $data['total_records'] = $totalOrders;
    $data['start'] = $page;
    $data['end'] = min($page + $config['per_page'] - 1, $totalOrders);
    $data['serial_no'] = $page;
    $data['franchiseFilter'] = $franchiseFilter;
    $data['from_date'] = $from_date;
    $data['to_date'] = $to_date;
    $data['search_query'] = $search_query;

    $this->global['pageTitle'] = 'Despatch Orders from WooCommerce';
    $this->loadViews("despatch/list", $this->global, $data, NULL);
}

*/
public function despatchListing() {
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');
    
    // Get franchise filter from query string
    $franchiseFilter = $this->input->get('franchiseNumber');
    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = ''; // Reset filter
    }
    
    // Pagination configuration
   $config = array();
$config['base_url'] = base_url('despatch/despatchListing');
$config['per_page'] = 10; 
$config['uri_segment'] = 3;
$config['page_query_string'] = true; // Enable query string
$config['reuse_query_string'] = true; // Preserve query string parameters
$page = ($this->input->get('per_page')) ? $this->input->get('per_page') : 0;

if ($userRole == '14' || $userRole == '1' || $userRole == '23' || $userRole == '16'|| $userRole == '29' || $userRole == '31') { 
    // Admin roles
    if ($franchiseFilter) {
        $config['total_rows'] = $this->dm->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
        $data['records'] = $this->dm->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
    } else {
        $config['total_rows'] = $this->dm->getTotalTrainingRecordsCount();
        $data['records'] = $this->dm->getAllTrainingRecords($config['per_page'], $page,$franchiseFilter);
    }
} elseif ($userRole == '15') { 
    // Specific roles
    $config['total_rows'] = $this->dm->getTotalTrainingRecordsCountByRole($userId,$franchiseFilter);
    $data['records'] = $this->dm->getTrainingRecordsByRole($userId, $config['per_page'], $page, $franchiseFilter);
} else { 
    // Franchise-specific logic
    $franchiseNumber = $this->dm->getFranchiseNumberByUserId($userId);
    if ($franchiseNumber) {
        if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
            $config['total_rows'] = $this->dm->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
            $data['records'] = $this->dm->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
        } else {
            $config['total_rows'] = $this->dm->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
            $data['records'] = $this->dm->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
        }
    } else {
        $data['records'] = []; // Handle case where franchise number is not found
    }
}

// Initialize pagination
$this->pagination->initialize($config);

$data["serial_no"] = $page + 1;
$data["links"] = $this->pagination->create_links();
$data["start"] = $page + 1;
$data["end"] = min($page + $config["per_page"], $config["total_rows"]);
$data["total_records"] = $config["total_rows"];
$data['pagination'] = $this->pagination->create_links();
$data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

$this->loadViews("despatch/list", $this->global, $data, NULL);
}

public function getDespatchChartData()
{
    // Get inputs from POST data with XSS cleaning
  $year = $this->input->post('year');
    $franchiseNumber = $this->input->post('franchiseNumber');
    // Validate year
    if (!is_numeric($year) || $year < 2000 || $year > date('Y')) {
        $year = date('Y');
       
    }

    // Initialize an array for 12 months
    $data = array_fill(0, 12, 0);

    // Get user role and franchise number for non-admin users
    $userRole = $this->session->userdata('role');
    $userId = $this->session->userdata('userId');

    // Fetch allowed franchise numbers (same as list.php)
    $allowedFranchises = [];
    if (in_array($userRole, [1, 14, 16, 23, 29, 31])) {
        $branchDetail = $this->bm->getBranchesFranchiseNumber();
        foreach ($branchDetail as $bd) {
            $allowedFranchises[] = $bd->franchiseNumber;
        }
    } else {
        $franchiseNumber = $this->session->userdata('franchiseNumber');
        $allowedFranchises[] = $franchiseNumber;
    }

    // Validate franchise number
    if ($franchiseNumber && !in_array($franchiseNumber, $allowedFranchises)) {
        $franchiseNumber = ''; // Ignore invalid franchise number
        log_message('error', "Invalid franchise number provided: {$this->input->post('franchiseNumber')}, ignoring filter");
    }

    // Build query
    $this->db->select("MONTH(dateoford) as month, COUNT(*) as count");
    $this->db->from('tbl_despatch');
    $this->db->where('YEAR(dateoford)', $year);
    if ($franchiseNumber) {
        $this->db->where('franchiseNumber', $franchiseNumber);
    }
    $this->db->group_by('MONTH(dateoford)');
    $query = $this->db->get();

    // Populate data array
    foreach ($query->result() as $row) {
        // Subtract 1 from month to match array index (1-12 to 0-11)
        $data[$row->month - 1] = (int)$row->count;
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['data' => $data]);
}


 function add()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Campaign';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
              $data['users'] = $this->dm->getUser();
            // $data['managers'] = $this->dm->getUsersByRoleId(15);
            $this->loadViews("despatch/add", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to add new user to the system
     */
   
    function addNewDespatch()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            // Form validation rules
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            // $this->form_validation->set_rules('modeOforder','Mode of Order','trim|required');
            // $this->form_validation->set_rules('others','Other Mode of Order','trim|callback_check_other_mode'); // Conditional validation
    
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
                $despatchTitle = $this->security->xss_clean($this->input->post('despatchTitle'));
                $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $AcInvoiceTitle = $this->security->xss_clean($this->input->post('AcInvoiceTitle'));
                $AcInvoiceAmount = $this->security->xss_clean($this->input->post('AcInvoiceAmount'));
                $AcDescription = $this->security->xss_clean($this->input->post('AcDescription'));
                $AcInvoicenumDesp = $this->security->xss_clean($this->input->post('AcInvoicenumDesp'));
                $orderNumber = $this->security->xss_clean($this->input->post('orderNumber'));
                $dateoford = $this->security->xss_clean($this->input->post('dateoford'));
                $prodListdespatch = $this->security->xss_clean($this->input->post('prodListdespatch'));
                $prodQtyDespatch = $this->security->xss_clean($this->input->post('prodQtyDespatch'));
                $dateofdespatch = $this->security->xss_clean($this->input->post('dateofdespatch'));
                // $modeOforder = $this->security->xss_clean($this->input->post('modeOforder'));
                // $others = $this->security->xss_clean($this->input->post('others')); // New field
                $transportCourior = $this->security->xss_clean($this->input->post('transportCourior'));
                $emailconfirmDispatchPOD = $this->security->xss_clean($this->input->post('emailconfirmDispatchPOD'));
                $podNumber = $this->security->xss_clean($this->input->post('podNumber'));
                $delStatus = $this->security->xss_clean($this->input->post('delStatus'));
                $delDate = $this->security->xss_clean($this->input->post('delDate'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $productDescription = $this->security->xss_clean($this->input->post('productDescription'));
                $franchisename = $this->security->xss_clean($this->input->post('franchisename'));

                $franchiseNumbers = implode(',',$franchiseNumberArray);
                
                $despatchInfo = array(
                    'brspFranchiseAssigned' => $brspFranchiseAssigned,
                    'despatchTitle' => $despatchTitle,
                    'AcInvoiceTitle' => $AcInvoiceTitle,
                    'AcInvoiceAmount' => $AcInvoiceAmount,
                    'AcDescription' => $AcDescription,
                    'franchiseNumber' => $franchiseNumbers,
                     'franchisename' => $franchisename,
                    'AcInvoicenumDesp' => $AcInvoicenumDesp,
                    'orderNumber' => $orderNumber,
                    'dateoford' => $dateoford,
                    'prodListdespatch' => $prodListdespatch,
                    'prodQtyDespatch' => $prodQtyDespatch,
                    'dateofdespatch' => $dateofdespatch,
                    // 'modeOforder' => $modeOforder,
                    // 'others' => $others, // Add otherMode
                    'transportCourior' => $transportCourior,
                    'emailconfirmDispatchPOD' => $emailconfirmDispatchPOD,
                    'podNumber' => $podNumber,
                    'delStatus' => $delStatus,
                    'delDate' => $delDate,
                    'productDescription' => $productDescription,
                    'description' => $description,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );
                
                $result = $this->dm->addNewDespatch($despatchInfo);
             //   print_r($despatchInfo);exit;
                if($result > 0) {
                    $this->load->model('Notification_model');
    
                    // Send Notification to Assigned Franchise User
                    if (!empty($brspFranchiseAssigned)) {
                        $notificationMessage = "<strong> Despatch :</strong>A new despatch has been added.";
                        $this->Notification_model->add_despatch_notification($brspFranchiseAssigned, $notificationMessage, $result);
                    }
                                // ✅ Notify Admins (roleId = 1, 14)
                $adminUsers = $this->bm->getUsersByRoles([1, 14, 16]);
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $adminUser) {
                        $this->Notification_model->add_despatch_notification($adminUser->userId, "A new despatch has been added.", $result);
                    }
                }
                    if(!empty($franchiseNumberArray)){
                        foreach ($franchiseNumberArray as $franchiseNumber){
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if(!empty($branchDetail)){
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Despatch";
                                $message = 'Dear '.$branchDetail->applicantName.' ';
                                $message .= 'You have been assigned a new Despatch. BY- '.$this->session->userdata("name").' ';
                                $message .= 'Please visit the portal.';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to,$subject,$message,$headers);
                                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
                                if (!empty($franchiseUser)) {
                                    $notificationMessage = "<strong> Despatch :</strong>A new despatch has been added.";
                                    $this->Notification_model->add_despatch_notification($franchiseUser->userId, $notificationMessage, $result);
                                }
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New Despatch created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Despatch creation failed');
                }
                
                redirect('despatch/despatchListing');
            }
        }
    }
    
    // Custom validation callback for otherMode
    public function check_other_mode($others)
    {
        $modeOforder = $this->input->post('modeOforder');
        if ($modeOforder === 'Others' && empty($others)) {
            $this->form_validation->set_message('check_other_mode', 'The Other Mode of Order field is required when Mode of Order is Others.');
            return FALSE;
        }
        return TRUE;
    }

    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
public function edit($despatchId = NULL)
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
        return;
    }

    if ($despatchId == null) {
        redirect('despatch/despatchListing');
    }

    $data['despatchInfo'] = $this->dm->getDespatchById($despatchId);

    if (empty($data['despatchInfo'])) {
        $this->session->set_flashdata('error', 'Despatch record not found');
        redirect('despatch/despatchListing');
    }

    $despatchInfo = $data['despatchInfo'];
    $orderNumber = $despatchInfo->orderNumber;

    $orderData = $this->getOrderFromWooAPI($orderNumber);
    $franchiseNumber = isset($orderData->billing->company) ? $orderData->billing->company : '';
//$data['productDescription'] = $productDescription;
    $data['franchiseNumberFromOrder'] = $franchiseNumber;
    $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
    $data['franchiseNumberArray'] = explode(',', $despatchInfo->brspFranchiseAssigned);
    $data['users'] = $this->dm->getUser();

    $this->global['pageTitle'] = 'Edit Despatch';
    $this->loadViews("despatch/edit", $this->global, $data, NULL);
}



    /**
     * This function is used to view the user information
     */
      function view($despatchId = NULL)
    { 
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($despatchId == null)
            {
                redirect('despatch/despatchListing');
            }
            $data['despatchInfo'] = $this->dm->getDespatchInfo($despatchId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $data['users'] = $this->dm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : View Despatch';            
            $this->loadViews("despatch/view", $this->global, $data, NULL);
        }
    }

    
    /**
     * This function is used to edit the user information
     */
  public function editDespatch()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        $this->load->model('notification_model'); // Load the notification model

        $despatchId = $this->input->post('despatchId');

        // Fetch existing data
        $existingDespatch = $this->dm->getDespatchById($despatchId);

        // Form validation rules
        $this->form_validation->set_rules('ordamtConfirmationStatus','Amount Confirmation Status','trim|required|max_length[1024]');
    

        if ($this->form_validation->run() == FALSE) {
            $this->edit($despatchId);
        } else {
            // Use existing values if new inputs are blank
            $despatchTitle = $this->security->xss_clean($this->input->post('despatchTitle'));
            $despatchTitle = !empty($despatchTitle) ? $despatchTitle : $existingDespatch->despatchTitle;

            $AcInvoicenumDesp = $this->security->xss_clean($this->input->post('AcInvoicenumDesp'));

            $dateoford = $this->security->xss_clean($this->input->post('dateoford'));
            $dateoford = !empty($dateoford) ? $dateoford : $existingDespatch->dateoford;

           /* $prodListdespatch = $this->security->xss_clean($this->input->post('prodListdespatch'));
            $prodListdespatch = !empty($prodListdespatch) ? $prodListdespatch : $existingDespatch->prodListdespatch;*/

            $prodQtyDespatch = $this->security->xss_clean($this->input->post('prodQtyDespatch'));
            $prodQtyDespatch = !empty($prodQtyDespatch) ? $prodQtyDespatch : $existingDespatch->prodQtyDespatch;

            $dateofdespatch = $this->security->xss_clean($this->input->post('dateofdespatch'));
            $dateofdespatch = !empty($dateofdespatch) ? $dateofdespatch : $existingDespatch->dateofdespatch;

            $description = $this->security->xss_clean($this->input->post('description'));
            $description = !empty($description) ? $description : $existingDespatch->description;

            $modeOforder = $this->security->xss_clean($this->input->post('modeOforder'));
            $modeOforder = !empty($modeOforder) ? $modeOforder : $existingDespatch->modeOforder;

            $others = $this->security->xss_clean($this->input->post('others'));
            $others = !empty($others) ? $others : $existingDespatch->others;

            $transportCourior = $this->security->xss_clean($this->input->post('transportCourior'));
            $transportCourior = !empty($transportCourior) ? $transportCourior : $existingDespatch->transportCourior;

            $emailconfirmDispatchPOD = $this->security->xss_clean($this->input->post('emailconfirmDispatchPOD'));
            $emailconfirmDispatchPOD = !empty($emailconfirmDispatchPOD) ? $emailconfirmDispatchPOD : $existingDespatch->emailconfirmDispatchPOD;

            $podNumber = $this->security->xss_clean($this->input->post('podNumber'));
            $podNumber = !empty($podNumber) ? $podNumber : $existingDespatch->podNumber;

            $AcInvoiceTitle = $this->security->xss_clean($this->input->post('AcInvoiceTitle'));
            $AcInvoiceAmount = $this->security->xss_clean($this->input->post('AcInvoiceAmount'));
            $AcDescription = $this->security->xss_clean($this->input->post('AcDescription'));

            $orderNumber = $this->security->xss_clean($this->input->post('orderNumber'));
            $orderNumber = !empty($orderNumber) ? $orderNumber : $existingDespatch->orderNumber;

             $ordamtConfirmationStatus = $this->security->xss_clean($this->input->post('ordamtConfirmationStatus'));
            $ordamtConfirmationStatus = !empty($ordamtConfirmationStatus) ? $ordamtConfirmationStatus : $existingDespatch->ordamtConfirmationStatus;

            $delStatus = $this->security->xss_clean($this->input->post('delStatus'));
            $delDate = $this->security->xss_clean($this->input->post('delDate'));
            $productDescription = $this->security->xss_clean($this->input->post('productDescription'));
            $orderNumber = $this->security->xss_clean($this->input->post('orderNumber'));
            $brspFranchiseAssigned = $this->security->xss_clean($this->input->post('brspFranchiseAssigned'));
            $brspFranchiseAssigned = !empty($brspFranchiseAssigned) ? $brspFranchiseAssigned : $existingDespatch->brspFranchiseAssigned;
           // 

            // Handle files
            $s3_file_link = [];
            if (!empty($_FILES["file"]["tmp_name"])) {
                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                rename($_FILES["file"]["tmp_name"], $destination);
                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();
                if (!empty($result_arr['ObjectURL'])) {
                    $s3_file_link[] = $result_arr['ObjectURL'];
                }
            }
            if (!empty($existingDespatch->acattachmentInvoiceS3File)) {
                $s3_file_link = array_merge(
                    explode(',', $existingDespatch->acattachmentInvoiceS3File),
                    $s3_file_link
                );
            }
            $s3files = implode(',', $s3_file_link);

            $s3_file_link1 = [];
            if (!empty($_FILES["vrlfile"]["tmp_name"])) {
                $dir1 = dirname($_FILES["vrlfile"]["tmp_name"]);
                $destination1 = $dir1 . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["vrlfile"]["name"];
                rename($_FILES["vrlfile"]["tmp_name"], $destination1);
                $storeFolder1 = 'attachements';
                $s3Result1 = $this->s3_upload->upload_file($destination1, $storeFolder1);
                $result_arr1 = $s3Result1->toArray();
                if (!empty($result_arr1['ObjectURL'])) {
                    $s3_file_link1[] = $result_arr1['ObjectURL'];
                }
            }
            if (!empty($existingDespatch->acattachmentVRLS3File)) {
                $s3_file_link1 = array_merge(
                    explode(',', $existingDespatch->acattachmentVRLS3File),
                    $s3_file_link1
                );
            }
            $s3files1 = implode(',', $s3_file_link1);

            // Prepare Update Data
            $despatchInfo = array(
                'despatchTitle' => $despatchTitle,
                 'brspFranchiseAssigned' => $brspFranchiseAssigned,
                'AcInvoicenumDesp' => $AcInvoicenumDesp,
                'AcInvoiceTitle' => $AcInvoiceTitle,
                'AcInvoiceAmount' => $AcInvoiceAmount,
                'AcDescription' => $AcDescription,
                'orderNumber' => $orderNumber,
                'dateoford' => $dateoford,
                'prodListdespatch' => $prodListdespatch,
                'prodQtyDespatch' => $prodQtyDespatch,
                'dateofdespatch' => $dateofdespatch,
                'modeOforder' => $modeOforder,
                'others' => $others,
                'transportCourior' => $transportCourior,
                'emailconfirmDispatchPOD' => $emailconfirmDispatchPOD,
                'podNumber' => $podNumber,
                'delStatus' => $delStatus,
                'delDate' => $delDate,
                'acattachmentVRLS3File' => $s3files1,
                'acattachmentInvoiceS3File' => $s3files,
                'description' => $description,
                'productDescription' => $productDescription,
                'ordamtConfirmationStatus' => $ordamtConfirmationStatus,    
                'updatedBy' => $this->vendorId,
                'updatedDtm' => date('Y-m-d H:i:s')
            );

            // Perform the update operation
            $result = $this->dm->editDespatch($despatchInfo, $despatchId);

if ($result) {
    $this->load->model('Notification_model');
     $this->load->model('Branches_model', 'bm');

    // Notify Assigned Franchise User
    if (!empty($brspFranchiseAssigned)) {
        $notificationMessage = "<strong>Despatch:</strong> Despatch ID " . $despatchId . " has been updated.";
        $this->Notification_model->add_despatch_notification($brspFranchiseAssigned, $notificationMessage, $despatchId);
    }

    // Notify Admins (roleId = 1, 14, 16, 23)
    $adminUsers = $this->bm->getUsersByRoles([1, 14, 16, 23]);
    if (!empty($adminUsers)) {
        foreach ($adminUsers as $adminUser) {
            $notificationMessage = "<strong>Despatch:</strong> Despatch ID " . $despatchId . " has been updated.";
            $this->Notification_model->add_despatch_notification($adminUser->userId, $notificationMessage, $despatchId);
        }
    }

    // Notify Franchise Users & Email
    $franchiseNumber = $this->dm->getFranchiseNumberByDespatchId($despatchId);
    if (!empty($franchiseNumber)) {
        $franchiseNumberArray = explode(',', $franchiseNumber); 
        foreach ($franchiseNumberArray as $franchiseNum) {
            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNum);
            if (!empty($branchDetail)) {
                // Email to Franchise
                $to = $branchDetail->officialEmailID;
                $subject = "Alert - eduMETA THE i-SCHOOL Despatch Updated";
                $message = 'Dear ' . $branchDetail->applicantName . ', ';
                $message .= 'Despatch information has been updated. BY- ' . $this->session->userdata("name") . ' ';
                $message .= 'Please visit the portal for details.';
                $headers = "From: Edumeta Team <noreply@theischool.com>\r\nBCC: dev.edumeta@gmail.com";
                mail($to, $subject, $message, $headers);

                // Notification to Franchise User
                $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNum);
                if (!empty($franchiseUser)) {
                    $notificationMessage = "<strong>Despatch:</strong> Despatch ID " . $despatchId . " has been updated.";
                    $this->Notification_model->add_despatch_notification($franchiseUser->userId, $notificationMessage, $despatchId);
                }
            }
        }
    }

    $this->session->set_flashdata('success', 'Despatch updated successfully');
} else {
    $this->session->set_flashdata('error', 'Despatch update failed');
}


            // Redirect to the despatch listing
            redirect('despatch/despatchListing');
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
private function getOrderFromWooAPI($orderId)
{
    $url = "https://shop.theischool.com/wp-json/wc/v3/orders/" . $orderId;
      $consumerKey = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579';
    $consumerSecret = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ":" . $consumerSecret);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response);
}

public function syncWooOrdersFull()
{
    $this->load->model('Despatch_model', 'dm');
    $this->dm->fetchAndInsertAllWooOrders();

    echo "All WooCommerce orders synced to tbl_despatch.";
}
 /*function orderinvoice($despatchId = NULL)
    { 
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($despatchId == null)
            {
                redirect('despatch/despatchListing');
            }
            $data['despatchInfo'] = $this->dm->getDespatchInfo($despatchId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $data['users'] = $this->dm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Order Invoice';            
            $this->loadViews("despatch/orderinvoice", $this->global, $data, NULL);
        }
    }*/
     public function orderinvoice($id = NULL)
    {
        if ($id == null) {
            redirect('despatch/despatchListing');
        }

        $consumerKey = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579';
        $consumerSecret = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';
        $apiUrl = "https://shop.theischool.com/wp-json/wc/v3/orders/{$id}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ":" . $consumerSecret);
        $response = curl_exec($ch);
        curl_close($ch);

        $order = json_decode($response, true);

        if (empty($order)) {
            show_404();
        }

        $data['order'] = $order;
        $data['showPrintButton'] = true; 
        $this->global['pageTitle'] = 'CodeInsect : Order Details';
        $this->loadViews("despatch/orderinvoice", $this->global, $data, NULL);
    }
public function vieworder($id = NULL)
    {
        if ($id == null) {
            redirect('despatch/despatchListing');
        }

        $consumerKey = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579';
        $consumerSecret = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';
        $apiUrl = "https://shop.theischool.com/wp-json/wc/v3/orders/{$id}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ":" . $consumerSecret);
        $response = curl_exec($ch);
        curl_close($ch);

        $order = json_decode($response, true);

        if (empty($order)) {
            show_404();
        }

        $data['order'] = $order;
        $data['showPrintButton'] = true; 
        $this->global['pageTitle'] = 'CodeInsect : Order Details';
        $this->loadViews("despatch/vieworder", $this->global, $data, NULL);
    }

}

?>