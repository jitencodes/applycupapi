<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Candidate_data extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('api/CandidateModel', 'Candidate');
        $this->load->model('api/JobListModel', 'JobList');
        $this->auth();
    }

    public function talent_pool_post()
    {
        $id = $this->post('id');
        $search = $this->post('keyword');
        $length = $this->post('length');
        $start = !$this->post('start') ? 0 : ($this->post('start') - 1);
        if ($start) {
            $start = $start * $length;
        }
        $filter = $this->post('filter');
        $user_id = $this->user_data->id;
        $user_role = $this->user_data->role_id;
        $total = $this->Candidate->count_all($search, $filter, $user_id, $user_role);
        $data = $this->Candidate->get_list($id, $search, $start, $length, $filter, $user_id, $user_role);
        if (!empty($data)) {
            $this->set_response(['data' => $data, 'total' => $total, 'role_id' => $user_role, 'base_path' => base_url()], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->set_response([
                'data' => [], 'total' => 0, 'base_path' => base_url(),
                'status' => FALSE,
                'role_id' => $user_role,
                'message' => 'Data could not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function index_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('candidate_name', 'Candidate Name', 'trim|required');
            $this->form_validation->set_rules('job_role', 'Opening', 'trim|required');
            $this->form_validation->set_rules('gender', 'Gender', 'trim|required');
            $this->form_validation->set_rules('email', 'Email', 'trim|required');
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
            $this->form_validation->set_rules('candidate_source', 'Candidate source', 'trim|required');
            $this->form_validation->set_rules('candidate_status', 'Candidate status', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $created_by = $this->user_data->id;
            $candidate_name = $this->post('candidate_name');
            $current_assign_reqirement_id = $this->post('job_role');
            $gender = $this->post('gender');
            $email = $this->post('email');
            $mobile = $this->post('mobile');
            $exprience = $this->post('exprience');
            $relevant_tech_exprience = $this->post('relevant_tech_exprience');
            $current_organisation = $this->post('current_organisation');
            $current_ctc = $this->post('current_ctc');
            $expected_ctc = $this->post('expected_ctc');
            $notice_period = $this->post('notice_period');
            $current_city = $this->post('current_city');
            $preferred_city = $this->post('preferred_city');
            $candidate_source = $this->post('candidate_source');
            $candidate_status = $this->post('candidate_status');
            $remark = $this->post('remark');
            $resume = "";
            if (isset($_FILES['resume'])) {
                $file = $this->MasterModel->upload('resume', 'resumes');
                if ($file['code'] == 1) {
                    $resume = $file['file_url'];
                } else {
                    $this->response([
                        'status' => False,
                        'message' => $file['msg'],
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }
            if ($this->Candidate->get_user_by_email($email)) {
                $this->response([
                    'status' => False,
                    'message' => "This candidate already exits",
                ], REST_Controller::HTTP_OK);
            }
            if ($this->Candidate->add_candidate($candidate_name, $current_assign_reqirement_id, $gender, $email, $mobile, $exprience, $relevant_tech_exprience, $current_organisation, $current_ctc, $expected_ctc, $notice_period, $current_city, $preferred_city, $candidate_source, $candidate_status, $resume,$remark, $created_by)) {
                $this->response([
                    'status' => True,
                    'message' => 'Data add successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data add failed',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function index_put(){
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'Candidate id', 'trim|required');
            $this->form_validation->set_rules('candidate_name', 'Candidate Name', 'trim|required');
            $this->form_validation->set_rules('email', 'Email', 'trim|required');
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $id = $this->put('id');
            $candidate_name = $this->put('candidate_name');
            $email = $this->put('email');
            $mobile = $this->put('mobile');
            $exprience = $this->put('total_experience');
            $relevant_tech_exprience = $this->put('relevant_experience');
            $current_organization = $this->put('current_organization');
            $current_ctc = $this->put('current_ctc');
            $expected_ctc = $this->put('expected_ctc');
            $notice_period = $this->put('notice_period');
            $current_city = $this->put('current_city');
            $preferred_city = $this->put('prefer_locations');
            $remark = $this->put('remark');
            if ($this->Candidate->get_user_by_email($email,$id)) {
                $this->response([
                    'status' => False,
                    'message' => "This candidate email already exits",
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if ($this->Candidate->update_candidate($id,$candidate_name, $email, $mobile, $exprience, $relevant_tech_exprience, $current_organization, $current_ctc, $expected_ctc, $notice_period, $current_city, $preferred_city,$remark, $updated_by)) {
                $this->response([
                    'status' => True,
                    'message' => 'Data update successfully',
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

    public function resume_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('candidate_id', 'Candidate', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $candidate_id = $this->post('candidate_id');
            $resume = "";
            if (isset($_FILES['resume'])) {
                $file = $this->MasterModel->upload('resume', 'resumes');
                if ($file['code'] == 1) {
                    $resume = $file['file_url'];
                } else {
                    throw new Exception('Resume uploading fail');
                }
            } else {
                throw new Exception('Resume file missing');
            }
            if ($this->Candidate->candidate_resume($candidate_id, $resume, $updated_by)) {
                $this->response([
                    'status' => True,
                    'message' => 'Resume update successfully',
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

    public function assign_opening_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('candidate_id', 'Candidate', 'trim|required');
            $this->form_validation->set_rules('job_id', 'Job', 'trim|required');
            $this->form_validation->set_rules('stage_id', 'Stage', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $candidate_id = $this->put('candidate_id');
            $stages = $this->put('stage_id');
            $job_id = $this->put('job_id');
            $prv_job = $this->put('prv_job');
            if ($this->Candidate->candidate_manage_opening($candidate_id, $job_id, $stages, $updated_by, $prv_job)) {
                $this->response([
                    'status' => True,
                    'message' => 'Resume update successfully',
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

    public function candidate_stage_move_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'Source Id', 'trim|required');
            $this->form_validation->set_rules('requirement_screening_level_id', 'Stage Id', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $id = $this->put('id');
            $stages = $this->put('requirement_screening_level_id');
            $ordering = $this->put('ordering');
            $job_id = $this->put('job_id');
            if ($this->Candidate->candidate_stage_move($id, $stages, $ordering, $updated_by)) {
                $res = $this->JobList->job_boarding($job_id);
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'message' => 'Candiate stage update successfully',
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

    public function job_stage_move_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('from', 'Source Id', 'is_natural');
            $this->form_validation->set_rules('to', 'Target Id', 'is_natural');
            $this->form_validation->set_rules('job_id', 'Job Id', 'trim|required');
            $this->form_validation->set_rules('stage_id', 'Stage Id', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $from = $this->put('from') + 1;
            $to = $this->put('to') + 1;
            $stage_id = $this->put('stage_id');
            $job_id = $this->put('job_id');
            if ($this->Candidate->job_stage_move($from, $to, $stage_id, $job_id, $updated_by)) {
                $res = $this->JobList->job_boarding($job_id);
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'message' => 'Board stage update successfully',
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

    public function candidate_reject_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('requirement_candidate_screening_id', 'Id', 'required');
            $this->form_validation->set_rules('reject_stage', 'Reject Stage', 'required');
            $this->form_validation->set_rules('reject_action_type', 'Action Type', 'required');
            $this->form_validation->set_rules('job_id', 'Job id', 'required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $requirement_candidate_screening_id = $this->put('requirement_candidate_screening_id');
            $feedback = $this->put('feedback');
            $reject_stage = $this->put('reject_stage');
            $remark = $this->put('remark');
            $reject_action_type = $this->put('reject_action_type');
            $job_id = $this->put('job_id');
            if ($this->Candidate->candidate_reject($requirement_candidate_screening_id, $feedback, $reject_stage, $remark, $reject_action_type, $job_id, $updated_by)) {
                $res = $this->JobList->job_boarding($job_id);
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'message' => 'Candidate reject successfully',
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

    public function candidate_assign_openings_get()
    {
        try {
            $this->form_validation->set_data($this->get());
            $this->form_validation->set_rules('job_id', 'Job id', 'required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $added_by_me = $this->user_data->id;
            $job_id = $this->get('job_id');
            $filter = $this->get('filter');
            $data = $this->Candidate->candidate_assign_openings($job_id, $filter, $added_by_me);
            if ($data) {
                $this->response([
                    'status' => True,
                    'data' => $data,
                    'message' => 'Data found',
                ], REST_Controller::HTTP_ACCEPTED);
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

    public function stage_move_by_id_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            if (is_array($this->put('id'))) {
                $this->form_validation->set_rules('id[]', 'Id', 'required');
            } else {
                $this->form_validation->set_rules('id', 'Id', 'required');
            }
            $this->form_validation->set_rules('stage_id', 'Stages', 'required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $id = $this->put('id');
            $requirement_screening_level_id = $this->put('stage_id');
            if ($this->Candidate->stage_move_by_id($id, $requirement_screening_level_id, $updated_by)) {
                $this->response([
                    'status' => True,
                    'message' => 'Data updated',
                ], REST_Controller::HTTP_ACCEPTED);
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

    public function candidate_job_assign_mail_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('candidate_id', 'Candidate Id', 'required');
            $this->form_validation->set_rules('job_id', 'Job Id', 'required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $candidate_id = $this->post('candidate_id');
            $job_id = $this->post('job_id');
            if ($this->Candidate->candidate_assign_mail($candidate_id, $job_id)) {
                $this->response([
                    'status' => True,
                    'message' => 'Email Send successfully',
                ], REST_Controller::HTTP_ACCEPTED);
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

    public function candidate_detail_get()
    {
        try {
            $this->form_validation->set_data($this->get());
            $this->form_validation->set_rules('candidate_id', 'Candidate Id', 'required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $candidate_id = $this->get('candidate_id');
            // $user_id = $this->user_data->id;
            $user_role = $this->user_data->role_id;
            $res = $this->Candidate->candidate_detail($candidate_id,$user_role);
            if ($res) {
                $this->response([
                    'data' => $res,
                    'status' => True,
                    'url' => base_url(),
                    'message' => 'Data found successfully',
                ], REST_Controller::HTTP_ACCEPTED);
            } else {
                // throw new Exception('Data not found');
                $this->response([
                    'data' => [],
                    'status' => True,
                    'url' => base_url(),
                    'message' => 'Data not found',
                ], REST_Controller::HTTP_ACCEPTED);
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function candidate_hold_put()
    {
        try{
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'Id', 'required');
            $this->form_validation->set_rules('requirement_screening_level_id', 'Requirement Screening Level Id', 'required');
            $this->form_validation->set_rules('job_id', 'Job id', 'required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $requirement_screening_level_id = $this->put('requirement_screening_level_id');
            $id = $this->put('id');
            $job_id = $this->put('job_id');
            if ($this->Candidate->candidate_hold($id, $requirement_screening_level_id, $updated_by)) {
                $res = $this->JobList->job_boarding($job_id);
                $this->response([
                    'status' => True,
                    'data' => $res,
                    'message' => 'Candidate hold successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Candidate hold fail');
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
