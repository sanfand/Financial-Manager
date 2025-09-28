<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('session');

        if(!$this->session->userdata('logged_in')){
            redirect('login');
        }
    }

    public function index(){
        $user_id = $this->session->userdata('user_id');
        $data['title']='Profile';
        $data['user'] = $this->User_model->get_user_by_id($user_id);
        $this->load->view('profile',$data);
    }

    public function update(){
        $user_id = $this->session->userdata('user_id');
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $update_data = [
            'name'=>$name,
            'email'=>$email,
            'updated_at'=>date('Y-m-d H:i:s')
        ];

        if($password) $update_data['password_hash']=password_hash($password,PASSWORD_DEFAULT);

        if(!empty($_FILES['profile_pic']['name'])){
            $config['upload_path'] = './uploads/profile/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048;
            $config['file_name'] = time().'_profile';
            $this->load->library('upload',$config);
            if($this->upload->do_upload('profile_pic')){
                $update_data['profile_pic']='uploads/profile/'.$this->upload->data('file_name');
            }
        }

        $success = $this->User_model->update($user_id,$update_data);

        if($success){
            $user = $this->User_model->get($user_id);
            echo json_encode(['status'=>'success','user'=>$user]);
        }else{
            echo json_encode(['status'=>'error','message'=>'Profile update failed']);
        }
    }
}
