
function getBaseUrl() {
    var getUrl = window.location;
    var baseUrl = getUrl .protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
    return baseUrl;
}
var playValue = false;

function play() {
    playValue = true;
}

function testButton() {
    console.log("test button click")
    // get_movie_data("tt0499549")
    get_undefined_ids();
    // error_handling();
}

function stop() {
    playValue = false;
}

window.onbeforeunload = function() {
    return ''; //preprečimo da gremo stran s strani
};




function insertMovieID(id) {

    $.post(
        "DBcontroller/create",
        {
            IMDB_ID : id,
        },
        function(data) {
          console.log(data);
        }
    );
}

function insertMovieTitleYear(title, year) {
    $.post(
        "DBcontroller/create",
        {
            TITLE : title,
            RELEASE_DATE: year + "-01-01"
        },
        function(data) {
            console.log(data);
        }
    );
}

var categoriesSet = new Set();
var moviesHashMap = [];

function start () {
    console.log("start");
    get(2004); // leta pred 1935 tem so bili oskarji od avgusta do julija neslednje leto
    // 1946 dodaj še noter, pred tem zbriši vse filme s tega leta, ker se je tm zataknu request
    // 1986 dodaj še noter, pred tem zbriši vse filme s tega leta, ker se je tm zataknu request
    // 2001 dodaj še noter, pred tem zbriši vse filme s tega leta, ker se je tm zataknu request
}

function get(year){
    if (year <= 2018 && playValue) {//todo temp
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
        t : title.replace(new RegExp(' ', 'g'), '+'),
        y : year,
        apikey : 'e0ce7de6'
        // output : 'JSON'
        },
        function(data) {
            //console.log(year + ":"+ title);
            if (data.Response != "False") {
                var imdbID = data.imdbID;
                console.log(data);
                insertMovieID(imdbID);
                // get_movie_data(imdbID);
            } else {
                console.log("NIMAMO" + title + "--"+year);

                //vseeno vstavimo brez idja
                insertMovieTitleYear(title, year);
                //todo kaj boš z ne dobljenimi filmi?
            }

        }
    );
}
