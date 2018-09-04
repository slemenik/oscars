
function get_movie_data(imdbID, counter) {
    console.log("get_movie_data() začetek, imdbID " + imdbID + ", counter: " + counter);
    $.get(
        "main/get_movie_data/" + imdbID,
        // {
        //     idIMDB : imdbID,
        //     token : '0f8e7753-a2d2-44eb-988b-afac4b7b0203',
        //     awards : 1,
        //     format : 'json'
        // },
        function(data) {
            var movieData = data.data.movies[0];
            console.log(movieData);
            counter++;
            if (counter<imdbIDs.length) {
                get_movie_data(imdbIDs[counter]['IMDB_ID'], counter)
            } else {
                console.log("end get_movie_data()")
            }

        }
    );
}

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
            console.log("dobil id-je")
            imdbIDs = data;
            get_movie_data(imdbIDs[0]['IMDB_ID'], 0);
        }
    );
}