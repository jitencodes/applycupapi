<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');
class Report_generate extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('ReportGenerateModel','ReportGenerate');
    }
    public function cv_source_report()
    {
        $res = $this->ReportGenerate->cv_source(date('Y-m-d'));
        if ($res) {
            echo json_encode($res);
        }
    }

    public function candidates_placement(){
        $res = $this->ReportGenerate->candidates_placement(date('Y-m-d'));
        if ($res) {
            echo json_encode($res);
        }
    }
}
