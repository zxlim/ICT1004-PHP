<?php

if (defined("CLIENT") === FALSE) {
    /**
     * Ghetto way to prevent direct access to "include" files.
     */
    http_response_code(404);
    die();
}

require_once("serverside/functions/database.php");

$urlsErr = $product_nameErr = $product_descErr = $tagsErr = $priceErr = $conditionErr = $ageErr = $categoryErr = $locationErr = "";
$urls = $product_name = $product_desc = $tags = $price = $condition = $age = $category = $location = "";
$links_array = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["imgur_link"])) {
        $links = $_POST["imgur_link"];
        foreach ($links as $key => $link) {
            array_push($links_array, $link);
        }
    } else {
        $urlsErr = "URL list is empty";
    }

    if (empty($_POST["product_name"])) {
        $product_nameErr = "Product Name is required";
    } else {
        $product_name = test_input($_POST["product_name"]);
    }

    if (empty($_POST["product_desc"])) {
        $product_descErr = "Product Description is required";
    } else {
        $product_desc = test_input($_POST["product_desc"]);
    }

    if (empty($_POST["tags"])) {
        $tagsErr = "Product Tags is required";
    } else {
        $tags = test_input($_POST["tags"]);
    }

    if (empty($_POST["price"])) {
        $priceErr = "Product Price is required";
    } else {
        $price = test_input($_POST["price"]);
    }

    if (empty($_POST["condition"])) {
        $conditionErr = "Product Condition is required";
    } else {
        $condition = test_input($_POST["condition"]);
    }

    if (empty($_POST["age"])) {
        $ageErr = "Product Age is required";
    } else {
        $age = test_input($_POST["age"]);
    }

    if (empty($_POST["categorySelection"])) {
        $categoryErr = "Product Category is required";
    } else {
        $category = test_input($_POST["categorySelection"]);
    }

    if (empty($_POST["locationSelection"])) {
        $locationErr = "Meetup Location is required";
    } else {
        $location = test_input($_POST["locationSelection"]);
    }
}

// DB Conn Part
$conn = get_conn();

$mrt_stations = array();
$mrt_result = "SELECT * FROM locations";
if ($query = $conn->prepare($mrt_result)) {
    $query->execute();
    $query->bind_result($stn_code, $stn_name, $stn_line);

    while ($query->fetch()) {
        $data = array(
            "stn_code" => $stn_code,
            "stn_name" => $stn_name,
            "stn_line" => $stn_line,
        );
        array_push($mrt_stations, $data);
    }
    $query->close();
}

$cat_list = array();
$cat_result = "SELECT * FROM category";
if ($query = $conn->prepare($cat_result)) {
    $query->execute();
    $query->bind_result($id, $name);

    while ($query->fetch()) {
        $data = array(
            "id" => (int)$id,
            "name" => $name
        );
        array_push($cat_list, $data);
    }
    $query->close();
}


if (isset($_POST['form_selling'])) {
    $insert_listing = "INSERT INTO listing('title', 'description', 'tags', 'price', 'condition', 'item_age', 'meetup_location', 'seller_id', 'category_id') VALUES(?,?,?,?,?,?,?,?,?)";
    $current_dt = get_datetime(TRUE);
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>