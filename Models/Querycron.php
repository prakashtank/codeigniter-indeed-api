<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Querycron extends CI_Model
{

    public function insert_jobdata($data)
    {
        return $this->db->insert('post_tbl',$data);
    }
    public function get_category()
    {
        $this->db->select('*');
        $this->db->where('cron','0');
        $this->db->limit('1');
        $this->db->order_by('id', 'DESC');
        $this->db->from('category_tbl');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function check_category()
    {
        $this->db->select('id');
        $this->db->where('cron','0');
        $this->db->order_by('id', 'DESC');
        $this->db->from('category_tbl');
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function update_category($data)
    {
      return $this->db->update('category_tbl', $data);
    }
   
   public function update_page_id($cate_id,$data)
   {
     $this->db->where('id', $cate_id);
     return $this->db->update('category_tbl', $data);
   }

   public function checkjobs($jobkey)
   {
        $this->db->select('id');
        $this->db->where('jobkey', $jobkey);
        $this->db->from('post_tbl');
        $query = $this->db->get();
        return $query->num_rows();
   }
    

}