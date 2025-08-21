<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Productlist (ProductlistController)
 * Productlist Class to control Productlist related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Productlist extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Productlist_model', 'prodlist');
        $this->isLoggedIn();
        $this->module = 'Productlist';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('productlist/productlistListing');
    }
    
    /**
     * This function is used to load the productlist list
     */
   /* function productlistListing()
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
            
            $count = $this->prodlist->productlistListingCount($searchText);

			$returns = $this->paginationCompress ( "productlistListing/", $count, 500 );
            
            $data['records'] = $this->prodlist->productlistListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Productlist';
            
            $this->loadViews("productlist/list", $this->global, $data, NULL);
        }
    }*/
public function productlistListing()
{
    if (!$this->hasListAccess()) {
        $this->loadThis();
    } else {
        $searchText = '';
        if (!empty($this->input->post('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
        }
        $data['searchText'] = $searchText;

        // Load pagination library
        $this->load->library('pagination');

        // Get the total count of records
        $totalRecords = $this->prodlist->productlistListingCount($searchText);

        // Set pagination configuration
        $config = array();
        $config['base_url'] = base_url() . 'productlist/productlistListing/';
        $config['total_rows'] = $totalRecords;
        $config['per_page'] = 10; // Number of records per page
        $config['uri_segment'] = 3; // Segment of URL that contains the page number
        
       

        // Initialize pagination
        $this->pagination->initialize($config);

        // Get the current page number
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        // Fetch records for the current page
        $records = $this->prodlist->productlistListing($searchText, $config['per_page'], $page);

        // Calculate start and end records for the current page
        $startRecord = $page + 1;
        $endRecord = min($page + $config['per_page'], $totalRecords);

        // Assign data to pass to the view
         $serial_no = $page + 1;
        $data['records'] = $records;
        $data['totalRecords'] = $totalRecords;
        $data['startRecord'] = $startRecord;
        $data['endRecord'] = $endRecord;
$data["serial_no"] = $serial_no;
        // Pass pagination links to view
        $data['pagination'] = $this->pagination->create_links();

        $this->global['pageTitle'] = 'CodeInsect : Productlist';
        
        // Load the view
        $this->loadViews("productlist/list", $this->global, $data, NULL);
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
             $lastProductCode = $this->prodlist->getLastProductCode();
               $lastProductCode = is_numeric($lastProductCode) ? (int)$lastProductCode : 0;
                $nextProductCode = $lastProductCode + 1;
             $data['nextProductCode'] = $nextProductCode;
            $this->global['pageTitle'] = 'CodeInsect : Add New Productlist';

            $this->loadViews("productlist/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
	 //code commented by yashi
    // function addNewProductlist()
    // {
        // if(!$this->hasCreateAccess())
        // {
            // $this->loadThis();
        // }
        // else
        // {
            // $this->load->library('form_validation');
            
            // $this->form_validation->set_rules('productName','Title','trim|required|max_length[50]');
            // $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            // if($this->form_validation->run() == FALSE)
            // {
                // $this->add();
            // }
            // else
            // {
                // $productCode = $this->security->xss_clean($this->input->post('productCode'));
                // $productName = $this->security->xss_clean($this->input->post('productName'));
                // $description = $this->security->xss_clean($this->input->post('description'));
                
                // $productlistInfo = array('productCode'=>$productCode, 'productName'=>$productName, 'description'=>$description, 'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                // $result = $this->prodlist->addNewProductlist($productlistInfo);
                
                // if($result > 0) {
                    // $this->session->set_flashdata('success', 'New Productlist created successfully');
                // } else {
                    // $this->session->set_flashdata('error', 'Productlist creation failed');
                // }
                
                // redirect('productlist/productlistListing');
            // }
        // }
    // }
	 //code commented by yashi
        //code done by yashi
		
		  
/*public function addNewProductlist()
{        
       $this->load->library('form_validation');
            
            $this->form_validation->set_rules('productName','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
      

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('add');
        } else {
    $data = array(
        'productCode' => $this->input->post('productCode'),
                'productName' => $this->input->post('productName'),
                'description' => $this->input->post('description')
        
    );
   
    $insert_id = $this->prodlist->addNewProductlist($data);
    
    if ($insert_id) {
        // Insert successful
        echo 'New Productlist created successfully';
    } else {
        // Insert failed
        echo 'Failed to insert data';
    }
	redirect('productlist/productlistListing');
}
}
*/
public function addNewProductlist()
{
    $this->load->library('form_validation');
    
    // Form validation rules
    $this->form_validation->set_rules('productName', 'Title', 'trim|required|max_length[50]');
    $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

    // Fetch the next available product code
    $lastProductCode = $this->prodlist->getLastProductCode(); // Assuming you have a model method for this
    $nextProductCode = $lastProductCode ? $lastProductCode + 1 : 1; // Increment or set default to 1
    
    if ($this->form_validation->run() == FALSE) {
        // Pass the nextProductCode to the view
        $data['nextProductCode'] = $nextProductCode;
        $this->loadViews("productlist/add", $this->global, $data, NULL);
       // $this->load->view('add', $data);  // Load the form view with next product code
    } else {
        // Prepare data for insertion, ensure productCode is automatically handled
        $data = array(
            'productCode' => $nextProductCode, // Automatically use the generated productCode
            'productName' => $this->input->post('productName'),
            'description' => $this->input->post('description')
        );

        // Insert into database
        $insert_id = $this->prodlist->addNewProductlist($data);
        
        // Check if the insertion was successful
        if ($insert_id) {
            echo 'New Productlist created successfully';
        } else {
            echo 'Failed to insert data';
        }

        // Redirect to the product listing page
        redirect('productlist/productlistListing');
    }
}

		
		
    /**
     * This function is used load productlist edit information
     * @param number $productlistId : Optional : This is productlist id
     */
    function edit($productlistId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($productlistId == null)
            {
                redirect('productlist/productlistListing');
            }
            
            $data['productlistInfo'] = $this->prodlist->getProductlistInfo($productlistId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Productlist';
            
            $this->loadViews("productlist/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editProductlist()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $productlistId = $this->input->post('productlistId');
            
            $this->form_validation->set_rules('productName','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($productlistId);
            }
            else
            {
                $productCode = $this->security->xss_clean($this->input->post('productCode'));
                $productName = $this->security->xss_clean($this->input->post('productName'));
                $description = $this->security->xss_clean($this->input->post('description'));
                
                $productlistInfo = array('productCode'=>$productCode, 'productName'=>$productName, 'description'=>$description, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->prodlist->editProductlist($productlistInfo, $productlistId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Productlist updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Productlist updation failed');
                }
                
                redirect('productlist/productlistListing');
            }
        }
    }
	/** Code for CK editor */
	  public function upload() {
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
}

?>