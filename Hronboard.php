<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Staff (StaffController)
 * Staff Class to control Staff related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 13 June 2024
 */
class Hronboard extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hronboard_model', 'hron');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->load->library('pagination');
        $this->module = 'Hronboard';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('hronboard/hronboardListing');
    }

    /**
     * This function is used to load the Staff list
     */

    //code done by yashi

    public function hronboardListing()
    {
        $userId = $this->session->userdata('userId');
        $userRole = $this->session->userdata('role');

        $franchiseFilter = $this->input->get('franchiseNumber');
        if ($this->input->get('resetFilter') == '1') {
            $franchiseFilter = '';
        }
        $config = array();
        $config['base_url'] = base_url('hronboard/hronboardListing');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        if ($userRole == '14' || $userRole == '1' || $userRole == '23' || $userRole == '20' || $userRole == '21' || $userRole == '28' || $userRole == '26') { // Admin
            if ($franchiseFilter) {
                $config['total_rows'] = $this->hron->getTotalTrainingRecordsCountByFranchise($franchiseFilter);
                $data['records'] = $this->hron->getTrainingRecordsByFranchise($franchiseFilter, $config['per_page'], $page);
            } else {
                $config['total_rows'] = $this->hron->getTotalTrainingRecordsCount();

                $data['records'] = $this->hron->getAllTrainingRecords($config['per_page'], $page);
            }
        } else if ($userRole == '15' || $userRole == '13') { // Specific roles
            $config['total_rows'] = $this->hron->getTotalTrainingRecordsCountByRole($userId);
            $data['records'] = $this->stf->getTrainingRecordsByRole($userId, $config['per_page'], $page);
        } else {
            $franchiseNumber = $this->hron->getFranchiseNumberByUserId($userId);
            if ($franchiseNumber) {
                if ($franchiseFilter && $franchiseFilter == $franchiseNumber) {
                    $config['total_rows'] = $this->hron->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->hron->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                } else {
                    $config['total_rows'] = $this->hron->getTotalTrainingRecordsCountByFranchise($franchiseNumber);
                    $data['records'] = $this->hron->getTrainingRecordsByFranchise($franchiseNumber, $config['per_page'], $page);
                }
            } else {
                $data['records'] = []; // Handle the case where franchise number is not found
            }
        }

        // Initialize pagination
        $data["serial_no"] = $page + 1;
        $this->pagination->initialize($config);
        $data["links"] = $this->pagination->create_links();
        $data["start"] = $page + 1;
        $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
        $data["total_records"] = $config["total_rows"];
        $data['pagination'] = $this->pagination->create_links();
        $data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $this->loadViews("hronboard/list", $this->global, $data, NULL);
    }

    //ends here
    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Staff';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();

            $this->loadViews("hronboard/add", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to add new user to the system
     */
    function addNewHronboard()
    {
        if (!$this->hasCreateAccess()) {
            $this->loadThis();
        } else {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('first_name', 'First Name', 'trim|required|max_length[256]');


            if ($this->form_validation->run() == FALSE) {
                $this->add();
            } else {
                // Personal details 
                $first_name = $this->security->xss_clean($this->input->post('first_name'));
                $last_name = $this->security->xss_clean($this->input->post('last_name'));
                $dob = $this->security->xss_clean($this->input->post('dob'));
                $gender = $this->security->xss_clean($this->input->post('gender'));
                $email = $this->security->xss_clean($this->input->post('email'));
                $contact_number = $this->security->xss_clean($this->input->post('contact_number'));
                $alt_contact_number = $this->security->xss_clean($this->input->post('alt_contact_number'));
                $permanent_address = $this->security->xss_clean($this->input->post('permanent_address'));
                $communication_address = $this->security->xss_clean($this->input->post('communication_address'));
                $city = $this->security->xss_clean($this->input->post('city'));
                $state = $this->security->xss_clean($this->input->post('state'));
                $pincode = $this->security->xss_clean($this->input->post('pincode'));
                $nationality = $this->security->xss_clean($this->input->post('nationality'));
                $languages_known = $this->security->xss_clean($this->input->post('languages_known'));
                $marital_status = $this->security->xss_clean($this->input->post('marital_status'));
                $anniversary_date = $this->security->xss_clean($this->input->post('anniversary_date'));
                $social_media = $this->security->xss_clean($this->input->post('social_media'));
                // Employment Details

                $designation = $this->security->xss_clean($this->input->post('designation'));
                $joining_date = $this->security->xss_clean($this->input->post('joining_date'));
                $employment_status = $this->security->xss_clean($this->input->post('employment_status'));
                $pay_rate = $this->security->xss_clean($this->input->post('pay_rate'));
                $prev_organisation = $this->security->xss_clean($this->input->post('prev_organisation'));
                //Bank Details

                $bank_name = $this->security->xss_clean($this->input->post('bank_name'));
                $branch_name = $this->security->xss_clean($this->input->post('branch_name'));
                $account_holder = $this->security->xss_clean($this->input->post('account_holder'));
                $account_number = $this->security->xss_clean($this->input->post('account_number'));
                $ifsc_code = $this->security->xss_clean($this->input->post('ifsc_code'));

                //Emergency Contact
                $emergency_name = $this->security->xss_clean($this->input->post('emergency_name'));
                $emergency_relationship = $this->security->xss_clean($this->input->post('emergency_relationship'));
                $emergency_contact = $this->security->xss_clean($this->input->post('emergency_contact'));
                $emergency_address = $this->security->xss_clean($this->input->post('emergency_address'));

                // Upload Documents Files

                if (isset($_FILES["file"]["tmp_name"]) && !empty($_FILES["file"]["tmp_name"])) {
                    $dir = dirname($_FILES["file"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                    if (rename($_FILES["file"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link[] = '';
                        }
                    } else {
                        $s3_file_link[] = '';
                    }
                } else {
                    $s3_file_link[] = '';
                }
                $s3files = implode(',', $s3_file_link);


                // Upload Documents File1       

                if (isset($_FILES["file1"]["tmp_name"]) && !empty($_FILES["file1"]["tmp_name"])) {
                    $dir = dirname($_FILES["file1"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file1"]["name"];
                    if (rename($_FILES["file1"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link1[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link1[] = '';
                        }
                    } else {
                        $s3_file_link1[] = '';
                    }
                } else {
                    $s3_file_link1[] = '';
                }
                $s3files1 = implode(',', $s3_file_link1);


                // Upload Documents File2        

                if (isset($_FILES["file2"]["tmp_name"]) && !empty($_FILES["file2"]["tmp_name"])) {
                    $dir = dirname($_FILES["file2"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file2"]["name"];
                    if (rename($_FILES["file2"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link2[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link2[] = '';
                        }
                    } else {
                        $s3_file_link2[] = '';
                    }
                } else {
                    $s3_file_link2[] = '';
                }
                $s3files2 = implode(',', $s3_file_link2);

                // Upload Documents File3 

                if (isset($_FILES["file3"]["tmp_name"]) && !empty($_FILES["file3"]["tmp_name"])) {
                    $dir = dirname($_FILES["file3"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file3"]["name"];
                    if (rename($_FILES["file3"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link3[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link3[] = '';
                        }
                    } else {
                        $s3_file_link3[] = '';
                    }
                } else {
                    $s3_file_link3[] = '';
                }
                $s3files3 = implode(',', $s3_file_link3);

                // Upload Documents File4 

                if (isset($_FILES["file4"]["tmp_name"]) && !empty($_FILES["file4"]["tmp_name"])) {
                    $dir = dirname($_FILES["file4"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file4"]["name"];
                    if (rename($_FILES["file4"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link4[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link4[] = '';
                        }
                    } else {
                        $s3_file_link4[] = '';
                    }
                } else {
                    $s3_file_link4[] = '';
                }
                $s3files4 = implode(',', $s3_file_link4);


                // Upload Documents File5 


                if (isset($_FILES["file5"]["tmp_name"]) && !empty($_FILES["file5"]["tmp_name"])) {
                    $dir = dirname($_FILES["file5"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file5"]["name"];
                    if (rename($_FILES["file5"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link5[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link5[] = '';
                        }
                    } else {
                        $s3_file_link5[] = '';
                    }
                } else {
                    $s3_file_link5[] = '';
                }
                $s3files5 = implode(',', $s3_file_link5);

                // Upload Documents File6 


                if (isset($_FILES["file6"]["tmp_name"]) && !empty($_FILES["file6"]["tmp_name"])) {
                    $dir = dirname($_FILES["file6"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file6"]["name"];
                    if (rename($_FILES["file6"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link6[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link6[] = '';
                        }
                    } else {
                        $s3_file_link6[] = '';
                    }
                } else {
                    $s3_file_link6[] = '';
                }
                $s3files6 = implode(',', $s3_file_link6);
                // Upload Documents File7 


                if (isset($_FILES["file7"]["tmp_name"]) && !empty($_FILES["file7"]["tmp_name"])) {
                    $dir = dirname($_FILES["file7"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file7"]["name"];
                    if (rename($_FILES["file7"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link7[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link7[] = '';
                        }
                    } else {
                        $s3_file_link7[] = '';
                    }
                } else {
                    $s3_file_link7[] = '';
                }
                $s3files7 = implode(',', $s3_file_link7);
                // Upload Documents File8 


                if (isset($_FILES["file8"]["tmp_name"]) && !empty($_FILES["file8"]["tmp_name"])) {
                    $dir = dirname($_FILES["file8"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file8"]["name"];
                    if (rename($_FILES["file8"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link8[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link8[] = '';
                        }
                    } else {
                        $s3_file_link8[] = '';
                    }
                } else {
                    $s3_file_link8[] = '';
                }
                $s3files8 = implode(',', $s3_file_link8);


                // Upload Documents File9 


                if (isset($_FILES["file9"]["tmp_name"]) && !empty($_FILES["file9"]["tmp_name"])) {
                    $dir = dirname($_FILES["file9"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file9"]["name"];
                    if (rename($_FILES["file9"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link9[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link9[] = '';
                        }
                    } else {
                        $s3_file_link9[] = '';
                    }
                } else {
                    $s3_file_link9[] = '';
                }
                $s3files9 = implode(',', $s3_file_link9);


                // Upload Documents File10 (10th Marksheet)
                if (isset($_FILES["file10"]["tmp_name"]) && !empty($_FILES["file10"]["tmp_name"])) {
                    $dir = dirname($_FILES["file10"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file10"]["name"];
                    if (rename($_FILES["file10"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link10[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link10[] = '';
                        }
                    } else {
                        $s3_file_link10[] = '';
                    }
                } else {
                    $s3_file_link10[] = '';
                }
                $s3files10 = implode(',', $s3_file_link10);

                // Upload Documents File11 (Certificate Upload)
                if (isset($_FILES["file11"]["tmp_name"]) && !empty($_FILES["file11"]["tmp_name"])) {
                    $dir = dirname($_FILES["file11"]["tmp_name"]);
                    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file11"]["name"];
                    if (rename($_FILES["file11"]["tmp_name"], $destination)) {
                        $storeFolder = 'attachements';
                        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                        $result_arr = $s3Result->toArray();
                        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
                            $s3_file_link11[] = $result_arr['ObjectURL'];
                        } else {
                            $s3_file_link11[] = '';
                        }
                    } else {
                        $s3_file_link11[] = '';
                    }
                } else {
                    $s3_file_link11[] = '';
                }
                $s3files11 = implode(',', $s3_file_link11);



                // Insert data into hronboardInfo array
                $hronboardInfo = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'dob' => $dob,
                    'gender' => $gender,
                    'email' => $email,
                    'contact_number' => $contact_number,
                    'alt_contact_number' => $alt_contact_number,
                    'permanent_address' => $permanent_address,
                    'communication_address' => $communication_address,
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode,
                    'nationality' => $nationality,
                    'languages_known' => $languages_known,
                    'marital_status' => $marital_status,
                    'anniversary_date' => $anniversary_date,
                    'social_media' => $social_media,
                    'designation' => $designation,
                    'joining_date' => $joining_date,
                    'employment_status' => $employment_status,
                    'pay_rate' => $pay_rate,
                    'prev_organisation' => $prev_organisation,
                    'bank_name' => $bank_name,
                    'branch_name' => $branch_name,
                    'account_holder' => $account_holder,
                    'account_number' => $account_number,
                    'ifsc_code' => $ifsc_code,
                    'emergency_name' => $emergency_name,
                    'emergency_relationship' => $emergency_relationship,
                    'emergency_contact' => $emergency_contact,
                    'emergency_address' => $emergency_address,

                    'passport_photo' => $s3files,
                    'id_proof' => $s3files1,
                    'address_proof' => $s3files2,
                    'qualification_1' => $s3files3,
                    'qualification_2' => $s3files4,
                    'qualification_3' => $s3files5,
                    'prev_com_joining_letter' => $s3files6,
                    'prev_com_reliving_letter' => $s3files7,
                    'prev_com_experience_letter' => $s3files8,
                    'police_verification' => $s3files9,
                    'tenthS3marksheet' => $s3files10,
                    'certificateS3upload' => $s3files11,

                    'createdBy' => $this->vendorId,
                    'createdDtm' => date('Y-m-d H:i:s')
                );


                $result = $this->hron->addNewHronboard($hronboardInfo);
                //print_r($hronboardInfo);exit;
                if ($result > 0) {



                    if (!empty($franchiseNumberArray)) {
                        foreach ($franchiseNumberArray as $franchiseNumber) {
                            $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                            if (!empty($branchDetail)) {
                                //$to = $branchDetail->branchEmail;
                                $to = $branchDetail->officialEmailID;
                                $subject = "Alert - eduMETA THE i-SCHOOL Assign New Staff Meeting";
                                $message = 'Dear ' . $branchDetail->applicantName . ' ';
                                //$message = ' '.$remark.' ';
                                $message .= 'You have been assigned a new meeting. BY- ' . $this->session->userdata("name") . ' ';
                                $message .= 'Please visit the portal.';
                                //$message = ' '.$remark.' ';
                                $headers = "From: Edumeta  Team<noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com";
                                mail($to, $subject, $message, $headers);

                                // ✅ Notify Admins (roleId = 1, 14)

                            }
                        }
                    }
                    $this->session->set_flashdata('success', 'New HrOnboard created successfully');
                } else {
                    $this->session->set_flashdata('error', 'HrOnboard creation failed');
                }

                redirect('hronboard/hronboardListing');
            }
        }
    }


    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($hronboardId = NULL)
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        if ($hronboardId == null) {
            redirect('hronboard/hronboardListing');
        }

        $data['hronboardInfo'] = $this->hron->getHronboardInfo($hronboardId);
        $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
        $data['departments'] = $this->hron->getAllRoles();

        // ✅ Assign roleId from hronboardInfo
        $data['selectedRoleId'] = $data['hronboardInfo']->roleId;

        $this->global['pageTitle'] = 'Meeting : Edit Staff';

        $this->loadViews("hronboard/edit", $this->global, $data, NULL);
    }
}

    function view($hronboardId = NULL)
    {
        if (!$this->hasUpdateAccess()) {
            $this->loadThis();
        } else {
            if ($hronboardId == null) {
                redirect('hronboard/hronboardListing');
            }

            $data['hronboardInfo'] = $this->hron->getHronboardInfo($hronboardId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'Meeting : Edit Staff';

            $this->loadViews("hronboard/view", $this->global, $data, NULL);
        }
    }
    /**
     * This function is used to edit the user information
     */
   function editHronboard()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $hronboardId = $this->input->post('hronboardId');

        $this->form_validation->set_rules('first_name', 'Name', 'trim|required|max_length[256]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($hronboardId);
        } else {
            $first_name = $this->security->xss_clean($this->input->post('first_name'));
            $last_name = $this->security->xss_clean($this->input->post('last_name'));
            $dob = $this->security->xss_clean($this->input->post('dob'));
            $gender = $this->security->xss_clean($this->input->post('gender'));
            $email = $this->security->xss_clean($this->input->post('email'));
            $contact_number = $this->security->xss_clean($this->input->post('contact_number'));
            $alt_contact_number = $this->security->xss_clean($this->input->post('alt_contact_number'));
            $permanent_address = $this->security->xss_clean($this->input->post('permanent_address'));
            $communication_address = $this->security->xss_clean($this->input->post('communication_address'));
            $city = $this->security->xss_clean($this->input->post('city'));
            $state = $this->security->xss_clean($this->input->post('state'));
            $pincode = $this->security->xss_clean($this->input->post('pincode'));
            $nationality = $this->security->xss_clean($this->input->post('nationality'));
            $languages_known = $this->security->xss_clean($this->input->post('languages_known'));
            $marital_status = $this->security->xss_clean($this->input->post('marital_status'));
            $anniversary_date = $this->security->xss_clean($this->input->post('anniversary_date'));
            $designation = $this->security->xss_clean($this->input->post('designation'));
            $joining_date = $this->security->xss_clean($this->input->post('joining_date'));
            $employment_status = $this->security->xss_clean($this->input->post('employment_status'));
            $pay_rate = $this->security->xss_clean($this->input->post('pay_rate'));
            $bank_name = $this->security->xss_clean($this->input->post('bank_name'));
            $branch_name = $this->security->xss_clean($this->input->post('branch_name'));
            $account_holder = $this->security->xss_clean($this->input->post('account_holder'));
            $account_number = $this->security->xss_clean($this->input->post('account_number'));
            $ifsc_code = $this->security->xss_clean($this->input->post('ifsc_code'));
            $emergency_name = $this->security->xss_clean($this->input->post('emergency_name'));
            $emergency_relationship = $this->security->xss_clean($this->input->post('emergency_relationship'));
            $emergency_contact = $this->security->xss_clean($this->input->post('emergency_contact'));
            $emergency_address = $this->security->xss_clean($this->input->post('emergency_address'));
             $leave_count = $this->security->xss_clean($this->input->post('leave_count'));
$roleId = $this->security->xss_clean($this->input->post('roleId'));

            // Upload Documents Files
            $s3_file_link = [];

// Check if a new file is uploaded
if (isset($_FILES["file"]["tmp_name"]) && !empty($_FILES["file"]["tmp_name"])) {
    $dir = dirname($_FILES["file"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];

    if (rename($_FILES["file"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();

        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link[] = $result_arr['ObjectURL'];
        } else {
            // fallback to existing file if upload fails
            $s3_file_link[] = $this->input->post('existing_file');
        }
    } else {
        // fallback if rename fails
        $s3_file_link[] = $this->input->post('existing_file');
    }
} else {
    // No new file uploaded — use existing
    $s3_file_link[] = $this->input->post('existing_file');
}

$s3files = implode(',', $s3_file_link);


           $s3_file_link1 = [];

// Upload Documents File1       
if (isset($_FILES["file1"]["tmp_name"]) && !empty($_FILES["file1"]["tmp_name"])) {
    $dir = dirname($_FILES["file1"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file1"]["name"];

    if (rename($_FILES["file1"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();

        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link1[] = $result_arr['ObjectURL'];
        } else {
            // fallback to existing file if upload fails
            $s3_file_link1[] = $this->input->post('existing_file1');
        }
    } else {
        // fallback if rename fails
        $s3_file_link1[] = $this->input->post('existing_file1');
    }
} else {
    // No new file uploaded — use existing
    $s3_file_link1[] = $this->input->post('existing_file1');
}

$s3files1 = implode(',', $s3_file_link1);

            // Upload Documents File2        
           // Upload Documents File2 (Retain existing if no new file uploaded)
if (isset($_FILES["file2"]["tmp_name"]) && !empty($_FILES["file2"]["tmp_name"])) {
    $dir = dirname($_FILES["file2"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file2"]["name"];
    if (rename($_FILES["file2"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link2[] = $result_arr['ObjectURL'];
        } else {
            // fallback to existing if S3 upload failed
            $s3_file_link2[] = $this->input->post('existing_file2');
        }
    } else {
        // fallback to existing if rename failed
        $s3_file_link2[] = $this->input->post('existing_file2');
    }
} else {
    // use existing file link if no new file uploaded
    $s3_file_link2[] = $this->input->post('existing_file2');
}
$s3files2 = implode(',', $s3_file_link2);


            // Upload Documents File3 
           // Upload Documents File3 (Retain existing if no new file uploaded)
if (isset($_FILES["file3"]["tmp_name"]) && !empty($_FILES["file3"]["tmp_name"])) {
    $dir = dirname($_FILES["file3"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file3"]["name"];
    if (rename($_FILES["file3"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link3[] = $result_arr['ObjectURL'];
        } else {
            // fallback to existing file if S3 upload fails
            $s3_file_link3[] = $this->input->post('existing_file3');
        }
    } else {
        // fallback to existing file if rename fails
        $s3_file_link3[] = $this->input->post('existing_file3');
    }
} else {
    // use existing file link if no new file uploaded
    $s3_file_link3[] = $this->input->post('existing_file3');
}
$s3files3 = implode(',', $s3_file_link3);

           // Upload Documents File4 (Retain existing if no new file uploaded)
if (isset($_FILES["file4"]["tmp_name"]) && !empty($_FILES["file4"]["tmp_name"])) {
    $dir = dirname($_FILES["file4"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file4"]["name"];
    if (rename($_FILES["file4"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link4[] = $result_arr['ObjectURL'];
        } else {
            $s3_file_link4[] = $this->input->post('existing_file4'); // fallback to existing file
        }
    } else {
        $s3_file_link4[] = $this->input->post('existing_file4'); // fallback to existing file
    }
} else {
    $s3_file_link4[] = $this->input->post('existing_file4'); // use existing file if no new upload
}
$s3files4 = implode(',', $s3_file_link4);


            // Upload Documents File5 
           // Upload Documents File5 (Retain existing if no new file uploaded)
if (isset($_FILES["file5"]["tmp_name"]) && !empty($_FILES["file5"]["tmp_name"])) {
    $dir = dirname($_FILES["file5"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file5"]["name"];
    if (rename($_FILES["file5"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link5[] = $result_arr['ObjectURL'];
        } else {
            $s3_file_link5[] = $this->input->post('existing_file5'); // fallback to existing
        }
    } else {
        $s3_file_link5[] = $this->input->post('existing_file5'); // fallback to existing
    }
} else {
    $s3_file_link5[] = $this->input->post('existing_file5'); // use existing if no upload
}
$s3files5 = implode(',', $s3_file_link5);
 // Upload Documents File6 (Retain existing if no new file uploaded)
if (isset($_FILES["file6"]["tmp_name"]) && !empty($_FILES["file6"]["tmp_name"])) {
    $dir = dirname($_FILES["file6"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file6"]["name"];
    if (rename($_FILES["file6"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link6[] = $result_arr['ObjectURL'];
        } else {
            $s3_file_link6[] = $this->input->post('existing_file6'); // fallback to existing
        }
    } else {
        $s3_file_link6[] = $this->input->post('existing_file6'); // fallback to existing
    }
} else {
    $s3_file_link6[] = $this->input->post('existing_file6'); // use existing if no upload
}
$s3files6 = implode(',', $s3_file_link6);

// Upload Documents File7 (Retain existing if no new file uploaded)
if (isset($_FILES["file7"]["tmp_name"]) && !empty($_FILES["file7"]["tmp_name"])) {
    $dir = dirname($_FILES["file7"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file7"]["name"];
    if (rename($_FILES["file7"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link7[] = $result_arr['ObjectURL'];
        } else {
            $s3_file_link7[] = $this->input->post('existing_file7'); // fallback to existing
        }
    } else {
        $s3_file_link7[] = $this->input->post('existing_file7'); // fallback to existing
    }
} else {
    $s3_file_link7[] = $this->input->post('existing_file7'); // use existing if no upload
}
$s3files7 = implode(',', $s3_file_link7);

// Upload Documents File8 (Retain existing if no new file uploaded)
if (isset($_FILES["file8"]["tmp_name"]) && !empty($_FILES["file8"]["tmp_name"])) {
    $dir = dirname($_FILES["file8"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file8"]["name"];
    if (rename($_FILES["file8"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link8[] = $result_arr['ObjectURL'];
        } else {
            $s3_file_link8[] = $this->input->post('existing_file8'); // fallback to existing
        }
    } else {
        $s3_file_link8[] = $this->input->post('existing_file8'); // fallback to existing
    }
} else {
    $s3_file_link8[] = $this->input->post('existing_file8'); // use existing if no upload
}
$s3files8 = implode(',', $s3_file_link8);
// Upload Documents File6 (Retain existing if no new file uploaded)
if (isset($_FILES["file9"]["tmp_name"]) && !empty($_FILES["file9"]["tmp_name"])) {
    $dir = dirname($_FILES["file9"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file9"]["name"];
    if (rename($_FILES["file6"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link9[] = $result_arr['ObjectURL'];
        } else {
            $s3_file_link9[] = $this->input->post('existing_file9'); // fallback to existing
        }
    } else {
        $s3_file_link9[] = $this->input->post('existing_file9'); // fallback to existing
    }
} else {
    $s3_file_link9[] = $this->input->post('existing_file9'); // use existing if no upload
}
$s3files9 = implode(',', $s3_file_link9);
            // Upload Documents File10 (10th Marksheet)
           // Upload Documents File10 (Retain existing if no new file uploaded)
if (isset($_FILES["file10"]["tmp_name"]) && !empty($_FILES["file10"]["tmp_name"])) {
    $dir = dirname($_FILES["file10"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file10"]["name"];
    if (rename($_FILES["file10"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link10[] = $result_arr['ObjectURL'];
        } else {
            $s3_file_link10[] = $this->input->post('existing_file10'); // fallback to existing
        }
    } else {
        $s3_file_link10[] = $this->input->post('existing_file10'); // fallback to existing
    }
} else {
    $s3_file_link10[] = $this->input->post('existing_file10'); // use existing if no upload
}
$s3files10 = implode(',', $s3_file_link10);


           // Upload Documents File11 (Certificate Upload - retain existing if no new file uploaded)
if (isset($_FILES["file11"]["tmp_name"]) && !empty($_FILES["file11"]["tmp_name"])) {
    $dir = dirname($_FILES["file11"]["tmp_name"]);
    $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file11"]["name"];
    if (rename($_FILES["file11"]["tmp_name"], $destination)) {
        $storeFolder = 'attachements';
        $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
        $result_arr = $s3Result->toArray();
        if (isset($result_arr['ObjectURL']) && !empty($result_arr['ObjectURL'])) {
            $s3_file_link11[] = $result_arr['ObjectURL'];
        } else {
            $s3_file_link11[] = $this->input->post('existing_file11'); // fallback to existing
        }
    } else {
        $s3_file_link11[] = $this->input->post('existing_file11'); // fallback to existing
    }
} else {
    $s3_file_link11[] = $this->input->post('existing_file11'); // use existing if no upload
}
$s3files11 = implode(',', $s3_file_link11);


            // Insert data into staffInfo array
            $hronboardInfo = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'dob' => $dob,
                'gender' => $gender,
                'email' => $email,
                'contact_number' => $contact_number,
                'alt_contact_number' => $alt_contact_number,
                'permanent_address' => $permanent_address,
                'communication_address' => $communication_address,
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode,
                'nationality' => $nationality,
                'languages_known' => $languages_known,
                'marital_status' => $marital_status,
                'anniversary_date' => $anniversary_date,
                'designation' => $designation,
                'joining_date' => $joining_date,
                'employment_status' => $employment_status,
                'pay_rate' => $pay_rate,
                'bank_name' => $bank_name,
                'branch_name' => $branch_name,
                'account_holder' => $account_holder,
                'account_number' => $account_number,
                'ifsc_code' => $ifsc_code,
                'emergency_name' => $emergency_name,
                'emergency_relationship' => $emergency_relationship,
                'emergency_contact' => $emergency_contact,
                'emergency_address' => $emergency_address,
                'passport_photo' => $s3files,
                'id_proof' => $s3files1,
                'address_proof' => $s3files2,
                'qualification_1' => $s3files3,
                'qualification_2' => $s3files4,
                'qualification_3' => $s3files5,
                'tenthS3marksheet' => $s3files10,
                'certificateS3upload' => $s3files11,
                'leave_count'=> $leave_count,
                'roleId'=>$roleId,
                'createdBy' => $this->vendorId,
                'createdDtm' => date('Y-m-d H:i:s')
            );

            $result = $this->hron->editHronboard($hronboardInfo, $hronboardId);

            if ($result == true) {
                // Email notification
                $franchiseNumberArray = [$this->hron->getFranchiseNumberByUserId($hronboardId)]; // Assuming you have a method to get franchise number by hronboardId
                if (!empty($franchiseNumberArray)) {
                    foreach ($franchiseNumberArray as $franchiseNumber) {
                        $branchDetail = $this->bm->getBranchesInfoByfranchiseNumber($franchiseNumber);
                        if (!empty($branchDetail)) {
                            $to = $branchDetail->officialEmailID;
                            $subject = "Alert - eduMETA THE i-SCHOOL Staff Information Updated";
                            $message = 'Dear ' . $branchDetail->applicantName . ', ';
                            $message .= 'Staff information has been updated. BY- ' . $this->session->userdata("name") . ' ';
                            $message .= 'Please visit the portal for details.';
                            $headers = "From: Edumeta Team <noreply@theischool.com>" . "\r\n" . "BCC: dev.edumeta@gmail.com,sourabh.edumeta@gmail.com";
                            mail($to, $subject, $message, $headers);
                        }
                    }
                }

                $this->session->set_flashdata('success', 'Updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Updation failed');
            }

            redirect('hronboard/hronboardListing');
        }
    }
}


    /** Code for CK editor */
    public function upload()
    {
        if (isset($_FILES['upload'])) {
            $file = $_FILES['upload'];
            $fileName = time() . '_' . $file['name'];
            $uploadPath = 'uploads/';

            if (move_uploaded_file($file['tmp_name'], $uploadPath . $fileName)) {
                $url = base_url($uploadPath . $fileName);
                $message = 'Image uploaded successfully';

                $callback = $_GET['CKEditorFuncNum'];
                echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($callback, '$url', '$message');</script>";
            } else {
                $message = 'Error while uploading file';
                echo "<script type='text/javascript'>alert('$message');</script>";
            }
        }
    }

    public function fetchAssignedUsers()
    {
        $franchiseNumber = $this->input->post('franchiseNumber');

        // Fetch the users based on the franchise number
        $users = $this->stf->getUsersByFranchise($franchiseNumber); // Adjust model method name if necessary

        // Generate HTML options for the response
        $options = '<option value="0">Select Role</option>';
        foreach ($users as $user) {
            $options .= '<option value="' . $user->userId . '">' . $user->name . '</option>';
        }

        echo $options; // Output the options as HTML
    }
    public function download_s3_file()
    {
        $fileUrl = $this->input->get('url'); // Get file URL from query string

        if ($fileUrl) {
            // Set headers to force download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($fileUrl) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            readfile($fileUrl); // Read from S3 URL directly
            exit;
        } else {
            echo "Invalid download URL.";
        }
    }
}
