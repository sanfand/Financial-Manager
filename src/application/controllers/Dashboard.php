<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Transaction_model');
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
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $this->load->model('Transaction_model');

        // Calculate summary
        $this->db->select('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income, SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense');
        $this->db->where('user_id', $user_id);
        $summary = $this->db->get('transactions')->row();

        $income = $summary ? (float) $summary->income : 0;
        $expense = $summary ? (float) $summary->expense : 0;

        // Get recent transactions
        $this->db->select('t.*, c.name as category_name');
        $this->db->from('transactions t');
        $this->db->join('categories c', 'c.id = t.category_id', 'left');
        $this->db->where('t.user_id', $user_id);
        $this->db->order_by('t.occurred_at', 'DESC');
        $this->db->limit(5);
        $recent_transactions = $this->db->get()->result();

        echo json_encode([
            'status' => 'success',
            'summary' => [
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense
            ],
            'recent_transactions' => $recent_transactions
        ]);
    }

    public function get_chart_data()
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');
        $chart_data = $this->Transaction_model->get_chart_data($user_id);

        $chart_data = [
            'labels' => isset($chart_data['labels']) && is_array($chart_data['labels']) ? $chart_data['labels'] : [],
            'income' => isset($chart_data['income']) && is_array($chart_data['income']) ? array_map('floatval', $chart_data['income']) : [],
            'expense' => isset($chart_data['expense']) && is_array($chart_data['expense']) ? array_map('floatval', $chart_data['expense']) : []
        ];

        $count = max(count($chart_data['labels']), count($chart_data['income']), count($chart_data['expense']));
        $chart_data['labels'] = array_pad($chart_data['labels'], $count, '');
        $chart_data['income'] = array_pad($chart_data['income'], $count, 0.0);
        $chart_data['expense'] = array_pad($chart_data['expense'], $count, 0.0);

        log_message('debug', 'Chart data: ' . json_encode($chart_data));

        echo json_encode(['status' => 'success', 'data' => $chart_data]);
    }
}