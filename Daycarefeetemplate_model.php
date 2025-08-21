<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Daycarefeetemplate_model (Daycare Fee Template Model)
 * Model class to handle daycare fee template related data
 * @author : [Your Name]
 * @version : 1.0
 * @since : May 2025
 */
class Daycarefeetemplate_model extends CI_Model
{
    function daycareFeeTemplateListingCount($searchText = '', $role, $franchiseNumber)
    {
        $this->db->from('tbl_daycarefeeTemplate');
        if ($searchText) {
            $this->db->group_start();
            $this->db->like('franchiseNumber', $searchText);
            $this->db->or_like('brAddress', $searchText);
            $this->db->or_like('branchContacNum', $searchText);
            $this->db->group_end();
        }
        if ($franchiseNumber) {
            $this->db->where('franchiseNumber', $franchiseNumber);
        }
        $this->db->where('isDeleted', 0);
        return $this->db->count_all_results();
    }

    function daycareFeeTemplateListing($searchText = '', $limit, $offset, $role, $franchiseNumber)
    {
        $this->db->select('dcfeetempId, franchiseNumber, ageGroupEarlyYears, earlyYearsDays_operation, earlyYearsHourse, earlyYearsFeeMonthly, earlyYearsFeeHourly, ageGroupJuniors, juniorsDays_operation, juniorsHourse, juniorsFeeMonthly, juniorsFeeHourly, brAddress, branchContacNum, createdDtm');
        $this->db->from('tbl_daycarefeeTemplate');
        if ($searchText) {
            $this->db->group_start();
            $this->db->like('franchiseNumber', $searchText);
            $this->db->or_like('brAddress', $searchText);
            $this->db->or_like('branchContacNum', $searchText);
            $this->db->group_end();
        }
        if ($franchiseNumber) {
            $this->db->where('franchiseNumber', $franchiseNumber);
        }
        $this->db->where('isDeleted', 0);
        $this->db->limit($limit, $offset);
        $this->db->order_by('createdDtm', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    function addNewDaycareFeeTemplate($feeTemplateInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_daycarefeeTemplate', $feeTemplateInfo);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Database error during insert: ' . $this->db->last_query());
            return false;
        }

        return $insert_id;
    }

    public function getUserNameById($userId)
    {
        if (!$userId) {
            return 'None';
        }
        $this->db->select('name');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->name : 'Unknown';
    }

    function getDaycareFeeTemplateInfo($dcfeetempId)
    {
        $this->db->select('dcfeetempId, franchiseNumber, ageGroupEarlyYears, earlyYearsDays_operation, earlyYearsHourse, earlyYearsFeeMonthly, earlyYearsFeeHourly, ageGroupJuniors, juniorsDays_operation, juniorsHourse, juniorsFeeMonthly, juniorsFeeHourly, brAddress, description, branchContacNum, branchFranchiseAssigned, createdBy');
        $this->db->from('tbl_daycarefeeTemplate');
        $this->db->where('dcfeetempId', $dcfeetempId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->row();
    }

    function editDaycareFeeTemplate($feeTemplateInfo, $dcfeetempId)
    {
        $this->db->where('dcfeetempId', $dcfeetempId);
        $this->db->update('tbl_daycarefeeTemplate', $feeTemplateInfo);
        return TRUE;
    }


    public function daycareFeeTemplateListingCountByFranchises($searchText, $franchiseNumbers)
{
    $this->db->from('tbl_daycarefeeTemplate');
    if ($searchText) {
        $this->db->group_start();
        $this->db->like('franchiseNumber', $searchText);
        $this->db->or_like('brAddress', $searchText);
        $this->db->or_like('branchContacNum', $searchText);
        $this->db->group_end();
    }
    $this->db->where_in('franchiseNumber', $franchiseNumbers);
    $this->db->where('isDeleted', 0);
    return $this->db->count_all_results();
}

public function daycareFeeTemplateListingByFranchises($searchText, $limit, $offset, $franchiseNumbers)
{
    $this->db->select('dcfeetempId, franchiseNumber, ageGroupEarlyYears, earlyYearsDays_operation, earlyYearsHourse, earlyYearsFeeMonthly, earlyYearsFeeHourly, ageGroupJuniors, juniorsDays_operation, juniorsHourse, juniorsFeeMonthly, juniorsFeeHourly, brAddress, branchContacNum, createdDtm');
    $this->db->from('tbl_daycarefeeTemplate');
    if ($searchText) {
        $this->db->group_start();
        $this->db->like('franchiseNumber', $searchText);
        $this->db->or_like('brAddress', $searchText);
        $this->db->or_like('branchContacNum', $searchText);
        $this->db->group_end();
    }
    $this->db->where_in('franchiseNumber', $franchiseNumbers);
    $this->db->where('isDeleted', 0);
    $this->db->limit($limit, $offset);
    $this->db->order_by('createdDtm', 'DESC');
    $query = $this->db->get();
    return $query->result();
}

public function getAssignedFranchisesByUserId($userId)
{
    $this->db->select('franchiseNumber');
    $this->db->from('tbl_branches');
    $this->db->where('branchFranchiseAssigned', $userId);
    $this->db->where('isDeleted', 0);
    $query = $this->db->get();
    return $query->result_array();
}


    function deleteDaycareFeeTemplate($dcfeetempId)
    {
        $this->db->where('dcfeetempId', $dcfeetempId);
        $this->db->update('tbl_daycarefeeTemplate', [
            'isDeleted' => 1,
            'updatedBy' => $this->session->userdata('userId'),
            'updatedDtm' => date('Y-m-d H:i:s')
        ]);
        return $this->db->affected_rows();
    }

    function getFranchises()
    {
        $this->db->select('franchiseNumber, applicantName');
        $this->db->from('tbl_branches');
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->result();
    }

    function getUsersByFranchise($franchiseNumber)
    {
        $this->db->select('tbl_users.userId, tbl_users.name');
        $this->db->from('tbl_branches');
        $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
        $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber);
        $this->db->where('tbl_branches.isDeleted', 0);
        $query = $this->db->get();
        $result = $query->result();
        if (empty($result)) {
            log_message('debug', "No users found for franchiseNumber: $franchiseNumber");
        }
        return $result;
    }

    function getFranchiseData($franchiseNumber)
    {
        $this->db->select('branchLocation AS brAddress, adminContactNum AS branchContacNum');
        $this->db->from('tbl_branches');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->row();
    }
}