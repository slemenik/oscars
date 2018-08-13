<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Documentation_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function get_all()
    {
        return $this->db->get('dokumentacija')->result_array();
    }

    public function get_data($id)
    {
        return $this->db->where('Id_strani', $id)->get('dokumentacija')->row_array();
    }

}