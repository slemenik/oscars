<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller{

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){
        header('Content-Type: text/plain');//temp
        $data['title_variable'] = "Main";
        $this->load->view('header_view', $data);
        //$this->load->view('main_menu_view', $data);
        // $this->load->view('home_view', $data);
        $json = file_get_contents("https://api.wolframalpha.com/v2/query" .
            "?input=Oscar+nominations+1988" .
            "&format=plaintext" .
            "&output=JSON" .
            "&appid=8V8KKQ-L2VKTA87YH" .
            "&outpust=JSON" .
            "&podstate=10@Result__More");
//        var_dump($json);
        $json_object = json_decode($json);

        $pods = $json_object->queryresult->pods;
        $result_index = array_search('Academy Award winners and nominees', array_column($pods, 'title'));
        $nominations = $pods [$result_index]->subpods;

        foreach ($nominations as $nomination){
            $category = $nomination->title;
            $plaintext = $nomination->plaintext;
            $nomineesArray = explode('\n', json_encode($plaintext));
            $winner = explode('|', $nomineesArray[0])[1];
            var_dump($winner);
        }
//        $states = $nominations->states;
//        Result__More







        $this->load->view('footer_view');
    }




}