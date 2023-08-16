<?php

class Department_master extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('api/DepartmentModel','Department');
        $this->auth();
    }

    public function index_get(){
        $id = $this->get('id');
        $search = $this->get('keyword');
        $length = $this->get('length');
        $start = $this->get('start');
        $total = $this->Department->count_all();
        $data = $this->Department->get_department($id,$search,$start,$length);
        if (!empty($data))
        {
            $this->set_response(['data' => $data,'total' => $total], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'message' => 'Data could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function index_post(){
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('department_name', 'Department Name', 'trim|required');
            $this->form_validation->set_rules('ordering', 'Ordering', 'trim|required');

            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $name = $this->post('department_name');
            $ordering = $this->post('ordering');
            $status = $this->post('status');
            if ($this->Department->check_department($name)){
                $this->response([
                    'status' => False,
                    'message' => "This department already exits",
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if ($this->Department->add($name,$ordering,$status)) {
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
            $this->form_validation->set_rules('id', 'Department Id', 'trim|required');
            $this->form_validation->set_rules('department_name', 'Department Name', 'trim|required');
            $this->form_validation->set_rules('ordering', 'Ordering', 'trim|required');

            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->put('id');
            $name = $this->put('department_name');
            $ordering = $this->put('ordering');
            $status = $this->put('status');
            if ($this->Department->check_department($name,$id)){
                $this->response([
                    'status' => False,
                    'message' => "This department already exits",
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if ($this->Department->update($id,$name,$ordering,$status)) {
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
