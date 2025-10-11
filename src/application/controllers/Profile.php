<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends CI_Controller
{
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Token_model');
        $this->load->helper('url');
        $this->load->library("Auth");

        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->authenticate();
    }


    private function authenticate()
    {
    $this->load->library('Auth');
    $userId = $this->auth->authenticate();

    if(!$userId){
        $this->output->set_content_type('application/json')->set_status_header(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
        exit;
    }

    $this->user_id = $userId;
    }

    public function index()
    {
        $this->output->set_content_type('application/json');

        try {
            $user = $this->User_model->get_user_by_id($this->user_id);
            if ($user) {
                if (!empty($user['profile_pic'])) {
                    $user['profile_pic'] = base_url($user['profile_pic']);
                }
                echo json_encode(['status' => 'success', 'user' => $user]);
            } else {
                $this->output->set_status_header(404);
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
            }
        } catch (Exception $e) {
            log_message('error', 'Profile index error: ' . $e->getMessage());
            $this->output->set_status_header(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }

    public function update_profile()
    {
        $this->output->set_content_type('application/json');

        try {
            $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            if (strpos($content_type, 'multipart/form-data') !== false) {
                $name = $this->input->post('name', TRUE);
                $email = $this->input->post('email', TRUE);
                $password = $this->input->post('password_hash', TRUE);
            } else {
                $raw_input = file_get_contents('php://input');
                $post_data = json_decode($raw_input, true) ?: [];

                $name = isset($post_data['name']) ? trim($post_data['name']) : '';
                $email = isset($post_data['email']) ? trim($post_data['email']) : '';
                $password = isset($post_data['password_hash']) ? $post_data['password_hash'] : '';
            }

            if (empty($name) || empty($email)) {
                $this->output->set_status_header(400);
                echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->output->set_status_header(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
                return;
            }

            $existing_user = $this->User_model->check_email_exists($email, $this->user_id);
            if ($existing_user) {
                $this->output->set_status_header(400);
                echo json_encode(['status' => 'error', 'message' => 'Email already taken']);
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

            if (!empty($_FILES['profile_pic']['name'])) {
                $config['upload_path'] = './uploads/profile_pics/';
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $config['max_size'] = 2048;
                $config['file_name'] = time() . '_profile_' . $this->user_id;

                if (!is_dir($config['upload_path'])) {
                    mkdir($config['upload_path'], 0755, true);
                }

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('profile_pic')) {
                    $upload_data = $this->upload->data();
                    $update_data['profile_pic'] = 'uploads/profile_pics/' . $upload_data['file_name'];
                } else {
                    $this->output->set_status_header(400);
                    echo json_encode(['status' => 'error', 'message' => $this->upload->display_errors('', '')]);
                    return;
                }
            }

            $success = $this->User_model->update_user($this->user_id, $update_data);

            if ($success) {
                $user = $this->User_model->get_user_by_id($this->user_id);
                if (!empty($user['profile_pic'])) {
                    $user['profile_pic'] = base_url($user['profile_pic']);
                }
                echo json_encode(['status' => 'success', 'user' => $user, 'message' => 'Profile updated successfully']);
            } else {
                log_message('error', 'Profile update failed: User ID=' . $this->user_id . ', Data=' . json_encode($update_data));
                $this->output->set_status_header(500);
                echo json_encode(['status' => 'error', 'message' => 'Profile update failed']);
            }
        } catch (Exception $e) {
            log_message('error', 'Update profile error: ' . $e->getMessage());
            $this->output->set_status_header(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }
}