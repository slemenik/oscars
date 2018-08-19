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

    public function get_nomenees_per_year($year) {
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

//        var_export($nominations);
//The Man Who Planted Trees
        $array = array();
        foreach ($nominations as $nomination){
            $category = $nomination->title;
            $plaintext = $nomination->plaintext;
            $nomineesArray = explode('\n', json_encode($plaintext, JSON_UNESCAPED_UNICODE ));//todo pogle kako je z bazo

//            $this->var_dump($nomineesArray);

            $winnerColumn = explode('|', $nomineesArray[0]);
            if (count($winnerColumn) <= 1) {
                continue; //najbrž ni stolpec, ki bi sploh povedal kaj konkretnega
            }
            $winner = $winnerColumn[1];

            $indexOfFor = strpos($winner, " for ");
            $indexOfIn = strpos($winner, " in ");
            if (!$indexOfFor && !$indexOfIn) {
                //glavni film?
                $person = null;
                $movie = $winner;
            } elseif ($indexOfFor) {
                $person = substr($winner, 0, $indexOfFor);
                $movie = substr($winner, $indexOfFor + strlen(" for "));

                $indexOfIn = strpos($movie, " in "); //narejeno pri pesmih in podobno, da se loči film
                if ($indexOfIn) {
                    $movie = substr($movie, $indexOfIn + strlen(" in "));
                }

            } elseif ($indexOfIn) {
                $person = substr($winner, 0, $indexOfIn);
                $movie = substr($winner, $indexOfIn + strlen(" in "));
            } else {
                $this->var_dump($indexOfIn);
                $this->var_dump($indexOfFor);

            }


//            $array[$category] = $winner;
            $array[$category] = array("original" => $winner, "person" => $person, "movie" => $movie);


//            var_dump(json_encode($plaintext, JSON_UNESCAPED_SLASHES  ));
//            var_dump(json_encode($plaintext, JSON_UNESCAPED_UNICODE   ));
//            var_dump(json_encode($plaintext, JSON_UNESCAPED_LINE_TERMINATORS   ));
//            var_dump(json_decode($json, false, 512, JSON_UNESCAPED_SLASHES  ));
        }
//        $states = $nominations->states;
//        Result__More
         $this->var_dump($array);
//        header('Content-Type: application/json');
//        echo json_encode($array);
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