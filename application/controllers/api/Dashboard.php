<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');
class Dashboard extends BD_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('api/DashBoardModel','DashBoardAPI');
        $this->auth();
    }

    public function index_get(){
        $user_data = $this->user_data;
        $user_id = $user_data->id;
        $role_id = $user_data->role_id;
        $data = [];
        $data['openings'] = $this->DashBoardAPI->openings($user_id,$role_id);
        $data['candidates'] = $this->DashBoardAPI->candidates_joining($user_id,$role_id);
        $data['today_interviews'] = $this->DashBoardAPI->today_interview($user_id,$role_id);
        $data['cv_sources'] = $this->DashBoardAPI->cv_source($user_id,$role_id);
        if($role_id == 2 || $role_id == 1){
            $data['today_cv_sources'] = $this->DashBoardAPI->today_recruiter_cv();
        }else{
            $data['today_cv_sources'] = [];
        }
        $data['candidates_placed'] = $this->DashBoardAPI->candidates_placed($user_id,$role_id);
        $data['candidates_month_leaderboard'] = $this->DashBoardAPI->candidates_month_leaderboard($user_id,$role_id);
        $this->set_response(['data' => $data,'status' => true,'message' => 'Data found'], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
}