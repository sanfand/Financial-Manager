<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Transaction_model');
        $this->load->model('Token_model');
        $this->load->model('User_model'); // ADD THIS LINE
        $this->load->helper('url');

        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->authenticate();
    }

    private function authenticate()
    {
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (!preg_match('/Bearer\s+(\S+)/', $auth_header, $matches)) {
            $this->output->set_content_type('application/json')->set_status_header(401);
            echo json_encode(['status' => 'error', 'message' => 'Token required']);
            exit;
        }
        
        $token = $matches[1];
        $this->user_id = $this->Token_model->verify($token);
        
        if (!$this->user_id) {
            $this->output->set_content_type('application/json')->set_status_header(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
            exit;
        }
    }

    public function index()
    {
        $this->output->set_content_type('application/json');
        
        try {
            $this->db->select('type, SUM(amount) as total');
            $this->db->where('user_id', $this->user_id);
            $this->db->group_by('type');
            $summary_query = $this->db->get('transactions')->result();

            $income = 0;
            $expense = 0;

            foreach ($summary_query as $row) {
                if ($row->type === 'income') {
                    $income = (float) $row->total;
                } elseif ($row->type === 'expense') {
                    $expense = (float) $row->total;
                }
            }

            $this->db->select('t.*, c.name as category_name');
            $this->db->from('transactions t');
            $this->db->join('categories c', 'c.id = t.category_id', 'left');
            $this->db->where('t.user_id', $this->user_id);
            $this->db->order_by('t.occurred_at', 'DESC');
            $this->db->limit(5);
            $recent_transactions = $this->db->get()->result();

            foreach ($recent_transactions as $transaction) {
                $transaction->amount = (float) $transaction->amount;
            }

            echo json_encode([
                'status' => 'success',
                'summary' => [
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $income - $expense
                ],
                'recent_transactions' => $recent_transactions
            ]);
        } catch (Exception $e) {
            log_message('error', 'Dashboard index error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error loading dashboard data']);
        }
    }

    public function get_chart_data()
    {
        $this->output->set_content_type('application/json');
        
        try {
            $chart_data = $this->Transaction_model->get_chart_data($this->user_id);

            if (!$chart_data) {
                $chart_data = [
                    'labels' => [],
                    'income' => [],
                    'expense' => []
                ];
            }

            echo json_encode(['status' => 'success', 'data' => $chart_data]);
        } catch (Exception $e) {
            log_message('error', 'Chart data error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Error loading chart data']);
        }
    }
}