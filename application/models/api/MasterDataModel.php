<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MasterDataModel extends CI_Model
{
    public function get_masters($master): array
    {
        $result = [];
        if (in_array('employees', $master) || in_array('all', $master)) {
            $result['employees'] = $this->db->select("id as value,CONCAT(first_name,' ',IFNULL(last_name,''),' - ',emp_id) as label,CONCAT(first_name,' ',IFNULL(last_name,'')) as employee_name")
                ->where('status', 1)
                ->get('employee')->result_array();
        }
        if (in_array('recruiters', $master) || in_array('all', $master)) {
            $result['recruiters'] = $this->db->select("id as value,CONCAT(first_name,' ',IFNULL(last_name,''),' - ',emp_id) as label,CONCAT(first_name,' ',IFNULL(last_name,'')) as employee_name")
                ->where('status', 1)
                ->where('role_id', 3)
                ->get('employee')->result_array();
        }
        if (in_array('candidate_source', $master) || in_array('all', $master)) {
            $result['candidate_source'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('candidate_source_master')->result_array();
        }
        if (in_array('candidate_status', $master) || in_array('all', $master)) {
            $result['candidate_status'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('candidate_status_master')->result_array();
        }
        if (in_array('client_source', $master) || in_array('all', $master)) {
            $result['client_source'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('client_source_master')->result_array();
        }
        if (in_array('client_status', $master) || in_array('all', $master)) {
            $result['client_status'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('client_status_master')->result_array();
        }
        if (in_array('job_type', $master) || in_array('all', $master)) {
            $result['job_type'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('employement_type_master')->result_array();
        }
        if (in_array('location', $master) || in_array('all', $master)) {
            $result['location'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('location_master')->result_array();
        }
        if (in_array('qualification', $master) || in_array('all', $master)) {
            $result['qualification'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('qualification_master')->result_array();
        }
        if (in_array('requirement_role', $master) || in_array('all', $master)) {
            $result['requirement_role'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('requirement_role_master')->result_array();
        }
        if (in_array('requirement_status', $master) || in_array('all', $master)) {
            $result['requirement_status'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('requirement_status_master')->result_array();
        }
        if (in_array('role_master', $master) || in_array('all', $master)) {
            $result['role_master'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('role_master')->result_array();
        }
        if (in_array('salary_currency', $master) || in_array('all', $master)) {
            $result['salary_currency'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('salary_currency_master')->result_array();
        }
        if (in_array('skill_master', $master) || in_array('all', $master)) {
            $result['skill_master'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('skill_master')->result_array();
        }
        if (in_array('notice_period', $master) || in_array('all', $master)) {
            $result['notice_period'] = $this->db->where('status', 1)
                ->select('id as value,name as label')
                ->order_by('ordering', 'asc')
                ->get('notice_period_master')->result_array();
        }
        if (in_array('current_openings', $master) || in_array('all', $master)) {
            $result['current_openings'] = $this->db->where(['r.is_approved' => 1])
                ->select("r.id as value,CONCAT(rr.name,' - ',c.name) as label")
                ->order_by('r.created_at', 'desc')
                ->join('requirement_role_master rr','rr.id=r.requirement_role_id','left')
                ->join('company c','c.id=r.company_id','left')
                ->get('requirements r')->result_array();
        }
        if(in_array('active_clients', $master) || in_array('all', $master)){
            $result['active_clients'] = $this->db->where('status',1)->select('id as value,name as label')
                ->order_by('created_at','desc')
                ->get('company')->result_array();
        }
        return $result;
    }
}
