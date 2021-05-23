<?php
if (!defined("STOP_STATISTICS"))
	define("STOP_STATISTICS", true);
if (!defined("NO_AGENT_STATISTIC"))
	define("NO_AGENT_STATISTIC","Y");
if (!defined("NO_AGENT_CHECK"))
	define("NO_AGENT_CHECK", true);
if (!defined("NO_KEEP_STATISTIC"))
	define("NO_KEEP_STATISTIC", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
include_once(__DIR__."/file.php");

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

try
{
	$controller = new MFIController;
	if ($request->isPost() &&
		$request->getPost("mfi_mode") == "upload" &&
		$controller->validate(array(
			"moduleId" => $request->getPost("moduleId") ?: "main",
			"forceMd5" => ($request->getPost("forceMd5") === true || $request->getPost("forceMd5") === "true" ? true : false),
			"allowUpload" => $request->getPost("allowUpload"),
			"allowUploadExt" => $request->getPost("allowUploadExt"),
			"uploadMaxFilesize" => $request->getPost("uploadMaxFilesize")
		), $request->getPost("mfi_sign"))
	)
	{
		$controller->setModuleId($request->getPost("moduleId"))->setForceMd5($request->getPost("forceMd5"));
		$controller->initUploader(array(
			"allowUpload" => $request->getPost("allowUpload"),
			"allowUploadExt" => $request->getPost("allowUploadExt"),
			"uploadMaxFilesize" => $request->getPost("uploadMaxFilesize")
		));
	}
	$controller->checkRequest();
}
catch(\Exception $e)
{
	$exceptionHandling = \Bitrix\Main\Config\Configuration::getValue("exception_handling");
	if ($exceptionHandling["debug"])
	{
		throw $e;
	}
	else
	{
		$errorCollection = new \Bitrix\Main\ErrorCollection();
		$errorCollection->add(array(new \Bitrix\Main\Error($e->getMessage(), $e->getCode())));
		$controller->sendErrorResponse($errorCollection);
	}
}

\CMain::finalActions();
die;
?>