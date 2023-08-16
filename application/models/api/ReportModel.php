<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');
class ReportModel extends CI_Model
{

    public function get_cv_report($year)
    {
        $query = "SELECT cv_source_report.employee_id,employee.emp_id,employee.first_name,employee.last_name
        , SUM(CASE WHEN month = 1 AND year=$year THEN total_cv_source END) January
        , SUM(CASE WHEN month = 2 AND year=$year THEN total_cv_source END) February
        , SUM(CASE WHEN month = 3 AND year=$year THEN total_cv_source END) March
        , SUM(CASE WHEN month = 4 AND year=$year THEN total_cv_source END) April
        , SUM(CASE WHEN month = 5 AND year=$year THEN total_cv_source END) May
        , SUM(CASE WHEN month = 6 AND year=$year THEN total_cv_source END) June
        , SUM(CASE WHEN month = 7 AND year=$year THEN total_cv_source END) July
        , SUM(CASE WHEN month = 8 AND year=$year THEN total_cv_source END) August
        , SUM(CASE WHEN month = 9 AND year=$year THEN total_cv_source END) September
        , SUM(CASE WHEN month = 10 AND year=$year THEN total_cv_source END) October
        , SUM(CASE WHEN month = 11 AND year=$year THEN total_cv_source END) November
        , SUM(CASE WHEN month = 12 AND year=$year THEN total_cv_source END) December FROM(SELECT cv_source_report.*, EXTRACT(YEAR FROM cv_source_report.date) year, EXTRACT(MONTH FROM cv_source_report.date) month FROM cv_source_report) cv_source_report LEFT JOIN employee ON employee.id=cv_source_report.employee_id GROUP BY employee_id ORDER BY employee_id;";
        $query = $this->db->query($query);
        return $query->result_array();
    }

    public function get_candidates_placement($year)
    {
        $query = "SELECT candidates_placement_report.employee_id,employee.emp_id,employee.first_name,employee.last_name
        , SUM(CASE WHEN month = 1 AND year=$year THEN total_placement END) January
        , SUM(CASE WHEN month = 2 AND year=$year THEN total_placement END) February
        , SUM(CASE WHEN month = 3 AND year=$year THEN total_placement END) March
        , SUM(CASE WHEN month = 4 AND year=$year THEN total_placement END) April
        , SUM(CASE WHEN month = 5 AND year=$year THEN total_placement END) May
        , SUM(CASE WHEN month = 6 AND year=$year THEN total_placement END) June
        , SUM(CASE WHEN month = 7 AND year=$year THEN total_placement END) July
        , SUM(CASE WHEN month = 8 AND year=$year THEN total_placement END) August
        , SUM(CASE WHEN month = 9 AND year=$year THEN total_placement END) September
        , SUM(CASE WHEN month = 10 AND year=$year THEN total_placement END) October
        , SUM(CASE WHEN month = 11 AND year=$year THEN total_placement END) November
        , SUM(CASE WHEN month = 12 AND year=$year THEN total_placement END) December FROM(SELECT candidates_placement_report.*, EXTRACT(YEAR FROM candidates_placement_report.report_date) year, EXTRACT(MONTH FROM candidates_placement_report.report_date) month FROM candidates_placement_report) candidates_placement_report LEFT JOIN employee ON employee.id=candidates_placement_report.employee_id GROUP BY employee_id ORDER BY employee_id;";
        $query = $this->db->query($query);
        return $query->result_array();
    }

    public function get_candidates_source($source_id = false, $year = false)
    {
        $q = "";
        if ($source_id) {
            $q = "WHERE candidates.candidate_source_master_id=$source_id";
        }
        $query = "SELECT candidate_source_master.id,candidate_source_master.name
        , COUNT(CASE WHEN month = 1 AND year=$year THEN 1 END) January
        , COUNT(CASE WHEN month = 2 AND year=$year THEN 1 END) February
        , COUNT(CASE WHEN month = 3 AND year=$year THEN 1 END) March
        , COUNT(CASE WHEN month = 4 AND year=$year THEN 1 END) April
        , COUNT(CASE WHEN month = 5 AND year=$year THEN 1 END) May
        , COUNT(CASE WHEN month = 6 AND year=$year THEN 1 END) June
        , COUNT(CASE WHEN month = 7 AND year=$year THEN 1 END) July
        , COUNT(CASE WHEN month = 8 AND year=$year THEN 1 END) August
        , COUNT(CASE WHEN month = 9 AND year=$year THEN 1 END) September
        , COUNT(CASE WHEN month = 10 AND year=$year THEN 1 END) October
        , COUNT(CASE WHEN month = 11 AND year=$year THEN 1 END) November
        , COUNT(CASE WHEN month = 12 AND year=$year THEN 1 END) December FROM(SELECT candidates.*, EXTRACT(YEAR FROM candidates.created_at) year, EXTRACT(MONTH FROM candidates.created_at) month FROM candidates $q) candidates LEFT JOIN candidate_source_master ON candidate_source_master.id=candidates.candidate_source_master_id GROUP BY candidate_source_master.id ORDER BY candidate_source_master.id;";
        $query = $this->db->query($query);
        return $query->result_array();
    }

    public function get_candidates_clients($client_id = false, $year = false)
    {
        $q = "";
        if ($client_id) {
            $q = "WHERE requirements.company_id=$client_id";
        }
        $query = "SELECT IF(company.id IS NULL or company.id='',0,company.id) as id,IF(company.name IS NULL or company.name='','Unassign',company.name) as company
        , COUNT(CASE WHEN month = 1 AND year=$year THEN 1 END) January
        , COUNT(CASE WHEN month = 2 AND year=$year THEN 1 END) February
        , COUNT(CASE WHEN month = 3 AND year=$year THEN 1 END) March
        , COUNT(CASE WHEN month = 4 AND year=$year THEN 1 END) April
        , COUNT(CASE WHEN month = 5 AND year=$year THEN 1 END) May
        , COUNT(CASE WHEN month = 6 AND year=$year THEN 1 END) June
        , COUNT(CASE WHEN month = 7 AND year=$year THEN 1 END) July
        , COUNT(CASE WHEN month = 8 AND year=$year THEN 1 END) August
        , COUNT(CASE WHEN month = 9 AND year=$year THEN 1 END) September
        , COUNT(CASE WHEN month = 10 AND year=$year THEN 1 END) October
        , COUNT(CASE WHEN month = 11 AND year=$year THEN 1 END) November
        , COUNT(CASE WHEN month = 12 AND year=$year THEN 1 END) December FROM(SELECT candidates.*, EXTRACT(YEAR FROM candidates.created_at) year, EXTRACT(MONTH FROM candidates.created_at) month FROM candidates $q) candidates LEFT JOIN requirements ON requirements.id=candidates.current_assign_reqirement_id LEFT JOIN company ON company.id=requirements.company_id GROUP BY company.id ORDER BY company.id;";
        $query = $this->db->query($query);
        return $query->result_array();
    }

    public function get_clients_req_by_job($job_id = false, $year = false)
    {
        $q = "";
        if ($job_id) {
            $q = "WHERE requirements.id=$job_id";
        }
        $query = "SELECT company.id,company.name
        , COUNT(CASE WHEN month = 1 AND year=$year THEN 1 END) January
        , COUNT(CASE WHEN month = 2 AND year=$year THEN 1 END) February
        , COUNT(CASE WHEN month = 3 AND year=$year THEN 1 END) March
        , COUNT(CASE WHEN month = 4 AND year=$year THEN 1 END) April
        , COUNT(CASE WHEN month = 5 AND year=$year THEN 1 END) May
        , COUNT(CASE WHEN month = 6 AND year=$year THEN 1 END) June
        , COUNT(CASE WHEN month = 7 AND year=$year THEN 1 END) July
        , COUNT(CASE WHEN month = 8 AND year=$year THEN 1 END) August
        , COUNT(CASE WHEN month = 9 AND year=$year THEN 1 END) September
        , COUNT(CASE WHEN month = 10 AND year=$year THEN 1 END) October
        , COUNT(CASE WHEN month = 11 AND year=$year THEN 1 END) November
        , COUNT(CASE WHEN month = 12 AND year=$year THEN 1 END) December FROM(SELECT candidates.*, EXTRACT(YEAR FROM candidates.created_at) year, EXTRACT(MONTH FROM candidates.created_at) month FROM candidates $q) candidates LEFT JOIN requirements ON requirements.id=candidates.current_assign_reqirement_id LEFT JOIN company ON company.id=requirements.company_id GROUP BY requirements.id ORDER BY company.id;";
        $query = $this->db->query($query);
        return $query->result_array();
    }

    public function get_roles($role_id = false, $year = false)
    {
        $q = "";
        if ($role_id) {
            $q = "WHERE requirements.requirement_role_id=$role_id";
        }
        $query = "SELECT requirement_role_master.id,requirement_role_master.name
        , COUNT(CASE WHEN month = 1 AND year=$year THEN 1 END) January
        , COUNT(CASE WHEN month = 2 AND year=$year THEN 1 END) February
        , COUNT(CASE WHEN month = 3 AND year=$year THEN 1 END) March
        , COUNT(CASE WHEN month = 4 AND year=$year THEN 1 END) April
        , COUNT(CASE WHEN month = 5 AND year=$year THEN 1 END) May
        , COUNT(CASE WHEN month = 6 AND year=$year THEN 1 END) June
        , COUNT(CASE WHEN month = 7 AND year=$year THEN 1 END) July
        , COUNT(CASE WHEN month = 8 AND year=$year THEN 1 END) August
        , COUNT(CASE WHEN month = 9 AND year=$year THEN 1 END) September
        , COUNT(CASE WHEN month = 10 AND year=$year THEN 1 END) October
        , COUNT(CASE WHEN month = 11 AND year=$year THEN 1 END) November
        , COUNT(CASE WHEN month = 12 AND year=$year THEN 1 END) December FROM(SELECT requirements.*, EXTRACT(YEAR FROM requirements.created_at) year, EXTRACT(MONTH FROM requirements.created_at) month FROM requirements $q) requirements LEFT JOIN requirement_role_master ON requirement_role_master.id=requirements.requirement_role_id GROUP BY requirements.requirement_role_id ORDER BY requirement_role_master.id;";
        $query = $this->db->query($query);
        return $query->result_array();
    }

    public function job_performance($start_date, $end_date, $job_id, $client_id, $job_status, $start, $length)
    {
        if ($start_date != '' && $end_date != '') {
            $this->db->where('DATE(r.created_at) >=',date('Y-m-d',strtotime($start_date)));
            $this->db->where('DATE(r.created_at) <=',date('Y-m-d',strtotime($end_date)));
        }
        if ($job_id) {
            $job = [];
            foreach ($job_id as $value) {
                $job[] = $value['value'];
            }
            if($job){
                $this->db->where_in('r.id', $job);
            }
        }
        if ($client_id) {
            $client = [];
            foreach ($client_id as $value) {
                $client[] = $value['value'];
            }
            if($client){
                $this->db->where_in('r.company_id', $client);
            }
        }
        if ($job_status && count($job_status)) {
            $status = [];
            foreach ($job_status as $value) {
                $status[] = $value['value'];
            }
            if($status){
                $this->db->where_in('r.requirement_status', $status);
            }
        }
        if ($length != -1)
            $this->db->limit($length, ($start * $length));

        $result = $this->db->select("r.id,rrm.name as job_title,c.name as client,CONCAT(e.first_name,' ',e.last_name) as recruiter_charge,DATEDIFF(NOW(),r.created_at) as open_duration,rsm.name as job_status,DATE(r.created_at) as first_created")
            ->order_by('r.created_at')
            ->join('requirement_role_master rrm', 'rrm.id=r.requirement_role_id')
            ->join('company c', 'c.id=r.company_id')
            ->join('requirement_status_master rsm', 'rsm.id=r.requirement_status')
            ->join('employee e', 'e.id=r.created_by')
            ->get('requirements r')->result_array();
        if ($result) {
            foreach ($result as $key => $value) {
                if($length == 100000){
                    $employees = $this->db->select("GROUP_CONCAT(distinct e.first_name,' ',e.last_name) as employee")
                    ->where(['re.requirement_id' => $value['id'], 're.status' => 1, 'e.status' => 1])
                    ->join('employee e', 'e.id=re.employee_id')
                    ->get('requirement_employees re')->row_array();
                    $result[$key]['employees'] = $employees['employee'];
                }else{
                    $result[$key]['employees'] = $this->db->select("e.id,CONCAT(e.first_name,' ',e.last_name) as employee")
                    ->where(['re.requirement_id' => $value['id'], 're.status' => 1, 'e.status' => 1])
                    ->join('employee e', 'e.id=re.employee_id')
                    ->get('requirement_employees re')->result_array();
                }
                $this->db->select("COUNT(rcs.id) as candidates_progress,SUM(IF(rsl.name='Joining',1,0)) as joining,SUM(IF(rsl.name='Reject',1,0)) as reject,SUM(IF(rsl.name='Offer Accepted',1,0)) as offered,SUM(IF(rsl.name='Screeening',1,0)) as added_candidates");
                    if ($start_date != '' && $end_date != '') {
                        $this->db->where('DATE(rcs.created_at) >=',date('Y-m-d',strtotime($start_date)));
                        $this->db->where('DATE(rcs.created_at) <=',date('Y-m-d',strtotime($end_date)));
                    }
                $result[$key]['candidate_data'] = $this->db->where(['rsl.status' => 1, 'rsl.requirement_id' => $value['id']])->where('rcs.reject_stage_id IS NULL')
                    ->join('requirement_candidate_screening rcs', 'rcs.requirement_screening_level_id=rsl.id AND rcs.status=1')
                    ->get('requirement_screening_levels rsl')->row_array();
            }
        }
        return $result;
    }

    public function job_performance_count($start_date, $end_date, $job_id, $client_id, $job_status)
    {
        if ($start_date != '' && $end_date != '') {
            $this->db->where('DATE(r.created_at) >=',date('Y-m-d',strtotime($start_date)));
            $this->db->where('DATE(r.created_at) <=',date('Y-m-d',strtotime($end_date)));
        }
        if ($job_id) {
            $job = [];
            foreach ($job_id as $value) {
                $job[] = $value['value'];
            }
            if($job){
                $this->db->where_in('r.id', $job);
            }
        }
        if ($client_id) {
            $client = [];
            foreach ($client_id as $value) {
                $client[] = $value['value'];
            }
            if($client){
                $this->db->where_in('r.company_id', $client);
            }
        }
        if ($job_status && count($job_status)) {
            $status = [];
            foreach ($job_status as $value) {
                $status[] = $value['value'];
            }
            if($status){
                $this->db->where_in('r.requirement_status', $status);
            }
        }

        return $this->db->select("r.id")
            ->join('requirement_role_master rrm', 'rrm.id=r.requirement_role_id')
            ->join('company c', 'c.id=r.company_id')
            ->join('requirement_status_master rsm', 'rsm.id=r.requirement_status')
            ->join('employee e', 'e.id=r.created_by')
            ->from('requirements r')->count_all_results();
    }

    public function job_performance_quick_view(){
        $data = $this->db->select('rsm.id,rsm.name as status')->where(['rsm.status' => 1])->get('requirement_status_master rsm')->result_array();
        $result = [];
        foreach ($data as $key => $value) {
            $total = $this->db->where('r.requirement_status',$value['id'])->from('requirements r')->count_all_results();
            $result[$key] = [
                'status' => $value['status'],
                'total' => $total
            ];
        }
        return $result;
    }

    public function client_tracking($start_date, $end_date, $client_id, $client_status, $start, $length){
        if ($length != -1)
            $this->db->limit($length, ($start * $length));

        if ($start_date != '' && $end_date != '') {
            $this->db->where('DATE(c.created_at) >=',date('Y-m-d',strtotime($start_date)));
            $this->db->where('DATE(c.created_at) <=',date('Y-m-d',strtotime($end_date)));
        }
        if ($client_id && count($client_id)) {
            $client = [];
            foreach ($client_id as $value) {
                $client[] = $value['value'];
            }
            if($client){
                $this->db->where_in('c.id', $client);
            }
        }
        if ($client_status && count($client_status)) {
            $status = [];
            foreach ($client_status as $value) {
                $status[] = $value['value'];
            }
            if($status){
                $this->db->where_in('c.client_status', $status);
            }
        }
        $result = $this->db->select("c.id,c.name as company,l.name as location,csm.name as source,cs.name as status,CONCAT(e.first_name,' ',e.last_name) as employee,CONCAT(ee.first_name,' ',ee.last_name) as created_by,(SELECT COUNT(id) as total FROM requirements WHERE company_id=c.id) as no_of_position,c.created_at,COUNT(rcs.id) as candidate_assign")
            ->group_by('c.id')
            ->join('location_master l','l.id=c.location')
            ->join('client_source_master csm','csm.id=c.client_source_master_id')
            ->join('client_status_master cs','cs.id=c.client_status')
            ->join('employee e','e.id=c.employe_assign_to')
            ->join('employee ee','ee.id=c.created_by','left outer')
            ->join('requirements r','r.company_id=c.id','inner')
            ->join('requirement_screening_levels rsl','rsl.requirement_id=r.id','inner')
            ->join('requirement_candidate_screening rcs','rcs.requirement_screening_level_id=rsl.id','inner')
            ->get('company c')->result_array();

        return $result;
    }

    public function client_tracking_count($start_date, $end_date, $client_id, $client_status){
        if ($start_date != '' && $end_date != '') {
            $this->db->where('DATE(c.created_at) >=',date('Y-m-d',strtotime($start_date)));
            $this->db->where('DATE(c.created_at) <=',date('Y-m-d',strtotime($end_date)));
        }
        if ($client_id && count($client_id)) {
            $client = [];
            foreach ($client_id as $value) {
                $client[] = $value['value'];
            }
            if($client){
                $this->db->where_in('c.id', $client);
            }
        }
        if ($client_status && count($client_status)) {
            $status = [];
            foreach ($client_status as $value) {
                $status[] = $value['value'];
            }
            if($status){
                $this->db->where_in('c.client_status', $status);
            }
        }

        $result = $this->db->select("c.id")
            ->group_by('c.id')
            ->join('location_master l','l.id=c.location')
            ->join('client_source_master csm','csm.id=c.client_source_master_id')
            ->join('client_status_master cs','cs.id=c.client_status')
            ->join('employee e','e.id=c.employe_assign_to')
            ->join('employee ee','ee.id=c.created_by','left outer')
            ->from('company c')->count_all_results();

        return $result;
    }

    public function client_tracking_quick_view(){
        $data = $this->db->select('csm.id,csm.name as status')->where(['csm.status' => 1])->get('client_status_master csm')->result_array();
        $result = [];
        $total_count = 0;
        foreach ($data as $key => $value) {
            $total = $this->db->where('c.client_status',$value['id'])->from('company c')->count_all_results();
            $result[$key] = [
                'status' => $value['status'],
                'total' => $total
            ];
            $total_count += $total;
        }
        $result[] = [
            'status' => 'Total',
            'total' => $total_count
        ];
        return $result;
    }


    public function team_tracker($start_date, $end_date, $job_id, $start, $length)
    {
        if ($start_date != '' && $end_date != '') {
            $this->db->where('DATE(rcs.created_at) >=',date('Y-m-d',strtotime($start_date)));
            $this->db->where('DATE(rcs.created_at) <=',date('Y-m-d',strtotime($end_date)));
        }
        if ($job_id) {
            $job = [];
            foreach ($job_id as $value) {
                $job[] = $value['value'];
            }
            if($job){
                $this->db->where_in('rsl.requirement_id', $job);
            }
        }
        
        if ($length != -1)
            $this->db->limit($length, ($start * $length));

        $result = $this->db->select("e.id,e.emp_id,CONCAT(e.first_name,' ',e.last_name) as employee_name,COUNT(rcs.candidate_id) as added_candidates,SUM(IF(rsl.name='Joining',1,0)) as joining,SUM(IF(rsl.name='Reject',1,0)) as reject,SUM(IF(rsl.name='Drop',1,0)) as dropped,SUM(IF(rsl.name='Offer Accepted',1,0)) as offered,(COUNT(rcs.candidate_id) - (SUM(IF(rsl.name='Joining',1,0)) + SUM(IF(rsl.name='Reject',1,0)) + SUM(IF(rsl.name='Drop',1,0)) + SUM(IF(rsl.name='Offer Accepted',1,0)))) as candidates_progress")
            ->group_by('e.id')
            ->order_by('e.id','desc')
            ->join('requirement_candidate_screening rcs', 'rcs.created_by=e.id','left outer')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id AND rcs.status=1','left outer')
            ->get('employee e')->result_array();
        return $result;
    }

    public function team_tracker_count($start_date, $end_date, $job_id)
    {
        if ($start_date != '' && $end_date != '') {
            $this->db->where('DATE(rcs.created_at) >=',date('Y-m-d',strtotime($start_date)));
            $this->db->where('DATE(rcs.created_at) <=',date('Y-m-d',strtotime($end_date)));
        }
        if ($job_id) {
            $job = [];
            foreach ($job_id as $value) {
                $job[] = $value['value'];
            }
            if($job){
                $this->db->where_in('rsl.requirement_id', $job);
            }
        }
        
        return $this->db->select("e.id")
            ->group_by('e.id')
            ->join('requirement_candidate_screening rcs', 'rcs.created_by=e.id','left outer')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id AND rcs.status=1','left outer')
            ->from('employee e')->count_all_results();
    }

    public function team_tracker_v2($start_date, $end_date, $job_id, $start, $length)
    {
        if ($length != -1)
            $this->db->limit($length, ($start * $length));

        $result = $this->db->select("e.id,e.emp_id,CONCAT(e.first_name,' ',e.last_name) as employee_name")
            ->order_by('e.id', 'desc')
            ->get('employee e')->result_array();

            if ($start_date != '' && $end_date != '') {
                $this->db->where('DATE(rcs.created_at) >=', date('Y-m-d', strtotime($start_date)));
                $this->db->where('DATE(rcs.created_at) <=', date('Y-m-d', strtotime($end_date)));
            }
            if ($job_id) {
                $job = [];
                foreach ($job_id as $value) {
                    $job[] = $value['value'];
                }
                if ($job) {
                    $this->db->where_in('rsl.requirement_id', $job);
                }
            }
        $candidates_progress = $this->db->where(['rsl.status' => 1])
            ->select('rcs.created_by')
            ->group_by('rcs.id')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->get('requirement_candidate_screening rcs')->result_array();
            if ($start_date != '' && $end_date != '') {
                $this->db->where('DATE(rcs.created_at) >=', date('Y-m-d', strtotime($start_date)));
                $this->db->where('DATE(rcs.created_at) <=', date('Y-m-d', strtotime($end_date)));
            }
            if ($job_id) {
                $job = [];
                foreach ($job_id as $value) {
                    $job[] = $value['value'];
                }
                if ($job) {
                    $this->db->where_in('rsl.requirement_id', $job);
                }
            }
        $join = $this->db->where(['rsl.status' => 1, 'rsl.name' => 'Joining'])
            ->select('rcs.created_by')
            ->group_by('rcs.id')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->get('requirement_candidate_screening rcs')->result_array();
            if ($start_date != '' && $end_date != '') {
                $this->db->where('DATE(rcs.created_at) >=', date('Y-m-d', strtotime($start_date)));
                $this->db->where('DATE(rcs.created_at) <=', date('Y-m-d', strtotime($end_date)));
            }
            if ($job_id) {
                $job = [];
                foreach ($job_id as $value) {
                    $job[] = $value['value'];
                }
                if ($job) {
                    $this->db->where_in('rsl.requirement_id', $job);
                }
            }
        $reject = $this->db->where(['rsl.status' => 1])->where("rsl.name='Reject' OR rsl.name='Drop'")
            ->select('rcs.created_by')
            ->group_by('rcs.id')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->get('requirement_candidate_screening rcs')->result_array();
            if ($start_date != '' && $end_date != '') {
                $this->db->where('DATE(rcs.created_at) >=', date('Y-m-d', strtotime($start_date)));
                $this->db->where('DATE(rcs.created_at) <=', date('Y-m-d', strtotime($end_date)));
            }
            if ($job_id) {
                $job = [];
                foreach ($job_id as $value) {
                    $job[] = $value['value'];
                }
                if ($job) {
                    $this->db->where_in('rsl.requirement_id', $job);
                }
            }
        $offer = $this->db->where(['rsl.status' => 1, 'rsl.name' => 'Offer Accepted'])
            ->select('rcs.created_by')
            ->group_by('rcs.id')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->get('requirement_candidate_screening rcs')->result_array();
            if ($start_date != '' && $end_date != '') {
                $this->db->where('DATE(rcs.created_at) >=', date('Y-m-d', strtotime($start_date)));
                $this->db->where('DATE(rcs.created_at) <=', date('Y-m-d', strtotime($end_date)));
            }
            if ($job_id) {
                $job = [];
                foreach ($job_id as $value) {
                    $job[] = $value['value'];
                }
                if ($job) {
                    $this->db->where_in('rsl.requirement_id', $job);
                }
            }
        $added_candidates = $this->db->where(['rsl.status' => 1, 'rsl.name' => 'Screeening'])
            ->select('rcs.created_by')
            ->group_by('rcs.id')
            ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
            ->get('requirement_candidate_screening rcs')->result_array();
        if ($result) {
            foreach ($result as $key => $value) {
                $progress_total = 0;
                foreach ($candidates_progress as $val) {
                    if($val['created_by'] == $value['id']){
                        $progress_total += 1;
                    }
                }
                $result[$key]['candidates_progress'] = $progress_total;
                $join_total = 0;
                foreach ($join as $val) {
                    $result[$key]['join'] = 0;
                    if($val['created_by'] == $value['id']){
                        $join_total += 1;
                    }
                }
                $result[$key]['join'] = $join_total;
                $reject_total = 0;
                foreach ($reject as $val) {
                    if($val['created_by'] == $value['id']){
                        $reject_total += 1;
                    }
                }
                $result[$key]['reject'] = $reject_total;
                $offer_total = 0;
                foreach ($offer as $val) {
                    if($val['created_by'] == $value['id']){
                        $offer_total += 1;
                    }
                }
                $result[$key]['offer'] = $offer_total;
                $added_candidates_total = 0;
                foreach ($added_candidates as $val) {
                    if($val['created_by'] == $value['id']){
                        $added_candidates_total += 1;
                    }
                }
                $result[$key]['added_candidates'] = $added_candidates_total;
            }
        }
        return $result;
    }
}
