<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');
class CandidateInterviewModel extends CI_Model
{
    var $table = "candidate_interviews";
    var $table_allis = "candidate_interviews ci";

    public function schedule_interview($id, $interview_mode, $interviewers, $interview_date, $duration, $start_time, $end_time, $candidate_number, $interview_address, $google_meet, $created_by, $type)
    {
        $add = [
            'requirement_candidate_screening_id' => $id,
            'interview_type' => $interview_mode,
            'interview_date' => $interview_date,
            'interview_duration' => $duration,
            'interview_start_time' => $start_time,
            'interview_end_time' => $end_time,
            'status' => 1
        ];
        if ($interview_mode == 1) {
            $add['interview_input'] = $candidate_number;
        } elseif ($interview_mode == 2) {
            $add['interview_input'] = $interview_address;
        } elseif ($interview_mode == 3) {
            $add['interview_input'] = $google_meet;
        }
        if ($type == 'EDIT') {
            $add['updated_by'] = $created_by;
            $add['updated_at'] = date('Y-m-d H:i:s');
            $add['is_reschedule'] = 1;
            $this->db->where('requirement_candidate_screening_id', $id);
            $res = $this->db->update($this->table, $add);
            $result = $this->db->where('requirement_candidate_screening_id', $id)->get($this->table)->row_array();
            $add_id = $result['id'];
        } else {
            $add['created_by'] = $created_by;
            $add['created_at'] = date('Y-m-d H:i:s');
            $res = $this->db->insert($this->table, $add);
            $add_id = $this->db->insert_id();
        }
        if ($res) {
            $this->db->where('id', $id)->update('requirement_candidate_screening', ['is_action_taken' => 1,'is_hold' => 0]);
            $this->interviewers($add_id, $interviewers);
            try {
                $this->candidate_interview_mail($id);
            } catch (\Throwable $th) {
                log_message('error', $th->getMessage(), false);
            }
            return true;
        } else {
            return false;
        }
    }

    public function interviewers($id, $interviewers)
    {
        $this->db->where('candidate_interviews_id', $id)->update('candidate_interviewer', ['status' => 0]);
        if ($interviewers) {
            foreach ($interviewers as $key => $interviewer) {
                $res = $this->db->where(['employee_id' => $interviewer['value'], 'candidate_interviews_id' => $id])->get('candidate_interviewer')->row_array();
                if ($res) {
                    $this->db->where('id', $res['id'])->update('candidate_interviewer', ['status' => 1]);
                } else {
                    $add = ['candidate_interviews_id' => $id, 'employee_id' => $interviewer['value'], 'status' => 1];
                    $this->db->insert('candidate_interviewer', $add);
                }
            }
        }
        return true;
    }

    public function get_schedule_interview($id)
    {
        $res = $this->db->where('requirement_candidate_screening_id', $id)->get($this->table)->row_array();
        if ($res) {
            $res['interviewers'] = $this->db->where(['candidate_interviews_id' => $res['id'], 'ci.status' => 1, 'e.status' => 1])
                ->select("e.id as value,CONCAT(e.first_name,' ',IFNULL(e.last_name,''),' - ',e.emp_id) as label")
                ->join('employee e', 'e.id=ci.employee_id')
                ->get('candidate_interviewer ci')->result_array();
        }
        return $res;
    }

    public function candidate_req_detail($candidate_interviews_id)
    {
        $this->db->select("ci.*,rsl.name as stage_name,rrm.name as requirement_role,cd.name as candidate_name,cd.email,c.name as company,cd.email as candidate_email")
            ->where('ci.requirement_candidate_screening_id', $candidate_interviews_id)
            ->join('requirement_candidate_screening rcs', 'rcs.id=ci.requirement_candidate_screening_id')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->join('requirements r', 'r.id=rsl.requirement_id')
            ->join('requirement_role_master rrm', 'rrm.id=r.requirement_role_id')
            ->join('company c', 'c.id=r.company_id')
            ->join('candidates cd', 'cd.id=rcs.candidate_id');
        return $this->db->get($this->table_allis)->row_array();
    }

    public function candidate_interview_mail($candidate_interviews_id)
    {
        $this->load->model('MasterModel');
        $candidate_detail = $this->candidate_req_detail($candidate_interviews_id);
        if ($candidate_detail) {
            if ($candidate_detail['interview_type'] == 2) {
                $interview_type = "Google Meet";
            } elseif ($candidate_detail['interview_type'] == 2) {
                $interview_type = "Face to Face";
            } else {
                $interview_type = "Telephonic";
            }
            if ($candidate_detail['is_reschedule']) {
                $schedule = "rescheduled";
            } else {
                $schedule = "scheduled";
            }
            $data = [
                'candidate_name' => $candidate_detail['candidate_name'],
                'job_role' => $candidate_detail['requirement_role'],
                'company_name' => $candidate_detail['company'],
                'interview_type' => $interview_type,
                'interview_mode' => $candidate_detail['interview_type'],
                'interview_input' => $candidate_detail['interview_input'],
                'interview_datetime' => date('d M, Y', strtotime($candidate_detail['interview_date'])) . ' ' . date('h:i A', strtotime($candidate_detail['interview_start_time'])) . ' ' . date('h:i A', strtotime($candidate_detail['interview_end_time'])),
            ];
            $email_template = $this->load->view('email_template/interview_schedule', $data, true);
            $candidate_email = $candidate_detail['candidate_email'];
            $subject = "$interview_type Interview $schedule for the " . $candidate_detail['requirement_role'] . " position";
            return $this->MasterModel->send_email('job@applycup.com',$candidate_email, $subject, $email_template);
        }
    }
}
