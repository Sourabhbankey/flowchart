<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Hreventgallery (HreventgalleryController)
 * Hreventgallery Class to control Hreventgallery related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 12 May 2025
 */
class Hreventgallery extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hreventgallery_model', 'hrgalry');
        $this->load->model('Notification_model', 'nm');
        $this->isLoggedIn();
        $this->module = 'Hreventgallery';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('hreventgallery/hreventgalleryListing');
    }

    /**
     * This function is used to load the hreventgallery list
     */
    public function hreventgalleryListing()
    {
        if (!$this->hasListAccess()) {
            $this->loadThis();
        } else {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $searchText;

            $this->load->library('pagination');

            $count = $this->hrgalry->hreventgalleryListingCount($searchText);

            $config = array();
            $config['base_url'] = base_url('hreventgallery/hreventgalleryListing');
            $config['total_rows'] = $count;
            $config['per_page'] = 10;
            $config['uri_segment'] = 3;
            $config['full_tag_open'] = '<ul class="pagination">';
            $config['full_tag_close'] = '</ul>';
            $config['first_link'] = 'First';
            $config['last_link'] = 'Last';
            $config['next_link'] = '&raquo;';
            $config['prev_link'] = '&laquo;';
            $config['cur_tag_open'] = '<li class="active"><a>';
            $config['cur_tag_close'] = '</a></li>';
            $config['num_tag_open'] = '<li>';
            $config['num_tag_close'] = '</li>';
            $config['anchor_class'] = 'class="page-link"';

            $this->pagination->initialize($config);

            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $data['records'] = $this->hrgalry->hreventgalleryListingpage($searchText, $config['per_page'], $page);

            $this->global['pageTitle'] = 'CodeInsect : Hreventgallery';

            $this->loadViews("hreventgallery/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Hreventgallery';

            $this->loadViews("hreventgallery/add", $this->global, NULL, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    function addNewHreventgallery()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('eventName', 'Title', 'trim|required|max_length[50]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                $eventName = $this->security->xss_clean($this->input->post('eventName'));
                $venue = $this->security->xss_clean($this->input->post('venue'));
                $eventDate = $this->security->xss_clean($this->input->post('eventDate'));
                $description = $this->security->xss_clean($this->input->post('description'));


                $uniqueFolder = 'attachements/events/' . url_title($eventName, '-', TRUE) . '-' . time();

                // Image uploads
                if (!empty($_FILES['files']['name'][0])) {
                    foreach ($_FILES['files']['name'] as $key => $name) {
                        $tmpName = $_FILES['files']['tmp_name'][$key];
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $uniqueFileName = uniqid('img_') . '.' . $ext; // Generate unique filename
                        $dir = dirname($tmpName);
                        $destination = $dir . DIRECTORY_SEPARATOR . $uniqueFileName;

                        if (move_uploaded_file($tmpName, $destination)) {
                            $s3Path = $uniqueFolder . '/images/' . $uniqueFileName; // Unique S3 path
                            $original_file_paths[] = $destination;
                            $temp_files[] = $destination;

                            $s3Result = $this->s3_upload->upload_file($destination, $s3Path);
                            $result_arr = $s3Result->toArray();

                            if (!empty($result_arr['ObjectURL'])) {
                                $s3_file_links[] = $result_arr['ObjectURL'];
                            } else {
                                $s3_file_links[] = '';
                            }
                        }
                    }
                }

                $s3files = implode(',', $s3_file_links);

                // Video upload
                if (isset($_FILES['file2']) && !empty($_FILES['file2']['tmp_name'])) {
                    $ext = pathinfo($_FILES['file2']['name'], PATHINFO_EXTENSION);
                    $uniqueFileName = uniqid('video_') . '.' . $ext; // Generate unique filename
                    $dir = dirname($_FILES["file2"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . $uniqueFileName;

                    if (rename($_FILES["file2"]["tmp_name"], $destination)) {
                        $s3Path = $uniqueFolder . '/videos/' . $uniqueFileName; // Unique S3 path
                        $original_file_paths[] = $destination;
                        $temp_files[] = $destination;

                        $s3Result = $this->s3_upload->upload_file($destination, $s3Path);
                        $result_arr = $s3Result->toArray();

                        if (!empty($result_arr['ObjectURL'])) {
                            $s3_video_links[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_video_links[] = '';
                        }
                    }
                }

                $s3files2 = implode(',', $s3_video_links);

                $hreventgalleryInfo = array('eventName' => $eventName, 'venue' => $venue, 'eventDate' => $eventDate, 'description' => $description, 'eventS3attachment' => $s3files, 'eventvideoS3attachment' => $s3files2, 'createdBy' => $this->vendorId, 'createdDtm' => date('Y-m-d H:i:s'));

                $result = $this->hrgalry->addNewHreventgallery($hreventgalleryInfo);

                if ($result > 0) {
                    $users = $this->nm->get_all_users(); // Fetch all users
                    foreach ($users as $user) {
                        $message = "<strong>Event Gallery:</strong>A new Galllery has been added";
                        $this->nm->add_hrevent_notification($result, $message, $user['userId']);
                    }
                    $this->session->set_flashdata('success', 'New Event created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Event creation failed');
                }

                redirect('hreventgallery/hreventgalleryListing');
            }
        }
    }
    function view($hreventId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($hreventId == null) {
                redirect('hreventgallery/hreventgalleryListing');
            }

            $data['hreventgalleryInfo'] = $this->hrgalry->getHreventgalleryInfo($hreventId);

            $this->global['pageTitle'] = 'CodeInsect : View Hreventgallery';

            $this->loadViews("hreventgallery/view", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used load hreventgallery edit information
     * @param number $hreventId : Optional : This is hreventgallery id
     */
    function edit($hreventId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($hreventId == null) {
                redirect('hreventgallery/hreventgalleryListing');
            }

            $data['hreventgalleryInfo'] = $this->hrgalry->getHreventgalleryInfo($hreventId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Event Galllery';

            $this->loadViews("hreventgallery/edit", $this->global, $data, NULL);
        }
    }


    /**
     * This function is used to edit the user information
     */
    function editHreventgallery()
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $hreventId = $this->input->post('hreventId');

            $this->form_validation->set_rules('eventName', 'Title', 'trim|required|max_length[50]');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

            if ($this->form_validation->run() == FALSE) {
                $this->edit($hreventId);
            } else {
                $eventName = $this->security->xss_clean($this->input->post('eventName'));
                $venue = $this->security->xss_clean($this->input->post('venue'));
                $eventDate = $this->security->xss_clean($this->input->post('eventDate'));
                $description = $this->security->xss_clean($this->input->post('description'));

                $hreventgalleryInfo = array('eventName' => $eventName, 'venue' => $venue, 'eventDate' => $eventDate, 'description' => $description, 'updatedBy' => $this->vendorId, 'updatedDtm' => date('Y-m-d H:i:s'));

                $result = $this->hrgalry->editHreventgallery($hreventgalleryInfo, $hreventId);

                if ($result == true) {
                    $this->session->set_flashdata('success', 'Employee of month updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Employee of month updation failed');
                }

                redirect('hreventgallery/hreventgalleryListing');
            }
        }
    }
}
