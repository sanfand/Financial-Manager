<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Category_model');
        $this->load->library('session');
        $this->load->helper('url');
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
    }

    public function index()
    {
        $data = [
            'title' => 'Categories'
        ];
        $this->load->view('categories', $data);
    }

    public function create()
    {
        $response = ['status' => 'error', 'message' => 'Invalid request'];
        if ($this->input->method() === 'post') {
            $user_id = $this->session->userdata('user_id');
            if (!$user_id) {
                $response = ['status' => 'error', 'message' => 'User not authenticated'];
            } else {
                $data = [
                    'name' => trim($this->input->post('name', TRUE)) ?: '',
                    'type' => $this->input->post('type', TRUE) ?: 'income',
                    'user_id' => $user_id
                ];
                $response = $this->Category_model->create_category($data);
                $response['user_id'] = $user_id; // Debug
            }
            $response['csrf_hash'] = $this->security->get_csrf_hash();
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function edit()
    {
        $response = ['status' => 'error', 'message' => 'Invalid request'];
        if ($this->input->method() === 'post') {
            $user_id = $this->session->userdata('user_id');
            if (!$user_id) {
                $response = ['status' => 'error', 'message' => 'User not authenticated'];
            } else {
                $data = [
                    'id' => $this->input->post('id', TRUE),
                    'name' => trim($this->input->post('name', TRUE)) ?: '',
                    'type' => $this->input->post('type', TRUE) ?: 'income',
                    'user_id' => $user_id
                ];
                if (!$data['id']) {
                    $response = ['status' => 'error', 'message' => 'Category ID is required'];
                } else {
                    $response = $this->Category_model->update_category($data);
                }
                $response['user_id'] = $user_id; // Debug
            }
            $response['csrf_hash'] = $this->security->get_csrf_hash();
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function delete()
    {
        $response = ['status' => 'error', 'message' => 'Invalid request'];
        if ($this->input->method() === 'post') {
            $user_id = $this->session->userdata('user_id');
            if (!$user_id) {
                $response = ['status' => 'error', 'message' => 'User not authenticated'];
            } else {
                $id = $this->input->post('id', TRUE);
                if (!$id) {
                    $response = ['status' => 'error', 'message' => 'Category ID is required'];
                } else {
                    // Check if category is used in transactions
                    if ($this->Category_model->is_category_used($id, $user_id)) {
                        $response = ['status' => 'error', 'message' => 'This category is used in a transaction and cannot be deleted'];
                    } else {
                        $response = $this->Category_model->delete_category($id, $user_id);
                    }
                    $response['user_id'] = $user_id; // Debug
                }
                $response['csrf_hash'] = $this->security->get_csrf_hash();
            }
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function search()
    {
        $response = ['status' => 'error', 'message' => 'Invalid request'];
        if ($this->input->method() === 'post') {
            $user_id = $this->session->userdata('user_id');
            if (!$user_id) {
                $response = ['status' => 'error', 'message' => 'User not authenticated'];
            } else {
                $filters = [
                    'search' => trim($this->input->post('search', TRUE)) ?: '',
                    'type' => $this->input->post('type', TRUE) ?: '',
                    'page' => max(1, (int)$this->input->post('page', TRUE)),
                    'per_page' => max(1, (int)$this->input->post('per_page', TRUE))
                ];
                $response = $this->Category_model->search_categories($user_id, $filters);
                $response['user_id'] = $user_id; // Debug
            }
            $response['csrf_hash'] = $this->security->get_csrf_hash();
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}