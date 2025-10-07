<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transactions extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Transaction_model');
        $this->load->model('Category_model');
        $this->load->library('session');

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

        $total_rows = $this->Transaction_model->count_transactions($user_id);
        $transactions = $this->Transaction_model->get_transactions($user_id, $per_page, ($page - 1) * $per_page);
        $categories = $this->Category_model->get_categories();

        $response = [
            'status' => 'success',
            'transactions' => is_array($transactions) ? array_map(function ($t) {
                $t->amount = floatval($t->amount ?? 0);
                return $t;
            }, $transactions) : [],
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

        // Handle JSON or POST input
        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: $this->input->post();

        $data = [
            'user_id' => $user_id,
            'title' => isset($post_data['title']) ? trim($post_data['title']) : '',
            'amount' => isset($post_data['amount']) ? (float) $post_data['amount'] : 0,
            'type' => isset($post_data['type']) ? $post_data['type'] : '',
            'category_id' => isset($post_data['category_id']) ? (int) $post_data['category_id'] : null,
            'occurred_at' => isset($post_data['occurred_at']) ? $post_data['occurred_at'] : '',
            'notes' => isset($post_data['notes']) ? trim($post_data['notes']) : ''
        ];

        // Validation
        if (empty($data['title']) || $data['amount'] <= 0 || !in_array($data['type'], ['income', 'expense']) || empty($data['occurred_at'])) {
            echo json_encode(['status' => 'error', 'message' => 'Title, valid amount, type (income/expense), and date are required']);
            return;
        }

        // Ensure Transaction_model is loaded
        $this->load->model('Transaction_model');
        $created = $this->Transaction_model->create($data);

        if ($created) {
            echo json_encode(['status' => 'success', 'message' => 'Transaction created']);
        } else {
            log_message('error', 'Transaction create failed: ' . json_encode($data));
            echo json_encode(['status' => 'error', 'message' => 'Create failed']);
        }
    }

    public function edit($id = null)
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction ID required']);
            return;
        }

        // Handle JSON or POST input
        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: $this->input->post();

        $data = [
            'title' => isset($post_data['title']) ? trim($post_data['title']) : '',
            'amount' => isset($post_data['amount']) ? (float) $post_data['amount'] : 0,
            'type' => isset($post_data['type']) ? $post_data['type'] : '',
            'category_id' => isset($post_data['category_id']) ? ($post_data['category_id'] === null ? null : (int) $post_data['category_id']) : null,
            'occurred_at' => isset($post_data['occurred_at']) ? $post_data['occurred_at'] : '',
            'notes' => isset($post_data['notes']) ? trim($post_data['notes']) : ''
        ];

        // Validation
        if (empty($data['title']) || $data['amount'] <= 0 || !in_array($data['type'], ['income', 'expense']) || empty($data['occurred_at'])) {
            echo json_encode(['status' => 'error', 'message' => 'Title, valid amount, type (income/expense), and date are required']);
            return;
        }

        // Verify transaction exists and belongs to user
        $this->load->model('Transaction_model');
        $transaction = $this->Transaction_model->get_transaction($id);
        if (!$transaction || $transaction->user_id != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized or transaction not found']);
            return;
        }

        // Update transaction
        try {
            $updated = $this->Transaction_model->update_transaction($id, $data);
            if ($updated) {
                echo json_encode(['status' => 'success', 'message' => 'Transaction updated']);
            } else {
                log_message('error', 'Transaction update failed: ID=' . $id . ', Data=' . json_encode($data));
                echo json_encode(['status' => 'error', 'message' => 'Update failed']);
            }
        } catch (Exception $e) {
            log_message('error', 'Transaction edit error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }


    public function delete($id = null)
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction ID required']);
            return;
        }

        $transaction = $this->Transaction_model->get_transaction($id);
        if (!$transaction || $transaction->user_id != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized or transaction not found']);
            return;
        }

        if ($this->Transaction_model->delete_transaction($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Transaction deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
        }
    }

    public function search()
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');

        // Handle JSON or POST input
        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: $this->input->post();

        $filters = [
            'search' => isset($post_data['search']) ? trim($post_data['search']) : '',
            'type' => isset($post_data['type']) ? $post_data['type'] : '',
            'category_id' => isset($post_data['category_id']) ? ($post_data['category_id'] ? (int) $post_data['category_id'] : '') : '',
            'start_date' => isset($post_data['start_date']) ? $post_data['start_date'] : '',
            'end_date' => isset($post_data['end_date']) ? $post_data['end_date'] : '',
            'page' => max(1, (int) ($post_data['page'] ?? 1)),
            'per_page' => max(1, (int) ($post_data['per_page'] ?? 10))
        ];

        try {
            $total_rows = $this->Transaction_model->count_transactions($user_id, $filters);
            $transactions = $this->Transaction_model->search_transactions($user_id, $filters, $filters['per_page'], ($filters['page'] - 1) * $filters['per_page']);

            $response = [
                'status' => 'success',
                'transactions' => is_array($transactions) ? $transactions : [],
                'current_page' => $filters['page'],
                'total_pages' => ceil($total_rows / $filters['per_page']) ?: 1
            ];
            echo json_encode($response);
        } catch (Exception $e) {
            log_message('error', 'Search transactions error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error searching transactions']);
        }
    }
}