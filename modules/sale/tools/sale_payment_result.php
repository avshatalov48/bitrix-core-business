<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

/** @var \Bitrix\Sale\PaySystem\Service $service */
$service = \Bitrix\Sale\PaySystem\Manager::searchByRequest($request);

if ($service !== false)
{
	$result = $service->processRequest($request);

	if ($service->getField("ENCODING") != '')
	{
		define("BX_SALE_ENCODING", $service->getField("ENCODING"));
		AddEventHandler("main", "OnEndBufferContent", "ChangeEncoding");
		function ChangeEncoding($content)
		{
			global $APPLICATION;
			header("Content-Type: text/html; charset=".BX_SALE_ENCODING);
			$content = $APPLICATION->ConvertCharset($content, SITE_CHARSET, BX_SALE_ENCODING);
			$content = str_replace("charset=".SITE_CHARSET, "charset=".BX_SALE_ENCODING, $content);
		}
	}

	if (!$result->isSuccess())
		echo join('\n', $result->getErrorMessages());
}