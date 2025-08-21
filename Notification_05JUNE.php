<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Task (TaskController)
 * Task Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Notification  extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notification_model', 'nm');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->load->library('pagination');
        $this->module = 'Notification';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */

    public function index()
    {
        redirect('Notification/fetch_notifications');
    }




    public function fetch_notifications()
    {
        $userId = $this->session->userdata('userId');

        $this->db->select('id, userId, message, taskId, supportMeetingId ,branchesId,brimgvideoId,amtconfId,coupnsId,departleaveId,credId, assetsId,proddefectiveId, approvalId, announcementId,blogId,pdcId,customDesignId,despatchId, acattachmentId, lgattachmentId,leaveId , qcId , faqId, brsetupid, admid, stockId, freegiftId, socialId, staffId, onboardfrmId , amcId, locationApprovalId , adminMeetingId, trainingId,ticketId');
        $this->db->where('userId', $userId);
        $this->db->where('is_read', 0);
        $this->db->order_by('created_at', 'DESC');

        $query = $this->db->get('tbl_notifications');
        echo json_encode($query->result_array());
    }


    /* public function mark_all_read()
{
    
    $this->db->where('userId', $userId);
    $this->db->update('tbl_notifications', ['is_read' => 1]); // Mark all as read
} */
    public function mark_as_read()
    {
        $id = $this->input->post('id');

        if (!$id) {
            log_message('error', "❌ ERROR: Invalid Notification ID");
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
            return;
        }

        // Update notification where ID matches
        $this->db->where('id', $id);
        $this->db->update('tbl_notifications', ['is_read' => 1]);

        if ($this->db->affected_rows() > 0) {
            log_message('error', "✅ SUCCESS: Notification Marked as Read: ID - $id");
            echo json_encode(['status' => 'success']);
        } else {
            log_message('error', "❌ ERROR: Update Failed for ID - $id | Query: " . $this->db->last_query());
            echo json_encode(['status' => 'error', 'message' => 'Update Failed']);
        }
    }





    public function generate_notifications()
    {
        $users = $this->nm->get_users_for_notifications();

        if (!empty($users)) {
            foreach ($users as $user) {
                $message = "Hello, you have an open task!";
                $this->nm->add_notification($user['userId'], $message);
            }

            echo "Notifications generated successfully.";
        } else {
            echo "No users with open tasks.";
        }
    }
    public function all_notifications(){
        $userId = $this->session->userdata('userId');
        $this->db->select('id, userId, message, taskId, clientId, supportMeetingId, qcId, coupnsId, credId,departleaveId, branchesId,brimgvideoId,amtconfId,assetsId, approvalId,proddefectiveId, dmfranchsehoId , announcementId, blogId, pdcId, customDesignId, despatchId, acattachmentId, lgattachmentId, leaveId, faqId, brsetupid, admid, stockId, freegiftId, socialId, staffId, onboardfrmId, amcId, locationApprovalId, adminMeetingId, trainingId, is_read, created_at,ticketId');
        $this->db->where('userId', $userId);
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('tbl_notifications');
        $data['notifications'] = $query->result();
        $this->global['pageTitle'] = 'All Notifications';
        $this->loadViews("notifications/list", $this->global, $data, NULL);
    }
}
