<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Transaction_model');
        $this->load->library('session');
        $this->load->helper('url');
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        $summary = $this->Transaction_model->get_financial_summary($user_id);
        $recent_transactions = $this->Transaction_model->get_recent_transactions($user_id, 6);

        // Validate and cast summary values
        $data = [
            'title' => 'Financial Dashboard',
            'summary' => [
                'income' => isset($summary['income']) ? floatval($summary['income']) : 0.0,
                'expense' => isset($summary['expense']) ? floatval($summary['expense']) : 0.0,
                'balance' => isset($summary['balance']) ? floatval($summary['balance']) : 0.0
            ],
            'recent_transactions' => is_array($recent_transactions) ? array_map(function($t) {
                $t->amount = floatval($t->amount ?? 0);
                return $t;
            }, $recent_transactions) : []
        ];

        // Log data for debugging
        log_message('debug', 'Dashboard index - Summary: ' . json_encode($data['summary']));
        log_message('debug', 'Dashboard index - Recent Transactions: ' . json_encode($data['recent_transactions']));

        $this->load->view('dashboard', $data);
    }

    public function get_chart_data() {
        if ($this->input->is_ajax_request()) {
            $user_id = $this->session->userdata('user_id');
            $chart_data = $this->Transaction_model->get_chart_data($user_id);

            // Validate and cast chart data
            $chart_data = [
                'labels' => isset($chart_data['labels']) && is_array($chart_data['labels']) ? $chart_data['labels'] : [],
                'income' => isset($chart_data['income']) && is_array($chart_data['income']) ? array_map('floatval', $chart_data['income']) : [],
                'expense' => isset($chart_data['expense']) && is_array($chart_data['expense']) ? array_map('floatval', $chart_data['expense']) : []
            ];

            // Ensure arrays have consistent lengths
            $count = max(count($chart_data['labels']), count($chart_data['income']), count($chart_data['expense']));
            $chart_data['labels'] = array_pad($chart_data['labels'], $count, '');
            $chart_data['income'] = array_pad($chart_data['income'], $count, 0.0);
            $chart_data['expense'] = array_pad($chart_data['expense'], $count, 0.0);

            // Log chart data for debugging
            log_message('debug', 'Chart data: ' . json_encode($chart_data));

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($chart_data));
        } else {
            show_404();
        }
    }
}