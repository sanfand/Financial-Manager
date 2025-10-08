<?php
class Category_model extends CI_Model
{
    protected $table = 'categories';

    public function get_categories($user_id = null)
    {
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }
        $query = $this->db->get('categories');
        return $query->result();
    }

    public function count_categories($user_id = null)
    {
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    public function create_category($data)
    {
        if (empty($data['name']) || !in_array($data['type'], ['income', 'expense'])) {
            return ['status' => 'error', 'message' => 'Invalid name or type'];
        }
        $this->db->insert('categories', $data);
        $id = $this->db->insert_id();
        $this->db->where('id', $id);
        $query = $this->db->get('categories');
        return [
            'status' => 'success',
            'category' => $query->row()
        ];
    }

    public function update_category($data)
    {
        if (empty($data['id']) || empty($data['name']) || !in_array($data['type'], ['income', 'expense'])) {
            return ['status' => 'error', 'message' => 'Invalid data'];
        }
        $this->db->where('id', $data['id']);
        $this->db->where('user_id', $data['user_id']);
        $this->db->update('categories', ['name' => $data['name'], 'type' => $data['type']]);
        if ($this->db->affected_rows() > 0) {
            $this->db->where('id', $data['id']);
            $query = $this->db->get('categories');
            return [
                'status' => 'success',
                'category' => $query->row()
            ];
        }
        return ['status' => 'error', 'message' => 'Category not found or no changes made'];
    }

    public function delete_category($id, $user_id)
    {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        $this->db->delete('categories');
        if ($this->db->affected_rows() > 0) {
            return ['status' => 'success'];
        }
        return ['status' => 'error', 'message' => 'Category not found'];
    }

    public function search_categories($user_id, $filters)
    {
        $this->db->where('user_id', $user_id);

        if (!empty($filters['search'])) {
            $this->db->like('name', $filters['search']);
        }
        if (!empty($filters['type'])) {
            $this->db->where('type', $filters['type']);
        }

        // Count total records for pagination
        $total_records = $this->db->count_all_results('categories', FALSE);

        // Apply pagination
        $page = max(1, (int) $filters['page']);
        $per_page = max(1, (int) $filters['per_page']);
        $offset = ($page - 1) * $per_page;
        $this->db->limit($per_page, $offset);

        $query = $this->db->get();
        $categories = $query->result();

        // Calculate total pages
        $total_pages = ceil($total_records / $per_page);

        return [
            'status' => 'success',
            'categories' => $categories,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records
        ];
    }

    public function is_category_used($id, $user_id)
    {
        $this->db->where('category_id', $id);
        $this->db->where('user_id', $user_id);
        $this->db->from('transactions');
        return $this->db->count_all_results() > 0;
    }

    public function get_user_categories($user_id)
    {
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('categories');
        return $query->result();
    }
}