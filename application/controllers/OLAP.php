<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OLAP extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', 0);
        $this->db->db_debug = true;
//        $this->load->model('MovieDTO');
//        $this->load->model('Person_model');
//        $this->load->model('Genre_model');
//        $this->load->model('Rating_model');
//        $this->load->model('Company_model');
//        $this->load->model('Award_model');
//        $this->load->model('Category_model');
//        header('Content-Type: application/json');
        // print_r(php_ini_loaded_file());
    }

    private $initialLoadSQL = "
        
    ";

    private $initalLoadActorSql = "
        INSERT INTO ipi_olap.dim_actor(ACTOR_ID, ACTOR_FULL_NAME, ACTOR_BIRTHDAY, ACTOR_GENDER, ACTOR_IMDB_ID )
        SELECT NULL, P.FULL_NAME, P.BIRTHDAY, P.GENDER, P.IMDB_ID
        FROM ipi.person P, ipi.actor A
        WHERE P.PERSON_ID = A.PERSON_ID;
    ";

    private $initalLoadDirectorSql = "
        INSERT INTO ipi_olap.dim_director(DIRECTOR_ID, DIRECTOR_FULL_NAME, DIRECTOR_BIRTHDAY, DIRECTOR_GENDER, DIRECTOR_IMDB_ID )
        SELECT NULL, P.FULL_NAME, P.BIRTHDAY, P.GENDER, P.IMDB_ID
        FROM ipi.person P, ipi.director D
        WHERE P.PERSON_ID = D.PERSON_ID;
    ";

    private $initalLoadWriterSql = "
        INSERT INTO ipi_olap.dim_screenwriter(SCREENWRITER_ID, SCREENWRITER_FULL_NAME, SCREENWRITER___BIRTHDAY, SCREENWRITER_GENDER, SCREENWRITER_IMDB_ID )
        SELECT NULL, P.FULL_NAME, P.BIRTHDAY, P.GENDER, P.IMDB_ID
        FROM ipi.person P, ipi.screenwriter S
        WHERE P.PERSON_ID = S.PERSON_ID;
    ";

    private $initalLoadGenreSql = "
        INSERT INTO ipi_olap.dim_genre(GENRE_ID, GENRE_NAME )
        SELECT NULL, GENRE_NAME
        FROM ipi.genre;
    ";

    private $initalLoadTimeSql = "
        INSERT INTO ipi_olap.dim_time (TIME_YEAR, TIME_MONTH, TIME_DECADE)
        SELECT DISTINCT ipi.received_award.YEAR, month.MONTH, decade.DECADE 
        FROM received_award, (
                                SELECT 1 MONTH
                                UNION ALL
                                SELECT 2 SeqValue
                                UNION ALL
                                SELECT 3 SeqValue
                                UNION ALL
                                SELECT 4 SeqValue
                                UNION ALL
                                SELECT 5 SeqValue
                                UNION ALL
                                SELECT 6 SeqValue
                                UNION ALL
                                SELECT 7 SeqValue
                                UNION ALL
                                SELECT 8 SeqValue
                                UNION ALL
                                SELECT 9 SeqValue
                                UNION ALL
                                SELECT 10 SeqValue
                                UNION ALL
                                SELECT 11 SeqValue
                                UNION ALL
                                SELECT 12 SeqValue
                            ) as month,
                            (
                                SELECT 0 DECADE
                                UNION ALL
                                SELECT 10 SeqValue
                                UNION ALL
                                SELECT 20 SeqValue
                                UNION ALL
                                SELECT 30 SeqValue
                                UNION ALL
                                SELECT 40 SeqValue
                                UNION ALL
                                SELECT 50 SeqValue
                                UNION ALL
                                SELECT 60 SeqValue
                                UNION ALL
                                SELECT 70 SeqValue
                                UNION ALL
                                SELECT 80 SeqValue
                                UNION ALL
                                SELECT 90 SeqValue
                            ) as decade;
    ";

    // vnese vse nagrajene ali nominirane filme
    private $initalLoadMovieSql = "
        INSERT INTO ipi_olap.dim_movie(MOVIE_ID, TITLE, BOX_OFFICE, BUDGET, RELEASE_DATE, LENGTH)
        SELECT NULL, TITLE, BOX_OFFICE, BUDGET, RELEASE_DATE, LENGTH
        FROM ipi.movie M
        WHERE exists(SELECT 1 FROM ipi.movie_award MA WHERE MA.MOVIE_ID = M.MOVIE_ID)
        OR EXISTS(SELECT 1 FROM ipi.actor_reward AR WHERE AR.MOVIE_ID = M.MOVIE_ID)
        OR EXISTS(SELECT 1 FROM ipi.screenwriter_award SA WHERE SA.MOVIE_ID = M.MOVIE_ID)
        OR EXISTS(SELECT 1 FROM ipi.director_award DA WHERE DA.MOVIE_ID = M.MOVIE_ID);
    ";

    private $initalLoadRatingSql = "
        INSERT INTO ipi_olap.dim_rating(RATING_ID, SOURCE, SCORE)
        SELECT DISTINCT NULL, SOURCE, IF (SOURCE = 'metascore', SCORE, FLOOR(CAST(SCORE as DECIMAL(9,2)) * 10)) AS SCORE
        FROM ipi.rating;
    ";

    private $initalLoadCompanySql = "
        INSERT INTO ipi_olap.dim_company(COMPANY_ID, COMPANY_NAME, CLASIFICATION)
        SELECT DISTINCT NULL, COMPANY_NAME, CLASIFICATION
        FROM ipi.company;
        ";

    private $initialLoadCategorySql = "
        INSERT INTO ipi_olap.dim_category(CATEGORY_ID, CATEGORY_NAME, PERSON_BASED, CATEGORY_SIGNIFICANCE) 
        SELECT DISTINCT NULL, CATEGORY_NAME, 
          IF (`EXISTS`(SELECT 1 
                        FROM ipi.movie_award MA 
                        WHERE  MA.CATEGORY_ID = C.CATEGORY_ID), 0, 1),
          (CASE WHEN (
            CATEGORY_NAME LIKE '%direc%' OR
            CATEGORY_NAME LIKE '%Direc%' OR
            CATEGORY_NAME LIKE '%act%' OR
            CATEGORY_NAME LIKE '%Act%'
            
          ) THEN 2
          WHEN (CATEGORY_NAME = 'Best Picture') THEN 3
          ELSE 1 END)
        FROM ipi.category C;
    ";

    private $initialLoadAwardSql = "
        INSERT INTO ipi_olap.dim_other_award(AWARD_ID, CATEGORY_ID, AWARD_NAME, YEAR, AWARD_SIGNIFICANCE) 
        SELECT DISTINCT NULL, 
		(SELECT DC.CATEGORY_ID 
         FROM IPI_OLAP.DIM_CATEGORY DC 
         WHERE DC.CATEGORY_NAME = CA.CATEGORY_NAME
        ) ID, 
        A.AWARD_NAME, 
        RA.YEAR, 
        (CASE 
         WHEN (A.AWARD_NAME = 'Golden Globes, USA') 
         THEN 3 
         WHEN (A.AWARD_NAME = 'Writers Guild of America, USA' 
               OR A.AWARD_NAME = 'Directors Guild of America, US' 
               OR A.AWARD_NAME = 'Screen Actors Guild Awards' 
               OR A.AWARD_NAME = 'BAFTA Awards') 
         THEN 2 
         ELSE 1 END) POMEMBNOST
        FROM IPI.award_type A, IPI.received_award ra, IPI.category CA
        WHERE RA.AWARD_TYPE_ID = A.AWARD_TYPE_ID
        AND CA.CATEGORY_ID = RA.CATEGORY_ID
        AND AWARD_NAME <> 'Academy Awards, USA';
    ";


//SELECT *
//FROM movie m,
//director d,
//actor a,
//screenwriter s,
//genre g,
//company c,
//movie_genre mg,
//movie_company mc
//where c.COMPANY_ID = mc.COMPANY_ID
//and mc.MOVIE_ID = m.MOVIE_ID
//and g.GENRE_ID = mg.GENRE_ID
//and mg.MOVIE_ID = m.MOVIE_ID
//and s.MOVIE_ID = m.MOVIE_ID
//and a.MOVIE_ID = m.MOVIE_ID





    public function testDB()
    {
        // $this->db->where('Id_projekta', $id_project);
//        var_dump($this->db->query("
//        SELECT * FROM
//        ipi_olap.dim_movie;
//        ")->result());

        $this->db->simple_query($this->initalLoadTimeSql);
    }

    public function test() {

        $sql = "
        
        ";
        $query = $this->db->query($sql);

        echo $query->num_rows();
    }

    public function clean_award_type() {


        $this->db->trans_begin();

        $sql = "SELECT AWARD_NAME, 
                count(*) 
                FROM `award_type` 
                group by AWARD_NAME 
                having count(*) > 1";

        $query = $this->db->query($sql);

        foreach ($query->result_array() as $row) // vsi awardi nami ko so duplikati
        {


            $name = $this->db->escape($row['AWARD_NAME']);
            $sql2 = "SELECT AWARD_TYPE_ID 
                      from award_type
                      where AWARD_NAME = $name
                      ";


            $query2 = $this->db->query($sql2);

            $results = $query2->result_array(); //vsi id-ji ki pripadajo duplikatnemu awardu
            $new_id = $results[0]['AWARD_TYPE_ID']; //prvi izmed rezulattov, nima veze kateri je

            foreach ($results as $key=>$id_arr) {

                if ($key == 0) {
                    continue; //prvo rundo spustimo
                }

                $old_id = $id_arr['AWARD_TYPE_ID']; // 4927

/////////////////////////////////////screenwriter_award/////////////////////////////////////////////////

                $sql4 = "INSERT IGNORE INTO received_award(CATEGORY_ID, AWARD_TYPE_ID, YEAR) 
                      SELECT CATEGORY_ID, $new_id, YEAR
                      from  screenwriter_award
                      where AWARD_TYPE_ID = $old_id;
                      "; // če še ne obstaja kombinacija ključev jo naredimo
                $this->db->query($sql4);

                $sql3= "
                  UPDATE `screenwriter_award` 
                  SET `AWARD_TYPE_ID` = $new_id 
                  WHERE AWARD_TYPE_ID = $old_id;";
                $this->db->query($sql3);

/////////////////////////////////////director_award/////////////////////////////////////////////////
                $sql4 = "INSERT IGNORE INTO received_award(CATEGORY_ID, AWARD_TYPE_ID, YEAR) 
                      SELECT CATEGORY_ID, $new_id, YEAR
                      from  director_award
                      where AWARD_TYPE_ID = $old_id;
                      "; // če še ne obstaja kombinacija ključev jo naredimo
                $this->db->query($sql4);

                $sql3= "
                  UPDATE `director_award` 
                  SET `AWARD_TYPE_ID` = $new_id 
                  WHERE AWARD_TYPE_ID = $old_id;";
                $this->db->query($sql3);

/////////////////////////////////////actor_reward/////////////////////////////////////////////////

                $sql4 = "INSERT IGNORE INTO received_award(CATEGORY_ID, AWARD_TYPE_ID, YEAR) 
                      SELECT CATEGORY_ID, $new_id, YEAR
                      from  actor_reward
                      where AWARD_TYPE_ID = $old_id;
                      "; // če še ne obstaja kombinacija ključev jo naredimo
                $this->db->query($sql4);

                $sql3= "
                  UPDATE `actor_reward` 
                  SET `AWARD_TYPE_ID` = $new_id 
                  WHERE AWARD_TYPE_ID = $old_id;";
                $this->db->query($sql3);

/////////////////////////////////////movie_award/////////////////////////////////////////////////

                $sql4 = "INSERT IGNORE INTO received_award(CATEGORY_ID, AWARD_TYPE_ID, YEAR) 
                      SELECT CATEGORY_ID, $new_id, YEAR
                      from  movie_award
                      where AWARD_TYPE_ID = $old_id;
                      "; // če še ne obstaja kombinacija ključev jo naredimo
            $this->db->query($sql4);

                $sql3= "
                  UPDATE `movie_award` 
                  SET `AWARD_TYPE_ID` = $new_id 
                  WHERE AWARD_TYPE_ID = $old_id;";

                $this->db->query($sql3);

////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $sql3= "
                  DELETE FROM `received_award` 
                  WHERE AWARD_TYPE_ID = $old_id;";

                $this->db->query($sql3);

                $sql3= "
                  DELETE FROM `award_type` 
                  WHERE AWARD_TYPE_ID = $old_id;";

                $this->db->query($sql3);

            }


        }

        var_dump("konc");
        $this->db->trans_commit();
    }

public function clean_category() {


    $this->db->trans_begin();

    $sql = "SELECT CATEGORY_NAME, 
                count(*) 
                FROM `category` 
                group by CATEGORY_NAME 
                having count(*) > 1";

    $query = $this->db->query($sql);

    foreach ($query->result_array() as $row) // vsi category nami ko so duplikati
    {


        $name = $this->db->escape($row['CATEGORY_NAME']);
        $sql2 = "SELECT CATEGORY_ID 
                      from category
                      where CATEGORY_NAME = $name
                      ";


        $query2 = $this->db->query($sql2);

        $results = $query2->result_array(); //vsi id-ji ki pripadajo duplikatnemu awardu
        $new_id = $results[0]['CATEGORY_ID']; //prvi izmed rezulattov, nima veze kateri je


        foreach ($results as $key => $id_arr) {

            if ($key == 0) {
                continue; //prvo rundo spustimo
            }

//            var_dump($new_id);
            $old_id = $id_arr['CATEGORY_ID'];
//            var_dump(1);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $sql4 = "INSERT IGNORE INTO received_award(CATEGORY_ID, AWARD_TYPE_ID, YEAR) 
                      SELECT $new_id, AWARD_TYPE_ID, YEAR
                      from  screenwriter_award
                      where CATEGORY_ID = $old_id;
                      "; // če še ne obstaja kombinacija ključev jo naredimo
            $this->db->query($sql4);

            $sql3= "
                  UPDATE `screenwriter_award` 
                  SET `CATEGORY_ID` = $new_id 
                  WHERE CATEGORY_ID = $old_id;";
            $this->db->query($sql3);
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $sql4 = "INSERT IGNORE INTO received_award(CATEGORY_ID, AWARD_TYPE_ID, YEAR) 
                      SELECT $new_id, AWARD_TYPE_ID, YEAR
                      from  director_award
                      where CATEGORY_ID = $old_id;
                      "; // če še ne obstaja kombinacija ključev jo naredimo
            $this->db->query($sql4);

            $sql3= "
                  UPDATE `director_award` 
                  SET `CATEGORY_ID` = $new_id 
                  WHERE CATEGORY_ID = $old_id;";
            $this->db->query($sql3);
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $sql4 = "INSERT IGNORE INTO received_award(CATEGORY_ID, AWARD_TYPE_ID, YEAR) 
                      SELECT $new_id, AWARD_TYPE_ID, YEAR
                      from  actor_reward
                      where CATEGORY_ID = $old_id;
                      "; // če še ne obstaja kombinacija ključev jo naredimo
            $this->db->query($sql4);

            $sql5 = "SELECT *
                      FROM actor_reward
                      WHERE CATEGORY_ID = $old_id; 
             ";//pomoje da bo rezultat vedno samo en
            $temp_row = $this->db->query($sql5)->row_array();


            $duplikat = false;
            if ($temp_row !== null) {
                $sql6 = "SELECT *
                      FROM actor_reward
                      WHERE CATEGORY_ID = $new_id
                      and AWARD_TYPE_ID = {$temp_row['AWARD_TYPE_ID']}
                      and YEAR = {$temp_row['YEAR']}
                      and MOVIE_ID = {$temp_row['MOVIE_ID']}
                      and PERSON_ID = {$temp_row['PERSON_ID']}
                      ";

                if (!empty($this->db->query($sql6)->result_array()) ) {
                    //ni prazna, torej obstaja, torej bo duplikat, torej samo zbriši
                    $duplikat = true;
                }
            }

            if ($duplikat) {
                $sql3 = "DELETE from actor_reward
                          WHERE CATEGORY_ID = $old_id
                          and AWARD_TYPE_ID = {$temp_row['AWARD_TYPE_ID']}
                          and YEAR = {$temp_row['YEAR']}
                          and MOVIE_ID = {$temp_row['MOVIE_ID']}
                          and PERSON_ID = {$temp_row['PERSON_ID']}
                          ";
                var_dump("kuku");
            } else {
                $sql3 = "
                  UPDATE `actor_reward` 
                  SET `CATEGORY_ID` = $new_id 
                  WHERE CATEGORY_ID = $old_id;";
            }
            $this->db->query($sql3);
            ////////////////////////////////////////////////////////////////////////////////////////////////////
            $sql4 = "INSERT IGNORE INTO received_award(CATEGORY_ID, AWARD_TYPE_ID, YEAR) 
                      SELECT $new_id, AWARD_TYPE_ID, YEAR
                      from  movie_award
                      where CATEGORY_ID = $old_id;
                      "; // če še ne obstaja kombinacija ključev jo naredimo
            $this->db->query($sql4);


            $sql5 = "SELECT *
                      FROM movie_award
                      WHERE CATEGORY_ID = $old_id; 
             ";//pomoje da bo rezultat vedno samo en
            $temp_row = $this->db->query($sql5)->row_array();


            $duplikat = false;
            if ($temp_row !== null) {
                $sql6 = "SELECT *
                      FROM movie_award
                      WHERE CATEGORY_ID = $new_id
                      and AWARD_TYPE_ID = {$temp_row['AWARD_TYPE_ID']}
                      and YEAR = {$temp_row['YEAR']}
                      and MOVIE_ID = {$temp_row['MOVIE_ID']}
                      ";

                if (!empty($this->db->query($sql6)->result_array()) ) {
                    //ni prazna, torej obstaja, torej bo duplikat, torej samo zbriši
                    $duplikat = true;
                }
            }

            if ($duplikat) {
                $sql3 = "DELETE from movie_award
                          WHERE CATEGORY_ID = $old_id
                          and AWARD_TYPE_ID = {$temp_row['AWARD_TYPE_ID']}
                          and YEAR = {$temp_row['YEAR']}
                          and MOVIE_ID = {$temp_row['MOVIE_ID']}";
                var_dump("kuku");
            } else {
                $sql3= "
                  UPDATE `movie_award` 
                  SET `CATEGORY_ID` = $new_id 
                  WHERE CATEGORY_ID = $old_id;";
            }
            $this->db->query($sql3);

            ////////////////////////////////////////////////////////////////////////////////////////////////////

//            var_dump(5);
////           $sql3= "
//                  UPDATE `received_award`
//                  SET `CATEGORY_ID` = $new_id
//                  WHERE CATEGORY_ID = $old_id;";

             $sql3= "
                  DELETE FROM `received_award`                  
                  WHERE CATEGORY_ID = $old_id;";

            $this->db->query($sql3);
//            var_dump(6);
            $sql3= "
                  DELETE FROM `category` 
                  WHERE CATEGORY_ID = $old_id;";

            $this->db->query($sql3);
//            var_dump($this->db->last_query());
//            var_dump(7);


        }


    }

    var_dump("konc");
    $this->db->trans_commit();
}

    public function clean_company() {


        $this->db->trans_begin();

        $sql = "SELECT COMPANY_NAME, CLASIFICATION,
                count(*) 
                FROM `company` 
                group by COMPANY_NAME,CLASIFICATION
                having count(*) > 1";

        $query = $this->db->query($sql);

        foreach ($query->result_array() as $row) // vsi category nami ko so duplikati
        {


            $name = $this->db->escape($row['COMPANY_NAME']);
            $classification = $this->db->escape($row['CLASIFICATION']);
            $sql2 = "SELECT COMPANY_ID 
                      from company
                      where COMPANY_NAME = $name
                      and CLASIFICATION = $classification
                      ";


            $query2 = $this->db->query($sql2);

            $results = $query2->result_array(); //vsi id-ji ki pripadajo duplikatnemu awardu
            $new_id = $results[0]['COMPANY_ID']; //prvi izmed rezulattov, nima veze kateri je

            foreach ($results as $key=>$id_arr) {

                $old_id = $id_arr['COMPANY_ID'];

                if ($key == 0) {
                    continue; //prvo rundo spustimo
                }

                $sql5 = "SELECT *
                      FROM movie_company
                      WHERE COMPANY_ID = $old_id; 
                     ";//pomoje da bo rezultat vedno samo en
                $temp_row = $this->db->query($sql5)->row_array();

                $duplikat = false;
                if ($temp_row !== null) {
                    $sql6 = "SELECT *
                          FROM movie_company
                          WHERE COMPANY_ID = $new_id                         
                          and MOVIE_ID = {$temp_row['MOVIE_ID']}
                          ";

                    if (!empty($this->db->query($sql6)->result_array()) ) {
                        //ni prazna, torej obstaja, torej bo duplikat, torej samo zbriši
                        $duplikat = true;
                    }
                }

                if ($duplikat) {
                    $sql3 = "DELETE from movie_company
                              WHERE COMPANY_ID = $old_id                         
                              and MOVIE_ID = {$temp_row['MOVIE_ID']}";
//                    var_dump("kuku");
                } else {

                    $sql3 = "
                      UPDATE `movie_company` 
                      SET `COMPANY_ID` = $new_id 
                      WHERE COMPANY_ID = $old_id;";

                }
                $this->db->query($sql3);
//                 var_dump($this->db->last_query());


                $sql3= "
                  DELETE FROM `company`                  
                  WHERE COMPANY_ID = $old_id;";
                $this->db->query($sql3);

            }


        }

        var_dump("konc");
        $this->db->trans_commit();
    }

}