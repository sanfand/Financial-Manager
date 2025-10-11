<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    public function __construct() {
        parent::__construct();
    }

    public function register($data) {
        return $this->db->insert('users', $data);
    }

    public function get_by_email_or_name($login) {
        log_message('debug', 'Looking up user by: ' . $login);
        $this->db->where('email', $login);
        $this->db->or_where('name', $login);
        $query = $this->db->get('users');
        $result = $query->row();
        log_message('debug', 'User lookup result: ' . ($result ? json_encode($result) : 'No user found'));
        return $result;
        }

    public function get_user_by_id($user_id) {
        return $this->db->get_where('users', ['id' => $user_id])->row_array();
    }

    public function update_user($user_id, $data) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', $data);
    }

    public function update_profile_pic($user_id, $filename) {
        return $this->db->update('users', 
            ['profile_pic' => $filename, 'updated_at' => date('Y-m-d H:i:s')], 
            ['id' => $user_id]
        );
    }
    
    public function check_email_exists($email, $exclude_user_id = null) {
        $this->db->where('email', $email);
        if ($exclude_user_id) {
            $this->db->where('id !=', $exclude_user_id);
        }
        return $this->db->get('users')->row();
    }

    public function get_all_users() {
        return $this->db->get('users')->result();
    }
}