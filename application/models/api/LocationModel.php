<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class LocationModel extends CI_Model
{
    protected $table = "location_master";
    var $selected_col = "id,name,status,ordering";
    var $column_search = ["id", "name", "status", "ordering"];

    public function get_location($id, $search, $start, $length, $status)
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

    function check_location($location, $id = false)
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

    public function import($data): array
    {
        $rowCount = 0;
        $query = "INSERT INTO `location_master`(`name`,`ordering`,`is_add_update`,`status`) VALUES ";
        $header = false;
        $firstRow = true;
        $temp = 0;
        $this->db->where_in('is_add_update',[1,2])->update($this->table,['is_add_update' => 0]);
        foreach ($data as $row) {
            if (!$header){
                $rowCount++;
                if($firstRow){
                    $firstRow = !$firstRow;
                    $query .= "(";
                    $query .= "'".$this->db->escape_str(trim($row['Name']))."',";
                    $query .= "'".$this->db->escape_str(trim($row['Ordering']))."',";
                    $query .= "'".$this->db->escape_str(1)."',";
                    $query .= "'".$this->db->escape_str(trim($row['Status']))."'";
                    $query .= ")";
                    $temp += 1;
                }else{
                    $query .= ",(";
                    $query .= "'".$this->db->escape_str(trim($row['Name']))."',";
                    $query .= "'".$this->db->escape_str(trim($row['Ordering']))."',";
                    $query .= "'".$this->db->escape_str(1)."',";
                    $query .= "'".$this->db->escape_str(trim($row['Status']))."'";
                    $query .= ")";
                    $temp += 1;
                    if ($temp == 500) {
                        $query .= "ON DUPLICATE KEY UPDATE ";
                        $query .= 'name= VALUES(name),';
                        $query .= 'status = VALUES(status),';
                        $query .= 'is_add_update = 2';
                        $this->db->query($query);
                        $temp = 0;
                        $firstRow = true;
                        $query = "INSERT INTO `location_master`(`name`,`ordering`,`is_add_update`,`status`) VALUES ";
                    }
                }
            }else{
                $header = !$header;
            }
        }
        $query .= "ON DUPLICATE KEY UPDATE ";
        $query .= 'name= VALUES(name),';
        $query .= 'status = VALUES(status),';
        $query .= 'is_add_update = 2';
        if(!$firstRow) {
            $this->db->query($query);
        }
        $insertCount = $this->db->where('is_add_update',1)->count_all_results($this->table);
        $updateCount = $this->db->where('is_add_update',2)->count_all_results($this->table);
        $notAddCount = ($rowCount - ($insertCount + $updateCount));
        $successMsg = 'Total Rows (' . $rowCount . ') | Inserted (' . $insertCount . ') | Updated (' . $updateCount . ') | Not Inserted (' . $notAddCount . ')';
        return ['msg' => $successMsg, 'res' => true];

    }
}
