<?php
use Bitrix\Main\Loader;

define('ADMIN_MODULE_NAME', 'perfmon');
define('PERFMON_STOP', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
/** @var CUser $USER */
Loader::includeModule('perfmon');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';

IncludeModuleLangFile(__FILE__);

$RIGHT = CMain::GetGroupRight('perfmon');
if ($RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$sTableID = 'tbl_perfmon_history';
$lAdmin = new CAdminList($sTableID);

$arID = $lAdmin->GroupAction();
if ($arID && $RIGHT >= 'W')
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$rsData = CPerfomanceHistory::GetList(['ID' => 'ASC']);
		while ($arRes = $rsData->Fetch())
		{
			$arID[] = $arRes['ID'];
		}
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
		{
			continue;
		}
		$ID = intval($ID);
		if ($_REQUEST['action'] === 'delete')
		{
				CPerfomanceHistory::Delete($ID);
		}
	}
}

$lAdmin->AddHeaders([
	[
		'id' => 'ID',
		'content' => GetMessage('PERFMON_HIST_ID'),
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'TIMESTAMP_X',
		'content' => GetMessage('PERFMON_HIST_TIMESTAMP_X'),
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'TOTAL_MARK',
		'content' => GetMessage('PERFMON_HIST_TOTAL_MARK'),
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'ACCELERATOR_ENABLED',
		'content' => GetMessage('PERFMON_HIST_ACCELERATOR_ENABLED'),
		'align' => 'right',
		'default' => true,
	],
]);

$rsData = CPerfomanceHistory::GetList(['ID' => 'DESC']);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('PERFMON_HIST_PAGE')));

while ($arRes = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arRes['ID'], $arRes);

	$row->AddViewField('TOTAL_MARK', perfmon_NumberFormat($arRes['TOTAL_MARK'], 2));
	$row->AddCheckField('ACCELERATOR_ENABLED', false);

	$arActions = [];
	if ($RIGHT >= 'W')
	{
		$arActions[] = [
			'ICON' => 'delete',
			'DEFAULT' => 'Y',
			'TEXT' => GetMessage('PERFMON_HIST_DELETE'),
			'ACTION' => "if(confirm('" . GetMessageJS('PERFMON_HIST_DELETE_CONFIRM') . "')) " . $lAdmin->ActionDoGroup($arRes['ID'], 'delete'),
		];
	}

	if (!empty($arActions))
	{
		$row->AddActions($arActions);
	}
}

$lAdmin->AddFooter(
	[
		[
			'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $rsData->SelectedRowsCount(),
		],
	]
);

$aContext = [];
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->AddFooter(
	[
		[
			'title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $rsData->SelectedRowsCount(),
		],
		[
			'counter' => true,
			'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'),
			'value' => '0',
		],
	]
);

$aGroupActions = [];
if ($RIGHT >= 'W')
{
	$aGroupActions['delete'] = GetMessage('MAIN_ADMIN_LIST_DELETE');
}
$lAdmin->AddGroupActionTable($aGroupActions);

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage('PERFMON_HIST_TITLE'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
$lAdmin->DisplayList();
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
