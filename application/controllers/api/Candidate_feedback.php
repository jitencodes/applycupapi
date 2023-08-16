<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Candidate_feedback extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('api/CandidateFeedbackModel', 'CandidateFeedback');
        $this->auth();
    }

    public function index_get()
    {
        try {
            $this->form_validation->set_data($this->get());
            $this->form_validation->set_rules('candidate_id', 'Candidate Id', 'trim|required');
            $this->form_validation->set_rules('job_id', 'Job Id', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $candidate_id = $this->get('candidate_id');
            $job_id = $this->get('job_id');
            $data = $this->CandidateFeedback->get_feedback_by_job($candidate_id, $job_id);
            if ($data) {
                $this->response([
                    'status' => True,
                    'data' => $data,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'data' => "",
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_OK);
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function feedback_by_id_get()
    {
        try {
            $this->form_validation->set_data($this->get());
            $this->form_validation->set_rules('id', 'Feedback Id', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->get('id');
            $data = $this->CandidateFeedback->get_feedback_id($id);
            if ($data) {
                $this->response([
                    'status' => True,
                    'data' => $data,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Update fail');
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function manage_feedback_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('candidates_id', 'Candidate Id', 'trim|required');
            $this->form_validation->set_rules('requirements_id', 'Job Id', 'trim|required');
            $this->form_validation->set_rules('requirement_screening_level_id', 'Stage Id', 'trim|required');
            $this->form_validation->set_rules('communication', 'Communication', 'trim|required');
            $this->form_validation->set_rules('attitude', 'Attitude', 'trim|required');
            $this->form_validation->set_rules('potential', 'Potential', 'trim|required');
            $this->form_validation->set_rules('technical', 'Technical', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->post('id');
            $candidates_id = $this->post('candidates_id');
            $requirements_id = $this->post('requirements_id');
            $requirement_screening_level_id = $this->post('requirement_screening_level_id');
            $communication = $this->post('communication');
            $attitude = $this->post('attitude');
            $potential_learn = $this->post('potential');
            $technical_skills = $this->post('technical');
            $overall_opinion = $this->post('overall_opinion');
            $overall_feedback = $this->post('overall_feedback');
            $is_not_interview = $this->post('interview');
            $created_by = $this->user_data->id;
            if ($this->CandidateFeedback->manage_feedback($id, $candidates_id, $requirements_id, $requirement_screening_level_id, $communication, $attitude, $potential_learn, $technical_skills, $overall_opinion, $overall_feedback, $is_not_interview, $created_by)) {
                $this->response([
                    'status' => True,
                    'message' => 'Feedback add successfully',
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

    public function feedback_delete_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'Feedback Id', 'trim|required');
            $this->form_validation->set_rules('job_id', 'Job Id', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $feedback_id = $this->put('id');
            $job_id = $this->put('job_id');
            $update_by = $this->user_data->id;
            if ($this->CandidateFeedback->delete_feedback($feedback_id,$job_id,$update_by)) {
                $this->response([
                    'status' => True,
                    'message' => 'Feedback deleted successfully',
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
}
