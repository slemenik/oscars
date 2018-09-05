<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DBcontroller extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', 0);
        $this->load->model('MovieDTO');
        // header('Content-Type: application/json');
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

    public function kek() {
        var_dump(phpversion());
    }

    public function update_movie() {
        $data = $this->input->post()['movieData'];

        var_dump($data);
        if (isset($data['releaseDate'])) {
            $releaseDate = substr($data['releaseDate'], 0,4).
                "-".substr($data['releaseDate'],4,2)
                ."-".substr($data['releaseDate'],6,2);
        } else {
            $releaseDate = null;
        }

        if (isset($data['runtime'])) {
            $lengthMin = explode(" ", $data['runtime'])[0];
            $hours = floor( $lengthMin / 60);
            $minutes = $lengthMin % 60;
            $length = "0" . $hours . ":" . $minutes . ":00";
        } else {
            $length = null;
        }

        if (isset($data['business']) && isset($data['business']['budget']) && $data['business']['budget'] != null) {
            $budget = str_replace(",","", substr($data['business']['budget'], 1));
        } else {
            $budget = null;
        }

        if (isset($data['business']) && isset($data['business']['worldwide']) && $data['business']['worldwide'] != null) {
            $box_office = str_replace(",","", substr($data['business']['worldwide'], 1));
        } else {
            $box_office = null;
        }

        if (isset($directors)) {

            foreach ($directors as $director) {
                //poglej če obstaja
                $db_id = $this->Person_model->get_person_by_imdb_id($director['id']);
                if ($db_id == null) {
                    var_dump(123);
                } elseif (is_array($db_id) && empty($db_id)) {
                    var_dump(456);
                } elseif (is_array($db_id)) {
                    var_dump(789);
                } else {
                    var_dump(000);
                }


                // če ne obstaja ga vstavi

                //dodaj k filmu
            }

        }



        $dto = [
            'TITLE' => $data['title'],
            'PART' => null,
            'BOX_OFFICE' => $box_office,
            'BUDGET' => $budget,
            'RELEASE_DATE' => $releaseDate,
            'LENGTH' => $length,
            'IMDB_ID'  => $data['idIMDB']
        ];

        // var_dump($dto);

        // echo $this->MovieDTO->update($dto);
    }

}