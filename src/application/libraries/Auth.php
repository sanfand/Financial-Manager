<?php

class Auth {

    public static function authenticate(): bool|int
    {
        $c = &get_instance();
        $c->load->model('Transaction_model');
        $c->load->model('Token_model');
        $c->load->model('User_model');

        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? '';
        
        if (!preg_match('/Bearer\s+(\S+)/', $auth_header, $matches)) {
            // $this->output->set_content_type('application/json')->set_status_header(401);
            // echo json_encode(['status' => 'error', 'message' => 'Token required']);
            // exit;
            return false;
        }
        
        $token = $matches[1];
        $user_id = $c->Token_model->verify($token);
        
        if (!$user_id) {
            // $this->output->set_content_type('application/json')->set_status_header(401);
            // echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
            // exit;
            return false;
        }

        return $user_id;
    }
}

?>