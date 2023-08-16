<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Employment_type extends BD_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('api/EmploymentTypeMasterModel','EmploymentTypeMaster');
        $this->auth();
    }

    public function index_get(){
        $id = $this->get('id');
        $search = $this->get('keyword');
        $length = $this->get('length');
        $start = !$this->get('start') ? 0 : ($this->get('start') - 1);
        $status = $this->get('status');
        $total = $this->EmploymentTypeMaster->count_all();
        $data = $this->EmploymentTypeMaster->get_list($id,$search,$start,$length,$status);
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
}
