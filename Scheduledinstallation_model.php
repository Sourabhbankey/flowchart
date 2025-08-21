<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Scheduledinstallation_model (Scheduledinstallation Model)
 * Scheduledinstallation model class to get to handle task related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 20 Jun 2024
 */
class Scheduledinstallation_model extends CI_Model
{
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function scheduledinstallationListingCount($searchText)
    {
        $this->db->select('BaseTbl.schinstaId, BaseTbl.schinsTitle, BaseTbl.instaCity, BaseTbl.schDateInstall,BaseTbl.dateofinstall, BaseTbl.schinsAddress, BaseTbl.installTL, BaseTbl.contactTL, BaseTbl.franchiseNumber, BaseTbl.franchiseNumber, BaseTbl.installTeam, BaseTbl.installTeamNum, BaseTbl.travelingFrom, BaseTbl.travelingTo, BaseTbl.dateOfDespatch, BaseTbl.modeOfTravel, BaseTbl.installStartedTime, BaseTbl.installFinishedTime, BaseTbl.dmMaterials, BaseTbl.dmMaterialsdesc, BaseTbl.missingMaterials, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_schInstallation as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.schinsTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.createdDtm', 'DESC');
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the task listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function scheduledinstallationListing($searchText, $page, $segment)
    {
        $this->db->select('BaseTbl.schinstaId, BaseTbl.schinsTitle, BaseTbl.instaCity, BaseTbl.schDateInstall , BaseTbl.dateofinstall, BaseTbl.schinsAddress, BaseTbl.installTL, BaseTbl.contactTL, BaseTbl.franchiseNumber, BaseTbl.installTeam, BaseTbl.installTeamNum, BaseTbl.travelingFrom, BaseTbl.travelingTo, BaseTbl.dateOfDespatch, BaseTbl.modeOfTravel, BaseTbl.installStartedTime, BaseTbl.installFinishedTime, BaseTbl.dmMaterials, BaseTbl.dmMaterialsdesc, BaseTbl.missingMaterials, BaseTbl.description, BaseTbl.createdDtm');
        $this->db->from('tbl_schInstallation as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.schinsTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.schinstaId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new task to system
     * @return number $insert_id : This is last inserted id
     */
    // function addNewScheduledinstallation($scheduledinstallationInfo)
    // {
    //     $this->db->trans_start();
    //     $this->db->insert('tbl_schInstallation', $scheduledinstallationInfo);
        
    //     $insert_id = $this->db->insert_id();
        
    //     $this->db->trans_complete();
        
    //     return $insert_id;
    // }
    
function addNewScheduledinstallation($scheduledinstallationInfo)
{
    $this->db->trans_start();

    // Insert into tbl_schInstallation
    $this->db->insert('tbl_schInstallation', $scheduledinstallationInfo);
    $insert_id = $this->db->insert_id(); // schinstaId

    // Prepare data for tbl_installation_expenses
    $expenseData = array(
        'schinstaId'      => $insert_id,
        'franchiseNumber' => $scheduledinstallationInfo['franchiseNumber'] ?? '',
        'schinsTitle'     => $scheduledinstallationInfo['schinsTitle'] ?? '',
        'instaCity'       => $scheduledinstallationInfo['instaCity'] ?? '',
        'schinsAddress'   => $scheduledinstallationInfo['schinsAddress'] ?? '',
        'installTL'       => $scheduledinstallationInfo['installTL'] ?? '',
        'contactTL'       => $scheduledinstallationInfo['contactTL'] ?? '',
        'createdDtm'      => date('Y-m-d H:i:s')
    );

    // Insert into tbl_installation_expenses
    $this->db->insert('tbl_installation_expenses', $expenseData);

    $this->db->trans_complete();

    return $insert_id;
}


    /**
     * This function used to get task information by id
     * @param number $schinstaId : This is training id
     * @return array $result : This is training information
     */
    function getScheduledinstallationInfo($schinstaId)
    {
        $this->db->select('schinstaId, schinsTitle, instaCity, schDateInstall, schinsAddress, installTL, dateofinstall, contactTL, installTeam, installTeamNum, travelingFrom, travelingTo, dateOfDespatch, modeOfTravel, installStartedTime, installFinishedTime, dmMaterials, dmMaterialsdesc, missingMaterials, franchiseNumber, description');
        $this->db->from('tbl_schInstallation');
        $this->db->where('schinstaId', $schinstaId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    
    /**
     * This function is used to update the task information
     * @param array $taskInfo : This is task updated information
     * @param number $taskId : This is task id
     */
    function editScheduledinstallation($scheduledinstallationInfo, $schinstaId)
    {
        $this->db->where('schinstaId', $schinstaId);
        $this->db->update('tbl_schInstallation', $scheduledinstallationInfo);
        
        return TRUE;
    }
    /**
     * This function is used to get the user  information
     * @return array $result : This is result of the query
     */
    function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 25);
        $this->db->order_by('createdDtm', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }
	//code done by yashi 
	 public function getFranchiseNumberByUserId($userId) {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }

  
   public function get_count($franchiseFilter = null) {
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_schInstallation');
	}

	public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    return $this->db->count_all_results('tbl_schInstallation');
	}

public function get_data($limit, $start, $franchiseFilter = null) {
    $this->db->limit($limit, $start);
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
    $this->db->order_by('createdDtm', 'DESC');
    $query = $this->db->get('tbl_schInstallation');
    return $query->result();
}

	public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null) {
    // Start building the query
    $this->db->from('tbl_schInstallation');
    
    // Ensure that the franchiseNumber matches
    $this->db->where("FIND_IN_SET('$franchiseNumber', franchiseNumber) >", 0);
$this->db->order_by('createdDtm', 'DESC');
    // If franchiseFilter is provided, add it as an additional condition
    if ($franchiseFilter) {
        $this->db->group_start();
        $this->db->where("FIND_IN_SET('$franchiseFilter', franchiseNumber) >", 0);
        $this->db->group_end();
    }

    // Apply limit and start for pagination
    $this->db->limit($limit, $start);

    // Execute the query
    $query = $this->db->get();
    return $query->result();
}
 public function getBranchByFranchiseNumber($franchiseNumber) {
        $this->db->select('franchiseName as branchSetupName, branchAddress as brcompAddress, branchcityName as city, branchState as state');
        $this->db->from('tbl_branches');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null;
        }
    }

}