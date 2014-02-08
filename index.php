<?php
	session_start();
	require_once('hidden/helper_classes/DatabaseConnection.php');

	$errorMessage = "";
	
	if (isset($_POST["username"]))
	{
		$conn = (new DatabaseConnection)->getConn();
		
		$query = 'SELECT * FROM user WHERE username=:username';
		$valuesArray = array();
		$valuesArray[":username"] = $_POST["username"];
		
		$stmt = $conn->prepare($query);
		$stmt->execute($valuesArray);
		$records = $stmt->fetchAll();
		
		if (count($records) == 0)
		{
			output_header();
			show_form("The user you have entered was not found");
		} else {
			if (password_verify_with_rehash($_POST["pwd"], $records[0]["password_hash"]))
			{
				$_SESSION['yubikey_prefix'] = $records[0]["yubikey_prefix"];
				output_header();
				?>
					<img src="images/yubikey.png" alt="" />
					<br/>One time password: <input type="text" name="OTP"/>
					<br/><input type="submit"/>	
				<?php
				
			} else {
				$errorMessage = "The password you have entered is incorrect";
			}
		}
	} elseif (isset($_POST["OTP"]))
	{
		require_once 'hidden/helper_classes/Yubico.php';
		$yubikey = parse_ini_file('hidden/git_ignore/yubikey.ini');
		$yubi = new Auth_Yubico($yubikey['client_id'], $yubikey['secret']);
		
		$auth = $yubi->verify($_POST["OTP"]);
		
		if (PEAR::isError($auth) || $_SESSION['yubikey_prefix'] != $yubi->parsePasswordOTP($_POST["OTP"])['prefix']) {
			
			echo "The one time Yubikey password you have entered is invalid for the specified user.";
			//print "<p>Authentication failed: " . $auth->getMessage();
			//print "<p>Debug output from server: " . $yubi->getLastResponse();
		} else {
			print "<p>You are authenticated!";
			$_SESSION['authenticated'] = true;
		}
	}
	else
	{
		output_header();
		show_form(null);
	}

	function output_header()
	{
		?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
					<title>Welcome!</title>
				</head>
				<body>
					<form action="index.php" method="post">
						<p>	
		<?php
	}
	
	function show_form($error)
	{
		?>
			Username: <input type="text" name="username"/>
			<br />Password: <input type="password" name="pwd" />
			<br/><input type="submit"/>	
		<?php
	}
	
	function password_verify_with_rehash($password, $hash) {
		if (!password_verify($password, $hash)) {
			return false;
		}

		if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
			$hash = password_hash($password, PASSWORD_DEFAULT);
			// update hash in database
		}
		return true;
	}
?>

			</p>
		</form>
	</body>
</html>
