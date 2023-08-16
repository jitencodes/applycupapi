<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');
class Clients extends BD_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('api/ClientModel','Clients');
        $this->auth();
    }

    public function index_get(){
        $id = $this->get('id');
        $search = $this->get('keyword');
        $length = $this->get('length');
        $start = !$this->get('start') ? 0 : ($this->get('start') - 1);
        $status = $this->get('status');
        $total = $this->Clients->count_all();
        $data = $this->Clients->get_list($id,$search,$start,$length,$status);
        if (!empty($data))
        {
            $this->set_response(['data' => $data,'total' => $total], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'data' => [],'total' => 0,
                'status' => FALSE,
                'message' => 'Data could not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function index_post(){
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('company', 'Company Name', 'trim|required');
            $this->form_validation->set_rules('location', 'Location', 'trim|required');
            $this->form_validation->set_rules('client_source_master_id', 'Client Source', 'trim|required');
            $this->form_validation->set_rules('client_status', 'Client Status', 'trim|required');
            $this->form_validation->set_rules('employe_assign_to', 'Employee', 'trim|required');
            $this->form_validation->set_rules('contact_person', 'Contact Person', 'trim|required');
            $this->form_validation->set_rules('mobile', 'Person mobile number', 'trim|required');
            $this->form_validation->set_rules('email', 'Person email', 'trim|required');
            $this->form_validation->set_rules('designation', 'Designation', 'trim|required');

            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $company = $this->post('company');
            $location = $this->post('location');
            $client_source_master_id = $this->post('client_source_master_id');
            $client_status = $this->post('client_status');
            $employe_assign_to = $this->post('employe_assign_to');
            $contact_person = $this->post('contact_person');
            $mobile = $this->post('mobile');
            $email = $this->post('email');
            $designation = $this->post('designation');
            $mobile_2 = $this->post('mobile_2');
            $status = $this->post('status');
            $notes = $this->post('notes');
            if ($this->Clients->is_exist($company)){
                $this->response([
                    'status' => False,
                    'message' => "This source already exits",
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if ($this->Clients->add($company,$location,$client_source_master_id,$client_status,$employe_assign_to,$status,$contact_person,$mobile,$email,$designation,$mobile_2,$notes)) {
                $this->response([
                    'status' => True,
                    'message' => 'Data add successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Update fail');
            }
        }
        catch(\Throwable $e)
        {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);

        }
    }

    public function index_put(){
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'Edit id', 'trim|required');
            $this->form_validation->set_rules('company', 'Company Name', 'trim|required');
            $this->form_validation->set_rules('location', 'Location', 'trim|required');
            $this->form_validation->set_rules('client_source_master_id', 'Client Source', 'trim|required');
            $this->form_validation->set_rules('client_status', 'Client Status', 'trim|required');
            $this->form_validation->set_rules('employe_assign_to', 'Employee', 'trim|required');
            $this->form_validation->set_rules('contact_person', 'Contact Person', 'trim|required');
            $this->form_validation->set_rules('mobile', 'Person mobile number', 'trim|required');
            $this->form_validation->set_rules('email', 'Person email', 'trim|required');
            $this->form_validation->set_rules('designation', 'Designation', 'trim|required');

            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->put('id');
            $company = $this->put('company');
            $location = $this->put('location');
            $client_source_master_id = $this->put('client_source_master_id');
            $client_status = $this->put('client_status');
            $employe_assign_to = $this->put('employe_assign_to');
            $contact_person = $this->put('contact_person');
            $mobile = $this->put('mobile');
            $email = $this->put('email');
            $designation = $this->put('designation');
            $mobile_2 = $this->put('mobile_2');
            $status = $this->put('status');
            $notes = $this->put('notes');
            $contact_person_status = $this->put('contact_person_status');
            if ($this->Clients->is_exist($company,$id)){
                $this->response([
                    'status' => False,
                    'message' => "This company already exits",
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if ($this->Clients->update($id,$company,$location,$client_source_master_id,$client_status,$employe_assign_to,$status,$contact_person,$mobile,$email,$designation,$mobile_2,$contact_person_status,$notes)) {
                $this->response([
                    'status' => True,
                    'message' => 'Data update successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Update fail');
            }
        }
        catch(\Throwable $e)
        {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);

        }
    }

    public function active_get(){
        $active_clients = $this->Clients->active_clients();
        if ($active_clients)
        {
            $this->set_response(['data' => $active_clients, 'status' => true], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'message' => 'Data could not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }
}
