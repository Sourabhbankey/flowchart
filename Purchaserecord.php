<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Purchaserecord (PurchaserecordController)
 * Purchaserecord Class to control Purchaserecord related operations.
 * @author : Ashish 
 * @version : 1.0
 * @since : 02 Jul 2024
 */
class Purchaserecord extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Purchaserecord_model', 'purrec');
          $this->load->model('Salesrecord_model', 'salrec');

        $this->isLoggedIn();
        $this->module = 'Purchaserecord';
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('purchaserecord/purchaserecordListing');
    }
    
    /**
     * This function is used to load the purchaserecord list
     */
    function purchaserecordListing()
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
            
            $count = $this->purrec->purchaserecordListingCount($searchText);

			$returns = $this->paginationCompress ( "purchaserecordListing/", $count, 500 );
            
            $data['records'] = $this->purrec->purchaserecordListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'CodeInsect : Purchaserecord';
            
            $this->loadViews("purchaserecord/list", $this->global, $data, NULL);
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
            $this->global['pageTitle'] = 'CodeInsect : Add New Purchaserecord';
        $data['codes'] = $this->salrec->get_product_codes();
        $data['name']  =  $this->salrec->get_product_name();
            $this->loadViews("purchaserecord/add", $this->global, $data, NULL);
        }
    }
    
    /**
     * This function is used to add new user to the system
     */
  function addNewPurchaserecord()
{
    if (!$this->hasCreateAccess()) {
        $this->loadThis();
    } else {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('productName', 'Title', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1024]');

        if ($this->form_validation->run() == FALSE) {
            $this->add();
        } else {
            // Get form data
            $productCode = $this->security->xss_clean($this->input->post('productCode'));
            $productName = $this->security->xss_clean($this->input->post('productName'));
            $dateOfOrderplaced = $this->security->xss_clean($this->input->post('dateOfOrderplaced'));
            $orderQty = $this->security->xss_clean($this->input->post('orderQty'));
            $boughtFrom = $this->security->xss_clean($this->input->post('boughtFrom'));
            $purchaseReceived = $this->security->xss_clean($this->input->post('purchaseReceived'));
            $receivedQty = $this->security->xss_clean($this->input->post('receivedQty'));
            $description = $this->security->xss_clean($this->input->post('description'));
             $purchaseattachment = $this->security->xss_clean($this->input->post('purchaseattachment'));
              $boughtFromVenderlist = $this->security->xss_clean($this->input->post('boughtFromVenderlist'));
        $boughtFrom = $this->security->xss_clean($this->input->post('boughtFrom'));
             $s3_file_link = [];
            if (!empty($_FILES["file"]["name"])) {
                $dir = dirname($_FILES["file"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file"]["name"];
                rename($_FILES["file"]["tmp_name"], $destination);

                $storeFolder = 'attachements';
                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();

                if (!empty($result_arr['ObjectURL'])) {
                    $s3_file_link[] = $result_arr['ObjectURL'];
                } else {
                    $s3_file_link[] = '';
                }
            }

            $s3files = implode(',', $s3_file_link);
            // Fetch current stock for the product from tbl_stock_mng
            $currentStockData = $this->purrec->getCurrentStockByProductCode($productCode);
            
            if ($currentStockData) {
                $currentStock = $currentStockData->currentStock; // Get the current stock value
            } else {
                $currentStock = 0; // If product doesn't have a stock record, assume stock is 0
            }

            // Ensure both values are numeric
            $currentStock = is_numeric($currentStock) ? (float)$currentStock : 0;
            $receivedQty = is_numeric($receivedQty) ? (float)$receivedQty : 0;

            // Calculate new updated stock
            $updatedStock = $currentStock + $receivedQty;

            // Create new purchase record array
            $purchaserecordInfo = array(
                'productCode' => $productCode,
                'productName' => $productName,
                'dateOfOrderplaced' => $dateOfOrderplaced,
                'orderQty' => $orderQty,
               // 'boughtFrom' => $boughtFrom,
                'purchaseReceived' => $purchaseReceived,
                'receivedQty' => $receivedQty,
                'updatedStock' => $updatedStock, // Auto-updated stock value
                'description' => $description,
                'createdBy' => $this->vendorId,
                'boughtFromVenderlist' => $boughtFromVenderlist,
            'boughtFrom' => $boughtFrom,
                'purchaseattachment' => $s3files,
                'createdDtm' => date('Y-m-d H:i:s')
            );

            // Insert the new purchase record
            $result = $this->purrec->addNewPurchaserecord($purchaserecordInfo);

            if ($result > 0) {
                // Update the stock in tbl_stock_mng using the model method
                $this->purrec->updateStock($productCode, $updatedStock);
                $this->session->set_flashdata('success', 'New Purchaserecord created successfully and stock updated');
            } else {
                $this->session->set_flashdata('error', 'Purchaserecord creation failed');
            }

            redirect('purchaserecord/purchaserecordListing');
        }
    }
}

    
    /**
     * This function is used load purchaserecord edit information
     * @param number $purcrecId : Optional : This is purchaserecord id
     */
    function edit($purcrecId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($purcrecId == null)
            {
                redirect('purchaserecord/purchaserecordListing');
            }
            
            $data['purchaserecordInfo'] = $this->purrec->getPurchaserecordInfo($purcrecId);
 $data['codes'] = $this->salrec->get_product_codes();
  $data['replies'] = $this->purrec->getRepliesByTicket($purcrecId);
        $data['name']  =  $this->salrec->get_product_name();
            $this->global['pageTitle'] = 'CodeInsect : Edit Purchaserecord';
            
            $this->loadViews("purchaserecord/edit", $this->global, $data, NULL);
        }
    }
    
    
    /**
     * This function is used to edit the user information
     */
    function editPurchaserecord()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $purcrecId = $this->input->post('purcrecId');
            
            $this->form_validation->set_rules('productName','Title','trim|required|max_length[50]');
            $this->form_validation->set_rules('description','Description','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($purcrecId);
            }
            else
            {
                $productCode = $this->security->xss_clean($this->input->post('productCode'));
                $productName = $this->security->xss_clean($this->input->post('productName'));
                $dateOfOrderplaced = $this->security->xss_clean($this->input->post('dateOfOrderplaced'));
                $orderQty = $this->security->xss_clean($this->input->post('orderQty'));
                $boughtFrom = $this->security->xss_clean($this->input->post('boughtFrom'));
                $purchaseReceived = $this->security->xss_clean($this->input->post('purchaseReceived'));
                $receivedQty = $this->security->xss_clean($this->input->post('receivedQty'));
                $updatedStock = $this->security->xss_clean($this->input->post('updatedStock'));
                $description = $this->security->xss_clean($this->input->post('description'));
                $dateOfSupplied = $this->security->xss_clean($this->input->post('dateOfSupplied'));
                $qtySupplied = $this->security->xss_clean($this->input->post('qtySupplied'));
                
                $purchaserecordInfo = array('productCode'=>$productCode, 'productName'=>$productName, 'dateOfOrderplaced'=>$dateOfOrderplaced, 'orderQty'=>$orderQty, 'boughtFrom'=>$boughtFrom, 'purchaseReceived'=>$purchaseReceived, 'receivedQty'=>$receivedQty,'dateOfSupplied'=>$dateOfSupplied, 'updatedStock'=>$updatedStock, 'description'=>$description,'qtySupplied'=>$qtySupplied, 'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->purrec->editPurchaserecord($purchaserecordInfo, $purcrecId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Purchaserecord updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Purchaserecord updation failed');
                }
                
                redirect('purchaserecord/purchaserecordListing');
            }
        }
    }

    public function addReply() {
   

    $purcrecId = $this->input->post('purcrecId');
    $message = $this->input->post('replyMessage');
    $userId = $this->session->userdata('userId');
 if (isset($_FILES['file1']) && $_FILES['file1']['error'] == 0) {
                $dir = dirname($_FILES["file1"]["tmp_name"]);
                $destination = $dir . DIRECTORY_SEPARATOR . time() . '-' . $_FILES["file1"]["name"];
                rename($_FILES["file1"]["tmp_name"], $destination);
                $storeFolder = 'attachements';

                $s3Result = $this->s3_upload->upload_file($destination, $storeFolder);
                $result_arr = $s3Result->toArray();

                $s3files1 = !empty($result_arr['ObjectURL']) ? $result_arr['ObjectURL'] : '';
            } else {
                $s3files1 = '';
            }

    $data = [
        'purcrecId' => $purcrecId,
        'message' => $message,
        'repliedBy' => $userId,
         'attachment' => $s3files1,
          'msgRead' => 0, // Everyone should see it
        'createdDtm' => date('Y-m-d H:i:s')
    ];

   
 $result = $this->purrec->insertReply($data);
    $this->session->set_flashdata('success', 'Reply submitted successfully.');
    redirect('purchaserecord/edit/' . $purcrecId);
}

}

?>