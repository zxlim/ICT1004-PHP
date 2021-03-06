<?php
##############################
# profile.php
# Logic for processing user profile details.
##############################

if (defined("CLIENT") === FALSE) {
    /**
     * Ghetto way to prevent direct access to "include" files.
     */
    http_response_code(404);
    die();
}

require_once("serverside/functions/validation.php");
require_once("serverside/functions/database.php");
require_once("serverside/functions/security.php");


$user_id = NULL;
$user_name = NULL;
$user_name = $loginid = $password = $email = $mobile = $bio = $profilepicaarray = $profilepic_path = $Test_pic = "";
$nameErr = $loginidErr = $pwdErr = $pwdcfmErr = $emailErr = $genderError = $mobileErr = $bioErr = "";

$results_selectuser = array();
$results_updateuserdetails = array();

$user_id = (int)$_SESSION["user_id"];


$sql_selectuser = "SELECT id, name, loginid, email, gender, mobile, bio, profile_pic FROM user where (id=$user_id)";
//echo $sql_updateuserdetails;

$conn = get_conn();

if ($query = $conn->prepare($sql_selectuser)) {
    $query->execute();
    $query->bind_result($id, $name, $loginid, $email, $gender, $mobile, $bio, $test_pic);

    while ($query->fetch()) {
        $data = array(
            "id" => (int)($id),
            "name" => $name,
            "loginid" => $loginid,
            "email" => $email,
            "gender" => $gender,
            "mobile" => $mobile,
            "bio" => $bio,
            "test_pic" => $test_pic,
        );

        if ($user_id === (int)($id)) {
            $user_name = $name;
            $login_id = $loginid;
            $Email = $email;
            $Gender = $gender;
            $Mobile = $mobile;
            $Bio = $bio;
            $Test_pic = $test_pic;
        }

        array_push($results_selectuser, $data);
    }
    $query->close();
}


if (isset($_POST["updateuser"])) {
    $user_id = (int)($_POST["id"]);
    $user_name = $_POST['name'];
    $loginid = $_POST['loginid'];


    $email = $_POST['email'];

    $mobile = $_POST['mobile'];
    $bio = $_POST['bio'];
    if (isset($_POST['profileimgur_link'])) {
        $profilepicarray = $_POST['profileimgur_link'];
        $profilepic_path = $profilepicarray[0];
    } else {
        $profilepic_path = $Test_pic;
    }

    $sql_updateuserdetails = "UPDATE user SET name='$user_name', loginid='$loginid', email='$email', mobile='$mobile', bio='$bio', profile_pic='$profilepic_path' WHERE (id='$user_id')";


    if ($query = $conn->prepare($sql_updateuserdetails)) {
        // echo $_POST["id"];
        $query->execute();
        $data = NULL;
        if ($query->execute()) {
            if (!(($_POST['password1']) == NULL)) {
                $password = pw_hash($_POST['password1']);

                $sql_updatepassword = "UPDATE user SET password='$password' WHERE (id='$user_id')";

                if ($query = $conn->prepare($sql_updatepassword)) {
                    $query->execute();
                    if ($query->execute()) {
                        $successupdate = 1;
                    } else {
                        $successupdate = 0;
                    }
                }
            } else {
                $successupdate = 1;
            }
        } else {
            $successupdate = 0;
        }
        array_push($results_updateuserdetails, $data);
        $query->close();
    }

    if ($user_id !== NULL) {
        // Successfully created listing. Redirect to item page.
        header(sprintf("Location: profile.php?id=%d", $user_id));
        // Prevent further execution of PHP code in case redirect fails.
        die();
    }
}
$conn->close();
