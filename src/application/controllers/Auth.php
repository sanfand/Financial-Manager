<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Token_model');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form']);

        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    public function login()
    {
        $this->output->set_content_type('application/json');
        
        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true);
        
        $usernameEmail = $post_data['username_email'] ?? '';
        $password = $post_data['password'] ?? '';

        log_message('debug', '=== LOGIN START ===');
        log_message('debug', 'Login attempt for: ' . $usernameEmail);

        if (empty($usernameEmail) || empty($password)) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Name/Email and password are required']);
            return;
        }

        $user = $this->User_model->get_by_email_or_name($usernameEmail);
        
        if ($user) {
            log_message('debug', 'User found - ID: ' . $user->id . ', Name: ' . $user->name . ', Email: ' . $user->email);
            log_message('debug', 'Password hash length: ' . strlen($user->password_hash));
            
            $password_verified = false;
            
            // Try password_verify first
            if (password_verify($password, $user->password_hash)) {
                log_message('debug', 'Password verified via password_verify()');
                $password_verified = true;
            } 
            // If that fails, check plain text match (for migration)
            else if ($user->password_hash === $password) {
                log_message('debug', 'Password matches plain text - migrating to hash');
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $this->User_model->update_user($user->id, ['password_hash' => $hashed_password]);
                $password_verified = true;
            }
            // If still fails, check if it's a different plain text scenario
            else if (strlen($user->password_hash) < 60) {
                log_message('debug', 'Password appears to be stored as plain text');
                if ($user->password_hash === $password) {
                    log_message('debug', 'Plain text password matches - migrating to hash');
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $this->User_model->update_user($user->id, ['password_hash' => $hashed_password]);
                    $password_verified = true;
                }
            }

            if ($password_verified) {
                log_message('debug', 'Password verification SUCCESS - generating token');
                
                $token = $this->Token_model->create($user->id);
                log_message('debug', 'Token generation result: ' . ($token ? 'SUCCESS' : 'FAILED'));
                
                if ($token) {
                    log_message('debug', 'Token generated: ' . $token);
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
                    log_message('debug', '=== LOGIN SUCCESS ===');
                    return;
                } else {
                    log_message('error', 'Token generation failed for user: ' . $user->id);
                    $this->output->set_status_header(500);
                    echo json_encode(['status' => 'error', 'message' => 'Token generation failed']);
                    return;
                }
            } else {
                log_message('debug', 'Password verification FAILED');
                log_message('debug', 'Provided: "' . $password . '"');
                log_message('debug', 'Stored: "' . $user->password_hash . '"');
            }
        } else {
            log_message('debug', 'No user found for: ' . $usernameEmail);
        }
        
        log_message('debug', '=== LOGIN FAILED ===');
        $this->output->set_status_header(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }

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

        if (strlen($password) < 6) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
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
            $db_error = $this->db->error();
            $error_msg = $db_error['message'] ?? 'Unknown database error';
            log_message('error', 'Registration failed. DB Error: ' . $error_msg);
            $this->output->set_status_header(500);
            echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $error_msg]);
        }
    }

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

    public function create_test_user() {
        $this->output->set_content_type('application/json');
        
        $name = 'sana';
        $email = 'sana@test.com';
        $password = '1234567';
        
        if ($this->User_model->get_by_email_or_name($name)) {
            echo json_encode(['status' => 'error', 'message' => 'User already exists']);
            return;
        }
        
        $userData = [
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->User_model->register($userData)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Test user created',
                'user' => [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create user']);
        }
    }

    public function debug_users() {
        $this->output->set_content_type('application/json');
        $users = $this->User_model->get_all_users();
        
        $user_details = [];
        foreach ($users as $user) {
            $user_details[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password_hash' => $user->password_hash,
                'password_length' => strlen($user->password_hash ?? ''),
                'is_hashed' => (strlen($user->password_hash) === 60),
                'created_at' => $user->created_at
            ];
        }
        
        echo json_encode([
            'status' => 'success', 
            'users' => $user_details,
            'total_users' => count($user_details)
        ]);
    }
    
    public function reset_password() {
        $this->output->set_content_type('application/json');
        
        $raw_input = file_get_contents('php://input');
        $post_data = json_decode($raw_input, true);
        
        $email = $post_data['email'] ?? '';
        $new_password = $post_data['new_password'] ?? 'password';
        
        if (empty($email)) {
            $this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Email or name required']);
            return;
        }
        
        $user = $this->User_model->get_by_email_or_name($email);
        if (!$user) {
            $this->output->set_status_header(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            return;
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updated = $this->User_model->update_user($user->id, ['password_hash' => $hashed_password]);
        
        if ($updated) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Password reset successfully to: ' . $new_password
            ]);
        } else {
            $this->output->set_status_header(500);
            echo json_encode(['status' => 'error', 'message' => 'Password reset failed']);
        }
    }
    
    public function debug_tokens() {
        $this->output->set_content_type('application/json');
        $tokens = $this->db->get('tokens')->result();
        
        echo json_encode([
            'status' => 'success', 
            'tokens' => $tokens,
            'total_tokens' => count($tokens)
        ]);
    }
}