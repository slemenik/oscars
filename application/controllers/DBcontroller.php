<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DBcontroller extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', 0);
        // print_r(php_ini_loaded_file());
    }

    public function testDB() {
        // $this->db->where('Id_projekta', $id_project);
//        var_dump(5);
        var_dump($this->db->get('movie')->result_array());
    }

    public function create() {
        var_dump($this->input->post());
    }

    public function index(){
        var_dump(123);
    }

}