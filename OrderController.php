<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

class OrderController extends BaseController
{
    private $ck = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579'; // Replace with actual
    private $cs = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';

    private $api_base = 'https://shop.theischool.com/wp-json/wc/v3/orders';

    public function __construct()
    {
        parent::__construct();
        $this->isLoggedIn();
        $this->module = 'Orders';
        $this->load->library('pagination'); // Load pagination library
    }

    public function index()
    {
        redirect('OrderController/all_orders');
    }

    public function all_orders()
    {
        $consumerKey = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579';
        $consumerSecret = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';

        // Get search query and date filters
        $search_query = $this->input->get('search', TRUE);
        $from_date = $this->input->get('from_date', TRUE);
        $to_date = $this->input->get('to_date', TRUE);

        // Pagination setup
        $config = array();
        $config['base_url'] = base_url('OrderController/all_orders');
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;

        // Build API URL with parameters
        $apiUrl = "https://shop.theischool.com/wp-json/wc/v3/orders?page={$page}&per_page={$config['per_page']}";
        if ($search_query) {
            $apiUrl .= "&search=" . urlencode($search_query);
        }
        if ($from_date) {
            $apiUrl .= "&after=" . urlencode($from_date . 'T00:00:00');
        }
        if ($to_date) {
            $apiUrl .= "&before=" . urlencode($to_date . 'T23:59:59');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ":" . $consumerSecret);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($ch);

        // Get total pages from header
        preg_match('/X-WP-TotalPages: (\d+)/i', $header, $matches);
        $totalPages = $matches[1] ?? 1;

        preg_match('/X-WP-Total: (\d+)/i', $header, $totalMatches);
        $totalOrders = $totalMatches[1] ?? 0;

        $config['total_rows'] = $totalOrders;
        $this->pagination->initialize($config);

        $orders = json_decode($body, true);

        // Chunk-based logic
        $chunkSize = 3;
        $currentChunk = ceil($page / $chunkSize);
        $startPage = ($currentChunk - 1) * $chunkSize + 1;
        $endPage = min($startPage + $chunkSize - 1, $totalPages);
        $nextChunk = ($endPage < $totalPages) ? $endPage + 1 : null;
        $prevChunk = ($startPage > 1) ? $startPage - $chunkSize : null;

        $data['orders'] = $orders;
        $data['currentPage'] = $page;
        $data['totalPages'] = $totalPages;
        $data['pagination'] = $this->pagination->create_links();
        $data['startPage'] = $startPage;
        $data['endPage'] = $endPage;
        $data['nextChunk'] = $nextChunk;
        $data['prevChunk'] = $prevChunk;
        $data['search_query'] = $search_query;
        $data['from_date'] = $from_date; // Pass from_date to view
        $data['to_date'] = $to_date;     // Pass to_date to view

        $this->global['pageTitle'] = 'CodeInsect : Orders Listing';
        $this->loadViews("orders/order_view", $this->global, $data, NULL);
    }

    public function order_details($id = NULL)
    {
        if ($id == null) {
            redirect('ordercontroller/all_orders');
        }

        $consumerKey = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579';
        $consumerSecret = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';
        $apiUrl = "https://shop.theischool.com/wp-json/wc/v3/orders/{$id}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ":" . $consumerSecret);
        $response = curl_exec($ch);
        curl_close($ch);

        $order = json_decode($response, true);

        if (empty($order)) {
            show_404();
        }

        $data['order'] = $order;
        $data['showPrintButton'] = true; 
        $this->global['pageTitle'] = 'CodeInsect : Order Details';
        $this->loadViews("orders/view", $this->global, $data, NULL);
    }

    public function customer_history($customerId)
    {
        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $consumerKey = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579';
        $consumerSecret = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';

        $apiUrl = "https://shop.theischool.com/wp-json/wc/v3/orders?customer={$customerId}&per_page=100";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ":" . $consumerSecret);
        $response = curl_exec($ch);
        curl_close($ch);

        $orders = json_decode($response, true);
        if (!is_array($orders)) {
            $orders = [];
        }

        // Filter by date
        if ($from || $to) {
            $orders = array_filter($orders, function ($order) use ($from, $to) {
                $orderDate = date('Y-m-d', strtotime($order['date_created']));
                return (!$from || $orderDate >= $from) && (!$to || $orderDate <= $to);
            });
        }

        // Calculate stats
        $totalRevenue = 0;
        $totalOrders = 0;
        $cancelledOrders = 0;

        foreach ($orders as $order) {
            if (isset($order['status']) && $order['status'] === 'cancelled') {
                $cancelledOrders++;
                continue;
            }
            $totalOrders++;
            $totalRevenue += isset($order['total']) ? floatval($order['total']) : 0;
        }

        $averageOrderValue = ($totalOrders > 0) ? ($totalRevenue / $totalOrders) : 0;

        $data = [
            'customerId' => $customerId,
            'orders' => $orders,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'cancelledOrders' => $cancelledOrders,
        ];

        $this->global['pageTitle'] = 'Customer Order History';
        $this->loadViews("orders/customer_history", $this->global, $data, NULL);
    }

    public function user_list()
    {
        $consumerKey = 'ck_0d0bbcdd0e50d5d503b591509a3a2d2c7c20f579';
        $consumerSecret = 'cs_8caa2a781b70569c7557d08bbc38433d587f4994';

        $page = $this->uri->segment(3);
        if (!$page || !is_numeric($page)) {
            $page = 1;
        }

        $per_page = 10;
        $apiUrl = "https://shop.theischool.com/wp-json/wc/v3/customers?per_page=$per_page&page=$page";

        // Init cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$consumerKey:$consumerSecret");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($ch);

        $users = json_decode($body, true);
        if (!is_array($users)) {
            $users = [];
        }

        // Parse headers into array (case-insensitive)
        $headers = [];
        $header_lines = explode("\r\n", $header);
        foreach ($header_lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }

        $totalPages = isset($headers['x-wp-totalpages']) ? (int)$headers['x-wp-totalpages'] : 1;
        $totalUsers = isset($headers['x-wp-total']) ? (int)$headers['x-wp-total'] : 0;

        // Pagination chunk logic
        $chunkSize = 3;
        $currentChunk = ceil($page / $chunkSize);
        $startPage = ($currentChunk - 1) * $chunkSize + 1;
        $endPage = min($startPage + $chunkSize - 1, $totalPages);
        $nextChunk = ($endPage < $totalPages) ? $endPage + 1 : null;
        $prevChunk = ($startPage > 1) ? $startPage - $chunkSize : null;

        $data = [
            'users' => $users,
            'currentPage' => $page,
            'per_page' => $per_page,
            'totalPages' => $totalPages,
            'startPage' => $startPage,
            'endPage' => $endPage,
            'nextChunk' => $nextChunk,
            'prevChunk' => $prevChunk,
            'totalUsers' => $totalUsers
        ];

        $this->global['pageTitle'] = 'WooCommerce Customers';
        $this->loadViews("orders/user_list", $this->global, $data, NULL);
    }

    public function get_orders_dropdown()
    {
        $url = $this->api_base . '?consumer_key=' . $this->ck . '&consumer_secret=' . $this->cs . '&per_page=100';

        $response = $this->curl_get($url);
        $orders = json_decode($response, true);

        $result = [];
        if (is_array($orders)) {
            foreach ($orders as $order) {
                // Skip orders with status 'cancelled' or 'failed'
                if (in_array($order['status'], ['cancelled', 'failed'])) {
                    continue;
                }

                $result[] = [
                    'id' => $order['id'],                                   // Internal ID
                    'number' => $order['number'] ?? '#' . $order['id']      // Display Number
                ];
            }
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function get_order_description()
    {
        $order_id = $this->input->post('order_id');
        $url = $this->api_base . '/' . $order_id . '?consumer_key=' . $this->ck . '&consumer_secret=' . $this->cs;

        $response = $this->curl_get($url);
        $order = json_decode($response, true);

        $desc = '';
        if (!empty($order)) {
            $desc .= "Items:\n";

            foreach ($order['line_items'] as $item) {
                $desc .= "- " . $item['name'] . " x " . $item['quantity'] . "\n";
            }
        }

        echo json_encode(['description' => $desc]);
    }

    public function get_franchise_description()
    {
        $order_id = $this->input->post('order_id');
        $url = $this->api_base . '/' . $order_id . '?consumer_key=' . $this->ck . '&consumer_secret=' . $this->cs;

        $response = $this->curl_get($url);
        $order = json_decode($response, true);

        // Try to get from billing first
        $franchise = $order['billing']['company'] ?? '';

        // If not found, try from meta_data
        if (!$franchise && !empty($order['line_items'])) {
            foreach ($order['line_items'][0]['meta_data'] as $meta) {
                if ($meta['key'] == 'company') {
                    $franchise = $meta['value'];
                    break;
                }
            }
        }

        echo json_encode(['franchisename' => $franchise]);
    }

    private function curl_get($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}