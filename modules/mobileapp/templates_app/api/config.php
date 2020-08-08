<?
header("Content-Type: application/x-javascript");
$hash = "bx_random_hash";
$config = array("appmap" =>
	array("main" => "#folder#",
		"left" => "/#folder#/left.php",
		"right" => "/#folder#/right.php",
		"settings" => "/#folder#/settings.php",
		"hash" => mb_substr($hash, rand(1, mb_strlen($hash)))
	)
);
echo json_encode($config);
?>