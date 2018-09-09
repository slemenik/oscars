<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }


    function get_category_id($category_name) {
        //if exists return, else create and return
        $category_id = $this->get_id_from_db($category_name);
        if ($category_id) {
            $category_id = $category_id['CATEGORY_ID'];
        } else {
            $category_id = $this->create_category($category_name);
        }
        return $category_id;

    }

    function get_id_from_db($category_name) {
        $this->db->select('CATEGORY_ID');
        $this->db->from('category');
        $this->db->where('CATEGORY_NAME', $category_name);
        return $this->db->get()->row_array();
    }

    function create_category($category_name) {
        $this->db->insert('category', ['CATEGORY_NAME' => $category_name]);
        return $this->db->insert_id();
    }


//
//
//    function set_award($movie_id, $genre_id) {
//        $this->db->insert('movie_genre', ['MOVIE_ID' => $movie_id, 'GENRE_ID' => $genre_id]);
//        return $this->db->insert_id();
//    }

}

