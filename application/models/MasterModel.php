<?php

if (!defined('BASEPATH')) exit('No direct script access alloed');
class MasterModel extends CI_Model
{
    public function upload($imagePost, $path = FALSE)
	{
		if (!is_dir('uploads/'.$path))
		{
			mkdir('./uploads/' . $path, 0775, TRUE);
		}
		$config['upload_path'] = './uploads/' . $path . '/';
		$config['allowed_types'] = 'jpg|png|gif|jpeg|webp|xls|xlsx|doc|docx|rtf|ppt|pptx|pptm|pdf|jfif|JFIF|pdf|PDF|mov|mp4|qt|M4P|M4V|OGG|MPG|MP2|MPEG|MPE|MPV|AVI|WMV|MOV|WEBM|FLV';
		$this->load->library('upload', $config);
		$this->upload->initialize($config);
		$data['code'] = 0;
		if (!$this->upload->do_upload($imagePost)) {
			$error = array('error' => $this->upload->display_errors());
			$data['msg'] = $error;
			return $data;
		} else {
			$data1 = $this->upload->data();
			$image_path = "uploads/" . $path . '/' . $this->upload->data()["file_name"];
			$data['code'] = 1;
			$data['parameter'] = $data1;
			$data['file_url'] = $image_path;
			$data['file_name'] = $this->upload->data()["file_name"];
			return $data;
		}
	}

	public function send_email($from_email,$to_email,$subject,$message){
		$config = array(
			'priority' => '1',
			'protocol' => smtp_protocol,
			'smtp_crypto' => smtp_crypto,
			'smtp_host' => smtp_host,
			'smtp_port' => smtp_port,
			'smtp_user' => smtp_user, // change it to yours
			'smtp_pass' => smtp_pass, // change it to yours
			'mailtype' => 'html',
			'charset' => 'iso-8859-1',
			'wordwrap' => TRUE,
			'newline' => "\r\n"
		);
		$this->email->initialize($config);
		$this->email->from($from_email);
		$this->email->to($to_email);
		$this->email->subject($subject);
		$this->email->message($message);
		if ($this->email->send()) {
			return true;
		}else{
			return false;
		}
	}


	function create_thumbs($file_name, $source_image,$thumb_path,$width,$height,$maintain_ratio)
	{
		$config = array(
			'image_library' => 'GD2',
			'source_image' => $source_image,
			'maintain_ratio' => $maintain_ratio,
			'width' => $width,
			'height' => $height,
			'new_image' => $thumb_path.$file_name
		);

		$this->load->library('image_lib', $config);
		$this->image_lib->initialize($config);
		if (!$this->image_lib->resize()) {
			return false;
		}else {
			$this->image_lib->clear();
			return true;
		}
	}

}
