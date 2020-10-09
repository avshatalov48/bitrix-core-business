<?
$file = preg_replace(array("#[\\\\\\/]+#", "#\\.+[\\\\\\/]#"), array("/", ""), (isset($_REQUEST["file"]) && is_string($_REQUEST["file"]) ? $_REQUEST["file"] : ""));

if(($p = mb_strpos($file, "\0"))!==false)
	$file = mb_substr($file, 0, $p);

if (mb_strpos($file, "/vote/") !== false)
{
	if (mb_strpos($file, "/bitrix/modules/vote/install/templates/vote/") === 0 ||
		mb_strpos($file, "/bitrix/templates/") === 0) @include($_SERVER["DOCUMENT_ROOT"]."/".$file);
}
?>
