<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');

class RequirementsModel extends CI_Model
{
    var $table = "requirements";
    var $table_requirement_skills = "requirement_skills";
    var $table_requirement_locations = "requirement_locations";
    var $tbl_requirement_qualifications = "requirement_qualifications";
    var $tbl_requirement_employees = "requirement_employees";

    public function add($company, $job_type, $number_of_vacancy, $experience_type, $is_industry_standard, $min_experience, $max_experience, $min_salary, $max_salary, $salary_currency, $notice_period_master_id, $requirement_description, $requirement_status, $job_role, $skills, $qualification, $assigned_to, $created_by, $status, $location_type, $location_id)
    {
        $add = [
            'company_id' => $company['value'],
            'requirement_role_id' => $job_role['value'],
            'employement_type' => $job_type['value'],
            'vacancy_count' => $number_of_vacancy,
            'experience_type' => $experience_type,
            'is_industry_standard' => $is_industry_standard,
            'notice_period_master_id' => $notice_period_master_id,
            'requirement_description' => $requirement_description,
            'requirement_status' => $requirement_status,
            'location_type' => $location_type,
            'created_by' => $created_by,
            'created_at' => date("Y-m-d H:i:s"),
            'post_on_web' => $status,
            'is_approved' => 1
        ];

        if ($is_industry_standard == 1) {
            $add['salary_currency_master_id'] = $salary_currency['value'];
            $add['min_salary'] = $min_salary;
            $add['max_salary'] = $max_salary;
        }
        if ($experience_type == 2) {
            $add['min_exp'] = $min_experience;
            $add['max_exp'] = $max_experience;
        }
        if ($this->db->insert($this->table, $add)) {
            $id = $this->db->insert_id();
            $this->add_skill($id, $skills);
            $this->add_location($id, $location_id);
            $this->add_qualification($id, $qualification);
            $this->assigned_to($id, $assigned_to);
            $screen_stages_master = $this->db->where('status', 1)->order_by('ordering', 'asc')->get('screen_stages_master')->result_array();
            foreach ($screen_stages_master as $item) {
                $this->screen_stages($id, $item, 1, $created_by);
            }
            return ['status' => true, 'res' => 'Data add successfully'];
        }
        return ['status' => false, 'res' => 'Data inserting failed'];
    }

    public function update($id, $company, $job_type, $number_of_vacancy, $experience_type, $is_industry_standard, $min_experience, $max_experience, $min_salary, $max_salary, $salary_currency, $notice_period_master_id, $requirement_description, $requirement_status, $job_role, $skills, $qualification, $assigned_to, $created_by, $status, $location_type, $location_id)
    {
        $add = [
            'company_id' => $company['value'],
            'requirement_role_id' => $job_role['value'],
            'employement_type' => $job_type['value'],
            'vacancy_count' => $number_of_vacancy,
            'experience_type' => $experience_type,
            'is_industry_standard' => $is_industry_standard,
            'notice_period_master_id' => $notice_period_master_id,
            'requirement_description' => $requirement_description,
            'requirement_status' => $requirement_status['value'],
            'location_type' => $location_type,
            'updated_by' => $created_by,
            'updated_at' => date("Y-m-d H:i:s"),
            'post_on_web' => $status,
        ];

        if ($is_industry_standard == 1) {
            $add['salary_currency_master_id'] = $salary_currency['value'];
            $add['min_salary'] = $min_salary;
            $add['max_salary'] = $max_salary;
        }
        if ($experience_type == 2) {
            $add['min_exp'] = $min_experience;
            $add['max_exp'] = $max_experience;
        }
        if ($this->db->where('id', $id)->update($this->table, $add)) {
            $this->add_skill($id, $skills);
            $this->add_location($id, $location_id);
            $this->add_qualification($id, $qualification);
            $this->assigned_to($id, $assigned_to);
            return ['status' => true, 'res' => 'Data update successfully'];
        }
        return ['status' => false, 'res' => 'Data updating failed'];
    }

    public function add_skill($id, $skills): bool
    {
        $this->db->where('requirement_id', $id)->update($this->table_requirement_skills, ['status' => 0]);
        if ($skills) {
            foreach ($skills as $skill) {
                $is_exits = $this->db->where(['requirement_id' => $id, 'skill_master_id' => $skill['value']])
                    ->get($this->table_requirement_skills)->row_array();
                if ($is_exits) {
                    $this->db->where('id', $is_exits['id'])->update($this->table_requirement_skills, ['status' => 1]);
                } else {
                    $add = [
                        'requirement_id' => $id,
                        'skill_master_id' => $skill['value'],
                        'status' => 1
                    ];
                    $this->db->insert($this->table_requirement_skills, $add);
                }
            }
        }
        return true;
    }

    public function add_location($id, $locations): bool
    {
        $this->db->where('requirement_id', $id)->update($this->table_requirement_locations, ['status' => 0]);
        if ($locations) {
            foreach ($locations as $location) {
                $is_exits = $this->db->where(['requirement_id' => $id, 'location_master_id' => $location['value']])
                    ->get($this->table_requirement_locations)->row_array();
                if ($is_exits) {
                    $this->db->where('id', $is_exits['id'])->update($this->table_requirement_locations, ['status' => 1]);
                } else {
                    $add = [
                        'requirement_id' => $id,
                        'location_master_id' => $location['value'],
                        'status' => 1
                    ];
                    $this->db->insert($this->table_requirement_locations, $add);
                }
            }
        }
        return true;
    }

    public function add_qualification($id, $qualifications): bool
    {
        $this->db->where('requirement_id', $id)->update($this->tbl_requirement_qualifications, ['status' => 0]);
        if ($qualifications) {
            foreach ($qualifications as $qualification) {
                $is_exits = $this->db->where(['requirement_id' => $id, 'qualification_master_id' => $qualification['value']])
                    ->get($this->tbl_requirement_qualifications)->row_array();
                if ($is_exits) {
                    $this->db->where('id', $is_exits['id'])->update($this->tbl_requirement_qualifications, ['status' => 1]);
                } else {
                    $add = [
                        'requirement_id' => $id,
                        'qualification_master_id' => $qualification['value'],
                        'status' => 1
                    ];
                    $this->db->insert($this->tbl_requirement_qualifications, $add);
                }
            }
        }
        return true;
    }

    public function assigned_to($id, $assigned_to): bool
    {
        $this->db->where('requirement_id', $id)->update($this->tbl_requirement_employees, ['status' => 0]);
        if ($assigned_to) {
            foreach ($assigned_to as $user) {
                $is_exits = $this->db->where(['requirement_id' => $id, 'employee_id' => $user['value']])
                    ->get($this->tbl_requirement_employees)->row_array();
                if ($is_exits) {
                    $this->db->where('id', $is_exits['id'])->update($this->tbl_requirement_employees, ['status' => 1]);
                } else {
                    $add = [
                        'requirement_id' => $id,
                        'employee_id' => $user['value'],
                        'status' => 1
                    ];
                    $this->db->insert($this->tbl_requirement_employees, $add);
                }
            }
        }
        return true;
    }

    public function screen_stages($id, $data, $status, $created_by)
    {
        try {
            $res = $this->db->where(['requirement_id' => $id, 'name' => $data['name']])
                ->get('requirement_screening_levels')->num_rows();
            if ($res > 0) {
                return ['status' => false, 'msg' => 'Screen stage name already exits'];
            } else {
                $add = [
                    'requirement_id' => $id,
                    'name' => $data['name'],
                    'ordering' => $data['ordering'],
                    'is_fixed' => $data['is_fixed'],
                    'action' => $data['action'],
                    'select_action' => $data['select_action'],
                    'hold_action' => $data['hold_action'],
                    'drop_action' => $data['drop_action'],
                    'reject_action' => $data['reject_action'],
                    'accept_action' => $data['accept_action'],
                    'decline_action' => $data['decline_action'],
                    'no_show_action' => $data['no_show_action'],
                    'joined_action' => $data['joined_action'],
                    'abscond_action' => $data['abscond_action'],
                    'created_by' => $created_by,
                    'created_at' => date("Y-m-d H:i:s"),
                    'status' => $status
                ];
                if ($this->db->insert('requirement_screening_levels', $add)) {
                    return ['status' => true, 'msg' => 'Screen stage add successfully'];
                }
            }
        } catch (\Throwable $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
        return ['status' => false, 'msg' => 'something went wrong'];
    }

    public function update_screen_stages($id, $status, $data = false)
    {
        if ($data && $status == 1) {
            $this->db->where('id', $id)->update('requirement_screening_levels', $data);
        }
        if ($status == 0 || $status == 3) {
            $this->db->where('id', $id)->update('requirement_screening_levels', ['status' => $status]);
        }
    }

    public function empolyee_assign($job_id, $employee_id): array
    {
        $is_exist = $this->db->where(['requirement_id' => $job_id, 'employee_id' => $employee_id])->get('requirement_employees')->row_array();
        if ($is_exist) {
            if ($is_exist['status'] == 0) {
                if ($this->db->where('id', $is_exist['id'])->update('requirement_employees', ['status' => 1])) {
                    //                    $employees = $this->db->where(['re.requirement_id' => $job_id, 're.status' => 1])->select('e.id,e.first_name,e.last_name,e.emp_id,e.email')
                    //                        ->join('employee e', 'e.id=re.employee_id AND e.status=1', 'left')
                    //                        ->get('requirement_employees re')->result_array();
                    return ['status' => true, 'res' => 'Data add successfully', 'data' => ""];
                }
            } else {
                return ['status' => false, 'res' => 'Employee already exist'];
            }
        } else {
            $add = [
                'requirement_id' => $job_id,
                'employee_id' => $employee_id,
                'status' => 1
            ];
            if ($this->db->insert($this->tbl_requirement_employees, $add)) {
                return ['status' => true, 'res' => 'Data add successfully', 'data' => ""];
            }
        }
        return ['status' => false, 'res' => 'Something went wrong!'];
    }

    public function candidate_favourite($screen_id, $is_favourite)
    {
        $update = ['is_favourite' => $is_favourite];
        $this->db->where(['id' => $screen_id]);
        $res = $this->db->update('requirement_candidate_screening', $update);
        if ($res) {
            return true;
        }
    }

    public function opening_with_stages($employee_id = false, $user_role = false, $search = false)
    {
        if ($user_role == 3) {
            $this->db->where(['re.employee_id' => $employee_id, "re.status" => 1])
                ->join('requirement_employees re', 're.requirement_id=r.id');
        }
        if($search){
            $this->db->like('rrm.name',$search);
        }
        $result = $this->db->select('r.id as value,rrm.name as label,c.name as company')->where('r.requirement_status !=', 0)->join('requirement_role_master rrm', 'rrm.id=r.requirement_role_id AND rrm.status=1')->join('company c', 'c.id=r.company_id')->limit('15')->get("requirements r")->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['stages'] = $this->db->select('id as value,name as label')
                ->where(['requirement_id' => $value['value'], 'status' => 1])
                ->order_by('ordering', 'asc')
                ->get('requirement_screening_levels')->result_array();
        }
        return $result;
    }

    public function screening_levels($from, $to, $stage_id, $job_id, $stage_name, $action)
    {
        $previous = $this->db->order_by('ordering', 'asc')->where(['requirement_id' => $job_id])->get('requirement_screening_levels')->result_array();
        $res = $this->array_move($from, $to, $previous);
        if ($res) {
            $update = [];
            $i = 1;
            foreach ($res as $value) {
                if ($value['id'] == $stage_id) {
                    $update[] = ['id' => $value['id'], 'name' => $stage_name, 'ordering' => $i, 'action' => $action];
                } else {
                    $update[] = ['id' => $value['id'], 'ordering' => $i];
                }
                $i++;
            }
            return $this->db->update_batch('requirement_screening_levels', $update, 'id');
        }
    }

    public function screening_levels_add($from, $to, $job_id, $stage_name, $action, $created_by)
    {
        $addTemp[] = ['id' => 0, 'requirement_id' => $job_id, 'name' => $stage_name, 'ordering' => 0];
        $previous = $this->db->order_by('ordering', 'asc')->where(['requirement_id' => $job_id])->get('requirement_screening_levels')->result_array();
        $new_array = array_merge($addTemp, $previous);
        $res = $this->array_move($from, $to, $new_array);
        if ($res) {
            $update = [];
            $i = 1;
            foreach ($res as $value) {
                if ($value['id'] == 0) {
                    $add = ['requirement_id' => $job_id, 'name' => $stage_name, 'ordering' => $i, 'action' => $action, 'status' => 1, 'created_by' => $created_by, 'created_at' => date('Y-m-d H:i:s')];
                    $this->db->insert('requirement_screening_levels', $add);
                } else {
                    $update[] = ['id' => $value['id'], 'ordering' => $i];
                }
                $i++;
            }
            return $this->db->update_batch('requirement_screening_levels', $update, 'id');
        }
    }


    function array_move($key, $new_index, $array)
    {
        if ($new_index < 0) return;
        if ($new_index >= count($array)) return;
        if (!array_key_exists($key, $array)) return;

        $ret = array();
        $ind = 0;
        foreach ($array as $k => $v) {
            if ($new_index == $ind) {
                $ret[$key] = $array[$key];
                $ind++;
            }
            if ($k != $key) {
                $ret[$k] = $v;
                $ind++;
            }
        }
        // one last check for end indexes
        if ($new_index == $ind)
            $ret[$key] = $array[$key];
        return $ret;
    }


    public function unpublish($id, $updated_by)
    {
        return $this->db->where('id', $id)->update('requirements', ['post_on_web' => 0, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => $updated_by]);
    }

    public function publish($id, $updated_by)
    {
        return $this->db->where('id', $id)->update('requirements', ['post_on_web' => 1, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => $updated_by]);
    }
}
