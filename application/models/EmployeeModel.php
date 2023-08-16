<?php if (!defined('BASEPATH')) exit('No direct script allowed');

class EmployeeModel extends CI_Model
{
    protected $table = "employee";

    function get_user($q)
    {
        return $this->db->get_where($this->table, $q);
    }

    function verify_token($id, $token)
    {
        return $this->db->get_where($this->table, ['id' => $id, 'token' => $token]);
    }

    function add_token($id, $token, $token_exp)
    {
        $token_exp = date('Y-m-d H:i:s', $token_exp);
        return $this->db->where('id', $id)->update($this->table, ['token' => $token, 'token_expiry' => $token_exp]);
    }

    function add_employee($first_name, $last_name, $emp_id, $mobile, $email, $mobile_2, $dob, $department, $employement_type, $role_id, $location, $remark, $requirement_enable, $status)
    {
        $data = [
            'first_name' => $first_name,
            'emp_id' => $emp_id,
            'email' => $email,
            'department' => $department,
            'employement_type' => $employement_type,
            'role_id' => $role_id,
            'status' => $status,
        ];
        if ($last_name) {
            $data['last_name'] = $last_name;
        }
        if ($mobile) {
            $data['mobile'] = $mobile;
        }
        if ($mobile_2) {
            $data['mobile_2'] = $mobile_2;
        }
        if ($location) {
            $data['location'] = $location;
        }
        if ($dob) {
            $data['dob'] = $dob;
        }
        if ($remark) {
            $data['remark'] = $remark;
        }
        $data['requirement_enable'] = $requirement_enable;
        $pass = substr($first_name,0,4).''.$emp_id;
        $data['password'] = sha1($pass);
        if ($this->db->insert($this->table,$data)){
            return $pass;
        }
    }

    function update_employee($id, $first_name, $last_name, $emp_id, $mobile, $email, $mobile_2, $dob, $department, $employement_type, $role_id, $location, $remark, $requirement_enable, $status)
    {
        $data = [
            'first_name' => $first_name,
            'emp_id' => $emp_id,
            'email' => $email,
            'department' => $department,
            'employement_type' => $employement_type,
            'role_id' => $role_id,
            'status' => $status,
        ];
        if ($last_name) {
            $data['last_name'] = $last_name;
        }
        if ($mobile) {
            $data['mobile'] = $mobile;
        }
        if ($mobile_2) {
            $data['mobile_2'] = $mobile_2;
        }
        if ($location) {
            $data['location'] = $location;
        }
        if ($dob) {
            $data['dob'] = $dob;
        }
        if ($remark) {
            $data['remark'] = $remark;
        }
        $data['requirement_enable'] = $requirement_enable;
        if ($this->db->where('id',$id)->update($this->table,$data)){
            return true;
        }
    }

    function check_employee_id($emp_id, $id = false)
    {
        if ($id){
            $this->db->where('id !=',$id);
        }
        return $this->db->where('emp_id',$emp_id)->get($this->table)->num_rows();
    }

    function get_responsible_user(){

    }

    function get_user_by($emp_id,$email)
    {
        return $this->db->get_where($this->table, ['emp_id' => $emp_id,'email' => $email]);
    }

    function check_old_password($user_id,$old_password){
        return $this->db->get_where($this->table,['id' => $user_id, 'password' => $old_password])->row_array();
    }

    function update_new_password($user_id,$new_password){
        return $this->db->where('id',$user_id)->update($this->table,['password' => $new_password]);
    }
    
    public function update_profile($user_id, $first_name,$last_name,$date_of_birth){
        return $this->db->where('id',$user_id)->update($this->table,['first_name' => $first_name,'last_name' => $last_name, 'dob' => $date_of_birth]);
    }
}
