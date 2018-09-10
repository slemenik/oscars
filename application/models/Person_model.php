<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Person_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_person_by_imdb_id($imdb_id) {

        //poglej če obstaja
        $this->db->select('PERSON_ID');
        $this->db->from('person');
        $this->db->where('IMDB_ID', $imdb_id);
        $db_id = $this->db->get()->row_array();

        if ($db_id) {
            return $db_id['PERSON_ID'];
        }

        //če ne obstaja kreiraj
        $this->db->insert('person', ['IMDB_ID' => $imdb_id]);
        return $this->db->insert_id();
    }

    function get_actor_by_movie_id($movie_id, $person_imdb_id, $person_name){
        $this->db->select('a.PERSON_ID');
        $this->db->from('actor a, person p');
        $this->db->where('a.PERSON_ID = p.PERSON_ID');
        $this->db->where('a.MOVIE_ID', $movie_id);
        $this->db->where('p.FULL_NAME ', $person_name);
        $this->db->where('p.IMDB_ID', $person_imdb_id);
        $db_id = $this->db->get()->row_array();

        if ($db_id) {
            return $db_id['PERSON_ID'];
        } else {
            return null;
        }
    }

    function update_person($data, $person_id) {
        $this->db->where('PERSON_ID', $person_id);
        $this->db->update('person', $data);
        return $this->db->affected_rows() == 1 ? true : false;

    }

    function create_director($movie_id, $person_id){
        $this->db->insert('director', ['MOVIE_ID' => $movie_id, 'PERSON_ID' => $person_id]);
        return $this->db->insert_id();
    }

    function create_writer($movie_id, $person_id){
        $insert_query = $this->db->insert_string('screenwriter', ['MOVIE_ID' => $movie_id, 'PERSON_ID' => $person_id]);
        $insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
        $this->db->query($insert_query);
//        return $this->db->insert_id();
    }

    function create_actor($movie_id, $person_id){
        $this->db->insert('actor', ['MOVIE_ID' => $movie_id, 'PERSON_ID' => $person_id]);
        return $this->db->insert_id();
    }



}