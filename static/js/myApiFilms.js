

function getClockString() {
    var d = new Date(); // for now
    return d.getHours() +":" +
    d.getMinutes() +":" + // =>  30
    d.getSeconds();

}

function get_movie_data(imdbID, counter) {
    console.log(getClockString() + " js get_movie_data(), imdbID " + imdbID + ", counter: " + counter);
    if (playValue == false) {
        return;
    }
    $.get(
        "main/get_movie_data/" + imdbID,
        // {
        //     idIMDB : imdbID,
        //     token : '0f8e7753-a2d2-44eb-988b-afac4b7b0203',
        //     awards : 1,
        //     format : 'json'
        // },
        function(data) {
            console.log(getClockString() + " success get_movie_data(), imdbID " + imdbID + ", counter: " + counter);
            if (data.data === undefined) {
                //ponoven klic
                console.log(getClockString() + " data je undefined, ponoven klic");
                get_movie_data(imdbID, counter);
                return;
            }
            var movieData = data.data.movies[0];
            counter++;
            updateMovie(movieData);
            if (counter<imdbIDs.length) {
                get_movie_data(imdbIDs[counter].IMDB_ID, counter)
            } else {
                console.log("end get_movie_data()")
            }

        }
    )  .done(function() {
        // console.log(123);
    })
        .fail(function() {
            console.log(getClockString() + " fail, ponoven klic");
            get_movie_data(imdbID, counter);
        });
}

function error_handling() {
    imdbIDs = problematicne;
    get_movie_data(imdbIDs[0].IMDB_ID, 0); //poglej prvi vnos
}

var problematicne = [{IMDB_ID: 'tt0026752'}, {IMDB_ID: 'tt0026643'}, {IMDB_ID: 'tt0026955'}, {IMDB_ID: 'tt0026143'}];

var imdbIDs= [];

function get_undefined_ids() {
    $.get(
        "DBcontroller/get_undefined_imdb_ids",
        // {
        //     idIMDB : imdbID,
        //     token : '0f8e7753-a2d2-44eb-988b-afac4b7b0203',
        //     awards : 1,
        //     format : 'json'
        // },
        function(data) {

            imdbIDs = jQuery.parseJSON(data);
            console.log(getClockString() + " dobil id-je, velikost: " + imdbIDs.length);
            // console.log(imdbIDs);
            get_movie_data(imdbIDs[0].IMDB_ID, 0); //poglej prvi vnos, 142. vnos, prejšnje si že
        }
    );
}

function updateMovie(movieData) {

    if (playValue == false) {
        return;
    }
    console.log(getClockString() + " js updateMovie()");
    // var releaseDate = movieData.releaseDate.substr(0,4)
    //     + "-" + movieData.releaseDate.substr(4,2)
    //     + "-" + movieData.releaseDate.substr(6,2);
    //
    // var lengthMin = movieData.runtime.split(" ")[0];
    // var hours = Math.floor( lengthMin / 60);
    // var minutes = lengthMin % 60;
    // var length = "0" + hours + ":" + minutes + ":00";
    //
    // var budget = business.budget.substr(1).replace(",","");
    // var box_office = business.worldwide.substr(1).replace(",","");


    // console.log(movieData);
    $.ajax({
        url:"DBcontroller/update_movie",
        type:"POST",
        data: JSON.stringify(movieData),
        contentType:"application/json; charset=utf-8",
        // dataType:"json",
        success: function(data){
            console.log(getClockString() + " success js updateMovie()");
            console.log(data);
        },
        error: function (data) {
            console.log(getClockString() + " error js funkcije updateMovie()");
            console.log(data.responseText);
        }
    })

    // $.post(
    //     "DBcontroller/update_movie",
    //     {
    //         // TITLE: movieData.title,
    //         // PART: null,
    //         // BOX_OFFICE: box_office,
    //         // BUDGET: budget,
    //         // RELEASE_DATE: releaseDate,
    //         // LENGTH: length,
    //         // IMDB_ID : movieData.idIMDB
    //         movieData: movieData
    //
    //     },
    //     function(data) {
    //         console.log(data);
    //     },
    //     'json'
    // );
}