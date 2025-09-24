<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        $this->load->model('Transaction_model');
        $this->load->model('Category_model');
        $this->load->library(['form_validation', 'pagination']);
        $this->load->helper(['url', 'form']);
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        $data['title'] = 'Transactions';

        // Compute offset explicitly before pagination
        $raw_offset = $this->uri->segment(3, '0'); // Default to '0' if segment 3 is missing
        $offset = (string)$raw_offset === '0' || ctype_digit((string)$raw_offset) ? (int)$raw_offset : 0;

        $total_rows = (int)$this->Transaction_model->count_transactions($user_id) ?: 0;

        $config = [
            'base_url'      => base_url('transactions/index'),
            'total_rows'    => $total_rows,
            'per_page'      => 6,
            'uri_segment'   => 3,
            'first_url'     => base_url('transactions/index/0'), // Ensure first page uses offset 0
            'offset'        => $offset, // Explicitly set the computed offset
            'full_tag_open' => '<ul class="pagination">',
            'full_tag_close'=> '</ul>',
            'first_tag_open'=> '<li class="page-item">',
            'first_tag_close'=> '</li>',
            'last_tag_open' => '<li class="page-item">',
            'last_tag_close'=> '</li>',
            'next_tag_open' => '<li class="page-item">',
            'next_tag_close'=> '</li>',
            'prev_tag_open' => '<li class="page-item">',
            'prev_tag_close'=> '</li>',
            'cur_tag_open'  => '<li class="page-item active"><span class="page-link">',
            'cur_tag_close' => '</span></li>',
            'num_tag_open'  => '<li class="page-item">',
            'num_tag_close' => '</li>', 
            'attributes'    => ['class' => 'page-link']
        ];

        $this->pagination->initialize($config);
        $data['links'] = $this->pagination->create_links();

        $data['transactions'] = $this->Transaction_model->get_transactions($user_id, $config['per_page'], $offset);
        $data['categories']   = $this->Category_model->get_categories($user_id);

        $this->load->view('header', $data);
        $this->load->view('transactions', $data);
        $this->load->view('footer');
    }


    public function create() {
        $user_id = $this->session->userdata('user_id');
        $this->_validate_transaction();

        $data = [
            'user_id' => $user_id,
            'title' => $this->input->post('title'),
            'amount' => $this->input->post('amount'),
            'type' => $this->input->post('type'),
            'category_id' => $this->input->post('category_id'),
            'occurred_at' => date('Y-m-d H:i:s', strtotime($this->input->post('occurred_at'))),
            'notes' => $this->input->post('notes'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->Transaction_model->create($data)) {
            echo json_encode(['status' => 'success', 'message' => 'Transaction added successfully']);
        } else {
            $error = $this->db->error();
            echo json_encode(['status' => 'error', 'message' => $error['message']]);
        }
    }

    public function edit($id) {
        $transaction = $this->_get_user_transaction($id);
        if (!$transaction) return;

        $this->_validate_transaction();

        $data = [
            'title' => $this->input->post('title'),
            'amount' => $this->input->post('amount'),
            'type' => $this->input->post('type'),
            'category_id' => $this->input->post('category_id'),
            'occurred_at' => date('Y-m-d H:i:s', strtotime($this->input->post('occurred_at'))),
            'notes' => $this->input->post('notes'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->Transaction_model->update_transaction($id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Transaction updated successfully']);
        } else {
            $error = $this->db->error();
            echo json_encode(['status' => 'error', 'message' => $error['message']]);
        }
    }

    public function delete($id) {
        $transaction = $this->_get_user_transaction($id);
        if (!$transaction) return;

        if ($this->Transaction_model->delete_transaction($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Transaction deleted successfully']);
        } else {
            $error = $this->db->error();
            echo json_encode(['status' => 'error', 'message' => $error['message']]);
        }
    }

    public function get_transaction($id) {
        $transaction = $this->_get_user_transaction($id);
        if (!$transaction) return;
        echo json_encode($transaction);
    }

    public function search() {
        $user_id = $this->session->userdata('user_id');
        $filters = [
            'search' => $this->input->get('search'),
            'type' => $this->input->get('type'),
            'category_id' => $this->input->get('category_id'),
            'start_date' => $this->input->get('start_date') ? date('Y-m-d 00:00:00', strtotime($this->input->get('start_date'))) : null,
            'end_date' => $this->input->get('end_date') ? date('Y-m-d 23:59:59', strtotime($this->input->get('end_date'))) : null,
        ];
        $transactions = $this->Transaction_model->get_transactions($user_id, null, null, $filters);
        echo json_encode(['status' => 'success', 'data' => $transactions]);
    }

    public function valid_date($date) {
        if (!$date) return true;
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function _validate_transaction() {
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('amount', 'Amount', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('type', 'Type', 'required|in_list[income,expense]');
        $this->form_validation->set_rules('category_id', 'Category', 'required|integer');
        $this->form_validation->set_rules('occurred_at', 'Date', 'required|callback_valid_date');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => strip_tags(validation_errors())]);
            exit;
        }
    }

    private function _get_user_transaction($id) {
        $user_id = $this->session->userdata('user_id');
        $transaction = $this->Transaction_model->get_transaction($id);
        if (!$transaction || $transaction->user_id != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
            return false;
        }
        return $transaction;
    }
}
