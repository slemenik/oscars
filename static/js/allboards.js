var app = angular.module("allboardsApp",[]);
app.controller("allboardsCtrl", function ($scope, $http) {
	
	// Sporočila
	$scope.message = {"show":false, "type":"", "text":""};	
	$scope.hideAlert = function(message){
		message.show = false;
	}
	
	function fill_boards() {
		$http.get(getBaseUrl() + '/boards/get_all').
        then(function(response)
        {   
            if (response.data.type == "success") {
				$scope.boards = response.data.text;
			}
			else {
				$scope.message.show = true;
				$scope.message.type = response.data.type;
				$scope.message.text = response.data.text;
			}		
        });
	}
	fill_boards();
	
	$scope.info = function() {
		$http.get(getBaseUrl() + '/boards/documentation').
		then (function (response) {
			if (response.data.type=='success') {
				$scope.documentation = response.data.text;
			}
		});
		$("#info").modal("show");
	};

});

app.controller("modalCtrl", function ($scope){});
app.controller("modal_info_Ctrl", function ($scope){});
