<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Import_data extends BD_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
    }
    public function index_post()
    {
        $master_name = $this->post('master_name');
        $file_name = $_FILES['master_data']['tmp_name'];
        $extension = pathinfo($_FILES['master_data']['name'], PATHINFO_EXTENSION);
        $allowed = ['xlsx', 'XLSX', 'xls', 'XLS'];
        if (in_array($extension, $allowed)) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        } else {
            $this->response([
                'status' => false,
                'message' => 'Select valid file type. please select excel sheet',
            ], REST_Controller::HTTP_OK);
        }
        try {
            $spreadsheet = $reader->load($file_name);
            $sheet_data = $spreadsheet->getSheetByName($master_name);
            if (!$sheet_data){
                $this->response([
                    'status' => false,
                    'message' => "Sheet not found",
                ], REST_Controller::HTTP_OK);
            }
            $sheet_data = $sheet_data->toArray();
            if (count($sheet_data) < 2){
                $this->response([
                    'status' => false,
                    'message' => "Data not found, Please add first row for header.",
                ], REST_Controller::HTTP_OK);
            }
            $list = [];
            $header = [];
            foreach ($sheet_data as $key => $val) {
                if ($key == 0) {
                    $header = $val;
                }else{
                    $temp = [];
                    foreach ($header as $k => $item) {
                        $temp[$item] = $val[$k];
                    }
                    $list[] = $temp;
                }
            }
            if (file_exists($file_name))
                unlink($file_name);
            if (count($list) > 0) {
                $result = false;
                if ($master_name == 'Skills'){
                    $this->load->model('api/SkillModel');
                    $result = $this->SkillModel->import($list);
                }else if ($master_name == 'Job-Roles'){
                    $this->load->model('api/RequirementRoleMaster');
                    $result = $this->RequirementRoleMaster->import($list);
                }else if ($master_name == 'Qualification'){
                    $this->load->model('api/QualificationModel');
                    $result = $this->QualificationModel->import($list);
                }else if ($master_name == 'Candidate-Source'){
                    $this->load->model('api/CandidateSourceModel');
                    $result = $this->CandidateSourceModel->import($list);
                }else if ($master_name == 'Client-Source'){
                    $this->load->model('api/ClientSourceModel');
                    $result = $this->ClientSourceModel->import($list);
                }else if ($master_name == 'Candidate-Status'){
                    $this->load->model('api/ClientSourceModel');
                    $result = $this->ClientSourceModel->import($list);
                }else if ($master_name == 'Client-Status'){
                    $this->load->model('api/ClientStatusModel');
                    $result = $this->ClientStatusModel->import($list);
                }else if ($master_name == 'Location-Master'){
                    $this->load->model('api/LocationModel');
                    $result = $this->LocationModel->import($list);
                }else if ($master_name == 'Requirement-Status'){
                    $this->load->model('api/RequirementStatusMaster');
                    $result = $this->RequirementStatusMaster->import($list);
                }else if ($master_name == 'Salary-Currency'){
                    $this->load->model('api/SalaryCurrencyMaster');
                    $result = $this->SalaryCurrencyMaster->import($list);
                }else{
                    $this->response([
                        'status' => false,
                        'message' => "No new record is found.",
                    ], REST_Controller::HTTP_OK);
                }
                if ($result) {
                    $this->response([
                        'status' => true,
                        'message' => 'Import successfully',
                        'data' => $result
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => "Something went wrong. Please try again.",
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => false,
                    'message' => "No new record is found.",
                ], REST_Controller::HTTP_OK);
            }
        }catch (\Throwable $exception){
            $this->response([
                'status' => false,
                'message' => $exception->getMessage(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
