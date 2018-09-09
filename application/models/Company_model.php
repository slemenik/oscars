<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Company_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_id($company_name, $type) {
        //if exists return, else create and return
        $company_id_db = $this->get_id_from_db($company_name, $type);
        if ($company_id_db) {
            $company_id = $company_id_db['COMPANY_ID'];
        } else {
            $company_id = $this->create_company($company_name, $type);
        }
        return $company_id;


    }

    function get_id_from_db($company_name, $type) {
        $this->db->select('COMPANY_ID');
        $this->db->from('company');
        $this->db->where('COMPANY_NAME', $company_name);
        $this->db->where('CLASIFICATION', $type);
        return $this->db->get()->row_array();
    }

    function create_company($company_name, $type) {
        $this->db->insert('company', ['COMPANY_NAME' => $company_name, 'CLASIFICATION' => $type]);
        return $this->db->insert_id();
    }


//
//
    function set_company($movie_id, $company_id) {
        $this->db->insert('company', ['MOVIE_ID' => $movie_id, 'COMPANY_ID' => $company_id]);
        return $this->db->insert_id();
    }
//
//

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