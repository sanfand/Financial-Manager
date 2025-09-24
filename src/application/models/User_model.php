<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    public function __construct() {
        parent::__construct();
    }

    public function register($data) {
        return $this->db->insert('users', $data);
    }

    public function get_by_email_or_username($login) {
        return $this->db
            ->where('email', $login)
            ->or_where('name', $login)
            ->get('users')
            ->row();
    }


    public function get_user_by_id($user_id) {
        return $this->db->get_where('users', ['id' => $user_id])->row();
    }

    public function update_user($user_id, $data) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', $data);
    }

    public function update_profile_pic($user_id, $filename) {
        return $this->db->update('users', ['profile_pic' => $filename, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $user_id]);
    }
}
