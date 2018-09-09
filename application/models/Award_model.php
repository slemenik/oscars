<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Award_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }


    function get_award_id($award_name) {
        //if exists return, else create and return
        $award_id = $this->get_id_from_db($award_name);
        if ($award_id) {
            $award_id = $award_id['AWARD_TYPE_ID'];
        } else {
            $award_id = $this->create_award($award_name);
        }
        return $award_id;

    }

    function get_id_from_db($award_name) {
        $this->db->select('AWARD_TYPE_ID');
        $this->db->from('award_type');
        $this->db->where('AWARD_NAME', $award_name);
        return $this->db->get()->row_array();
    }

    function create_award($award_name) {
        $this->db->insert('award_type', ['AWARD_NAME' => $award_name]);
//        return $this->db->insert_id();
    }

    function set_actor_award($data) {
        $this->db->insert('actor_reward', $data);
//        return $this->db->insert_id();
    }

    function set_director_award($data) {
        $this->db->insert('director_award', $data);
//        return $this->db->insert_id();
    }

    function set_writer_award($data) {
        $this->db->insert('screenwriter_award', $data);
//        return $this->db->insert_id();
    }

    function set_movie_award($data) {
        $this->db->insert('movie_award', $data);
    }



//
//
//    function set_award($movie_id, $genre_id) {
//        $this->db->insert('movie_genre', ['MOVIE_ID' => $movie_id, 'GENRE_ID' => $genre_id]);
//        return $this->db->insert_id();
//    }

}

