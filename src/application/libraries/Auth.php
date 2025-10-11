<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth {
    public function authenticate() {
        $CI =& get_instance();
        $CI->load->model('Token_model');
        
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (!preg_match('/Bearer\s+(\S+)/', $auth_header, $matches)) {
            return false;
        }

        $token = $matches[1];
        $user_id = $CI->Token_model->verify($token);

        return $user_id ? $user_id : false;
    }
}