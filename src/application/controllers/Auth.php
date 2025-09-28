<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model'); // we'll use this for DB operations
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
    }

    // Show login page
    public function login() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        if ($this->input->is_ajax_request()) {
            $usernameEmail = $this->input->post('username_email', TRUE);
            $password = $this->input->post('password', TRUE);

            if (!$usernameEmail || !$password) {
                echo json_encode(['status' => 'error', 'message' => 'Username/Email and password are required.']);
                return;
            }

            $user = $this->User_model->get_by_email_or_username($usernameEmail);
            if ($user && password_verify($password, $user->password_hash)) {
                $this->session->set_userdata([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'logged_in' => TRUE
                ]);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
            }
            return;
        }

        $this->load->view('login'); // login.php in view root
    }

    // Show register page
    public function register() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        if ($this->input->is_ajax_request()) {
            $name = $this->input->post('name', TRUE);
            $email = $this->input->post('email', TRUE);
            $password = $this->input->post('password', TRUE);
            $passwordConfirm = $this->input->post('password_confirm', TRUE);

            if (!$name || !$email || !$password || !$passwordConfirm) {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
                return;
            }

            if ($password !== $passwordConfirm) {
                echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
                return;
            }

            if ($this->User_model->get_by_email_or_username($email)) {
                echo json_encode(['status' => 'error', 'message' => 'Email or username already exists.']);
                return;
            }

            $userData = [
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $inserted = $this->User_model->register($userData);
            if ($inserted) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Registration failed.']);
            }
            return;
        }

        $this->load->view('register'); // register.php in view root
    }

    // Update user profile
    public function update_profile() {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        if ($this->input->is_ajax_request()) {
            $user_id = $this->session->userdata('user_id');
            $name = $this->input->post('name', TRUE);
            $email = $this->input->post('email', TRUE);

            if (!$name || !$email) {
                echo json_encode(['status' => 'error', 'message' => 'Name and email are required.']);
                return;
            }

            if ($this->User_model->get_by_email_or_username($email) && 
                $this->User_model->get_by_email_or_username($email)->id != $user_id) {
                echo json_encode(['status' => 'error', 'message' => 'Email or username already exists.']);
                return;
            }

            $userData = [
                'name' => $name,
                'email' => $email,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->User_model->update_user($user_id, $userData);
            if ($updated) {
                $this->session->set_userdata([
                    'name' => $name,
                    'email' => $email
                ]);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Profile update failed.']);
            }
            return;
        }

        $this->load->view('profile'); // profile.php in view root
    }

    // Update profile picture
    public function update_profile_picture() {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        if ($this->input->is_ajax_request()) {
            $user_id = $this->session->userdata('user_id');
            $config['upload_path'] = './uploads/profile_pics/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048; // 2MB
            $config['file_name'] = 'profile_' . $user_id . '_' . time();

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('profile_pic')) {
                echo json_encode(['status' => 'error', 'message' => $this->upload->display_errors('', '')]);
                return;
            }

            $upload_data = $this->upload->data();
            $filename = $upload_data['file_name'];

            $updated = $this->User_model->update_profile_pic($user_id, $filename);
            if ($updated) {
                echo json_encode(['status' => 'success', 'filename' => $filename]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Profile picture update failed.']);
            }
            return;
        }

        $this->load->view('profile'); // profile.php in view root
    }

    // Logout
    public function logout() {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}