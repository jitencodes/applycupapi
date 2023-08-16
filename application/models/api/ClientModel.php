<?php
if (!defined('BASEPATH')) exit('No direct script allowed');

class ClientModel extends CI_Model
{
    protected $table = "company";
    protected $table_alias = "company c";
    var $selected_col = "c.id,c.name,lm.name as location,cs.name as client_status,e.first_name,e.last_name,c.status,cc.name as contact_person,cc.mobile,cc.email,cc.mobile_2,cc.designation,cc.mobile,cc.mobile_2,cc.designation,csm.name as client_source,cs.name as client_status,c.location as location_id,c.client_source_master_id,c.client_status as client_status_id,c.employe_assign_to as employee_id,c.notes";
    var $column_search = ["c.id", "c.name", "lm.name", "cs.name", "e.first_name", "e.last_name", "c.status", "cc.name", "cc.mobile", "cc.email", "cc.mobile_2", "cc.designation", "cc.mobile", "cc.mobile_2", "cc.designation","c.notes"];

    public function get_list($id, $search, $start, $length, $status)
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

            if ($status) {
                $this->db->where('c.status', $status);
            }
            return $this->db->select($this->selected_col)
                ->order_by('c.created_at', 'desc')
                ->join('location_master lm', 'lm.id=c.location', 'left')
                ->join('client_source_master csm', 'csm.id=c.client_source_master_id', 'left')
                ->join('client_status_master cs', 'cs.id=c.client_status', 'left')
                ->join('company_contact_person cc', 'cc.company_id=c.id', 'left')
                ->join('employee e', 'e.id=c.employe_assign_to', 'left outer')
                ->get($this->table_alias)->result_array();
        }

        // Find and return a single record for a particular user.

        $id = (int)$id;

        // Validate the id.
        if ($id <= 0) {
            return false;
        }
        if ($status) {
            $this->db->where('c.status', $status);
        }
        return $this->db->where(['c.id' => $id])
            ->select($this->selected_col)
            ->order_by('c.created_at', 'desc')
            ->join('location_master lm', 'lm.id=c.location', 'left')
            ->join('client_source_master csm', 'csm.id=c.client_source_master_id', 'left')
            ->join('client_status_master cs', 'cs.id=c.client_status', 'left')
            ->join('company_contact_person cc', 'cc.company_id=c.id', 'left')
            ->join('employee e', 'e.id=c.employe_assign_to', 'left outer')
            ->get($this->table_alias)->row_array();
    }

    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    function is_exist($value, $id = false)
    {
        if ($id) {
            $this->db->where('id !=', $id);
        }
        return $this->db->where('name', $value)->get($this->table)->num_rows();
    }

    public function add($company,$location,$client_source_master_id,$client_status,$employe_assign_to,$status,$contact_person,$mobile,$email,$designation,$mobile_2,$notes): bool
    {
        $data = [
            'name' => $company,
            'location' => $location,
            'client_source_master_id' => $client_source_master_id,
            'client_status' => $client_status,
            'employe_assign_to' => $employe_assign_to,
            'notes' => $notes,
            'created_at' => date("Y-m-d H:i:s"),
            'status' => $status
        ];
        if ($this->db->insert($this->table, $data)){
            $id = $this->db->insert_id();
            $contact_person_data = [
                'name' => $contact_person,
                'mobile' => $mobile,
                'email' => $email,
                'designation' => $designation,
                'company_id' => $id,
                'status' => 1
            ];
            if ($mobile_2){
                $contact_person_data['mobile_2'] = $mobile_2;
            }
            if ($this->db->insert('company_contact_person', $contact_person_data)){
                return true;
            }
        }
        return false;
    }

    public function update($id, $company,$location,$client_source_master_id,$client_status,$employe_assign_to,$status,$contact_person,$mobile,$email,$designation,$mobile_2,$contact_person_status,$notes): bool
    {
        $data = [
            'name' => $company,
            'location' => $location,
            'client_source_master_id' => $client_source_master_id,
            'client_status' => $client_status,
            'employe_assign_to' => $employe_assign_to,
            'notes' => $notes,
            'status' => $status
        ];
        if ($this->db->where('id',$id)->update($this->table, $data)){
            $contact_person_data = [
                'name' => $contact_person,
                'mobile' => $mobile,
                'email' => $email,
                'designation' => $designation,
                'status' => $contact_person_status
            ];
            if ($mobile_2){
                $contact_person_data['mobile_2'] = $mobile_2;
            }
            if ($this->db->where('company_id',$id)->update('company_contact_person', $contact_person_data)){
                return true;
            }
        }
        return false;
    }

    public function active_clients(){
        return $this->db->where('status',1)->select('id as value,name as label')->order_by('created_at','desc')->get($this->table)->result_array();
    }
}
