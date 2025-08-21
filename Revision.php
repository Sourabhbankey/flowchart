<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Revision extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Revision_history_model');

        // For testing/demo only — set dummy user session if not logged in
        if (!$this->session->userdata('userId')) {
            $this->session->set_userdata('userId', 1);
            $this->session->set_userdata('name', 'John Doe');
        }
    }

    // Test method to manually log a sample revision (you can remove in production)
    public function log_example()
    {
        $data = [
            'userId'      => $this->session->userdata('userId'),
            'name'        => $this->session->userdata('name'),
            'module_name' => 'Example Module',
            'action'      => 'Updated record ID 42 with new values',
            'changed_at'  => date('Y-m-d H:i:s')
        ];

        $this->Revision_history_model->log($data);

        echo "Revision logged successfully.";
    }

    // Show all revisions
    public function history()
    {
        $data['revisions'] = $this->Revision_history_model->get_all();
        $this->load->view('revision_history_view', $data);
    }
}
