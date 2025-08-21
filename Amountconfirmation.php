<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Announcement (AnnouncementController)
 * Announcement Class to control Announcement related operations.
 * @author : Ashish 
 * @version : 1
 * @since : 24 Jul 2024
 */
class Amountconfirmation extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Amountconfirmation_model', 'amnt');
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
        redirect('amountconfirmation/amountconfirmationListing');
    }

    /**
     * This function is used to load the announcement list
     */
    public function amountconfirmationListing()
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
        $count = $this->amnt->amountconfirmationListingCount($searchText);

        // Pagination configuration
        $config = array();
        $config["base_url"] = base_url("amountconfirmation/amountconfirmationListing");
        $config["total_rows"] = $count;
        $config["per_page"] = 10;
        $config["uri_segment"] = 3; // the page segment is expected at index 3 of the URI

        // Initialize pagination
        $this->pagination->initialize($config);

        // Get the current page number
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        // Fetch records for the current page
        $data['records'] = $this->amnt->amountconfirmationListing($searchText, $config['per_page'], $page);

        // Get the pagination links
        $data['pagination'] = $this->pagination->create_links();

        // Record range and total
        $data['start'] = $page + 1;
        $data['end'] = min($page + $config['per_page'], $config['total_rows']);
        $data['total_records'] = $config['total_rows'];

        // Set the page title
        $this->global['pageTitle'] = 'CodeInsect : Ticket';

        // Load the view
        $this->loadViews("amountconfirmation/list", $this->global, $data, NULL);
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
        $this->global['pageTitle'] = 'CodeInsect : Add New Amount confirmation';
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $this->loadViews("amountconfirmation/add", $this->global, $data, NULL);
    }
    /*}*/

    /**
     * This function is used to add new user to the system
     */
public function addNewAmountconfirmation()
{
    $this->load->library('form_validation');
    $this->form_validation->set_rules('amount', 'amount', 'trim|required|max_length[10024]');

    if ($this->form_validation->run() == FALSE) {
        $this->add();
    } else {
        $namePayee = $this->security->xss_clean($this->input->post('namePayee'));
        $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
        $amount = $this->security->xss_clean($this->input->post('amount'));
        $date_of_Payment = $this->security->xss_clean($this->input->post('date_of_Payment'));
        $amount_Paid_for = $this->security->xss_clean($this->input->post('amount_Paid_for'));
        $description = $this->security->xss_clean($this->input->post('description'));
        $mode_of_Payment = $this->security->xss_clean($this->input->post('mode_of_Payment'));
        $confirmationStatus = $this->security->xss_clean($this->input->post('confirmationStatus'));

        $s3_file_link = [];
        if (!empty($_FILES["file"]["tmp_name"])) {
            $dir = dirname($_FILES["file"]["tmp_name"]);
            $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];

            if (rename($_FILES["file"]["tmp_name"], $destination)) {
                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();
                $s3_file_link[] = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
            } else {
                $s3_file_link[] = '';
            }
        } else {
            $s3_file_link[] = '';
        }

        $s3files = implode(',', $s3_file_link);
        $amountconfirmationInfo = array(
            'namePayee' => $namePayee,
            'franchiseNumber' => $franchiseNumber,
            'amount' => $amount,
            'date_of_Payment' => $date_of_Payment,
            'amount_Paid_for' => $amount_Paid_for,
            'paysnapshotS3File' => $s3files,
            'mode_of_Payment' => $mode_of_Payment,
            'description' => $description,
            'confirmationStatus' => $confirmationStatus,
            'createdDtm' => date('Y-m-d H:i:s')
        );

        $result = $this->amnt->addNewAmountconfirmation($amountconfirmationInfo);

        if ($result > 0) {
            $this->load->model('Notification_model'); // ✅ Ensure model is loaded

            $notificationMessage = "<strong>Amount Confirmation:</strong> A new payment has been added.";

            // ✅ Notify Franchise User
            $franchiseUser = $this->bm->getUserByFranchiseNumber($franchiseNumber);
            if (!empty($franchiseUser)) {
                $this->Notification_model->add_amtconfId_notification($franchiseUser->userId, $notificationMessage, $result);
            }

            // ✅ Notify Admin and Team Lead Roles
            $adminUsers = $this->bm->getUsersByRoles([1, 14, 16]);
            if (!empty($adminUsers)) {
                foreach ($adminUsers as $adminUser) {
                    $this->Notification_model->add_amtconfId_notification($adminUser->userId, $notificationMessage, $result);
                }
            }

            $this->session->set_flashdata('success', 'Amount confirmation added successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to add amount confirmation.');
        }

        redirect('amountconfirmation/amountconfirmationListing');
    }
}


    /*}*/

    /**
     * This function is used load announcement edit information
     * @param number $announcementId : Optional : This is announcement id
     */
    function edit($amtconfId = NULL)
    {
        if ($amtconfId == null) {
            redirect('amountconfirmation/amountconfirmationListing');
        }

        $data['amountconfirmationInfo'] = $this->amnt->getAmountconfirmationInfo($amtconfId); // Add this line

        $this->global['pageTitle'] = 'CodeInsect : Edit Amount';
        $this->loadViews("amountconfirmation/edit", $this->global, $data, NULL); // Pass $data
    }



    /**
     * This function is used to edit the user information
     */
  public function editAmountconfirmation()
{
    $this->load->library('form_validation');
    $this->form_validation->set_rules('confirmationStatus', 'Confirmation Status', 'required');

    $amtconfId = $this->input->post('amtconfId');

    if ($this->form_validation->run() == FALSE) {
        $this->edit($amtconfId);
    } else {
        $confirmationStatus = $this->security->xss_clean($this->input->post('confirmationStatus'));

        $amountconfirmationInfo = array(
            'confirmationStatus' => $confirmationStatus,
            'updatedBy' => $this->session->userdata('userId'), // 👈 safer than $this->vendorId
            'updatedDtm' => date('Y-m-d H:i:s')
        );

        $result = $this->amnt->editAmountconfirmation($amountconfirmationInfo, $amtconfId);

        if ($result > 0) {
            $this->load->model('Notification_model');

            // Get franchiseNumber (you must fetch this from DB using amtconfId)
            $this->db->select('franchiseNumber');
            $this->db->from('tbl_amount_confirmation');
            $this->db->where('amtconfId', $amtconfId);
            $row = $this->db->get()->row();

            $notificationMessage = "<strong>Amount Confirmation:</strong> A payment confirmation was updated.";

            // ✅ Notify franchise user
            if (!empty($row)) {
                $franchiseUser = $this->bm->getUserByFranchiseNumber($row->franchiseNumber);
                if (!empty($franchiseUser)) {
                    $this->Notification_model->add_amtconfId_notification($franchiseUser->userId, $notificationMessage, $amtconfId);
                }
            }

            // ✅ Notify Admins and Team Leads
            $adminUsers = $this->bm->getUsersByRoles([1, 14, 16]);
            if (!empty($adminUsers)) {
                foreach ($adminUsers as $adminUser) {
                    $this->Notification_model->add_amtconfId_notification($adminUser->userId, $notificationMessage, $amtconfId);
                }
            }

            $this->session->set_flashdata('success', 'Amount confirmation updated successfully');
        } else {
            $this->session->set_flashdata('error', 'Amount confirmation update failed');
        }

        redirect('amountconfirmation/amountconfirmationListing');
    }
}

}
