<link href="<?= base_url('/static/css/style.css') ?>" rel="stylesheet">


<div ng-app="allprojectsApp">
	<div ng-controller="allprojectsCtrl" class="container-fluid p-3">
	
		<div ng-show="message.show" class="alert alert-{{message.type}}">
			{{message.text}}
			<button type="button" class="close" ng-click="hideAlert(message)">&times;</button>
		</div>
		
		<button class="btn btn-primary" ng-click="selectProject(empty)">Dodaj</button>
		<div class="float-right">
			<input type="text" id="search" name="search" ng-model="search" class="form-control" placeholder="Išči">
		</div>

	    <table class="table table-hover my-3">
	    	<thead>
	    		<tr>
	    			<th>#</th>
	    			<th>Šifra</th>
	    			<th>Naziv</th>
	    			<th>Naročnik</th>
	    			<th>Datum začetka</th>
	    			<th>Datum zaključka</th>
	    			<th>Skupina</th>
	    			<th></th>
	    		</tr>
	    	</thead>
			<tbody>
				<tr ng-repeat="project in projects | filter: search" ng-class="{'table-secondary': project.deaktiviran == 1}">
					<td class="font-weight-bold">{{$index + 1}}</td>
					<td>{{project.Sifra_projekta}}</td>
					<td>{{project.Naziv_projekta}}</td>
					<td>{{project.Naziv_narocnika}}</td>
					<td>{{project.Datum_zacetka | date: 'dd. MM. yyyy'}}</td>
					<td>{{project.Datum_konca | date: 'dd. MM. yyyy'}}</td>
					<td>{{groupName(project.Id_skupina)}}</td>
					<td>
						<div class="float-right">
							<button class="btn btn-sm mr-2" ng-show="project.deaktiviran == 0" ng-click="selectProject(project)">Uredi</button>
							<button class="btn btn-danger btn-sm" ng-click="deleteProject(project)">Izbriši</button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<div ng-controller="modalCtrl">
			<div class="modal fade" id="selected" tabindex="-1">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">

						<div class="modal-header mb-3">
							<h5 class="modal-title" ng-show="type=='old'">Uredi</h5>
							<h5 class="modal-title" ng-show="type=='new'">Dodaj</h5>
						</div>

						<div ng-show="modal_message.show" class="alert alert-{{modal_message.type}} mx-3">
							{{modal_message.text}}
							<button type="button" class="close" ng-click="hideAlert(modal_message)">&times;</button>
						</div>							

						<form name="projectForm" novalidate>
							<div class="container">

								<div class="row">
									<div class="form-group col">
	                                    <label for="sifra">Šifra</label>
										<input type="text" id="sifra" name="sifra" ng-model="selected.Sifra_projekta" placeholder="Šifra" class="form-control" required ng-class="{'is-invalid': projectForm.sifra.$invalid && projectForm.sifra.$touched}">
										<div class="invalid-feedback">Obvezno polje.</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col">
	                                    <label for="naziv">Naziv</label>
										<input type="text" id="naziv" name="naziv" ng-model="selected.Naziv_projekta" placeholder="Naziv" class="form-control" required ng-class="{'is-invalid': projectForm.naziv.$invalid && projectForm.naziv.$touched}">
										<div class="invalid-feedback">Obvezno polje.</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col">
	                                    <label for="narocnik">Naročnik</label>
										<input type="text" id="narocnik" name="narocnik" ng-model="selected.Naziv_narocnika" placeholder="Naročnik" class="form-control" required ng-class="{'is-invalid': projectForm.narocnik.$invalid && projectForm.narocnik.$touched}">
										<div class="invalid-feedback">Obvezno polje.</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col">
	                                    <label for="dzac">Datum začetka</label>
										<input type="date" id="dzac" name="dzac" ng-model="selected.Datum_zacetka" placeholder="Datum začetka" class="form-control" required ng-class="{'is-invalid': projectForm.dzac.$invalid && projectForm.dzac.$touched}" max="{{today}}">
										<div class="invalid-feedback">
											<p ng-show="projectForm.dzac.$error.required">Obvezno polje.</p>
											<p ng-show="projectForm.dzac.$error.max">Datum začetka mora biti manjši ali enak današnjemu datumu.</p>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col">
	                                    <label for="dkon">Predvideni datum zaključka</label>
										<input type="date" id="dkon" name="dkon" ng-model="selected.Datum_konca" placeholder="Datum zaključka" class="form-control" required ng-class="{'is-invalid': projectForm.dkon.$invalid && projectForm.dkon.$touched}" min="{{today}}">
										<div class="invalid-feedback">
											<p ng-show="projectForm.dkon.$error.required">Obvezno polje.</p>
											<p ng-show="projectForm.dkon.$error.min">Datum mora biti večji od začetnega in današnjega datuma.</p>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col">
										<label for="group">Razvojna skupina</label>
										<select id="group" name="group" class="selectpicker" data-width="100%" data-live-search="true" ng-model="selected.Id_skupina" ng-options="group.Id_skupina as group.Ime_skupine for group in allgroups">
											<option value="">Dodaj skupino...</option>
										</select>
									</div>
								</div>
								
								<button type="button" ng-click="projectForm.$setUntouched()" class="btn btn-secondary float-right mb-3" data-dismiss="modal">Prekliči</button>
								<button type="button" ng-click="editProject(type); projectForm.$setUntouched()" class="btn btn-primary float-right mb-3 mr-3" ng-disabled="projectForm.$invalid">Shrani</button>					

							</div>
						</form>

					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<script type="text/javascript"< src="<?= base_url('/static/js/allprojects.js') ?>"></script>
