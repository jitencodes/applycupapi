<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Auth extends BD_Controller
{
    public function __construct()
    {
        parent::__construct();
        header_remove("X-Powered-By");
        header("Strict-Transport-Security:max-age=63072000");
        header("X-XSS-Protection: 1; mode=block");
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options:nosniff");
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
        $this->load->model('EmployeeModel');
    }

    public function index_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('emp_id', 'Employee ID', 'trim|required');
            $this->form_validation->set_rules('password', 'Password', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $u = $this->post('emp_id',true); //Username Posted
            $p = sha1($this->post('password',true)); //Pasword Posted
            $q = array('emp_id' => $u); //For where query condition
            $kunci = $this->config->item('apikey');
            $invalidLogin = ['status' => 'Invalid Login']; //Respon if login invalid
            $val = $this->EmployeeModel->get_user($q)->row(); //Model to get single data row from database base on username
            if ($this->EmployeeModel->get_user($q)->num_rows() == 0) {
                $this->response($invalidLogin, REST_Controller::HTTP_UNAUTHORIZED);
            }
            $match = $val->password;   //Get password for user from database
            if ($p == $match) {  //Condition if password matched
                $token['id'] = $val->id;  //From here
                $token['first_name'] = $val->first_name;
                $token['last_name'] = $val->last_name;
                $token['role_id'] = $val->role_id;
                $token['location'] = $val->location;
                $token['email'] = $val->email;
                $token['username'] = $u;
                $token['requirement_enable'] = $val->requirement_enable ? true : false;
                $date = new DateTime();
                $token['iat'] = $date->getTimestamp();
                $token['exp'] = $date->getTimestamp() + 24 * 60 * 60 * 5; //To here is to generate token
                $output['token'] = JWT::encode($token, $kunci); //This is the output token
                if ($this->EmployeeModel->add_token($val->id, $output['token'], $token['exp'])) {
                    $output['first_name'] = $val->first_name;
                    $output['last_name'] = $val->last_name;
                    $output['email'] = $val->email;
                    $output['role_id'] = $val->role_id;
                    $output['emp_id'] = $u;
                    $output['user_id'] = $val->id;
                    $this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
                } else {
                    $error = ['status' => 'Something went wrong!']; //Respon if login invalid
                    $this->set_response($error, REST_Controller::HTTP_EXPECTATION_FAILED); //This is the respon if failed
                }
            } else {
                $this->set_response($invalidLogin, REST_Controller::HTTP_UNAUTHORIZED); //This is the respon if failed
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function forget_password_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('empID', 'Employee ID', 'trim|required');
            $this->form_validation->set_rules('email', 'email', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $invalidLogin = ['status' => 'Invalid detail']; //Respon if login invalid
            $employee_id = $this->put('empID',true); //Username Posted
            $email = $this->put('email',true); //Username Posted
            if ($this->EmployeeModel->get_user_by($employee_id, $email)->num_rows() == 0) {
                $this->response($invalidLogin, REST_Controller::HTTP_UNAUTHORIZED);
            }
            $data = $this->EmployeeModel->get_user_by($employee_id, $email)->row(); //Model to get single data row from database base on username
            $this->load->model('api/ForgetPwdModel', 'ForgetPwd');
            if ($this->ForgetPwd->user_pwd_forget($data->id, $email)) {
                $output = [
                    'status' => true, 'msg' => 'Forget password reset success!'
                ];
                $this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
            } else {
                $error = ['status' => false, 'msg' => 'Forget password reset failed!'];
                $this->set_response($error, REST_Controller::HTTP_OK);
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function reset_password_put()
    {
        try {
            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('token', 'Token', 'trim|required');
            $this->form_validation->set_rules('password', 'Password', 'trim|required');
            $this->form_validation->set_rules('confirm_password', 'Confirm password', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $invalidLogin = ['status' => false, 'msg' => 'Invalid token'];
            $token = $this->put('token',true);
            $password = $this->put('password',true);
            $confirm_password = $this->put('confirm_password');
            $user = ['pwd_token' => $token, 'pwd_token_expired >=' => date('Y-m-d')];
            if ($this->EmployeeModel->get_user($user)->num_rows() == 0) {
                $this->response($invalidLogin, REST_Controller::HTTP_UNAUTHORIZED);
            } else {
                if ($password == $confirm_password) {
                    $this->load->model('api/ForgetPwdModel', 'ForgetPwd');
                    if ($this->ForgetPwd->update_password($token, $password)) {
                        $output = [
                            'status' => true, 'msg' => 'Password successfully change'
                        ];
                        $this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
                    } else {
                        $error = ['status' => false, 'msg' => 'Password change failed! Try again'];
                        $this->set_response($error, REST_Controller::HTTP_OK);
                    }
                } else {
                    $invalidLogin = ['status' => false, 'msg' => 'Password and confirm password does not match'];
                    $this->response($invalidLogin, REST_Controller::HTTP_FORBIDDEN);
                }
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function verify_reset_link_get()
    {
        try {
            $this->form_validation->set_data($this->get());
            $this->form_validation->set_rules('token', 'token', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $invalidLogin = ['status' => false, 'msg' => 'Invalid link'];
            $token = $this->get('token',true); //Username Posted
            $user = ['pwd_token' => $token, 'pwd_token_expired >=' => date('Y-m-d')];
            if ($this->EmployeeModel->get_user($user)->num_rows() == 0) {
                $this->response($invalidLogin, REST_Controller::HTTP_UNAUTHORIZED);
            } else {
                $output = [
                    'status' => true, 'msg' => 'Link valid!'
                ];
                $this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
