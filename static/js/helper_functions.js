
function getBaseUrl() {
    var getUrl = window.location;
    var baseUrl = getUrl .protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
    return baseUrl;
}

// console.log(123);


var categoriesSet = new Set();
var moviesHashMap = [];

function start () {
    console.log("start");
    get(1935); // leta pred tem so bili oskarji od avgusta do julija neslednje leto

}

function get(year){
    if (year <= 2018) {
        $.get(
            "main/get_nomenees_per_year/" + year,
            // {
            // input : 'Oscar nominations 1988',
            // format : 'plaintext',
            // appid : '8V8KKQ-L2VKTA87YH',
            // output : 'JSON'
            // },
            function(data) {

                // console.log(data);
                moviesHashMap[year] = [];
                var categories = Object.keys(data);
                for (var i = 0; i<categories.length;i++){
                    // categoriesSet.add(categories[i]);
                    var categoryName = categories[i];
                    var categoryNominees = data[categoryName];
                    // console.log(categoryNominees);
                    for (var j = 0; j<categoryNominees.length; j++) {
                        var movieName = categoryNominees[j].movie;

                        //na film smo naleteli prvič
                        if (typeof moviesHashMap[year][movieName] === 'undefined') {
                            moviesHashMap[year][movieName] = "random vrednost";
                            // console.log(year + ":"+categoryName + ":"+ movieName);
                            get_movie(movieName, year-1);
                        }
                    }

                }
                console.log('----');
                year++;
                get(year);



            }
        );
    } else {
        console.log("end");
        console.log(categoriesSet);
    }
}

function get_movie(title, year) {

    $.get(
        "http://www.omdbapi.com/",
        {
        t : encodeURIComponent(title),
        y : year,
        apikey : 'e0ce7de6'
        // output : 'JSON'
        },
        function(data) {
            console.log(year + ":"+ title);
            // var imdbID = data.imdbID;
            //prvi filmi so bili
            console.log(data);
            // get_movie_data(imdbID);
        }
    );
}

// function get_movie_data2(imdbID) {
//     $.get(
//         "http://api.myapifilms.com/imdb/idIMDB",
//         {
//             idIMDB : imdbID,
//             token : '0f8e7753-a2d2-44eb-988b-afac4b7b0203',
//             awards : 1,
//             format : 'json'
//         },
//         function(data) {
//             console.log(data);
//         }
//     );
// }

function get_movie_data(imdbID) {
    $.get(
        "main/get_movie_data/" + imdbID,
        // {
        //     idIMDB : imdbID,
        //     token : '0f8e7753-a2d2-44eb-988b-afac4b7b0203',
        //     awards : 1,
        //     format : 'json'
        // },
        function(data) {
            console.log(data);
        }
    );
}
// function get_movie_data(title, year) {
//     $.get(
//         "main/get_movie_data/" + title + "/" + year,
//         // {
//         //     idIMDB : imdbID,
//         //     token : '0f8e7753-a2d2-44eb-988b-afac4b7b0203',
//         //     awards : 1,
//         //     format : 'json'
//         // },
//         function(data) {
//             console.log((year) + ":"+ title);
//             console.log(data);
//         }
//     );
// get_movie_data
// }
