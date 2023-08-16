<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class UserRoleModel extends CI_Model
{
    protected $table = "role_master";
    var $selected_col = "id,name,permission_group_id,name as label,id as value,status,ordering";
    var $column_search = ["id", "name", "status", "ordering"];

    public function get_list($id, $search, $start, $length, $status)
    {
        if ($id === NULL) {
            if ($length != -1)
                $this->db->limit($length, $start);
            if ($search !== NULL) {
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

            if ($status){
                $this->db->where('status',$status);
            }
            return $this->db->select($this->selected_col)
                ->order_by('ordering')
                ->get($this->table)->result_array();
        }

        // Find and return a single record for a particular user.

        $id = (int)$id;

        // Validate the id.
        if ($id <= 0) {
            return false;
        }
        if ($status){
            $this->db->where('status',$status);
        }
        return $this->db->where(['id' => $id])
            ->select($this->selected_col)
            ->get($this->table)->row_array();
    }

    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
}
