<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        $this->load->model('Category_model');
        $this->load->model('Transaction_model'); // to check if category is used
        $this->load->library('form_validation');
    }

    // Show categories with pagination
    public function index() {
        $user_id = $this->session->userdata('user_id');
        $data['title'] = 'Categories';

        $per_page = 5;
        $page = (int) $this->input->get('page') ?: 1;
        $offset = ($page - 1) * $per_page;

        $categories = $this->Category_model->get_categories($user_id, $per_page, $offset);

        // Add `used` property for each category
        foreach ($categories as &$category) {
            $category->used = $this->Transaction_model->is_category_used($category->id);
        }

        $data['categories'] = $categories;

        $total = $this->Category_model->count_categories($user_id);

        $data['pagination'] = [
            'current' => $page,
            'per_page' => $per_page,
            'total' => $total,
            'pages' => ceil($total / $per_page)
        ];

        $this->load->view('header', $data);
        $this->load->view('categories', $data);
        $this->load->view('footer');
    }

    // Create category
    public function create() {
        $user_id = $this->session->userdata('user_id');

        $this->form_validation->set_rules('name', 'Category Name', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(['status'=>'error', 'message'=>validation_errors()]);
            return;
        }

        $data = [
            'user_id' => $user_id,
            'name' => $this->input->post('name', true),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->Category_model->create_category($data)) {
            echo json_encode(['status'=>'success', 'message'=>'Category added successfully']);
        } else {
            echo json_encode(['status'=>'error', 'message'=>'Failed to add category']);
        }
    }

    // Get single category for edit
    public function get_category($id) {
        $category = $this->Category_model->get_category($id);
        if (!$category) {
            echo json_encode(['status'=>'error', 'message'=>'Category not found']);
            return;
        }
        echo json_encode($category);
    }

    // Edit category
    public function edit($id) {
        $user_id = $this->session->userdata('user_id');
        $category = $this->Category_model->get_category($id);

        if (!$category || ($category->user_id != $user_id && $category->user_id !== null)) {
            echo json_encode(['status'=>'error', 'message'=>'Category not found']);
            return;
        }

        $this->form_validation->set_rules('name', 'Category Name', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            echo json_encode(['status'=>'error', 'message'=>validation_errors()]);
            return;
        }

        $data = [
            'name' => $this->input->post('name', true),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->Category_model->update_category($id, $data)) {
            echo json_encode(['status'=>'success', 'message'=>'Category updated successfully']);
        } else {
            echo json_encode(['status'=>'error', 'message'=>'Failed to update category']);
        }
    }

    // Delete category
    public function delete($id) {
        $user_id = $this->session->userdata('user_id');
        $category = $this->Category_model->get_category($id);

        if (!$category || ($category->user_id != $user_id && $category->user_id !== null)) {
            echo json_encode(['status'=>'error', 'message'=>'Category not found']);
            return;
        }

        // Block deletion if category is used in transactions
        if ($this->Transaction_model->is_category_used($id)) {
            echo json_encode(['status'=>'error', 'message'=>'Cannot delete: category is used in transactions']);
            return;
        }
        

        if ($this->Category_model->delete_category($id)) {
            echo json_encode(['status'=>'success', 'message'=>'Category deleted successfully']);
        } else {
            echo json_encode(['status'=>'error', 'message'=>'Failed to delete category']);
        }
    }
}
