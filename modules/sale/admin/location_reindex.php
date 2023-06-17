<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\Helper;

/** @global CMain $APPLICATION */

const NO_AGENT_CHECK = true;
const NO_KEEP_STATISTIC = true;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/prolog.php';

$APPLICATION->SetTitle(Loc::getMessage('SALE_LOCATION_REINDEX_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$APPLICATION->IncludeComponent(
	'bitrix:sale.location.reindex',
	'admin',
	[
		'PATH_TO_REINDEX' => Loader::includeModule('sale') ? Helper::getReindexUrl() : '',
		'INITIAL_TIME' => time(),
	],
	false,
	['HIDE_ICONS' => 'Y']
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
