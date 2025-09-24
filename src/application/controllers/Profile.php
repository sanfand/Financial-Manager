<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        $this->load->model('User_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $user_id = $this->session->userdata('user_id');
        $data['title'] = 'Profile';
        $data['user'] = $this->User_model->get_user_by_id($user_id);
        
        $this->load->view('header', $data);
        $this->load->view('profile', $data);
        $this->load->view('footer');
    }

    public function update() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');

        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = [
            'name' => $this->input->post('name', true),
            'email' => $this->input->post('email', true),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->User_model->update_user($user_id, $data)) {
            $this->session->set_userdata(['name' => $data['name'], 'email' => $data['email']]);
            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
        }
    }

    public function update_password() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');

        $this->form_validation->set_rules('current_password', 'Current Password', 'required');
        $this->form_validation->set_rules('new_password', 'New Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[new_password]');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $user = $this->User_model->get_user_by_id($user_id);
        $current_password = $this->input->post('current_password');
        $new_password = $this->input->post('new_password');

        if (!password_verify($current_password, $user->password_hash)) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
            return;
        }

        $enc_password = password_hash($new_password, PASSWORD_DEFAULT);

        if ($this->User_model->update_user($user_id, ['password_hash' => $enc_password])) {
            echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
        }
    }

    public function upload_profile_pic() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');

        $config = [
            'upload_path' => './uploads/profile_pics/', 
            'allowed_types' => 'gif|jpg|png|jpeg',
            'max_size'     => 2048,
            'encrypt_name'  => TRUE

        ];

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('profile_pic')) {
            echo json_encode(['status' => 'error', 'message' => strip_tags($this->upload->display_errors())]);
        } else {
            $filename = $this->upload->data('file_name');
            if ($this->User_model->update_profile_pic($user_id, $filename)) {
                $this->session->set_userdata('profile_pic', $filename);
                echo json_encode(['status' => 'success', 'message' => 'Profile picture updated successfully', 'filename' => $filename]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update profile picture']);
            }
        }
    }
}
