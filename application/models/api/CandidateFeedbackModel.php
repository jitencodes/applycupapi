<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');
class CandidateFeedbackModel extends CI_Model
{
    var $table = "requirement_candidate_feedback rcf";
    var $candidate_tbl = "candidates c";
    var $employee_tbl = "employee e";
    var $screening_tbl = "requirement_screening_levels rsl";

    public function get_feedback_by_job($candidate_id, $job_id)
    {
        return $this->db->where(["rcf.candidates_id" => $candidate_id, "rcf.requirements_id" => $job_id, "rcf.status" => 1])
            ->select("rcf.*,CONCAT(e.first_name,' ',e.last_name) as employee,rsl.name stage")
            ->order_by("rcf.created_at", "asc")
            ->join($this->screening_tbl, "rsl.id=rcf.requirement_screening_level_id", "left")
            ->join($this->employee_tbl, "e.id=rcf.created_by", "left")
            ->get($this->table)->result_array();
    }

    public function get_feedback_id($id)
    {
        return $this->db->where(["rcf.id" => $id, "rcf.status" => 1])
            ->select("rcf.*,CONCAT(e.first_name,' ',e.last_name) as employee,rsl.name stage")
            ->order_by("rcf.created_at", "asc")
            ->join($this->screening_tbl, "rsl.id=rcf.requirement_screening_level_id", "left")
            ->join($this->employee_tbl, "e.id=rcf.created_by", "left")
            ->get($this->table)->row_array();
    }

    public function manage_feedback($id, $candidates_id, $requirements_id, $requirement_screening_level_id, $communication, $attitude, $potential_learn, $technical_skills, $overall_opinion, $overall_feedback, $is_not_interview, $created_by)
    {
        $add = [
            'candidates_id' => $candidates_id,
            'requirements_id' => $requirements_id,
            'requirement_screening_level_id' => $requirement_screening_level_id,
            'communication' => $communication,
            'attitude' => $attitude,
            'potential_learn' => $potential_learn,
            'technical_skills' => $technical_skills,
            'overall_opinion' => $overall_opinion,
            'overall_feedback' => $overall_feedback,
            'is_not_interview' => $is_not_interview,
            'status' => 1
        ];
        if ($id) {
            $add['update_at'] = date('Y-m-d H:i:s');
            $add['update_by'] = $created_by;
            return $this->db->where('id', $id)->update('requirement_candidate_feedback', $add);
        } else {
            $add['created_at'] = date('Y-m-d H:i:s');
            $add['created_by'] = $created_by;
        }
        return $this->db->insert('requirement_candidate_feedback', $add);
    }

    public function delete_feedback($feedback_id,$job_id,$update_by)
    {
        $update = ['status' => 2, 'update_at' => date('Y-m-d H:i:s'), 'update_by' => $update_by];
        return $this->db->where(['id'=> $feedback_id,'requirements_id' => $job_id])->update('requirement_candidate_feedback', $update);
    }
}
