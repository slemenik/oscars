<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Card_model extends CI_Model
{
    public function get_cards($id_project){
        $this->db->where('Id_projekta', $id_project);
        return $this->db->get('projekt')->result_array();
    }
}