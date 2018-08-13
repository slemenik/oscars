<link href="<?= base_url('/static/css/style.css') ?>" rel="stylesheet">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<a class="navbar-brand" href="<?= base_url('/home') ?>">Kanban</a>
		<div class="collapse navbar-collapse" id="navbarNavAltMarkup">

			<div class="navbar-nav mr-auto">
	            <?php if (isset($_SESSION['user']) && $_SESSION['user']['Admin']): ?>
	                <a class="nav-item nav-link <?php if (strtolower($this->uri->segment(1))=='users') echo 'active'; ?>" href="<?= base_url('/users') ?>">Uporabniki</a>
	            <?php endif; ?>
	            <?php if (isset($_SESSION['user']) && in_array(1, $_SESSION['user']['roles'])): ?>
				    <a class="nav-item nav-link <?php if (strtolower($this->uri->segment(1))=='groups') echo 'active'; ?>" href="<?= base_url('/Groups') ?>">Skupine</a>
				    <a class="nav-item nav-link <?php if (strtolower($this->uri->segment(1))=='projects') echo 'active'; ?>" href="<?= base_url('/Projects') ?>">Projekti</a>
	            <?php endif; ?>
				<a class="nav-item nav-link <?php if (strtolower($this->uri->segment(1))=='boards') echo 'active'; ?>" href="<?= base_url('/Boards') ?>">Table</a>
			</div>

			<?php if (isset($_SESSION['user'])): ?>
				<div class="dropdown">
					<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown">
						<?php echo $_SESSION['user']['Ime'] . ' ' . $_SESSION['user']['Priimek']; ?>
					</button>
					<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">						
						<?php if (isset($_SESSION['user']) && $_SESSION['user']['Admin']): ?>
							<div class="dropdown-item disabled"><span class="badge badge-danger">Admin</span></div>
						<?php endif; ?>						
						<?php if (isset($_SESSION['user']) && in_array(2, $_SESSION['user']['roles'])): ?>
							<div class="dropdown-item disabled"><span class="badge badge-success">Product Owner</span></div>
						<?php endif; ?>
						<?php if (isset($_SESSION['user']) && in_array(1, $_SESSION['user']['roles'])): ?>
							<div class="dropdown-item disabled"><span class="badge badge-dark">Kanban Master</span></div>
						<?php endif; ?>
						<?php if (isset($_SESSION['user']) && in_array(3, $_SESSION['user']['roles'])): ?>
							<div class="dropdown-item disabled"><span class="badge badge-light">Razvijalec</span></div>
						<?php endif; ?>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="<?= base_url('/home/logout') ?>">Odjava</a>
					</div>
				</div>
			<?php endif; ?>
			<!-- <button class="nav-item btn btn-secondary ml-2" href="<?= base_url('/home/logout') ?>">Odjava</button> -->

		</div>
</nav>