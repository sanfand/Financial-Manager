<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    private function generate_token() {
        return bin2hex(random_bytes(16)); //  32 bytes for tokens
    }

    // Token_model.php
    public function create($user_id) {
        log_message('debug', 'Create called with user_id: ' . $user_id);
        if (!$user_id || !is_numeric($user_id)) {
            log_message('error', 'Invalid user_id for token create: ' . $user_id);
            return false;
        }

        $token = $this->generate_token();
        $now = date('Y-m-d H:i:s');
        $expires_at = date('Y-m-d H:i:s', strtotime($now . ' +1 hour'));

        $data = [
            'user_id' => (int) $user_id,
            'token' => $token,
            'created_at' => $now,
            'expires_at' => $expires_at,
            'is_valid' => 1
        ];

        log_message('debug', 'Insert data: ' . json_encode($data));

        // Invalidate old tokens for this user
        $this->db->where('user_id', $user_id);
        $this->db->where('is_valid', 1);
        $this->db->update('tokens', ['is_valid' => 0]);
        log_message('debug', 'Invalidated old tokens: ' . $this->db->affected_rows() . ' rows');

        // Insert new token
        $this->db->insert('tokens', $data);
        $insert_id = $this->db->insert_id();
        $error = $this->db->error();

        log_message('debug', 'Insert result: ID=' . $insert_id . ', Error=' . json_encode($error));

        if ($error['code'] != 0) {
            log_message('error', 'Token insert failed: ' . $error['message']);
            return false;
        }

        log_message('debug', 'Token created and returning: ' . $token);
        return $token;
        }

    public function verify($token) {
    log_message('debug', 'Verifying token: ' . $token);
    
    $this->db->where('token', $token);
    $this->db->where('is_valid', 1);
    $this->db->where('expires_at >', date('Y-m-d H:i:s'));
    $query = $this->db->get('tokens');
    
    log_message('debug', 'Token query result: ' . $query->num_rows() . ' rows');
    
    if ($query->num_rows() > 0) {
        $token_data = $query->row();
        log_message('debug', 'Token valid for user_id: ' . $token_data->user_id);
        return $token_data->user_id;
    }
    
    log_message('debug', 'Token invalid or expired');
    return false;
    }
    
    public function invalidate_user($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->update('tokens', ['is_valid' => 0]);
        return $this->db->affected_rows() > 0;
    }
    
    public function cleanup_expired() {
        $this->db->where('expires_at <', date('Y-m-d H:i:s'));
        $this->db->or_where('is_valid', 0);
        $this->db->delete('tokens');
        return $this->db->affected_rows();
    }
}