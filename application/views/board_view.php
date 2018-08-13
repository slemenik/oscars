<link href="<?= base_url('/static/css/style.css') ?>" rel="stylesheet">

<div class="container-fluid p-3">
	<div class="vrstica">
		<div class="stolpec">
			<div class="card h-100" style="min-width: 200px"><div class="card-body">Header</div></div>
		</div>
		<div class="stolpec">
			<div class="card" style="min-width: 200px"><div class="card-body">Header</div></div>			
			<div class="vrstica">
				<div class="stolpec">
					<div class="card h-100" style="min-width: 200px"><div class="card-body">Header</div></div>					
				</div>
				<div class="stolpec">
					<div class="card h-100" style="min-width: 200px"><div class="card-body">Header</div></div>
				</div>
				<div class="stolpec">
					<div class="card h-100" style="min-width: 200px"><div class="card-body">Header</div></div>
				</div>
			</div>
		</div>
		<div class="stolpec">
			<div class="card h-100" style="min-width: 200px"><div class="card-body">Header</div></div>
		</div>
		<div class="stolpec">
			<div class="card h-100" style="min-width: 200px"><div class="card-body">Header</div></div>
		</div>
		<div class="stolpec">
			<div class="card h-100" style="min-width: 200px"><div class="card-body">Header</div></div>
		</div>

	</div>
	<div class="vrstica">
		<div class="card h-100" style="min-width: 200px; min-height: 600px"><div class="card-body">
			<div class="stolpec"></div>
		</div></div>
		<div class="card h-100" style="min-width: 200px; min-height: 600px"><div class="card-body">
			<div class="stolpec"></div>
		</div></div>
		<div class="card h-100" style="min-width: 200px; min-height: 600px"><div class="card-body">
			<div class="stolpec"></div>
		</div></div>
		<div class="card h-100" style="min-width: 200px; min-height: 600px"><div class="card-body">
			<div class="stolpec"></div>
		</div></div>
		<div class="card h-100" style="min-width: 200px; min-height: 600px"><div class="card-body">
			<div class="stolpec"></div>
		</div></div>
		<div class="card h-100" style="min-width: 200px; min-height: 600px"><div class="card-body">
			<div class="stolpec"></div>
		</div></div>
		<div class="card h-100" style="min-width: 200px; min-height: 600px"><div class="card-body">
			<div class="stolpec"></div>
		</div></div>
	</div>
	<div class="vrstica">
	</div>
</div>

<style>
.vrstica {
	display: flex;
	flex-wrap: nowrap;
	/*background-color: LightGray;*/
}
.stolpec {
  display: flex;
  flex-direction: column;
  /*background-color: MediumSeaGreen;*/
}
.card {
	margin: 2px;
	padding: 0px;
}

</style>