<?php
    require_once "core.php";
	require_once "profile_util.php";
    
	if (!isLoggedIn()) {
		errorVSX("You must be logged in to view settings - <a href=login.php>login</a> or <a href=register.php>sign up</a>!");
		exit;
	}
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$usr = $_SESSION["usr"];
		$avatar = $_POST["avatar"];
		$name = $_POST["name"];
		$website = $_POST["website"];
		$bio = $_POST["bio"];
		
		$update = $db->prepare("UPDATE users SET avatar = ?, name = ?, website = ?, bio = ? WHERE usr = ?");
		$update->bind_param("sssss", $avatar, $name, $website, $bio, $usr);
		$update->execute();
		successVSX("Your profile has been succesfully updated - click <a href='profile.php'>here</a> to view", 30);
		echo "<br>";
	}
	
	$profile = $_SESSION["usr"];
	$profileData = $db->query(
		"SELECT *
		FROM users
		WHERE usr = '" . $profile . "'
		LIMIT 1"
	);
	$result = $profileData->fetch_assoc();
	
	?>
	<div class="container">
		<div class="row">
			<div class="col-md-4 col-md-offset-4 avatar-display">
				<div class="thumbnail profile">
					<form action="settings.php" class="form" method="post">
						<div style="padding-left: 10px; padding-right: 10px;">
							<?php
								if (isset($result["avatar"]) && !empty($result["avatar"])) {
									?>
										<img class="img-responsive" src=<?php echo $result["avatar"]; ?> >
									<?php
								}
							?>
							<input type="text" class="form form-control" name="avatar" placeholder="Avatar URL" value="<?php echo $result["avatar"]; ?>">
							<hr>
						</div>
						<div style="padding-left: 10px; padding-right: 10px; padding-bottom: 10px;">
						<h3><?php echo $profile; ?> <small>(<i>Your user name cannot be changed</i>)</small></h3>
						<?php
							if (isset($result["name"])) {
								?>
									<input type="text" class="form-control" name="name" placeholder="Name" value="<?php echo $result["name"]; ?>">
								<?php
							}
							else {
								?>
									<input type="text" class="form-control" name="name" placeholder="Name">
								<?php
							}
							?>
							<br>
							<?php
							if (isset($result["website"]) && !empty($result["website"])) {
								?>
									<input type="text" class="form-control" name="website" placeholder="Website URL" value="<?php echo $result["website"]; ?>">
								<?php
							}
							else {
								?>
									<input type="text" class="form-control" name="website" placeholder="Website URL">
								<?php
							}
							?>
							<br>
							<?php
							if (isset($result["bio"]) && !empty($result["avatar"])) {
								?>
									<textarea class="form-control" cols="40" rows="5" name="bio" placeholder="Bio"><?php echo $result["bio"]; ?></textarea>
								<?php
							}
							else {
								?>
									<textarea class="form-control" cols="40" rows="5" name="bio" placeholder="Bio"></textarea>
								<?php
							}
							?>
							<br>
							<input type="submit" value="Update" class="form-control btn btn-primary">
							<?php
						?>
					</form>
				</div>
			</div>
		</div>
	<?php
?>
