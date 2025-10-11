<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Load models, libraries, helpers
        $this->load->model('User_model');
        $this->load->model('Token_model');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form']);

        // CORS headers for dev
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    // handle authentication
    public function authenticate() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (!preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            return false;
        }

        $token = $matches[1];
        
        // Load the Token_model if not already loaded
        $CI =& get_instance();
        $CI->load->model('Token_model');
        $userId = $CI->Token_model->verify($token);

        return $userId ? $userId : false;
    }


    // LOGIN
    public function login()
    {
        $this->output->set_content_type('application/json');

        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true);

        $usernameEmail = $post_data['username_email'] ?? '';
        $password = $post_data['password'] ?? '';

        if (empty($usernameEmail) || empty($password)) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Name/Email and password are required']);
            return;
        }

        $user = $this->User_model->get_by_email_or_name($usernameEmail);

        if ($user) {
            $password_verified = false;

            if (password_verify($password, $user->password_hash)) {
                $password_verified = true;
            } else if ($user->password_hash === $password || strlen($user->password_hash) < 60) {
                // Plain text migration
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $this->User_model->update_user($user->id, ['password_hash' => $hashed_password]);
                $password_verified = true;
            }

            if ($password_verified) {
                $token = $this->Token_model->create($user->id);

                if ($token) {
                    $this->output->set_status_header(200);
                    echo json_encode([
                        'status' => 'success',
                        'token' => $token,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'profile_pic' => $user->profile_pic ? base_url($user->profile_pic) : null
                        ]
                    ]);
                    return;
                } else {
                    $this->output->set_status_header(500);
                    echo json_encode(['status' => 'error', 'message' => 'Token generation failed']);
                    return;
                }
            }
        }

        $this->output->set_status_header(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }

    // REGISTER
    public function register()
    {
        $this->output->set_content_type('application/json');

        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true);

        $name = $post_data['name'] ?? '';
        $email = $post_data['email'] ?? '';
        $password = $post_data['password'] ?? '';
        $password_confirm = $post_data['password_confirm'] ?? '';

        if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            return;
        }

        if ($password !== $password_confirm) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
            return;
        }

        if ($this->User_model->get_by_email_or_name($email)) {
            $this->output->set_status_header(409);
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
            return;
        }

        if ($this->User_model->get_by_email_or_name($name)) {
            $this->output->set_status_header(409);
            echo json_encode(['status' => 'error', 'message' => 'Name already exists']);
            return;
        }

        $userData = [
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $registered = $this->User_model->register($userData);

        if ($registered) {
            $this->output->set_status_header(201);
            echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
        } else {
            $this->output->set_status_header(500);
            echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
        }
    }

    // LOGOUT
    public function logout()
    {
        $this->output->set_content_type('application/json');
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s+(\S+)/', $auth_header, $matches)) {
            $token = $matches[1];
            $user_id = $this->Token_model->verify($token);
            if ($user_id) {
                $this->Token_model->invalidate_user($user_id);
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
    }

    
}
