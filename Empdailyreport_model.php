<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class : Empdailyreport_model (Employee Daily Report Model)
 * Employee Daily Report model class to handle daily report-related data
 * @author : Ashish
 * @version : 1.1
 * @since : 28 May 2024
 * @updated : 17 May 2025
 */
class Empdailyreport_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function dailyReportListingCount($searchText = '')
    {
        $roleId = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');

        $this->db->from('tbl_dailyreport as BaseTbl');
        if (!empty($searchText)) {
            $searchText = $this->db->escape_like_str($searchText);
            $this->db->where("(BaseTbl.dailyRepempName LIKE '%$searchText%' OR BaseTbl.dailyRepTitle LIKE '%$searchText%' OR BaseTbl.description LIKE '%$searchText%')");
        }

        if (!in_array($roleId, [1, 14])) {
            $this->db->where('BaseTbl.dailyempDeartment', $userId);
        }

        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();

        if (!$query) {
            log_message('error', 'dailyReportListingCount query failed: ' . $this->db->_error_message());
            return 0;
        }

        return $query->num_rows();
    }

    public function dailyReportListing($searchText = '', $page, $segment)
    {
        $roleId = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');

        $this->db->select('BaseTbl.dailyreportId, BaseTbl.dailyRepempName, BaseTbl.dailyRepTitle, BaseTbl.dailyempDeartment, BaseTbl.description, BaseTbl.createdBy, BaseTbl.createdDtm, Users.name as userName');
        $this->db->from('tbl_dailyreport as BaseTbl');
        $this->db->join('tbl_users as Users', 'Users.userId = BaseTbl.dailyempDeartment', 'left');

        if (!empty($searchText)) {
            $searchText = $this->db->escape_like_str($searchText);
            $this->db->where("(BaseTbl.dailyRepempName LIKE '%$searchText%' OR BaseTbl.dailyRepTitle LIKE '%$searchText%' OR BaseTbl.description LIKE '%$searchText%')");
        }

        if (!in_array($roleId, [1, 14])) {
            $this->db->where('BaseTbl.dailyempDeartment', $userId);
        }

        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.createdDtm', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();

        if (!$query) {
            log_message('error', 'dailyReportListing query failed: ' . $this->db->_error_message());
            return [];
        }

        return $query->result();
    }

    public function addNewDailyReport($reportInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_dailyreport', $reportInfo);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'addNewDailyReport failed: ' . $this->db->_error_message());
            return 0;
        }

        return $insert_id;
    }

    public function getDailyReportInfo($dailyreportId)
    {
        $this->db->select('dailyreportId, dailyRepempName, dailyRepTitle, dailyempDeartment, description, createdBy, createdDtm');
        $this->db->from('tbl_dailyreport');
        $this->db->where('dailyreportId', $dailyreportId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();

        if (!$query) {
            log_message('error', 'getDailyReportInfo query failed: ' . $this->db->_error_message());
            return null;
        }

        return $query->row();
    }

    public function editDailyReport($reportInfo, $dailyreportId)
    {
        $this->db->where('dailyreportId', $dailyreportId);
        $result = $this->db->update('tbl_dailyreport', $reportInfo);

        if (!$result) {
            log_message('error', 'editDailyReport failed: ' . $this->db->_error_message());
            return false;
        }

        return true;
    }

    public function deleteDailyReport($dailyreportId)
    {
        if (!$dailyreportId) {
            return false;
        }

        $data = [
            'isDeleted' => 1,
            'updatedBy' => $this->session->userdata('userId'),
            'updatedDtm' => date('Y-m-d H:i:s')
        ];

        $this->db->where('dailyreportId', $dailyreportId);
        $result = $this->db->update('tbl_dailyreport', $data);

        if (!$result) {
            log_message('error', 'deleteDailyReport failed: ' . $this->db->_error_message());
            return false;
        }

        return true;
    }

    public function getTotalRecords()
    {
        $roleId = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');

        $this->db->from('tbl_dailyreport');
        if (!in_array($roleId, [1, 14])) {
            $this->db->where('dailyempDeartment', $userId);
        }
        $this->db->where('isDeleted', 0);
        return $this->db->count_all_results();
    }

    public function getData($limit, $start)
    {
        $roleId = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');

        $this->db->select('dailyreportId, dailyRepempName, dailyRepTitle, dailyempDeartment, description, createdBy, createdDtm');
        $this->db->from('tbl_dailyreport');
        if (!in_array($roleId, [1, 14])) {
            $this->db->where('dailyempDeartment', $userId);
        }
        $this->db->where('isDeleted', 0);
        $this->db->order_by('createdDtm', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();

        if (!$query) {
            log_message('error', 'getData query failed: ' . $this->db->_error_message());
            return [];
        }

        return $query->result();
    }

    public function getAllRecords()
    {
        $roleId = $this->session->userdata('role');
        $userId = $this->session->userdata('userId');

        $this->db->select('dailyreportId, dailyRepempName, dailyRepTitle, dailyempDeartment, description, createdBy, createdDtm');
        $this->db->from('tbl_dailyreport');
        if (!in_array($roleId, [1, 14])) {
            $this->db->where('dailyempDeartment', $userId);
        }
        $this->db->where('isDeleted', 0);
        $this->db->order_by('createdDtm', 'DESC');
        $query = $this->db->get();

        if (!$query) {
            log_message('error', 'getAllRecords query failed: ' . $this->db->_error_message());
            return [];
        }

        return $query->result();
    }

    public function getUsers()
    {
        $this->db->select('userId, name');
        $this->db->from('tbl_users');
        $this->db->where_not_in('roleId', [1, 14]);
        $query = $this->db->get();

        if (!$query) {
            log_message('error', 'getUsers query failed: ' . $this->db->_error_message());
            return [];
        }

        return $query->result();
    }
}
?>