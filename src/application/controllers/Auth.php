<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Prevent session errors by checking for premature output
        if (ob_get_length()) {
            log_message('error', 'Output detected before session: ' . ob_get_contents());
            ob_end_clean();
        }
        $this->load->model('User_model');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form']);
        // Log request details for debugging
        log_message('debug', 'Request URI: ' . $this->uri->uri_string());
        log_message('debug', 'Request Headers: ' . print_r(getallheaders(), TRUE));
    }

    public function login()
    {
        $this->output->set_header('Access-Control-Allow-Credentials: true');
        $this->output->set_header('Access-Control-Allow-Origin: http://localhost:5173'); // your frontend URL
        
        if ($this->session->userdata('logged_in')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Already logged in']));
            return;
        }

        if ($this->input->is_ajax_request()) {
            $this->form_validation->set_rules('username_email', 'Username/Email', 'required|trim');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if ($this->form_validation->run() === FALSE) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => strip_tags(validation_errors())]));
                return;
            }

            $usernameEmail = $this->input->post('username_email', TRUE);
            $password = $this->input->post('password', TRUE);

            $user = $this->User_model->get_by_email_or_username($usernameEmail);
            if ($user && password_verify($password, $user->password_hash)) {
                $this->session->set_userdata([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'logged_in' => TRUE
                ]);
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'success']));
            } else {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid credentials.']));
            }
            return;
        }

        $this->load->view('login');
    }

    public function register()
    {
        $this->output->set_content_type('application/json');

        // Log raw input for debugging
        $raw_input = file_get_contents('php://input');
        log_message('debug', 'Raw Input: ' . $raw_input);
        log_message('debug', 'POST Data: ' . print_r($this->input->post(), TRUE));

        if ($this->session->userdata('logged_in')) {
            $this->output->set_output(json_encode(['status' => 'success', 'message' => 'Already logged in']));
            return;
        }

        // Handle JSON input for application/json requests
        $post_data = json_decode($raw_input, TRUE);
        if ($post_data === null && json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'JSON decode error: ' . json_last_error_msg());
            $post_data = $this->input->post(); // Fallback to standard POST
        }

        // Set validation data from JSON or POST
        $this->form_validation->set_data($post_data ?: $this->input->post());

        // Validation rules
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->output->set_output(json_encode(['status' => 'error', 'message' => strip_tags(validation_errors())]));
            return;
        }

        // Use JSON data if available, else fallback to POST
        $name = $post_data['name'] ?? $this->input->post('name', TRUE);
        $email = $post_data['email'] ?? $this->input->post('email', TRUE);
        $password = $post_data['password'] ?? $this->input->post('password', TRUE);

        // Check if email already exists
        if ($this->User_model->get_by_email_or_username($email)) {
            $this->output->set_output(json_encode(['status' => 'error', 'message' => 'Email already exists.']));
            return;
        }

        // Prepare user data
        $userData = [
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Log user data for debugging
        log_message('debug', 'User data for registration: ' . print_r($userData, TRUE));

        // Attempt registration
        $registered = $this->User_model->register($userData);
        if ($registered) {
            $this->output->set_output(json_encode(['status' => 'success']));
        } else {
            $db_error = $this->db->error();
            $error_msg = $db_error['message'] ?? 'Unknown database error';
            log_message('error', 'Registration failed. DB Error: ' . $error_msg);
            $this->output->set_output(json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $error_msg]));
        }
    }

    public function check()
    {
        $this->output->set_content_type('application/json');
        $isLoggedIn = $this->session->userdata('logged_in') ? true : false;
        $this->output->set_output(json_encode(['status' => 'success', 'is_logged_in' => $isLoggedIn]));
    }

    public function logout()
    {
        $this->output->set_content_type('application/json');
        $this->session->sess_destroy();
        $this->output->set_output(json_encode(['status' => 'success']));
    }
}