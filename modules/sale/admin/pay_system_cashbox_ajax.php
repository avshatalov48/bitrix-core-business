<?php

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\PaySystem;

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Main\Loader::includeModule('sale');

$request = Application::getInstance()->getContext()->getRequest();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/lib/internals/input.php';

global $APPLICATION;
$saleModulePermissions = $APPLICATION->GetGroupRight('sale');

$arResult = [];

if ($saleModulePermissions >= 'W' && check_bitrix_sessid())
{
	$action = ($request->get('action') !== null) ? trim($request->get('action')) : '';
	switch ($action)
	{
		case 'reload_settings':

			$service = PaySystem\Manager::getObjectById($request->get('paySystemId'));
			$cashbox = [
				'HANDLER' => $request->get('handler'),
				'KKM_ID' => $request->get('kkmId'),
			];

			/** @var Cashbox\Cashbox $handler */
			$handler = $cashbox['HANDLER'];
			if (is_subclass_of($handler, Cashbox\Cashbox::class))
			{
				ob_start();
				require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/admin/pay_system_cashbox_edit.php';
				$arResult['HTML'] = ob_get_clean();
			}

			break;
	}
}

if (mb_strtolower(SITE_CHARSET) !== 'utf-8')
{
	$arResult = Main\Text\Encoding::convertEncoding($arResult, SITE_CHARSET, 'utf-8');
}

header('Content-Type: application/json');
die(json_encode($arResult));