
function getBaseUrl() {
    var getUrl = window.location;
    var baseUrl = getUrl .protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
    return baseUrl;
}

console.log(123);

// $.get(
//     "https://api.wolframalpha.com/v2/query",
//     {
//         input : 'Oscar nominations 1988',
//         format : 'plaintext',
//         appid : '8V8KKQ-L2VKTA87YH',
//         output : 'JSON'
//     },
//     function(data) {
//         console.log(data);
//
//         var pods = data.pods;
//         var b=  pods.find(x => x.title == 'Academy Award winners and nominees');
//         console.log(b);
//
//     }
// );