<?php
	session_start();
	require_once('hidden/helper_classes/DatabaseConnection.php');

	$errorMessage = "";
	
	$dest = dirname($_SERVER['REQUEST_URI']);
	if(isset($_GET['dest'])) { $dest = $_GET['dest']; }
	elseif(isset($_POST['dest'])) { $dest = $_POST['dest']; }
	
	function base_url()
	{
		$s = $_SERVER;
		$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
		$sp = strtolower($s['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		$port = $s['SERVER_PORT'];
		$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
		$host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];
		return $protocol . '://' . $host . $port;
	}
	
	if (isset($_SESSION['authenticated']))
	{
		header( 'Location: thebasics.php' );
	}
	elseif (isset($_POST["username"]))
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
			show_form("The user you have entered was not found", $dest);
		} else {
			if (password_verify_with_rehash($_POST["pwd"], $records[0]["password_hash"]))
			{
				$_SESSION['yubikey_prefix'] = $records[0]["yubikey_prefix"];
				output_header();
				?>
					<img src="images/yubikey.png" alt="" />
					<br/>One time password: <input type="text" name="OTP"/>
					<br/><input type="submit" value="Submit"/>	<input type="hidden" name="dest" value="<?=$dest?>" />
				<?php
				
			} else {
				output_header();
				show_form("The password you have entered is incorrect", $dest);
			}
		}
	} elseif (isset($_POST["OTP"]))
	{
		require_once 'hidden/helper_classes/Yubico.php';
		$yubikey = parse_ini_file($_SERVER["DOCUMENT_ROOT"] . '/../git_ignore/yubikey.ini');
		$yubi = new Auth_Yubico($yubikey['client_id'], $yubikey['secret']);
		
		$auth = $yubi->verify($_POST["OTP"]);
		
		if (PEAR::isError($auth) || $_SESSION['yubikey_prefix'] != $yubi->parsePasswordOTP($_POST["OTP"])['prefix']) {
			output_header();
			echo "The one time Yubikey password you have entered is invalid for the specified user.";
		} else {
			$_SESSION['authenticated'] = true;
			header( 'Location: ' . base_url() . urldecode($dest));
		}
	}
	else
	{
		output_header();
		show_form(null, $dest);
	}

	function output_header()
	{
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<title>Welcome!</title>
		<link rel="stylesheet" type="text/css" href="lib/style.css" />
	</head>
	<body>
		<div class="mainbody">
		<form action="login.php" method="post">
			<p>	
<?php
	}
	
	function show_form($error, $dest)
	{
?>
			Username: <input type="text" name="username"/>
			<br />Password: <input type="password" name="pwd" />
			<br/><input type="submit" value="Submit"/>	
			<input type="hidden" name="dest" value="<?=$dest?>" />
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
		</div>
	</body>
</html>
