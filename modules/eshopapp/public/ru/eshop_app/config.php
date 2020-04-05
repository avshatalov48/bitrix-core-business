<?
header("Content-Type: application/x-javascript");
$config = array("appmap" => array("main"=>"#SITE_DIR#eshop_app/", "left"=>"#SITE_DIR#eshop_app/left.php"));
echo json_encode($config);
?>