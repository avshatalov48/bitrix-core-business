<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

use Bitrix\Main\Context;
use Bitrix\Main\Authentication\Internal\UserDeviceLoginTable;
use Bitrix\Main\UI\Filter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Service\GeoIp\Internal\GeonameTable;
use Bitrix\Main\Type\DateTime;

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

require_once __DIR__ . '/../include/prolog_admin_before.php';
require_once __DIR__ . '/../prolog.php';

define('HELP_FILE', 'users/user_devices.php');

if (!$USER->CanDoOperation('edit_all_users'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$request = Context::getCurrent()->getRequest();

$excelMode = ($request['mode'] == 'excel');

$tableId = 'tbl_user_devices_history';
$sort = new CAdminUiSorting($tableId, 'ID', 'desc');
$list = new CAdminUiList($tableId, $sort);

$filterFields = [
	['id' => 'DEVICE_ID', 'name' => Loc::getMessage('main_user_devices_history_device_id'), 'default' => true],
	['id' => 'LOGIN_DATE', 'name' => Loc::getMessage('main_user_devices_history_date'), 'type' => 'date', 'default' => true],
	['id' => 'IP', 'name' => Loc::getMessage('main_user_devices_history_ip'), 'default' => true],
	['id' => 'CITY_GEOID', 'name' => Loc::getMessage('main_user_devices_history_city_id'), 'default' => true],
	['id' => 'REGION_GEOID', 'name' => Loc::getMessage('main_user_devices_history_region_id'), 'default' => true],
	['id' => 'COUNTRY_ISO_CODE', 'name' => Loc::getMessage('main_user_devices_history_country_id'), 'default' => true],
];

$headers = [
	['id' => 'ID', 'content' => 'ID', 'sort' => 'ID', 'default' => true],
	['id' => 'DEVICE_ID', 'content' => Loc::getMessage('main_user_devices_history_device_id'), 'sort' => 'DEVICE_ID', 'default' => true],
	['id' => 'LOGIN_DATE', 'content' => Loc::getMessage('main_user_devices_history_date'), 'sort' => 'LOGIN_DATE', 'default' => true],
	['id' => 'IP', 'content' => Loc::getMessage('main_user_devices_history_ip'), 'sort' => 'IP', 'default' => true],
	['id' => 'CITY_GEOID', 'content' => Loc::getMessage('main_user_devices_history_city'), 'sort' => 'CITY_GEOID', 'default' => true],
	['id' => 'REGION_GEOID', 'content' => Loc::getMessage('main_user_devices_history_region'), 'sort' => 'REGION_GEOID', 'default' => true],
	['id' => 'COUNTRY_ISO_CODE', 'content' => Loc::getMessage('main_user_devices_history_country'), 'sort' => 'COUNTRY_ISO_CODE', 'default' => true],
	['id' => 'APP_PASSWORD_ID', 'content' => Loc::getMessage('main_user_devices_history_app_pass'), 'sort' => 'APP_PASSWORD_ID', 'default' => false],
	['id' => 'STORED_AUTH_ID', 'content' => Loc::getMessage('main_user_devices_history_stored_pass'), 'sort' => 'STORED_AUTH_ID', 'default' => false],
	['id' => 'HIT_AUTH_ID', 'content' => Loc::getMessage('main_user_devices_history_hash_pass'), 'sort' => 'HIT_AUTH_ID', 'default' => false],
];

$list->addHeaders($headers);

$query = UserDeviceLoginTable::query();

$query->setSelect(['*']);

// TODO: do something about globals
global $by, $order;

$sortBy = strtoupper($by);
if (!UserDeviceLoginTable::getEntity()->hasField($sortBy))
{
	$sortBy = 'ID';
}

$sortOrder = strtoupper($order);
if ($sortOrder != 'ASC')
{
	$sortOrder = 'DESC';
}
$query->setOrder([$sortBy => $sortOrder]);

if ($list->isTotalCountRequest())
{
	$query->countTotal(true);
}

$nav = $list->getPageNavigation('pages-user-devices-history');
$query->setOffset($nav->getOffset());
if (!$excelMode)
{
	$query->setLimit($nav->getLimit() + 1);
}

$filterOption = new Filter\Options($tableId);
$filter = $filterOption->getFilter($filterFields);

if ($filter['FIND'] != '')
{
	$query->whereLike('IP', '%' . $filter['FIND'] . '%');
}
if (isset($filter['DEVICE_ID']))
{
	$query->where('DEVICE_ID', $filter['DEVICE_ID']);
}
if (isset($filter['LOGIN_DATE_from']))
{
	if ($date = DateTime::tryParse($filter['LOGIN_DATE_from']))
	{
		$query->where('LOGIN_DATE', '>=', $date);
	}
}
if (isset($filter['LOGIN_DATE_to']))
{
	if ($date = DateTime::tryParse($filter['LOGIN_DATE_to']))
	{
		$query->where('LOGIN_DATE', '<=', $date);
	}
}
if (isset($filter['IP']))
{
	$query->whereLike('IP', '%' . $filter['IP'] . '%');
}
if (isset($filter['CITY_GEOID']))
{
	$query->where('CITY_GEOID', $filter['CITY_GEOID']);
}
if (isset($filter['REGION_GEOID']))
{
	$query->where('REGION_GEOID', $filter['REGION_GEOID']);
}
if (isset($filter['COUNTRY_ISO_CODE']))
{
	$query->where('COUNTRY_ISO_CODE', $filter['COUNTRY_ISO_CODE']);
}
$result = $query->exec();

if ($list->isTotalCountRequest())
{
	$list->sendTotalCountResponse($result->getCount());
}

$n = 0;
$pageSize = $list->getNavSize();

$records = [];
$geoids = [];
while ($history = $result->fetch())
{
	$n++;
	if ($n > $pageSize && !$excelMode)
	{
		break;
	}

	$records[] = $history;

	if ($history['CITY_GEOID'] > 0)
	{
		$geoids[$history['CITY_GEOID']] = $history['CITY_GEOID'];
	}
	if ($history['REGION_GEOID'] > 0)
	{
		$geoids[$history['REGION_GEOID']] = $history['REGION_GEOID'];
	}
}

$countries = GetCountries();
$geonames = GeonameTable::get($geoids);
$currentLang = Context::getCurrent()->getLanguageObject()->getCode();

foreach ($records as $history)
{
	$row = $list->addRow($history['ID'], $history);

	if ($history['CITY_GEOID'] > 0)
	{
		$geoid = $history['CITY_GEOID'];
		$name = $geonames[$geoid][$currentLang] ?? $geonames[$geoid]['en'] ?? '';
		$row->addViewField('CITY_GEOID', '[' . $geoid . '] ' . HtmlFilter::encode($name));
	}

	if ($history['REGION_GEOID'] > 0)
	{
		$geoid = $history['REGION_GEOID'];
		$name = $geonames[$geoid][$currentLang] ?? $geonames[$geoid]['en'] ?? '';
		$row->addViewField('REGION_GEOID', '[' . $geoid . '] ' . HtmlFilter::encode($name));
	}

	if ($history['COUNTRY_ISO_CODE'] != '')
	{
		$country = $history['COUNTRY_ISO_CODE'];
		$row->addViewField('COUNTRY_ISO_CODE', '[' . $country . '] ' . $countries[$country]['NAME']);
	}
}

$nav->setRecordCount($nav->getOffset() + $n);
$list->setNavigation($nav, Loc::getMessage('main_user_devices_history_page'), false);

$list->AddAdminContextMenu();

$list->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('main_user_devices_history_title'));

require __DIR__ . '/../include/prolog_admin_after.php';

$list->DisplayFilter($filterFields);
$list->DisplayList(['SHOW_COUNT_HTML' => true, 'ACTION_PANEL' => false]);

require __DIR__ . '/../include/epilog_admin.php';
