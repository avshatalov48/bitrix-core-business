<?
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "uncloud")
{
	$loader = new \Bitrix\Main\UI\FileInputUnclouder();
	$loader
		->setValue($_REQUEST["file"] ?? 0)
		->setSignature($_REQUEST["signature"] ?? '')
		->exec(
			$_REQUEST["mode"] ?? '',
			[
				"width" => $_REQUEST["width"] ?? 0,
				"height" => $_REQUEST["height"] ?? 0,
			]
		)
	;
}
else if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "error")
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
	$receiver = new \Bitrix\Main\UI\FileInputReceiver($_POST["signature"] ?? '');
	$receiver->exec();
}

