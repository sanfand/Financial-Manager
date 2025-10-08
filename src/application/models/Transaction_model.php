<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model {

    protected $table = 'transactions';

    public function create_transaction($data) {
        return $this->db->insert($this->table, $data);
    }

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

    public function search_transactions($user_id, $filters, $limit = 10, $offset = 0)
    {
        $this->db->select('transactions.*, categories.name as category_name');
        $this->db->from('transactions');
        $this->db->join('categories', 'transactions.category_id = categories.id', 'left');
        $this->db->where('transactions.user_id', $user_id);
        $this->db->order_by('occurred_at', 'DESC');
        $this->db->limit($limit, $offset);
        if (!empty($filters['search'])) {
            $this->db->like('transactions.title', $filters['search']);
        }
        if (!empty($filters['type'])) {
            $this->db->where('transactions.type', $filters['type']);
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('transactions.category_id', $filters['category_id']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('transactions.occurred_at >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('transactions.occurred_at <=', $filters['end_date']);
        }

        $result = $this->db->get()->result_array();
        foreach ($result as &$row) {
            $row['amount'] = (float) $row['amount'];
        }
        return $result;
    }

    public function get_chart_data($user_id)
    {
        if (!$user_id) {
            return ['labels' => [], 'income' => [], 'expense' => []];
        }

        $labels = [];
        $income = [];
        $expense = [];

        for ($i = 6; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $labels[] = date('M Y', strtotime($month));

            $this->db->select('type, SUM(amount) as total');
            $this->db->where('user_id', $user_id);
            $this->db->where("DATE_FORMAT(occurred_at, '%Y-%m') =", $month);
            $this->db->group_by('type');
            $query = $this->db->get('transactions');

            $month_income = 0.0;
            $month_expense = 0.0;

            foreach ($query->result() as $row) {
                if ($row->type === 'income') {
                    $month_income = (float) $row->total;
                } elseif ($row->type === 'expense') {
                    $month_expense = (float) $row->total;
                }
            }

            $income[] = $month_income;
            $expense[] = $month_expense;

            $this->db->reset_query(); // clears query builder for next loop
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expense' => $expense
        ];
    }

    public function get_monthly_trends($user_id) {
        $this->db->select("DATE_FORMAT(occurred_at, '%Y-%m') as month, type, SUM(amount) as total");
        $this->db->where('user_id', $user_id);
        $this->db->group_by("month, type");
        $this->db->order_by('month ASC');
        return $this->db->get($this->table)->result();
    }

    public function count_transactions($user_id, $filters = [])
    {
        $this->db->from($this->table);
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

        return $this->db->count_all_results();
    }
}