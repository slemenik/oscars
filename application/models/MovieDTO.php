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




}