<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Blog (Blog)
 * Blog Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 28 May 2024
 */
class Festiveimages extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Festiveimages_model', 'fstive');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Festiveimages';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('festiveimages/festiveimagesListing');
    }

    /**
     * This function is used to load the Support list
     */
    public function festiveimagesListing()
    {
        if (!$this->hasListAccess()) {
            $this->loadThis();
        } else {
            $searchText = $this->security->xss_clean($this->input->post('searchText') ?? '');
            $data['searchText'] = $searchText;

            $this->load->library('pagination');
            $count = $this->fstive->festiveimagesListingCount($searchText);
            $returns = $this->paginationCompress("festiveimages/", $count, 500);

            $records = $this->fstive->festiveimagesListing($searchText, $returns["page"], $returns["segment"]);
            $data['branchDetail'] = $this->fstive->getBranchesFranchiseNumber();
          

            $franchiseData = [];
            if (!empty($records)) {
                $franchiseNumbers = array_unique(array_column($records, 'franchiseNumber'));
                foreach ($franchiseNumbers as $fn) {
                    $fData = $this->fstive->getFranchiseData($fn);
                    $franchiseData[$fn] = $fData ? [
                        'branchAddress' => $fData->branchAddress ?? 'N/A',
                        'mobile' => $fData->mobile ?? '1234567890'
                    ] : [
                        'branchAddress' => 'N/A',
                        'mobile' => '1234567890'
                    ];
                }
            }
            $data['franchiseData'] = $franchiseData;
            $data['records'] = $records;

            $defaultFranchise = $this->session->userdata('franchiseNumber');
            if ($defaultFranchise && !$this->session->userdata('branchAddress')) {
                $fData = $this->fstive->getFranchiseData($defaultFranchise);
                if ($fData) {
                    $this->session->set_userdata([
                        'franchiseNumber' => $defaultFranchise,
                        'branchAddress' => $fData->branchAddress,
                        'branchContacNum' => $fData->mobile
                    ]);
                }
            }

            $this->global['pageTitle'] = 'Festiveimages : Festive Images';
            $this->loadViews("festiveimages/list", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Blog';
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("festiveimages/add", $this->global,  NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
   public function addNewFestiveimages()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('festiveimagesTitle', 'Festiveimages Title', 'trim|required|max_length[256]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            $festiveimagesTitle = $this->security->xss_clean($this->input->post('festiveimagesTitle'));
            $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
            $branchAddress = $this->security->xss_clean($this->input->post('branchAddress'));
            $mobile = $this->security->xss_clean($this->input->post('mobile'));

            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $dir = dirname($_FILES["file"]["tmp_name"]);
                // Generate a unique file name with original extension
                $fileExtension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
                $uniqueName = uniqid('festive_', true) . '.' . $fileExtension;
                $destination = $dir . DIRECTORY_SEPARATOR . $uniqueName;
                rename($_FILES["file"]["tmp_name"], $destination);

                // Generate a unique folder path for S3
                $uniqueFolder = 'images/festive/' . date('Ymd_His') . '_' . uniqid();
                $storeFolder = $uniqueFolder;

                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();

                $s3files = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
            } else {
                $s3files = '';
            }

            $festiveimagesInfo = [
                'festiveimagesTitle' => $festiveimagesTitle,
                'festiveimagesS3Image' => $s3files,
                'franchiseNumber' => $franchiseNumber,
                'branchAddress' => $branchAddress,
                'mobile' => $mobile,
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
            ];

            $festiveimagesId = $this->fstive->addNewFestiveimages($festiveimagesInfo);

            if ($festiveimagesId > 0) {
                // Send Notification to Admins or Specific Users
                $notificationMessage = "<strong>Festival Images Confirmation:</strong> New Festive images confirmation";
                $users = $this->db->select('userId')
                    ->from('tbl_users')
                    ->where_in('roleId', [1, 14, 25, 15, 18, 19])
                    ->get()
                    ->result_array();

                if (!empty($users)) {
                    $userIds = array_column($users, 'userId');
                    foreach ($userIds as $userId) {
                        $notificationResult = $this->nm->add_festiveimages_notification($festiveimagesId, $notificationMessage, $userId);
                        if (!$notificationResult) {
                            log_message('error', "Failed to add notification for user {$userId} on campaign ID {$festiveimagesId}");
                        }
                    }
                }

                $this->session->set_flashdata('success', 'New Blog created successfully');
            } else {
                $this->session->set_flashdata('error', 'Blog creation failed');
            }

            redirect('festiveimages/festiveimagesListing');
        }
    }
}

    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($festiveimagesId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($festiveimagesId == null) {
                redirect('festiveimages/festiveimagesListing');
            }

            $data['festiveimagesInfo'] = $this->fstive->getfestiveimagesInfo($festiveimagesId);
            //$data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'festiveimages : Edit festiveimages';

            $this->loadViews("festiveimages/edit", $this->global, $data, NULL);
        }
    }

    public function fetchFranchiseData()
    {
        $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
        $response = [
            'status' => 'success',
            'franchiseData' => null,
            'franchiseMessage' => ''
        ];

        if ($franchiseNumber) {
            $franchiseData = $this->fstive->getFranchiseData($franchiseNumber);
            if ($franchiseData) {
                $response['franchiseData'] = [
                    'brAddress' => htmlspecialchars($franchiseData->branchAddress ?? ''),
                    'branchContacNum' => htmlspecialchars($franchiseData->mobile ?? '')
                ];
                // Update session data
                $this->session->set_userdata([
                    'franchiseNumber' => $franchiseNumber,
                    'branchLocAddressPremise' => $franchiseData->branchAddress,
                    'branchContacNum' => $franchiseData->mobile
                ]);
            } else {
                $response['franchiseMessage'] = 'No franchise data found for franchise number: ' . htmlspecialchars($franchiseNumber);
                log_message('error', $response['franchiseMessage']);
            }
        } else {
            $response['franchiseMessage'] = 'No franchise number provided.';
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    /**
     * This function is used to edit the user information
     */
    function editFestiveimages()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $blogId = $this->input->post('festiveimagesId');

            $this->form_validation->set_rules('festiveimagesTitle', 'festiveimages Title', 'trim|required|max_length[256]');


            if ($this->form_validation->run() == FALSE) {
                $this->edit($festiveimagesId);
            } else {
                $festiveimagesTitle = $this->security->xss_clean($this->input->post('festiveimagesTitle'));

                /*-ENd-added-field-*/
                $festiveimagesInfo = array('festiveimagesTitle' => $festiveimagesTitle, 'updatedBy' => $this->vendorId, 'updatedDtm' => date('Y-m-d H:i:s'));
                $result = $this->fstive->editFestiveimages($festiveimagesInfo, $blogId);

                if ($result == true) {
                    $this->session->set_flashdata('success', 'Festiveimages updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Festiveimages updation failed');
                }

                redirect('festiveimages/festiveimagesListing');
            }
        }
    }
}
