<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Installationexpenses (InstallationexpensesController)
 * Installationexpenses Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 27 Jun 2025
 */
class Installationexpenses extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Installationexpenses_model', 'expins');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
		$this->load->library('pagination');
        $this->module = 'Installationexpenses';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('installationexpenses/installationexpensesListing');
    }
    
        function installationexpensesListing(){
        $userId = $this->session->userdata('userId');
            $userRole = $this->session->userdata('role');

            // Get filter input
            //$franchiseFilter = $this->input->get('franchiseNumber');
        $franchiseFilter = $this->input->get('franchiseNumber');
            if ($this->input->get('resetFilter') == '1') {
                $franchiseFilter = '';
            }
            $config = array();
            $config["base_url"] = base_url() . "installationexpenses/installationexpensesListing";
            $config["per_page"] = 10;
            $config["uri_segment"] = 3;

            if ($userRole == '14' || $userRole == '1' || $userRole == '15') {
                $config["total_rows"] = $this->expins->get_count($franchiseFilter);
            } else {
                $franchiseNumber = $this->expins->getFranchiseNumberByUserId($userId);
                if ($franchiseNumber) {
                    $config["total_rows"] = $this->expins->get_count_by_franchise($franchiseNumber, $franchiseFilter);
                } else {
                    $config["total_rows"] = 0;
                }
            }

            $this->pagination->initialize($config);
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

            if ($userRole == '14' || $userRole == '1' || $userRole == '15') {
                $data["records"] = $this->expins->get_data($config["per_page"], $page, $franchiseFilter);
            } else {
                if ($franchiseNumber) {
                    $data["records"] = $this->expins->get_data_by_franchise($franchiseNumber, $config["per_page"], $page, $franchiseFilter);
                } else {
                    $data["records"] = [];
                }
            }

            $data["links"] = $this->pagination->create_links();
            $data["start"] = $page + 1;
            $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
            $data["total_records"] = $config["total_rows"];
            $data["franchiseFilter"] = $franchiseFilter; // Pass the filter value to the view
          $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("installationexpenses/list", $this->global, $data, NULL);
        }
    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Add New Installationexpenses';
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            $this->loadViews("installationexpenses/add", $this->global, $data, NULL);
        }
    }

public function addNewInstallationexpenses()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
        return;
    }

    $this->load->library('form_validation');
    $this->form_validation->set_rules('schinsTitle', 'Installation Title', 'trim|required|max_length[255]');

    if ($this->form_validation->run() === FALSE) {
        $this->add(); // Reload form on error
    } else {
        // Clean form inputs
        $schinsTitle        = $this->security->xss_clean($this->input->post('schinsTitle'));
        $instaCity          = $this->security->xss_clean($this->input->post('instaCity'));
        $schDateInstall     = $this->security->xss_clean($this->input->post('schDateInstall'));
        $schinsAddress      = $this->security->xss_clean($this->input->post('schinsAddress'));
        $installTL          = $this->security->xss_clean($this->input->post('installTL'));
        $contactTL          = $this->security->xss_clean($this->input->post('contactTL'));
        $installTeam        = $this->security->xss_clean($this->input->post('installTeam'));
        $franchiseNumberArr = $this->security->xss_clean($this->input->post('franchiseNumber'));
        $franchiseNumbers   = implode(',', (array)$franchiseNumberArr);

        // Handle dynamic expenses
        $sr_nos  = $this->input->post('expSrnum');
        $titles  = $this->input->post('expTitle');
        $amounts = $this->input->post('expAmt');
        $dates   = $this->input->post('expDate');
        $files   = $_FILES['expUploadfile'];

        $expenses = [];

        foreach ($files['name'] as $i => $originalName) {
            $attachmentUrl = '';
            if (!empty($originalName)) {
                $tmpFilePath = $files['tmp_name'][$i];

                // Clean filename
                $cleanName = preg_replace('/\s+/', '_', basename($originalName));
                $filename = time() . '_' . $cleanName;
                $s3Key = 'expenses/' . $filename;

                // ✅ Fix: use tmpFilePath instead of originalName
                $uploaded = $this->s3_upload->upload_file($tmpFilePath, $s3Key);

                if ($uploaded) {
                    $attachmentUrl = 'https://support-smsfiles.s3.ap-south-1.amazonaws.com/' . $s3Key;
                }
            }

            $expenses[] = [
                'sr_no'     => isset($sr_nos[$i]) ? $sr_nos[$i] : 0,
                'title'     => isset($titles[$i]) ? $titles[$i] : '',
                'amount'    => isset($amounts[$i]) ? $amounts[$i] : '',
                'date'      => isset($dates[$i]) ? $dates[$i] : '',
                'attachment'=> $attachmentUrl
            ];
        }

        // Handle general file uploads
        $s3_file_link = [];

        if (!empty($_FILES['files']['name'][0])) {
            foreach ($_FILES['files']['name'] as $key => $name) {
                $tmpName = $_FILES['files']['tmp_name'][$key];

                // Clean filename
                $filename = time() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
                $s3Key = 'expenses/' . $filename;

                // Upload to S3
                $this->s3_upload->upload_file($tmpName, $s3Key);

                // Build public URL
                $s3_file_link[] = 'https://support-smsfiles.s3.ap-south-1.amazonaws.com/' . $s3Key;
            }
        }

        $s3files = implode(',', $s3_file_link);

        // Prepare data for DB insert
        $data = [
            'schinsTitle'        => $schinsTitle,
            'instaCity'          => $instaCity,
            'schDateInstall'     => $schDateInstall,
            'schinsAddress'      => $schinsAddress,
            'installTL'          => $installTL,
            'contactTL'          => $contactTL,
            'installTeam'        => $installTeam,
            'franchiseNumber'    => $franchiseNumbers,
            'exp1S3attachment'   => $s3files,
            'expenses_json'      => json_encode($expenses),
            'createdBy'          => $this->vendorId,
            'createdDtm'         => date('Y-m-d H:i:s'),
            'isDeleted'          => 0
        ];

        $result = $this->expins->insertInstallationExpense($data);

        if ($result) {
            $this->session->set_flashdata('success', 'Installation expense added successfully!');
            redirect('installationexpenses/installationexpensesListing');
        } else {
            $this->session->set_flashdata('error', 'Failed to add installation expense.');
            $this->add();
        }
    }
}



    
    /**
     * This function is used load task edit information
     * @param number $taskId : Optional : This is task id
     */
    function edit($installationexpensesId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($installationexpensesId == null)
            {
                redirect('installationexpenses/installationexpensesListing');
            }
            
            $data['installationexpensesInfo'] = $this->expins->getInstallationexpensesInfo($installationexpensesId);
            $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
            //$data['users'] = $this->tm->getUser();
            $this->global['pageTitle'] = 'CodeInsect : Edit Installationexpenses';
            
            $this->loadViews("installationexpenses/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */

public function editInstallationexpenses()
{
    if (!$this->hasUpdateAccess()) {
        $this->loadThis();
        return;
    }

    $this->load->library('form_validation');

    $expId = $this->input->post('expId'); // <-- CHANGED HERE

    $this->form_validation->set_rules('schinsTitle', 'Installation Title', 'trim|required|max_length[256]');

    if ($this->form_validation->run() == FALSE) {
        $this->edit($expId); // <-- CHANGED HERE
    } else {
        // Clean form inputs
        $schinsTitle     = $this->security->xss_clean($this->input->post('schinsTitle'));
        $instaCity       = $this->security->xss_clean($this->input->post('instaCity'));
        $schDateInstall  = $this->security->xss_clean($this->input->post('schDateInstall'));
        $schinsAddress   = $this->security->xss_clean($this->input->post('schinsAddress'));
        $installTL       = $this->security->xss_clean($this->input->post('installTL'));
        $contactTL       = $this->security->xss_clean($this->input->post('contactTL'));
        $installTeam     = $this->security->xss_clean($this->input->post('installTeam'));

        $franchiseNumberArray = $this->security->xss_clean($this->input->post('franchiseNumber'));
        $franchiseNumbers = implode(',', (array)$franchiseNumberArray);

        // Expense Inputs
        $sr_nos        = $this->input->post('expSrnum');
        $titles        = $this->input->post('expTitle');
        $amounts = $this->input->post('expAmt');
        $dates         = $this->input->post('expDate');
        $existingFiles = $this->input->post('existingFiles'); // hidden inputs for old attachments
        $files         = $_FILES['expUploadfile'];

        $expenses = [];

        foreach ($sr_nos as $i => $sr_no) {
            $attachmentUrl = '';

            if (!empty($files['name'][$i])) {
                $tmpFilePath = $files['tmp_name'][$i];
                $filename = time() . '_' . basename($files['name'][$i]);
                $s3Key = 'expenses/' . $filename;

                $s3Upload = $this->s3_upload->upload_file($tmpFilePath, $s3Key);
                if ($s3Upload) {
                    $attachmentUrl = 'https://support-smsfiles.s3.ap-south-1.amazonaws.com/' . $s3Key;
                }
            } elseif (!empty($existingFiles[$i])) {
                $attachmentUrl = $existingFiles[$i];
            }

            $expenses[] = [
                'sr_no'      => $sr_no ?? 0,
                'title'      => $titles[$i] ?? '',
                'amount'    => $amounts[$i] ?? '',
                'date'       => $dates[$i] ?? '',
                'attachment' => $attachmentUrl
            ];
        }

        // Update array
        $updateData = [
            'schinsTitle'      => $schinsTitle,
            'instaCity'        => $instaCity,
            'schDateInstall'   => $schDateInstall,
            'schinsAddress'    => $schinsAddress,
            'installTL'        => $installTL,
            'contactTL'        => $contactTL,
            'installTeam'      => $installTeam,
            'franchiseNumber'  => $franchiseNumbers,
            'expenses_json'    => json_encode($expenses),
            'updatedBy'        => $this->vendorId,
            'updatedDtm'       => date('Y-m-d H:i:s')
        ];

        $result = $this->expins->editInstallationexpenses($updateData, $expId); // <-- CHANGED HERE

        if ($result) {
            $this->session->set_flashdata('success', 'Installation expense updated successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update installation expense.');
        }

        redirect('installationexpenses/installationexpensesListing');
    }
}


   public function getBranchDetails()
    {
        $franchiseNumber = $this->input->post('franchiseNumber');
        if ($franchiseNumber) {
          //  $this->load->model('BranchModel'); // Replace with your model name
            $branchDetails = $this->expins->getBranchByFranchiseNumber($franchiseNumber);

            if (!empty($branchDetails)) {
                echo json_encode($branchDetails);
            } else {
                echo json_encode([]);
            }
        } else {
            echo json_encode([]);
        }
    }
/*--Expenses-Section--*/
public function addExpenseRows()
{
    $this->load->model('Installationexpenses_model', 'expins'); // already loaded
    $this->load->helper(['form', 'url']);
    $this->load->library('upload');

    // Inputs from the dynamic form
    $sr_nums = $this->input->post('expSrnum');
    $titles  = $this->input->post('expTitle');
    $amounts  = $this->input->post('expAmt');
    $dates   = $this->input->post('expDate');
    $files   = $_FILES['expUploadfile'];

    $tempFilePath = $attachments['tmp_name'][$i];
    $filename = time() . '_' . $attachments['name'][$i];
    $s3_folder = 'expenses';

    $uploadResult = $this->s3_upload->upload_file($tempFilePath, $s3_folder . '/' . $filename);
    $uploadArray = $uploadResult->toArray();

    if (!empty($uploadArray['ObjectURL'])) {
        $filename = $uploadArray['ObjectURL']; // use S3 URL as filename
    } else {
        $filename = null;
    }


    foreach ($sr_nums as $index => $sr_no) {
        $filename = null;

        // Handle file upload if exists
        if (!empty($files['name'][$index])) {
            $_FILES['file']['name']     = $files['name'][$index];
            $_FILES['file']['type']     = $files['type'][$index];
            $_FILES['file']['tmp_name'] = $files['tmp_name'][$index];
            $_FILES['file']['error']    = $files['error'][$index];
            $_FILES['file']['size']     = $files['size'][$index];

            $config['upload_path']   = $upload_path;
            $config['allowed_types'] = '*';
            $config['file_name']     = time() . '_' . $files['name'][$index];

            $this->upload->initialize($config);

            if ($this->upload->do_upload('file')) {
                $upload_data = $this->upload->data();
                $filename = $upload_data['file_name'];
            }
        }

        // Prepare data for DB
        $expenseData = [
            'sr_no'     => $sr_no,
            'title'     => $titles[$index],
            'amount'    => $amounts[$index],
            'date'      => $dates[$index],
            'attachment'=> $filename,
            'created_by'=> $this->vendorId,
            'created_at'=> date('Y-m-d H:i:s')
        ];

        // Save using model
        $this->expins->insertExpense($expenseData);
    }

    $this->session->set_flashdata('success', 'Expenses added successfully.');
    redirect('installationexpenses/add'); // or wherever you want to redirect
}

/*--End-Expenses-Section--*/
/*Save-EXP*/
public function saveExpenses()
{
    $expId = $this->input->post('expId');
    $franchiseNumber = $this->input->post('franchiseNumber');

    $sr_nos = $this->input->post('sr_no');
    $titles = $this->input->post('title');
    $amounts = $this->input->post('amount');
    $dates  = $this->input->post('date');
    $attachments = $_FILES['attachment'];

    $this->load->model('Installationexpenses_model');

    foreach ($sr_nos as $i => $sr_no) {
        $filename = null;

        // Handle attachment upload
        $tempFilePath = $attachments['tmp_name'][$i];
        $filename = time() . '_' . $attachments['name'][$i];
        $s3_folder = 'expenses';

        $uploadResult = $this->s3_upload->upload_file($tempFilePath, $s3_folder . '/' . $filename);
        $uploadArray = $uploadResult->toArray();

        if (!empty($uploadArray['ObjectURL'])) {
            $filename = $uploadArray['ObjectURL']; // use S3 URL as filename
        } else {
            $filename = null;
        }


        $expenseData = array(
            'expId'       => $expId,
            'franchiseNumber'  => $franchiseNumber,
            'sr_no'            => $sr_no,
            'title'            => $titles[$i],
            'amount'            => $amounts[$i],
            'date'             => $dates[$i],
            'attachment'       => $filename,
            'created_by'       => $this->session->userdata('userId'),
            'created_at'       => date('Y-m-d H:i:s')
        );

        $this->Installationexpenses_model->insertExpense($expenseData);
    }

    // Redirect or return JSON response
    redirect('installationexpenses/details/'.$expId);
}

/*End-Save-EXP*/

}

?>