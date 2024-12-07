<?php

use Bitrix\Main;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

/** @global CAdminPage $adminPage */
global $adminPage;
$adminPage->hideTitle();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

global $APPLICATION;
$APPLICATION->SetTitle(Main\Localization\Loc::getMessage('CATALOG_ADMIN_AGENT_CONTRACT_TITLE'));

$APPLICATION->IncludeComponent(
	'bitrix:ui.toolbar',
	'admin',
	[]
);

$APPLICATION->IncludeComponent(
	'bitrix:catalog.agent.contract.controller',
	'',
	[
		'BACK_URL' => '/bitrix/admin/cat_agent_contract.php?lang=' . LANGUAGE_ID,
	]
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
