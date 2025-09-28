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
        $data = [
            'title' => 'Financial Dashboard',
            'summary' => $this->Transaction_model->get_financial_summary($user_id),
            'recent_transactions' => $this->Transaction_model->get_recent_transactions($user_id, 6)
        ];
        $this->load->view('dashboard', $data);
    }

    public function get_chart_data() {
        if ($this->input->is_ajax_request()) {
            $user_id = $this->session->userdata('user_id');
            $chart_data = $this->Transaction_model->get_chart_data($user_id);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($chart_data));
        } else {
            show_404();
        }
    }
}