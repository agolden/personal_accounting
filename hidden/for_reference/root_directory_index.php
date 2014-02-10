<?php
	session_start();
	
	if (isset($_SESSION['authenticated']))
	{
		header( 'Location: thebasics.php' );
	}
	else
	{
		header( 'Location: personal_accounting/login.php?dest=%2Fthebasics.php' );
	}
?>