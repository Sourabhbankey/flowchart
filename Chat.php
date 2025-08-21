<?php
class Chat extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Chat_model');
        $this->load->helper('url');
        $this->load->library('session');
    }

    public function index($friendId = null) {
        if (!$this->session->userdata('userId')) {
            redirect('login');
        }

        $data['friendId'] = $friendId;
        $this->load->view('chat_view', $data);
    }

    public function fetch_messages() {
        $userId = $this->session->userdata('userId');
        $friendId = $this->input->post('friendId');
        $messages = $this->Chat_model->get_messages($userId, $friendId);
        echo json_encode($messages);
    }

    public function send_message() {
        $data = [
            'senderId' => $this->session->userdata('userId'),
            'receiverId' => $this->input->post('receiver_id'),
            'message' => $this->input->post('message')
        ];
        $this->Chat_model->send_message($data);
    }
}
?>
