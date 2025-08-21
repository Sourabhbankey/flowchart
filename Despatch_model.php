<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Despatch_model (Despatch Model)
 * Despatch model class to get to handle despatch related data 
 * @author : Ashish
 * @version : 1.0
 * @since : 08 June 2023
 */
class Despatch_model extends CI_Model
{
    /**
     * This function is used to get the despatch listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function despatchListingCount($searchText)
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.despatchTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the despatch listing
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function despatchListing($searchText, prestazione$page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch as BaseTbl');
        
        if (!empty($searchText)) {
            $likeCriteria = "(BaseTbl.despatchTitle LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.despatchId', 'DESC'); // Ensure latest despatchId is at the top
        $this->db->limit($page, $segment);
        
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * This function is used to add new despatch to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewDespatch($despatchInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_despatch', $despatchInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get despatch information by id
     * @param number $despatchId : This is despatch id
     * @return array $result : This is despatch information
     */
    function getDespatchInfo($despatchId)
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch');
        $this->db->where('despatchId', $despatchId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }
    
    public function getFranchiseNumberByDespatchId($despatchId)
{
    $this->db->select('franchiseNumber');
    $this->db->from('tbl_despatch'); // Adjust table name if different
    $this->db->where('despatchId', $despatchId); // Adjust column name if different
    $query = $this->db->get();
    return $query->row() ? $query->row()->franchiseNumber : null;
}
    /**
     * This function is used to update the despatch information
     * @param array $despatchInfo : This is despatch updated information
     * @param number $despatchId : This is despatch id
     */
    function editDespatch($despatchInfo, $despatchId)
    {
        $this->db->where('despatchId', $despatchId);
        $this->db->update('tbl_despatch', $despatchInfo);
        
        return TRUE;
    }
    
    /**
     * This function is used to get despatch by id
     * @param number $despatchId : This is despatch id
     * @return object $result : This is despatch information
     */
    function getDespatchById($despatchId)
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch');
        $this->db->where('despatchId', $despatchId);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return null;
        }
    }
    
    /**
     * This function is used to get the user information
     * @return array $result : This is result of the query
     */
    function getUser()
    {
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1, 14, 2]);
        $this->db->where('userTbl.roleId', 15);
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * This function is used to get franchise information
     * @return array $result : This is result of the query
     */
    function getFranchise()
    {
        $this->db->select('userTbl.userId, userTbl.name, userTbl.roleId, userTbl.others, userTbl.isAdmin, userTbl.email, userTbl.franchiseNumber');
        $this->db->from('tbl_users as userTbl');
        $this->db->where('userTbl.roleId', 25);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * This function is used to get all attachment records
     * @return array $result : This is result of the query
     */
    public function getAllacattachmentRecords()
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch');
        $this->db->order_by('shoporderId', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * This function is used to get attachment records by franchise
     * @param string $franchiseNumber : This is franchise number
     * @return array $result : This is result of the query
     */
    public function getattachmentRecordsByFranchise($franchiseNumber)
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->order_by('shoporderId', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * This function is used to get the count of despatch records
     * @param string $franchiseFilter : Optional franchise filter
     * @return number $count : This is row count
     */
    public function get_count($franchiseFilter = null)
    {
        $this->db->from('tbl_despatch');
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results();
    }

    /**
     * This function is used to get the count of PDC records by franchise
     * @param string $franchiseNumber : Franchise number
     * @param string $franchiseFilter : Optional franchise filter
     * @return number $count : This is row count
     */
    public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null)
    {
        $this->db->from('tbl_pdc');
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results();
    }

    /**
     * This function is used to get despatch data with pagination
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @param string $franchiseFilter : Optional franchise filter
     * @return array $result : This is result of the query
     */
    public function get_data($limit, $start, $franchiseFilter = null)
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch');
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('shoporderId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * This function is used to get despatch data by franchise with pagination
     * @param string $franchiseNumber : Franchise number
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @param string $franchiseFilter : Optional franchise filter
     * @return array $result : This is result of the query
     */
    public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null)
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch');
        $this->db->where('franchiseNumber', $franchiseNumber);
        if ($franchiseFilter) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('shoporderId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * This function is used to get the total count of despatch records by franchise
     * @param string $franchiseNumber : Franchise number
     * @return number $count : This is row count
     */
    public function getTotalTrainingRecordsCountByFranchise($franchiseNumber)
    {
        $this->db->from('tbl_despatch');
        $this->db->where('franchiseNumber', $franchiseNumber);
        return $this->db->count_all_results();
    }
    
    /**
     * This function is used to get despatch records by franchise
     * @param string $franchiseNumber : Franchise number
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @return array $result : This is result of the query
     */
    public function getTrainingRecordsByFranchise($franchiseNumber, $limit, $start)
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch');
        $this->db->where('franchiseNumber', $franchiseNumber);
        $this->db->order_by('shoporderId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * This function is used to get the total count of all despatch records
     * @return number $count : This is row count
     */
    public function getTotalTrainingRecordsCount()
    {
        $this->db->from('tbl_despatch');
        return $this->db->count_all_results();
    }
    
    /**
     * This function is used to get all despatch records with pagination
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @It's not accepting franchiseFilter as parameter
     * @return array $result : This is result of the query
     */
    public function getAllTrainingRecords($limit, $offset, $franchiseFilter = '')
{
    $this->db->select('*');
    $this->db->from('tbl_despatch');

    if (!empty($franchiseFilter)) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }

    $this->db->order_by('shoporderId', 'DESC'); // <-- Order by most recent shoporderId
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    return $query->result();
}


    /**
     * This function is used to get the total count of despatch records by role
     * @param number $roleId : This is user role id
     * @param string $franchiseFilter : Optional franchise filter
     * @return number $count : This is row count
     */
    public function getTotalTrainingRecordsCountByRole($roleId, $franchiseFilter = null)
    {
        $this->db->from('tbl_despatch');
        $this->db->where('brspFranchiseAssigned', $roleId);
        if (!empty($franchiseFilter)) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        return $this->db->count_all_results();
    }

    /**
     * This function is used to get despatch records by role
     * @param number $roleId : This is user role id
     * @param number $limit : This is pagination limit
     * @param number $start : This is pagination offset
     * @param string $franchiseFilter : Optional franchise filter
     * @return array $result : This is result of the query
     */
    public function getTrainingRecordsByRole($roleId, $limit, $start, $franchiseFilter = null)
    {
        $this->db->select('*');
        $this->db->from('tbl_despatch');
        $this->db->where('brspFranchiseAssigned', $roleId);
        if (!empty($franchiseFilter)) {
            $this->db->where('franchiseNumber', $franchiseFilter);
        }
        $this->db->order_by('shoporderId', 'DESC');
        $this->db->limit($limit, $start);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * This function is used to get franchise number by user id
     * @param number $userId : This is user id
     * @return string $franchiseNumber : This is franchise number
     */
    public function getFranchiseNumberByUserId($userId)
    {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }

    /**
     * This function is used to get users by franchise
     * @param string $franchiseNumber : This is franchise number
     * @return array $result : This is result of the query
     */
    public function getUsersByFranchise($franchiseNumber)
    {
        $this->db->select('tbl_users.userId, tbl_users.name');
        $this->db->from('tbl_branches');
        $this->db->join('tbl_users', 'tbl_branches.branchFranchiseAssigned = tbl_users.userId', 'inner');
        $this->db->where('tbl_branches.franchiseNumber', $franchiseNumber);
        $this->db->where('tbl_branches.isDeleted', 0);
        return $this->db->get()->result();
    }

  public function getDespatchChartData($year, $franchiseNumber = null, $restrictDeleted = true) {
    $data = array_fill(0, 12, 0);

    $this->db->select("MONTH(dateofdespatch) as month, COUNT(*) as count");
    $this->db->from('tbl_despatch');
    $this->db->where('YEAR(dateofdespatch)', $year);
    if (!empty($franchiseNumber)) {
        $this->db->where('franchiseNumber', $franchiseNumber);
    }
    if ($restrictDeleted) {
        $this->db->where('isDeleted', 0);
    }
    $this->db->group_by('MONTH(dateofdespatch)');
    $query = $this->db->get();

    foreach ($query->result() as $row) {
        $monthIndex = (int)$row->month - 1; // 1-12 to 0-11
        $data[$monthIndex] = (int)$row->count;
    }

    return $data;
}

    /**
     * This function is used to get managers by franchise
     * @param string $franchiseNumber : This is franchise number
     * @return array $result : This is result of the query
     */
    public function getManagersByFranchise($franchiseNumber)
    {
        return $this->db->select('u.userId, u.name')
                        ->from('tbl_users as u')
                        ->join('tbl_branches as b', 'b.branchFranchiseAssigned = u.userId')
                        ->where('b.franchiseNumber', $franchiseNumber)
                        ->get()
                        ->result();
    }

    /**
     * This function is used to get franchise details
     * @param string $franchiseNumber : This is franchise number
     * @return object $result : This is franchise information
     */
    public function getFranchiseDetails($franchiseNumber)
    {
        return $this->db->select('*')
                        ->from('tbl_branches as f')
                        ->where('f.franchiseNumber', $franchiseNumber)
                        ->get()
                        ->row();
    }
   public function getDespatchByOrderId($orderNumber)
{
    $this->db->where('orderNumber', $orderNumber);
    $query = $this->db->get('tbl_despatch');
    return $query->row(); // returns object
}

public function insertDespatchIfNotExists($order)
{
    $this->db->where('shoporderId', $order->id);
    $query = $this->db->get('tbl_despatch');

    if ($query->num_rows() == 0) {
        $shippingName = $order->shipping->first_name . ' ' . $order->shipping->last_name;
        $shippingAddress = $order->shipping->address_1 . ', ' . $order->shipping->city;

        $data = array(
            'shoporderId' => $order->id,
             'orderNumber' => $order->number,
            'billingName' => $order->billing->first_name . ' ' . $order->billing->last_name,
            'franchiseNumber' => $order->billing->company ?? '',
            'billingEmail' => $order->billing->email,
            'billingPhone' => $order->billing->phone,
            'billingAddress' => $order->billing->address_1 . ', ' . $order->billing->city,

            // New: Shipping details
            'shippingName' => $shippingName,
            'shippingAddress' => $shippingAddress,
            'shippingPhone' => $order->billing->phone, // WooCommerce usually doesn't store separate phone for shipping

            'createdDtm' => date('Y-m-d H:i:s'),
            'createdBy' => $this->session->userdata('userId')
        );

        $this->db->insert('tbl_despatch', $data);
    }
}
public function getDespatchByShopOrderId($shopOrderId)
{
    $this->db->select('despatchId');
    $this->db->from('tbl_despatch');
    $this->db->where('shoporderId', $shopOrderId);
    $query = $this->db->get();

    return $query->row(); // returns NULL if not found
}
public function fetchAndInsertAllWooOrders()
{
    $consumerKey = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579';
    $consumerSecret = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';
    $perPage = 100;
    $page = 1;
    $totalPages = 1;

    do {
        $apiUrl = "https://shop.theischool.com/wp-json/wc/v3/orders?page={$page}&per_page={$perPage}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ":" . $consumerSecret);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);

        // Get total pages on first run
        if ($page === 1) {
            preg_match('/X-WP-TotalPages: (\d+)/i', $header, $matches);
            $totalPages = isset($matches[1]) ? (int)$matches[1] : 1;
        }

        $orders = json_decode($body);
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $exists = $this->db->get_where('tbl_despatch', ['shoporderId' => $order->id])->row();
                if (!$exists) {
                    $data = [
                        'shoporderId'       => $order->id,
                        'orderNumber'       => $order->number,
                        'status'        => $order->status,
                        'billingName'       => $order->billing->first_name . ' ' . $order->billing->last_name,
                        'franchiseNumber'   => $order->billing->company ?? '',
                        'billingEmail'      => $order->billing->email,
                        'billingPhone'      => $order->billing->phone,
                        'billingAddress'    => $order->billing->address_1 . ', ' . $order->billing->city,
                        'shippingName'      => $order->shipping->first_name . ' ' . $order->shipping->last_name,
                        'shippingAddress'   => $order->shipping->address_1 . ', ' . $order->shipping->city,
                        'shippingPhone'     => $order->billing->phone,
                        'productDescription'=> $order->line_items[0]->name ?? '',
                        'createdDtm'        => date('Y-m-d H:i:s'),
                        'createdBy'         => $this->session->userdata('userId')
                    ];
                    $this->db->insert('tbl_despatch', $data);
                }
            }
        }

        $page++;
    } while ($page <= $totalPages);
}


}