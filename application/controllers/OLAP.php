<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OLAP extends CI_Controller
{

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
        INSERT INTO ipi_olap.dim_screenwriter(SCREENWRITER_ID, SCREENWRITER_ID_FULL_NAME, SCREENWRITER_ID_BIRTHDAY, SCREENWRITER_ID_GENDER, SCREENWRITER_ID_IMDB_ID )
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

    private $initalLoadMovieSql = "
        INSERT INTO ipi_olap.dim_movie(MOVIE_ID, TITLE, BOX_OFFICE, BUDGET, RELEASE_DATE, LENGTH)
        SELECT NULL, TITLE, BOX_OFFICE, BUDGET, RELEASE_DATE, LENGTH
        FROM ipi.movie;
    ";

    private $initalLoadRatingSql = "
        INSERT INTO ipi_olap.dim_rating(RATING_ID, SOURCE, SCORE)
        SELECT NULL, TITLE, SOURCE, IF (SOURCE = 'metascore', SCORE, FLOOR(CAST(SCORE as DECIMAL(9,2)) * 10)) AS SCORE
        FROM ipi.rating;
    ";

    private $initalLoadCompanySql = "
        INSERT INTO ipi_olap.dim_company(COMPANY_ID, COMPANY_NAME, CLASIFICATION)
        SELECT NULL, COMPANY_NAME, CLASIFICATION
        FROM ipi.company;
        ";

    private $initialLoadCategorySql = "
        INSERT INTO ipi_olap.dim_category(CATEGORY_ID, CATEGORY_NAME, PERSON_BASED, CATEGORY_SIGNIFICANCE) 
        SELECT NULL, CATEGORY_NAME, 
          IF (`EXISTS`(SELECT 1 
                        FROM ipi.movie_award MA 
                        WHERE  MA.CATEGORY_ID = C.CATEGORY_ID), 0, 1),
          NULL 
        FROM ipi.category C
    ";//TODO








    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', 0);
//        $this->db->db_debug = FALSE;
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

    public function testDB()
    {
        // $this->db->where('Id_projekta', $id_project);
//        var_dump($this->db->query("
//        SELECT * FROM
//        ipi_olap.dim_movie;
//        ")->result());

        $this->db->simple_query($this->initalLoadTimeSql);
    }

}