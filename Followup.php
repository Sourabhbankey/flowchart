<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Salesrecord (SalesrecordController)
 * Salesrecord Class to control Salesrecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Followup extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {  
        parent::__construct();
       $this->load->model('followup_model', 'fm');
       $this->load->model('clients_model', 'cm');
        $this->isLoggedIn();
        $this->module = 'Followup';
		
		
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('followup/followupListing');
    }
    
    /**
     * This function is used to load the salesrecord list
     */
public function followupListing()
{
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');
    $data['userRole'] = $userRole;

    // Fetch input values with filtering
    $searchText = $this->input->post('searchText', true) ?? '';
    $fromDate = $this->input->post('fromDate', true) ?? '';
    $toDate = $this->input->post('toDate', true) ?? '';
    $userFilter = $this->input->post('userFilter', true) ?? '';

    $data['searchText'] = $searchText;
    $data['fromDate'] = $fromDate;
    $data['toDate'] = $toDate;
    $data['userFilter'] = $userFilter;

    $this->load->library('pagination');

    // Get record count with role-based filtering
    $count = $this->fm->followupListingCount($searchText, $fromDate, $toDate, $userRole, $userId, $userFilter);
    $returns = $this->paginationCompress("followupListing/", $count, 500);

    // Fetch records with pagination
    $data['records'] = $this->fm->followuprecordListing($searchText, $returns["page"], $returns["segment"], $userRole, $userId, $fromDate, $toDate, $userFilter);
    
    // Fetch users for dropdown based on role
    $data['users'] = $this->cm->getAllUsers($userId, $userRole);
    
    // Pass pagination data to the view
    $data['returns'] = $returns;

    $this->global['pageTitle'] = 'CodeInsect : My Followup';
    $this->loadViews("followup/list", $this->global, $data, NULL);
}




   /* }*/

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
            $this->global['pageTitle'] = 'CodeInsect : Add New followup';
			

          // $this->loadViews("salesrecord/add", $this->global, $data);
           $this->loadViews("followup/add", $this->global, Null, NULL);
        }
		
    }
    
    /**
     * This function is used to add new user to the system
     */

public function addNewFollowuprecord()
{
     if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        /*$this->form_validation->set_rules('productName', 'Title', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');*/

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
             $userID = $this->session->userdata('userId'); 
        $userRole = $this->session->userdata('role');

        // Fetch teamlead ID from tbl_users based on userID
        $this->db->select('teamlead');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userID);
        $query = $this->db->get();
        $userData = $query->row();
        $teamlead = !empty($userData) ? $userData->teamlead : NULL;
            // Get form data

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

          

            // Create new record array
            $followuprecordInfo = array(
                'roleId' => $userRole,
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
                'description2' => $description2
                
            );

            // Insert the new sales record
            $result = $this->cm->addNewfollowuprecord($followuprecordInfo);

            if ($result > 0) {

                 $notificationMessage = "<strong>Follow up Confirmation:</strong> New Follow up confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 29, 28 , 2])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_followup_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }


               
                $this->session->set_flashdata('success', 'New followup record created successfully ');
            } else {
                $this->session->set_flashdata('error', 'followup record creation failed');
            }

            redirect('followup/followupListing');
        }
    }
    
}

    
    /**
     * This function is used load salesrecord edit information
     * @param number $salesrecId : Optional : This is salesrecord id
     */
    function edit($followupId = NULL)
    {
       /*if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {*/
            if($followupId == null)
            {
                redirect('followup/followupListing');
            }
            
            $data['followuprecordInfo'] = $this->fm->getFollowuprecordInfo($followupId);
 $data['users'] = $this->fm->getUsersByRole([29, 31]);
            $this->global['pageTitle'] = 'CodeInsect : Edit followup';
            
            $this->loadViews("followup/edit", $this->global, $data, NULL);
        }
    /*}*/
    
    
    /**
     * This function is used to edit the user information
     */
 public function editFollowuprecord()
{
    $this->load->library('form_validation');
    
    $followupId = $this->input->post('followupId');

    $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

    if ($this->form_validation->run() == FALSE) {
        $this->edit($followupId);
    } else { 
        $description = $this->security->xss_clean($this->input->post('description'));
        $lastcall = $this->security->xss_clean($this->input->post('lastcall'));
        $nextfollowup = $this->security->xss_clean($this->input->post('nextfollowup'));
        $description2 = $this->security->xss_clean($this->input->post('description2'));
        $description3 = $this->security->xss_clean($this->input->post('description3'));
        $description4 = $this->security->xss_clean($this->input->post('description4'));
        $status = $this->security->xss_clean($this->input->post('status'));
        $salesTeamAssign = $this->security->xss_clean($this->input->post('salesTeamAssign'));
         $finalfranchisecost = $this->security->xss_clean($this->input->post('finalfranchisecost'));
          $agreementtenure = $this->security->xss_clean($this->input->post('agreementtenure'));
            $amountreceived = $this->security->xss_clean($this->input->post('amountreceived'));
            $initialkitsoffered = $this->security->xss_clean($this->input->post('initialkitsoffered'));
            $duedatefinalpayment = $this->security->xss_clean($this->input->post('duedatefinalpayment'));
            $premisestatus = $this->security->xss_clean($this->input->post('premisestatus'));
            $expectedinstallationdate = $this->security->xss_clean($this->input->post('expectedinstallationdate'));
            $additionaloffer = $this->security->xss_clean($this->input->post('additionaloffer'));

        $followuprecordInfo = array(
            'description' => $description,
            'lastcall' => $lastcall, 
            'nextfollowup' => $nextfollowup,
            'description2' => $description2,
            'description3' => $description3,
            'description4' => $description4,
            'status' => $status,
            'salesTeamAssign'=> $salesTeamAssign,
            'finalfranchisecost' => $finalfranchisecost,
                'agreementtenure' => $agreementtenure,
                'amountreceived' => $amountreceived,
                'initialkitsoffered' => $initialkitsoffered,
                'duedatefinalpayment' => $duedatefinalpayment, 
                'premisestatus' => $premisestatus, 
                'expectedinstallationdate' => $expectedinstallationdate,
                'additionaloffer' => $additionaloffer
        );

        // **Update Record Instead of Insert**
        $result = $this->fm->editFollowuprecord($followuprecordInfo, $followupId);

        if ($result) {

               $notificationMessage = "<strong>Follow up Confirmation:</strong> Update Follow up confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 29, 28 , 2])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_followup_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }

            $this->session->set_flashdata('success', 'Follow-up updated successfully');
        } else {
            $this->session->set_flashdata('error', 'Follow-up update failed');
        }

        redirect('Followup/followupListing');
    }
}

    /*}*/
	



}

?>