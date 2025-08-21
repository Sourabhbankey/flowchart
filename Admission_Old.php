<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Despatch (DespatchController)
 * Despatch Class to control task related operations.
 * @author : Ashish
 * @version : 1.0
 * @since : 08 Jun 2023
  */
class Admission extends BaseController
{
    /**
     * This is default constructor of the class
     */

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admission_model', 'adm');
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Admission';
        //$this->global ['pageTitle'] = 'CodeInsect : Access Denied';
       
       //$this->load->view ( 'includes/header', $this->global );
        //$this->load->view ( 'general/access' );
        //$this->load->view ( 'includes/footer' );
        /*$this->load->view('includes/header');
        $this->load->view('includes/footer');*/
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {

        //redirect('admission/admissionListing');
        $this->load->view('admission/list');
    }
 
}

?>