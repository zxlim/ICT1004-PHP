<?php declare(strict_types=1);
/**
* @license		MIT License
* @copyright	Copyright (c) 2019 Zhao Xiang.
*
* @author		ZHAO XIANG LIM	(developer@zxlim.xyz)
*/

if (defined("CLIENT") === FALSE) {
	/**
	* Ghetto way to prevent direct access to "include" files.
	*/
	http_response_code(404);
	die();
}

require_once("serverside/private/dbpasswd.php");

function get_conn(): mysqli {
	/**
	* A function to create a connection to a MySQL server.
	* Connection parameters are defined in `dbpasswd.php`.
	*
	* @return 	mysqli	$conn	The MySQLi connection object.
	*/
	$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

	if (mysqli_connect_errno()) {
		die("[!] Failed to connect to database: " . mysqli_connect_errno());
	}

	return $conn;
}