<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
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
        $user = $this->User_model->get_user_by_id($user_id);
        if ($user) {
            // Ensure profile_pic includes full URL
            if (!empty($user['profile_pic'])) {
                $user['profile_pic'] = base_url($user['profile_pic']);
            }
            echo json_encode(['status' => 'success', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    }

    public function update_profile()
    {
        $this->output->set_content_type('application/json');
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        // Handle JSON or POST input
        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true) ?: $this->input->post();

        $name = isset($post_data['name']) ? trim($post_data['name']) : $this->input->post('name', TRUE);
        $email = isset($post_data['email']) ? trim($post_data['email']) : $this->input->post('email', TRUE);
        $password = isset($post_data['password_hash']) ? $post_data['password_hash'] : $this->input->post('password_hash', TRUE);

        if (empty($name) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            return;
        }

        $update_data = [
            'name' => $name,
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($password)) {
            $update_data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Handle file upload
        if (!empty($_FILES['profile_pic']['name'])) {
            $config['upload_path'] = './uploads/profile_pics/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048;
            $config['file_name'] = time() . '_profile_' . $user_id;
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('profile_pic')) {
                echo json_encode(['status' => 'error', 'message' => $this->upload->display_errors('', '')]);
                return;
            }

            $upload_data = $this->upload->data();
            $update_data['profile_pic'] = 'uploads/profile_pics/' . $upload_data['file_name'];
        }

        $success = $this->User_model->update_user($user_id, $update_data);

        if ($success) {
            $this->session->set_userdata(['name' => $name, 'email' => $email]);
            if (isset($update_data['profile_pic'])) {
                $this->session->set_userdata('profile_pic', $update_data['profile_pic']);
            }
            $user = $this->User_model->get_user_by_id($user_id);
            // Ensure profile_pic includes full URL
            if (!empty($user['profile_pic'])) {
                $user['profile_pic'] = base_url($user['profile_pic']);
            }
            echo json_encode(['status' => 'success', 'user' => $user]);
        } else {
            log_message('error', 'Profile update failed: User ID=' . $user_id . ', Data=' . json_encode($update_data));
            echo json_encode(['status' => 'error', 'message' => 'Profile update failed']);
        }
    }
}
