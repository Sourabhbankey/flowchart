<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Revision_history_model extends CI_Model
{
    public function log($data)
    {
        return $this->db->insert('tbl_revision_history', $data);
    }

    public function get_all()
    {
        $query = $this->db->order_by('changed_at', 'DESC')->get('tbl_revision_history');
        return $query->result();
    }
}
