<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Requirement_candidate_notes extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('api/CandidateModel', 'Candidate');
        $this->load->model('api/RequirementCandidateNotesModel', 'RequirementCandidateNotes');
        $this->auth();
    }

    public function index_get()
    {
        try {
            $id = $this->get('id');
            $data = $this->RequirementCandidateNotes->get($id);
            if (!empty($data)) {
                $this->set_response(['data' => $data,'status' => true,'message' => 'Data found'], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->set_response([
                    'data' => [],
                    'status' => FALSE,
                    'message' => 'Data could not be found'
                ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function index_post(){
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('requirement_candidate_screening_id', 'Candidate screening id', 'trim|required');
            $this->form_validation->set_rules('notes', 'Notes', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $created_by = $this->user_data->id;
            $requirement_candidate_screening_id = $this->post('requirement_candidate_screening_id');
            $notes = $this->post('notes');
            $is_private = $this->post('is_private');
            if ($this->RequirementCandidateNotes->add($requirement_candidate_screening_id, $notes, $is_private,$created_by)) {
                $this->response([
                    'status' => True,
                    'message' => 'Notes add successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Notes add fail');
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
            $this->form_validation->set_rules('id', 'Id', 'trim|required');
            $this->form_validation->set_rules('notes', 'Notes', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $update_by = $this->user_data->id;
            $id = $this->put('id');
            $notes = $this->put('notes');
            $is_private = $this->put('is_private');
            if ($this->RequirementCandidateNotes->update($id, $notes, $is_private, $update_by)) {
                $this->response([
                    'status' => True,
                    'message' => 'Notes update successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Notes update fail');
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function delete_put(){
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'Id', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->put('id');
            if ($this->RequirementCandidateNotes->delete($id)) {
                $this->response([
                    'status' => True,
                    'message' => 'Notes deleted',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Notes update fail');
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
