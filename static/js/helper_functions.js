
function getBaseUrl() {
    var getUrl = window.location;
    var baseUrl = getUrl .protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
    return baseUrl;
}

// console.log(123);


var categories = new Set();

function start () {
    console.log("start");
    get(1929);

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

                console.log(data);
                properties = Object.keys(data);
                for (var i = 0; i<properties.length;i++){
                    categories.add(properties[i]);
                }

                year++;
                get(year);



            }
        );
    } else {
        console.log("end");
        console.log(categories);
    }
}

function get_movie() {
    var title = "Avatar";
    var year = 2009;
    $.get(
        "http://www.omdbapi.com/",
        {
        t : title,
        y : year,
        apikey : 'e0ce7de6'
        // output : 'JSON'
        },
        function(data) {

            var imdbID = data.imdbID;
            console.log(imdbID);
            get_movie_data(imdbID);
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
