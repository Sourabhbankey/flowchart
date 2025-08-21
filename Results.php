<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Salesrecord (SalesrecordController)
 * Salesrecord Class to control Salesrecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Results extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {  
        parent::__construct();
       $this->load->model('results_model', 'rem');
       $this->load->model('followup_model', 'fm');
       $this->load->model('clients_model', 'cm');
       
        $this->isLoggedIn();
        $this->module = 'Results';
		
		
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('results/resultsListing');
    }
    
    /**
     * This function is used to load the salesrecord list
     */
function resultsListing()
{
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');
    $data['userRole'] = $userRole;

    // Get search and filter values (using GET instead of POST)
    $searchText = $this->input->get('searchText', true) ?? '';
    $userFilter = $this->input->get('userFilter', true) ?? '';
    $fromDate = $this->input->get('fromDate', true) ?? '';
    $toDate = $this->input->get('toDate', true) ?? '';

    // Store filters in the data array for the view
    $data['searchText'] = $searchText;
    $data['userFilter'] = $userFilter;
    $data['fromDate'] = $fromDate;
    $data['toDate'] = $toDate;

    $this->load->library('pagination');

    // Build query parameters for pagination links
    $queryString = http_build_query([
        'searchText' => $searchText,
        'userFilter' => $userFilter,
        'fromDate'   => $fromDate,
        'toDate'     => $toDate
    ]);

    // Get total count based on filters
    $totalRecords = $this->rem->resultsListingCount($searchText, $userRole, $userId, $fromDate, $toDate, $userFilter);

    // Pagination configuration (ensures filters persist)
    $config['base_url'] = base_url('results/resultsListing');
    $config['total_rows'] = $totalRecords;
    $config['per_page'] = 10;  // Display 10 records per page
    $config['uri_segment'] = 3;
    $config['use_page_numbers'] = TRUE;
    $config['first_url'] = base_url('results/resultsListing/1');

    $this->pagination->initialize($config);

    // Determine the current page
    $page = ($this->uri->segment(3)) ? (int)$this->uri->segment(3) : 1;
    $offset = ($page - 1) * $config['per_page'];

    // Set serial number dynamically
    $startRecord = $offset + 1;
    $endRecord = min($offset + $config['per_page'], $totalRecords);

    $data['serialNumber'] = $startRecord;
    $data['startRecord'] = $startRecord;
    $data['endRecord'] = $endRecord;
    $data['totalRecords'] = $totalRecords;

    // Fetch filtered records
    $data['records'] = $this->rem->resultsListing($searchText, $offset, $config["per_page"], $userRole, $userId, $fromDate, $toDate, $userFilter);
    $data["links"] = $this->pagination->create_links();

    // Get all users for filtering
   $data['users'] = $this->cm->getAllUsers($userId, $userRole);

    $this->global['pageTitle'] = 'CodeInsect : My Results';
    $this->loadViews("results/list", $this->global, $data, NULL);
}

public function fetch_leaderboard() {
  $userId = $this->session->userdata('userId');
  $role = $this->session->userdata('role');
  $selectedMonth = $this->input->post('month');

  // Role-based filtering
  $whereClause = '';
  $params = [];
  if (in_array($role, [1, 2, 14, 28])) {
    $whereClause = '1 = 1';
  } elseif ($role == 31) {
    $whereClause = '(r.userID = ? OR r.userID IN (SELECT userId FROM tbl_users WHERE teamLeadsales = ?))';
    $params = [$userId, $userId];
  } else {
    $whereClause = 'r.userID = ?';
    $params = [$userId];
  }

  // Date filtering
  $startDate = $selectedMonth ? $selectedMonth . '-01' : '';
  $endDate = $selectedMonth ? date('Y-m-t', strtotime($startDate)) : '';
  $dateClause = $selectedMonth ? 'AND DATE(r.issuedon) BETWEEN ? AND ?' : '';
  $queryParams = $selectedMonth ? array_merge($params, [$startDate, $endDate]) : $params;

  // Query
  $query = $this->db->query("
    SELECT 
        u.name AS UserName,
        COUNT(r.resultsId) AS SalesCount,
        GROUP_CONCAT(r.clientname) AS ClientNames
    FROM tbl_results_sales r
    JOIN tbl_users u ON u.userId = r.userID
    WHERE $whereClause $dateClause AND r.isDeleted = 0
    GROUP BY r.userID
    ORDER BY SalesCount DESC
  ", $queryParams);

  $results = $query->result();

  // Output HTML
  if (!empty($results)) {
    echo "<table class='table table-striped' id='leaderboardTable'>
          <thead>
              <tr class='table-primary'>
                  <th>Rank</th>
                  <th>User Name</th>
                  <th>Sales Count</th>
              </tr>
          </thead>
          <tbody>";
    $rank = 1;
    foreach ($results as $row) {
      echo "<tr>
              <td>$rank</td>
              <td>{$row->UserName}</td>
              <td>{$row->SalesCount}</td>
            </tr>";
      $rank++;
    }
    echo "</tbody></table>";
  } else {
    echo "<p>No sales data found for " . ($selectedMonth ? date('F Y', strtotime($selectedMonth)) : 'all months') . ".</p>";
  }
}

public function getClientChartData()
{
    $year = $this->input->get('year') ?? date('Y');
    $userFilter = $this->input->get('userFilter');
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');

    try {
        if (!$userId || !$userRole) {
            throw new Exception('User ID or Role not found in session');
        }

        // Common conditions for tbl_results_sales
        $commonConditions = function() use ($year) {
            $this->db->where('YEAR(issuedon)', $year);
            $this->db->where('isDeleted', 0);
            // Add a condition for "converted" status if applicable, e.g.:
            // $this->db->where('premisestatus', 'Converted'); // Adjust based on your schema
        };

        // Role-based user scope (aligned with getUsersForFilter and hierarchy)
        $userScope = function() use ($userRole, $userId, $userFilter) {
            $this->db->select('userId');
            $this->db->from('tbl_users');
            $this->db->where('isDeleted', 0);

            switch ($userRole) {
                case '1': // Admin
                case '14': // Another admin-like role
                    if ($userFilter) {
                        $this->db->where('userId', $userFilter);
                    }
                    break;
                case '2': // Sales Head
                    if ($userFilter) {
                        $this->db->where('userId', $userFilter);
                    } else {
                        $this->db->where_in('roleId', [29, 31, 2]); // AOMs, TLs, and self
                    }
                    break;
                case '31': // Team Leader
                    if ($userFilter) {
                        $this->db->where('userId', $userFilter);
                    } else {
                        $this->db->where('teamLeadsales', $userId); // AOMs under this TL
                        $this->db->or_where('userId', $userId); // Include self
                    }
                    break;
                case '29': // AOM
                    $this->db->where('userId', $userId); // Only self
                    break;
                case '25': // General Manager
                case '32': // Sales Manager
                    if ($userFilter) {
                        $this->db->where('userId', $userFilter);
                    } else {
                        $this->db->where('generalManagerId', $userId); // Team under GM/SM
                        $this->db->or_where('userId', $userId); // Include self
                    }
                    break;
                default:
                    $this->db->where('userId', $userId); // Default to self
                    break;
            }
            return $this->db->get()->result_array();
        };

        // Fetch allowed user IDs based on scope
        $allowedUsers = array_column($userScope(), 'userId');

        // 1. Fetch Monthly Converted Leads Data
        $this->db->select('MONTH(issuedon) AS month, COUNT(*) AS count');
        $this->db->from('tbl_results_sales');
        $commonConditions();
        if ($userFilter) {
            $this->db->where('userId', $userFilter);
        } else {
            $this->db->where_in('userId', $allowedUsers);
        }
        $this->db->group_by('MONTH(issuedon)');
        $this->db->order_by('MONTH(issuedon)', 'ASC');
        $clientQuery = $this->db->get();
        if ($clientQuery === false) {
            throw new Exception('Converted leads query failed: ' . $this->db->error()['message']);
        }
        $results = $clientQuery->result_array();
        $monthlyClients = array_fill(0, 12, 0);
        foreach ($results as $row) {
            $monthIndex = (int)$row['month'] - 1;
            $monthlyClients[$monthIndex] = (int)$row['count'];
        }

        // 2. Fetch Distinct Users Contributing to Converted Leads
        $this->db->select('MONTH(issuedon) AS month, COUNT(DISTINCT userId) AS total_count');
        $this->db->from('tbl_results_sales');
        $commonConditions();
        if ($userFilter) {
            $this->db->where('userId', $userFilter);
        } else {
            $this->db->where_in('userId', $allowedUsers);
        }
        $this->db->group_by('MONTH(issuedon)');
        $this->db->order_by('MONTH(issuedon)', 'ASC');
        $userQuery = $this->db->get();
        if ($userQuery === false) {
            throw new Exception('User count query failed: ' . $this->db->error()['message']);
        }
        $userResults = $userQuery->result_array();
        $totalRecordsByUser = array_fill(0, 12, 0);
        foreach ($userResults as $row) {
            $monthIndex = (int)$row['month'] - 1;
            $totalRecordsByUser[$monthIndex] = (int)$row['total_count'];
        }

        // Response
        $response = [
            'clients' => $monthlyClients, // Converted leads per month
            'totalRecordsByUser' => $totalRecordsByUser // Distinct users per month
        ];

        log_message('debug', 'Chart data for year ' . $year . ' (Role: ' . $userRole . ', UserID: ' . $userId . '): ' . json_encode($response));

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($response));
    } catch (Exception $e) {
        log_message('error', 'Chart data fetch failed: ' . $e->getMessage());
        $this->output
             ->set_status_header(500)
             ->set_content_type('application/json')
             ->set_output(json_encode([
                 'clients' => array_fill(0, 12, 0),
                 'totalRecordsByUser' => array_fill(0, 12, 0),
                 'error' => $e->getMessage()
             ]));
    }
}

public function getUsersForFilter()
{
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');

    try {
        $users = $this->cm->getAllUsers($userId, $userRole);
        $response = ['users' => $users];
        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($response));
    } catch (Exception $e) {
        log_message('error', 'User fetch failed: ' . $e->getMessage());
        $this->output
             ->set_status_header(500)
             ->set_content_type('application/json')
             ->set_output(json_encode(['error' => $e->getMessage()]));
    }
}
 /*}*/

    /**
     * This function is used to load the add new form
     */
    function add()

    {   
	
		
		
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->global['pageTitle'] = 'CodeInsect : Add Results';
			

          // $this->loadViews("salesrecord/add", $this->global, $data);
           $this->loadViews("results/add", $this->global, Null, NULL);
        }
		
    }
    
    /**
     * This function is used to add new user to the system
     */

  public function addNewResultsrecord()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            // Define validation rules
            $this->form_validation->set_rules('finalfranchisecost', 'Final Franchise Cost', 'trim|required|numeric');
            $this->form_validation->set_rules('agreementtenure', 'Agreement Tenure', 'trim|required|numeric');
            $this->form_validation->set_rules('amountreceived', 'Amount Received', 'trim|required|numeric');
            $this->form_validation->set_rules('initialkitsoffered', 'Initial Kits Offered', 'trim|required|numeric');
            $this->form_validation->set_rules('duedatefinalpayment', 'Due Date Final Payment', 'trim|required');
            $this->form_validation->set_rules('premisestatus', 'Premise Status', 'trim|required');
            $this->form_validation->set_rules('expectedinstallationdate', 'Expected Installation Date', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                // Get form data
                $finalfranchisecost = $this->security->xss_clean($this->input->post('finalfranchisecost'));
                $agreementtenure = $this->security->xss_clean($this->input->post('agreementtenure'));
                $amountreceived = $this->security->xss_clean($this->input->post('amountreceived'));
                $initialkitsoffered = $this->security->xss_clean($this->input->post('initialkitsoffered'));
                $duedatefinalpayment = $this->security->xss_clean($this->input->post('duedatefinalpayment'));
                $premisestatus = $this->security->xss_clean($this->input->post('premisestatus'));
                $expectedinstallationdate = $this->security->xss_clean($this->input->post('expectedinstallationdate'));
                $additionaloffer = $this->security->xss_clean($this->input->post('additionaloffer'));
                $clientId = $this->security->xss_clean($this->input->post('clientId')); // Assuming clientId is submitted

                // Create new record array
                $resultsrecordInfo = array(
                    'clientId' => $clientId,
                    'finalfranchisecost' => $finalfranchisecost,
                    'agreementtenure' => $agreementtenure,
                    'amountreceived' => $amountreceived,
                    'initialkitsoffered' => $initialkitsoffered,
                    'duedatefinalpayment' => $duedatefinalpayment,
                    'premisestatus' => $premisestatus,
                    'expectedinstallationdate' => $expectedinstallationdate,
                    'additionaloffer' => $additionaloffer,
                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );

                // Insert the new results record
                $result = $this->rem->addNewResultsrecord($resultsrecordInfo);

                if ($result > 0) {
                    // Fetch client details to get clientname and franchise email
                    $clientInfo = $this->cm->getClientInfo($clientId);
                    $clientname = !empty($clientInfo->clientname) ? $clientInfo->clientname : 'Unknown Client';

                    // Email notification to admin
                    $adminEmail = 'dev.edumeta@gmail.com'; // Replace with actual admin email
                    $adminSubject = "Alert - eduMETA THE i-SCHOOL New Results Record Created";
                    $adminMessage = 'A new results record has been created. ';
                    $adminMessage .= 'Client Name: ' . $clientname . ', Record ID: ' . $result . '. ';
                    $adminMessage .= 'Please visit the portal for details.';
                    $adminHeaders = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                    mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);

                    $this->session->set_flashdata('success', 'Results record created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Results record creation failed');
                }

                redirect('results/resultsListing');
            }
        }
    }
    
    /**
     * This function is used load salesrecord edit information
     * @param number $salesrecId : Optional : This is salesrecord id
     */
    function edit($resultsId = NULL)
    {
       /*if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            if($resultsId == null)
            {
                redirect('results/resultsListing');
            }
            
            $data['resultsrecordInfo'] = $this->rem->getResultsrecordInfo($resultsId);
             $data['incentiverecordInfo'] = $this->rem->getResultsincentiverecordInfo($resultsId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Results';
            
            $this->loadViews("results/edit", $this->global, $data, NULL);
        }
    /*}*/
     function view($resultsId = NULL)
    {
       /*if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            if($resultsId == null)
            {
                redirect('results/resultsListing');
            }
            
            $data['resultsrecordInfo'] = $this->rem->getResultsrecordInfo($resultsId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Results';
            
            $this->loadViews("results/view", $this->global, $data, NULL);
        }

/*public function viewincentive()
{
    $month = $this->input->get('month'); 
    $userId = $this->input->get('userId'); 

    $data['monthly_incentives'] = $this->rem->getAllMonthlyIncentives($month, $userId);
    $data['users'] = $this->rem->getAllUsers();
    
    $this->global['pageTitle'] = 'View Incentive';
    $this->loadViews("results/viewincentive", $this->global, $data, NULL);
}*/
public function viewincentive()
{
    $month = $this->input->get('month'); 
    $userId = $this->input->get('userId'); 
    $roleId = $this->session->userdata('role'); // Get logged-in user's role
    $loggedInUserId = $this->session->userdata('userId'); // Get logged-in user ID

    if ($roleId == 2) {
        // Role 2 sees all incentives
        $data['monthly_incentives'] = $this->rem->getAllMonthlyIncentives($month);
    } elseif ($roleId == 31) {
        // Role 31 sees their own incentives and those of assigned Role 29 users
        $assignedUsers = $this->rem->getAssignedUsers($loggedInUserId, 29); // Fetch assigned users
        $userIds = array_merge([$loggedInUserId], array_column($assignedUsers, 'userId'));
        $data['monthly_incentives'] = $this->rem->getAllMonthlyIncentives($month, $userIds);
    } elseif ($roleId == 29) {
        // Role 29 sees only their own incentives
        $data['monthly_incentives'] = $this->rem->getAllMonthlyIncentives($month, [$loggedInUserId]);
    } else {
        $data['monthly_incentives'] = [];
    }

    $data['users'] = $this->rem->getAllUsers();
    $this->global['pageTitle'] = 'View Incentive';
    $this->loadViews("results/viewincentive", $this->global, $data, NULL);
}


    /**
     * This function is used to edit the user information
     */
 public function editResultsrecord()
    { 
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $resultsId = $this->input->post('resultsId');
            
            // Define validation rules
            $this->form_validation->set_rules('finalfranchisecost', 'Final Franchise Cost', 'trim|required|numeric');
            $this->form_validation->set_rules('agreementtenure', 'Agreement Tenure', 'trim|required|numeric');
            $this->form_validation->set_rules('amountreceived', 'Amount Received', 'trim|required|numeric');
            $this->form_validation->set_rules('initialkitsoffered', 'Initial Kits Offered', 'trim|required|numeric');
            $this->form_validation->set_rules('duedatefinalpayment', 'Due Date Final Payment', 'trim|required');
            $this->form_validation->set_rules('premisestatus', 'Premise Status', 'trim|required');
            $this->form_validation->set_rules('expectedinstallationdate', 'Expected Installation Date', 'trim|required');
            $this->form_validation->set_rules('incentivereceived', 'Incentive Received', 'trim|numeric');
            $this->form_validation->set_rules('incentivereceivedSTL', 'Incentive Received STL', 'trim|numeric');

            if($this->form_validation->run() == FALSE)
            {
                $this->edit($resultsId);
            }
            else
            {
                $finalfranchisecost = $this->security->xss_clean($this->input->post('finalfranchisecost'));
                $agreementtenure = $this->security->xss_clean($this->input->post('agreementtenure'));
                $amountreceived = $this->security->xss_clean($this->input->post('amountreceived'));
                $initialkitsoffered = $this->security->xss_clean($this->input->post('initialkitsoffered'));
                $duedatefinalpayment = $this->security->xss_clean($this->input->post('duedatefinalpayment'));
                $premisestatus = $this->security->xss_clean($this->input->post('premisestatus'));
                $expectedinstallationdate = $this->security->xss_clean($this->input->post('expectedinstallationdate'));
                $additionaloffer = $this->security->xss_clean($this->input->post('additionaloffer'));
                $incentivereceived = $this->security->xss_clean($this->input->post('incentivereceived'));
                $incentivereceivedSTL = $this->security->xss_clean($this->input->post('incentivereceivedSTL'));
                $offername = $this->security->xss_clean($this->input->post('offername'));
                $clientId = $this->security->xss_clean($this->input->post('clientId')); // Assuming clientId is submitted

                $resultsrecordInfo = array(
                    'clientId' => $clientId,
                    'finalfranchisecost' => $finalfranchisecost,
                    'agreementtenure' => $agreementtenure,
                    'amountreceived' => $amountreceived,
                    'initialkitsoffered' => $initialkitsoffered,
                    'duedatefinalpayment' => $duedatefinalpayment,
                    'premisestatus' => $premisestatus,
                    'expectedinstallationdate' => $expectedinstallationdate,
                    'additionaloffer' => $additionaloffer,
                    'incentivereceived' => $incentivereceived,
                    'incentivereceivedSTL' => $incentivereceivedSTL,
                    'offername' => $offername,
                    'is_edited' => 1,
                    'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->rem->editResultsrecord($resultsrecordInfo, $resultsId);

                if ($result) {
                    // Fetch client details to get clientname and franchise email
                    $clientInfo = $this->cm->getClientInfo($clientId);
                    $clientname = !empty($clientInfo->clientname) ? $clientInfo->clientname : 'Unknown Client';

                    // Update incentive record
                    $userId = $this->rem->getUserIdByResultsId($resultsId);
                    if (!empty($userId) && (!empty($incentivereceived) || !empty($incentivereceivedSTL))) {
                        $existingIncentive = $this->rem->getIncentiveByResultsId($resultsId);
                        $incentiveData = array(
                            'userId' => $userId,
                            'resultsId' => $resultsId,
                            'incentivereceived' => $incentivereceived,
                            'incentivereceivedSTL' => $incentivereceivedSTL,
                        );

                        if ($existingIncentive) {
                            $this->rem->updateIncentiveRecord($incentiveData, $resultsId);
                        } else {
                            $incentiveData['created_at'] = date('Y-m-d H:i:s');
                            $this->rem->addIncentiveRecord($incentiveData);
                        }
                    }

                    // Email notification to admin
                    $adminEmail = 'dev.edumeta@gmail.com'; // Replace with actual admin email
                    $adminSubject = "Alert - eduMETA THE i-SCHOOL Results Record Updated";
                    $adminMessage = 'A results record has been updated. ';
                    $adminMessage .= 'Client Name: ' . $clientname . ', Record ID: ' . $resultsId . '. ';
                    $adminMessage .= 'Please visit the portal for details.';
                    $adminHeaders = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                    mail($adminEmail, $adminSubject, $adminMessage, $adminHeaders);

                    $this->session->set_flashdata('success', 'Results record updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Results record updation failed');
                }
                
                redirect('results/resultsListing');
            }
        }
    }
public function fetchIncentives() {
    // Retrieve userId and userRole
    $userId = $this->input->post('userId', true);
    $userRole = $this->session->userdata('role'); // Get roleId

    // Check if userId and roleId are set
    if (empty($userId) || empty($userRole)) {
        echo "<tr><td colspan='5' class='text-center'>Error: Missing User ID or Role!</td></tr>";
        return;
    }

    // Fetch incentives from the database
   $this->db->select("
    tbl_users.name, 
    tbl_clients_sales.clientname, 
    DATE_FORMAT(tbl_incentive.created_at, '%Y-%m-%d') AS incentive_date, 
    tbl_incentive.incentivereceived, 
    tbl_incentive.incentivereceivedSTL
");
$this->db->from("tbl_incentive");
$this->db->join("tbl_results_sales", "tbl_results_sales.resultsId = tbl_incentive.resultsId", "left");
$this->db->join("tbl_clients_sales", "tbl_clients_sales.clientId = tbl_results_sales.clientId", "left");
$this->db->join("tbl_users", "tbl_users.userId = tbl_results_sales.userId", "left");
$this->db->where("tbl_incentive.userId", $userId);
$this->db->order_by("tbl_incentive.created_at", "DESC");

    $query = $this->db->get();
    $incentives = $query->result_array();

    if (!$query) {
        echo "Database error: " . $this->db->error()['message'];  // Show error if the query fails
        return;
    }

    // Initialize total variables
    $totalSalesAOM = 0;
    $totalTL = 0;
    $grandTotal = 0;

    // Check if there are any incentives
    if (!empty($incentives)) {
        // Loop through the incentives to calculate totals and display data
        foreach ($incentives as $incentive) {
            $totalSalesAOM += $incentive['incentivereceived'];
            $totalTL += $incentive['incentivereceivedSTL'];
            $totalIncentive = $incentive['incentivereceived'] + $incentive['incentivereceivedSTL'];
            $grandTotal += $totalIncentive;

            // Output the row for this incentive
            echo "<tr>
                   
                    <td>" . htmlspecialchars($incentive['clientname'] ?? 'Unknown') . "</td>  <!-- Client Name -->
                    <td>" . htmlspecialchars($incentive['incentive_date'] ?? 'Unknown') . "</td>
                    <td>" . number_format($incentive['incentivereceived'], 2) . "</td>";

            // Only display "Incentive Received By TL" & "Total-(SAOM + TL)" if roleId != 29
            if ($userRole != 29) {
                echo "<td>" . number_format($incentive['incentivereceivedSTL'], 2) . "</td>
                      <td>" . number_format($totalIncentive, 2) . "</td>";
            }

            echo "</tr>";
        }

        // Output the total row
        echo "<tr class='incent-total'>
                <td colspan='2' class='text-right'>Total:</td>
                <td>" . number_format($totalSalesAOM, 2) . "</td>";

        // Include total for TL if userRole is not 29
        if ($userRole != 29) {
            echo "<td>" . number_format($totalTL, 2) . "</td>
                  <td>" . number_format($grandTotal, 2) . "</td>";
        }

        echo "</tr>";
    } else {
        // No incentives found
        echo "<tr><td colspan='5' class='text-center'>No incentives found</td></tr>";
    }
}



}

?>
