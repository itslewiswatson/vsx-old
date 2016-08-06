<?php
	require "core.php";
	_header();
	buttons();

	function celebrate() {
		?>
		<div class="container">
			<div class="row">
				<div class="text-center col-md-6 col-md-offset-3">
					<p><?php echo "Welcome, " . $_SESSION["usr"]; ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	if (isset($_SESSION["usr"])) {
		celebrate();
	}

	_footer();
?>
