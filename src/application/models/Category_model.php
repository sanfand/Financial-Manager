<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // Create new category
    public function create_category($data) {
        return $this->db->insert('categories', $data);
    }

    // Get categories for user and global categories
    public function get_categories($user_id, $limit = null, $offset = null) {
        $this->db->group_start()
                 ->where('user_id', $user_id)
                 ->or_where('user_id', null)
                 ->group_end();
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get('categories')->result();
    }

    // Count total categories for pagination
    public function count_categories($user_id) {
        $this->db->group_start()
                 ->where('user_id', $user_id)
                 ->or_where('user_id', null)
                 ->group_end();
        return $this->db->count_all_results('categories');
    }

    // Get single category by ID (needed for edit modal)
    public function get_category($id) {
        $query = $this->db->get_where('categories', ['id' => $id]);
        return $query->row();
    }

    // Update category
    public function update_category($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('categories', $data);
    }

    // Delete category
    public function delete_category($id) {
        $this->db->where('id', $id);
        return $this->db->delete('categories');
    }
}
