<link href="<?= base_url('/static/css/style.css') ?>" rel="stylesheet">
<link href="<?= base_url('/static/css/toggle.css') ?>" rel="stylesheet">

<div ng-app="allgroupsApp">
	<div ng-controller="allgroupsCtrl" class="container-fluid p-3">

		<div ng-show="message.show" class="alert alert-{{message.type}}">
			{{message.text}}
			<button type="button" class="close" ng-click="hideAlert(message)">&times;</button>
		</div>
		
		<button class="btn btn-primary" ng-click="selectGroup(empty)">Dodaj</button>
		<div class="float-right">
			<input type="text" id="search" name="search" ng-model="search" class="form-control" placeholder="Išči">
		</div>

	    <table class="table table-hover my-3">
	    	<thead>
	    		<tr>
	    			<th>#</th>
	    			<th>Ime</th>
	    			<th>Člani</th>
	    			<th>Projekti</th>
	    			<th></th>
	    		</tr>
	    	</thead>
			<tbody>
				<tr ng-repeat="group in groups | filter: search">
					<td class="font-weight-bold">{{$index + 1}}</td>
					<td>{{group.Ime_skupine}}</td>
					<td>
						<div ng-repeat="member in group.Members">
							<div ng-class="{'text-muted': !member.Activen}">
								{{member.Ime + ' ' + member.Priimek}}
								<span ng-show="member.Activen" ng-repeat="role in member.Konkretne_vloge" class="badge" ng-class="{'badge-light': role == 3, 'badge-dark': role == 1, 'badge-success': role == 2}">{{roles_dict[role]}}</span>
								<span ng-show="member.Activen"><small class="text-secondary">Dodan: {{member.Datum_zacetka | date: 'dd. MM. yyyy'}} ob {{member.Datum_zacetka | date: 'H:mm'}}</small></span>
								<span ng-show="!member.Activen"><small class="text-secondary">Izbrisan: {{member.Datum_konca | date: 'dd. MM. yyyy'}} ob {{member.Datum_konca | date: 'H:mm'}}</small></span>
							</div>
						</div>					
					</td>
					<td>
						<div ng-repeat="project in group.Projects">
							{{project.Sifra_projekta}}
						</div>
					</td>
					<td>
						<span class="float-right">
							<button class="btn btn-sm mr-2" ng-click="selectGroup(group)">Uredi</button>
							<button class="btn btn-danger btn-sm" ng-click="deleteGroup(group)">Izbriši</button>
						</span>
					</td>
				</tr>
			</tbody>
		</table>

		<div ng-controller="modalCtrl">
			<div class="modal fade" id="selected" tabindex="-1">
				<div class="modal-dialog modal-dialog-centered modal-lg">
					<div class="modal-content">

						<div class="modal-header mb-3">
							<h5 class="modal-title" ng-show="type=='old'">Uredi</h5>
							<h5 class="modal-title" ng-show="type=='new'">Dodaj</h5>
						</div>

						<div ng-show="modal_message.show" class="alert alert-{{modal_message.type}} mx-3">
							{{modal_message.text}}
							<button type="button" class="close" ng-click="hideAlert(modal_message)">&times;</button>
						</div>							

						<form name="groupForm" novalidate>
							<div class="container">

								<div class="row">
									<div class="form-group col">
										<label for="ime">Ime</label>
										<input type="text" id="ime" name="ime" ng-model="selected.Ime_skupine" placeholder="Ime skupine" class="form-control" required ng-class="{'is-invalid': groupForm.ime.$invalid && groupForm.ime.$touched}">
										<div class="invalid-feedback">Obvezno polje.</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col">
										<label for="members">Člani</label>
										<select id="members" name="members" class="selectpicker" data-width="100%" data-live-search="true" ng-model="selectedUser" ng-options="user.Ime + ' ' + user.Priimek for user in users_minus" ng-change="addMember()">
											<option style="display:none" value="">Dodaj člana...</option>
										</select>
									</div>
								</div>

								<div class="row">
									<div class="form-group col">
										<div ng-repeat="member in selected.Members">
											<div class="card mb-1" ng-show="member.Activen">
												<div class="card-header bg-transparent">
													{{member.Ime + ' ' + member.Priimek}}
													&nbsp;	
													<span ng-repeat="(role_id, role_val) in konkretne_vloge_dict[member.Id_uporabnik]" class="badge" ng-show="role_val" ng-class="{'badge-light': role_id == 3, 'badge-dark': role_id == 1, 'badge-success': role_id == 2}">{{roles_dict[role_id]}}</span>									
													<span class="float-right">
														<button class="btn btn-danger btn-sm" ng-click="removeMember(member)">Izbriši</button>
													</span>
													<span class="float-right">
														<button class="btn btn-sm mr-2" data-toggle="collapse" data-target="#roles{{$index}}">Vloge</button>
													</span>
												</div>
												<div id="roles{{$index}}" class="collapse">
													<ul class="list-group list-group-flush">
														<li class="list-group-item" ng-repeat="role in member.Mozne_vloge">{{roles_dict[role]}}
															<span class="float-right">
																<label class="switch">
																	<input type="checkbox" ng-checked="konkretne_vloge_dict[member.Id_uporabnik][role]" ng-model="konkretne_vloge_dict[member.Id_uporabnik][role]">
																	<span class="slider round"></span>
																</label>
															</span>
														</li>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>

								<button type="button" ng-click="groupForm.$setUntouched()" class="btn btn-secondary float-right mb-3" data-dismiss="modal">Prekliči</button>
								<button type="button" ng-click="editGroup(type); groupForm.$setUntouched()" class="btn btn-primary float-right mb-3 mr-3" ng-disabled="groupForm.$invalid">Shrani</button>

							</div>
						</form>

					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<script type="text/javascript"< src="<?= base_url('/static/js/allgroups.js') ?>"></script>
