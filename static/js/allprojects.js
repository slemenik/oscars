var app = angular.module("allprojectsApp", []);
app.controller("allprojectsCtrl", function($scope, $http) {

	// Današnji datum
	$scope.today = new Date();

	// Sporočila
	$scope.message = {"show":false, "type":"", "text":""};
	$scope.modal_message = {"show":false, "type":"", "text":""};
	$scope.hideAlert = function(message){
    	message.show = false;
	}

	// Prazni podatki
	$scope.empty = {
		"Sifra_projekta": null,
		"Ime_projekta": null,
		"Naziv_narocnika": null,
		"Datum_zacetka": null,
		"Datum_konca": null,
		"deaktiviran": false,
		"Id_skupina": null
	};
	$scope.type = null;
	
	// Podatki o vseh projektih
	function fill_projects() {
        $http.get(getBaseUrl() + '/projects/get_all').
        then(function(response)
        {
            $scope.projects = response.data.text;
            for (i in $scope.projects) {
            	$scope.projects[i].Datum_zacetka = new Date($scope.projects[i].Datum_zacetka);
            	$scope.projects[i].Datum_konca = new Date($scope.projects[i].Datum_konca);
            }
        });
    }
	fill_projects();

	// Podatki o vseh skupinah
	function fill_groups() {
		$http.get(getBaseUrl() + '/groups/get_all_groups').
		then(function(response)
		{
			$scope.allgroups = response.data.text;
		});
	}
	fill_groups();

	// Nastavi selected na prazne podatke ali izbran projekt (gumb DODAJ, UREDI)
	$scope.selectProject = function(project) {
		$scope.modal_message.show = false;
		if (project == $scope.empty) {$scope.type = "new";} else {$scope.type = "old";}
		$("#selected").modal("show");
		$scope.selected = angular.copy(project);
		angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });
	}

	// Brisanje projekta
	$scope.deleteProject = function(project) {
		$http.post(getBaseUrl() + '/projects/delete', {"Id_projekta": project.Id_projekta}).
        then(function(response)
        {        	
            if (response.data.type == "success" || response.data.type == "info") {
				fill_projects();
			}
			$scope.message.show = true;
			$scope.message.type = response.data.type;
			$scope.message.text = response.data.text;
        });
	}

	// Urejanje in dodajanje projekta
	$scope.editProject = function(type) {
		if (type == "new") {
			$http.post(getBaseUrl() + '/projects/create', {project_data: {
				"Sifra_projekta": $scope.selected.Sifra_projekta,
				"Naziv_projekta": $scope.selected.Naziv_projekta,
				"Naziv_narocnika": $scope.selected.Naziv_narocnika,
				"Datum_zacetka": $scope.selected.Datum_zacetka.valueOf(),
				"Datum_konca": $scope.selected.Datum_konca.valueOf(),
				"Id_skupina": $scope.selected.Id_skupina
			}}).
			then ( function(response) {
				if (response.data.type == "success") {
    				fill_projects();
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
			$http.post(getBaseUrl() + '/projects/edit', {project_data: {
				"Id_projekta": $scope.selected.Id_projekta,
				"Sifra_projekta": $scope.selected.Sifra_projekta,
				"Naziv_projekta": $scope.selected.Naziv_projekta,
				"Naziv_narocnika": $scope.selected.Naziv_narocnika,
				"Datum_zacetka": $scope.selected.Datum_zacetka.valueOf(),
				"Datum_konca": $scope.selected.Datum_konca.valueOf(),
				"Id_skupina": $scope.selected.Id_skupina
			}}).
			then ( function(response) {
				if (response.data.type == "success") {
    				fill_projects();
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

	// Poizvedba o imenu skupine
	$scope.groupName = function(id) {
		for (group_inx in $scope.allgroups) {
			var group = $scope.allgroups[group_inx];
			if (group.Id_skupina == id) {
				return group.Ime_skupine;
			}
		}
	}

	$scope.info = function() {
		$http.get(getBaseUrl() + '/projects/documentation').
		then (function (response) {
			if (response.data.type=='success') {
				$scope.documentation = response.data.text;
			}
		});
		$("#info").modal("show");
	};
	/////////////////////////////////////////////////////////////////
	$scope.addCard = function(project) {
		$http.post(getBaseUrl() + '/projects/create_card_demo', {
			"Id_projekta": project.Id_projekta
		}).
		then ( function(response) {
			if (response.data.type == "success") {
				fill_projects();
			}
			$scope.message.show = true;
			$scope.message.type = response.data.type;
			$scope.message.text = response.data.text;
		});
	}
	$scope.deleteCard = function(project) {
		$http.post(getBaseUrl() + '/projects/delete_card_demo', {
			"Id_projekta": project.Id_projekta
		}).
		then ( function(response) {
			if (response.data.type == "success") {
				fill_projects();
			}
			$scope.message.show = true;
			$scope.message.type = response.data.type;
			$scope.message.text = response.data.text;
		});
	}
	/////////////////////////////////////////////////////////////////

});

app.controller("modalCtrl", function($scope) {});
app.controller("groupCtrl", function($scope) {});
app.controller("modal_info_Ctrl", function ($scope){});
