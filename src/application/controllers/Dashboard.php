<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        $this->load->model('Transaction_model');
        $this->load->model('Category_model');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        
        $data['title'] = 'Dashboard';
        $data['recent_transactions'] = $this->Transaction_model->get_recent_transactions($user_id, 6);
        $data['summary'] = $this->Transaction_model->get_financial_summary($user_id);
        $data['monthly_trends'] = $this->Transaction_model->get_monthly_trends($user_id);
        
        $this->load->view('header', $data);
        $this->load->view('dashboard', $data);
        $this->load->view('footer');
    }

    public function get_chart_data() {
        $user_id = $this->session->userdata('user_id');
        $data = $this->Transaction_model->get_chart_data($user_id);
        echo json_encode($data);
    }
}