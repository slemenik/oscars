var app = angular.module("boardApp",["dndLists"]);
app.controller("boardCtrl", function ($scope, $http) {


	// Sporočila
	$scope.message = {"show":false, "type":"", "text":""};
	$scope.modal_message = {"show":false, "type":"", "text":""};
	$scope.hideAlert = function(message){
		message.show = false;
	}

	// Seznam vseh projektov in vseh projektov minus selected
	function fill_projects() {
		$http.get(getBaseUrl() + '/boards/get_available_projects').
		then(function(response)
		{
			$scope.allprojects = response.data.text;
			for (i in $scope.allprojects) {
				$scope.allprojects[i].Datum_zacetka = new Date($scope.allprojects[i].Datum_zacetka);
				$scope.allprojects[i].Datum_konca = new Date($scope.allprojects[i].Datum_konca);
			}
			update_projects();
			angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });
		});
	}
	fill_projects();
	// Ne pokaže selected_projects v allprojects
	function update_projects() {
		$scope.projects_minus = angular.copy($scope.allprojects);
		for (projInx in $scope.selected_projects) {
			var project = $scope.selected_projects[projInx];
			for (selectedInx in $scope.projects_minus) {
				var selectedProj = $scope.projects_minus[selectedInx];
				if (project.Id_projekta==selectedProj.Id_projekta) {
					var index = $scope.projects_minus.indexOf(selectedProj);
					if (index != -1) {$scope.projects_minus.splice(index, 1);}
				}
			}
		}
	}
	// Funkcija, ki pridobi podatke o tabli iz baze
	function fill_board(id) {
		$http.post(getBaseUrl() + '/boards/get', {"Id_table":id}).
		then(function(response) {        	
			if (response.data.type == "success") {

				// Sporočilo o uspešno kreirani tabli
				if (response.data.text.text!=null) {
					$scope.message.show = true;
					$scope.message.type = response.data.type;
					$scope.message.text = response.data.text.text;
				}

				// Podatki o tabli
				$scope.board = response.data.text.structure;
				$scope.selected_projects = response.data.text.projects;
				posodobiProjekte(stolpciBrezPodstolpcev($scope.board.columns),[],'uredi');
				if ($scope.board_new) {					
					$scope.board.Id_table = null;
					$scope.board.Ime_table = null;
                    $scope.board.N_dni = null;
					$scope.board.D_dni = null;
					$scope.selected_projects = [];
				}

				// Podatki o vlogah prijavljenega uporabnika
				$http.post(getBaseUrl() + '/boards/get_roles_per_project', {"Id_table":id}).
				then(function(response) {
					$scope.user_roles = response.data;
					$scope.edit_privilege = false;
					for (i in $scope.user_roles) {
						if ($scope.user_roles[i].indexOf('1')!=-1) {
							$scope.edit_privilege = true;
						}
					}

					// Prirejanje podatkov
					$scope.cards_in_testing = {};
					$scope.move_card_privilege = {};
					$scope.neighbours = {};
					$scope.pari_zavrnjena = {};

					var podstolpci = stolpciBrezPodstolpcev($scope.board.columns);
					for (i in podstolpci) {
						if (podstolpci[i].Nujni) {
							var seznam = angular.copy(podstolpci);
							seznam.splice(Number(i), podstolpci.length);
							var Nujni = podstolpci[i].Id_stolpec;
							var levo_od_Nujni = [];
							for (j in seznam) {
								levo_od_Nujni.push(seznam[j].Id_stolpec);
							}
						}
						if (podstolpci[i].Testni) {
							var seznam = angular.copy(podstolpci).splice(Number(i)+1);
							var Testni = podstolpci[i].Id_stolpec;
							var desno_od_Testni = [];
							for (j in seznam) {
								desno_od_Testni.push(seznam[j].Id_stolpec);
							}
							break;
						}
					}

					for (i in $scope.selected_projects) {
						var project = $scope.selected_projects[i];
						project.Datum_zacetka = new Date(project.Datum_zacetka);
						project.Datum_konca = new Date(project.Datum_konca);

						// Kartice lahko premikajo le člani projekta
						if ($scope.user_roles[project.Id_projekta].length != 0) {
							$scope.move_card_privilege[project.Id_projekta] = true;
						}
						else {
							$scope.move_card_privilege[project.Id_projekta] = false;
						}

						for (j in project.track) {
							var column = project.track[j];

							// Kanban Master in Razvijalec lahko premikata le kartice levo od testnega stolpca
							// Product Owner lahko premika kartice v stolpcih desno od testnega	
							if ((($scope.user_roles[project.Id_projekta].indexOf('1') != -1 ||
								$scope.user_roles[project.Id_projekta].indexOf('3') != -1) &&
								desno_od_Testni.indexOf(column.Id_stolpec)==-1) ||
								($scope.user_roles[project.Id_projekta].indexOf('2') != -1 &&
								(desno_od_Testni.indexOf(column.Id_stolpec)!=-1 || Testni == column.Id_stolpec)))
							{
								$scope.neighbours[column.Id_proge] = [column.Id_proge];
								if (project.track[Number(j)-1]) {
									$scope.neighbours[column.Id_proge].push(project.track[Number(j)-1].Id_proge);
								}
								if (project.track[Number(j)+1]) {
									$scope.neighbours[column.Id_proge].push(project.track[Number(j)+1].Id_proge);
								}
							}
							else {
								$scope.neighbours[column.Id_proge] = [];
							}

							if ($scope.user_roles[project.Id_projekta].indexOf('2') != -1 &&
									(levo_od_Nujni.indexOf(column.Id_stolpec)!=-1 || Nujni == column.Id_stolpec))
							{
								for (q in project.track) {
									if (project.track[q].Id_stolpec == Testni) {
										var testni_id = project.track[q].Id_proge;
										if (!$scope.pari_zavrnjena.hasOwnProperty(testni_id)) {
											$scope.pari_zavrnjena[testni_id] = [];
										}
										break;
									}
								}
								$scope.neighbours[column.Id_proge].push(testni_id);
								$scope.pari_zavrnjena[testni_id].push(column.Id_proge);
							}

							for (k in column.cards) {
								var card = column.cards[k];
								card.Rok_koncanja = new Date(card.Rok_koncanja);
								if (card.Stevilo_tock) {
									card.Stevilo_tock = Number(card.Stevilo_tock);									
								}
							}
						}
					}
				});						
			}
			else {
				$scope.message.show = true;
				$scope.message.type = response.data.type;
				$scope.message.text = response.data.text;
			}
		});
	}	
	
	// Preveri: UREDI / KOPIRAJ / NOVA	
	$scope.edit = false; // podatek o tem, ali je tabla v urejanju
	$scope.board_new = false; // podatek o tem, ali je tabla nova
	function preveriId() {
		var lokacija = window.location.pathname;
		var sez = new Array();
		sez = lokacija.split("/");
		var zadnjiNiz = sez[sez.length-1];
		var predzadnjiNiz = sez [sez.length-2];
		
		if (zadnjiNiz=="new_board") {
			$scope.board = {'Id_table':null, 'Ime_table':null, 'N_dni':null, 'D_dni':null, 'columns':[]};
			$scope.selected_projects = [];
			$scope.edit = true;
			$scope.board_new = true;
		}
		else if (predzadnjiNiz=="copy_board") {
			var id_table = parseInt(zadnjiNiz);			
			$scope.edit = true;
			$scope.board_new = true;
			fill_board(id_table);
		}
		else {
			var id_table = parseInt(zadnjiNiz);
			fill_board(id_table);
			$scope.edit = false;
			$scope.board_new = false;			
		}
	}
	preveriId();	
	
	// Uredi tablo	
	$scope.editBoard = function() {
		$scope.board_new = false;
		$scope.edit = true;
		$scope.old_board = angular.copy($scope.board);
		$scope.old_selected_projects = angular.copy($scope.selected_projects);
		$scope.old_columns = [];
		$scope.deleted_columns = [];
		allOldColumns($scope.board.columns);
	}
	
	// Prekliči urejanje table
	$scope.dismiss = function() {
		if ($scope.board_new==true) {
			window.location.href=getBaseUrl()+"/boards";
		}
		else {			
			$scope.board = angular.copy($scope.old_board);
			$scope.selected_projects = angular.copy($scope.old_selected_projects);
			$scope.edit = false;			
		}
	}

	// Pošiljanje podatkov o ustvarjeni tabli	
	function createBoard() {
		var stolpciBrezId = angular.copy($scope.board.columns);
		odstraniId(stolpciBrezId);

		$http.post(getBaseUrl() + '/boards/create', {'board':{"columns":stolpciBrezId, "Ime_table":$scope.board.Ime_table, "N_dni":$scope.board.N_dni, "D_dni":$scope.board.D_dni}, "selected_projects":$scope.selected_projects}).
		then(function(response) {
			if (response.data.type == "success") {
				$scope.edit = false;
				$scope.board_new = false;
				var idTabla = angular.copy(response.data.text);
				window.location.href=getBaseUrl()+'/boards/board/'+idTabla+''; 
			}
			else {
				$scope.message.show = true;
				$scope.message.type = response.data.type;
				$scope.message.text = response.data.text;
			}
		});
	}

	function updateBoard() {
		$http.post(getBaseUrl() + '/boards/update',{'board':$scope.board, 'selected_projects':$scope.selected_projects, 'deleted_columns':$scope.deleted_columns}).
		then (function(response) {
			if (response.data.type=='success') {
				$scope.edit = false;
				fill_board($scope.board.Id_table);
				fill_projects();
			}
			$scope.message.show = true;
			$scope.message.type = response.data.type;
			$scope.message.text = response.data.text;
		});
	}
	
	// Shranjevanje table	
	$scope.saveBoard = function() {
		var stolpciBrezId = angular.copy($scope.board.columns);
		if ($scope.board_new) {
            odstraniId(stolpciBrezId);
		}
		$http.post(getBaseUrl() + '/boards/validate', {'board':{"columns":stolpciBrezId, "Ime_table":$scope.board.Ime_table, "Id_table":$scope.board.Id_table, "N_dni":$scope.board.N_dni, "D_dni":$scope.board.D_dni}, "selected_projects":$scope.selected_projects}).
		then(function(response) {
			if (response.data.type=='success') {
				if ($scope.board_new) {
					createBoard();
				}
				else {
					updateBoard();
				}
			}
			else if (response.data.type=='warning') {
				$("#confirm").modal("show");
			}
            else if (response.data.type=='info') {
                $("#confirm_wip_column").modal("show");
            }
			else {
				$scope.message.show = true;
				$scope.message.type = response.data.type;
				$scope.message.text = response.data.text;
			}
		});		
	}
	
	// Funkcija za potrditveno okno
	$scope.potrdi = function(odgovor) {
		if (odgovor == "da") {
			$("#confirm").modal("hide");
			$("#confirm_wip_column").modal("hide");
			if ($scope.board_new) {
				createBoard();
			}
			else {
				updateBoard();
			}
		}
		else {
			$("#confirm").modal("hide");
            $("#confirm_wip_column").modal("hide");
		}
	}

	// Dodajanje projektov
	$scope.addProject = function() {
		var novProjekt = this.selected_project;
		var prazniStolpci = stolpciBrezPodstolpcev($scope.board.columns);
		var trak = [];
		for (i in prazniStolpci) {
			trak.push({'Id_proge':null, 'Id_projekta':null, 'Id_stolpec':prazniStolpci[i].Id_stolpec, 'cards':[]});
		}
		novProjekt.track = trak;		
		$scope.selected_projects.push(novProjekt);
		this.selected_project = null;
		update_projects();
		angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });	
	}
	
	// Brisanje projekta TODO: projekta se ne da izbrisati če stolpci vsebujejo kartice
	$scope.deleteProject = function(idProj) {
		$scope.selected_projects.forEach(function (el) {
			if (el.Id_projekta==idProj) {
				if (projektImaKartice(el)==true) {
					$scope.message.show = true;
					$scope.message.type = "danger";
					$scope.message.text = "Projekt ima kartice.";		
				}
				else {
					var indeks=$scope.selected_projects.indexOf(el);
					$scope.selected_projects.splice(indeks,1);
				}
			}
		});
		update_projects();
		angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });
	}

	// Prazni podatki za kartico
	$scope.type_card = null;
	$scope.empty_card = {
		'Id_projekta':null,
		'Ime_kartice': null,
		'Opis_kartice': null,
		'Prioriteta': '3',
		'Rok_koncanja': null,
		'Stevilo_tock': null,
		'Dodeljen': null};
	
	
	// Izbiranje kartice
	$scope.selectCard = function(project, card) {
		if (card == $scope.empty_card) {$scope.type_card = "new";} else {$scope.type_card = "old";}
		$scope.curr_project = project;
		$scope.allowedUpdate = false;
		$scope.allowedDelete = false;
		isUpdateAllowed(project,card);
		$scope.selected_card = angular.copy(card);		
		$http.post(getBaseUrl() + '/cards/get_possible_assignees', {'Id_projekta': project.Id_projekta}).
		then(function(response) {
			$scope.project_devs = response.data.text;
			angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });
		});		
		$scope.modal_message.show = false;
		var idKartice = card.Id_kartice;
		premiki(idKartice);
		krsitve(idKartice);
		zgodovina(idKartice);
		$("#card").modal("show");
	}
	
	// Shranjevanje kartice
	$scope.saveCard = function() {		
		$scope.selected_card.Id_projekta=$scope.curr_project.Id_projekta;
		if ($scope.type_card == 'new') {
			$http.post(getBaseUrl() + '/cards/create_card', $scope.selected_card).
			then(function(response) {
				if (response.data.type=='success') {
					var cardMessage = angular.copy(response.data.text);
					fill_board($scope.board.Id_table);					
					$scope.message.show = true;
					$scope.message.type = response.data.type;
					$scope.message.text = cardMessage;
					$("#card").modal("hide");
				}
				else {
					$scope.modal_message.show = true;
					$scope.modal_message.type = response.data.type;
					$scope.modal_message.text = response.data.text;
				}
			});
		}
		else if ($scope.type_card == 'old') {
			$http.post(getBaseUrl() + '/cards/update_card', $scope.selected_card).
			then (function(response){
				if (response.data.type=="success"){
					var cardMessage = angular.copy(response.data.text);
					fill_board($scope.board.Id_table);
					$scope.message.show = true;
					$scope.message.type = response.data.type;
					$scope.message.text = cardMessage;
					$("#card").modal("hide");
				}
				else {
					$scope.modal_message.show = true;
					$scope.modal_message.type = response.data.type;
					$scope.modal_message.text = response.data.text;
				}
			});
		} 
		
	}
	
	//Funkcija, ki vrne seznam vlog prijavljenega uporabnika pri danem projektu
	function vlogePriProjektu(projekt){
		for (p in $scope.user_roles){			
			if (p==projekt.Id_projekta){
				return $scope.user_roles[p];
			}
		}
		return [];	
	}
	//Funkcija, ki pri dani kartici preveri, ali jo trenutno prijavljen uporabnik lahko spreminja
	function isUpdateAllowed (projekt,card){
		var vloge = vlogePriProjektu(projekt);
		var stolpci = stolpciBrezPodstolpcev($scope.board.columns);
		var zacetni = false;
		var indZacetni = null;
		var indKoncni = null;
		
		for (i in stolpci) {
			if (stolpci[i].Mejni && !zacetni) {
				zacetni = true;
				indZacetni = i;
			}
			else if (stolpci[i].Mejni && zacetni) {
				indKoncni = i;
				break;
			}
		}
		if (indKoncni==null){
			indKoncni=indZacetni;
		}
		
		if (vloge.length==0){
			$scope.allowedUpdate = false;
			$scope.allowedDelete = false;
		}
				
		else {
			var cardInd = null;
			for (j in projekt.track) {
				if (projekt.track[j].cards.indexOf(card)!=-1){
					cardInd = j;
					break;
				}
			}
			
			if (cardInd<indZacetni) {
				
				for (v in vloge) {
					if (vloge[v]=="1" || vloge[v]=="2") {
						$scope.allowedUpdate = true;
						$scope.allowedDelete = true;
						break;
						
						
					}
				}
			}
			else if (cardInd>=indZacetni && cardInd<=indKoncni) {
				for (v in vloge) {
					if (vloge[v]=="1"){
						$scope.allowedUpdate = true;	
						$scope.allowedDelete = true;
						break;
					}
					else if (vloge[v]=="3") {
						$scope.allowedUpdate = true;	
						break;
					}
				}
			}
			
			else {
				for (v in vloge) {
					if (vloge[v]=="1"){	
						$scope.allowedDelete = true;
						break;
					}
				}
			}
		}
	}
	
	//Brisanje kartice
	$scope.deleteCard = function() {
		//console.log($scope.delete_reason);
		$http.post(getBaseUrl() + '/cards/delete_card', {'Id_projekta':$scope.curr_project.Id_projekta,'Id_kartice':$scope.selected_card.Id_kartice,'Vzrok_brisanja':$scope.delete_reason}).
		then (function(response) {
			if (response.data.type=='success'){
				var cardMessage = angular.copy(response.data.text);
				fill_board($scope.board.Id_table);
				$scope.message.show = true;
				$scope.message.type = response.data.type;
				$scope.message.text = cardMessage;
				$("#confirm_delete_card").modal("hide");
				$("#card").modal("hide");
			}
			else {
				
				$scope.modal_message.show = true;
				$scope.modal_message.type = response.data.type;
				$scope.modal_message.text = response.data.text;
				//console.log(response.data.text);
			}
			
		});
	}
	
	// Kršitve in premiki
	function premiki(idKartice) {
		$http.post(getBaseUrl() + '/cards/get_cards_moves', {'Id_kartice':idKartice}).
			then(function(response) {
				if (response.data.type=='success') {
					$scope.premiki = response.data.text;
					for (j in $scope.premiki) {
						$scope.premiki[j].Datum_dogodka = new Date($scope.premiki[j].Datum_dogodka);
					}
				}
			});
	}
	
	function krsitve(idKartice) {
		$http.post(getBaseUrl() + '/cards/get_cards_violations', {'Id_kartice':idKartice}).
			then(function(response) {
				if (response.data.type=='success') {
					$scope.krsitve = response.data.text;
					for (j in $scope.krsitve) {
						$scope.krsitve[j].Datum_dogodka = new Date($scope.krsitve[j].Datum_dogodka);
					}
				}
			});
	}
	
	//Zgodovina kartice
	function zgodovina(idKartice) {
		$http.post(getBaseUrl() + '/cards/get_card_history', {'Id_kartice':idKartice}).
		then (function(response){
			if (response.data.type=="success") {
				$scope.zgodovina = response.data.text;
				for (j in $scope.zgodovina) {
						$scope.zgodovina[j].Datum_dogodka = new Date($scope.zgodovina[j].Datum_dogodka);
					}
			}
		});
	}
	
	//Prestavljanje kartice
	$scope.moveCard = function(dovoli) {		
		$("#confirm_card").modal("hide");
		if (dovoli) {
			if ($scope.pari_zavrnjena[$scope.id_start]) {
				if ($scope.pari_zavrnjena[$scope.id_start].indexOf($scope.id_cilj)!=-1){					
					$scope.zavrnjena = 1;
				} else {
					$scope.zavrnjena = undefined;
				}
			}
			$http.post(getBaseUrl() + '/cards/move_card', {
				"Id_projekta":$scope.id_projekt,
				"Id_kartice":$scope.item.Id_kartice,
				"Id_stolpec_old":$scope.id_start,
				"Id_stolpec_new":$scope.id_cilj,
				"Zavrnjena":$scope.zavrnjena}).
			then(function(response)
			{
				$scope.message.show = true;
				$scope.message.type = response.data.type;
				$scope.message.text = response.data.text;
				if (response.data.type == "success") {
					fill_board($scope.board.Id_table);
				}
				else {
					return false;
				}
			});
		}
		else {
			return false;
		}
    };

    $scope.tryToMoveCard = function(item, id_start, id_cilj, id_projekt) {
		$scope.item = item;
		$scope.id_start = id_start;
		$scope.id_cilj = id_cilj;
		$scope.id_projekt = id_projekt;
		$http.post(getBaseUrl() + '/cards/validate_card_move', {
			"Id_projekta":id_projekt,
			"Id_kartice":item.Id_kartice,
			"Id_stolpec_old":id_start,
			"Id_stolpec_new":id_cilj}).
		then(function(response)
		{
			if (response.data.type == 'success') {
				$scope.moveCard(true);
			}
			else if (response.data.type == 'warning') {
				$("#confirm_card").modal("show");
			}
			else {
				$scope.message.show = true;
				$scope.message.type = response.data.type;
				$scope.message.text = response.data.text;
				return false;
			}
		});
	}
	

	// Podatki o kliknjenem stolpcu pri dodajanju novega stolpca
	var id_column = null;
	var direction = null;
	$scope.newColumn = function(dir, stolpec) {
		direction = dir;
		if (stolpec) {
			id_column = stolpec.Id_stolpec;
		}
		else {
			id_column = null;
		}
		$scope.new_column = {"Id_stolpec":null, "Ime_stolpca":null, 'WIP':null, 'Mejni': false, 'Nujni': false, 'Testni': false, "subcolumns": []};		
		$scope.modal_message.show = false;
		$("#add").modal("show");
	}
	
	// Dodajanje stolpca
	$scope.addColumn = function() {
		dodajStolpec($scope.board.columns, id_column);
		posodobiProjekte(stolpciBrezPodstolpcev($scope.board.columns),[],'dodaj'); 
		$("#add").modal("hide");		
		angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });	
	}	
	// Brisanje stolpca
	$scope.deleteColumn = function(stolpec) {
		izbrisiStolpec($scope.board.columns, stolpec.Id_stolpec);
		if ($scope.board.columns.length==0) {
			$scope.selected_projects = [];
			update_projects();
			angular.element(document).ready(function () { $('select').selectpicker("destroy"); $('select').selectpicker("render"); });
		}
		else {
			if (imaKartice(stolpec)==false) {
				var listi = listiStolpca(stolpec);
				posodobiProjekte(stolpciBrezPodstolpcev($scope.board.columns),listi,'zbrisi'); // TODO: projektom se morajo pobrisati stolpci, zaenkrat se pa vse povozi
			}
		}
	}
	
	// Urejanje stolpca
	$scope.editColumn = function(stolpec) {

		$scope.selected_column = angular.copy(stolpec);
		$scope.selected_column.WIP = parseInt($scope.selected_column.WIP);
        $("#edit").modal("show");
	}

	$scope.saveColumn = function() {
		urediStolpec($scope.board.columns, $scope.selected_column.Id_stolpec);
		$("#edit").modal("hide");
	}
	
	$scope.info = function() {
		$http.get(getBaseUrl() + '/boards/documentation').
		then (function (response) {
			if (response.data.type=='success') {
				$scope.documentation = response.data.text;
			}
		});
		$("#info").modal("show");
	};

	// POMOŽNE FUNKCIJE

	// Vsi stolpci ki nimajo podstolpcev
	function stolpciBrezPodstolpcev(seznam) {		
		var prazniStolpci = [];
		var preveri = function(seznam) {
			var len = seznam.length;
			for (var i=0; i<len; i++) {
				if (seznam[i].subcolumns.length==0) {
					prazniStolpci.push(seznam[i]);
				}
				else {
					preveri(seznam[i].subcolumns);
				}
			}
		}		
		preveri(seznam);
		return prazniStolpci;
	}
	
	// Funkcija za posodabljanje stolpcev pri projektih ob dodajanju/brisanju stolpcev na tabli
	function posodobiProjekte(prazniStolpci, st, uredi) {
		$scope.selected_projects.forEach (function (proj) {
			if (uredi=='zbrisi') {
				st.forEach(function(el) {
					proj.track.forEach(function (tr) {
						if (tr.Id_stolpec==el) {
							var ind = proj.track.indexOf(tr);
							proj.track.splice(ind,1);
						}
					});
				});
			}
			
			var trak = [];
			prazniStolpci.forEach (function(pr) {
				var s = stolpecVProjektu(pr,proj);
				if (s[0]==true) {
					trak.push(s[1]);
				}
				else {
					trak.push({'Id_proge':null, 'Id_projekta':null, 'Id_stolpec':pr.Id_stolpec, 'cards':[]});
				}
			});
			proj.track = trak;
			
		});
	}

	// Funkcija, ki vrne seznam id-jev stolpcev, ki so bili na tabli (za old_columns)
	function allOldColumns(seznam) {
		seznam.forEach(function(el) {
			$scope.old_columns.push(el.Id_stolpec);
			if (el['subcolumns'].length!=0) {
				allOldColumns(el.subcolumns);
			}
		});
	}
	
	function updateDeletedCols (col) {
		$scope.deleted_columns.push(col.Id_stolpec);
		$scope.old_columns.splice($scope.old_columns.indexOf(col.Id_stolpec),1);
		if (col.subcolumns.length!=0) {
			col.subcolumns.forEach(function(el) {
				updateDeletedCols(el);
			});
		}
	}

	// Funkcija, ki stolpcem iz seznama odstrani id-je
	function odstraniId(seznam) {
		seznam.forEach(function(el) {
			//console.log(el['Id_stolpec']);
			delete el['Id_stolpec'];
			if (el['subcolumns'].length!=0) {
				odstraniId(el['subcolumns']);
			}
		});
	}

	// Rekurzivna funkcija za dodajanje stolpcev	
	var l = 1;
	var dodajStolpec = function(seznam, stolpecId) {		
		if (direction=='prvi') {
			$scope.new_column.Id_stolpec = 'id0';
			seznam.push($scope.new_column);
		}
		else {
			var len = seznam.length;
			for (var i=0; i<len; i++) {
				if (seznam[i].Id_stolpec==stolpecId) {					
					if (direction=='left') {
						$scope.new_column.Id_stolpec = 'id'+l+'';
						seznam.splice(i,0,$scope.new_column);
						id_column = null;
						direction = null;
						l+=1;
						break;
					}
					else if (direction=='right') {
						$scope.new_column.Id_stolpec = 'id'+l+'';
						seznam.splice(i+1,0,$scope.new_column);
						id_column = null;
						direction = null;
						l+=1;
						break;
					}					
					else {
						if (imaKartice(seznam[i])==true) {
							$scope.message.show = true;
							$scope.message.type = "danger";
							$scope.message.text = "Stolpec vsebuje kartice.";
						}
						else {
							$scope.new_column.Id_stolpec = 'id'+l+'';
							seznam[i].subcolumns.push($scope.new_column);
							id_column = null;
							direction = null;
							l+=1;
						}
						break;
					}
				}
				else {
					dodajStolpec(seznam[i].subcolumns,stolpecId);
				}
			}
		}
	}
	
	// Rekurzivna funkcija za brisanje stolpcev
	var izbrisiStolpec = function(seznam, idStolpca) {
		seznam.forEach(function (el) {
			if (el.Id_stolpec==idStolpca) {
				if (imaKartice(el)==true) {
					$scope.message.show = true;
					$scope.message.type = "danger";
					$scope.message.text = "Stolpec vsebuje kartice.";					
				}
				else {
					var indeks=seznam.indexOf(el);
					seznam.splice(indeks,1);
					if (!$scope.board_new && $scope.old_columns.indexOf(el.Id_stolpec)!=-1) {
						updateDeletedCols(el);
					}
				}
			}
			else {
				if (el.subcolumns.length != 0) {
					izbrisiStolpec(el.subcolumns,idStolpca);
				}
			}
		});
	}
	
	// Rekurzivna funkcija za urejanje stolpcev
	var urediStolpec = function(seznam,stolpecId) {
		var len = seznam.length;
		for (var i=0; i<len; i++) {
			if (seznam[i].Id_stolpec==stolpecId) {
				seznam[i] = $scope.selected_column;
			}
			else {
				urediStolpec(seznam[i].subcolumns, stolpecId);
			}
		}
	}
	
	// Funkcija, ki preveri, ali stolpec oz. njegovi podstolpci vsebujejo kartice
	function imaKartice(s) {
		if (s.subcolumns.length==0) {
			for (i in $scope.selected_projects) {
				var trak = $scope.selected_projects[i].track;
				for (j in trak) {
					if (trak[j].Id_stolpec==s.Id_stolpec) {						
						if (trak[j].cards.length!=0) {
							return true;
						}
					}
				}
			}
			return false;
		}
		else {
			for (i in s.subcolumns) {
				if (imaKartice(s.subcolumns[i])==true) {
					return true;
					break;
				}
			}
			return false;
		}
	}	
	
	// Funkcija, ki preveri, ali ima projekt kartice
	function projektImaKartice(el) {
		for (i in el.track) {
			if (el.track[i].cards.length!=0) {
				return true;
				break;
			}
		}
		return false;
	}
	
	// Funkcija, ki vrne indeks stolpca iz prazniStolpci v Projektu
	function stolpecVProjektu (el, proj) {
		for (i in proj.track) {
			var tr = proj.track[i];
			if (tr.Id_stolpec==el.Id_stolpec) {
				return [true,tr];
				break;
			}
		}
		return [false,null];
	}
	
	function listiStolpca (st) {
		var listi = [];
		function poisci (stolp) {
			if (stolp.subcolumns.length==0) {
				listi.push(stolp.Id_stolpec);
			}
			else {
				listi.push(st.Id_stolpec);
				stolp.subcolumns.forEach(function(el) {
					poisci(el);
				});
			}
		}
		poisci(st);
		return listi;
	}

	// Nastavljanje maksimalne širine za stolpce brez podstolpcev
	$scope.maxSirina = function(stolpec) {
		if (stolpec.subcolumns.length == 0) {
			return '300px';
		}
		else {
			return 'auto';
		}
	}
});

app.controller("modal_new_Ctrl", function ($scope){});
app.controller("modal_edit_Ctrl", function ($scope){});
app.controller("modal_name_Ctrl", function ($scope){});
app.controller("modal_confirm_Ctrl", function ($scope){});
app.controller("modal_select_card_Ctrl", function ($scope){});
app.controller("modal_confirm_card_Ctrl", function ($scope){});
app.controller("modal_info_Ctrl", function ($scope){});
