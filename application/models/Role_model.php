<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model{

    public function __construct()
    {
        parent::__construct();
        $this->db->db_debug = FALSE;//temp
    }

    public function get_all_role_names()
    {
        return $this->db->get('vloga')->result_array();
    }

    public function get_role_name($id)
    {
        $this->db->where('Id_vloga', $id);
        return $this->db->get('vloga');
    }

    public function get_potential_roles($id_user)
    {
        return $this->db->query('
                  SELECT vloga.Id_vloga, vloga.Naziv
                  FROM vloga, mozna_vloga 
                  WHERE vloga.Id_vloga = mozna_vloga.Id_vloga 
                  AND mozna_vloga.Id_uporabnik = ?', array($id_user))->result_array();
    }

    public function add_potential_roles($id_user, $roles)
    {
        $data = array();
        foreach ($roles as $role){
            $row = array(
                'Id_uporabnik' => $id_user ,
                'Id_vloga' => $role
            );
            array_push($data, $row);
        }
        return $this->db->insert_batch('mozna_vloga', $data);
    }

    public function change_potential_roles($id_user,$roles)
    {
        $this->db->trans_begin();
        $this->db                       ->where('Id_uporabnik', $id_user);
        if (!empty($roles)) {$this->db  ->where_not_in('Id_vloga', $roles);}
        $this->db                       ->delete('mozna_vloga');
        switch ($this->db->error()['code']){
            case 0:
                break;
            default:
                $this->db->trans_rollback();
                return false;
        }
        foreach ($roles as $role){
            $this->db->query('INSERT IGNORE INTO mozna_vloga VALUES(?,?)', array($role, $id_user));
        }
        switch ($this->db->error()['code']){
            case 0:
                $this->db->trans_commit();
                return true;
            default:
                $this->db->trans_rollback();
                return false;
        }
        /*if ($this->db->trans_status() == true){
            $this->db->trans_commit();
            return true;
        } else {
            $this->db->trans_rollback();
            return false;
        }*/
    }

    public function user_has_concrete_roles($id_user)
    {
        return $this->db->where('Id_uporabnik', $id_user)->get('konkretna_vloga')->num_rows() > 0;
    }

    public function get_concrete_roles($id_user, $id_group)
    {
        $this->db->select('vloga.Id_vloga, vloga.Naziv');
        $this->db->from('konkretna_vloga');
        $this->db->join('vloga', 'konkretna_vloga.Id_vloga = vloga.Id_vloga');
        $this->db->where('Id_uporabnik', $id_user);
        $this->db->where('Id_skupina', $id_group);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function add_concrete_roles($id_user, $id_group, $roles)
    {
        $data = array();
        foreach ($roles as $role){
            $row = array(
                'Id_uporabnik' => $id_user ,
                'Id_skupina' => $id_group,
                'Id_vloga' => $role
            );

            array_push($data, $row);
        }
        return $this->db->insert_batch('konkretna_vloga', $data);
    }
}