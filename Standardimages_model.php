<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Standardimages_model (Standardimages Model)
 * Standardimages model class to handle festive images related data
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Standardimages_model extends CI_Model
{
    function StandardimagesListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_standardimages as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.StandardimagesTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0); // Uncommented for consistency
        $query = $this->db->get();
        return $query->num_rows();
    }

    function StandardimagesListing($searchText, $page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_standardimages as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.StandardimagesTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.StandardimagesId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        return $query->result();
    }

    function addNewStandardimages($StandardimagesInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_standardimages', $StandardimagesInfo);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();
        return $insert_id;
    }

    function getStandardimagesInfo($StandardimagesId)
    {
        $this->db->select('*');
        $this->db->from('tbl_standardimages');
        $this->db->where('StandardimagesId', $StandardimagesId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->row();
    }

    function editStandardimages($StandardimagesInfo, $StandardimagesId)
    {
        $this->db->where('StandardimagesId', $StandardimagesId);
        $this->db->update('tbl_standardimages', $StandardimagesInfo);
        return TRUE;
    }
}