<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Candidate_interview extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('api/CandidateInterviewModel', 'CandidateInterview');
        $this->auth();
    }

    public function index_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('id', 'Id', 'trim|required');
            $this->form_validation->set_rules('interview_mode', 'Interview Mode', 'trim|required');
            // $this->form_validation->set_rules('interviewer', 'Interviewer', 'required');
            $this->form_validation->set_rules('interview_date', 'Interview Date', 'trim|required');
            $this->form_validation->set_rules('duration', 'Duration', 'trim|required');
            $this->form_validation->set_rules('start_time', 'Start Time', 'trim|required');
            $this->form_validation->set_rules('end_time', 'End Time', 'trim|required');
            $this->form_validation->set_rules('type', 'Form Type', 'trim|required');
            if ($this->post('interview_mode') == 1) {
                $this->form_validation->set_rules('candidate_number', 'Candidate Number', 'trim|required');
            }elseif($this->post('interview_mode') == 2){
                $this->form_validation->set_rules('interview_address', 'Interview location address', 'trim|required');
            }elseif($this->post('interview_mode') == 3){
                $this->form_validation->set_rules('google_meet', 'Google Meet', 'trim|required');
            }
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->post('id');
            $interview_mode = $this->post('interview_mode');
            $interviewer = $this->post('interviewer');
            $interview_date = $this->post('interview_date');
            $duration = $this->post('duration');
            $start_time = $this->post('start_time');
            $end_time = $this->post('end_time');
            $candidate_number = $this->post('candidate_number');
            $interview_address = $this->post('interview_address');
            $google_meet = $this->post('google_meet');
            $created_by = $this->user_data->id;
            $type = $this->post('type');
            if ($this->CandidateInterview->schedule_interview($id, $interview_mode, $interviewer, $interview_date, $duration, $start_time, $end_time, $candidate_number, $interview_address, $google_meet, $created_by, $type)) {
                $this->response([
                    'status' => True,
                    'message' => 'Interview schedule successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Feedback add fail');
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function index_get(){
        try {
            $this->form_validation->set_data($this->get());
            $this->form_validation->set_rules('id', 'Id', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->get('id');
            $result = $this->CandidateInterview->get_schedule_interview($id);
            if($result){
                $this->response([
                    'data' => $result,
                    'status' => True,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
            } else {
                throw new Exception('Data not found');
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function resend_mail_post(){
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('id', 'Id', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->post('id');
            if($this->CandidateInterview->candidate_interview_mail($id)){
                $this->response([
                    'status' => True,
                    'message' => 'Email send succesfully',
                ], REST_Controller::HTTP_ACCEPTED);
            } else {
                throw new Exception('Data not found');
            }
        } catch (\Throwable $th) {
            $this->response([
                'status' => False,
                'message' => $th->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}