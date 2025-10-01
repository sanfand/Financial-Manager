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
        $this->load->library('pagination');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index()
    {
        $user_id = $this->session->userdata('user_id');
        $data['title'] = 'Transactions';

        $config['base_url'] = base_url('transactions/index');
        $config['total_rows'] = $this->Transaction_model->count_transactions($user_id);
        $config['per_page'] = 5;
        $config['use_page_numbers'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $this->pagination->initialize($config);

        $page = $this->uri->segment(3) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        $data['transactions'] = $this->Transaction_model->get_transactions($user_id, $config['per_page'], $offset);
        $data['categories'] = $this->Category_model->get_categories();
        $data['links'] = $this->pagination->create_links();

        if (!$data['transactions'] || !$data['categories']) {
            $this->session->set_flashdata('error', 'Failed to load data');
        }

        $this->load->view('transactions', $data);
    }

    public function create()
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');
        $data = [
            'user_id' => $user_id,
            'title' => $this->input->post('title'),
            'amount' => $this->input->post('amount'),
            'type' => $this->input->post('type'),
            'category_id' => $this->input->post('category_id') ?: null,
            'occurred_at' => $this->input->post('occurred_at'),
            'notes' => $this->input->post('notes'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['title']) || empty($data['amount']) || empty($data['type']) || empty($data['occurred_at'])) {
            echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
            return;
        }

        if (!in_array($data['type'], ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid transaction type']);
            return;
        }

        if ($this->Transaction_model->create($data)) {
            $id = $this->db->insert_id();
            $transaction = $this->Transaction_model->get_transaction($id);
            echo json_encode(['status' => 'success', 'message' => 'Transaction added', 'transaction' => $transaction]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add transaction']);
        }
    }

    public function edit()
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');
        $data = [
            'title' => $this->input->post('title'),
            'amount' => $this->input->post('amount'),
            'type' => $this->input->post('type'),
            'category_id' => $this->input->post('category_id') ?: null,
            'occurred_at' => $this->input->post('occurred_at'),
            'notes' => $this->input->post('notes'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id) || empty($data['title']) || empty($data['amount']) || empty($data['type']) || empty($data['occurred_at'])) {
            echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
            return;
        }

        if (!in_array($data['type'], ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid transaction type']);
            return;
        }

        // Verify transaction belongs to user
        $transaction = $this->Transaction_model->get_transaction($id);
        if (!$transaction || $transaction->user_id != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized or transaction not found']);
            return;
        }

        if ($this->Transaction_model->update_transaction($id, $data)) {
            $updated_transaction = $this->Transaction_model->get_transaction($id);
            echo json_encode(['status' => 'success', 'message' => 'Transaction updated', 'transaction' => $updated_transaction]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
        }
    }

    public function delete()
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');
        $id = $this->input->post('id');

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction ID required']);
            return;
        }

        // Verify transaction belongs to user
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
        if ($this->input->is_ajax_request()) {
            $user_id = $this->session->userdata('user_id');
            $filters = json_decode($this->input->raw_input_stream, true);
            
            $filters = [
                'search' => isset($filters['search']) ? $filters['search'] : '',
                'type' => isset($filters['type']) ? $filters['type'] : '',
                'category_id' => isset($filters['category_id']) ? $filters['category_id'] : '',
                'start_date' => isset($filters['start_date']) ? $filters['start_date'] : '',
                'end_date' => isset($filters['end_date']) ? $filters['end_date'] : ''
            ];
            try {
                $transactions = $this->Transaction_model->search_transactions($user_id, $filters);
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'success', 'transactions' => $transactions]));
            } catch (Exception $e) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'An error occurred while searching transactions']));
            }
        } else {
            show_404();
        }
    }
}