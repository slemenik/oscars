<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller{

    public function __construct()
    {
        parent::__construct();

    }

    private function var_dump($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    public function get_nomenees_per_year($year = 1979) {
        $json = file_get_contents("https://api.wolframalpha.com/v2/query" .
            "?input=Oscar+nominations+$year" .
            "&format=plaintext" .
            "&output=JSON" .
            "&appid=8V8KKQ-L2VKTA87YH" .
            "&outpust=JSON" .
            "&podstate=10@Result__More");
//        var_dump($json);
        $json_object = json_decode($json);

//        $this->var_dump($json_object);
        $pods = $json_object->queryresult->pods;
        $result_index = array_search('Academy Award winners and nominees', array_column($pods, 'title'));
        $nominations = $pods [$result_index]->subpods;

        $array = array();
        foreach ($nominations as $nomination){
            $category = $nomination->title;
            $plaintext = $nomination->plaintext;
//            $this->var_dump($plaintext);
            if (strpos($plaintext, "\n") === false) { //ni nominarancev, samo zmagovalci
                $losers = array();
                $nomineesArray = array($plaintext);
            } else { //vsaj en nominiranec
                $nomineesArray = explode('\n', json_encode($plaintext, JSON_UNESCAPED_UNICODE ));//todo pogle kako je z bazo
            }
//            $this->var_dump($nomineesArray);

            foreach($nomineesArray as $nomineeColumnKey => $nomineeColumn) {
                $nomineeColumnArray = explode('|', trim($nomineeColumn));
                if (count($nomineeColumnArray) <= 1) {
                    continue; //najbrž ni stolpec, ki bi sploh povedal kaj konkretnega
                }
                $nominee = str_replace("\"", "", trim($nomineeColumnArray[1]) ) ;

                $indexOfFor = strpos($nominee, " for ");
                $indexOfIn = strpos($nominee, " in ");
                if (!$indexOfFor && !$indexOfIn) {
                    //glavni film? oz oseba
                    $persons_array = array();
                    $movie = $nominee;

                } elseif ($indexOfIn) {
                    $persons = trim(substr($nominee, 0, $indexOfIn));
                    $indexOfFor2 = strpos($persons, " for ");
                    if ($indexOfFor2) {
                        $persons = substr($persons, 0, $indexOfFor-1);
                    }

                    $persons_array = explode(', ', str_replace(' and ', ', ', $persons));
                    $movie = substr($nominee, $indexOfIn + strlen(" in "));



                } elseif ($indexOfFor) {
                    $persons = trim(substr($nominee, 0, $indexOfFor));
                    $persons_array = explode(', ', str_replace(' and ', ', ', $persons));
                    $movie = substr($nominee, $indexOfFor + strlen(" for "));

                    $indexOfIn2 = strpos($movie, " in "); //narejeno pri pesmih in podobno, da se loči film
                    if ($indexOfIn2) {
                        $movie = substr($movie, $indexOfIn2 + strlen(" in "));
                    }


                } else {
                    $this->var_dump($indexOfIn);
                    $this->var_dump($indexOfFor);
                    return;

                }

                //združi besedo 'Jr.' z ustrezno osebo
                foreach ($persons_array as $key2 => $person) {
                    if ($person === "Jr.") {
                        $persons_array[$key2-1] = $persons_array[$key2-1] . ", Jr.";
                        unset($persons_array[$key2]);
                    }

                }

                //todo ostrani (produced by v filmu

                $type = count($nomineesArray) === 1 || $nomineeColumnKey === 0 ? 'winner' : 'loser';
                $array[$category][$nomineeColumnKey] = array(
                   // "Aoriginal" => $nominee,
                    "status" => $type,
                    "persons" => $persons_array,
                    "movie" => trim($movie),

                    "year" => $year-1
                );
            }

        }

//         $this->var_dump($array);
        header('Content-Type: application/json');
        echo json_encode($array);
    }

    private function get_persons_array_from_string($persons) {
        $persons_array = explode(', ', str_replace($persons, ' and ', ', '));

    }

    public function test($title, $year) {
        $imdb_id = $this->get_imdb_id($title, $year);
        $this->get_movie_data($imdb_id);
    }

//    public function get_imdb_id($title, $year) {
//
//        $json = file_get_contents("http://www.omdbapi.com/?" .
//            "&apikey=e0ce7de6" .
//            "&t=$title" .
//            "&y=$year");
//        $json_object = json_decode($json, true);
//        echo json_encode($json_object['imdbID']);
//    }

    public function get_movie_data($imdb_id = 'tt0499549') {
        ini_set('max_execution_time', 0);
        $json = file_get_contents("http://api.myapifilms.com/imdb/idIMDB?" .
            "idIMDB=$imdb_id" .
            "&token=0f8e7753-a2d2-44eb-988b-afac4b7b0203" .
            "&format=json" .
            "&awards=1");
            //.            "&actorActress=0&actorTrivia=0&similarMovies=0&goofs=0&keyword=0&quotes=0&fullSize=0&companyCredits=0&filmingLocations=0

        $json_object = json_decode($json, true);
        echo json_encode($json_object);
    }

    public function index(){
//        header('Content-Type: text/plain');//temp
        $data['title_variable'] = "Main";
        $this->load->view('header_view', $data);
        //$this->load->view('main_menu_view', $data);
        $this->load->view('home_view', $data);


        $this->load->view('footer_view');
    }




}