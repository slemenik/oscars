
function getBaseUrl() {
    var getUrl = window.location;
    var baseUrl = getUrl .protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
    return baseUrl;
}

console.log(123);
get(1929);

var categories = new Set();

function get(year){
    $.get(
        "main/get_nomenees_per_year/" + year,
        // {
        // input : 'Oscar nominations 1988',
        // format : 'plaintext',
        // appid : '8V8KKQ-L2VKTA87YH',
        // output : 'JSON'
        // },
        function(data) {
            console.log(year);
            // console.log(data);
            properties = Object.keys(data);
            for (var i = 0; i<properties.length;i++){
                categories.add(properties[i]);
            }
            if (year <= 2018) {
                year++;
                get(year);
            } else {
                console.log(categories);
            }


        }
    );
}
