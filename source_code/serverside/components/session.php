<?php require_once("serverside/functions/session.php");
/**
* If sessions are used in the page, please include this file first at the top.
*/

session_start();

$session_is_authenticated = FALSE;
$session_is_admin = FALSE;

// Check if client session is authenticated (Logged in).
if (session_isauth() === TRUE) {
	// Client is authenticated.
	$session_is_authenticated = TRUE;
	$session_is_admin = $_SESSION["is_admin"];
} else {
	// Client is not authenticated.
	if (isset($_SESSION["is_register"]) === TRUE && $_SESSION["is_register"] === TRUE) {
		// Registration going on.
	} else if (defined("REQUIRE_SESSION") === FALSE || REQUIRE_SESSION === FALSE) {
		// Page does not require session.
		// Unset and destroy session instance.
		//session_end();
	}
	
	if (defined("REQUIRE_AUTH") === TRUE && REQUIRE_AUTH === TRUE) {
		// Page requires authentication.
		header("HTTP/1.1 401 Unauthorised");
		header("Location: login.php");
		die("Please login to access the requested resource.");
	}
}