var app = angular.module("allusersApp", []);
app.controller("allusersCtrl", function($scope, $http){

	// Sporočila
	$scope.message = {"show":false, "type":"", "text":""};
	$scope.modal_message = {"show":false, "type":"", "text":""};
	$scope.hideAlert = function(message){
    	message.show = false;
	}

	// Prazni podatki
	$scope.empty = {"Id_uporabnik": null, "Ime": null, "Priimek": null, "Email": null, "Geslo": null, "Admin": false, "roles":[], "Aktiven": true};
	$scope.type = null;
	$scope.Password = null;
	$scope.PasswordCheck = null;

	// Podatki o vseh vlogah
	function fill_roles() {
		$http.get(getBaseUrl() + '/users/roles').
		then(function(response)
		{
			$scope.roles = response.data.roles;
			$scope.roles_dict = {};
			for (i in $scope.roles) {
				$scope.roles_dict[$scope.roles[i].Id_vloga] = $scope.roles[i].Naziv;
			}
		});
	}
	fill_roles();

	// Podatki o vseh uporabnikih
	function fill_users() {
        $http.get(getBaseUrl() + '/users/get_all').
        then(function(response)
        {
            $scope.users = response.data.text;
        });
    }
	fill_users();

	// Nastavi selected na prazne podatke ali izbranega uporabnika (gumb DODAJ, UREDI)
	$scope.selectUser = function(user) {
		if (user == $scope.empty) {$scope.type = "new";} else {$scope.type = "old";}
		$scope.modal_message.show = false;
		$scope.Password = null;
		$scope.PasswordCheck = null;			
		$scope.selected = angular.copy(user);
		set_mozne();
		angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });
		$("#selected").modal("show");
	}

	// Brisanje uporabnika (gumb IZBRIŠI)
	$scope.deleteUser = function(user) {
		$http.post(getBaseUrl() + '/users/delete', {"Id_uporabnik" : user.Id_uporabnik}).
		then(
			function (response)
			{
				if (response.data.type == "success")
				{
					fill_users();
				}
				$scope.message.show = true;
				$scope.message.type = response.data.type;
				$scope.message.text = response.data.text;
			}
		);
	}	

	// Urejanje in dodajanje uporabnika
	$scope.editUser = function(type) {
		var mozne = [];
		for (i in $scope.mozne_vloge_dict) {
			if ($scope.mozne_vloge_dict[i]) {
				mozne.push(i);
			}
		}
		if (type == "new") {
    		$http.post(getBaseUrl() + '/users/create', {user_data: {
				"Ime" : $scope.selected.Ime,
				"Priimek" : $scope.selected.Priimek,
				"Email" : $scope.selected.Email,
				"Geslo" : $scope.Password,
				"Admin" : $scope.selected.Admin
				},
				roles: mozne
			}).
			then(function (response) {
    			if (response.data.type == "success") {
    				fill_users();
    				$("#selected").modal("hide");
    				$scope.message.show = true;
    				$scope.message.type = response.data.type;
    				$scope.message.text = response.data.text;
    			}
    			else {
    				$scope.modal_message.show = true;
    				$scope.modal_message.type = response.data.type;
    				$scope.modal_message.text = response.data.text;
    			}    			
    		});
    	}
    	else if (type == "old") {
    		$http.post(getBaseUrl() + '/users/update', {user_data: {
    			"Id_uporabnik" : $scope.selected.Id_uporabnik,
    			"Ime" : $scope.selected.Ime,
    			"Priimek" : $scope.selected.Priimek, 
    			"Email" : $scope.selected.Email,
				"Geslo" : $scope.Password,
				"Admin" : $scope.selected.Admin
				},
				roles: mozne
			}).
    		then (function(response) {
    			if (response.data.type == "success") {
    				fill_users();    			
    				$("#selected").modal("hide");
    				$scope.message.show = true;
    				$scope.message.type = response.data.type;
    				$scope.message.text = response.data.text;
    			}
    			else {
    				$scope.modal_message.show = true;
    				$scope.modal_message.type = response.data.type;
    				$scope.modal_message.text = response.data.text;
    			}
    		});
    	}		
	}
	
	$scope.info = function() {
		$http.get(getBaseUrl() + '/users/documentation').
		then (function (response) {
			if (response.data.type=='success') {
				$scope.documentation = response.data.text;
			}
		});
		$("#info").modal("show");
	};

	// POMOŽNE FUNKCIJE	

	// Nastavljanje možnih vlog
	function set_mozne() {
		$scope.mozne_vloge_dict = {};
		for (i in $scope.roles) {
			$scope.mozne_vloge_dict[$scope.roles[i].Id_vloga] = false;	
		}
		for (i in $scope.selected.roles) {
			$scope.mozne_vloge_dict[$scope.selected.roles[i]] = true;
		}
	}

});

app.controller("modalCtrl", function($scope) {});
app.controller("modal_info_Ctrl", function ($scope){});
