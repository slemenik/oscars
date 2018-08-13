<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Group_model extends CI_Model
{
    public function get_groups_data($id = null)
    {
        $this->db->select('Id_skupina, Ime_skupine');
        $this->db->from('razvojna_skupina');
        if ($id) $this->db->where('Id_skupina', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_group_data($id)
    {
        $this->db->select('uporabnik.Id_uporabnik, uporabnik.Ime, uporabnik.Priimek, uporabnik.Email, uporabnik_skupina.Activen, uporabnik_skupina.Datum_zacetka, uporabnik_skupina.Datum_konca');
        $this->db->from('uporabnik_skupina');
        $this->db->join('uporabnik', 'uporabnik.Id_uporabnik = uporabnik_skupina.Id_uporabnik');
        $this->db->where('Id_skupina', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_groups_projects($id_group)
    {
        $this->db->select('Sifra_projekta, Naziv_projekta');
        $this->db->from('projekt');
        $this->db->where('Id_skupina', $id_group);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function add_group($name)
    {
        $data = array(
            'Ime_skupine' => $name
        );
        $this->db->insert('razvojna_skupina', $data);
        return $this->db->insert_id();
    }

    public function get_roles_distribution_in_group($id_group)
    {
        $this->db->select('Id_vloga, COUNT(Id_uporabnik) AS counter');
        $this->db->from('konkretna_vloga');
        $this->db->where('Id_skupina', $id_group);
        $this->db->group_by("Id_vloga");
        $this->db->order_by("Id_vloga", "ASC");
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_user_in_group($id_group, $id_user)
    {
        $this->db->select('Id_skupina, Id_uporabnik, Activen, Datum_zacetka, Datum_konca');
        $this->db->from('uporabnik_skupina');
        $this->db->where('Id_skupina', $id_group);
        $this->db->where('Id_uporabnik', $id_user);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function add_user_to_group($id_group, $user)
    {
        $data_member = array(
            'Id_skupina' => $id_group,
            'Id_uporabnik' => $user['Id_uporabnik'],
            'Activen' => '1',
            'Datum_zacetka' => date("Y-m-d H:i:s")
        );
        $this->db->insert('uporabnik_skupina', $data_member);

    }

    public function reactivate_user_in_group($id_group, $id_user)
    {
        $this->db->set('Activen', '1');
        $this->db->set('Datum_konca', null);
        $this->db->where('Id_skupina', $id_group);
        $this->db->where('Id_uporabnik', $id_user);
        $this->db->update('uporabnik_skupina');
    }

    public function deactivate_user_in_group($id_group, $id_user)
    {
        $this->db->set('Activen', '0');
        $this->db->set('Datum_konca', date("Y-m-d H:i:s"));
        $this->db->where('Id_skupina', $id_group);
        $this->db->where('Id_uporabnik', $id_user);
        $this->db->update('uporabnik_skupina');
    }

    public function set_users_roles_in_group($id_group, $user)
    {
        $roles = $user['Konkretne_vloge'];
        foreach ($roles as $role) {
            $data_roles = array(
                'Id_skupina' => $id_group,
                'Id_uporabnik' => $user['Id_uporabnik'],
                'Id_vloga' => $role
            );
            $this->db->insert('konkretna_vloga', $data_roles);
        }
    }

    public function remove_users_roles_in_group($id_group, $id_user)
    {
        $this->db->where('Id_skupina', $id_group);
        $this->db->where('Id_uporabnik', $id_user);
        $this->db->delete('konkretna_vloga');
    }

    public function rename_group($id_group, $name)
    {
        $this->db->set('Ime_skupine', $name);
        $this->db->where('Id_skupina', $id_group);
        $this->db->update('razvojna_skupina');
    }

    public function remove_group($id_group)
    {
        // check if group is assigned to any project
        $this->db->where('Id_skupina', $id_group);
        $this->db->from('projekt');
        if ($this->db->count_all_results() > 0)
            return false;

        // delete first from table concrete roles
        $this->db->where('Id_skupina', $id_group);
        $this->db->delete('konkretna_vloga');
        // remove users from group
        $this->db->where('Id_skupina', $id_group);
        $this->db->delete('uporabnik_skupina');
        // delete group
        $this->db->where('Id_skupina', $id_group);
        $this->db->delete('razvojna_skupina');

        return true;
    }
}