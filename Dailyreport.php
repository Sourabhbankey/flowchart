<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Salesrecord (SalesrecordController)
 * Salesrecord Class to control Salesrecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Dailyreport extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {  
        parent::__construct();
       $this->load->model('dailyreport_model', 'dr');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'dailyreport';
		
		
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('dailyreport/dailyreportListing');
    }
    
    /**
     * This function is used to load the salesrecord list
     */
 function dailyreportListing()
{
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');
    $data['userRole'] = $userRole;

    $searchText = $this->input->post('searchText') ? $this->security->xss_clean($this->input->post('searchText')) : '';
    $searchUserId = $this->input->get('searchUserId') ? $this->security->xss_clean($this->input->get('searchUserId')) : '';
    $fromDate = $this->input->get('fromDate') ? $this->security->xss_clean($this->input->get('fromDate')) : '';
    $toDate = $this->input->get('toDate') ? $this->security->xss_clean($this->input->get('toDate')) : '';

    $data['searchText'] = $searchText;
    $data['searchUserId'] = $searchUserId;
    $data['fromDate'] = $fromDate;
    $data['toDate'] = $toDate;

    $this->load->library('pagination');

    // Fetch count with filters
    $count = $this->dr->dailyreportListingCount($searchText, $userId, $userRole, $searchUserId, $fromDate, $toDate);
    $returns = $this->paginationCompress("dailyreportrecordListing/", $count, 500);

    // Fetch records with filters
    $data['records'] = $this->dr->dailyreportrecordListing($searchText, $returns["page"], $returns["segment"], $userId, $userRole, $searchUserId, $fromDate, $toDate);
    $data['users'] = $this->dr->getAllUsers();

    $this->global['pageTitle'] = 'CodeInsect : My Followup';
    $this->loadViews("dailyreport/list", $this->global, $data, NULL);
}


   /* }*/

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
            $this->global['pageTitle'] = 'CodeInsect : Add New followup';
			

          // $this->loadViews("salesrecord/add", $this->global, $data);
           $this->loadViews("dailyreport/add", $this->global, Null, NULL);
        }
		
   /* }*/
    
    /**
     * This function is used to add new user to the system
     */

public function addNewdailyreportrecord()
{
    $this->load->library('form_validation');
    
    $this->form_validation->set_rules('talktime', 'Talk Time', 'trim|required|max_length[1024]');

    if ($this->form_validation->run() == FALSE) {
        $this->add();
    } else {
        $userId = $this->session->userdata('userId');
        $roleId = $this->session->userdata('role');

        // Fetch teamLeadsales ID from tbl_users based on userId
        $this->db->select('teamLeadsales');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $userData = $query->row();
        $teamLeadsales = !empty($userData) ? $userData->teamLeadsales : NULL;

        // Get form data
        $dailyreportrecordInfo = array(
            'userId'               => $userId,
            'roleId'               => $roleId,
            'teamLeadsales'        => $teamLeadsales, // ✅ Added teamLeadsales
            'date'                 => $this->security->xss_clean($this->input->post('date')),
            'nooffreshcalls'       => $this->security->xss_clean($this->input->post('nooffreshcalls')),
            'nooftotalconnectedcalls' => $this->security->xss_clean($this->input->post('nooftotalconnectedcalls')),
            'noofoldfollowups'     => $this->security->xss_clean($this->input->post('noofoldfollowups')),
            'noofrecordingshared'  => $this->security->xss_clean($this->input->post('noofrecordingshared')),
            'prospects'            => $this->security->xss_clean($this->input->post('prospects')),
             'virtualmeetings'            => $this->security->xss_clean($this->input->post('virtualmeetings')),
            'converted'            => $this->security->xss_clean($this->input->post('converted')),
            'talktime'             => $this->security->xss_clean($this->input->post('talktime')),
            'description'          => $this->security->xss_clean($this->input->post('description'))
        );

        // Insert the new daily report record
        $result = $this->dr->addNewdailyreportrecord($dailyreportrecordInfo);

        if ($result > 0) {
                $notificationMessage = "<strong>Daily Confirmation:</strong> New  Daily Report confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 15, 29, 28 ,2])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_dailyreport_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
            $this->session->set_flashdata('success', 'New daily report record created successfully');
        } else {
            $this->session->set_flashdata('error', 'Daily report record creation failed');
        }

        redirect('dailyreport/dailyreportListing');
    }
}

    
    /**
     * This function is used load salesrecord edit information
     * @param number $salesrecId : Optional : This is salesrecord id
     */
    function edit($dailyreportId = NULL)
    {
       /*if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            if($dailyreportId == null)
            {
                redirect('dailyreport/dailyreportListing');
            }
            
            $data['dailyreportrecordInfo'] = $this->dr->getdailyreportrecordInfo($dailyreportId );

            $this->global['pageTitle'] = 'CodeInsect : Edit dailyreport';
            
            $this->loadViews("dailyreport/edit", $this->global, $data, NULL);
        }
    /*}*/
    
    
    /**
     * This function is used to edit the user information
     */
    function editdailyreportrecord()
    {
       /* if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            $this->load->library('form_validation');
            
            $dailyreportId = $this->input->post('dailyreportId');
            
         
            $this->form_validation->set_rules('talktime','Talktime','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($dailyreportId );
            }
            else
            { 
        
               $date   = $this->security->xss_clean($this->input->post('date'));
            $nooffreshcalls = $this->security->xss_clean($this->input->post('nooffreshcalls'));
            $nooftotalconnectedcalls  = $this->security->xss_clean($this->input->post('nooftotalconnectedcalls'));
            $noofoldfollowups  = $this->security->xss_clean($this->input->post('noofoldfollowups'));
             $noofrecordingshared  = $this->security->xss_clean($this->input->post('noofrecordingshared'));
              $prospects  = $this->security->xss_clean($this->input->post('prospects'));
               $converted  = $this->security->xss_clean($this->input->post('converted'));
                $talktime  = $this->security->xss_clean($this->input->post('talktime'));
                $virtualmeetings  = $this->security->xss_clean($this->input->post('virtualmeetings'));
            $description = $this->security->xss_clean($this->input->post('description'));
               $dailyreportrecordInfo = array(
                
                 'date' => $date, 
                
                 'nooffreshcalls' => $nooffreshcalls, 
                'nooftotalconnectedcalls' => $nooftotalconnectedcalls, 
                'noofoldfollowups' => $noofoldfollowups,
                'noofrecordingshared' => $noofrecordingshared,
                'prospects' => $prospects,
                'virtualmeetings' => $virtualmeetings,
                'converted' => $converted,
                'talktime' => $talktime, 
                'description' => $description
                
            );
               //print_r($followuprecordInfo);EXIT; 
                $result = $this->dr->editdailyreportrecord($dailyreportrecordInfo, 
                    $dailyreportId );
                
                if($result == true)
                {

                       $notificationMessage = "<strong>Daily Confirmation:</strong> New  Daily Report confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 15, 29, 28 ,2])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_dailyreport_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'Dailyreport updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Dailyreport updation failed');
                }
                
                redirect('dailyreport/dailyreportListing');
            }
        }
    /*}*/
	

/*public function talkTimeReport()
{
    $data['talkTimeStats'] = $this->dr->getTalkTimeStats();

    $this->global['pageTitle'] = 'Daily Report - Talk Time Statistics';
    $this->loadViews("dailyreport/view", $this->global, $data, NULL);
}*/
public function talkTimeReport()
{
    $this->load->model('Dailyreport_model');

    $month = $this->input->get('month'); // Get selected month from request
    $year = date('Y'); // Default to the current year

    if (!$month) {
        $month = date('m'); // Default to the current month
    }

    // Fetch Talk Time Stats based on selected month
    $data['talkTimeStats'] = $this->dr->getTalkTimeStats($month, $year);
    $data['selectedMonth'] = $month; // Pass selected month to the view

     $this->loadViews("dailyreport/view", $this->global, $data, NULL);
}

}

?>