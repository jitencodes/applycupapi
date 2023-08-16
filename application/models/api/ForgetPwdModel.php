<?php if (!defined('BASEPATH')) exit('No direct script allowed');

class ForgetPwdModel extends CI_Model
{

    public function user_pwd_forget($user_id,$email)
    {
        $this->load->helpers('string');
        $token = random_string('alnum',10);
        $forget_link = DOMAIN_URL.'reset-password/'.$token;
        $date = date('Y-m-d');
        if($this->db->where('id',$user_id)->update('employee',['pwd_token' => $token,'pwd_token_expired' => date('Y-m-d',strtotime($date. '+1 day'))])){
            return $this->send_email_pwd($email,$forget_link);
        }
    }


    public function send_email_pwd($email,$forget_link)
    {
        $data = [
            'email' => $email,
            'forget_link' => $forget_link
        ];
        $email_template = $this->load->view('email_template/forget_password', $data, true);
        $subject = "Thank You for your application!";
        return $this->MasterModel->send_email('admin@applycup.com', $email, $subject, $email_template);
    }

    public function update_password($token, $password){
        if($this->db->where('pwd_token',$token)->update('employee',['password' => sha1($password), 'pwd_token' => NULL,'pwd_token_expired' => NULL])){
            return true;
        }
    }

}