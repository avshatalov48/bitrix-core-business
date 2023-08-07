<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

try
{
	require($_SERVER["DOCUMENT_ROOT"]."/desktop_app/headers.php");
	if (!defined("BX_FORCE_DISABLE_SEPARATED_SESSION_MODE"))
	{
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('%Bitrix24.Disk/([0-9.]+)%i', $_SERVER['HTTP_USER_AGENT']))
		{
			define("BX_FORCE_DISABLE_SEPARATED_SESSION_MODE", true);
		}
	}
	require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

	/** @var CMain $APPLICATION */
	$diskEnabled = false;
	if(IsModuleInstalled('disk'))
	{
		$diskEnabled =
			\Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false) &&
			CModule::includeModule('disk');
		if($diskEnabled && \Bitrix\Disk\Configuration::REVISION_API >= 5)
		{
			$storageController = new Bitrix\Disk\Bitrix24Disk\Legacy\StorageController();
			$storageController
				->setActionName($_REQUEST['action'])
				->exec()
			;
		}
		else
		{
			$diskEnabled = false;
		}
	}
	if(!$diskEnabled)
	{
		$APPLICATION->IncludeComponent('bitrix:webdav.disk', '', array('VISUAL' => false));
		CMain::FinalActions();
		die();
	}
}
catch (\Throwable $e)
{
	$errorCollection = new ErrorCollection();
	$exceptionHandling = Configuration::getValue('exception_handling');
	if (!empty($exceptionHandling['debug']))
	{
		$errorCollection[] = new Error(ExceptionHandlerFormatter::format($e));
		if ($e->getPrevious())
		{
			$errorCollection[] = new Error(ExceptionHandlerFormatter::format($e->getPrevious()));
		}
	}

	if ($e instanceof \Exception || $e instanceof \Error)
	{
		$exceptionHandler = Application::getInstance()->getExceptionHandler();
		$exceptionHandler->writeToLog($e);
	}

	global $APPLICATION;
	$application = Application::getInstance();
	if (($APPLICATION instanceof \CMain) && $application->isInitialized())
	{
		$APPLICATION->RestartBuffer();
		while (ob_end_clean());

		Application::getInstance()->end(0, AjaxJson::createError($errorCollection));
	}
	if (!headers_sent())
	{
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode([
			'status' => 'error',
			'errors' => [
				[
					'message' => 'Application can\'t start ',
				],
			],
		]);
	}
}
