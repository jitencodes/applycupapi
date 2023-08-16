<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Master_data extends BD_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('api/MasterDataModel','MasterData');
        $this->auth();
    }

    public function index_get(){
        $master_type = ['all'];
        if ($this->get('masters')){
            $master_type = explode(',',$this->get('masters'));
        }
        $data = $this->MasterData->get_masters($master_type);
        if ($data)
        {
            $this->set_response(['data' => $data,'status' => true], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'data' => [],
                'status' => FALSE,
                'message' => 'Data could not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }
}
