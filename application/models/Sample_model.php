<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sample_model extends CI_Model
{

    public function sample_model_function($parameter)
    {
        //connect to databse
        $query = $this->db->query('SELECT * FROM uporabnik');
        echo $parameter . "<br>";
        return 'Total Results: ' . $query->num_rows();
    }
}