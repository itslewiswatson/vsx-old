<?php
	require_once "core.php";
	require_once "profile_util.php";

	if ($_SERVER["REQUEST_METHOD"] !== "GET") {
		$email = $_POST["email"];
		$name = $_POST["name"];
		$subject = $_POST["subject"];
		$enquiry = $_POST["enquiry"];
		?>
			<title>Contact Us - VSX</title>
			<body>
				<div class="container">
					<div class="row">
						<div class="col-md-6 col-md-offset-3 text-center">
							<h4>Thanks for contacting us about <i><?php echo $subject; ?></i>, <?php echo $name; ?>. We'll get back to you soon.<br><br>
							<small>In the meantime, you should check out the website! Click <a href="index.php">here</a> to go home.</small></h4>
						</div>
					</div>
				</div>
			</body>
		<?php
		exit;
	}

	?>
	<html>
		<title>Contact Us - VSX</title>
		<body>
			<div class="container">
				<div class="row">
					<div class="col-md-10 col-md-offset-1">
						<h3 class="text-center">Have a question or concern? Let us know, and we'll be glad to help!</h3>
						<hr>
						<div class="col-md-5 col-md-offset-3" style="float: none; margin: 0 auto;">
							<form action="contact.php" method="post">
								<input type="text" class="form-control" name="name" placeholder="Name" required>
								<br>
								<?php
									if (isLoggedIn()) {
										global $db;
										$email = $db->query("SELECT email FROM users WHERE usr = '" . $_SESSION["usr"] . "' LIMIT 1")->fetch_assoc()["email"];
										?>
											<input type="email" class="form-control" name="email" placeholder="Email" value=<?php echo $email; ?> readonly required>
										<?php
									}
									else {
										?>
											<input type="email" class="form-control" name="email" placeholder="Email" required>
										<?php
									}
								?>
								<br>
								<input type="text" class="form-control" name="subject" placeholder="Subject" required>
								<br>
								<textarea class="form-control" cols="40" rows="5" name="enquiry" placeholder="Your message here" required></textarea>
								<br>
								<input type="submit" class="form-control btn btn-primary" value="Send">
							</form>
						</div>
					</div>
				</div>
			</div>
		</body>
	</html>
	<?php
?>
