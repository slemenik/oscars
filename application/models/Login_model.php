<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Login_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    private function isUserLocked($email)
    {
        $sql = "SELECT St_poskus_prijave, Datum_poskusa_prijave FROM uporabnik WHERE Email = ?";
        $last_login = $this->db->query($sql, array($email));


        if (!empty($last_login))
        {
            $data = $last_login->result_array()[0];
            $timepassed = 0;
            $wrongLoginTries = 0;
            if (isset($data['St_poskus_prijave']))
                $wrongLoginTries = (int)$data['St_poskus_prijave'];
            if (isset($data['Datum_poskusa_prijave']))
                $timepassed = time() - strtotime($data['Datum_poskusa_prijave']);

            if ($wrongLoginTries > 2 && $timepassed < (15))//v sekundah
            {
                return true;
            }
        }

        return false;
    }

    private function removeUnsuccessfulLoginTries($mail)
    {
        $sql = "UPDATE uporabnik
                SET St_poskus_prijave = 0, Datum_poskusa_prijave = ?
                WHERE Email = ?";
        $this->db->query($sql, array(date("Y-m-d H:i:s"), $mail));

    }

    public function run($login)
    {
        try
        {
            if(strlen($login['mail']) > 0 && strlen($login['password']) > 0)
            {
                $mail = $login['mail'];

                $pwd = hash('sha512', $login['password']);

                $sql = "SELECT Id_uporabnik, Ime, Priimek, Aktiven, Email, Admin  FROM uporabnik
                        WHERE Email = ? AND Geslo = ?";
                $result = $this->db->query($sql, array($mail, $pwd));


                if($result->num_rows() == 1)
                {

                    $data = $result->result_array()[0];
                    //Chck if user is unactive
                    if ($data['Aktiven'] == 0){
                        return array(
                            'type' => INFO_MESSAGE,
                            'text' => 'Uporabnik je neaktiven!'
                        );
                    }

                    //Check if user is locked
                    if($this->isUserLocked($mail))
                    {
                        return array(
                            'type' => WARNING_MESSAGE,
                            'text' => 'Uporabnik je zaklenjen!'
                        );
                    }

                    //login & remove unsuccessful log ins
                    $this->removeUnsuccessfulLoginTries($mail);

                    $_SESSION['user'] = $data;
                    $this->load->model('Role_model');
                    $DBroles = $this->Role_model->get_potential_roles($data['Id_uporabnik']);
                    $roles = array();
                    foreach ($DBroles as $role){
                        array_push($roles, $role['Id_vloga']);
                    }
                    $_SESSION['user']['roles'] = $roles;
                    return array(
                        'type' => SUCCESS_MESSAGE
                    );
                }
                else
                {



                    $sql =  "SELECT * FROM uporabnik
                             WHERE Email = ?
                             AND Aktiven = 1";
                    $result = $this->db->query($sql, array($mail));

                    $userId = null;
                    $no_logs = 1;
                    if($result->num_rows() == 1)
                    {
                        $data = $result->result_array()[0];

                        //Check if user is locked
                        if($this->isUserLocked($mail))
                        {
                            //$_SESSION['loginError'] = "User is locked!";
                            //redirect('/login', 'refresh');
                            return array(
                                'type' => WARNING_MESSAGE,
                                'text' => 'Uporabnik je zaklenjen!'
                            );
                        }
                        elseif ((isset($data['St_poskus_prijave']) && $data['St_poskus_prijave'] >= 3)){
                            //ni lockan in ima hkrati 3x napacni dostop
                            $this->removeUnsuccessfulLoginTries($mail);
                            $data['St_poskus_prijave'] = 0;
                        }


                        $userId = $data['Id_uporabnik'];
                        if (isset($data['St_poskus_prijave'])){
                            $no_logs += (int)$data['St_poskus_prijave'];
                            if ($no_logs >= 3) $no_logs = 3;
                        }


                        $sql = "UPDATE uporabnik
                            SET St_poskus_prijave = ?, Datum_poskusa_prijave = ?
                            WHERE Id_uporabnik = ?";
                        $this->db->query($sql, array($no_logs, date("Y-m-d H:i:s"), $userId));
                    }



                    //$_SESSION['loginError'] = "Wrong email or password.";
                }
            }

            //redirect('/login', 'refresh');
            return array(
                'type' => DANGER_MESSAGE,
                'text' => 'Napačno uporabniško ime ali geslo.'
            );
        }catch(Exception $ex)
        {
            //$_SESSION['loginError'] = "error sql";//$ex->getMessage();
            //redirect('/login', 'refresh');
            return array(
                'type' => DANGER_MESSAGE,
                'text' => 'Napaka v bazi.'
            );
        }
    }
}