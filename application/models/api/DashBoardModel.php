<?php
if (!defined('BASEPATH')) exit('No direct script allowed');
date_default_timezone_set('Asia/Kolkata');
class DashBoardModel extends CI_Model
{
    // opening data ==============================
    public function openings($user_id, $user_role)
    {
        $total = $this->total_opening($user_id, $user_role);
        $today = $this->today_opening($user_id, $user_role);
        $month = $this->month_opening($user_id, $user_role);
        if ($total) {
            $data[] = ['label' => 'Total Openings', 'Total' => $total];
        } else {
            $data[] = ['label' => 'Total Openings', 'Total' => 0];
        }
        if ($today) {
            $data[] = ['label' => 'Created today', 'Total' => $today];
        } else {
            $data[] = ['label' => 'Created today', 'Total' => 0];
        }
        if ($month) {
            $data[] = ['label' => 'Created within a month', 'Total' => $month];
        } else {
            $data[] = ['label' => 'Created within a month', 'Total' => 0];
        }
        $total_opening = 0;
        if ($total){
            $total_opening = $total;
        }
        return ['total_opening' => $total_opening, 'opening' => $data];
    }


    public function today_opening($user_id, $user_role)
    {
        if ($user_role == 3 || $user_role == 2) {
            $this->db->where('created_by', $user_id);
        }
        return $this->db->where('DATE(created_at)', date('Y-m-d'))->from('requirements')->count_all_results();
    }

    public function total_opening($user_id, $user_role)
    {
        if ($user_role == 3 || $user_role == 2) {
            $this->db->where('created_by', $user_id);
        }
        return $this->db->from('requirements')->count_all_results();
    }

    public function month_opening($user_id, $user_role)
    {
        if ($user_role == 3 || $user_role == 2) {
            $this->db->where('created_by', $user_id);
        }
        return $this->db->where('MONTH(created_at)', date('m'))
            ->where('YEAR(created_at)', date('Y'))->from('requirements')->count_all_results();
    }

    // Joining in a Month ========================

    public function candidates_joining($user_id, $user_role)
    {
        if ($user_role == 3 || $user_role == 2) {
            $this->db->where('c.created_by', $user_id);
        }
        $candidates = $this->db->where('MONTH(c.created_at)', date('m'))
            ->group_by('c.id')
            ->order_by('c.created_at','desc')
            ->select("c.name,rrm.name as job_role,com.name as company,c.created_by,rsl.name as candidate_status,r.id as job_id,c.created_at")
            ->join('requirement_role_master rrm', 'rrm.id=c.requirement_role_id AND rrm.status=1', 'left')
            ->join('requirements r', 'r.id=c.current_assign_reqirement_id', 'left')
            ->join('requirement_candidate_screening rcs', 'rcs.candidate_id=c.id AND rcs.status=1', 'left')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id AND rsl.requirement_id=r.id AND rsl.status=1', 'left')
            ->join('company com', 'com.id=r.company_id AND com.status=1', 'left')
            ->where('MONTH(c.created_at)', date('m'))
            ->where('YEAR(c.created_at)', date('Y'))->get('candidates c')->result_array();

        foreach ($candidates as $key => $candidate) {
            $candidates[$key]['recruiters'] = $this->db->where(['re.requirement_id' => $candidate['job_id'], 're.status' => 1, 'e.role_id' => 3])
                ->select("e.id,CONCAT(e.first_name,' ',IFNULL(e.last_name,'')) as recruiter,e.emp_id,e.email")
                ->join('employee e', 'e.id=re.employee_id AND e.status=1', 'left')
                ->get('requirement_employees re')->result_array();
        }

        return ['total_candidates' => count($candidates), 'candidates' => $candidates];
    }

    // Today interviews ============================

    public function today_interview($user_id, $user_role)
    {
        if ($user_role == 3) {
            $this->db->where('cir.employee_id', $user_id);
        } elseif ($user_role == 2) {
            $this->db->where('ci.created_by', $user_id);
        }
        $candidate_interviews = $this->db->where(['ci.status' => 1, 'ci.interview_date' => date('Y-m-d')])
            ->select("c.id,c.name as candidate,c.mobile,c.email,rrm.name as job_role,com.name as company,ci.interview_start_time,ci.interview_end_time,ci.id as interview_id,CASE ci.interview_type WHEN 1 THEN 'Telephonic' WHEN 2 THEN 'Face to Face' WHEN 3 THEN 'Google Meet' END as interview_mode")
            ->group_by('ci.id')
            ->join('candidate_interviewer cir', 'cir.candidate_interviews_id=ci.id AND cir.status=1')
            ->join('requirement_candidate_screening rcs', 'rcs.id=ci.requirement_candidate_screening_id AND rcs.status=1', 'left')
            ->join('candidates c', 'c.id=rcs.candidate_id')
            ->join('requirement_screening_levels rcl', 'rcl.id=rcs.requirement_screening_level_id', 'left')
            ->join('requirements r', 'r.id=rcl.requirement_id', 'left')
            ->join('requirement_role_master rrm', 'rrm.id=r.requirement_role_id', 'left')
            ->join('company com', 'com.id=r.company_id', 'left')
            ->get('candidate_interviews ci')->result_array();
        foreach ($candidate_interviews as $key => $candidate) {
            $candidate_interviews[$key]['recruiters'] = $this->db->where(['re.candidate_interviews_id' => $candidate['interview_id'], 're.status' => 1, 'e.role_id' => 3])
                ->select("e.id,CONCAT(e.first_name,' ',IFNULL(e.last_name,'')) as recruiter,e.emp_id,e.email")
                ->join('employee e', 'e.id=re.employee_id AND e.status=1', 'left')
                ->get('candidate_interviewer re')->result_array();
        }
        return ['total_interviews' => count($candidate_interviews), 'candidates_interviews' => $candidate_interviews];
    }

    // CV Sourced ========================

    public function cv_source($user_id, $user_role)
    {
        $today = $this->today_cv_source($user_id, $user_role);
        $week = $this->week_cv_source($user_id, $user_role);
        $month = $this->month_cv_source($user_id, $user_role);
        if ($today) {
            $data[] = ['label' => 'Today', 'Total' => $today];
        } else {
            $data[] = ['label' => 'Today', 'Total' => 0];
        }
        if ($week) {
            $data[] = ['label' => 'This Week', 'Total' => $week];
        } else {
            $data[] = ['label' => 'This Week', 'Total' => 0];
        }
        if ($month) {
            $data[] = ['label' => 'This Month', 'Total' => $month];
        } else {
            $data[] = ['label' => 'This Month', 'Total' => 0];
        }
        $total_opening = 0;
        if($month) {
            $total_opening = $month;
        }
        return ['total_cv_source' => $total_opening, 'cv_sources' => $data];
    }

    public function today_cv_source($user_id, $user_role)
    {
        if ($user_role == 3 || $user_role == 2) {
            $this->db->where('created_by', $user_id);
        }
        return $this->db->where('DATE(created_at)', date('Y-m-d'))->from('candidates')->count_all_results();
    }

    public function week_cv_source($user_id, $user_role)
    {
        if ($user_role == 3 || $user_role == 2) {
            $this->db->where('created_by', $user_id);
        }
        $date_start = strtotime('last Sunday');
        $week_start = date('Y-m-d', $date_start);
        $date_end = strtotime('next Sunday');
        $week_end = date('Y-m-d', $date_end);
        $this->db->where('created_at >=', $week_start);
        $this->db->where('created_at <=', $week_end);
        return $this->db->from('candidates')->count_all_results();
    }

    public function month_cv_source($user_id, $user_role)
    {
        if ($user_role == 3 || $user_role == 2) {
            $this->db->where('created_by', $user_id);
        }
        return $this->db->where('MONTH(created_at)', date('m'))
            ->where('YEAR(created_at)', date('Y'))->from('candidates')->count_all_results();
    }

    // Today CV sources with recruiter name  ===================

    public function today_recruiter_cv()
    {
        $this->db->select("e.id,CONCAT(e.first_name,' ',IFNULL(e.last_name,'')) as recruiter,COUNT(c.id) as total");
        $this->db->group_by('e.id');
        // $this->db->having('total >',0);
        $this->db->join('employee e', 'e.id=c.created_by AND e.status=1', 'left');
        return $this->db->where('DATE(created_at)', date('Y-m-d'))->get('candidates c')->result_array();
    }

    // Candidate joined ========================================

    public function candidates_month_leaderboard($user_id, $user_role)
    {
        if ($user_role == 2 || $user_role == 3) {
            $this->db->where('c.created_by', $user_id);
        }
        $recruiters = $this->db->where(['rcs.status' => 1, 'rsl.name' => 'Joining'])
            ->where('MONTH(rcs.created_at)', date('m'))
            ->where('YEAR(rcs.created_at)', date('Y'))
            ->select("e.id,CONCAT(e.first_name,' ',IFNULL(e.last_name,'')) as recruiter,COUNT(c.id) as total")
            ->group_by('e.id')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->join('candidates c', 'c.id=rcs.candidate_id')
            ->join('employee e', 'e.id=c.created_by AND e.status=1', 'left')
            ->get('requirement_candidate_screening rcs')->result_array();
        $total_placed = 0;
        foreach ($recruiters as $value) {
            $total_placed = $total_placed + $value['total'];
        }
        if ($total_placed == 0) {
            $recruiters = [];
        }
        return ['total_placed' => $total_placed, 'recruiters' => $recruiters];
    }

    public function candidates_placed($user_id, $user_role)
    {
        $today = $this->total_candidates_today_placed($user_id, $user_role);
        $week = $this->total_candidates_week_placed($user_id, $user_role);
        $month = $this->total_candidates_month_placed($user_id, $user_role);
        if ($today) {
            $data[] = ['label' => 'Today', 'Total' => $today];
        } else {
            $data[] = ['label' => 'Today', 'Total' => 0];
        }
        if ($week) {
            $data[] = ['label' => 'This Week', 'Total' => $week];
        } else {
            $data[] = ['label' => 'This Week', 'Total' => 0];
        }
        if ($month) {
            $data[] = ['label' => 'This Month', 'Total' => $month];
        } else {
            $data[] = ['label' => 'This Month', 'Total' => 0];
        }
        $total_candidates_placed = 0;
        if($month){
            $total_candidates_placed = $month;
        }
        return ['total_candidates_placed' => $total_candidates_placed, 'candidates_placed' => $data];
    }

    public function total_candidates_today_placed($user_id, $user_role)
    {
        if ($user_role == 2 || $user_role == 3) {
            $this->db->where('c.created_by', $user_id);
        }
        $recruiters = $this->db->where(['rcs.status' => 1, 'rsl.name' => 'Joining'])
            ->where('DATE(rcs.updated_at)', date('Y-m-d'))
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->join('candidates c', 'c.id=rcs.candidate_id')
            ->from('requirement_candidate_screening rcs')->count_all_results();

        return $recruiters;
    }

    public function total_candidates_week_placed($user_id, $user_role)
    {
        if ($user_role == 2 || $user_role == 3) {
            $this->db->where('c.created_by', $user_id);
        }
        $date_start = strtotime('last Sunday');
        $week_start = date('Y-m-d', $date_start);
        $date_end = strtotime('next Sunday');
        $week_end = date('Y-m-d', $date_end);
        $this->db->where('rcs.updated_at >=', $week_start);
        $this->db->where('rcs.updated_at <=', $week_end);
        $recruiters = $this->db->where(['rcs.status' => 1, 'rsl.name' => 'Joining'])
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->join('candidates c', 'c.id=rcs.candidate_id')
            ->from('requirement_candidate_screening rcs')->count_all_results();

        return $recruiters;
    }

    public function total_candidates_month_placed($user_id, $user_role)
    {
        if ($user_role == 2 || $user_role == 3) {
            $this->db->where('c.created_by', $user_id);
        }
        $recruiters = $this->db->where(['rcs.status' => 1, 'rsl.name' => 'Joining'])
            ->where('MONTH(rcs.updated_at)', date('m'))
            ->where('YEAR(rcs.updated_at)', date('Y'))
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->join('candidates c', 'c.id=rcs.candidate_id')
            ->from('requirement_candidate_screening rcs')->count_all_results();

        return $recruiters;
    }
}
