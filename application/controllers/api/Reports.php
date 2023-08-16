<?php 
defined('BASEPATH') or exit('No direct script access allowed');
class Reports extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('api/ReportModel');
        $this->auth();
    }

    public function cv_report_get(){
        try{
            $year = $this->get('year');
            if(!$year){
                $year = date('Y');
            }
            $res = $this->ReportModel->get_cv_report($year);
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'selected_year' => $year,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function candidates_placement_get()
    {
        try{
            $year = $this->get('year');
            if(!$year){
                $year = date('Y');
            }
            $res = $this->ReportModel->get_candidates_placement($year);
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'selected_year' => $year,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function candidate_source_get()
    {
        try{
            $year = $this->get('year');
            $source_id = $this->get('source_id');
            if(!$year){
                $year = date('Y');
            }
            $res = $this->ReportModel->get_candidates_source($source_id,$year);
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'selected_year' => $year,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function clients_candidate_assign_get()
    {
        try{
            $year = $this->get('year');
            $client_id = $this->get('client_id');
            if(!$year){
                $year = date('Y');
            }
            $res = $this->ReportModel->get_candidates_clients($client_id,$year);
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'selected_year' => $year,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function requirement_role_get()
    {
        try{
            $year = $this->get('year');
            $role_id = $this->get('role_id');
            if(!$year){
                $year = date('Y');
            }
            $res = $this->ReportModel->get_roles($role_id,$year);
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'selected_year' => $year,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function job_performance_post(){
        try{
            $job_status = $this->post('job_status');
            $client_id = $this->post('clients');
            $start_date = $this->post('start_date');
            $end_date = $this->post('end_date');
            $job_id = $this->post('jobs');
            $length = $this->post('length');
            $start = !$this->post('start') ? 0 : ($this->post('start') - 1);
            $res = $this->ReportModel->job_performance($start_date, $end_date, $job_id, $client_id, $job_status, $start , $length);
            $total = $this->ReportModel->job_performance_count($start_date, $end_date,$job_id, $client_id, $job_status);
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'current_count' => count($res),
                    'total' => $total,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }else{
                $this->response([
                    'status' => false,
                    'data' => [],
                    'current_count' => 0,
                    'total' => 0,
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function quick_view_get(){
        try{
            $res = $this->ReportModel->job_performance_quick_view();
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }else{
                $this->response([
                    'status' => false,
                    'data' => [],
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function client_tracking_post(){
        try{
            $client_status = $this->post('client_status');
            $client_id = $this->post('clients');
            $start_date = $this->post('start_date');
            $end_date = $this->post('end_date');
            $length = $this->post('length');
            $start = !$this->post('start') ? 0 : ($this->post('start') - 1);
            $res = $this->ReportModel->client_tracking($start_date, $end_date, $client_id, $client_status, $start , $length);
            $total = $this->ReportModel->client_tracking_count($start_date, $end_date, $client_id, $client_status);
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'current_count' => count($res),
                    'total' => $total,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }else{
                $this->response([
                    'status' => false,
                    'data' => [],
                    'current_count' => 0,
                    'total' => 0,
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function client_quick_view_get(){
        try{
            $res = $this->ReportModel->client_tracking_quick_view();
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }else{
                $this->response([
                    'status' => false,
                    'data' => [],
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function team_tracker_post(){
        try{
            $start_date = $this->post('start_date');
            $end_date = $this->post('end_date');
            $job_id = $this->post('jobs');
            $length = $this->post('length');
            $start = !$this->post('start') ? 0 : ($this->post('start') - 1);
            $res = $this->ReportModel->team_tracker($start_date, $end_date, $job_id, $start , $length);
            $total = $this->ReportModel->team_tracker_count($start_date, $end_date,$job_id);
            if($res){
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'current_count' => count($res),
                    'total' => $total,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            }else{
                $this->response([
                    'status' => false,
                    'data' => [],
                    'current_count' => 0,
                    'total' => 0,
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}