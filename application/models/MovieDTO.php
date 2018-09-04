<?php defined('BASEPATH') OR exit('No direct script access allowed');


class MovieDTO extends CI_Model
{
    public $MOVIE_ID;
    public $TITLE ;
    public $PART;
    public $BOX_OFFICE;
    public $BUDGET;
    public $RELEASE_DATE ;
    public $LENGTH ;

    function __construct()
    {
        parent::__construct();
    }

    function insert($dto) {
        $this->db->insert('movie', $dto);
        return $this->db->insert_id();
    }

    function get_undefined_imdb_ids(){

        $this->db->select('IMDB_ID');
        $this->db->from('movie');
        $this->db->where('TITLE IS NULL');
        return $this->db->get()->result_array();

    }




}