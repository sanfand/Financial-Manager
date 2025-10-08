<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transactions extends CI_Controller
{
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Transaction_model');
        $this->load->model('Category_model');
        $this->load->model('Token_model');
        $this->load->library("Auth");

        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->authenticate();
    }

    private function authenticate()
    {
        // $headers = getallheaders();
        // $auth_header = $headers['Authorization'] ?? $headers['Authorization'] ?? '';

        // echo "auth:";
        // print_r($auth_header);
        // return;
        // log_message('debug', 'Transactions authenticate - Headers: ' . json_encode($headers));
        // log_message('debug', 'Transactions authenticate - Authorization header: ' . $auth_header);

        // if (!preg_match('/Bearer\s+(\S+)/', $auth_header, $matches)) {
        //     log_message('error', 'Transactions authenticate - No valid Bearer token found');
        //     $this->output->set_content_type('application/json')->set_status_header(401);
        //     echo json_encode(['status' => 'error', 'message' => 'Token required']);
        //     exit;
        // }

        // $token = $matches[1];
        // log_message('debug', 'Transactions authenticate - Token extracted: ' . $token);
        // $this->user_id = $this->Token_model->verify($token);

        // if (!$this->user_id) {
        //     log_message('error', 'Transactions authenticate - Token verification failed for token: ' . $token);
        //     $this->output->set_content_type('application/json')->set_status_header(401);
        //     echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
        //     exit;
        // }
        // log_message('debug', 'Transactions authenticate - Token verified, user_id: ' . $this->user_id);

        $auth = new Auth();

        $userId = $auth->authenticate();

        if(!$userId){
            echo 'token or user is not valid';
            exit;
        }

        $this->user_id = $userId;
    }

    public function index()
    {
        $this->output->set_content_type('application/json');

        $page = max(1, (int) $this->input->get('page', TRUE));
        $per_page = max(1, (int) $this->input->get('per_page', TRUE));

        try {
            $total_rows = $this->Transaction_model->count_transactions($this->user_id);
            $transactions = $this->Transaction_model->get_transactions($this->user_id, $per_page, ($page - 1) * $per_page);
            $categories = $this->Category_model->get_user_categories($this->user_id);

            $response = [
                'status' => 'success',
                'transactions' => $transactions,
                'categories' => $categories,
                'current_page' => $page,
                'total_pages' => ceil($total_rows / $per_page) ?: 1,
                'total_records' => $total_rows
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            log_message('error', 'Transactions index error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error loading transactions']);
        }
    }

    public function create()
    {
        $this->output->set_content_type('application/json');

        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: [];

        $data = [
            'title' => isset($post_data['title']) ? trim($post_data['title']) : '',
            'amount' => isset($post_data['amount']) ? (float) $post_data['amount'] : 0,
            'type' => isset($post_data['type']) ? $post_data['type'] : '',
            'category_id' => isset($post_data['category_id']) && !empty($post_data['category_id']) ? (int) $post_data['category_id'] : null,
            'occurred_at' => isset($post_data['occurred_at']) ? $post_data['occurred_at'] : date('Y-m-d H:i:s'),
            'notes' => isset($post_data['notes']) ? trim($post_data['notes']) : '',
            'user_id' => $this->user_id,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['title']) || empty($data['type']) || $data['amount'] <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Title, type, and valid amount are required']);
            return;
        }

        if (!in_array($data['type'], ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'Type must be income or expense']);
            return;
        }

        try {
            if ($this->Transaction_model->create_transaction($data)) {
                echo json_encode(['status' => 'success', 'message' => 'Transaction created successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create transaction']);
            }
        } catch (Exception $e) {
            log_message('error', 'Create transaction error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error creating transaction']);
        }
    }

    public function delete($id = null)
    {
        $this->output->set_content_type('application/json');

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction ID required']);
            return;
        }

        try {
            $this->db->where('id', $id);
            $this->db->where('user_id', $this->user_id);
            $query = $this->db->get('transactions');

            if ($query->num_rows() === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
                return;
            }

            $this->db->where('id', $id);
            $this->db->where('user_id', $this->user_id);
            $deleted = $this->db->delete('transactions');

            if ($deleted) {
                echo json_encode(['status' => 'success', 'message' => 'Transaction deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete transaction']);
            }
        } catch (Exception $e) {
            log_message('error', 'Delete transaction error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error deleting transaction']);
        }
    }

    public function search()
    {
        $this->output->set_content_type('application/json');

        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: [];

        $filters = [
            'search' => isset($post_data['search']) ? trim($post_data['search']) : '',
            'type' => isset($post_data['type']) ? $post_data['type'] : '',
            'category_id' => isset($post_data['category_id']) && !empty($post_data['category_id']) ? (int) $post_data['category_id'] : '',
            'start_date' => isset($post_data['start_date']) ? $post_data['start_date'] : '',
            'end_date' => isset($post_data['end_date']) ? $post_data['end_date'] : '',
            'page' => max(1, (int) ($post_data['page'] ?? 1)),
            'per_page' => max(1, (int) ($post_data['per_page'] ?? 10))
        ];

        try {
            $total_rows = $this->Transaction_model->count_transactions($this->user_id, $filters);
            $transactions = $this->Transaction_model->search_transactions($this->user_id, $filters, $filters['per_page'], ($filters['page'] - 1) * $filters['per_page']);

            $response = [
                'status' => 'success',
                'transactions' => $transactions,
                'current_page' => $filters['page'],
                'total_pages' => ceil($total_rows / $filters['per_page']) ?: 1,
                'total_records' => $total_rows
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            log_message('error', 'Search transactions error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error searching transactions']);
        }
    }
}