<?php
class Chat_model extends CI_Model {

    public function get_messages($userId, $friendId) {
        $this->db->where("(
            (senderId = ? AND receiverId = ?) OR 
            (senderId = ? AND receiverId = ?)
        )", [$userId, $friendId, $friendId, $userId]);
        
        $this->db->order_by('created_at', 'ASC');
        return $this->db->get('tbl_chats')->result();
    }

    public function send_message($data) {
        return $this->db->insert('tbl_chats', $data);
    }
}
?>

