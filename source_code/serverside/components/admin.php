<?php
##############################
# admin.php
# Logic for processing FastTrade
# administrative actions.
##############################

if (defined("CLIENT") === FALSE) {
	/**
	* Ghetto way to prevent direct access to "include" files.
	*/
	http_response_code(404);
	die();
}

require_once("serverside/functions/validation.php");
require_once("serverside/functions/security.php");
require_once("serverside/functions/database.php");

$valid_request = FALSE;
$valid_response = TRUE;
$response_error = 200;
$response_message = NULL;

if ($session_is_authenticated === FALSE || $session_is_admin === FALSE) {
	// No privilege to access.
	$response_error = 403;
} else if ($_SERVER["REQUEST_METHOD"] === "POST") {
	// POST validation.
	if (isset($_POST["action"]) === TRUE) {
		 if ($_POST["action"] === "update_user") {
			// UPDATE: user.
			if (isset($_POST["id"]) && validate_int($_POST["id"]) === TRUE &&
				isset($_POST["name"]) && validate_notempty($_POST["name"]) === TRUE &&
				isset($_POST["loginid"]) && validate_notempty($_POST["loginid"]) === TRUE &&
				isset($_POST["email"]) && validate_notempty($_POST["email"]) === TRUE &&
				isset($_POST["gender"]) && validate_notempty($_POST["gender"]) === TRUE &&
				isset($_POST["suspended"]) && validate_int($_POST["suspended"]) === TRUE &&
				((int)($_POST["suspended"]) === 0 || (int)($_POST["suspended"]) === 1) &&
				isset($_POST["admin"]) && validate_int($_POST["admin"]) === TRUE &&
				((int)($_POST["admin"]) === 0 || (int)($_POST["admin"]) === 1)
			) {
				$valid_request = TRUE;
			}
		} else if ($_POST["action"] === "update_category") {
			// UPDATE: category.
			if (isset($_POST["id"]) && validate_int($_POST["id"]) === TRUE &&
				isset($_POST["name"]) && validate_notempty($_POST["name"]) === TRUE
			) {
				$valid_request = TRUE;
			}
		} else if ($_POST["action"] === "update_location") {
			// UPDATE: location.
			if (isset($_POST["id"]) && validate_int($_POST["id"]) === TRUE &&
				isset($_POST["stn_name"]) && validate_notempty($_POST["stn_name"]) === TRUE
			) {
				$valid_request = TRUE;
			}
		} else if (
			$_POST["action"] === "delete_user" ||
			$_POST["action"] === "delete_category" ||
			$_POST["action"] === "delete_location"
		) {
			// DELETE: user, category, location.
			if (validate_int($_POST["id"]) === TRUE) {
				$valid_request = TRUE;
			}
		} else if ($_POST["action"] === "insert_user") {
			// INSERT: user.
			if (isset($_POST["name"]) && validate_notempty($_POST["name"]) === TRUE &&
				isset($_POST["loginid"]) && validate_notempty($_POST["loginid"]) === TRUE &&
				isset($_POST["password"]) && validate_notempty($_POST["password"]) === TRUE &&
				isset($_POST["email"]) && validate_notempty($_POST["email"]) === TRUE &&
				isset($_POST["suspended"]) && validate_int($_POST["suspended"]) === TRUE &&
				((int)($_POST["suspended"]) === 0 || (int)($_POST["suspended"]) === 1) &&
				isset($_POST["admin"]) && validate_int($_POST["admin"]) === TRUE &&
				((int)($_POST["admin"]) === 0 || (int)($_POST["admin"]) === 1)
			) {
				$valid_request = TRUE;
			}
		} else if ($_POST["action"] === "insert_category") {
			// INSERT: category.
			if (isset($_POST["name"]) && validate_notempty($_POST["name"]) === TRUE) {
				$valid_request = TRUE;
			}
		} else if ($_POST["action"] === "insert_location") {
			// INSERT: location.
			if (isset($_POST["stn_name"]) && validate_notempty($_POST["stn_name"]) === TRUE) {
				$valid_request = TRUE;
			}
		}
	}
}

if ($valid_request === FALSE && $response_error !== 200) {
	switch ($response_error) {
		case 400:
			header("HTTP/1.1 400 Bad Request");
			break;
		case 401:
			header("HTTP/1.1 401 Unauthorised");
			header("Location: login.php");
			break;
		case 403:
			header("HTTP/1.1 403 Forbidden");
			header("Location: login.php");
			break;
		case 405:
			header("HTTP/1.1 405 Method Not Allowed");
			break;
	}

	die(); // Prevent further execution of PHP code.
}

$sql = NULL;
$rows_users = $rows_categories = $rows_locations = array();

$conn = get_conn();

if ($_SERVER["REQUEST_METHOD"] === "POST" && $valid_request === TRUE) {
	// POST request method.
	if ($_POST["action"] === "update_category") {
		// UPDATE: category.
		$id = html_safe($_POST["id"], TRUE);
		$cat_name = html_safe($_POST["name"], TRUE);

		$sql = "UPDATE category
				SET name = ?
				WHERE id = ?";

		if ($query = $conn->prepare($sql)) {
			$query->bind_param("si", $cat_name, $id);
			if (!$query->execute()) {
				$valid_response = FALSE;
				$response_message = "Failed to update category.";
			} else {
				$response_message = "Updated category successfully.";
			}
			$query->close();
		}
	} else if ($_POST["action"] === "update_location") {
		// UPDATE: location.
		$id = html_safe($_POST["id"], TRUE);
		$stn_code = validate_notempty($_POST["stn_code"]) ? html_safe($_POST["stn_code"], TRUE) : NULL;
		$stn_line = validate_notempty($_POST["stn_line"]) ? html_safe($_POST["stn_line"], TRUE) : NULL;
		$stn_name = html_safe($_POST["stn_name"], TRUE);

		$sql = "UPDATE locations
				SET stn_code = ?, stn_name = ?, stn_line = ?
				WHERE id = ?";

		if ($query = $conn->prepare($sql)) {
			$query->bind_param("sssi",
				$stn_code, $stn_name, $stn_line, $id
			);
			if (!$query->execute()) {
				$valid_response = FALSE;
				$response_message = "Failed to update location.";
			} else {
				$response_message = "Updated location successfully.";
			}
			$query->close();
		}
	} else if ($_POST["action"] === "update_user") {
		// UPDATE: user.
		$id = html_safe($_POST["id"], TRUE);

		$user_name = html_safe($_POST["name"], TRUE);
		$user_loginid = html_safe($_POST["loginid"], TRUE);
		$user_email = html_safe($_POST["email"], TRUE);
		$user_gender = html_safe($_POST["gender"], TRUE);
		$user_mobile = validate_notempty($_POST["mobile"]) ? html_safe($_POST["mobile"], TRUE) : NULL;
		$user_bio = validate_notempty($_POST["bio"]) ? html_safe($_POST["bio"], TRUE) : NULL;
		$user_suspended = html_safe($_POST["suspended"], TRUE);
		$user_admin = html_safe($_POST["admin"], TRUE);

		$sql = "UPDATE user
				SET name = ?, loginid = ?, email = ?, gender = ?, mobile = ?, bio = ?, suspended = ?, admin = ?
				WHERE id = ?";

		if ($query = $conn->prepare($sql)) {
			$query->bind_param("ssssssiii",
				$user_name, $user_loginid, $user_email, $user_gender, $user_mobile, $user_bio, $user_suspended, $user_admin, $id
			);
			if (!$query->execute()) {
				$valid_response = FALSE;
				$response_message = "Failed to update user.";
			} else {
				$response_message = "Updated user successfully.";
			}
			$query->close();
		}
	} else if ($_POST["action"] === "delete_user") {
		// DELETE: user.
		$id = html_safe($_POST["id"], TRUE);

		$sql = "DELETE FROM user
				WHERE id = ?";

		if ($query = $conn->prepare($sql)) {
			$query->bind_param("i", $id);
			if (!$query->execute()) {
				$valid_response = FALSE;
				$response_message = "Failed to delete user.";
			} else {
				$response_message = "Deleted user successfully.";
			}
			$query->close();
		}
	} else if ($_POST["action"] === "delete_category") {
		// DELETE: category.
		$id = html_safe($_POST["id"], TRUE);

		$sql = "DELETE FROM category
				WHERE id = ?";

		if ($query = $conn->prepare($sql)) {
			$query->bind_param("i", $id);
			if (!$query->execute()) {
				$valid_response = FALSE;
				$response_message = "Failed to delete category.";
			} else {
				$response_message = "Deleted category successfully.";
			}
			$query->close();
		}
	} else if ($_POST["action"] === "delete_location") {
		// DELETE: location.
		$id = html_safe($_POST["id"], TRUE);

		$sql = "DELETE FROM locations
				WHERE id = ?";

		if ($query = $conn->prepare($sql)) {
			$query->bind_param("i", $id);
			if (!$query->execute()) {
				$valid_response = FALSE;
				$response_message = "Failed to delete location.";
			} else {
				$response_message = "Deleted location successfully.";
			}
			$query->close();
		}
	} else if ($_POST["action"] === "insert_user") {
		// INSERT: user.
		$user_name = html_safe($_POST["name"], TRUE);
		$user_loginid = html_safe($_POST["loginid"], TRUE);
		$user_pw = pw_hash($_POST["password"]);
		$user_email = html_safe($_POST["email"], TRUE);
		$current_dt = get_datetime();
		$user_suspended = html_safe($_POST["suspended"], TRUE);
		$user_admin = html_safe($_POST["admin"], TRUE);

		$sql = "INSERT INTO user (name, loginid, password, email, join_date, suspended, admin)
				VALUES (?, ?, ?, ?, ?, ?, ?)";

		if ($query = $conn->prepare($sql)) {
			$query->bind_param("sssssii",
				$user_name, $user_loginid, $user_pw, $user_email, $current_dt, $user_suspended, $user_admin
			);
			if (!$query->execute()) {
				$valid_response = FALSE;
				$response_message = "Failed to create user.";
			} else {
				$response_message = "Created user successfully.";
			}
			$query->close();
		}

		unset($user_pw);
	} else if ($_POST["action"] === "insert_category") {
		// INSERT: category.
		$cat_name = html_safe($_POST["name"], TRUE);

		$sql = "INSERT INTO category (name)
				VALUES (?)";

		if ($query = $conn->prepare($sql)) {
			$query->bind_param("s", $cat_name);
			if (!$query->execute()) {
				$valid_response = FALSE;
				$response_message = "Failed to create category.";
			} else {
				$response_message = "Created category successfully.";
			}
			$query->close();
		}
	} else if ($_POST["action"] === "insert_location") {
		// INSERT: location.
		$stn_code = validate_notempty($_POST["stn_code"]) ? html_safe($_POST["stn_code"], TRUE) : NULL;
		$stn_line = validate_notempty($_POST["stn_line"]) ? html_safe($_POST["stn_line"], TRUE) : NULL;
		$stn_name = html_safe($_POST["stn_name"], TRUE);

		$sql = "INSERT INTO locations (stn_code, stn_name, stn_line)
				VALUES (?, ?, ?)";

		if ($query = $conn->prepare($sql)) {
			$query->bind_param("sss", $stn_code, $stn_name, $stn_line);
			if (!$query->execute()) {
				$valid_response = FALSE;
				$response_message = "Failed to create location.";
			} else {
				$response_message = "Created location successfully.";
			}
			$query->close();
		}
	}
}

if ($sql !== NULL) {
	unset($sql);
}

$sql_users = "SELECT id, name, loginid, email, gender, mobile, bio, suspended, admin FROM user ORDER BY id";
$sql_categories = "SELECT id, name FROM category ORDER BY id";
$sql_locations = "SELECT id, stn_code, stn_name, stn_line FROM locations ORDER BY id";

if ($query = $conn->prepare($sql_users)) {
	// Get all users.
	$query->execute();
	$query->bind_result($id, $name, $loginid, $email, $gender, $mobile, $bio, $suspended, $admin);

	while ($query->fetch()) {
		$row = array(
			"id" => (int)($id),
			"name" => $name,
			"loginid" => $loginid,
			"email" => $email,
			"gender" => $gender,
			"mobile" => validate_notempty($mobile) ? $mobile : "",
			"bio" => validate_notempty($bio) ? $bio : "",
			"suspended" => (bool)($suspended),
			"admin" => (bool)($admin),
		);

		array_push($rows_users, $row);
	}

	$query->close();
}

if ($query = $conn->prepare($sql_categories)) {
	// Get all categories.
	$query->execute();
	$query->bind_result($id, $name);

	while ($query->fetch()) {
		$row = array(
			"id" => (int)($id),
			"name" => $name,
		);

		array_push($rows_categories, $row);
	}

	$query->close();
}

if ($query = $conn->prepare($sql_locations)) {
	// Get all locations.
	$query->execute();
	$query->bind_result($id, $code, $name, $line);

	while ($query->fetch()) {
		$row = array(
			"id" => (int)($id),
			"code" => $code,
			"name" => $name,
			"line" => $line,
		);

		array_push($rows_locations, $row);
	}

	$query->close();
}

$conn->close();
