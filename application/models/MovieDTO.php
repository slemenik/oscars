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

    function update($dto, $movie_id) {
        $this->db->where('MOVIE_ID', $movie_id);
        $this->db->update('movie', $dto);
        return $this->db->affected_rows() == 1 ? true : false;
    }

    function get_movie_id($imdb_id){

        $this->db->select('MOVIE_ID');
        $this->db->from('movie');
        $this->db->where('IMDB_ID', $imdb_id);
        return $this->db->get()->row_array()['MOVIE_ID'];

    }

    public function update_group($group_id, $project_id)
    {
        $this->db   ->set('Id_skupina', $group_id)
            ->where('Id_projekta',$project_id)
            ->update('projekt');
    }

    public function update_project($project_data)
    {
        $this->db->where('Id_projekta', $project_data['Id_projekta']);
        $this->db->update('projekt', $project_data);
        return $this->db->affected_rows() == 1 ? true : false;
    }




}