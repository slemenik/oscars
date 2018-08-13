<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{

    public function get_project_info($id_project = null)
    {
        if ($id_project) $this->db->where('Id_projekta', $id_project);
        return $this->db->get('projekt')->result_array();
    }

    public function create_project($data)
    {
        $this->db->insert('projekt', $data);
        return $this->db->insert_id();
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

    public function get_startdate_when_card_exists($project_id)
    {
        //projekt se je zacel, ce obstaja vsaj ena kartica
        return $this->db   ->select('projekt.Datum_zacetka')
                                ->from(array('kartica', 'stolpec','projekt'))
                                ->where('projekt.Id_table', 'stolpec.Id_table', false)
                                ->where('stolpec.Id_stolpec', 'kartica.Id_stolpec', false)
                                ->where('projekt.Id_projekta', $project_id)->get()->row();
    }

    public function delete_project($project_id)
    {
        $this->db->where('Id_projekta', $project_id)->delete('projekt');
        return $this->db->affected_rows() == 1 ? true : false;
    }

    public function deactivate_project($project_id)
    {
        $this->db->where('Id_projekta', $project_id);
        return $this->db->update('projekt', array('deaktiviran' => 1));
    }

    public function is_deactivated($id_project)
    {
        $project_info = $this->get_project_info($id_project);
        $deactivated = boolval($project_info[0]['deaktiviran']);
        return $deactivated;
    }
}