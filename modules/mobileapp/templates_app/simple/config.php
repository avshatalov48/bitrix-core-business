<?
header("Content-Type: application/x-javascript");
$hash = "12345678";
$config = array("appmap" =>
	array("main" => "/#folder#/",
		"left" => "/#folder#/menu.php",
		"settings" => "/#folder#/settings.php",
		"hash" => mb_substr($hash, rand(1, mb_strlen($hash)))
	)
);
echo json_encode($config);