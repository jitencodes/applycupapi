<?php

defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');
class Requirements extends BD_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('api/RequirementsModel','Requirements');
        $this->load->model('api/JobListModel','JobList');
        $this->auth();
    }

    public function index_get(){
        $search = $this->get('keyword');
        $length = $this->get('length');
        $start_date = $this->get('start_date');
        $end_date = $this->get('end_date');
        $start = !$this->get('start') ? 0 : ($this->get('start') - 1);
        $user_id = $this->user_data->id;
        $user_role = $this->user_data->role_id;
        $requirement_enable = $this->user_data->requirement_enable;
        $total = $this->JobList->count_all($user_id,$user_role,$requirement_enable,$start_date,$end_date);
        $data = $this->JobList->get_opening_list($user_id,$user_role,$search,$start,$length,$requirement_enable,$start_date,$end_date);
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
            $this->form_validation->set_rules('number_of_vacancy', 'Number of vacancy', 'trim|required');
            $this->form_validation->set_rules('experience_type', 'Experience', 'trim|required');
            $this->form_validation->set_rules('is_industry_standard', 'Salary type', 'trim|required');
            $this->form_validation->set_rules('requirement_description', 'Requirement description', 'trim|required');
            $this->form_validation->set_rules('notice_period_master_id', 'Notice Period', 'trim|required');
            $this->form_validation->set_rules('location_type', 'Location', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $company = $this->post('company_id');
            $job_type = $this->post('job_type');
            $skills = $this->post('skills');
            $salary_currency = $this->post('salary_currency');
            $is_industry_standard = $this->post('is_industry_standard');
            if (!isset($company['value'])){
                $this->response([
                    'status' => false,
                    'message' => "Company field is empty",
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
            if (!isset($job_type['value'])){
                $this->response([
                    'status' => false,
                    'message' => "Job type field is empty",
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
            if ($is_industry_standard == 1 && !isset($salary_currency['value'])){
                $this->response([
                    'status' => false,
                    'message' => "Salary currency field is empty",
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
            $number_of_vacancy = $this->post('number_of_vacancy');
            $experience_type = $this->post('experience_type');
            $min_experience = $this->post('min_experience');
            $max_experience = $this->post('max_experience');
            $min_salary = $this->post('min_salary');
            $max_salary = $this->post('max_salary');
            $location_type = $this->post('location_type');
            $location_id = $this->post('location_id');
            $notice_period_master_id = $this->post('notice_period_master_id');
            $requirement_description = $this->post('requirement_description');
            $requirement_status = $this->post('requirement_status');
            $job_role = $this->post('job_role');
            $qualification = $this->post('qualification');
            $assigned_to = $this->post('assigned_to');
            $created_by = $this->user_data->id;
            $status = $this->post('status');
            $res = $this->Requirements->add($company,$job_type,$number_of_vacancy,$experience_type,$is_industry_standard,$min_experience,$max_experience,$min_salary,$max_salary,$salary_currency,$notice_period_master_id,$requirement_description,$requirement_status,$job_role,$skills,$qualification,$assigned_to,$created_by,$status,$location_type,$location_id);
            if ($res['status']) {
                $this->response([
                    'status' => True,
                    'message' => 'Data add successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => $res['msg'],
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
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
            $this->form_validation->set_rules('number_of_vacancy', 'Number of vacancy', 'trim|required');
            $this->form_validation->set_rules('experience_type', 'Experience', 'trim|required');
            $this->form_validation->set_rules('is_industry_standard', 'Salary type', 'trim|required');
            $this->form_validation->set_rules('requirement_description', 'Requirement description', 'trim|required');
            $this->form_validation->set_rules('notice_period_master_id', 'Notice Period', 'trim|required');
            $this->form_validation->set_rules('location_type', 'Location', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->put('id');
            $company = $this->put('company_id');
            $job_type = $this->put('job_type');
            $skills = $this->put('skills');
            $salary_currency = $this->put('salary_currency');
            $is_industry_standard = $this->put('is_industry_standard');
            if (!isset($company['value'])){
                $this->response([
                    'status' => false,
                    'message' => "Company field is empty",
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
            if (!isset($job_type['value'])){
                $this->response([
                    'status' => false,
                    'message' => "Job type field is empty",
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
            if ($is_industry_standard == 1 && !isset($salary_currency['value'])){
                $this->response([
                    'status' => false,
                    'message' => "Salary currency field is empty",
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
            $number_of_vacancy = $this->put('number_of_vacancy');
            $experience_type = $this->put('experience_type');
            $min_experience = $this->put('min_experience');
            $max_experience = $this->put('max_experience');
            $min_salary = $this->put('min_salary');
            $max_salary = $this->put('max_salary');
            $location_type = $this->put('location_type');
            $location_id = $this->put('location_id');
            $notice_period_master_id = $this->put('notice_period_master_id');
            $requirement_description = $this->put('requirement_description');
            $requirement_status = $this->put('requirement_status');
            $job_role = $this->put('job_role');
            $qualification = $this->put('qualification');
            $assigned_to = $this->put('assigned_to');
            $created_by = $created_by = $this->user_data->id;;
            $status = $this->put('status');
            $res = $this->Requirements->update($id,$company,$job_type,$number_of_vacancy,$experience_type,$is_industry_standard,$min_experience,$max_experience,$min_salary,$max_salary,$salary_currency,$notice_period_master_id,$requirement_description,$requirement_status,$job_role,$skills,$qualification,$assigned_to,$created_by,$status,$location_type,$location_id);
            if ($res['status']) {
                $this->response([
                    'status' => True,
                    'message' => $res['res'],
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => $res['res'],
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
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

    public function candidate_favourite_put(){
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('screen_id', 'Id', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $screen_id = $this->put('screen_id');
            $is_favourite = $this->put('is_favourite');
            $res = $this->Requirements->candidate_favourite($screen_id,$is_favourite);
            if($res){
                $this->response([
                    'status' => True,
                    'message' => 'Update successfully',
                ], REST_Controller::HTTP_CREATED);
            }else{
                $this->response([
                    'status' => false,
                    'message' => 'Something went wrong!',
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        }catch (\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function assign_employee_post(){
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('job_id', 'Job missing', 'trim|required');
            $this->form_validation->set_rules('employee_id', 'Employee', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $job_id = $this->post('job_id');
            $employee_id = $this->post('employee_id');
            $res = $this->Requirements->empolyee_assign($job_id,$employee_id);
            if ($res['status']){
                $this->response([
                    'status' => True,
                    'message' => $res['res'],
                    'data' => $res['data'],
                ], REST_Controller::HTTP_CREATED);
            }else{
                $this->response([
                    'status' => false,
                    'message' => $res['res'],
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        }catch (\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function job_detail_get(){
        try {
            $this->form_validation->set_data($this->get());
            $this->form_validation->set_rules('job_id', 'Job missing', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $job_id = $this->get('job_id');
            $res = $this->JobList->get_job_detail($job_id);
            if ($res){
                $this->response([
                    'status' => True,
                    'message' => 'Data found',
                    'data' => $res,
                ], REST_Controller::HTTP_CREATED);
            }else{
                $this->response([
                    'status' => false,
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        }catch (\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function job_boarding_get(){
        try {
            $this->form_validation->set_data($this->get());
            $this->form_validation->set_rules('job_id', 'Job missing', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $job_id = $this->get('job_id');
            $search = $this->get('keyword');
            $res = $this->JobList->job_boarding($job_id,$search);
            if ($res){
                $this->response([
                    'status' => True,
                    'message' => 'Data found',
                    'data' => $res,
                ], REST_Controller::HTTP_CREATED);
            }else{
                $this->response([
                    'status' => false,
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        }catch (\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function opening_with_stages_get()
    {
        try {
            $search = $this->get('keyword');
            $employee_id = $this->user_data->id;
            $user_role = $this->user_data->role_id;
            $res = $this->Requirements->opening_with_stages($employee_id,$user_role,$search);
            if ($res){
                $this->response([
                    'status' => True,
                    'message' => 'Data found',
                    'data' => $res,
                ], REST_Controller::HTTP_CREATED);
            }else{
                $this->response([
                    'status' => false,
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_NO_CONTENT);
            }
        }catch (\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function screening_levels_put(){
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('from', 'Source Id', 'is_natural');
            $this->form_validation->set_rules('to', 'Target Id', 'is_natural');
            $this->form_validation->set_rules('job_id', 'Job Id', 'trim|required');
            $this->form_validation->set_rules('stage_id', 'Stage Id', 'trim|required');
            $this->form_validation->set_rules('stage_name', 'Stage Name', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            // $updated_by = $this->user_data->id;
            $from = $this->put('from');
            $to = $this->put('to');
            $stage_id = $this->put('stage_id');
            $job_id = $this->put('job_id');
            $stage_name = $this->put('stage_name');
            $action = $this->put('action');
            if($this->Requirements->screening_levels($from,$to,$stage_id,$job_id,$stage_name,$action)){
                $res = $this->JobList->job_boarding($job_id);
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'message' => 'Board stage update successfully',
                ], REST_Controller::HTTP_CREATED);
            }else{
                throw new Exception('Update fail');
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function screening_levels_add_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('from', 'Source Id', 'is_natural');
            $this->form_validation->set_rules('to', 'Target Id', 'is_natural');
            $this->form_validation->set_rules('job_id', 'Job Id', 'trim|required');
            $this->form_validation->set_rules('stage_name', 'Stage Name', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $created_by = $this->user_data->id;
            $from = $this->post('from');
            $to = $this->post('to');
            $job_id = $this->post('job_id');
            $stage_name = $this->post('stage_name');
            $action = $this->post('action');
            if($this->Requirements->screening_levels_add($from,$to,$job_id,$stage_name,$action,$created_by)){
                $res = $this->JobList->job_boarding($job_id);
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'message' => 'Board stage add successfully',
                ], REST_Controller::HTTP_CREATED);
            }else{
                throw new Exception('Add fail');
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function unpublish_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'Job Id', 'is_natural');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $id = $this->put('id');
            if($this->Requirements->unpublish($id,$updated_by)){
                $this->response([
                    'status' => True,
                    'message' => 'Requirement unpublished',
                ], REST_Controller::HTTP_CREATED);
            }else{
                throw new Exception('Requirement unpublished fail');
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function publish_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'Job Id', 'is_natural');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $id = $this->put('id');
            if($this->Requirements->publish($id,$updated_by)){
                $this->response([
                    'status' => True,
                    'message' => 'Requirement published',
                ], REST_Controller::HTTP_CREATED);
            }else{
                throw new Exception('Requirement published fail');
            }
        }catch(\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function export_job_boarding_get()
    {
        try {
            $this->form_validation->set_data($this->get());
            $this->form_validation->set_rules('job_id', 'Job missing', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $job_id = $this->get('job_id');
            $usersData = $this->JobList->export_job_boarding($job_id);
            if ($usersData){
                // $filename = 'Job Boarding ' . date('Y-m-d h-i-sa') . '.csv';
                // header("Content-Description: File Transfer");
                // header("Content-Disposition: attachment; filename=$filename");
                // header("Content-Type: application/csv; ");
                // $file = fopen('php://output', 'w');
                $header = array();
                foreach ($usersData[0] as $k => $val) {
                    $header[] = ['label' => $k,'key' => $k];
                }
                // fputcsv($file, $header);

                // foreach ($usersData as $key => $line) {
                //     fputcsv($file, $line);
                // }

                // fclose($file);
                // exit;
                $this->response([
                    'headers' => $header,
                    'data' => $usersData,
                    'status' => True,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_CREATED);
            }else{
                $this->response([
                    'status' => false,
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        }catch (\Throwable $e){
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
