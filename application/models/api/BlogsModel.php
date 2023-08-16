<?php
if (!defined('BASEPATH')) exit('No direct script allowed');

class BlogsModel extends CI_Model
{
    protected $table = "blogs";
    var $selected_col = "id,title,slug,author,image,thumb,description,blog_date,status,created_at,updated_at,meta_title,meta_keywords,meta_description";
    var $column_search = ["id", "title", "slug", "author", "image", "thumb", "description", "blog_date", "status"];

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
                $this->db->where('status', $status);
            }
            return $this->db->select($this->selected_col)
                ->order_by('created_at', 'desc')
                ->get($this->table)->result_array();
        }

        // Find and return a single record for a particular user.

        $id = (int)$id;

        // Validate the id.
        if ($id <= 0) {
            return false;
        }
        if ($status) {
            $this->db->where('status', $status);
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

    function is_exist($value, $id = false)
    {
        if ($id) {
            $this->db->where('id !=', $id);
        }
        return $this->db->where('name', $value)->get($this->table)->num_rows();
    }

    public function add($title, $slug, $author, $image, $thumb, $description, $blog_date, $created_by, $status,$meta_title,$meta_keywords,$meta_description): bool
    {
        $data = [
            'title' => $title,
            'slug' => $slug,
            'author' => $author,
            'description' => $description,
            'blog_date' => $blog_date,
            'meta_title' => $meta_title,
            'meta_keywords' => $meta_keywords,
            'meta_description' => $meta_description,
            'created_by' => $created_by,
            'created_at' => date("Y-m-d H:i:s"),
            'status' => $status
        ];
        if($image){
            $data['image'] = $image;
            $data['thumb'] = $thumb;
        }
        if ($this->db->insert($this->table, $data)) {
            return true;
        }
    }

    public function update($id, $title, $author, $slug, $image, $thumb, $description, $blog_date, $updated_by, $status,$meta_title,$meta_keywords,$meta_description): bool
    {
        $data = [
            'title' => $title,
            'slug' => $slug,
            'author' => $author,
            'description' => $description,
            'blog_date' => $blog_date,
            'meta_title' => $meta_title,
            'meta_keywords' => $meta_keywords,
            'meta_description' => $meta_description,
            'updated_by' => $updated_by,
            'updated_at' => date("Y-m-d H:i:s"),
            'status' => $status
        ];
        if($image){
            $data['image'] = $image;
            $data['thumb'] = $thumb;
        }
        if ($this->db->where('id', $id)->update($this->table, $data)) {
            return true;
        }
        return false;
    }
}
