<?php defined('BASEPATH') OR exit('No direct script access allowed');


class MovieDTO extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_person_by_imdb_id($imdb_id) {
        $this->db->select('PERSON_ID');
        $this->db->from('person');
        $this->db->where('IMDB_ID', $imdb_id);
        return $this->db->get()->row_array();
    }

}