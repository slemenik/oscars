<link href="<?= base_url('/static/css/style.css') ?>" rel="stylesheet">
<link href="<?= base_url('/static/css/toggle.css') ?>" rel="stylesheet">

<div ng-app="allusersApp">
	<div ng-controller="allusersCtrl" class="container-fluid p-3">

		<div ng-show="message.show" class="alert alert-{{message.type}}">
			{{message.text}}
			<button type="button" class="close" ng-click="hideAlert(message)">&times;</button>
		</div>
	
		<button class="btn btn-primary" ng-click="selectUser(empty)">Dodaj</button>
		<div class="float-right">
			<input type="text" id="search" name="search" ng-model="search" class="form-control" placeholder="Išči">
		</div>

		<table class="table table-hover my-3">
			<thead>
				<tr>
					<th>#</th>
					<th>Ime</th>
					<th>Priimek</th>
					<th>Email</th>
					<th>Vloge</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr ng-repeat="user in users | filter: search" ng-class="{'table-secondary': user.Aktiven == 0}">
					<td class="font-weight-bold">{{$index + 1}}</td>
					<td>{{user.Ime}}</td>
					<td>{{user.Priimek}}</td>
					<td>{{user.Email}}</td>
					<td>
						<span ng-show="user.Admin" class="badge badge-danger">Admin</span>
						<span ng-repeat="role in user.roles" class="badge" ng-class="{'badge-light': role == 3, 'badge-dark': role == 1, 'badge-success': role == 2}">{{roles_dict[role]}}</span>
					</td>
					<td>
						<div class="float-right">
							<button class="btn btn-sm mr-2" ng-click="selectUser(user)" ng-show="user.Aktiven == 1">Uredi</button>
							<button class="btn btn-danger btn-sm" ng-click="deleteUser(user)" ng-show="user.Aktiven == 1">Izbriši</button>
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

						<form name="userForm" novalidate>
							<div class="container">

								<div class="row">
									<div class="form-group col">
										<label for="ime">Ime</label>
										<input type="text" id="ime" name="ime" ng-model="selected.Ime" placeholder="Ime" class="form-control" required ng-class="{'is-invalid': userForm.ime.$invalid && userForm.ime.$touched}">
										<div class="invalid-feedback">Obvezno polje.</div>
									</div>
									<div class="form-group col">
										<label for="priimek">Priimek</label>
										<input type="text" id="priimek" name="priimek" ng-model="selected.Priimek" placeholder="Priimek" class="form-control" required ng-class="{'is-invalid': userForm.priimek.$invalid && userForm.priimek.$touched}">
										<div class="invalid-feedback">Obvezno polje.</div>
									</div>
								</div>

								<label for="email">Email</label>
								<div class="row">									
									<div class="input-group col mb-3">																		
										<div class="input-group-prepend">
											<span class="input-group-text">@</span>
										</div>										
										<input type="email" id="email" name="email" ng-model="selected.Email" placeholder="Email" class="form-control" required ng-class="{'is-invalid': userForm.email.$invalid && userForm.email.$touched}">
										<div class="invalid-feedback">
											<p ng-show="userForm.email.$error.required">Obvezno polje.</p>
											<p ng-show="userForm.email.$error.email">Vnesite veljaven email naslov.</p>
										</div>
									</div>
								</div>
								
								<div class="row">
									<div ng-switch="type">
										<div ng-switch-when="new">										
											<div class="form-group col">
												<label for="password">Geslo</label>
												<input type="password" id="password" name="password" ng-model="$parent.$parent.password" placeholder="Geslo" class="form-control" required ng-class="{'is-invalid': userForm.password.$invalid && (userForm.password.$touched || userForm.$submitted)}">
												<div class="invalid-feedback">
													<p ng-show="userForm.password.$error.required">Obvezno polje.</p>
												</div>
											</div>											
										</div>
										<div ng-switch-when="old">
											<div class="form-group col">
												<label for="password">Geslo</label>
												<input type="password" id="password" name="password" ng-model="$parent.$parent.password" placeholder="Geslo" class="form-control">
											</div>																		
										</div>
									</div>
									<div class="form-group col">
										<label for="ipasswordCheck">Potrdi geslo</label>
										<input type="password" id="passwordCheck" name="passwordCheck" ng-model="passwordCheck" placeholder="Potrdi geslo" class="form-control" ng-required="$parent.password != null" ng-disabled="$parent.password == null" ng-pattern="$parent.password" ng-class="{'is-invalid': userForm.passwordCheck.$invalid && (userForm.passwordCheck.$touched || userForm.$submitted)}">
										<div class="invalid-feedback">
											<p ng-show="userForm.passwordCheck.$error.pattern">Gesli se ne ujemata.</p>
											<p ng-show="userForm.passwordCheck.$error.required">Potrdite geslo.</p>
										</div>
									</div>
								</div>						

								<div class="card mb-3">
									<ul class="list-group list-group-flush">
										<li class="list-group-item">
											Administrator
											<span class="float-right">
												<label class="switch">
													<input type="checkbox" ng-checked="selected.Admin" ng-model="selected.Admin">
													<span class="slider round red"></span>
												</label>
											</span>
										</li>
									</ul>
								</div>

								<div class="card mb-3">
									<ul class="list-group list-group-flush">
										<li ng-repeat="role in roles" class="list-group-item">
											{{role.Naziv}}
											<span class="float-right">
												<label class="switch">
													<input type="checkbox" ng-checked="mozne_vloge_dict[role.Id_vloga]" ng-model="mozne_vloge_dict[role.Id_vloga]">
													<span class="slider round"></span>
												</label>
											</span>
										</li>
									</ul>
								</div>					

								<button type="button" ng-click="userForm.$setUntouched()" class="btn btn-secondary float-right mb-3" data-dismiss="modal">Prekliči</button>
								<button type="button" ng-click="editUser(type); userForm.$setUntouched()" class="btn btn-primary float-right mb-3 mr-3" ng-disabled="userForm.$invalid">Shrani</button>

							</div>
						</form>

					</div>
				</div>
			</div>
		</div>	
	</div>
</div>

<script type="text/javascript" src="<?= base_url('/static/js/allusers.js') ?>"></script>