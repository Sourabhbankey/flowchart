<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Notification_model (Notification Model)
 * Notification model class to get to handle task related data 
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

    // Insert a new notification
   public function add_notification($userId, $message, $taskId = null) {
    $data = [
        'userId' => $userId,
        'message' => $message,
        'is_read' => 0,
        'taskId' => $taskId, // Include taskId if available
        'created_at' => date('Y-m-d H:i:s')
    ];
    return $this->db->insert('tbl_notifications', $data);
}

  public function get_admin_users() {
    $this->db->select('userId');
    $this->db->from('tbl_users');
    $this->db->where('roleId', '14'); // Assuming 'role' column exists
    return $this->db->get()->result_array();
}
/*public function add_support_notification($userId, $message, $supportMeetingId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('supportMeetingId', $supportMeetingId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'supportMeetingId' => $supportMeetingId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}*/

public function add_support_notification($userId, $message, $supportMeetingId)
{
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
public function add_branch_notification($userId, $message, $branchesId)
{
    if (!empty($branchesId)) { // Ensure branchesId is valid
        $this->db->where('userId', $userId);
        $this->db->where('branchesId', $branchesId);
        $query = $this->db->get('tbl_notifications');

        if ($query->num_rows() == 0) { 
            $data = [
                'userId' => $userId,
                'message' => $message,
                'branchesId' => $branchesId,
                'is_read' => 0, // Unread notification
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('tbl_notifications', $data);
        }
    } else {
        log_message('error', " ERROR: branchesId is missing for notification");
    }
}

public function get_assigned_user_by_branch($branchId) 
{
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
        'userId' => $userId,  // ✅ Insert correct userId
        'message' => $message,
        'announcementId' => $announcementId, // ✅ Insert correct announcementId
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('tbl_notifications', $data);
}



public function get_all_users() {
    return $this->db->get('tbl_users')->result_array(); // ✅ Fetch all users
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
public function add_hrevent_notification($hreventId, $message, $userId) {
    if (empty($hreventId)) {
        log_message('error', "ERROR: hreventId is missing for notification");
        return;
    }

    $data = [
        'userId' => $userId,  
        'message' => $message,
        'hreventId' => $hreventId, 
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('tbl_notifications', $data);
}


/*public function add_pdc_notification($userId, $message, $pdcId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('pdcId', $pdcId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'pdcId' => $pdcId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}*/
public function add_pdc_notification($userId, $message, $pdcId)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'pdcId' => $pdcId,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
} 
public function add_custom_design_notification($customDesignId, $message, $userId)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'customDesignId' => $customDesignId,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
}

/*public function add_despatch_notification($userId, $message, $despatchId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('despatchId', $despatchId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'despatchId' => $despatchId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}*/
public function add_despatch_notification($despatchId, $message, $userId)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'despatchId' => $despatchId,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
}

/*public function add_acattachment_notification($userId, $message, $acattachmentId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('acattachmentId', $acattachmentId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'acattachmentId' => $acattachmentId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}*/
public function add_acattachment_notification($userId, $message, $acattachmentId)
{
    $data = [
        'userId'         => $userId,
        'message'        => $message,
        'acattachmentId' => $acattachmentId,
       // 'type'           => 'acattachment',  // Add this if not already in your table
        'is_read'        => 0,
        'created_at'     => date('Y-m-d H:i:s')
    ];

    $this->db->insert('tbl_notifications', $data);
}
public function add_legal_notification($userId, $message, $lgattachmentId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('lgattachmentId', $lgattachmentId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'lgattachmentId' => $lgattachmentId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}


 /* public function add_amtconfId_notification($amtconfId, $message, $userId) {
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
    }*/
public function add_amtconfId_notification($userId, $message, $amtconfId)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'amtconfId' => $amtconfId,
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


     public function add_client_notification($clientId, $message, $userId) {
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



         public function add_followup_notification($followupId, $message, $userId) {
        if (empty($followupId)) {
            log_message('error', "ERROR: clientId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'followupId' => $followupId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }


        public function add_assetassignment_notification($assetsId, $message, $userId) {
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



    /*  public function add_dmfranchse_notification($dmfranchseId, $message, $userId) {
        if (empty($dmfranchseId)) {
            log_message('error', "ERROR: clientId is missing for notification");
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
    }*/
public function add_dmfranchse_notification($userId, $message, $dmfranchseId)
{
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
            log_message('error', "ERROR: clientId is missing for notification");
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



     public function add_approval_notification($approvalId, $message, $userId) {
        if (empty($approvalId)) {
            log_message('error', "ERROR: clientId is missing for notification");
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

      public function add_empofmonths_notification($empofmonthsId, $message, $userId) {
        if (empty($empofmonthsId)) {
            log_message('error', "ERROR: clientId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'empofmonthsId' => $empofmonthsId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }


     public function add_Branchinstallationimg_notification($brimgvideoId, $message, $userId) {
        if (empty($brimgvideoId)) {
            log_message('error', "ERROR: clientId is missing for notification");
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


    public function add_dailyreport_notification($dailyreportId, $message, $userId) {
        if (empty($dailyreportId)) {
            log_message('error', "ERROR: clientId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'dailyreportId' => $dailyreportId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }

  
    public function add_coupns_notification($coupnsId, $message, $userId) {
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


   /*  public function add_Credentials_notification($credId, $message, $userId) {
        if (empty($credId)) {
            log_message('error', "ERROR: coupnsId is missing for notification");
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
    }*/
    public function add_Credentials_notification($userId, $message, $credId)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'credId' => $credId,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
} 

      public function add_proddefective_notification($proddefectiveId, $message, $userId) {
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


    

public function add_festiveimages_notification($festiveimagesId, $message, $userId)
    {
        if (empty($festiveimagesId)) {
            log_message('error', "ERROR: festiveimagesId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'festiveimagesId' => $festiveimagesId,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }



  public function add_trans_notification($transId, $message, $userId)
    {
        if (empty($transId)) {
            log_message('error', "ERROR: transId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'transId' => $transId, // Corrected field name and value
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }


      public function add_trainingassessment_notification($assmentId, $message, $userId)
    {
        if (empty($assmentId)) {
            log_message('error', "ERROR: assmentId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'assmentId' => $assmentId, 
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }


  public function add_standardimages_notification($standardimagesId, $message, $userId)
    {
        if (empty($standardimagesId)) {
            log_message('error', "ERROR: assmentId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'standardimagesId' => $standardimagesId, 
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }


     public function add_internaldesign_notification($internaldesignid, $message, $userId)
    {
        if (empty($internaldesignid)) {
            log_message('error', "ERROR: assmentId is missing for notification");
            return;
        }

        $data = [
            'userId' => $userId,
            'message' => $message,
            'internaldesignid' => $internaldesignid, 
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('tbl_notifications', $data);
    }


    

public function add_leave_notification($userId, $message, $leaveId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('leaveId', $leaveId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'leaveId' => $leaveId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}
public function getUsersByRole($roleId)
{
    $this->db->select('userId');
    $this->db->from('tbl_users');
    $this->db->where('roleId', $roleId);
    $query = $this->db->get();
    
    return $query->result_array(); // ✅ Ensures an associative array instead of objects
}

public function add_faq_notification($faqId, $message, $userId) {
    if (empty($faqId)) {
        log_message('error', "ERROR: faqId is missing for notification");
        return;
    }

    $data = [
        'userId' => $userId,  // ✅ Insert correct userId
        'message' => $message,
        'faqId' => $faqId, // ✅ Insert correct announcementId
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('tbl_notifications', $data);
}

/*public function add_branchinstall_notification($userId, $message, $brsetupid)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('brsetupid', $brsetupid);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'brsetupid' => $brsetupid,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}*/
public function add_branchinstall_notification($userId, $message, $brsetupid)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'brsetupid' => $brsetupid,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
}

public function add_admdet_notification($userId, $message, $admid)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('admid', $admid);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'admid' => $admid,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}
public function add_stock_notification($stockId, $message, $userId) {
    if (empty($stockId)) {
        log_message('error', "ERROR: stockId is missing for notification");
        return;
    }

    $data = [
        'userId' => $userId,  // ✅ Insert correct userId
        'message' => $message,
        'stockId' => $stockId, // ✅ Insert correct announcementId
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('tbl_notifications', $data);
}

public function add_dcfeetemp_notification($dcfeetempId, $message, $userId) {
    if (empty($dcfeetempId)) {
        log_message('error', "ERROR: stockId is missing for notification");
        return;
    }

    $data = [
        'userId' => $userId,  // ✅ Insert correct userId
        'message' => $message,
        'dcfeetempId' => $dcfeetempId, // ✅ Insert correct announcementId
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('tbl_notifications', $data);
}


public function add_classfee_notification($classfeeId, $message, $userId) {
    if (empty($classfeeId)) {
        log_message('error', "ERROR: stockId is missing for notification");
        return;
    }

    $data = [
        'userId' => $userId, 
        'message' => $message,
        'classfeeId' => $classfeeId, 
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('tbl_notifications', $data);
}


public function add_studentkit_notification($studentkitId, $message, $userId) {
    if (empty($studentkitId)) {
        log_message('error', "ERROR: studentkitId is missing for notification");
        return;
    }

    $data = [
        'userId' => $userId, 
        'message' => $message,
        'studentkitId' => $studentkitId, 
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
        'userId' => $userId,  // ✅ Insert correct userId
        'message' => $message,
        'freegiftId' => $freegiftId, // ✅ Insert correct announcementId
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('tbl_notifications', $data);
}

public function add_standardformat_notification($standId, $message, $userId) {
    if (empty($standId)) {
        log_message('error', "ERROR: standId is missing for notification");
        return;
    }

    $data = [
        'userId' => $userId,  // ✅ Insert correct userId
        'message' => $message,
        'standId' => $standId, // ✅ Insert correct announcementId
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('tbl_notifications', $data);
}



public function add_socialmedia_notification($userId, $message, $socialId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('socialId', $socialId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'socialId' => $socialId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}
/*public function add_staff_notification($userId, $message, $staffId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('staffId', $staffId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'staffId' => $staffId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}*/
public function add_staff_notification($userId, $message, $staffId)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'staffId' => $staffId,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
}

public function add_onboard_notification($userId, $message, $onboardfrmId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('onboardfrmId', $onboardfrmId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'onboardfrmId' => $onboardfrmId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}


public function add_amc_notification($userId, $message, $amcId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('amcId', $amcId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'amcId' => $amcId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}
public function add_locationapproval_notification($userId, $message, $locationApprovalId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('locationApprovalId', $locationApprovalId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'locationApprovalId' => $locationApprovalId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}
/*public function add_admintrain_notification($userId, $message, $adminMeetingId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('adminMeetingId', $adminMeetingId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'adminMeetingId' => $adminMeetingId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}*/

public function add_admintrain_notification($userId, $message, $adminMeetingId)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'adminMeetingId' => $adminMeetingId,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
} 
public function add_qc_notification($userId, $message, $qcId)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'qcId' => $qcId,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
} 

public function add_empdailyreport_notification($userId, $message, $referenceId)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'referenceId' => $referenceId,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
}

public function add_training_notification($userId, $message, $trainingId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('trainingId', $trainingId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'trainingId' => $trainingId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s') 
        ]; 
        $this->db->insert('tbl_notifications', $data);
    }
}
/*public function add_admdetnew_notification($userId, $message, $admid)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('admid', $admid);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'admid' => $admid,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tbl_notifications', $data);
    }
}*/
public function add_admdetnew_notification($userId, $message, $admid)
{
    $data = [
        'userId' => $userId,
        'message' => $message,
        'admid' => $admid,
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->insert('tbl_notifications', $data);
}

public function edit_social_notification($userId, $message, $socialId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('socialId', $socialId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'socialId' => $socialId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s') 
        ]; 
        $this->db->insert('tbl_notifications', $data);
    }
}
public function getUsersByFranchiseNumber($franchiseNumbers)
{
    $this->db->where_in('franchiseNumber', explode(',', $franchiseNumbers));
    $query = $this->db->get('tbl_users');
    return $query->result();
}
public function add_ticket_notification($userId, $message, $ticketId)
{
    
    $this->db->where('userId', $userId);
    $this->db->where('ticketId', $ticketId);
    $query = $this->db->get('tbl_notifications');

    if ($query->num_rows() == 0) { // ✅ Insert only if no existing notification
        $data = [
            'userId' => $userId,
            'message' => $message,
            'ticketId' => $ticketId,
            'is_read' => 0, // Unread notification
            'created_at' => date('Y-m-d H:i:s') 
        ]; 
        $this->db->insert('tbl_notifications', $data);
    }
}
public function getUsersByRoleId($roleId) {
    $this->db->select('userId');
    $this->db->from('tbl_users');
    $this->db->where('roleId', $roleId);
    $query = $this->db->get();
    return $query->result();
}
/**
 * Add a student fee notification
 */
    public function add_studentsfee_notification($userId, $message, $feeId)
    {
        $data = [
            'userId'     => $userId,
            'message'     => $message,
            'feeId' => $feeId,
            'created_at'  => date('Y-m-d H:i:s')
            //'status'      => 'unread' // optional
        ];

        $this->db->insert('tbl_notifications', $data);
        return $this->db->insert_id();
    }

}