var app = angular.module("loginApp",[]);
app.controller("loginCtrl", function($scope, $http, $window){

	// Sporočila
	$scope.message = {"show":false, "type":"", "text":""};
	$scope.hideAlert = function(message){
    	message.show = false;
	}

	// Vpis
	$scope.authenticate = function ()
	{
		$http.post(getBaseUrl() + '/login/run',	{
			"email": $scope.user.email,
			"password": $scope.user.password
		}).
		then(
			function (response)
			{
				if (response.data.type == "success")
				{
					$window.location.href = getBaseUrl()+"/home";
				}
				else
				{
					console.log(response.data);
					$scope.message.show = true;
					$scope.message.type = response.data.type;
					$scope.message.text = response.data.text;
				}				
			},
			function (error)
			{
				$scope.message.show = true;
				$scope.message.type = "danger";
				$scope.message.text = "Povezava s strežnikom ni uspela.";
			}
		);
	}
	
	$scope.info = function() {
		$http.get(getBaseUrl() + '/login/documentation').
		then (function (response) {
			console.log(response);
			if (response.data.type=='success') {
				$scope.documentation = response.data.text;
			}
		});
		$("#info").modal("show");
	};

});
app.controller("modal_info_Ctrl", function ($scope){});
