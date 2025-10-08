<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends CI_Controller
{
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Category_model');
        $this->load->model('Token_model');
        $this->load->helper('url');

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
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        log_message('debug', 'Categories authenticate - Headers: ' . json_encode($headers));
        log_message('debug', 'Categories authenticate - Authorization header: ' . $auth_header);

        if (!preg_match('/Bearer\s+(\S+)/', $auth_header, $matches)) {
            log_message('error', 'Categories authenticate - No valid Bearer token found');
            $this->output->set_content_type('application/json')->set_status_header(401);
            echo json_encode(['status' => 'error', 'message' => 'Token required']);
            exit;
        }

        $token = $matches[1];
        log_message('debug', 'Categories authenticate - Token extracted: ' . $token);
        $this->user_id = $this->Token_model->verify($token);

        if (!$this->user_id) {
            log_message('error', 'Categories authenticate - Token verification failed for token: ' . $token);
            $this->output->set_content_type('application/json')->set_status_header(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
            exit;
        }
        log_message('debug', 'Categories authenticate - Token verified, user_id: ' . $this->user_id);
    }

    public function index()
    {
        $this->output->set_content_type('application/json');

        $page = max(1, (int) $this->input->get('page', TRUE));
        $per_page = max(1, (int) $this->input->get('per_page', TRUE));

        try {
            $filters = [
                'page' => $page,
                'per_page' => $per_page
            ];

            $result = $this->Category_model->search_categories($this->user_id, $filters);
            echo json_encode($result);
        } catch (Exception $e) {
            log_message('error', 'Categories index error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error loading categories']);
        }
    }

    public function create()
    {
        $this->output->set_content_type('application/json');

        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: [];

        $data = [
            'name' => isset($post_data['name']) ? trim($post_data['name']) : '',
            'type' => isset($post_data['type']) ? $post_data['type'] : 'income',
            'user_id' => $this->user_id,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Category name is required']);
            return;
        }

        if (!in_array($data['type'], ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'Type must be income or expense']);
            return;
        }

        $result = $this->Category_model->create_category($data);
        echo json_encode($result);
    }

    public function edit($id = null)
    {
        $this->output->set_content_type('application/json');

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Category ID required']);
            return;
        }

        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: [];

        $data = [
            'id' => $id,
            'name' => isset($post_data['name']) ? trim($post_data['name']) : '',
            'type' => isset($post_data['type']) ? $post_data['type'] : '',
            'user_id' => $this->user_id
        ];

        if (empty($data['name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Category name is required']);
            return;
        }

        if (!in_array($data['type'], ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'Type must be income or expense']);
            return;
        }

        $result = $this->Category_model->update_category($data);
        echo json_encode($result);
    }

    public function delete($id = null)
    {
        $this->output->set_content_type('application/json');

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Category ID required']);
            return;
        }

        try {
            // Check if category exists and belongs to user
            $this->db->where('id', $id);
            $this->db->where('user_id', $this->user_id);
            $query = $this->db->get('categories');

            if ($query->num_rows() === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Category not found']);
                return;
            }

            if ($this->Category_model->is_category_used($id, $this->user_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot delete category: it is being used in transactions']);
                return;
            }

            $result = $this->Category_model->delete_category($id, $this->user_id);
            echo json_encode($result);
        } catch (Exception $e) {
            log_message('error', 'Delete category error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error']);
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
            'page' => max(1, (int) ($post_data['page'] ?? 1)),
            'per_page' => max(1, (int) ($post_data['per_page'] ?? 10))
        ];

        try {
            $result = $this->Category_model->search_categories($this->user_id, $filters);
            echo json_encode($result);
        } catch (Exception $e) {
            log_message('error', 'Search categories error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error searching categories']);
        }
    }

    public function get_categories_list()
    {
        $this->output->set_content_type('application/json');

        try {
            $categories = $this->Category_model->get_user_categories($this->user_id);
            echo json_encode(['status' => 'success', 'categories' => $categories]);
        } catch (Exception $e) {
            log_message('error', 'Get categories list error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error loading categories']);
        }
    }
}