<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Salesrecord (SalesrecordController)
 * Salesrecord Class to control Salesrecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 22 June 2024
 */
class Salesrecord extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Salesrecord_model', 'salrec');
		 $this->load->model('Productlist_model', 'prodrec');
        $this->isLoggedIn();
        $this->module = 'Salesrecord';
		
		
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('salesrecord/salesrecordListing');
    }
    
    /**
     * This function is used to load the salesrecord list
     */
  
 /* function salesrecordListing()
{
    if (!$this->hasListAccess()) {
        $this->loadThis();
    } else {
        $searchText = '';
        if (!empty($this->input->post('searchText'))) {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
        }
        $data['searchText'] = $searchText;

        $this->load->library('pagination');

        $count = $this->salrec->salesrecordListingCount($searchText);
        $returns = $this->paginationCompress("salesrecordListing/", $count, 10); // 10 records per page

        $data['records'] = $this->salrec->salesrecordListing($searchText, $returns["page"], $returns["segment"]);

        $this->global['pageTitle'] = 'CodeInsect : Salesrecord';
        $this->loadViews("salesrecord/list", $this->global, $data, NULL);
    }
}
*/
 function salesrecordListing()
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
            
            $count = $this->salrec->salesrecordListingCount($searchText);

            $returns = $this->paginationCompress("salesrecordListing/", $count, 500 );
            
           $data['records'] = $this->salrec->salesrecordListing($searchText, $returns["segment"], $returns["page"]);

            
            $this->global['pageTitle'] = 'CodeInsect : Purchaserecord';
            
            $this->loadViews("salesrecord/list", $this->global, $data, NULL);
        }
    }

    /**
     * This function is used to load the add new form
     */
    function add()

    {   
	
		$data['codes'] = $this->salrec->get_product_codes();
		$data['name'] = $this->salrec->get_product_name();
		
		
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->global['pageTitle'] = 'CodeInsect : Add New Salesrecord';
			

          // $this->loadViews("salesrecord/add", $this->global, $data);
           $this->loadViews("salesrecord/add", $this->global, $data, NULL);
        }
		
    }
    
    /**
     * This function is used to add new user to the system
     */


public function addNewSalesrecord()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('productCode', 'Product Code', 'trim|required');
        $this->form_validation->set_rules('productName', 'Product Name', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');
        $this->form_validation->set_rules('prodQuantity', 'Product Quantity', 'trim|required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->add(); // Reload form with errors
        } else {
            $productCode   = $this->security->xss_clean($this->input->post('productCode'));
            $productName   = $this->security->xss_clean($this->input->post('productName'));
            $dateOfsale    = $this->security->xss_clean($this->input->post('dateOfsale'));
            $soldTo        = $this->security->xss_clean($this->input->post('soldTo'));
            $prodQuantity  = (float) $this->security->xss_clean($this->input->post('prodQuantity'));
            $description   = $this->security->xss_clean($this->input->post('description'));

            $s3_file_link = [];
            if (!empty($_FILES["file"]["name"])) {
                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                rename($_FILES["file"]["tmp_name"], $destination);

                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();

                $s3_file_link[] = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
            }

            $s3files = implode(',', $s3_file_link);

            // Get current stock
            $stockData = $this->salrec->getCurrentStockByProductCode($productCode);
            $currentStock = ($stockData) ? (float)$stockData->currentStock : 0;
            $updatedStock = $currentStock - $prodQuantity;

            if ($updatedStock < 0) {
                $this->session->set_flashdata('error', 'Insufficient stock for selected product.');
                redirect('salesrecord/add');
            }

            // If it's a kit, update individual items' stock
            $stock = $this->salrec->getStockByProductCode($productCode);
            $stockId = ($stock) ? $stock->stockId : 0;
            $kitItems = $this->salrec->getKitContents($stockId);

            if (!empty($kitItems)) {
                foreach ($kitItems as $item) {
                    $itemCode = $item['productCode'];
                    $qtyPerKit = (float)$item['kitprodQty'];
                    $totalToDeduct = $qtyPerKit * $prodQuantity;

                    $itemStockData = $this->salrec->getCurrentStockByProductCode($itemCode);
                    $itemCurrentStock = ($itemStockData) ? (float)$itemStockData->currentStock : 0;
                    $itemUpdatedStock = $itemCurrentStock - $totalToDeduct;

                    if ($itemUpdatedStock < 0) {
                        $this->session->set_flashdata('error', 'Insufficient stock for kit item: ' . $itemCode);
                        redirect('salesrecord/add');
                    }

                    $this->salrec->updateStock($itemCode, $itemUpdatedStock);
                }
            }

            $salesrecordInfo = array(
                'productCode'      => $productCode,
                'productName'      => $productName,
                'dateOfsale'       => $dateOfsale,
                'soldTo'           => $soldTo,
                'prodQuantity'     => $prodQuantity,
                'updatedStock'     => $updatedStock,
                'description'      => $description,
                'salesattachment'  => $s3files,
                'createdBy'        => $this->vendorId,
                'createdDtm'       => date('Y-m-d H:i:s')
            );

            // Check if record already exists
            $existingRecord = $this->salrec->getSalesRecordByProductCode($productCode);

            if ($existingRecord) {
                $this->salrec->updateSalesrecordByProductCode($productCode, $salesrecordInfo);
                $this->salrec->updateStock($productCode, $updatedStock);
                $this->session->set_flashdata('success', 'Sales record updated successfully.');
            } else {
                $insert_id = $this->salrec->addNewSalesrecord($salesrecordInfo);
                $this->salrec->updateStock($productCode, $updatedStock);

                if ($insert_id > 0) {
                    $this->session->set_flashdata('success', 'Sales record added successfully.');
                } else {
                    $this->session->set_flashdata('error', 'Failed to add sales record.');
                }
            }

            redirect('salesrecord/salesrecordListing');
        }
    }
}



    /**
     * This function is used load salesrecord edit information
     * @param number $salesrecId : Optional : This is salesrecord id
     */
    function edit($salesrecId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($salesrecId == null)
            {
                redirect('salesrecord/salesrecordListing');
            }
            
            $data['salesrecordInfo'] = $this->salrec->getSalesrecordInfo($salesrecId);

            $this->global['pageTitle'] = 'CodeInsect : Edit Salesrecord';
            
            $this->loadViews("salesrecord/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editSalesrecord()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $salesrecId = $this->input->post('salesrecId');
            
            $this->form_validation->set_rules('productName','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($salesrecId);
            }
            else
            { 
		
                $productCode = $this->security->xss_clean($this->input->post('productCode'));
                $productName = $this->security->xss_clean($this->input->post('productName'));
                $dateOfsale = $this->security->xss_clean($this->input->post('dateOfsale'));
                $soldTo = $this->security->xss_clean($this->input->post('soldTo'));
                $prodQuantity = $this->security->xss_clean($this->input->post('prodQuantity'));
                $updatedStock = $this->security->xss_clean($this->input->post('updatedStock'));
                $description = $this->security->xss_clean($this->input->post('description'));
                
                $salesrecordInfo = array('productCode'=>$productCode, 'productName'=>$productName, 'dateOfsale'=>$dateOfsale, 'soldTo'=>$soldTo, 'prodQuantity'=>$prodQuantity, 'updatedStock'=>$updatedStock, 'description'=>$description, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->salrec->editSalesrecord($salesrecordInfo, $salesrecId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Salesrecord updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Salesrecord updation failed');
                }
                
                redirect('salesrecord/salesrecordListing');
            }
        }
    }
	


				public function get_description() {
					$code = $this->input->post('productCode');
					$product = $this->salrec->get_product_description($code);

					if ($product) {
						echo json_encode(['productName' => $product['productName']]);
					} else {
						echo json_encode(['productName' => '']);
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

    public function get_product_names() {
        // Get the productCode from the AJAX request
        $productCode = $this->input->post('productCode');
        
        // Fetch product names using the model
        $productNames = $this->salrec->get_product_names_by_code($productCode);
        
        if ($productNames) {
            $options = '<option value="">Select Product Name</option>';
            foreach ($productNames as $name) {
                $options .= '<option value="' . $name['productName'] . '">' . $name['productName'] . '</option>';
            }
            echo $options;
        } else {
            echo '<option value="">No Product Names Found</option>';
        }
    }

}

?>