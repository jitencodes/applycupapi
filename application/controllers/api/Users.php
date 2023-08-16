<?php

use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Trunc;

defined('BASEPATH') or exit('No direct script access allowed');

class Users extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('EmployeeModel', 'Employee');
        $this->auth();
    }

    var $column_search = array('e.id', 'e.first_name', 'e.last_name', 'e.emp_id', 'e.mobile', 'e.email', 'e.mobile_2', 'e.dob', 'e.department', 'e.employement_type', 'e.role_id', 'e.location');

    public function index_get()
    {
        $id = $this->get('id');
        $search = $this->get('keyword');
        $length = $this->get('length');
        $start = $this->get('start') - 1;
        if ($start) {
            $start = $start * $length;
        }
        $selected_col = "e.id,first_name,e.last_name,e.emp_id,e.mobile,e.email,e.mobile_2,e.dob,department,e.employement_type,e.role_id,e.location,et.name as employment_type,lm.name as location_name,rm.name as role_name,e.status,IF(e.role_id=1,1,e.requirement_enable) as requirement_enable,e.remark";
        // If the id parameter doesn't exist return all the users

        if ($id === NULL) {
            if ($length != -1)
                $this->db->limit($length, $start);

            if ($search) {
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

            $users = $this->db->where('e.status !=', 3)
                ->select($selected_col)
                ->join('employement_type_master et', 'et.id=e.employement_type AND et.status=1', 'left')
                ->join('location_master lm', 'lm.id=e.location AND lm.status=1', 'left outer')
                ->join('role_master rm', 'rm.id=e.role_id AND rm.status=1', 'left')
                ->get('employee e')->result_array();

            $this->db->where('status !=', 3)->from('employee');
            $total = $this->db->get()->num_rows();
            // Check if the users data store contains users (in case the database result returns NULL)
            if ($users) {
                // Set the response and exit
                $this->response(['data' => $users, 'total' => $total, 'status' => true], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                // Set the response and exit
                $this->response([
                    'data' => [],
                    'total' => 0,
                    'status' => FALSE,
                    'message' => 'No users were found'
                ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
            }
        }

        // Find and return a single record for a particular user.

        $id = (int) $id;

        // Validate the id.
        if ($id <= 0) {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Get the user from the array, using the id as key for retrieval.
        // Usually a model is to be used for this.

        $user = $this->db->where(['e.id' => $id])
            ->select($selected_col)
            ->join('employement_type_master et', 'et.id=e.employement_type AND et.status=1', 'left')
            ->join('location_master lm', 'lm.id=e.location AND lm.status=1', 'left outer')
            ->join('role_master rm', 'rm.id=e.role_id AND rm.status=1', 'left')
            ->get('employee e')->row_array();

        if (!empty($user)) {
            $this->set_response($user, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->set_response([
                'data' => [],
                'total' => 0,
                'status' => FALSE,
                'message' => 'User could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function index_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
            $this->form_validation->set_rules('emp_id', 'Employee Id', 'trim|required');
            $this->form_validation->set_rules('role_id', 'Role', 'trim|required');
            $this->form_validation->set_rules('employement_type', 'Employment type', 'required');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $first_name = $this->post('first_name');
            $last_name = $this->post('last_name');
            $emp_id = $this->post('emp_id');
            $mobile = $this->post('mobile');
            $email = $this->post('email');
            $mobile_2 = $this->post('mobile_2');
            $dob = $this->post('dob');
            $department = $this->post('department');
            $employement_type = $this->post('employement_type');
            $role_id = $this->post('role_id');
            $location = $this->post('location');
            $remark = $this->post('remark');
            $requirement_enable = $this->post('requirement_enable') ? 1 : 0;
            $status = $this->post('status');
            if ($this->Employee->check_employee_id($emp_id)) {
                $this->response([
                    'status' => False,
                    'message' => "This employee id already used",
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if ($this->Employee->add_employee($first_name, $last_name, $emp_id, $mobile, $email, $mobile_2, $dob, $department, $employement_type, $role_id, $location, $remark, $requirement_enable, $status)) {
                $this->response([
                    'status' => True,
                    'sort_msg' => "Password first name in 4 character + Employee ID",
                    'message' => 'Data add successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Update fail');
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function index_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('id', 'User Id', 'trim|required');
            $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
            $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
            $this->form_validation->set_rules('emp_id', 'Employee Id', 'trim|required');
            $this->form_validation->set_rules('role_id', 'Role', 'trim|required');
            $this->form_validation->set_rules('employement_type', 'Employment type', 'required');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $id = $this->put('id');
            $first_name = $this->put('first_name');
            $last_name = $this->put('last_name');
            $emp_id = $this->put('emp_id');
            $mobile = $this->put('mobile');
            $email = $this->put('email');
            $mobile_2 = $this->put('mobile_2');
            $dob = $this->put('dob');
            $department = $this->put('department');
            $employement_type = $this->put('employement_type');
            $role_id = $this->put('role_id');
            $location = $this->put('location');
            $remark = $this->put('remark');
            $requirement_enable = $this->put('requirement_enable') ? 1 : 0;
            $status = $this->put('status');
            if ($this->Employee->check_employee_id($emp_id, $id)) {
                $this->response([
                    'status' => False,
                    'message' => "This employee id already used",
                ], REST_Controller::HTTP_CONFLICT);
            }
            if ($this->Employee->update_employee($id, $first_name, $last_name, $emp_id, $mobile, $email, $mobile_2, $dob, $department, $employement_type, $role_id, $location, $remark, $requirement_enable, $status)) {
                $this->response([
                    'status' => True,
                    'message' => 'Data update successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                throw new Exception('Update fail');
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function users_delete()
    {
        $id = (int) $this->get('id');

        // Validate the id.
        if ($id <= 0) {
            // Set the response and exit
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // $this->some_model->delete_something($id);
        $message = [
            'id' => $id,
            'message' => 'Deleted the resource'
        ];

        $this->set_response($message, REST_Controller::HTTP_NO_CONTENT); // NO_CONTENT (204) being the HTTP response code
    }

    public function change_password_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
            $this->form_validation->set_rules('old_password', 'Old Password', 'trim|required');
            $this->form_validation->set_rules('new_password', 'New Password', 'trim|required');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required');

            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $user_id = $this->user_data->id;
            $old_password = $this->put('old_password');
            $new_password = $this->put('new_password');
            $confirm_password = $this->put('confirm_password');
            if ($new_password == $confirm_password) {
                $check_password = $this->Employee->check_old_password($user_id, sha1($old_password));
                if ($check_password) {
                    if($this->Employee->update_new_password($user_id, sha1($new_password))){
                        $this->response([
                            'status' => true,
                            'message' => "Password change successfully",
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                            'status' => False,
                            'message' => "Password change failed",
                        ], REST_Controller::HTTP_OK);
                    }
                } else {
                    $this->response([
                        'status' => False,
                        'message' => "Old password not match",
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => False,
                    'message' => "Password and confirm password not match",
                ], REST_Controller::HTTP_OK);
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function profile_update_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
            $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
            $this->form_validation->set_rules('date_of_birth', 'Date of birth', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $user_id = $this->user_data->id;
            $first_name = $this->put('first_name');
            $last_name = $this->put('last_name');
            $date_of_birth = $this->put('date_of_birth');
            if($this->Employee->update_profile($user_id, $first_name,$last_name,$date_of_birth)){
                $this->response([
                    'status' => true,
                    'message' => "Profile update successfully",
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => False,
                    'message' => "Profile update failed",
                ], REST_Controller::HTTP_OK);
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
