<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Brholiday (InvestorsController)
 * Brholiday Class to control Brholiday related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 20 Mar 2025
 */
class Brholiday extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Brholiday_model', 'brhd');
        $this->isLoggedIn();
        $this->module = 'Brholiday';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('brholiday/brholidayListing');
    }
    
    /**
     * This function is used to load the brholiday list
     */
    function brholidayListing()
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
            
            $count = $this->brhd->brholidayListingCount($searchText);

            $returns = $this->paginationCompress ( "brholidayListing/", $count, 10 );
            
            $data['records'] = $this->brhd->brholidayListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Holiday';
            
            $this->loadViews("brholiday/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Holiday';

            $this->loadViews("brholiday/add", $this->global, NULL, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewBrholiday()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('holidayTitle','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $holidayTitle = $this->security->xss_clean($this->input->post('holidayTitle'));
                $holidayDay = $this->security->xss_clean($this->input->post('holidayDay'));
                $holidayFromdate = $this->security->xss_clean($this->input->post('holidayFromdate'));
                $holidayTodate = $this->security->xss_clean($this->input->post('holidayTodate'));
                $description = $this->security->xss_clean($this->input->post('description'));
                
                $brholidayInfo = array('holidayTitle'=>$holidayTitle, 'holidayDay'=>$holidayDay, 'holidayFromdate'=>$holidayFromdate, 'holidayTodate'=>$holidayTodate, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->brhd->addNewBrholiday($brholidayInfo);
                
                if($result > 0) {
                    $this->session->set_flashdata('success', 'New Holiday created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Holiday creation failed');
                }
                
                redirect('brholiday/brholidayListing');
            }
        }
    }
    function view($holidayId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($holidayId == null)
            {
                redirect('brholiday/brholidayListing');
            }
            
            $data['brholidayInfo'] = $this->brhd->getBrholidayInfo($holidayId);

            $this->global['pageTitle'] = 'CodeInsect : View Holiday';
            
            $this->loadViews("brholiday/view", $this->global, $data, NULL);
        }
    }

    
    /**
     * This function is used load brholiday edit information
     * @param number $holidayId : Optional : This is brholiday id
     */
    function edit($holidayId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($holidayId == null)
            {
                redirect('brholiday/brholidayListing');
            }
            
            $data['brholidayInfo'] = $this->brhd->getBrholidayInfo($holidayId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Holiday';
            
            $this->loadViews("brholiday/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editBrholiday()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $holidayId = $this->input->post('holidayId');
            
            $this->form_validation->set_rules('holidayTitle','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($holidayId);
            }
            else
            {

                $holidayTitle = $this->security->xss_clean($this->input->post('holidayTitle'));
                $holidayDay = $this->security->xss_clean($this->input->post('holidayDay'));
                $holidayFromdate = $this->security->xss_clean($this->input->post('holidayFromdate'));
                $holidayTodate = $this->security->xss_clean($this->input->post('holidayTodate'));
                $description = $this->security->xss_clean($this->input->post('description'));
                
                $brholidayInfo = array('holidayTitle'=>$holidayTitle, 'holidayDay'=>$holidayDay, 'holidayFromdate'=>$holidayFromdate, 'holidayTodate'=>$holidayTodate, 'description'=>$description, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->brhd->editBrholiday($brholidayInfo, $holidayId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Holiday updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Holiday updation failed');
                }
                
                redirect('brholiday/brholidayListing');
            }
        }
    }
    public function holiday()
{
    
    $this->global['pageTitle'] = 'Holiday List';
    $this->loadViews("brholiday/holiday", $this->global, NULL, NULL);
}
}

?>