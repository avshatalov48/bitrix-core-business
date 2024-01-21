<?php
use Bitrix\Main\Loader;

define('ADMIN_MODULE_NAME', 'perfmon');
define('PERFMON_STOP', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
/** @var CUser $USER */
/** @var string $DBType */
Loader::includeModule('perfmon');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';
IncludeModuleLangFile(__FILE__);

$RIGHT = CMain::GetGroupRight('perfmon');
if ($RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$APPLICATION->SetTitle(GetMessage('PERFMON_EXPLAIN_TITLE'));

/** @var \Bitrix\Main\HttpRequest $request */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$ID = intval($request->get('ID'));
$sTableID = 'tbl_perfmon_explain';
$lAdmin = new CAdminList($sTableID);

$connection = \Bitrix\Main\Application::getConnection();
if ($connection->getType() === 'mysql')
{
	$arHeader = [
		[
			'id' => 'select_type',
			'content' => GetMessage('PERFMON_EXPLAIN_F_SELECT_TYPE'),
			'align' => 'left',
			'default' => true,
		],
		[
			'id' => 'table',
			'content' => GetMessage('PERFMON_EXPLAIN_F_TABLE'),
			'align' => 'left',
			'default' => true,
		],
		[
			'id' => 'type',
			'content' => GetMessage('PERFMON_EXPLAIN_F_TYPE'),
			'align' => 'left',
			'default' => true,
		],
		[
			'id' => 'possible_keys',
			'content' => GetMessage('PERFMON_EXPLAIN_F_POSSIBLE_KEYS'),
			'align' => 'left',
			'default' => true,
		],
		[
			'id' => 'key',
			'content' => GetMessage('PERFMON_EXPLAIN_F_KEY'),
			'align' => 'left',
			'default' => true,
		],
		[
			'id' => 'key_len',
			'content' => GetMessage('PERFMON_EXPLAIN_F_KEY_LEN'),
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'ref',
			'content' => GetMessage('PERFMON_EXPLAIN_F_REF'),
			'align' => 'left',
			'default' => true,
		],
		[
			'id' => 'rows',
			'content' => GetMessage('PERFMON_EXPLAIN_F_ROWS'),
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'Extra',
			'content' => GetMessage('PERFMON_EXPLAIN_F_EXTRA'),
			'align' => 'left',
			'default' => true,
		],
	];
}
else
{
	$arHeader = [
		[
			'id' => 'plan',
			'content' => 'QUERY PLAN',
			'align' => 'left',
			'default' => true,
		],
		[
			'id' => 'cost',
			'content' => 'cost',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'rows',
			'content' => 'rows',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'width',
			'content' => 'width',
			'align' => 'right',
			'default' => true,
		],
	];
}
$lAdmin->AddHeaders($arHeader);

$arPlan = false;
$rsSQL = CPerfomanceSQL::GetList(['ID', 'SQL_TEXT'], ['=ID' => $ID], [], false);
$arSQL = $rsSQL->Fetch();
$strSQL = CPerfQuery::transform2select($arSQL['SQL_TEXT']);
if ($strSQL)
{
	$rsData = $DB->Query('explain ' . $strSQL, true);
}
else
{
	$rsData = false;
}

if ($rsData)
{
	$SQL_TEXT = CPerfomanceSQL::Format($strSQL);
	$lAdmin->BeginPrologContent();
	echo '<p>' . str_replace(
			[' ', "\t", "\n"],
			[' ', '&nbsp;&nbsp;&nbsp;', '<br>'],
			htmlspecialcharsbx(CSqlFormat::reformatSql($SQL_TEXT))
		) . '</p>';

	if ($arPlan['OPTIMIZER'])
	{
		echo '<p>' . GetMessage('PERFMON_EXPLAIN_F_OPTIMIZER') . ': ' . $arPlan['OPTIMIZER'] . '</p>';
		echo '<p>' . GetMessage('PERFMON_EXPLAIN_F_COST') . ': ' . $arPlan['POSITION'] . '</p>';
	}
	$lAdmin->EndPrologContent();
}
else
{
	$rsData = new CDBResult;
	$rsData->InitFromArray([]);
	$lAdmin->BeginPrologContent();
	$message = new CAdminMessage([
		'MESSAGE' => GetMessage('PERFMON_EXPLAIN_SQL_ERROR'),
		'TYPE' => 'ERROR',
	]);
	echo $message->Show();
	$lAdmin->EndPrologContent();
}

$Comment = '';
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
while ($arRes = $rsData->GetNext())
{
	if (array_key_exists('DEPTH', $arRes))
	{
		$arRes['OPERATION'] = str_repeat('&nbsp;&nbsp;', $arRes['DEPTH']) . $arRes['OPERATION'];
	}
	if (array_key_exists('select_type', $arRes))
	{
		$arRes['select_type'] = $arRes['id'] . ' ' . $arRes['select_type'];
	}
	if (array_key_exists('QUERY PLAN', $arRes))
	{
		$arRes['plan'] = $arRes['~QUERY PLAN'];
		if (preg_match('/^( )+/', $arRes['plan'], $match))
		{
			$arRes['plan'] = str_repeat('&nbsp;', strlen($match[1]) * 2) . substr($arRes['plan'], strlen($match[1]));
		}
		if (preg_match('/\(.*(cost=)([0-9.]+).*\)/', $arRes['plan'], $match))
		{
			$arRes['cost'] = $match[2];
			$arRes['plan'] = str_replace($match[1] . $match[2], '', $arRes['plan']);
		}
		if (preg_match('/\(.*(rows=)([0-9.]+).*\)/', $arRes['plan'], $match))
		{
			$arRes['rows'] = $match[2];
			$arRes['plan'] = str_replace($match[1] . $match[2], '', $arRes['plan']);
		}
		if (preg_match('/\(.*(width=)([0-9.]+).*\)/', $arRes['plan'], $match))
		{
			$arRes['width'] = $match[2];
			$arRes['plan'] = str_replace($match[1] . $match[2], '', $arRes['plan']);
		}
		$arRes['plan'] = preg_replace('/\(\s*\)/', '', $arRes['plan']);
	}
	$row = $lAdmin->AddRow($arRes['ID'], $arRes);
	if (array_key_exists('Comment', $arRes))
	{
		$Comment .= $arRes['Comment'] . "\n";
	}
}

if ($Comment)
{
	$lAdmin->BeginEpilogContent();
	$message = new CAdminMessage([
		'MESSAGE' => $Comment,
		'TYPE' => 'OK',
	]);
	echo $message->Show();
	$lAdmin->EndEpilogContent();
}

$lAdmin->AddFooter([]);
$lAdmin->CheckListMode();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_popup_admin.php';
$lAdmin->DisplayList();
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_popup_admin.php';
