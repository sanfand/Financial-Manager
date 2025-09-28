<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('Category_model');
        $this->load->library('session');

        if(!$this->session->userdata('logged_in')){
            redirect('login');
        }
    }

    public function index(){
        $user_id = $this->session->userdata('user_id');
        $data['title']='Categories';
        $data['categories'] = $this->Category_model->get_categories($user_id);
        $this->load->view('categories',$data);
    }

    public function create(){
        $user_id = $this->session->userdata('user_id');
        $data = $this->input->post();
        $data['user_id']=$user_id;
        $data['created_at']=date('Y-m-d H:i:s');

        $id = $this->Category_model->create($data);
        if($id){
            echo json_encode(['status'=>'success','message'=>'Category added','id'=>$id]);
        }else{
            echo json_encode(['status'=>'error','message'=>'Failed to add category']);
        }
    }

    public function edit($id){
        $user_id = $this->session->userdata('user_id');
        $data = $this->input->post();
        $data['updated_at']=date('Y-m-d H:i:s');

        $success = $this->Category_model->update($id,$data,$user_id);
        if($success){
            echo json_encode(['status'=>'success','message'=>'Category updated']);
        }else{
            echo json_encode(['status'=>'error','message'=>'Update failed']);
        }
    }

    public function delete($id){
        $user_id = $this->session->userdata('user_id');
        $success = $this->Category_model->delete($id,$user_id);

        if($success){
            echo json_encode(['status'=>'success','message'=>'Category deleted']);
        }else{
            echo json_encode(['status'=>'error','message'=>'Delete failed']);
        }
    }
}
