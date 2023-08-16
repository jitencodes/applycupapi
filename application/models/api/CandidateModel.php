<?php
if (!defined('BASEPATH')) exit('No direct script allowed');
date_default_timezone_set('Asia/Kolkata');
class CandidateModel extends CI_Model
{
    var $table = 'candidates';
    var $candidate_locations = 'candidate_locations';
    public function __construct()
    {
        parent::__construct();
    }

    var $column_search = ["c.id", "c.name", "c.candidate_status", "c.email", "c.mobile", "cm.name"];
    var $selected_col = "c.id,c.name,c.candidate_status,c.email,c.mobile,c.total_experience,c.relevent_experience,c.current_company,c.current_ctc,c.expected_ctc,cm.name as assign_job_company,rr.name as opening,np.name as notice_period,lm.name as location,cs.name as candidate_source,csm.name as candidate_status,c.created_at,CONCAT_WS(' ' , e.first_name, e.last_name) as employee,c.resume,c.current_assign_reqirement_id";
    public function get_list($id, $search, $start, $length, $filter, $user_id, $user_role)
    {
        if ($id === NULL) {
            if ($length != -1)
                $this->db->limit($length, $start);
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

                    if (count($this->column_search) - 1 == $i) //last loop
                        $this->db->group_end(); //close bracket
                    $i++;
                }
            }
            if ($filter) {
                if ($filter['stage']) {
                    $this->db->where_in('rsl.name', $filter['stage']);
                }
                if ($filter['source']) {
                    $this->db->where('cs.id', $filter['source']['value']);
                }
                if ($filter['unassign']) {
                    $where = "c.current_assign_reqirement_id='null' OR c.current_assign_reqirement_id=0";
                    $this->db->where($where);
                }
                if ($filter['opening']) {
                    $this->db->where('r.id', $filter['opening']);
                }
                if ($filter['add_by_me'] == true) {
                    $this->db->where('c.created_by', $user_id);
                }
            }
            $check_owner = "IF(c.created_by = $user_id,'TRUE','FALSE') as candidate_owner,IF(r.created_by = $user_id,'TRUE','FALSE') as job_owner";
            $result = $this->db->select($this->selected_col)
                ->select($check_owner)
                ->order_by('c.id', 'desc')
                ->group_by('c.id')
                ->join('requirements r', 'r.id=c.current_assign_reqirement_id', 'left outer')
                ->join('requirement_role_master rr', 'rr.id=r.requirement_role_id', 'left outer')
                ->join('requirement_candidate_screening rcs', 'rcs.candidate_id=c.id AND rcs.status=1', 'left outer')
                ->join('requirement_screening_levels rsl', 'rsl.requirement_id=r.id AND rsl.id=rcs.requirement_screening_level_id', 'left outer')
                ->join('company cm', 'cm.id=r.company_id', 'left')
                ->join('notice_period_master np', 'np.id=c.notice_period_master_id', 'left')
                ->join('location_master lm', 'lm.id=c.current_location_master_id', 'left')
                ->join('candidate_source_master cs', 'cs.id=c.candidate_source_master_id', 'left')
                ->join('candidate_status_master csm', 'csm.id=c.candidate_status', 'left outer')
                ->join('employee e', 'e.id=c.created_by', 'left outer')
                ->get('candidates c')->result_array();
            $data = [];
            foreach ($result as $key => $value) {
                $stage = $this->db->select("rsl.name as stage,rsl.id as stage_id,rcs.is_favourite")
                ->where(['rcs.candidate_id' => $value['id'],'rsl.requirement_id' => $value['current_assign_reqirement_id']])
                ->join('requirement_screening_levels rsl','rsl.id=rcs.requirement_screening_level_id','left')
                ->get('requirement_candidate_screening rcs')->row_array();
                if($stage){
                    $data[$key] = array_merge($value,$stage);
                }else{
                    $stage = ["stage" => null,'stage_id' => null,"is_favourite" => 0];
                    $data[] = array_merge($value,$stage);
                }
            }
            return $data;
        }

        // Find and return a single record for a particular user.

        $id = (int)$id;

        // Validate the id.
        if ($id <= 0) {
            return false;
        }
        $check_owner = "IF(c.created_by = $user_id,'TRUE','FALSE') as candidate_owner,IF(r.created_by = $user_id,'TRUE','FALSE') as job_owner";
        return $this->db->where(['id' => $id])
            ->select($this->selected_col)
            ->select($check_owner)
            ->group_by('c.id')
            ->join('requirements r', 'r.id=c.current_assign_reqirement_id', 'left outer')
            ->join('requirement_role_master rr', 'rr.id=r.requirement_role_id', 'left outer')
            ->join('requirement_candidate_screening rcs', 'rcs.candidate_id=c.id AND rcs.status=1', 'left outer')
            ->join('requirement_screening_levels rsl', 'rsl.requirement_id=r.id AND rsl.id=rcs.requirement_screening_level_id', 'left outer')
            ->join('company cm', 'cm.id=r.company_id', 'left')
            ->join('notice_period_master np', 'np.id=c.notice_period_master_id', 'left')
            ->join('location_master lm', 'lm.id=c.current_location_master_id', 'left')
            ->join('candidate_source_master cs', 'cs.id=c.candidate_source_master_id', 'left')
            ->join('candidate_status_master csm', 'csm.id=c.candidate_status', 'left outer')
            ->join('employee e', 'e.id=c.created_by', 'left outer')
            ->get('candidates c')->row_array();
    }


    public function count_all($search, $filter, $user_id, $user_role)
    {

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

                if (count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
                $i++;
            }
        }
        if ($filter) {
            if ($filter['stage']) {
                $this->db->where_in('rsl.name', $filter['stage']);
            }
            if ($filter['source']) {
                $this->db->where('cs.id', $filter['source']['value']);
            }
            if ($filter['unassign']) {
                $where = "c.current_assign_reqirement_id='null' OR c.current_assign_reqirement_id=0";
                $this->db->where($where);
            }
            if ($filter['opening']) {
                $this->db->where('r.id', $filter['opening']);
            }
            if ($filter['add_by_me'] == true) {
                $this->db->where('c.created_by', $user_id);
            }
        }
        return $this->db->select('c.id')->join('requirements r', 'r.id=c.current_assign_reqirement_id', 'left outer')
            ->group_by('c.id')
            ->join('requirement_role_master rr', 'rr.id=r.requirement_role_id', 'left outer')
            ->join('requirement_candidate_screening rcs', 'rcs.candidate_id=c.id AND rcs.status=1', 'left outer')
            ->join('requirement_screening_levels rsl', 'rsl.requirement_id=r.id AND rsl.id=rcs.requirement_screening_level_id', 'left outer')
            ->join('candidate_source_master cs', 'cs.id=c.candidate_source_master_id', 'left')
            ->join('company cm', 'cm.id=r.company_id', 'left')
            ->from('candidates c')->count_all_results();
    }

    public function get_user_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table)->row_array();
    }

    public function get_user_by_email($email,$id = false)
    {
        if($id){
            $this->db->where('id !=',$id);
        }
        $this->db->where('email', $email);
        return $this->db->get($this->table)->row_array();
    }

    public function add_candidate($candidate_name, $current_assign_reqirement_id, $gender, $email, $mobile, $exprience, $relevant_tech_exprience, $current_organisation, $current_ctc, $expected_ctc, $notice_period, $current_city, $preferred_city, $candidate_source, $candidate_status, $resume, $remark, $created_by)
    {
        $role_screening = $this->get_role_screening($current_assign_reqirement_id);
        $add = [
            'name' => $candidate_name,
            'current_assign_reqirement_id' => $current_assign_reqirement_id,
            'gender' => $gender,
            'email' => $email,
            'mobile' => $mobile,
            'current_location_master_id' => $current_city,
            'candidate_source_master_id' => $candidate_source,
            'candidate_status' => $candidate_status,
            'created_by' => $created_by,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if ($role_screening) {
            $add['requirement_role_id'] = $role_screening['requirement_role_id'];
        }
        if ($preferred_city == 0) {
            $add['is_all_locations'] = 0;
        }
        if ($exprience) {
            $add['total_experience'] = $exprience;
        }
        if ($relevant_tech_exprience) {
            $add['relevent_experience'] = $relevant_tech_exprience;
        }
        if ($current_organisation) {
            $add['current_company'] = $current_organisation;
        }
        if ($current_ctc) {
            $add['current_ctc'] = $current_ctc;
        }
        if ($expected_ctc) {
            $add['expected_ctc'] = $expected_ctc;
        }
        if ($notice_period) {
            $add['notice_period_master_id'] = $notice_period;
        }
        if ($resume) {
            $add['resume'] = $resume;
        }
        if($remark){
            $add['remark'] = $remark;
        }
        if ($this->db->insert($this->table, $add)) {
            $id = $this->db->insert_id();
            if ($role_screening) {
                $add_data = [
                    'candidate_id' => $id,
                    'requirement_screening_level_id' => $role_screening['requirement_screening_level_id'],
                    'ordering' => 1,
                    'status' => 1,
                    'created_by' => $created_by,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->insert('requirement_candidate_screening', $add_data);
                try {
                    $this->candidate_assign_mail($id, $current_assign_reqirement_id);
                } catch (\Throwable $th) {
                    log_message('error','Candidate email send failed');
                }
            }
            $this->candidate_locations($id, $preferred_city);
            return true;
        }
        return false;
    }

    public function update_candidate($id,$candidate_name, $email, $mobile, $exprience, $relevant_tech_exprience, $current_organization, $current_ctc, $expected_ctc, $notice_period, $current_city, $preferred_city,$remark, $updated_by){
        $add = [
            'name' => $candidate_name,
            'email' => $email,
            'mobile' => $mobile,
            'current_location_master_id' => $current_city,
            'updated_by' => $updated_by,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if (!$preferred_city) {
            $add['is_all_locations'] = 1;
        }else{
            $add['is_all_locations'] = 0;
        }
        if ($exprience) {
            $add['total_experience'] = $exprience;
        }
        if ($relevant_tech_exprience) {
            $add['relevent_experience'] = $relevant_tech_exprience;
        }
        if ($current_organization) {
            $add['current_company'] = $current_organization;
        }
        if ($current_ctc) {
            $add['current_ctc'] = $current_ctc;
        }
        if ($expected_ctc) {
            $add['expected_ctc'] = $expected_ctc;
        }
        if ($notice_period) {
            $add['notice_period_master_id'] = $notice_period;
        }
        if($remark){
            $add['remark'] = $remark;
        }
        $preferred_cities = [];
        foreach ($preferred_city as $value) {
            $preferred_cities[] = $value['value'];
        }
        if ($this->db->where('id',$id)->update($this->table, $add)) {
            $this->candidate_locations($id, $preferred_cities);
            return true;
        }
        return false;
    }

    public function get_role_screening($reqirement_id)
    {
        return $this->db->select('r.*,rs.id as requirement_screening_level_id')->where('r.id', $reqirement_id)->group_by('r.id')->join('requirement_screening_levels rs', 'rs.requirement_id=r.id')->get('requirements r')->row_array();
    }

    public function candidate_locations($id, $locations)
    {
        $this->db->where('candidates_id', $id)->update($this->candidate_locations, ['status' => 0]);
        if ($locations) {
            foreach ($locations as $location) {
                $is_exits = $this->db->where(['candidates_id' => $id, 'location_master_id' => $location])
                    ->get($this->candidate_locations)->row_array();
                if ($is_exits) {
                    $this->db->where('id', $is_exits['id'])->update($this->candidate_locations, ['status' => 1]);
                } else {
                    $add = [
                        'candidates_id' => $id,
                        'location_master_id' => $location,
                        'status' => 1
                    ];
                    $this->db->insert($this->candidate_locations, $add);
                }
            }
        }
        return true;
    }


    public function candidate_resume($candidate_id, $file, $updated_by)
    {
        $update = ['resume' => $file, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => $updated_by];
        return $this->db->where('id', $candidate_id)->update($this->table, $update);
    }

    public function candidate_manage_opening($candidate_id, $job_id, $stages, $updated_by, $prv_job = false)
    {
        if ($prv_job) {
            $candidate_data = $this->db->select('rcs.id')
                ->where(['rsl.requirement_id' => $prv_job, 'rcs.candidate_id' => $candidate_id])
                ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
                ->get('requirement_candidate_screening rcs')->row_array();
        } else {
            $candidate_data = $this->db->select('rcs.id')
                ->where(['rsl.requirement_id' => $job_id, 'rcs.candidate_id' => $candidate_id])
                ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
                ->get('requirement_candidate_screening rcs')->row_array();
        }
        if ($candidate_data) {
            $candidate_update = ['current_assign_reqirement_id' => $job_id];
            $this->db->where('id', $candidate_id)->update('candidates', $candidate_update);
            $update = ['requirement_screening_level_id' => $stages, 'status' => 1, 'created_by' => $updated_by];
            if ($this->db->where(['id' => $candidate_data['id']])->update('requirement_candidate_screening', $update)) {
                return $this->candidate_req_update($candidate_id, $job_id);
            }
        } else {
            $candidate_update = ['current_assign_reqirement_id' => $job_id];
            $this->db->where('id', $candidate_id)->update('candidates', $candidate_update);
            $add = ['candidate_id' => $candidate_id, 'requirement_screening_level_id' => $stages, 'ordering' => 1, 'status' => 1, 'created_by' => $updated_by];
            if ($this->db->insert('requirement_candidate_screening', $add)) {
                return $this->candidate_req_update($candidate_id, $job_id);
            }
        }
    }

    public function candidate_stage_move($id, $stages, $ordering, $updated_by)
    {
        $update = ['requirement_screening_level_id' => $stages,'is_hold' => 0, 'ordering' => $ordering, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => $updated_by];
        return $this->db->where('id', $id)->update('requirement_candidate_screening', $update);
    }

    public function job_stage_move($from, $to, $stage_id, $job_id, $updated_by)
    {
        $previous = $this->db->where(['ordering' => $to, 'requirement_id' => $job_id])->get('requirement_screening_levels')->row_array();
        if ($previous) {
            $update = ['ordering' => $from, 'updated_by' => $updated_by, 'updated_at' => date('Y-m-d H:i:s')];
            $this->db->where('id', $previous['id'])->update('requirement_screening_levels', $update);
        }
        $update = ['ordering' => $to, 'updated_by' => $updated_by, 'updated_at' => date('Y-m-d H:i:s')];
        return $this->db->where(['id' => $stage_id])->update('requirement_screening_levels', $update);
    }

    public function candidate_reject($requirement_candidate_screening_id, $feedback, $reject_stage, $remark, $reject_action_type, $job_id, $updated_by)
    {
        $candidate_detail = $this->db->where('id',$requirement_candidate_screening_id)->get('requirement_candidate_screening')->row_array();
        if($reject_action_type == 'Drop'){
            $requirement_screening_levels = $this->db->where(['name' => 'Drop','requirement_id' => $job_id])->get('requirement_screening_levels')->row_array();
        }else{
            $requirement_screening_levels = $this->db->where(['name' => 'Reject','requirement_id' => $job_id])->get('requirement_screening_levels')->row_array();
        }
        if($candidate_detail && $requirement_screening_levels){
            $update = [
                'requirement_screening_level_id' => $requirement_screening_levels['id'],
                'reject_stage_id' => $reject_stage,
                'reject_action_type' => $reject_action_type,
                'updated_at' => date('Y-m-d H:i:s'), 
                'updated_by' => $updated_by
            ];
            if ($remark) {
                $update['reject_feedback'] = $feedback;
            }
            if($this->db->where('id', $requirement_candidate_screening_id)->update('requirement_candidate_screening', $update)){
                return $this->db->where('id',$candidate_detail['candidate_id'])->update('candidates',['current_assign_reqirement_id' => 0]);
            }
        }
    }

    public function candidate_hold($id, $requirement_screening_level_id, $updated_by){
        $candidate_detail = $this->db->where(['id' => $id, 'requirement_screening_level_id' => $requirement_screening_level_id])->get('requirement_candidate_screening')->row_array();
        if($candidate_detail){
            $update = [
                'is_hold' => 1,
                'updated_at' => date('Y-m-d H:i:s'), 
                'updated_by' => $updated_by
            ];
            return $this->db->where('id', $id)->update('requirement_candidate_screening', $update);
        }
    }

    public function candidate_assign_openings($job_id, $filters, $added_by_me)
    {
        $res = $this->db->where('r.id', $job_id)
            ->select('rr.name as job_name,r.id,c.name as company')
            ->join('requirement_role_master rr', 'rr.id=r.requirement_role_id')
            ->join('company c', 'c.id=r.company_id')
            ->get('requirements r')->row_array();

        $stages = $this->db->select('id,name')->where(['requirement_id' => $job_id, 'status' => 1])->order_by('ordering', 'asc')->get('requirement_screening_levels')->result_array();
        $res['stages'] = $stages;
        foreach ($stages as $key => $stage) {
            if ($filters) {
                if ($filters == 'AddedByMe') {
                    $this->db->where('rcs.created_by', $added_by_me);
                }
                if($filters != 'AllCandidates' && $filters != 'AddedByMe'){
                    $this->db->where('rsl.name', $filters);
                    $this->db->join('requirement_screening_levels rsl','rsl.id=rcs.requirement_screening_level_id');
                }
            }
            $res['stages'][$key]['candidates'] = $this->db->where(['rcs.requirement_screening_level_id' => $stage['id']])
                ->select('rcs.id,c.id as candidate_id,c.name as candidate_name,rcs.created_at as applied_date,rcs.requirement_screening_level_id')
                ->join('candidates c', 'c.id=rcs.candidate_id', 'left')
                ->get('requirement_candidate_screening rcs')->result_array();
        }
        return $res;
    }

    public function stage_move_by_id($id, $requirement_screening_level_id, $updated_by)
    {
        if (is_array($id)) {
            $update = [];
            foreach ($id as $value) {
                $update[] = [
                    'id' => $value,
                    'requirement_screening_level_id' => $requirement_screening_level_id,
                    'updated_at' => date('Y-m-d'),
                    'updated_by' => $updated_by
                ];
            }
            if ($update) {
                return $this->db->update_batch('requirement_candidate_screening', $update, 'id');
            }
        } else {
            $update = [
                'id' => $id,
                'requirement_screening_level_id' => $requirement_screening_level_id,
                'updated_at' => date('Y-m-d'),
                'updated_by' => $updated_by
            ];
            return $this->db->where('id', $id)->update('requirement_candidate_screening', $update);
        }
    }

    public function candidate_assign_mail($candidate_id, $job_id)
    {
        $this->load->model('MasterModel');
        $candidate_detail = $this->candidate_req_detail($candidate_id, $job_id);
        if ($candidate_detail) {
            $data = [
                'candidate_name' => $candidate_detail['candidate_name'],
                'job_role' => $candidate_detail['requirement_role'],
                'company' => $candidate_detail['company']
            ];
            $email_template = $this->load->view('email_template/assign_job', $data, true);
            $candidate_email = $candidate_detail['candidate_email'];
            $subject = "Thank You for your application!";
            return $this->MasterModel->send_email('job@applycup.com', $candidate_email, $subject, $email_template);
        }
    }

    public function candidate_req_detail($id, $job_id)
    {
        $this->db->select("rrm.name as requirement_role,cd.name as candidate_name,cd.email,c.name as company,cd.email as candidate_email")
            ->where('cd.id', $id)
            ->join('requirements r', 'r.id=' . $job_id)
            ->join('requirement_role_master rrm', 'rrm.id=r.requirement_role_id')
            ->join('company c', 'c.id=r.company_id');
        return $this->db->get('candidates cd')->row_array();
    }

    public function candidate_req_update($candidate_id, $job_id)
    {
        return $this->db->where('id', $candidate_id)->update('candidates', ['current_assign_reqirement_id' => $job_id]);
    }

    public function candidate_detail($candidate_id, $user_role)
    {
        $candidates = $this->db->where('c.id', $candidate_id)
            ->select("c.*,np.name as notice_period,lm.name as location,cs.name as candidate_source,CONCAT_WS(' ' , e.first_name, e.last_name) as employee,csm.name as candidate_status,csm.id as candidate_status_id")
            ->join('notice_period_master np', 'np.id=c.notice_period_master_id', 'left')
            ->join('location_master lm', 'lm.id=c.current_location_master_id', 'left')
            ->join('candidate_source_master cs', 'cs.id=c.candidate_source_master_id', 'left')
            ->join('candidate_status_master csm', 'csm.id=c.candidate_status', 'left outer')
            ->join('employee e', 'e.id=c.created_by', 'left')
            ->get('candidates c')->row_array();

        if ($candidates) {
            $candidates['prefer_locations'] = $this->db->where(['cl.candidates_id' => $candidate_id, 'cl.status' => 1])
                ->select('lm.id,lm.name')
                ->order_by('lm.ordering', 'asc')
                ->join('location_master lm', 'lm.id=cl.location_master_id')
                ->get('candidate_locations cl')->result_array();

            $candidates['current_apply_job'] = $this->db->where(['r.id' => $candidates['current_assign_reqirement_id'], 'rcs.candidate_id' => $candidates['id']])
                ->select("rsl.name as stage,rsl.id as stage_id,c.name as company,rrm.name as requirement,r.id as requirement_id")
                ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
                ->join('requirements r', 'r.id=rsl.requirement_id')
                ->join('requirement_role_master rrm', 'rrm.id=r.requirement_role_id')
                ->join('company c', 'c.id=r.company_id')
                ->get('requirement_candidate_screening rcs')->row_array();

            $candidates['feedbacks'] = $this->db->where(['rcf.candidates_id' => $candidate_id, 'rcf.status' => 1])
                ->select("rcf.*,rrm.name as requirement,rsl.name as stage,CONCAT_WS(' ' , e.first_name, e.last_name) as employee")
                ->order_by('rcf.created_at', 'desc')
                ->join('requirement_screening_levels rsl', 'rsl.id=rcf.requirement_screening_level_id')
                ->join('requirements r', 'r.id=rcf.requirements_id')
                ->join('requirement_role_master rrm', 'rrm.id=r.requirement_role_id')
                ->join('employee e', 'e.id=rsl.created_by', 'left')
                ->get('requirement_candidate_feedback rcf')->result_array();
            if ($user_role == 3) {
                $this->db->where('rcn.is_private', 0);
            }
            $candidates['notes'] = $this->db->where(['rcs.candidate_id' => $candidate_id, 'rcn.status' => 1])
                ->select("rcn.*,rrm.name as requirement,rsl.name as stage,CONCAT_WS(' ' , e.first_name, e.last_name) as employee")
                ->join('requirement_candidate_notes rcn', 'rcn.requirement_candidate_screening_id=rcs.id')
                ->join('requirement_screening_levels rsl', 'rsl.id=rcs.requirement_screening_level_id')
                ->join('requirements r', 'r.id=rsl.requirement_id')
                ->join('requirement_role_master rrm', 'rrm.id=r.requirement_role_id')
                ->join('employee e', 'e.id=rcn.created_by', 'left')
                ->get('requirement_candidate_screening rcs')->result_array();
        }

        return $candidates;
    }
}
