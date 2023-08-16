<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Blogs extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('api/BlogsModel');
        $this->load->model('MasterModel','Master');
        $this->auth();
    }

    public function index_get()
    {
        $id = $this->get('id');
        $search = $this->get('keyword');
        $length = $this->get('length');
        $start = !$this->get('start') ? 0 : ($this->get('start') - 1);
        $status = $this->get('status');
        $total = $this->BlogsModel->count_all();
        $data = $this->BlogsModel->get_list($id, $search, $start, $length, $status);
        if (!empty($data)) {
            $this->set_response(['data' => $data,'status' => true,'image_url' => base_url(), 'total' => $total], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->set_response([
                'data' => [], 'total' => 0,
                'status' => FALSE,
                'message' => 'Data could not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function index_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('title', 'Blog title', 'trim|required');
            $this->form_validation->set_rules('author', 'Author', 'trim|required');
            $this->form_validation->set_rules('description', 'Description', 'required');
            $this->form_validation->set_rules('meta_title', 'Meta Title', 'required');
            $this->form_validation->set_rules('blog_date', 'Blog Date', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $created_by = $this->user_data->id;
            $title = $this->post('title');
            $author = $this->post('author');
            $description = $this->post('description');
            $meta_title = $this->post('meta_title');
            $meta_keywords = $this->post('meta_keywords');
            $meta_description = $this->post('meta_description');
            $blog_date = $this->post('blog_date');
            $status = $this->post('status');
            $image = "";
            $thumb = "";
            if (isset($_FILES['image'])) {
                $file = $this->Master->upload('image', 'blogs');
                if ($file['code'] == 1) {
                    $image = $file['file_url'];
                    if ($this->Master->create_thumbs($file['file_name'], $image,'./uploads/blogs/thumb/',355,231,true)){
                        $thumb = 'uploads/blogs/thumb/' . $file['file_name'];
                    }    
                } else {
                    $this->response([
                        'status' => False,
                        'message' => $file['msg'],
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }
            $slug = url_title($title, '-', TRUE);
            if ($this->BlogsModel->add($title, $slug, $author, $image, $thumb, $description, $blog_date, $created_by, $status,$meta_title,$meta_keywords,$meta_description)) {
                $this->response([
                    'status' => True,
                    'message' => 'Data add successfully',
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data add failed',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        } catch (\Throwable $e) {
            $this->response([
                'status' => False,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function edit_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('id', 'Blog Id', 'trim|required');
            $this->form_validation->set_rules('title', 'Blog title', 'trim|required');
            $this->form_validation->set_rules('author', 'Author', 'trim|required');
            $this->form_validation->set_rules('description', 'Description', 'required');
            $this->form_validation->set_rules('meta_title', 'Meta Title', 'required');
            $this->form_validation->set_rules('blog_date', 'Blog Date', 'trim|required');
            if (!$this->form_validation->run()) throw new Exception(validation_errors());
            $updated_by = $this->user_data->id;
            $id = $this->post('id');
            $title = $this->post('title');
            $author = $this->post('author');
            $description = $this->post('description');
            $meta_title = $this->post('meta_title');
            $meta_keywords = $this->post('meta_keywords');
            $meta_description = $this->post('meta_description');
            $blog_date = $this->post('blog_date');
            $status = $this->post('status');
            $image = "";
            $thumb = "";
            if (isset($_FILES['image'])) {
                $file = $this->MasterModel->upload('image', 'blogs');
                if ($file['code'] == 1) {
                    $image = $file['file_url'];
                    $thumb = "";
                } else {
                    $this->response([
                        'status' => False,
                        'message' => $file['msg'],
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }
            $slug = url_title($title, '-', TRUE);
            if ($this->BlogsModel->update($id, $title, $author, $slug, $image, $thumb, $description, $blog_date, $updated_by, $status,$meta_title,$meta_keywords,$meta_description)) {
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
}
