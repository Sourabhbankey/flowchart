<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Admissiondetails (AdmissiondetailsController)
 * Admissiondetails Class to control Admissiondetails related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 14 June 2024
 */
class Admissiondetailsnew25 extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admissiondetailsnew25_model', 'admdetnew25');
        $this->load->model('Branches_model', 'bm');
        $this->load->model('Staff_model', 'stf');
        $this->isLoggedIn();
		   $this->load->library('pagination');
        $this->module = 'Admissiondetails';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('admissiondetailsnew25/admissiondetailsListing25');
    }
    
    /**
     * This function is used to load the Admissiondetails list
     */
 

public function admissiondetailsListing25() {
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');

    // Get the franchise filter from GET request
    $franchiseFilter = $this->input->get('franchiseNumber');
    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = '';
    }

    // Pagination configuration
    $config = array();
    $config['base_url'] = base_url('admissiondetailsnew25/admissiondetailsListing25');
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;
    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

    // Fetch records based on role and franchise filter
    if (in_array($userRole, ['14', '1', '20', '28','19','26'])) {
        if ($franchiseFilter) {
            $config['total_rows'] = $this->admdetnew25->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
            $data['records'] = $this->admdetnew25->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
        } else {
            $config['total_rows'] = $this->admdetnew25->getTotalTrainingRecordsCount();
            $data['records'] = $this->admdetnew25->getAllTrainingRecords($config['per_page'], $page);
        }
    } else if (in_array($userRole, ['15', '13'])) {
        if ($franchiseFilter) {
            $config['total_rows'] = $this->admdetnew25->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
            $data['records'] = $this->admdetnew25->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
        } else {
            $config['total_rows'] = $this->admdetnew25->getTotalTrainingRecordsCountByRole($userId);
            $data['records'] = $this->admdetnew25->getTrainingRecordsByRole($userId, $config['per_page'], $page);
        }
    } else {
        $franchiseNumber = $this->admdetnew25->getFranchiseNumberByUserId($userId);
        if ($franchiseNumber) {
            $config['total_rows'] = $this->admdetnew25->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
            $data['records'] = $this->admdetnew25->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
        } else {
            $data['records'] = [];
        }
    }

    // --- START: Normalize and map class names for summary ---

   $classMapping = [
    'PLAYGROUP' => 'Play Group',
    'NURSERY' => 'Nursery',
    'KG1' => 'KG-1',
    'KG2' => 'KG-2',
    '1' => '1st', '1ST' => '1st',
    '2' => '2nd', '2ND' => '2nd',
    '3' => '3rd', '3RD' => '3rd',
    '4' => '4th', '4TH' => '4th',
    '5' => '5th', '5TH' => '5th',
    '6' => '6th', '6TH' => '6th',
    '7' => '7th', '7TH' => '7th',
    '8' => '8th', '8TH' => '8th',
    '9' => '9th', '9TH' => '9th',
    '10' => '10th', '10TH' => '10th',
    '11' => '11th', '11TH' => '11th',
    '12' => '12th', '12TH' => '12th',
];
 // Fetch all admissions for summary (ignoring pagination)
    $allAdmissions = $this->admdetnew25->getAllAdmissionsForSummary($franchiseFilter);

    $franchiseData = [];

    foreach ($allAdmissions as $row) {
        $franchise = $row->franchiseNumber;
        // Normalize class string: remove spaces, uppercase
        $classRaw = strtoupper(str_replace(' ', '', $row->class));

        // Map normalized class to display key, if not found map to null
        $class = isset($classMapping[$classRaw]) ? $classMapping[$classRaw] : null;

        if (!isset($franchiseData[$franchise])) {
            $franchiseData[$franchise] = [
                'total' => 0,
    'Play Group' => 0,
    'Nursery' => 0,
    'KG-1' => 0,
    'KG-2' => 0,
    '1st' => 0,
    '2nd' => 0,
    '3rd' => 0,
    '4th' => 0,
    '5th' => 0,
    '6th' => 0,
    '7th' => 0,
    '8th' => 0,
    '9th' => 0,
    '10th' => 0,
    '11th' => 0,
    '12th' => 0,
            ];
        }

        if ($class !== null) {
            $franchiseData[$franchise][$class] += $row->totalCount;
            $franchiseData[$franchise]['total'] += $row->totalCount;
        }
    }

    $data['franchiseData'] = $franchiseData;

    // Pagination and view data setup
    $data["serial_no"] = $page + 1;
    $this->pagination->initialize($config);
    $data["links"] = $this->pagination->create_links();
    $data["start"] = $page + 1;
    $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
    $data["total_records"] = $config["total_rows"];
    $data['pagination'] = $this->pagination->create_links();
    $data["franchiseFilter"] = $franchiseFilter;
    $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

    // Load the view with data
    $this->loadViews("admissiondetailsnew25/list", $this->global, $data, NULL);
}



    /**
     * This function is used to load the add new form
     */
   
    // sourabh code 12-04-2024
    public function fetchAssignedUsers() {
        $franchiseNumber = $this->input->post('franchiseNumber', TRUE);
        if (empty($franchiseNumber)) {
            echo json_encode(['status' => 'error', 'message' => 'Franchise number is required']);
            return;
        }
        $users = $this->admdetnew->getUsersByFranchise($franchiseNumber);
        if (empty($users)) {
            echo json_encode(['status' => 'success', 'html' => 'No users found for this franchise.', 'userIds' => '']);
            return;
        }
        $userNames = [];
        $userIds = [];
        foreach ($users as $user) {
            $userNames[] = htmlspecialchars($user->name);
            $userIds[] = $user->userId;
        }
        $html = implode(', ', $userNames);
        $userIdsString = implode(',', $userIds);
        echo json_encode(['status' => 'success', 'html' => $html, 'userIds' => $userIdsString]);
    }
  


}

?>