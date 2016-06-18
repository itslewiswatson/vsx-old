<?php
	include "/src/templates/header.html";
	require "core.php";
	
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
	
	include "/src/templates/footer.html";
?>