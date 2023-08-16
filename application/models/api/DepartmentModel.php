<?php if (!defined('BASEPATH')) exit('No direct script allowed');

class DepartmentModel extends CI_Model
{
    protected $table = "department_master";
    var $selected_col = "id,name,status,ordering";
    var $column_search = ["id", "name", "status", "ordering"];

    public function get_department($id, $search, $start, $length):array
    {
        if ($id === NULL) {
            if ($length != -1)
                $this->db->limit($length, ($start * $length));
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

            return $this->db->where('status', 1)
                ->order_by('ordering')
                ->select($this->selected_col)
                ->get($this->table)->result_array();
        }

        // Find and return a single record for a particular user.

        $id = (int)$id;

        // Validate the id.
        if ($id <= 0) {
            return [];
        }
        return $this->db->where(['id' => $id, 'status' => 1])
            ->select($this->selected_col)
            ->get($this->table)->row_array();
    }

    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    function check_department($location, $id = false)
    {
        if ($id) {
            $this->db->where('id !=', $id);
        }
        return $this->db->where('name', $location)->get($this->table)->num_rows();
    }

    public function add($name, $ordering, $status)
    {
        $data = [
            'name' => $name,
            'ordering' => $ordering,
            'status' => $status
        ];
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $name, $ordering, $status)
    {
        $data = [
            'name' => $name,
            'ordering' => $ordering,
            'status' => $status
        ];
        return $this->db->where('id',$id)->update($this->table, $data);
    }
}
