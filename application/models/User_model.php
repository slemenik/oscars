<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{

    public function get_public_user_data($id = null)
    {
        $this->db->select('Id_uporabnik, Ime,Priimek,Aktiven,Email,Admin');
        if ($id) $this->db->where('Id_uporabnik', $id);
        $query = $this->db->get('uporabnik');
        return $query->result_array();
    }

    public function set_activation_param($id, $is_active_int){
        if ($this->Role_model->user_has_concrete_roles($id)) return false;
        $this->db->where('Id_uporabnik', $id);
        return $this->db->update('uporabnik', array('Aktiven' => $is_active_int));
    }

    public function update_user($user_data){
        $this->db->where('Id_uporabnik', $user_data['Id_uporabnik']);
        return $this->db->update('uporabnik', $user_data);
    }

    public function create_user($user_data){
        $this->db->insert('uporabnik', $user_data);
        return $this->db->insert_id();
    }

    public function get_users_with_role($id_role)
    {
        $this->db->select('uporabnik.Id_uporabnik, uporabnik.Ime, uporabnik.Priimek, uporabnik.Email');
        $this->db->from('uporabnik');
        $this->db->join('mozna_vloga', 'uporabnik.Id_uporabnik = mozna_vloga.Id_uporabnik');
        $this->db->where('mozna_vloga.Id_vloga', $id_role);
        $query = $this->db->get();
        return $query->result_array();
    }

}