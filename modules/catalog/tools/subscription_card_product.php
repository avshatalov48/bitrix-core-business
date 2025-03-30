<?php
/** @global CUser $USER */
/** @global CMain $APPLICATION */
const STOP_STATISTICS = true;
const NO_AGENT_CHECK = true;
const PUBLIC_AJAX_MODE = true;

use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

Loc::loadMessages(__FILE__);

$error = false;
$errorMessage = '';
if (!Loader::includeModule('catalog'))
{
	$error = true;
	$errorMessage = Loc::getMessage('CSD_MODULE_NOT_INSTALLED', array('#NAME#' => 'catalog'));
}
if (
	!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
	&& !AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_VIEW)
)
{
	$error = true;
	$errorMessage = Loc::getMessage('CSD_ACCESS_DENIED');
}
if (!check_bitrix_sessid())
{
	$error = true;
	$errorMessage = Loc::getMessage('CSD_INCORRECT_SESSION');
}
if ($error)
{
	echo Bitrix\Main\Web\Json::encode([
		'error' => true,
		'message' => $errorMessage,
	]);
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_after.php');
	die();
}

$request = Main\Context::getCurrent()->getRequest();
$totalCount = 0;
$activeCount = 0;

if ($request->isPost() && $request->get('getSubscriptionData') === 'Y')
{
	try
	{
		$totalCount = Catalog\SubscribeTable::getCount([
			'=ITEM_ID' => (int)$request->get('itemId'),
		]);

		$activeCount = Catalog\SubscribeTable::getCount([
			'=ITEM_ID' => (int)$request->get('itemId'),
			[
				'LOGIC' => 'OR',
				['=DATE_TO' => false],
				['>DATE_TO' => new DateTime()],
			]
		]);

		echo Bitrix\Main\Web\Json::encode([
			'success' => true,
			'data' => [
				'totalCount' => $totalCount,
				'activeCount' => $activeCount,
			],
		]);
		require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_after.php');
		die();
	}
	catch(Main\SystemException $exception)
	{
		echo Bitrix\Main\Web\Json::encode([
			'error' => true,
			'message' => $exception->getMessage(),
		]);
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
		die();
	}
}
