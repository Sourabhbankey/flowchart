<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Investors (InvestorsController)
 * Investors Class to control Investors related operations.
 * @author : Ashish 
 * @version : 1.5
 * @since : 18 Mar 2024
 */
class Investors extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Investors_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Investors';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('investors/investorsListing');
    }
    
    /**
     * This function is used to load the investors list
     */
    function investorsListing()
    {
        if(!$this->hasListAccess())
        {
            $this->loadThis();
        }
        else
        {
            $searchText = '';
            if(!empty($this->input->post('searchText'))) {
                $searchText = $this->security->xss_clean($this->input->post('searchText'));
            }
            $data['searchText'] = $searchText;
            
            $this->load->library('pagination');
            
            $count = $this->bm->investorsListingCount($searchText);

			$returns = $this->paginationCompress ( "investorsListing/", $count, 10 );
            
            $data['records'] = $this->bm->investorsListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Investors';
            
            $this->loadViews("investors/list", $this->global, $data, NULL);
        }
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Investors';

            $this->loadViews("investors/add", $this->global, NULL, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewInvestors()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('investorsName','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $investorsName = $this->security->xss_clean($this->input->post('investorsName'));
                $description = $this->security->xss_clean($this->input->post('description'));
                
                $investorsInfo = array('investorsName'=>$investorsName, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->bm->addNewInvestors($investorsInfo);
                
                if($result > 0)
                {
                    // Send email notification
                    $to = 'dev.edumeta@gmail.com'; // Replace with actual admin email or fetch dynamically
                    $subject = "New Investor Added - eduMETA THE i-SCHOOL";
                    $message = "Dear Admin,<br><br>";
                    $message .= "A new investor has been added by {$this->session->userdata('name')}.<br>";
                    $message .= "<strong>Investor Details:</strong><br>";
                    $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                    $headers = "From: eduMETA Team <noreply@theischool.com>\r\n";
                    $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if (!mail($to, $subject, $message, $headers)) {
                        log_message('error', 'Failed to send email to ' . $to);
                    }

                    $this->session->set_flashdata('success', 'New Investor created successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Investor creation failed');
                }
                
                redirect('investors/investorsListing');
            }
        }
    }


    function view($investorsId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($investorsId == null)
            {
                redirect('investors/investorsListing');
            }
            
            $data['investorsInfo'] = $this->bm->getInvestorsInfo($investorsId);

            $this->global['pageTitle'] = 'CodeInsect : View Investors';
            
            $this->loadViews("investors/view", $this->global, $data, NULL);
        }
    }

    
    /**
     * This function is used load investors edit information
     * @param number $investorsId : Optional : This is investors id
     */
    function edit($investorsId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($investorsId == null)
            {
                redirect('investors/investorsListing');
            }
            
            $data['investorsInfo'] = $this->bm->getInvestorsInfo($investorsId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Investors';
            
            $this->loadViews("investors/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
   function editInvestors()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $investorsId = $this->input->post('investorsId');
            
            $this->form_validation->set_rules('investorsName','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($investorsId);
            }
            else
            {
                $investorsName = $this->security->xss_clean($this->input->post('investorsName'));
                $description = $this->security->xss_clean($this->input->post('description'));
                
                $investorsInfo = array('investorsName'=>$investorsName, 'description'=>$description, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->bm->editInvestors($investorsInfo, $investorsId);
                
                if($result == true)
                {
                    // Send email notification
                    $to = 'dev.edumeta@gmail.com'; // Replace with actual admin email or fetch dynamically
                    $subject = "Investor Details Updated - eduMETA THE i-SCHOOL";
                    $message = "Dear Admin,<br><br>";
                    $message .= "The investor details have been updated by {$this->session->userdata('name')}.<br>";
                    $message .= "<strong>Investor Details:</strong><br>";
                    $message .= "Investor Name: {$investorsInfo['investorsName']}<br>";
                    $message .= "Description: {$investorsInfo['description']}<br>";
                    $message .= "Please visit the portal for more details.<br><br>";
                    $message .= "Best regards,<br>eduMETA THE i-SCHOOL Team";

                    $headers = "From: eduMETA Team <noreply@theischool.com>\r\n";
                    $headers .= "Bcc: dev.edumeta@gmail.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    if (!mail($to, $subject, $message, $headers)) {
                        log_message('error', 'Failed to send email to ' . $to);
                    }

                    $this->session->set_flashdata('success', 'Investor updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Investor update failed');
                }
                
                redirect('investors/investorsListing');
            }
        }
    }
}

?>