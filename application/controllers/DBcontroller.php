<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DBcontroller extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', 0);
        $this->load->model('MovieDTO');
        header('Content-Type: application/json');
        // print_r(php_ini_loaded_file());
    }

    public function testDB() {
        // $this->db->where('Id_projekta', $id_project);
        var_dump($this->db->get('movie')->result_array());
    }

    public function create() {
       $dto = $this->input->post();
       echo $this->MovieDTO->insert($dto);
    }

    public function index(){
        var_dump(123);
    }

    public function get_undefined_imdb_ids(){
//        var_dump($this->MovieDTO->get_undefined_imdb_ids());
        echo json_encode($this->MovieDTO->get_undefined_imdb_ids());
    }

}