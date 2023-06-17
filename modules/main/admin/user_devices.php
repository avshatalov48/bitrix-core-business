<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

use Bitrix\Main\Context;
use Bitrix\Main\Authentication\Internal\UserDeviceTable;
use Bitrix\Main\Web\UserAgent\DeviceType;
use Bitrix\Main\UI\Filter;
use Bitrix\Main\Localization\Loc;

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

$tableId = 'tbl_user_devices';
$sort = new CAdminUiSorting($tableId, 'ID', 'desc');
$list = new CAdminUiList($tableId, $sort);

$deviceTypes = DeviceType::getDescription();

$filterFields = [
	['id' => 'USER_ID', 'name' => Loc::getMessage('main_user_devices_user_id'), 'default' => true],
	['id' => 'DEVICE_UID', 'name' => Loc::getMessage('main_user_devices_device_uid'), 'default' => false],
	['id' => 'DEVICE_TYPE', 'name' => Loc::getMessage('main_user_devices_device_type'), 'type' => 'list', 'items' => ['' => Loc::getMessage('main_user_devices_not_selected')] + $deviceTypes,	'default' => true],
	['id' => 'BROWSER', 'name' => Loc::getMessage('main_user_devices_browser'), 'default' => true],
	['id' => 'PLATFORM', 'name' => Loc::getMessage('main_user_devices_platform'), 'default' => true],
	['id' => 'USER_AGENT', 'name' => Loc::getMessage('main_user_devices_agent'), 'default' => true],
];

$headers = [
	['id' => 'ID', 'content' => 'ID', 'sort' => 'ID', 'default' => true],
	['id' => 'USER_ID', 'content' => Loc::getMessage('main_user_devices_user_id'), 'sort' => 'USER_ID', 'default' => true],
	['id' => 'DEVICE_UID', 'content' => Loc::getMessage('main_user_devices_device_uid'), 'sort' => 'DEVICE_UID', 'default' => false],
	['id' => 'DEVICE_TYPE', 'content' => Loc::getMessage('main_user_devices_device_type'), 'sort' => 'DEVICE_TYPE', 'default' => true],
	['id' => 'BROWSER', 'content' => Loc::getMessage('main_user_devices_browser'), 'sort' => 'BROWSER', 'default' => true],
	['id' => 'PLATFORM', 'content' => Loc::getMessage('main_user_devices_platform'), 'sort' => 'PLATFORM', 'default' => true],
	['id' => 'USER_AGENT', 'content' => Loc::getMessage('main_user_devices_agent'), 'sort' => 'USER_AGENT', 'default' => true],
];

$list->addHeaders($headers);

$query = UserDeviceTable::query();

$query->setSelect(['*']);

// TODO: do something about globals
global $by, $order;

$sortBy = strtoupper($by);
if (!UserDeviceTable::getEntity()->hasField($sortBy))
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

$nav = $list->getPageNavigation('pages-user-devices');
$query->setOffset($nav->getOffset());
if (!$excelMode)
{
	$query->setLimit($nav->getLimit() + 1);
}

$filterOption = new Filter\Options($tableId);
$filter = $filterOption->getFilter($filterFields);

if (!empty($filter['FIND']))
{
	$query->whereLike('USER_AGENT', '%' . $filter['FIND'] . '%');
}
if (isset($filter['USER_ID']))
{
	$query->where('USER_ID', $filter['USER_ID']);
}
if (isset($filter['DEVICE_UID']))
{
	$query->where('DEVICE_UID', $filter['DEVICE_UID']);
}
if (isset($filter['DEVICE_TYPE']))
{
	$query->where('DEVICE_TYPE', $filter['DEVICE_TYPE']);
}
if (isset($filter['BROWSER']))
{
	$query->whereLike('BROWSER', '%' . $filter['BROWSER'] . '%');
}
if (isset($filter['PLATFORM']))
{
	$query->whereLike('PLATFORM', '%' . $filter['PLATFORM'] . '%');
}
if (isset($filter['USER_AGENT']))
{
	$query->whereLike('USER_AGENT', '%' . $filter['USER_AGENT'] . '%');
}

$result = $query->exec();

if ($list->isTotalCountRequest())
{
	$list->sendTotalCountResponse($result->getCount());
}

$n = 0;
$pageSize = $list->getNavSize();

while ($device = $result->fetch())
{
	$n++;
	if ($n > $pageSize && !$excelMode)
	{
		break;
	}

	$loginsUrl = 'user_devices_history.php?lang=' . LANGUAGE_ID . '&DEVICE_ID=' . $device['ID'] . '&apply_filter=Y';

	$row = $list->addRow($device['ID'], $device, $loginsUrl, Loc::getMessage('main_user_devices_row_title'));

	$row->addViewField('DEVICE_TYPE', $deviceTypes[$device['DEVICE_TYPE']]);

	$actions = [
		[
			'ICON' => 'view',
			'TEXT' => Loc::getMessage('main_user_devices_menu_history'),
			'LINK' => $loginsUrl,
			'DEFAULT' => true,
		]
	];

	$row->addActions($actions);
}

$nav->setRecordCount($nav->getOffset() + $n);
$list->setNavigation($nav, Loc::getMessage('main_user_devices_page'), false);

$list->AddAdminContextMenu();

$list->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('main_user_devices_title'));

require __DIR__ . '/../include/prolog_admin_after.php';

$list->DisplayFilter($filterFields);
$list->DisplayList(['SHOW_COUNT_HTML' => true, 'ACTION_PANEL' => false]);

require __DIR__ . '/../include/epilog_admin.php';
