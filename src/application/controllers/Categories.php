<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Category_model');
        $this->load->library('session');
        $this->load->helper('url');
        if (!$this->session->userdata('logged_in')) {
            $this->output->set_content_type('application/json');
            echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
            return;
        }
    }

    public function index()
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');
        $page = max(1, (int) $this->input->get('page', TRUE));
        $per_page = max(1, (int) $this->input->get('per_page', TRUE));

        $total_rows = $this->Category_model->count_categories($user_id);
        $categories = $this->Category_model->get_categories($user_id, $per_page, ($page - 1) * $per_page);

        $response = [
            'status' => 'success',
            'categories' => is_array($categories) ? $categories : [],
            'current_page' => $page,
            'total_pages' => ceil($total_rows / $per_page) ?: 1
        ];

        echo json_encode($response);
    }

    public function create()
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: [];

        $data = [
            'name' => isset($post_data['name']) ? trim($post_data['name']) : '',
            'type' => isset($post_data['type']) ? $post_data['type'] : 'income',
            'user_id' => $user_id
        ];

        if (empty($data['name']) || !in_array($data['type'], ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'Name and valid type (income/expense) are required']);
            return;
        }

        try {
            $result = $this->Category_model->create_category($data); // Fix: Call create_category
            echo json_encode($result);
        } catch (Exception $e) {
            log_message('error', 'Category create error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }

    public function edit($id = null)
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');

        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: [];

        $data = [
            'id' => $id ?: (isset($post_data['id']) ? $post_data['id'] : null),
            'name' => isset($post_data['name']) ? trim($post_data['name']) : '',
            'type' => isset($post_data['type']) ? $post_data['type'] : 'income',
            'user_id' => $user_id
        ];

        if (!$data['id'] || empty($data['name']) || !in_array($data['type'], ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID and valid name/type required']);
            return;
        }

        try {
            // Check if category exists
            $this->db->where('id', $data['id']);
            $this->db->where('user_id', $user_id);
            $query = $this->db->get('categories');
            if ($query->num_rows() === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Category not found']);
                return;
            }

            $result = $this->Category_model->update_category($data);
            // Treat 'no changes made' as success since the category exists and input is valid
            if ($result['status'] === 'error' && $result['message'] === 'Category not found or no changes made') {
                $category = $query->row();
                if ($category->name === $data['name'] && $category->type === $data['type']) {
                    echo json_encode(['status' => 'success', 'category' => $category]);
                    return;
                }
            }
            echo json_encode($result);
        } catch (Exception $e) {
            log_message('error', 'Category update error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }

    public function delete($id = null)
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Category ID required']);
            return;
        }

        try {
            // Check if category exists
            $this->db->where('id', $id);
            $this->db->where('user_id', $user_id);
            $query = $this->db->get('categories');
            if ($query->num_rows() === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Category not found']);
                return;
            }

            // Check if category is used
            if ($this->Category_model->is_category_used($id, $user_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Category in use']);
                return;
            }

            $result = $this->Category_model->delete_category($id, $user_id);
            echo json_encode($result);
        } catch (Exception $e) {
            log_message('error', 'Delete category error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }

    public function search()
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');

        // Get raw input data for JSON
        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: $this->input->post();

        $filters = [
            'search' => isset($post_data['search']) ? trim($post_data['search']) : '',
            'type' => isset($post_data['type']) ? $post_data['type'] : '',
            'page' => max(1, (int) ($post_data['page'] ?? $this->input->post('page', TRUE))),
            'per_page' => max(1, (int) ($post_data['per_page'] ?? $this->input->post('per_page', TRUE)))
        ];

        try {
            $result = $this->Category_model->search_categories($user_id, $filters);
            echo json_encode($result);
        } catch (Exception $e) {
            log_message('error', 'Search categories error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error searching categories']);
        }
    }
}