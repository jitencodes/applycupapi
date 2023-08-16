<?php
defined('BASEPATH') or exit('No direct script access allowed');
class RequirementCandidateNotesModel extends CI_Model
{
    public function get($id)
    {
        return $this->db->where(['rcn.requirement_candidate_screening_id' => $id, 'rcn.status' => 1])
            ->select("rcn.*,CONCAT(e.first_name,' ',IFNULL(e.last_name,'')) as created_by,CONCAT(emp.first_name,' ',IFNULL(emp.last_name,'')) as update_by")
            ->join('employee e', 'e.id=rcn.created_by', 'left')
            ->join('employee emp', 'emp.id=rcn.updated_by', 'left outer')
            ->get('requirement_candidate_notes rcn')
            ->result_array();
    }

    public function add($requirement_candidate_screening_id, $remark, $is_private,$created_by)
    {
        $add = [
            'requirement_candidate_screening_id' => $requirement_candidate_screening_id,
            'remark' => $remark,
            'is_private' => $is_private,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $created_by,
            'status' => 1
        ];
        return $this->db->insert('requirement_candidate_notes', $add);
    }

    public function update($id, $remark, $is_private, $updated_by)
    {
        $update = [
            'remark' => $remark,
            'is_private' => $is_private,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $updated_by,
            'status' => 1
        ];
        return $this->db->where('id', $id)->update('requirement_candidate_notes', $update);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->update('requirement_candidate_notes', ['status' => 2]);
    }
}
