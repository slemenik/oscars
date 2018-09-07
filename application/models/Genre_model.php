<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Genre_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function set_genre_list($movie_id, $genre_list) {
        foreach ($genre_list as $genre_name) {

            $genre_id_db = $this->get_genre_id($genre_name);
            if ($genre_id_db) {
                $genre_id = $genre_id_db['GENRE_ID'];
            } else {
                $genre_id = $this->create_genre($genre_name);
            }
            $this->set_genre($movie_id, $genre_id);
        }
    }

    function get_genre_id($genre_name) {
        $this->db->select('GENRE_ID');
        $this->db->from('genre');
        $this->db->where('GENRE_NAME', $genre_name);
        return $this->db->get()->row_array();
    }

    function set_genre($movie_id, $genre_id) {
        $this->db->insert('movie_genre', ['MOVIE_ID' => $movie_id, 'GENRE_ID' => $genre_id]);
        return $this->db->insert_id();
    }

    function create_genre($genre_name) {
        $this->db->insert('genre', ['GENRE_NAME' => $genre_name]);
        return $this->db->insert_id();
    }

//    function get_person_by_imdb_id($imdb_id) {
//
//        //poglej če obstaja
//        $this->db->select('PERSON_ID');
//        $this->db->from('person');
//        $this->db->where('IMDB_ID', $imdb_id);
//        $db_id = $this->db->get()->row_array();
//
//        if ($db_id) {
//            return $db_id['PERSON_ID'];
//        }
//
//        //če ne obstaja kreiraj
//        $this->db->insert('person', ['IMDB_ID' => $imdb_id]);
//        return $this->db->insert_id();
//    }
//
//    function create_director($movie_id, $person_id){
//        $this->db->insert('director', ['MOVIE_ID' => $movie_id, 'PERSON_ID' => $person_id]);
//        return $this->db->insert_id();
//    }
//
//    function create_writer($movie_id, $person_id){
//        $this->db->insert('screenwriter', ['MOVIE_ID' => $movie_id, 'PERSON_ID' => $person_id]);
//        return $this->db->insert_id();
//    }



}