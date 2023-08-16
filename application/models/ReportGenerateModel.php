<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class ReportGenerateModel extends CI_Model
{
    public function cv_source($date = false){
        if($date){
            $this->db->where('DATE(c.created_at)',$date);
        }
        $res = $this->db->select('e.id,COUNT(c.id) as total_candidates,c.created_at')
            ->group_by('DATE(c.created_at)')
            ->order_by('e.id')
            ->group_by('e.id')
            ->having('total_candidates >',0)
            ->join('candidates c','c.created_by=e.id','left')
            ->get('employee e')->result_array();
        if($res){
            return $this->cv_source_insert($res);
        }
    }

    function cv_source_insert($data){
        $rowCount = 0;
        $query = "INSERT INTO `cv_source_report`(`employee_id`,`total_cv_source`,`date`) VALUES ";
        $header = false;
        $firstRow = true;
        $temp = 0;
        foreach ($data as $row) {
            $date = date('Y-m-d',strtotime($row['created_at']));
            if (!$header){
                $rowCount++;
                if($firstRow){
                    $firstRow = !$firstRow;
                    $query .= "(";
                    $query .= "'".$this->db->escape_str(trim($row['id']))."',";
                    $query .= "'".$this->db->escape_str(trim($row['total_candidates']))."',";
                    $query .= "'".$this->db->escape_str(trim($date))."'";
                    $query .= ")";
                    $temp += 1;
                }else{
                    $query .= ",(";
                    $query .= "'".$this->db->escape_str(trim($row['id']))."',";
                    $query .= "'".$this->db->escape_str(trim($row['total_candidates']))."',";
                    $query .= "'".$this->db->escape_str(trim($date))."'";
                    $query .= ")";
                    $temp += 1;
                    if ($temp == 500) {
                        $query .= "ON DUPLICATE KEY UPDATE ";
                        $query .= 'total_cv_source= VALUES(total_cv_source)';
                        $this->db->query($query);
                        $temp = 0;
                        $firstRow = true;
                        $query = "INSERT INTO `cv_source_report`(`employee_id`,`total_cv_source`,`date`) VALUES ";
                    }
                }
            }else{
                $header = !$header;
            }
        }
        $query .= "ON DUPLICATE KEY UPDATE ";
        $query .= 'total_cv_source= VALUES(total_cv_source)';
        if(!$firstRow) {
            $this->db->query($query);
        }
        $successMsg = 'Total Rows (' . $rowCount . ')';
        return ['msg' => $successMsg, 'res' => true];
    }

    public function candidates_placement($date = false){
        if($date){
            $this->db->where('DATE(rcs.updated_at)',$date);
        }
        $res = $this->db->select('e.id,COUNT(c.id) as total_candidates,c.created_at')
            ->where(['rcl.name' => 'Joining','rcs.status' => 1])
            ->group_by('DATE(c.created_at)')
            ->order_by('e.id')
            ->group_by('e.id')
            ->having('total_candidates >',0)
            ->join('requirement_candidate_screening rcs','rcs.requirement_screening_level_id=rcl.id','left')
            ->join('candidates c','c.id=rcs.candidate_id','left')
            ->join('employee e','e.id=c.created_by','left')
            ->get('requirement_screening_levels rcl')->result_array();
        if($res){
            return $this->candidate_placement_insert($res);
        }
    }

    function candidate_placement_insert($data){
        $rowCount = 0;
        $query = "INSERT INTO `candidates_placement_report`(`employee_id`,`total_placement`,`report_date`) VALUES ";
        $header = false;
        $firstRow = true;
        $temp = 0;
        foreach ($data as $row) {
            $date = date('Y-m-d',strtotime($row['created_at']));
            if (!$header){
                $rowCount++;
                if($firstRow){
                    $firstRow = !$firstRow;
                    $query .= "(";
                    $query .= "'".$this->db->escape_str(trim($row['id']))."',";
                    $query .= "'".$this->db->escape_str(trim($row['total_candidates']))."',";
                    $query .= "'".$this->db->escape_str(trim($date))."'";
                    $query .= ")";
                    $temp += 1;
                }else{
                    $query .= ",(";
                    $query .= "'".$this->db->escape_str(trim($row['id']))."',";
                    $query .= "'".$this->db->escape_str(trim($row['total_candidates']))."',";
                    $query .= "'".$this->db->escape_str(trim($date))."'";
                    $query .= ")";
                    $temp += 1;
                    if ($temp == 500) {
                        $query .= "ON DUPLICATE KEY UPDATE ";
                        $query .= 'total_placement= VALUES(total_placement)';
                        $this->db->query($query);
                        $temp = 0;
                        $firstRow = true;
                        $query = "INSERT INTO `candidates_placement_report`(`employee_id`,`total_placement`,`report_date`) VALUES ";
                    }
                }
            }else{
                $header = !$header;
            }
        }
        $query .= "ON DUPLICATE KEY UPDATE ";
        $query .= 'total_placement= VALUES(total_placement)';
        if(!$firstRow) {
            $this->db->query($query);
        }
        $successMsg = 'Total Rows (' . $rowCount . ')';
        return ['msg' => $successMsg, 'res' => true];
    }
}