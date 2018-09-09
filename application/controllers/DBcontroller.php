<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DBcontroller extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', 0);
        $this->db->db_debug = FALSE;
        $this->load->model('MovieDTO');
        $this->load->model('Person_model');
        $this->load->model('Genre_model');
        $this->load->model('Rating_model');
        $this->load->model('Company_model');
        $this->load->model('Award_model');
        $this->load->model('Category_model');
//        header('Content-Type: application/json');
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
//        $data = $this->input->post();//['movieData'];
        $data = get_request();

        $this->db->trans_begin();

        $movie_imdb_id = $data['idIMDB'];
        $movie_id = $this->MovieDTO->get_movie_id($movie_imdb_id);

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

        $dto = [
            'TITLE' => $data['title'],
            'PART' => null,
            'BOX_OFFICE' => $box_office,
            'BUDGET' => $budget,
            'RELEASE_DATE' => $releaseDate,
            'LENGTH' => $length,
            //'IMDB_ID'  => $data['idIMDB'] // ne spreminjamo id-ja
        ];

        $this->MovieDTO->update($dto, $movie_id);


        if (isset($data['directors'])) {

            foreach ($data['directors'] as $director) {
                $person_id = $this->Person_model->get_person_by_imdb_id($director['id']);
                $this->Person_model->update_person(['FULL_NAME' => $director['name']], $person_id);
                $this->Person_model->create_director($movie_id, $person_id);
            }

        }

        if (isset($data['writers'])) {
            foreach ($data['writers'] as $writer) {
                $person_id = $this->Person_model->get_person_by_imdb_id($writer['id']);
                $this->Person_model->update_person(['FULL_NAME' => $writer['name']], $person_id);
                $this->Person_model->create_writer($movie_id, $person_id);
            }
        }

        if (isset($data['genres'])) {
            $this->Genre_model->set_genre_list($movie_id, $data['genres']);
        }

        if (isset($data['rating'])) {
            $this->Rating_model->add_rating([
                'MOVIE_ID' => $movie_id,
                'SOURCE' => 'imdb',
                'SCORE' => $data['rating']
            ]);
        }

        if (isset($data['metascore'])) {
            $this->Rating_model->add_rating([
                'MOVIE_ID' => $movie_id,
                'SOURCE' => 'metascore',
                'SCORE' => $data['metascore']
            ]);
        }

        if (isset($data['companyCreditsFull'])) {
            foreach ($data['companyCreditsFull'] as $company_types) {
                $type = $company_types['type'];
                $actual_companies = $company_types['company'];
                foreach ($actual_companies as $company) {
                    $company_name = $company['name'];
                    $company_id = $this->Company_model->get_id($company_name, $type);
                    $this->Company_model->set_company($movie_id, $company_id);
                }
            }
        }

        if (isset($data['actors'])) {
            foreach ($data['actors'] as $actor) {
                $name = $actor['actorName'];
                $actor_imdb_id = $actor['actorId'];
                if (isset($actor['biography'])) {
                    $biography_data = $actor['biography'];

                    //spol
                    if (isset($biography_data['actorActress'])) {
                        $sex = $biography_data['actorActress'];
                        $gender = ($sex == "Actor") ? "M" : "F";
                    }
                    else {
                        $gender = null;
                    }

                    //datum rojstva
                    if (isset($biography_data['born'])) {
                        $date_string = $biography_data['born'];
                        $month = date_parse(explode(' ', $date_string)[0])['month'];
                        $day = str_replace(",", "", explode(' ', $date_string)[1]);
                        $year = explode(' ', $date_string)[2];
                        $birthday = "$year-$month-$day";
                    }
                    else {
                        $birthday = null;
                    }

                } else {
                    $gender = null;
                    $birthday = null;
                }

                $actor_data = [
                    'FULL_NAME' => $name,
                    'BIRTHDAY' => $birthday,
                    'GENDER' => $gender
                ];
                $actor_db_id = $this->Person_model->get_person_by_imdb_id($actor_imdb_id);
                $this->Person_model->update_person($actor_data, $actor_db_id);
                $this->Person_model->create_actor($movie_id, $actor_db_id);
            }
        }

        if (isset($data['awards'])) {
            foreach ($data['awards'] as $award) {
                $award_name_string = $award['award'];
                $explode = explode(' ', $award_name_string);
                $last_element = $explode[count($explode)-1];
                if (is_numeric($last_element)) {
                    $year = intval($last_element);
                    unset($explode[count($explode)-1]);
                    $award_name = implode($explode);
                } else {
                    $year = null;
                    $award_name = $award_name_string;
                }

                $award_id = $this->Award_model->get_award_id($award_name);

                foreach ($award['titlesAwards'] as $titlesAward) {
                    $titlesAwardOutcome = $titlesAward['titleAwardOutcome']; // Winner Oscar
                    $outcome = explode(' ', $titlesAwardOutcome)[0];
                    $outcome = $outcome == 'Winner' ? 1 : 0;
                    foreach ($titlesAward['categories'] as $category) {
                        $category_name = $category['category'];
                        if ($category_name == "") {
                            $category_name = substr(strstr($category_name," "), 1); //odstranimo prvo besedo (Winner ali Nomenee)
                        }
                        $category_id = $this->Category_model->get_category_id($category_name);
                        foreach ($category['names'] as $name) {
                            $person_name = $name['name'];
                            $person_imdb_id = $name['id'];

                            $person_db_id = $this->Person_model->get_person_by_imdb_id($person_imdb_id);
                            $this->Person_model->update_person(['FULL_NAME' => $person_name], $person_db_id);

                            $this->Award_model->set_received_award([
                                'CATEGORY_ID' => $category_id,
                                'AWARD_TYPE_ID' => $award_id,
                                'YEAR' => $year
                            ]);

                            if (strpos($category_name, "actor") !== false ||
                                strpos($category_name, "actress") !== false ||
                                strpos($category_name, "Actor") !== false ||
                                strpos($category_name, "Actress") !== false ||
                                strpos($category_name, " acti") !== false ||
                                strpos($category_name, " Acti") !== false
                            ) { //igralska vloga

                                $this->Award_model->set_actor_award([
                                    'MOVIE_ID' => $movie_id,
                                    'PERSON_ID' => $person_db_id,
                                    'CATEGORY_ID' => $category_id,
                                    'AWARD_TYPE_ID' => $award_id,
                                    'YEAR' => $year,
                                    'WINNER' => $outcome
                                ]);

                            } elseif (strpos($category_name, "direct") !== false ||
                                strpos($category_name, "Direct") !== false
                            ) { //režiser
                                $this->Award_model->set_director_award([
                                    'MOVIE_ID' => $movie_id,
                                    'PERSON_ID' => $person_db_id,
                                    'CATEGORY_ID' => $category_id,
                                    'AWARD_TYPE_ID' => $award_id,
                                    'YEAR' => $year,
                                    'WINNER' => $outcome
                                ]);

                            } elseif (strpos($category_name, "writ") !== false ||
                                strpos($category_name, "Writ") !== false
                            ) { //scenarij
                                $this->Award_model->set_writer_award([
                                    'MOVIE_ID' => $movie_id,
                                    'PERSON_ID' => $person_db_id,
                                    'CATEGORY_ID' => $category_id,
                                    'AWARD_TYPE_ID' => $award_id,
                                    'YEAR' => $year,
                                    'WINNER' => $outcome
                                ]);

                            } else { //ostalo
                                $this->Award_model->set_movie_award([
                                    'MOVIE_ID' => $movie_id,
                                    'CATEGORY_ID' => $category_id,
                                    'AWARD_TYPE_ID' => $award_id,
                                    'YEAR' => $year,
                                    'WINNER' => $outcome
                                ]);
                            }


                        }
                    }
                }
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['SUCESS' => false]);
        } else {
            $this->db->trans_commit();
            echo json_encode(['SUCESS' => true]);
        }





        // echo json_encode($dto);

        // var_dump($dto);

        // echo $this->MovieDTO->update($dto);
    }

}