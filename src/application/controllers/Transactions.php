<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('Transaction_model');
        $this->load->model('Category_model');
        $this->load->library('session');

        if(!$this->session->userdata('logged_in')){
            redirect('login');
        }
    }

    public function index(){
        $user_id = $this->session->userdata('user_id');
        $data['title']='Transactions';
        $data['transactions'] = $this->Transaction_model->get_transactions($user_id);
        $data['categories'] = $this->Category_model->get_categories($user_id);
        $data['links']=''; //for pagination
        $this->load->view('transactions',$data);
    }

    public function create(){
        $user_id = $this->session->userdata('user_id');
        $data = $this->input->post();
        $data['user_id']=$user_id;
        $data['created_at']=date('Y-m-d H:i:s');

        $id = $this->Transaction_model->create($data);
        if($id){
            echo json_encode(['status'=>'success','message'=>'Transaction added','id'=>$id]);
        }else{
            echo json_encode(['status'=>'error','message'=>'Failed to add transaction']);
        }
    }

    public function edit($id){
        $user_id = $this->session->userdata('user_id');
        $data = $this->input->post();
        $data['updated_at']=date('Y-m-d H:i:s');

        $success = $this->Transaction_model->update($id,$data,$user_id);
        if($success){
            echo json_encode(['status'=>'success','message'=>'Transaction updated']);
        }else{
            echo json_encode(['status'=>'error','message'=>'Update failed']);
        }
    }

    public function delete($id){
        $user_id = $this->session->userdata('user_id');
        $success = $this->Transaction_model->delete($id,$user_id);

        if($success){
            echo json_encode(['status'=>'success','message'=>'Transaction deleted']);
        }else{
            echo json_encode(['status'=>'error','message'=>'Delete failed']);
        }
    }
}
