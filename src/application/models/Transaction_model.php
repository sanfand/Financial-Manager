<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model {

    protected $table = 'transactions';

    public function get_transactions($user_id, $limit = null, $offset = null, $filters = []) {
        $this->db->select('t.*, c.name as category_name');
        $this->db->from($this->table . ' t');
        $this->db->join('categories c', 'c.id = t.category_id', 'left');
        $this->db->where('t.user_id', $user_id);

        if (!empty($filters['type'])) {
            $this->db->where('t.type', $filters['type']);
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('t.category_id', $filters['category_id']);
        }
        if (!empty($filters['search'])) {
            $this->db->like('t.title', $filters['search']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('t.occurred_at >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('t.occurred_at <=', $filters['end_date']);
        }

        $this->db->order_by('t.occurred_at', 'DESC');

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function get_transaction($id) {
        $this->db->select('t.*, c.name as category_name');
        $this->db->from($this->table . ' t');
        $this->db->join('categories c', 'c.id = t.category_id', 'left');
        $this->db->where('t.id', $id);
        return $this->db->get()->row();
    }

    public function create($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update_transaction($id, $data) {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete_transaction($id) {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function count_transactions($user_id, $filters = []) {
        $this->db->where('user_id', $user_id);

        if (!empty($filters['type'])) {
            $this->db->where('type', $filters['type']);
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['search'])) {
            $this->db->like('title', $filters['search']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('occurred_at >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('occurred_at <=', $filters['end_date']);
        }

        return $this->db->count_all_results($this->table);
    }

    public function get_financial_summary($user_id) {
        $this->db->select('type, SUM(amount) as total');
        $this->db->where('user_id', $user_id);
        $this->db->group_by('type');
        $result = $this->db->get($this->table)->result();

        $summary = ['income' => 0, 'expense' => 0];
        foreach ($result as $row) {
            $summary[$row->type] = $row->total;
        }
        $summary['balance'] = $summary['income'] - $summary['expense'];
        return $summary;
    }

    public function get_recent_transactions($user_id, $limit = 6) {
        return $this->get_transactions($user_id, $limit, 0);
    }

    public function get_monthly_trends($user_id) {
        $this->db->select("DATE_FORMAT(occurred_at, '%Y-%m') as month, type, SUM(amount) as total");
        $this->db->where('user_id', $user_id);
        $this->db->group_by("month, type");
        $this->db->order_by('month ASC');
        return $this->db->get($this->table)->result();
    }

    public function get_chart_data($user_id) {
        $trends = $this->get_monthly_trends($user_id);
        $labels = [];
        $income = [];
        $expense = [];
        $uniqueMonths = [];
        foreach ($trends as $row) {
            $uniqueMonths[$row->month] = true;
        }
        $labels = array_keys($uniqueMonths);
        sort($labels);
        foreach ($labels as $month) {
            $income[$month] = 0;
            $expense[$month] = 0;
        }
        foreach ($trends as $row) {
            if ($row->type == 'income') {
                $income[$row->month] = (float) $row->total;
            } else if ($row->type == 'expense') {
                $expense[$row->month] = (float) $row->total;
            }
        }
        return [
            'labels' => $labels,
            'income' => array_values($income),
            'expense' => array_values($expense)
        ];
    }

    // Check if category is used in any transaction
    public function is_category_used($category_id) {
        $this->db->where('category_id', $category_id);
        $query = $this->db->get('transactions'); 
        return $query->num_rows() > 0;
    }

    

}