<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Festiveimages_model (Festiveimages Model)
 * Festiveimages model class to handle festive images related data
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Festiveimages_model extends CI_Model
{
    function festiveimagesListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_festiveimages as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.festiveimagesTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0); // Uncommented for consistency
        $query = $this->db->get();
        return $query->num_rows();
    }

    function festiveimagesListing($searchText, $page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_festiveimages as BaseTbl');
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.festiveimagesTitle LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.festiveimagesId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        return $query->result();
    }

    function addNewFestiveimages($festiveimagesInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_festiveimages', $festiveimagesInfo);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();
        return $insert_id;
    }

    function getfestiveimagesInfo($festiveimagesId)
    {
        $this->db->select('*');
        $this->db->from('tbl_festiveimages');
        $this->db->where('festiveimagesId', $festiveimagesId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->row();
    }

    function editFestiveimages($festiveimagesInfo, $festiveimagesId)
    {
        $this->db->where('festiveimagesId', $festiveimagesId);
        $this->db->update('tbl_festiveimages', $festiveimagesInfo);
        return TRUE;
    }

    function getFranchiseData($franchiseNumber)
    {
        $this->db->select('branchAddress, mobile');
        $this->db->from('tbl_branches'); 
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Fetch branches for franchise number dropdown
     * @return array
     */
    function getBranchesFranchiseNumber()
    {
        $this->db->select('franchiseNumber,branchAddress AS brAddress, mobile AS branchContacNum');
        $this->db->from('tbl_branches'); 
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        return $query->result();
    }
}