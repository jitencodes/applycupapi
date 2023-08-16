<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Client_status_master extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('api/ClientStatusModel','ClientStatus');
        $this->auth();
    }

    public function index_get(){
        $id = $this->get('id');
        $search = $this->get('keyword');
        $length = $this->get('length');
        $start = !$this->get('start') ? 0 : ($this->get('start') - 1);
        $status = $this->get('status');
        $total = $this->ClientStatus->count_all();
        $data = $this->ClientStatus->get_list($id,$search,$start,$length,$status);
        if (!empty($data))
        {
            $this->set_response(['data' => $data,'total' => $total], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'data' => [],
                'total' => 0,
                'status' => FALSE,
                'message' => 'Data could not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function index_post(){
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('status_title', 'Status Name', 'trim|required');
            $this->form_validation->set_rules('ordering', 'Ordering', 'trim|required');

            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $name = $this->post('status_title');
            $ordering = $this->post('ordering');
            $status = $this->post('status');
            if ($this->ClientStatus->is_exist($name)){
                $this->response([
                    'status' => False,
                    'message' => "This status already exits",
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if ($this->ClientStatus->add($name,$ordering,$status)) {
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

    public function index_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'Status Id', 'trim|required');
            $this->form_validation->set_rules('status_title', 'Status Name', 'trim|required');
            $this->form_validation->set_rules('ordering', 'Ordering', 'trim|required');

            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->put('id');
            $name = $this->put('status_title');
            $ordering = $this->put('ordering');
            $status = $this->put('status');
            if ($this->ClientStatus->is_exist($name,$id)){
                $this->response([
                    'status' => False,
                    'message' => "This status already exits",
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if ($this->ClientStatus->update($id,$name,$ordering,$status)) {
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
                'message' => "Parameter empty",
            ], REST_Controller::HTTP_BAD_REQUEST);

        }
    }
}