<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('User_model');
        $this->load->helper(array('url','form','security'));
    }

    public function login() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        if ($this->input->method() === 'post') {
            // Validation
            $this->form_validation->set_rules('login', 'Username or Email', 'required');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if ($this->form_validation->run() === FALSE) {
                $data['title'] = 'Login';
                $this->load->view('header', $data);
                $this->load->view('login', $data);
                $this->load->view('footer');
                return;
            }

            // Get input
            $login = $this->input->post('login', TRUE);
            $password = $this->input->post('password', TRUE);

            // Get user via model
            $user = $this->User_model->get_by_email_or_username($login);

            // Check password
            if (!$user || !password_verify($password, $user->password_hash)) {
                $this->session->set_flashdata('error', 'Invalid username/email or password');
                redirect('auth/login');
            }

            // Set session
            $session_data = [
                'user_id'   => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'logged_in' => TRUE
            ];
            $this->session->set_userdata($session_data);

            redirect('dashboard');
        }

        // GET request
        $data['title'] = 'Login';
        $this->load->view('header', $data);
        $this->load->view('login', $data);
        $this->load->view('footer');
    }



    public function register() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');

            if ($this->form_validation->run() === FALSE) {
                $data['title'] = 'Register';
                $this->load->view('header', $data);
                $this->load->view('register', $data);
                $this->load->view('footer');
                return;
            }

            $user_data = array(
                'name' => $this->input->post('name', TRUE),
                'email' => $this->input->post('email', TRUE),
                'password_hash' => password_hash($this->input->post('password', TRUE), PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            );

            if ($this->User_model->register($user_data)) {
                $this->session->set_flashdata('success', 'Registration successful. Please login.');
                redirect('auth/login');
            } else {
                $data['title'] = 'Register';
                $data['error'] = 'Registration failed. Please try again.';
                $this->load->view('header', $data);
                $this->load->view('register', $data);
                $this->load->view('footer');
            }
        } else {
            $data['title'] = 'Register';
            $this->load->view('header', $data);
            $this->load->view('register', $data);
            $this->load->view('footer');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth/login');
    }

    public function refresh_csrf() {
        echo json_encode(['token' => $this->security->get_csrf_hash()]);
    }
}