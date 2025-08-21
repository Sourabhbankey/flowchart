<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : BranchAnniversary (Branch Anniversary Controller)
 * Controller class to handle branch anniversary related operations
 * @author : Your Name
 * @version : 1.0
 * @since : 13 June 2025
 */
class BranchAnniversary extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Branchanniversary_model');
        $this->load->library('session');
        $this->isLoggedIn();
        $this->module = 'BranchAnniversary';
    }

    /**
     * This is default routing method
     * It routes to the anniversary listing page
     */
    public function index()
    {
        redirect('branchanniversary/anniversaryListing');
    }

    /**
     * This function is used to load the branch anniversary list
     */
    public function anniversaryListing()
    {
        $user_role = $this->session->userdata('role');
        $allowed_roles = ['admin', 'manager']; // Adjust roles as needed

        if (!in_array($user_role, $allowed_roles)) { 
            $this->loadViews('branchanniversary/anniversarylisting', $this->global,NULL);
            return;
        }
        // Fetch branch anniversaries
        $data['rows'] = $this->Branchanniversary_model->getBranchAnniversaries();
        $this->loadViews('branchanniversary/anniversarylisting', $this->global, $data, NULL);
    }


    public function branchowneranniversaryListing()
    {
        $user_role = $this->session->userdata('role');
        $allowed_roles = ['admin', 'manager']; // Adjust roles as needed

        if (!in_array($user_role, $allowed_roles)) { 
            $this->loadViews('branchanniversary/branchowneranniversarylisting', $this->global, NULL);
            return;
        }
        // Fetch branch anniversaries for the upcoming month
        $data['rows'] = $this->Branchanniversary_model->branchowneranniversarylisting();
        $this->loadViews('branchanniversary/branchowneranniversarylisting', $this->global, $data, NULL);
    }


      public function edumetaworkanniversaryListing()
    {
        $user_role = $this->session->userdata('role');
        $allowed_roles = ['admin', 'manager']; // Adjust roles as needed

        if (!in_array($user_role, $allowed_roles)) { 
            $this->loadViews('branchanniversary/edumetaworkanniversarylisting', $this->global, NULL);
            return;
        }
        // Fetch branch anniversaries for the upcoming month
        $data['rows'] = $this->Branchanniversary_model->edumetaworkanniversaryListing();
        $this->loadViews('branchanniversary/edumetaworkanniversarylisting', $this->global, $data, NULL);
    }


       public function branchempbirthListing()
    {
        $user_role = $this->session->userdata('role');
        $allowed_roles = ['admin', 'manager']; // Adjust roles as needed

        if (!in_array($user_role, $allowed_roles)) { 
            $this->loadViews('branchanniversary/branchempbirthlisting', $this->global, NULL);
            return;
        }
        // Fetch branch anniversaries for the upcoming month
        $data['rows'] = $this->Branchanniversary_model->branchempbirthListing();
        $this->loadViews('branchanniversary/branchempbirthlisting', $this->global, $data, NULL);
    }


     public function studentbirthdayListing()
    {
        $user_role = $this->session->userdata('role');
        $allowed_roles = ['admin', 'manager']; // Adjust roles as needed

        if (!in_array($user_role, $allowed_roles)) { 
            $this->loadViews('branchanniversary/studentbirthdaylisting', $this->global, NULL);
            return;
        }
        // Fetch branch anniversaries for the upcoming month
        $data['rows'] = $this->Branchanniversary_model->studentbirthdayListing();
        $this->loadViews('branchanniversary/studentbirthdaylisting', $this->global, $data, NULL);
    }

      public function edumetamarriageanniversaryListing()
    {
        $user_role = $this->session->userdata('role');
        $allowed_roles = ['admin', 'manager']; // Adjust roles as needed

        if (!in_array($user_role, $allowed_roles)) { 
            $this->loadViews('branchanniversary/edumetamarriageanniversarylisting', $this->global, NULL);
            return;
        }
        // Fetch branch anniversaries for the upcoming month
        $data['rows'] = $this->Branchanniversary_model->edumetamarriageanniversaryListing();
        $this->loadViews('branchanniversary/edumetamarriageanniversarylisting', $this->global, $data, NULL);
    }


      public function empbirthlist()
    {
        $user_role = $this->session->userdata('role');
        $allowed_roles = ['admin', 'manager']; // Adjust roles as needed

        if (!in_array($user_role, $allowed_roles)) { 
            $this->loadViews('branchanniversary/empbirthlist', $this->global, NULL);
            return;
        }
        // Fetch branch anniversaries for the upcoming month
        $data['rows'] = $this->Branchanniversary_model->empbirthlist();
        $this->loadViews('branchanniversary/empbirthlist', $this->global, $data, NULL);
    }
}


    