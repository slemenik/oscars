var app = angular.module("allgroupsApp", []);
app.controller("allgroupsCtrl", function($scope, $http) {

	// Sporočila
	$scope.message = {"show":false, "type":"", "text":""};
	$scope.modal_message = {"show":false, "type":"", "text":""};

	$scope.hideAlert = function(message){
    	message.show = false;
	}

	// Prazni podatki
	$scope.empty = {
		"Ime_skupine": null,
		"Members": []
	};
	$scope.type = null;

	// Podatki o vseh skupinah
	function fill_groups() {
		$http.get(getBaseUrl() + '/groups/get_all_groups').
		then(function(response)
		{
			$scope.groups = response.data.text;
			for (i in $scope.groups) {
				for (j in $scope.groups[i].Members) {
					for (k in $scope.groups[i].Members[j])
						$scope.groups[i].Members[j].Datum_zacetka = new Date($scope.groups[i].Members[j].Datum_zacetka);
            			$scope.groups[i].Members[j].Datum_konca = new Date($scope.groups[i].Members[j].Datum_konca);
            	}
            }
		});
	}
	fill_groups();

	// Podatki o vseh uporabnikih
	function fill_users() {
		$http.get(getBaseUrl() + '/groups/get_active_user_with_roles').
		then(function(response)
		{
			var response = response.data.text;
			$scope.users = [];
			for (i in response) {
				$scope.users.push({
					"Id_uporabnik": response[i].Id_uporabnik,
					"Ime": response[i].Ime,
					"Priimek": response[i].Priimek,
        			"Email": response[i].Email,
        			"Mozne_vloge": response[i].roles,
        			"Konkretne_vloge": [],
        			"Activen": true
        		});
			}			     	
		});
	}
	fill_users();

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

	// Nastavi selected na prazne podatke ali izbrano skupino (gumb DODAJ, UREDI)
	$scope.selectGroup = function(group) {
		$scope.modal_message.show = false;
		if (group == $scope.empty) {$scope.type = "new";} else {$scope.type = "old";}	
		$scope.selected = angular.copy(group);
		$scope.before = angular.copy(group);
		update_users();
		set_konkretne();
		angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });
		$("#selected").modal("show");
	}

	// Brisanje skupine (gumb IZBRIŠI)
	$scope.deleteGroup = function(group) {
		$http.post(getBaseUrl() + '/groups/remove_group', {
			"Id_skupina" : group.Id_skupina
		}).
		then (
			function (response) {
				if (response.data.type == "success") {
					fill_groups();
				}
				$scope.message.show = true;
				$scope.message.type = response.data.type;
				$scope.message.text = response.data.text;
		});
	}
	
	// Dodajanje člana
	$scope.addMember = function() {
		var found = false;
		for (i in $scope.before.Members) {
			if (this.selectedUser.Id_uporabnik == $scope.before.Members[i].Id_uporabnik) {
				$scope.selected.Members[i].Activen = true;
				found = true;
				break;
			}
		}
		if (!found) {
			$scope.selected.Members.push(this.selectedUser);
		}
		this.selectedUser = null;
		update_users();
		angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });
	}

	// Odstranjevanje člana
	$scope.removeMember = function(member) {
		var found = false;
		for (i in $scope.before.Members) {
			if (member.Id_uporabnik == $scope.before.Members[i].Id_uporabnik) {				
				$scope.selected.Members[i].Activen = false;
				$scope.selected.Members[i].Konkretne_vloge = [];
				$scope.konkretne_vloge_dict[member.Id_uporabnik] = {};
				found = true;
				break;
			}
		}
		if (!found) {
			var index = $scope.selected.Members.indexOf(member);
			if (index != -1) {$scope.selected.Members.splice(index, 1);}
		}
		update_users();
		angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });
	}
		
	// Urejanje in dodajanje skupine
	$scope.editGroup = function(type) {
        for (member_inx in $scope.selected.Members) {
        	var member = $scope.selected.Members[member_inx];
        	var roles = $scope.konkretne_vloge_dict[member.Id_uporabnik];
        	var konkretne = [];
        	for (role_id in roles) {
    			if (roles[role_id]) {
    				konkretne.push(role_id);
    			}
        		$scope.selected.Members[member_inx].Konkretne_vloge = konkretne;        		
        	}        	           
        }
    	if (type == "new") {
    		$http.post(getBaseUrl() + '/groups/add_group', {
    			"Ime_skupine": $scope.selected.Ime_skupine,
    			"Members": $scope.selected.Members
    		}).
    		then (function(response) {
    			if (response.data.type == "success") {
    				fill_groups();
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
    		$http.post(getBaseUrl() + '/groups/update_group', {
    			"Id_skupina": $scope.selected.Id_skupina,
    			"Ime_skupine": $scope.selected.Ime_skupine,
    			"Members": $scope.selected.Members
    		}).
    		then (function(response) {
    			if (response.data.type == "success") {
    				fill_groups();    			
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
			
	// POMOŽNE FUNKCIJE

	// Nastavljanje konkretnih vlog
	function set_konkretne() {
		$scope.konkretne_vloge_dict = {};
		for (user_inx in $scope.users) {
			var user = $scope.users[user_inx];
			$scope.konkretne_vloge_dict[user.Id_uporabnik] = {};
		}
		for (member_inx in $scope.selected.Members) {
			var member = $scope.selected.Members[member_inx];
			var roles_dict = {};
			for (role_inx in member.Konkretne_vloge) {
				roles_dict[member.Konkretne_vloge[role_inx]] = true;
			}
			$scope.konkretne_vloge_dict[member.Id_uporabnik] = roles_dict;
		}	
	}

	// ne pokaže selected v users
	function update_users() {
		$scope.users_minus = angular.copy($scope.users);
		for (member_inx in $scope.selected.Members) {
			var member = $scope.selected.Members[member_inx];
			for (user_inx in $scope.users_minus) {
				var user = $scope.users_minus[user_inx];
				if (member.Id_uporabnik == user.Id_uporabnik && member.Activen == true) {
					var index = $scope.users_minus.indexOf(user);
					if (index != -1) {$scope.users_minus.splice(index, 1);}
				}
			}
		}
	}
	
	$scope.info = function() {
		$http.get(getBaseUrl() + '/groups/documentation').
		then (function (response) {
			if (response.data.type=='success') {
				$scope.documentation = response.data.text;
			}
		});
		$("#info").modal("show");
	};

});

app.controller("modalCtrl", function($scope){});
app.controller("modal_info_Ctrl", function ($scope){});
