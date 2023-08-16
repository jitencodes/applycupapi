<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');

class JobListModel extends CI_Model
{
    var $table = "requirements r";
    var $requirement_role = "requirement_role_master rr";
    var $r_skill = "requirement_skills rs";
    var $company = "company c";
    var $location = "location_master l";
    var $employement_type = "employement_type_master etm";
    var $r_location = "requirement_locations rl";
    var $r_qualifications = "requirement_qualifications rq";
    var $employees = "employee e";
    var $r_employees = "requirement_employees re";
    var $skill_master = "skill_master sm";
    var $salary_currency_master = "salary_currency_master scm";
    var $screening_levels = "requirement_screening_levels rsl";
    var $screening_candidate_levels = "requirement_candidate_screening rcs";
    var $qualification_master = "qualification_master qm";
    var $column_search = ["rr.name", "l.name","c.name","l.name","sm.name"];
    public function get_opening_list($user_id, $user_role, $search = false, $start = false, $length = false,$requirement_enable,$start_date = false,$end_date = false)
    {
        // if ($user_role == 3 && $requirement_enable == false) {
        //     $this->db->where('re.employee_id', $user_id);
        //     $this->db->join($this->r_employees,'re.requirement_id=r.id AND re.status=1');
        // }
        // if($user_role == 2){
        //     $this->db->where('r.created_by', $user_id);
        // }
        if ($length != -1)
            $this->db->limit($length, ($start * $length));
        if ($search !== NULL) {
            $i = 0;
            foreach ($this->column_search as $item) // loop column
            {
                if ($i === 0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $search);
                } else {
                    $this->db->or_like($item, $search);
                }
                if($search == "Remote" || $search == 'remote'){
                    $this->db->or_like("r.location_type", 1);
                }elseif($search == "Onsite" || $search == 'onsite'){
                    $this->db->or_like("r.location_type", 2);
                }elseif($search == "Hybrid" || $search == 'hybrid'){
                    $this->db->or_like("r.location_type", 3);
                }
                if (count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
                $i++;
            }
        }
        if($start_date && $end_date){
            $this->db->where('DATE(r.created_at) >=', date('Y-m-d', strtotime($start_date)));
            $this->db->where('DATE(r.created_at) <=', date('Y-m-d', strtotime($end_date)));
        }
        $result = $this->db->select("r.id,IF(r.experience_type=2,CONCAT(r.min_exp,' - ',r.max_exp,' Years'),'Fresher') as experience,IF(r.location_type=2,'Onsite',IF(r.location_type=3,'Hybrid','Remote')) as location_type,GROUP_CONCAT(DISTINCT l.name) as location,rr.name as requirement_role,c.name as company,r.post_on_web,r.created_by,rsm.name as requirement_status")
            ->order_by('r.created_at', 'desc')
            ->group_by('r.id')
            ->join($this->requirement_role, 'rr.id=r.requirement_role_id AND rr.status=1', 'left')
            ->join($this->company, 'c.id=r.company_id AND c.status=1', 'left')
            ->join('requirement_status_master rsm', 'rsm.id=r.requirement_status AND rsm.status=1', 'left')
            ->join($this->employement_type, 'etm.id=r.employement_type AND etm.status=1', 'left')
            ->join($this->r_location, 'rl.requirement_id=r.id AND rl.status=1', 'left outer')
            ->join($this->location, 'l.id=rl.location_master_id AND l.status=1', 'left outer')
            ->join($this->r_skill, 'rs.requirement_id=r.id AND rs.status=1', 'left outer')
            ->join($this->skill_master, 'sm.id=rs.skill_master_id AND sm.status=1', 'left outer')
            ->get($this->table)->result_array();
        foreach ($result as $k => $item) {
            $requirement_screening = $this->db->where('rsl.requirement_id',$item['id'])
                ->select('rsl.name,COUNT(rcs.id) as total')
                ->group_by('rsl.id')
                ->order_by('rsl.ordering','asc')
                ->join($this->screening_candidate_levels,'rcs.requirement_screening_level_id=rsl.id AND rcs.status=1','left outer')
                ->get($this->screening_levels)->result_array();
            $result[$k]['requirement_screening'] = $requirement_screening;
            $employees = $this->db->where(['re.requirement_id' => $item['id'],'re.status' => 1])->select('e.id,e.first_name,e.last_name,e.emp_id,e.email')
                ->join($this->employees,'e.id=re.employee_id AND e.status=1','left')
                ->get($this->r_employees)->result_array();
            $result[$k]['employees'] = $employees;
        }
        return $result;
    }

    public function count_all($user_id, $user_role,$requirement_enable,$start_date = false,$end_date = false){
        // if ($user_role == 3 && $requirement_enable == false){
        //     $this->db->where('re.employee_id', $user_id);
        //     $this->db->join($this->r_employees,'re.requirement_id=r.id AND re.status=1');
        // }
        // if($user_role == 2 && $requirement_enable == false){
        //     $this->db->where('r.created_by', $user_id);
        // }
        if($start_date && $end_date){
            $this->db->where('DATE(r.created_at) >=', date('Y-m-d', strtotime($start_date)));
            $this->db->where('DATE(r.created_at) <=', date('Y-m-d', strtotime($end_date)));
        }
        $this->db->group_by('r.id')
            ->select('r.id')
            ->join($this->requirement_role, 'rr.id=r.requirement_role_id AND rr.status=1', 'left')
            ->join($this->company, 'c.id=r.company_id AND c.status=1', 'left')
            ->join('requirement_status_master rsm', 'rsm.id=r.requirement_status AND rsm.status=1', 'left')
            ->join($this->employement_type, 'etm.id=r.employement_type AND etm.status=1', 'left')
            ->join($this->r_location, 'rl.requirement_id=r.id AND rl.status=1', 'left outer')
            ->join($this->location, 'l.id=rl.location_master_id AND l.status=1', 'left outer')
            ->join($this->r_skill, 'rs.requirement_id=r.id AND rs.status=1', 'left outer')
            ->join($this->skill_master, 'sm.id=rs.skill_master_id AND sm.status=1', 'left outer')
            ->from($this->table);
        return $this->db->count_all_results();
    }

    public function get_job_detail($id){
        $result = $this->db->select("r.id,r.experience_type,r.min_exp,r.max_exp,r.location_type,r.requirement_role_id as role_id,rr.name as role,r.employement_type,etm.name as employement_type_name,c.id as company_id,c.name as company_name,e.id as created_by,CONCAT(e.first_name,' ',IFNULL(e.last_name,''),' - ',e.emp_id) as created_by_name,r.is_industry_standard,r.min_salary,r.max_salary,r.vacancy_count,r.post_on_web,r.is_approved,r.requirement_description,scm.id as currency_id,scm.name as currency_name,npm.id as notice_period_id,npm.name as notice_period_name,rtm.id as req_status_id,rtm.name as req_status_name,r.created_at")
            ->where('r.id',$id)
            ->group_by('r.id')
            ->join($this->requirement_role, 'rr.id=r.requirement_role_id AND rr.status=1', 'left')
            ->join($this->company, 'c.id=r.company_id AND c.status=1', 'left')
            ->join($this->employement_type, 'etm.id=r.employement_type AND etm.status=1', 'left')
            ->join($this->employees,'e.id=r.created_by AND e.status=1','left')
            ->join('notice_period_master npm','npm.id=r.notice_period_master_id AND npm.status=1','left outer')
            ->join('requirement_status_master rtm','rtm.id=r.requirement_status AND rtm.status=1','left outer')
            ->join($this->salary_currency_master,'scm.id=r.salary_currency_master_id AND scm.status=1','left outer')
            ->get($this->table)->row_array();
        if ($result){
            $employees = $this->db->where(['re.requirement_id' => $result['id'],'re.status' => 1])->select("e.id as value,CONCAT(e.first_name,' ',IFNULL(e.last_name,''),' - ',e.emp_id) as label,CONCAT(e.first_name,' ',IFNULL(e.last_name,'')) as employee_name")
                ->join($this->employees,'e.id=re.employee_id AND e.status=1','left')
                ->get($this->r_employees)->result_array();
            $result['employees'] = $employees;
            $result['locations'] = $this->db->where(['rl.status' => 1,'rl.requirement_id' => $result['id']])
                ->select("l.id as value,l.name as label")
                ->join($this->location, 'l.id=rl.location_master_id AND l.status=1', 'left')
                ->get($this->r_location)->result_array();
            $result['skills'] = $this->db->where(['rs.status' => 1,'rs.requirement_id' => $result['id']])
                ->select("sm.id as value,sm.name as label")
                ->join($this->skill_master, 'sm.id=rs.skill_master_id AND sm.status=1', 'left')
                ->get($this->r_skill)->result_array();
            $result['qualifications'] = $this->db->where(['rq.status' => 1,'rq.requirement_id' => $result['id']])
                ->select("qm.id as value,qm.name as label")
                ->join($this->qualification_master,'qm.id=rq.qualification_master_id','left')
                ->get($this->r_qualifications)->result_array();
        }
        return $result;
    }

    public function job_boarding($id,$search = false): array
    {
        $job_detail = $this->db->where('r.id',$id)
            ->select("GROUP_CONCAT(l.name) as location,rr.name as requirement_role")
            ->join($this->requirement_role, 'rr.id=r.requirement_role_id AND rr.status=1', 'left')
            ->join($this->r_location, 'rl.requirement_id=r.id AND rl.status=1', 'left outer')
            ->join($this->location, 'l.id=rl.location_master_id AND l.status=1', 'left outer')
            ->get($this->table)->row_array();
        $requirement_screening = $this->db->where('rsl.requirement_id',$id)->order_by('rsl.ordering','asc')
            ->select("rsl.*,rsl.name as title")
            ->get($this->screening_levels)->result_array();
        foreach ($requirement_screening as $k => $item) {
            if($search != NULL){
                $search_col = ['cd.name','cd.email'];
                $i = 0;
                foreach ($search_col as $col) // loop column
                {
                    if ($i === 0) // first loop
                    {
                        $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                        $this->db->like($col, $search);
                    } else {
                        $this->db->or_like($col, $search);
                    }

                    if (count($search_col) - 1 == $i) //last loop
                        $this->db->group_end(); //close bracket
                    $i++;
                }
            }
            $candidates = $this->db->where(['rcs.requirement_screening_level_id' => $item['id'], 'rcs.status' => 1 ])
                ->select("rcs.id,cd.id as candidate_id,cd.name as candidate_name,rcs.is_favourite,rsl.action as action_allow,rsl.name as stage_name,rsl.id as stage_id,rcs.is_action_taken,rcs.reject_stage_id,rcs.is_hold,rcs.reject_action_type,l.name as reject_stage_name")
                ->order_by('rsl.ordering','asc')
                ->join('candidates cd','cd.id=rcs.candidate_id','left')
                ->join($this->screening_levels,'rsl.id=rcs.requirement_screening_level_id','left')
                ->join('requirement_screening_levels l','l.id=rcs.reject_stage_id','left outer')
                ->get($this->screening_candidate_levels)->result_array();
                foreach ($candidates as $key => $value) {
                    $candidates[$key]['total_feedback'] =  $this->db->where(['candidates_id' => $value['candidate_id'],'requirements_id' => $id,'requirement_screening_level_id' =>$item['id'],'status' => 1])->from('requirement_candidate_feedback')->count_all_results();
                }
            // $candidates_data = ['id' => $k+1, 'description' => $item['name'],'members' => $candidates];
            $requirement_screening[$k]['cards'] = $candidates;
        }
        $job_detail['requirement_screening'] = $requirement_screening;
        return $job_detail;
    }

    public function export_job_boarding($id){
        $requirement_screening = $this->db->where('rsl.requirement_id',$id)->order_by('rsl.ordering','asc')
            ->select("rsl.id,rsl.name as stage")
            ->get($this->screening_levels)->result_array();
        $report = [];
        foreach ($requirement_screening as $k => $item) {
            $candidates = $this->db->where(['rcs.requirement_screening_level_id' => $item['id'], 'rcs.status' => 1 ])
                ->select("cd.name as candidate_name,cd.email")
                ->order_by('rsl.ordering','asc')
                ->join('candidates cd','cd.id=rcs.candidate_id','left')
                ->join($this->screening_levels,'rsl.id=rcs.requirement_screening_level_id','left')
                ->get($this->screening_candidate_levels)->result_array();
            $requirement_screening[$k]['candidates'] = $candidates;
        }
        if($requirement_screening){
            $header = [];
            foreach ($requirement_screening as $value) {
                $header[] = $value['stage'];
            }
            $length = count($header);
            foreach ($requirement_screening as $k => $val) {
                for ($j = 0; $j < $length;$j++) {
                    $report[$j][$val['stage']] = "";
                    foreach ($val['candidates'] as $i => $candidate) {
                        foreach ($header as $head) {
                            if($head == $val['stage']){
                                $report[$i][$val['stage']] = $candidate['candidate_name'].''.(!$candidate['email']?"":' - '.$candidate['email']);
                            }
                        }
                    }
                    if(!$val['candidates']){
                        $report[$j][$val['stage']] = "";
                    }
                }
            }
        }
        return $report;
    }
}
