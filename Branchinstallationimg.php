<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Branchinstallationimg (BranchinstallationimgController)
 * Branchinstallationimg Class to control branch installation images and videos related operations.
 * @author : [Your Name]
 * @version : 1.0
 * @since : 16 May 2025
 */
class Branchinstallationimg extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Branchinstallationimg_model', 'bii');
        $this->load->model('Branches_model', 'bm'); // Load Branches_model
          $this->load->model('Notification_model', 'nm');
        $this->load->library(['form_validation', 'pagination']);
        $this->load->helper(['form', 'url']);
        $this->isLoggedIn();
        $this->module = 'Branchinstallationimg';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('branchinstallationimg/branchInstallationListing');
    }

    /**
     * This function is used to load the branch installation list
     */
   
public function branchInstallationListing()
{
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');
    $isAdminUser = in_array($userRole, ['1', '14']);
    $franchiseFilter = $this->input->get('franchiseNumber');

    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = '';
    }

    // Pagination configuration
    $config = array();
    $config['base_url'] = base_url('branchinstallationimg/branchInstallationListing');
    $config['per_page'] = 10;
    $config['uri_segment'] = 3;
    $config['num_links'] = 2; // Show 2 links on either side of current page
    $config['use_page_numbers'] = TRUE; // Use page numbers in URLs (e.g., /1, /2)
    $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
    $offset = ($page - 1) * $config['per_page'];

    // Bootstrap-compatible pagination tags
    $config['full_tag_open'] = '<ul class="pagination justify-content-center">';
    $config['full_tag_close'] = '</ul>';
    $config['first_tag_open'] = '<li class="page-item">';
    $config['first_tag_close'] = '</li>';
    $config['last_tag_open'] = '<li class="page-item">';
    $config['last_tag_close'] = '</li>';
    $config['next_tag_open'] = '<li class="page-item">';
    $config['next_tag_close'] = '</li>';
    $config['prev_tag_open'] = '<li class="page-item">';
    $config['prev_tag_close'] = '</li>';
    $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
    $config['cur_tag_close'] = '</a></li>';
    $config['num_tag_open'] = '<li class="page-item">';
    $config['num_tag_close'] = '</li>';
    $config['attributes'] = array('class' => 'page-link');

    $this->load->model('Branchinstallationimg_model', 'bim');
    $franchiseNumbers = [];

    if (!$isAdminUser) {
        $franchiseNumber = $this->bim->getFranchiseNumberByUserId($userId);
        if ($franchiseNumber) {
            $franchiseNumbers = [$franchiseNumber];
        }
    }

    if ($isAdminUser) {
        if ($franchiseFilter) {
            $franchiseNumbers = [$franchiseFilter];
            $config['total_rows'] = $this->bim->getTotalRecordsCount($userId, $userRole, $franchiseNumbers, $isAdminUser);
            $data['records'] = $this->bim->getAllRecords($config['per_page'], $offset, $userId, $userRole, $franchiseNumbers, $isAdminUser);
        } else {
            $config['total_rows'] = $this->bim->getTotalRecordsCount($userId, $userRole, [], $isAdminUser);
            $data['records'] = $this->bim->getAllRecords($config['per_page'], $offset, $userId, $userRole, [], $isAdminUser);
        }
    } elseif ($userRole == '15' || $userRole == '13') {
        $config['total_rows'] = $this->bim->getTotalRecordsCountByRole($userId);
        $data['records'] = $this->bim->getRecordsByRole($userId, $config['per_page'], $offset);
    } else {
        if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
            $franchiseNumbers = [$franchiseFilter];
        }
        $config['total_rows'] = $this->bim->getTotalRecordsCount($userId, $userRole, $franchiseNumbers, $isAdminUser);
        $data['records'] = $this->bim->getAllRecords($config['per_page'], $offset, $userId, $userRole, $franchiseNumbers, $isAdminUser);
    }


    // Initialize pagination
    $this->pagination->initialize($config);
    $data['serial_no'] = $offset + 1;
    $data['links'] = $this->pagination->create_links();
    $data['start'] = $offset + 1;
    $data['end'] = min($offset + $config['per_page'], $config['total_rows']);
    $data['total_records'] = $config['total_rows'];
    $data['franchiseFilter'] = $franchiseFilter;
    $data['role'] = $userRole;
    $data['is_admin'] = $isAdminUser ? 1 : 0;

    // Load view
    $this->loadViews('branchinstallationimg/list', $this->global, $data, NULL);
}
    /**
     * This function is used to load the add new form
     */
    public function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->global['pageTitle'] = 'eduMETA : Add New Branch Installation';
            $this->global['role'] = $this->session->userdata('role');
            $this->global['name'] = $this->session->userdata('name');
            $this->global['is_admin'] = $this->session->userdata('is_admin');
            $this->global['access_info'] = $this->session->userdata('access_info') ?? [];
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber(); // Use Branches_model
            $this->loadViews('branchinstallationimg/add', $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new branch installation to the system
     */
 public function addNewBranchinstallationimg()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->form_validation->set_rules('franchiseNumber', 'Franchise Number', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('brimgvideoTitle', 'Title', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            $this->add();
        } else {
            $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
            
            // Validate that franchiseNumber is not an array
            if (is_array($franchiseNumber)) {
                $this->session->set_flashdata('error', 'Franchise Number must be a single value, not multiple selections.');
                $this->add();
                return;
            }

            $branchInstallationInfo = [
                'franchiseNumber' => $franchiseNumber,
                'brimgvideoTitle' => $this->security->xss_clean($this->input->post('brimgvideoTitle')),
                'description' => $this->security->xss_clean($this->input->post('description')),
                'brspFranchiseAssigned' => $this->security->xss_clean($this->input->post('brspFranchiseAssigned')), 
                'isDeleted' => 0,
                'createdBy' => $this->session->userdata('userId'),
                'createdDtm' => date('Y-m-d H:i:s'),
                'updatedBy' => $this->session->userdata('userId'),
                'updatedDtm' => date('Y-m-d H:i:s')
            ];

            // Handle image uploads (multiple files)
         $s3_image_links = [];
if (!empty($_FILES['files']['name'][0])) {
    $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'branch_installation_temp' . DIRECTORY_SEPARATOR;
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true); // Create temporary directory if it doesn't exist
    }

    foreach ($_FILES['files']['name'] as $key => $name) {
        if (!empty($name)) {
            $tmpName = $_FILES['files']['tmp_name'][$key];
            $fileType = mime_content_type($tmpName);
            if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/gif'])) {
                $this->session->set_flashdata('error', 'Only JPEG, PNG, or GIF images are allowed.');
                $this->add();
                return;
            }

            // Generate a unique file name
            $uniqueName = time() . '_' . $key . '_' . uniqid() . '_' . preg_replace("/[^A-Za-z0-9._-]/", "", $name);
            $tempFilePath = $tempDir . $uniqueName;

            // Move the uploaded file to the temporary directory
            if (move_uploaded_file($tmpName, $tempFilePath)) {
                $storeFolder = 'branch_installation_images';
                $s3Key = $storeFolder . '/' . $uniqueName;

                // Upload to S3
                $s3Result = $this->s3_upload->upload_file($tempFilePath, $s3Key);
                $result_arr = $s3Result->toArray();

                if (!empty($result_arr['ObjectURL'])) {
                    $s3_image_links[] = $result_arr['ObjectURL'];
                } else {
                    $s3_image_links[] = '';
                    log_message('error', 'S3 upload failed for image: ' . $name . ' - Error: ' . json_encode($result_arr));
                }

                // Clean up the temporary file
                @unlink($tempFilePath);
            } else {
                $this->session->set_flashdata('error', 'Failed to move uploaded file: ' . $name);
                $this->add();
                return;
            }
        }
    }

    // Clean up the temporary directory if empty
    @rmdir($tempDir);
}
$branchInstallationInfo['empimgS3attachment'] = implode(',', $s3_image_links);



            // Handle video upload (single file)
            $s3_video_links = [];
            if (isset($_FILES['file2']) && !empty($_FILES['file2']['tmp_name'])) {
                $fileType = mime_content_type($_FILES['file2']['tmp_name']);
                if (!in_array($fileType, ['video/mp4', 'video/avi', 'video/quicktime'])) {
                    $this->session->set_flashdata('error', 'Only MP4, AVI, or MOV videos are allowed.');
                    $this->add();
                    return;
                }

                $dir = dirname($_FILES['file2']['tmp_name']);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES['file2']['name'];

                if (rename($_FILES['file2']['tmp_name'], $destination)) {
                    $storeFolder = 'branch_installation_videos';

                    $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                    $result_arr = $s3Result->toArray();

                    if (!empty($result_arr['ObjectURL'])) {
                        $s3_video_links[] = $result_arr['ObjectURL'];
                    } else {
                        log_message('error', 'S3 upload failed for video: ' . $_FILES['file2']['name'] . ' - Error: ' . json_encode($result_arr));
                        $s3_video_links[] = '';
                    }

                    @unlink($destination);
                }
            }
            $branchInstallationInfo['empvideosS3attachment'] = implode(',', $s3_video_links);


            
            $result = $this->bii->insert($branchInstallationInfo);

            if ($result > 0) {
                    // Add notifications for all users
                    $notificationMessage = "<strong>Branch Installation Image Confirmation:</strong> New Branch Installation Image confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 19, 25, 15,13])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_Branchinstallationimg_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
                $this->session->set_flashdata('success', 'Branch installation added successfully.');
            } else {
                $this->session->set_flashdata('error', 'Failed to add branch installation.');
            }

            redirect('Branchinstallationimg/branchInstallationListing');
        }
    }
}

    /**
     * This function is used to load the edit form
     */
    public function edit($id = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($id == NULL) {
                redirect('Branchinstallationimg/branchInstallationListing');
            }

            $data['record'] = $this->bii->get_by_id($id);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber(); // Add branch details for edit form

            if (!$data['record']) {
                $this->session->set_flashdata('error', 'Record not found.');
                redirect('Branchinstallationimg/branchInstallationListing');
            }

            $this->global['pageTitle'] = 'eduMETA : Edit Branch Installation';
            $this->global['role'] = $this->session->userdata('role');
            $this->global['name'] = $this->session->userdata('name');
            $this->global['is_admin'] = $this->session->userdata('is_admin');
            $this->global['access_info'] = $this->session->userdata('access_info') ?? [];

            $this->loadViews('branchinstallationimg/edit', $this->global, $data, NULL);
        }
    }


     public function view($brimgvideoId = NULL)
{
    if (!$this->hasListAccess()) {
        $this->loadThis();
    } else {
        if ($brimgvideoId == NULL) {
            $this->session->set_flashdata('error', 'Branch installation not found');
            redirect('Branchinstallationimg/branchInstallationListing');
        }

        $data['record'] = $this->bii->get_by_id($brimgvideoId);
        if (empty($data['record'])) {
            $this->session->set_flashdata('error', 'Branch installation not found');
            redirect('Branchinstallationimg/branchInstallationListing');
        }

        $this->global['pageTitle'] = 'eduMETA : View Branch Installation';
        $this->loadViews('branchinstallationimg/view', $this->global, $data, NULL);
    }
}
    /**
     * This function is used to update the branch installation
     */
    public function update()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $id = $this->input->post('brimgvideoId');

            $this->form_validation->set_rules('franchiseNumber', 'Franchise Number', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('brimgvideoTitle', 'Title', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('description', 'Description', 'trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                $this->edit($id);
            } else {
                $branchInstallationInfo = [
                    'franchiseNumber' => $this->security->xss_clean($this->input->post('franchiseNumber')),
                    'brimgvideoTitle' => $this->security->xss_clean($this->input->post('brimgvideoTitle')),
                    'description' => $this->security->xss_clean($this->input->post('description')),
                    'updatedBy' => $this->session->userdata('userId'),
                    'updatedDtm' => date('Y-m-d H:i:s')
                ];

                // Handle image uploads (multiple files)
                $s3_image_links = [];
                if (!empty($_FILES['files']['name'][0])) {
                    foreach ($_FILES['files']['name'] as $key => $name) {
                        if (!empty($name)) {
                            $tmpName = $_FILES['files']['tmp_name'][$key];
                            $dir = dirname($tmpName);
                            $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $name;

                            if (move_uploaded_file($tmpName, $destination)) {
                                $storeFolder = 'attachments';

                                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                                $result_arr = $s3Result->toArray();

                                if (!empty($result_arr['ObjectURL'])) {
                                    $s3_image_links[] = $result_arr['ObjectURL'];
                                } else {
                                    $s3_image_links[] = '';
                                }

                                // Optionally, delete the temporary file
                                @unlink($destination);
                            }
                        }
                    }
                    $branchInstallationInfo['empimgS3attachment'] = implode(',', $s3_image_links);
                }

                // Handle video upload (single file)
                $s3_video_links = [];
                if (isset($_FILES['file2']) && !empty($_FILES['file2']['tmp_name'])) {
                    $dir = dirname($_FILES['file2']['tmp_name']);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES['file2']['name'];

                    if (rename($_FILES['file2']['tmp_name'], $destination)) {
                        $storeFolder = 'branch_installation_videos';

                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();

                        if (!empty($result_arr['ObjectURL'])) {
                            $s3_video_links[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_video_links[] = '';
                        }

                        // Optionally, delete the temporary file
                        @unlink($destination);
                    }
                }
                if (!empty($s3_video_links)) {
                    $branchInstallationInfo['empvideosS3attachment'] = implode(',', $s3_video_links);
                }

                $result = $this->bii->update($id, $branchInstallationInfo);

                if ($result > 0) {
                    // Add notifications for all users
                    $notificationMessage = "<strong>Branch Installation Image Confirmation:</strong> Update Branch Installation Image confirmation";
                    $users = $this->db->select('userId')
                        ->from('tbl_users')
                        ->where_in('roleId', [1, 14, 19, 25, 15,13])
                        ->get()
                        ->result_array();

                    if (!empty($users)) {
                        $userIds = array_column($users, 'userId');
                        foreach ($userIds as $userId) {
                            $notificationResult = $this->nm->add_Branchinstallationimg_notification($result, $notificationMessage, $userId);
                            if (!$notificationResult) {
                                log_message('error', "Failed to add notification for user {$userId} on campaign ID {$result}");
                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'Branch installation updated successfully.');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update branch installation.');
                }

                redirect('Branchinstallationimg/branchInstallationListing');
            }
        }
    }

    /**
     * This function is used to delete the branch installation
     */
    public function delete($id = NULL)
    {
        if (!$this->hasDeleteAccess()) {
            $this->loadThis();
        } else {
            if ($id == NULL) {
                $this->session->set_flashdata('error', 'Invalid record ID.');
                redirect('Branchinstallationimg/branchInstallationListing');
            }

            $result = $this->bii->delete($id);

            if ($result) {
                $this->session->set_flashdata('success', 'Branch installation deleted successfully.');
            } else {
                $this->session->set_flashdata('error', 'Failed to delete branch installation.');
            }

            redirect('Branchinstallationimg/branchInstallationListing');
        }
    }
  
}