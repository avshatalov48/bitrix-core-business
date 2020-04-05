<?php
require($_SERVER["DOCUMENT_ROOT"]."/desktop_app/headers.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

/** @var CAllMain $APPLICATION */
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
			->exec();
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