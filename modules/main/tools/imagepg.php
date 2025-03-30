<?php

//This function will protect against utf-7 xss
//on page with no character setting
function htmlspecialchars_plus($str)
{
	return str_replace("+","&#43;", htmlspecialchars($str));
}

if (!isset($_GET["img"]) || !is_string($_GET["img"]))
{
	die();
}

$img = $_GET["img"];

if (!str_starts_with($img, '/') || str_starts_with($img, '//') || str_starts_with($img, '/\\'))
{
	// external url
	die();
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script>
function KeyPress()
{
	if (window.event.keyCode == 27)
	{
		window.close();
	}
}
</script>
<style type="text/css">
body {margin:0;}
</style>
<title>Image</title>
</head>
<body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" onKeyPress="KeyPress()">
<img src="<?= htmlspecialchars_plus($img) ?>" border="0" alt="">
</body>
</html>
