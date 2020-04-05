<?
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if ($_REQUEST["action"] == "uncloud")
{
	$loader = new \Bitrix\Main\UI\FileInputUnclouder();
	$loader->setValue($_REQUEST["file"])->setSignature($_REQUEST["signature"])->exec($_REQUEST["mode"], array("width" => $_REQUEST["width"], "height" => $_REQUEST["height"]));
}
else if ($_REQUEST["action"] == "error")
{
	$errorCatcher = new \Bitrix\Main\UI\Uploader\ErrorCatcher();
	$errorCatcher->log($_REQUEST["path"], $_REQUEST["data"]);
}
else if ($_SERVER["REQUEST_METHOD"] == "GET")
{
	$uploader = new \Bitrix\Main\UI\Uploader\Uploader($_GET);
	$uploader->checkPost(false);
}
else
{
	$receiver = new \Bitrix\Main\UI\FileInputReceiver($_POST, $_POST["signature"]);
	$receiver->exec();
}

