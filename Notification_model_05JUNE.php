<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Task_model (Task Model)
 * Task model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 16 May 2023
 */
class Notification_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    public function get_notifications($user_id) {
        $this->db->where('userId', $user_id); 
        $this->db->where('is_read', 0);
        $this->db->order_by('created_at', 'DESC');

        $query = $this->db->get('tbl_notifications');

        if ($query->num_rows() == 0) {
            return []; // No notifications found
        }

        return $query->result_array();
    }

    public function mark_as_read() {
        $id = $this->input->post('id'); // Notification ID

        if ($id) {
            $this->db->where('id', $id); // Update by notification ID
            $this->db->update('tbl_notifications', ['is_read' => 1]);

            if ($this->db->affected_rows() > 0) {
                log_message('error', " SUCCESS: Notification Marked as Read: ID - $id");
                echo json_encode(['status' => 'success']);
            } else {
                log_message('error', " ERROR: Update Failed for ID - $id | Query: " . $this->db->last_query());
                echo json_encode(['status' => 'error', 'message' => 'Update Failed']);
            }
        } else {
            log_message('error', " ERROR: Invalid Notification ID");
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        }
    }

    public function get_users_for_notifications() {
        $this->db->select('DISTINCT t.assignedBy AS userId', false);
        $this->db->from('tbl_task t');
        $this->db->join('tbl_users u', 't.assignedBy = u.userId', 'inner'); // Match assigned_by with userId
        $this->db->where('t.status', 'open');  // Only select users with open tasks
        return $this->db->get()->result_array();
    }

    public function add_notification($taskId, $message, $userId) {
        if (empty($taskId)) {
            log_message('error', "ERROR: taskId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'taskId' => $taskId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function get_admin_users() {
        $this->db->select('userId');
        $this->db->from('tbl_users');
        $this->db->where('roleId', '14'); // Assuming 'role' column exists
        return $this->db->get()->result_array();
    }

    public function add_support_notification($supportMeetingId, $message, $userId) {
        if (empty($supportMeetingId)) {
            log_message('error', "ERROR: supportMeetingId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'supportMeetingId' => $supportMeetingId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function fetch_notifications($userId) {
        $this->db->select('*');
        $this->db->from('tbl_notifications');
        $this->db->where('userId', $userId);
        $this->db->where('is_read', 0);
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get();
        
        // Debugging - Check if data is being fetched
        log_message('error', 'Fetch Notifications Data: ' . json_encode($query->result()));
        
        return $query->result();
    }

    public function add_branch_notification($branchesId, $message, $userId) {
        if (empty($branchesId)) {
            log_message('error', "ERROR: branchesId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'branchesId' => $branchesId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function get_assigned_user_by_branch($branchId) {
        $this->db->select('branchFranchiseAssigned'); // Get the assigned user ID
        $this->db->from('tbl_branches'); 
        $this->db->where('branchesId', $branchId);
        
        $query = $this->db->get();
        return $query->row(); // Return a single row instead of an array
    }

    public function add_announcement_notification($announcementId, $message, $userId) {
        if (empty($announcementId)) {
            log_message('error', "ERROR: announcementId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'announcementId' => $announcementId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function get_all_users() {
        return $this->db->get('tbl_users')->result_array();
    }

    public function add_blog_notification($blogId, $message, $userId) {
        if (empty($blogId)) {
            log_message('error', "ERROR: blogId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'blogId' => $blogId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_pdc_notification($pdcId, $message, $userId) {
        if (empty($pdcId)) {
            log_message('error', "ERROR: pdcId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'pdcId' => $pdcId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_custom_design_notification($customDesignId, $message, $userId) {
        if (empty($customDesignId)) {
            log_message('error', "ERROR: customDesignId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'customDesignId' => $customDesignId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_despatch_notification($despatchId, $message, $userId) {
        if (empty($despatchId)) {
            log_message('error', "ERROR: despatchId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'despatchId' => $despatchId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_acattachment_notification($acattachmentId, $message, $userId) {
        if (empty($acattachmentId)) {
            log_message('error', "ERROR: acattachmentId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'acattachmentId' => $acattachmentId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_legal_notification($lgattachmentId, $message, $userId) {
        if (empty($lgattachmentId)) {
            log_message('error', "ERROR: lgattachmentId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'lgattachmentId' => $lgattachmentId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_leave_notification($leaveId, $message, $userId) {
        if (empty($leaveId)) {
            log_message('error', "ERROR: leaveId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'leaveId' => $leaveId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_attachment_notification($attachmentId, $message, $userId) {
        if (empty($attachmentId)) {
            log_message('error', "ERROR: attachmentId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'attachmentId' => $attachmentId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_Dmfranchse_notification($dmfranchseId, $message, $userId) {
        if (empty($dmfranchseId)) {
            log_message('error', "ERROR: dmfranchseId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'dmfranchseId' => $dmfranchseId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_Dmfranchseho_notification($dmfranchsehoId, $message, $userId) {
        if (empty($dmfranchsehoId)) {
            log_message('error', "ERROR: dmfranchsehoId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'dmfranchsehoId' => $dmfranchsehoId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_amtconfId_notification($amtconfId, $message, $userId) {
        if (empty($amtconfId)) {
            log_message('error', "ERROR: amtconfId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'amtconfId' => $amtconfId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_approvalId_notification($approvalId, $message, $userId) {
        if (empty($approvalId)) {
            log_message('error', "ERROR: approvalId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'approvalId' => $approvalId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_assetsId_notification($assetsId, $message, $userId) {
        if (empty($assetsId)) {
            log_message('error', "ERROR: assetsId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'assetsId' => $assetsId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_brimgvideoId_notification($brimgvideoId, $message, $userId) {
        if (empty($brimgvideoId)) {
            log_message('error', "ERROR: brimgvideoId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'brimgvideoId' => $brimgvideoId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_clientId_notification($clientId, $message, $userId) {
        if (empty($clientId)) {
            log_message('error', "ERROR: clientId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'clientId' => $clientId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_coupnsId_notification($coupnsId, $message, $userId) {
        if (empty($coupnsId)) {
            log_message('error', "ERROR: coupnsId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'coupnsId' => $coupnsId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_credId_notification($credId, $message, $userId) {
        if (empty($credId)) {
            log_message('error', "ERROR: credId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'credId' => $credId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_proddefectiveId_notification($proddefectiveId, $message, $userId) {
        if (empty($proddefectiveId)) {
            log_message('error', "ERROR: proddefectiveId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'proddefectiveId' => $proddefectiveId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_departleaveId_notification($departleaveId, $message, $userId) {
        if (empty($departleaveId)) {
            log_message('error', "ERROR: departleaveId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'departleaveId' => $departleaveId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function getUsersByRole($roleId) {
        $this->db->select('userId');
        $this->db->from('tbl_users');
        $this->db->where('roleId', $roleId);
        $query = $this->db->get();
        
        return $query->result_array();
    }

    public function add_faq_notification($faqId, $message, $userId) {
        if (empty($faqId)) {
            log_message('error', "ERROR: faqId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'faqId' => $faqId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_qc_notification($qcId, $message, $userId) {
        if (empty($qcId)) {
            log_message('error', "ERROR: qcId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'qcId' => $qcId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_branchinstall_notification($brsetupid, $message, $userId) {
        if (empty($brsetupid)) {
            log_message('error', "ERROR: brsetupid is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'brsetupid' => $brsetupid,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_admdet_notification($admid, $message, $userId) {
        if (empty($admid)) {
            log_message('error', "ERROR: admid is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'admid' => $admid,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_stock_notification($stockId, $message, $userId) {
        if (empty($stockId)) {
            log_message('error', "ERROR: stockId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'stockId' => $stockId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_freegift_notification($freegiftId, $message, $userId) {
        if (empty($freegiftId)) {
            log_message('error', "ERROR: freegiftId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'freegiftId' => $freegiftId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_socialmedia_notification($socialId, $message, $userId) {
        if (empty($socialId)) {
            log_message('error', "ERROR: socialId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'socialId' => $socialId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_staff_notification($staffId, $message, $userId) {
        if (empty($staffId)) {
            log_message('error', "ERROR: staffId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'staffId' => $staffId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_onboard_notification($onboardfrmId, $message, $userId) {
        if (empty($onboardfrmId)) {
            log_message('error', "ERROR: onboardfrmId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'onboardfrmId' => $onboardfrmId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_amc_notification($amcId, $message, $userId) {
        if (empty($amcId)) {
            log_message('error', "ERROR: amcId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'amcId' => $amcId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_locationapproval_notification($locationApprovalId, $message, $userId) {
        if (empty($locationApprovalId)) {
            log_message('error', "ERROR: locationApprovalId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'locationApprovalId' => $locationApprovalId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_admintrain_notification($adminMeetingId, $message, $userId) {
        if (empty($adminMeetingId)) {
            log_message('error', "ERROR: adminMeetingId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'adminMeetingId' => $adminMeetingId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_training_notification($trainingId, $message, $userId) {
        if (empty($trainingId)) {
            log_message('error', "ERROR: trainingId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'trainingId' => $trainingId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function add_admdetnew_notification($admid, $message, $userId) {
        if (empty($admid)) {
            log_message('error', "ERROR: admid is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'admid' => $admid,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function edit_social_notification($socialId, $message, $userId) {
        if (empty($socialId)) {
            log_message('error', "ERROR: socialId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'socialId' => $socialId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function getUsersByFranchiseNumber($franchiseNumbers) {
        $this->db->where_in('franchiseNumber', explode(',', $franchiseNumbers));
        $query = $this->db->get('tbl_users');
        return $query->result();
    }

    public function add_ticket_notification($ticketId, $message, $userId) {
        if (empty($ticketId)) {
            log_message('error', "ERROR: ticketId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'ticketId' => $ticketId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

    public function getUsersByRoleId($roleId) {
        $this->db->select('userId');
        $this->db->from('tbl_users');
        $this->db->where('roleId', $roleId);
        $query = $this->db->get();
        return $query->result();
    }
}