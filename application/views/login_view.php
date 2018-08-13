<link href="<?= base_url('/static/css/style.css') ?>" rel="stylesheet">

<div ng-app="loginApp" class="container h-100">
	<div ng-controller="loginCtrl" class="h-100">

		<div class="row align-items-center h-100">
			<div class="col-6 mx-auto">

					<img src="<?= base_url('/static/img/logo.png') ?>" class="img-fluid"></img>
					<form name="loginForm">
						<div class="card">
							<div class="card-body">

								<div ng-show="message.show" class="alert alert-{{message.type}}">
									{{message.text}}
									<button type="button" class="close" ng-click="hideAlert(message)">&times;</button>
								</div>

								<div class="row">
									<div class="input-group input-group-lg col mb-3">
										<div class="input-group-prepend">
											<span class="input-group-text">@</span>
										</div>
										<input type="email" id="email" name="email" ng-model="user.email" placeholder="Email" class="form-control" required ng-class="{'is-invalid': loginForm.email.$invalid && loginForm.email.$touched}">
										<div class="invalid-feedback">
											<p ng-show="loginForm.email.$error.required">Obvezno polje.</p>
											<p ng-show="loginForm.email.$error.email">Vnesite veljaven email naslov.</p>
										</div>
									</div>
								</div>

								<div class="input-group input-group-lg mb-3">
									<input type="password" id="password" name="password" class="form-control" ng-model="user.password" placeholder="Geslo" required ng-class="{'is-invalid': loginForm.password.$invalid && loginForm.password.$touched}">
									<div class="invalid-feedback">Obvezno polje.</div>
								</div>

								<button ng-click="authenticate()" ng-disabled="loginForm.$invalid" class="btn btn-primary btn-lg btn-block">Prijava</button>

							</div>
						</div>
					</form>

			</div>
		</div>

	</div>
</div>

<script type="text/javascript" src="<?= base_url('/static/js/login.js') ?>"></script>