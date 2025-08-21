<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Salesrecord (SalesrecordController)
 * Salesrecord Class to control Salesrecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Clients extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('clients_model', 'cm');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Clients';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('clients/clientsListing');
    }

    /**
     * This function is used to load the salesrecord list
     */
    public function clientsListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        $data['userRole'] = $userRole;

        // Get search filters from GET request
        $searchText = $this->input->get('searchText') ? $this->security->xss_clean($this->input->get('searchText')) : '';
        $searchUserId = $this->input->get('searchUserId') ? $this->security->xss_clean($this->input->get('searchUserId')) : '';
        $fromDate = $this->input->get('fromDate') ? $this->security->xss_clean($this->input->get('fromDate')) : '';
        $toDate = $this->input->get('toDate') ? $this->security->xss_clean($this->input->get('toDate')) : '';

        $data['searchText'] = $searchText;
        $data['searchUserId'] = $searchUserId;
        $data['fromDate'] = $fromDate;
        $data['toDate'] = $toDate;

        $this->load->library('pagination');

        $count = $this->cm->clientsListingCount($searchText, $userRole, $userId, $searchUserId, $fromDate, $toDate);

        $config['base_url'] = base_url("clients/clientsListing");
        $config['total_rows'] = $count;
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $config['use_page_numbers'] = TRUE;

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? (int)$this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];

        $data['total_records'] = $count;
        $data['offset'] = $offset;
        $data['per_page'] = $config['per_page'];

        $data['records'] = $this->cm->clientsrecordListing($searchText, $config["per_page"], $offset, $userRole, $userId, $searchUserId, $fromDate, $toDate);

        // Generate pagination links and retain filters
        $query_params = http_build_query(['searchText' => $searchText, 'searchUserId' => $searchUserId, 'fromDate' => $fromDate, 'toDate' => $toDate]);
        $data["links"] = str_replace('/clientsListing/', '/clientsListing/' . $query_params . '/', $this->pagination->create_links());

        $data['users'] = $this->cm->getAllUsers($userId, $userRole);


        $this->global['pageTitle'] = 'CodeInsect : My Clients';
        $this->loadViews("clients/list", $this->global, $data, NULL);
    }


    /**
     * This function is used to load the add new form
     */
    function add()

    {


        $this->global['pageTitle'] = 'CodeInsect : Add New Salesrecord';


        // $this->loadViews("salesrecord/add", $this->global, $data);
        $this->loadViews("clients/add", $this->global, Null, NULL);
    }
    public function listUsers()
    {
        // Fetch users with roleId 29 and 31
        $this->global['pageTitle'] = 'CodeInsect : Add New Salesrecord';

        $data['users'] = $this->cm->getUsersByRoles([29, 31]);


        $this->loadViews("clients/listusers", $this->global, $data, NULL);
    }
    /**
     * This function is used to add new user to the system
     */

    public function addNewClientsrecord()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('clientname', 'Client Name', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $userID = $this->session->userdata('userId');
            $userRole = $this->session->userdata('role');

            // Fetch teamlead ID from tbl_users based on userID
            $this->db->select('teamLeadsales');
            $this->db->from('tbl_users');
            $this->db->where('userId', $userID);
            $query = $this->db->get();
            $userData = $query->row();
            $teamLeadsales = !empty($userData) ? $userData->teamLeadsales : NULL;

            // Get form data
            $clientsrecordInfo = array(
                'userID' => $userID,
                'roleId' => $userRole,
                'teamLeadsales' => $teamLeadsales,  // Insert fetched teamlead ID
                'issuedon' => $this->security->xss_clean($this->input->post('issuedon')),
                'firstcall' => $this->security->xss_clean($this->input->post('firstcall')),
                'clientname' => $this->security->xss_clean($this->input->post('clientname')),
                'contactno' => $this->security->xss_clean($this->input->post('contactno')),
                'altercontactno' => $this->security->xss_clean($this->input->post('altercontactno')),
                'emailid' => $this->security->xss_clean($this->input->post('emailid')),
                'city' => $this->security->xss_clean($this->input->post('city')),
                'location' => $this->security->xss_clean($this->input->post('location')),
                'lastcall' => $this->security->xss_clean($this->input->post('lastcall')),
                'nextfollowup' => $this->security->xss_clean($this->input->post('nextfollowup')),
                'status' => $this->security->xss_clean($this->input->post('status')),
                'description' => $this->security->xss_clean($this->input->post('description')),
                'offername' => $this->security->xss_clean($this->input->post('offername')),
                'offercost' => $this->security->xss_clean($this->input->post('offercost')),
                'finalfranchisecost' => $this->security->xss_clean($this->input->post('finalfranchisecost')),
                'amountreceived' => $this->security->xss_clean($this->input->post('amountreceived')),
                'initialkitsoffered' => $this->security->xss_clean($this->input->post('initialkitsoffered')),
                'duedatefinalpayment' => $this->security->xss_clean($this->input->post('duedatefinalpayment')),
                'premisestatus' => $this->security->xss_clean($this->input->post('premisestatus')),
                'expectedinstallationdate' => $this->security->xss_clean($this->input->post('expectedinstallationdate')),
                'additionaloffer' => $this->security->xss_clean($this->input->post('additionaloffer')),


            );

            $result = $this->cm->addNewClientsrecord($clientsrecordInfo);

            if ($result > 0) {
                // Add notifications for all users
                $this->load->model('Notification_model', 'nm');

                // Send notifications to users with roleId 19, 14, 25
                $notificationMessage = "<strong>Client Confirmation:</strong> New Client confirmation";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [1, 14, 29, 28 , 2])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $notificationResult = $this->nm->add_client_notification($result, $notificationMessage, $userId);
                        if (!$notificationResult) {
                            log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                        }
                    }
                }
                $this->session->set_flashdata('success', 'New Client record created successfully');
            } else {
                $this->session->set_flashdata('error', 'Client record creation failed');
            }

            redirect('clients/clientsListing');
        }
    }




    /**
     * This function is used load salesrecord edit information
     * @param number $salesrecId : Optional : This is salesrecord id
     */
    function edit($clientId = NULL)
    {

        if ($clientId == null) {
            redirect('clients/clientsListing');
        }

        $data['clientsrecordInfo'] = $this->cm->getClientsrecordInfo($clientId);

        $this->global['pageTitle'] = 'CodeInsect : Edit Clients';

        $this->loadViews("clients/edit", $this->global, $data, NULL);
    }


    /**
     * This function is used to edit the user information
     */
    function editClientsrecord()
    {

        $this->load->library('form_validation');

        $clientId = $this->input->post('clientId');

        $this->form_validation->set_rules('clientname', 'clientname', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($clientsId);
        } else {

            $issuedon = $this->security->xss_clean($this->input->post('issuedon'));
            $firstcall = $this->security->xss_clean($this->input->post('firstcall'));
            $clientname = $this->security->xss_clean($this->input->post('clientname'));
            $contactno = $this->security->xss_clean($this->input->post('contactno'));
            $altercontactno = $this->security->xss_clean($this->input->post('altercontactno'));
            $emailid = $this->security->xss_clean($this->input->post('emailid'));
            $city = $this->security->xss_clean($this->input->post('city'));
            $location = $this->security->xss_clean($this->input->post('location'));
            $lastcall = $this->security->xss_clean($this->input->post('lastcall'));
            $nextfollowup = $this->security->xss_clean($this->input->post('nextfollowup'));
            $status = $this->security->xss_clean($this->input->post('status'));
            $description = $this->security->xss_clean($this->input->post('description'));
            $description2 = $this->security->xss_clean($this->input->post('description2'));
            $offername = $this->security->xss_clean($this->input->post('offername'));
            $offercost = $this->security->xss_clean($this->input->post('offercost'));


            // Create new record array
            $clientsrecordInfo = array(
                'issuedon' => $issuedon,
                'firstcall' => $firstcall,
                'clientname' => $clientname,
                'contactno' => $contactno,
                'altercontactno' => $altercontactno,
                'emailid' => $emailid,
                'city' => $city,
                'location' => $location,
                'lastcall' => $lastcall,
                'nextfollowup' => $nextfollowup,
                'status' => $status,
                'description' => $description,
                'offername' => $offername,
                'offercost' => $offercost,
                'description2' => $description2

            );

            $result = $this->cm->editClientsrecord($clientsrecordInfo, $clientId);
            //  print_r($clientsrecordInfo);exit;
            if ($result > 0) {
                 $this->load->model('Notification_model', 'nm');

                // Send notifications to users with roleId 19, 14, 25
                $notificationMessage = "<strong>Client Confirmation:</strong> Update Client confirmation";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [1, 14, 29, 28 , 2])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $notificationResult = $this->nm->add_client_notification($result, $notificationMessage, $userId);
                        if (!$notificationResult) {
                            log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                        }
                    }
                }
                $this->session->set_flashdata('success', 'Clients updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Clients updation failed');
            }

            redirect('Clients/clientsListing');
        }
    }

    public function getDashboardChartData()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');
        $year = $this->input->get('year') ?? date('Y'); // Default to current year if not provided

        try {
            // Query to count records per month based on issuedon
            $this->db->select('MONTH(issuedon) AS month, COUNT(*) AS issued_count');
            $this->db->from('tbl_results');
            $this->db->where('YEAR(issuedon)', $year);

            // Restrict data to the user's records unless they are admin (role 1 or 14)
            if ($userRole !== '1' && $userRole !== '14') {
                $this->db->where('createdBy', $userId);
            }

            $this->db->group_by('MONTH(issuedon)');
            $this->db->order_by('MONTH(issuedon)', 'ASC');

            $query = $this->db->get();
            $results = $query->result_array();

            // Initialize array for all 12 months with 0 values (1-12 for months)
            $monthlyData = array_fill(1, 12, 0);

            // Populate with actual counts
            foreach ($results as $row) {
                $monthlyData[$row['month']] = (int)$row['issued_count'];
            }

            // Return JSON array of 12 values (Jan-Dec)
            header('Content-Type: application/json');
            echo json_encode(array_values($monthlyData)); // Convert to 0-11 index for JS
            exit;
        } catch (Exception $e) {
            // Log error and return fallback data
            log_message('error', 'Chart data fetch failed: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(array_fill(0, 12, 0));
            exit;
        }
    }
}
