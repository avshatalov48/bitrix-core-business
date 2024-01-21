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

/** @var \Bitrix\Main\HttpRequest $request */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$group = (string)$request->get('group');
if ($group !== 'comp' && $group !== 'type' && $group !== 'dir' && $group !== 'file')
{
	$group = 'none';
}

$DOCUMENT_ROOT_LEN = mb_strlen($_SERVER['DOCUMENT_ROOT']);
$sTableID = 'tbl_perfmon_cache_list_' . $group;
$oSort = new CAdminSorting($sTableID, 'NN', 'asc');
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = [
	'find',
	'find_type',
	'find_hit_id',
	'find_component_id',
	'find_component_name',
	'find_module_name',
	'find_op_mode',
	'find_base_dir',
	'find_init_dir',
	'find_file_name',
];

$currentFilter = $lAdmin->InitFilter($FilterArr);
foreach ($FilterArr as $fieldName)
{
	$currentFilter[$fieldName] = ($currentFilter[$fieldName] ?? '');
}

if ($group === 'none')
{
	$arFilter = [
		'COMPONENT_NAME' => ($currentFilter['find'] !== '' && $currentFilter['find_type'] === 'component_name' ? $currentFilter['find'] : $currentFilter['find_component_name']),
		'=HIT_ID' => ($currentFilter['find'] !== '' && $currentFilter['find_type'] === 'hit_id' ? $currentFilter['find'] : $currentFilter['find_hit_id']),
		'MODULE_NAME' => $currentFilter['find_module_name'],
		'=COMPONENT_ID' => $currentFilter['find_component_id'],
		'=OP_MODE' => $currentFilter['find_op_mode'],
		'=BASE_DIR' => $currentFilter['find_base_dir'],
		'=INIT_DIR' => $currentFilter['find_init_dir'],
		'=FILE_NAME' => $currentFilter['find_file_name'],
	];
}
else
{
	$arFilter = [];
}

foreach ($arFilter as $key => $value)
{
	if (!$value)
	{
		unset($arFilter[$key]);
	}
}

if ($group === 'comp')
{
	$arHeaders = [
		[
			'id' => 'COMPONENT_NAME',
			'content' => GetMessage('PERFMON_CACHE_COMPONENT_NAME'),
			'sort' => 'COMPONENT_NAME',
			'default' => true,
		],
		[
			'id' => 'COUNT',
			'content' => GetMessage('PERFMON_CACHE_COUNT'),
			'sort' => 'COUNT',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_R',
			'content' => GetMessage('PERFMON_CACHE_COUNT_R'),
			'sort' => 'COUNT_R',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_W',
			'content' => GetMessage('PERFMON_CACHE_COUNT_W'),
			'sort' => 'COUNT_W',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_C',
			'content' => GetMessage('PERFMON_CACHE_COUNT_C'),
			'sort' => 'COUNT_C',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'SUM_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_SUM_CACHE_SIZE'),
			'sort' => 'SUM_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'AVG_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_AVG_CACHE_SIZE'),
			'sort' => 'AVG_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'MIN_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_MIN_CACHE_SIZE'),
			'sort' => 'MIN_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'MAX_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_MAX_CACHE_SIZE'),
			'sort' => 'MAX_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
	];
}
elseif ($group === 'type')
{
	$arHeaders = [
		[
			'id' => 'BASE_DIR',
			'content' => GetMessage('PERFMON_CACHE_BASE_DIR'),
			'sort' => 'BASE_DIR',
			'default' => true,
		],
		[
			'id' => 'COUNT',
			'content' => GetMessage('PERFMON_CACHE_COUNT'),
			'sort' => 'COUNT',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_R',
			'content' => GetMessage('PERFMON_CACHE_COUNT_R'),
			'sort' => 'COUNT_R',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_W',
			'content' => GetMessage('PERFMON_CACHE_COUNT_W'),
			'sort' => 'COUNT_W',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_C',
			'content' => GetMessage('PERFMON_CACHE_COUNT_C'),
			'sort' => 'COUNT_C',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'SUM_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_SUM_CACHE_SIZE'),
			'sort' => 'SUM_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'AVG_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_AVG_CACHE_SIZE'),
			'sort' => 'AVG_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'MIN_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_MIN_CACHE_SIZE'),
			'sort' => 'MIN_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'MAX_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_MAX_CACHE_SIZE'),
			'sort' => 'MAX_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
	];
}
elseif ($group === 'dir')
{
	$arHeaders = [
		[
			'id' => 'BASE_DIR',
			'content' => GetMessage('PERFMON_CACHE_BASE_DIR'),
			'sort' => 'INIT_DIR',
			'default' => true,
		],
		[
			'id' => 'INIT_DIR',
			'content' => GetMessage('PERFMON_CACHE_INIT_DIR'),
			'sort' => 'INIT_DIR',
			'default' => true,
		],
		[
			'id' => 'COUNT',
			'content' => GetMessage('PERFMON_CACHE_COUNT'),
			'sort' => 'COUNT',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_R',
			'content' => GetMessage('PERFMON_CACHE_COUNT_R'),
			'sort' => 'COUNT_R',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_W',
			'content' => GetMessage('PERFMON_CACHE_COUNT_W'),
			'sort' => 'COUNT_W',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_C',
			'content' => GetMessage('PERFMON_CACHE_COUNT_C'),
			'sort' => 'COUNT_C',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'SUM_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_SUM_CACHE_SIZE'),
			'sort' => 'SUM_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'AVG_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_AVG_CACHE_SIZE'),
			'sort' => 'AVG_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'MIN_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_MIN_CACHE_SIZE'),
			'sort' => 'MIN_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'MAX_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_MAX_CACHE_SIZE'),
			'sort' => 'MAX_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
	];
}
elseif ($group === 'file')
{
	$arHeaders = [
		[
			'id' => 'BASE_DIR',
			'content' => GetMessage('PERFMON_CACHE_BASE_DIR'),
			'sort' => 'INIT_DIR',
			'default' => true,
		],
		[
			'id' => 'INIT_DIR',
			'content' => GetMessage('PERFMON_CACHE_INIT_DIR'),
			'sort' => 'INIT_DIR',
			'default' => true,
		],
		[
			'id' => 'FILE_NAME',
			'content' => GetMessage('PERFMON_CACHE_FILE_NAME'),
			'sort' => 'FILE_NAME',
			'default' => true,
		],
		[
			'id' => 'HIT_RATIO',
			'content' => GetMessage('PERFMON_CACHE_HIT_RATIO'),
			'sort' => 'HIT_RATIO',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT',
			'content' => GetMessage('PERFMON_CACHE_COUNT'),
			'sort' => 'COUNT',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_R',
			'content' => GetMessage('PERFMON_CACHE_COUNT_R'),
			'sort' => 'COUNT_R',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_W',
			'content' => GetMessage('PERFMON_CACHE_COUNT_W'),
			'sort' => 'COUNT_W',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COUNT_C',
			'content' => GetMessage('PERFMON_CACHE_COUNT_C'),
			'sort' => 'COUNT_C',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'SUM_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_SUM_CACHE_SIZE'),
			'sort' => 'SUM_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'AVG_CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_AVG_CACHE_SIZE'),
			'sort' => 'AVG_CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
	];
}
else
{
	$arHeaders = [
		[
			'id' => 'ID',
			'content' => GetMessage('PERFMON_CACHE_ID'),
			'sort' => 'ID',
			'align' => 'right',
		],
		[
			'id' => 'HIT_ID',
			'content' => GetMessage('PERFMON_CACHE_HIT_ID'),
			'sort' => 'HIT_ID',
			'align' => 'right',
		],
		[
			'id' => 'NN',
			'content' => GetMessage('PERFMON_CACHE_NN'),
			'sort' => 'NN',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'COMPONENT_NAME',
			'content' => GetMessage('PERFMON_CACHE_COMPONENT_NAME'),
			'sort' => 'COMPONENT_NAME',
			'default' => true,
		],
		[
			'id' => 'MODULE_NAME',
			'content' => GetMessage('PERFMON_CACHE_MODULE_NAME'),
			'sort' => 'MODULE_NAME',
			'default' => true,
		],
		[
			'id' => 'CACHE_SIZE',
			'content' => GetMessage('PERFMON_CACHE_CACHE_SIZE'),
			'sort' => 'CACHE_SIZE',
			'align' => 'right',
			'default' => true,
		],
		[
			'id' => 'OP_MODE',
			'content' => GetMessage('PERFMON_CACHE_OP_MODE'),
			'sort' => 'OP_MODE',
			'default' => true,
		],
		[
			'id' => 'BASE_DIR',
			'content' => GetMessage('PERFMON_CACHE_BASE_DIR'),
			'sort' => 'FILE_PATH',
			'default' => true,
		],
		[
			'id' => 'INIT_DIR',
			'content' => GetMessage('PERFMON_CACHE_INIT_DIR'),
			'sort' => 'FILE_PATH',
			'default' => true,
		],
		[
			'id' => 'FILE_NAME',
			'content' => GetMessage('PERFMON_CACHE_FILE_NAME'),
			'sort' => 'FILE_PATH',
			'default' => true,
		],
		[
			'id' => 'CACHE_PATH',
			'content' => GetMessage('PERFMON_CACHE_CACHE_PATH'),
		],
	];
}

$lAdmin->AddHeaders($arHeaders);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
{
	foreach ($arHeaders as $info)
	{
		if ($info['default'])
		{
			$arSelectedFields[] = $info['id'];
		}
	}
}
if (in_array('FILE_NAME', $arSelectedFields, true))
{
	$arSelectedFields[] = 'FILE_PATH';
}
if (!in_array('HIT_ID', $arSelectedFields, true))
{
	$arSelectedFields[] = 'HIT_ID';
}

$arNumCols = [
	'CACHE_SIZE' => 0,
	'COUNT' => 0,
	'COUNT_R' => 0,
	'COUNT_W' => 0,
	'COUNT_C' => 0,
	'SUM_CACHE_SIZE' => 0,
	'AVG_CACHE_SIZE' => 0,
	'MIN_CACHE_SIZE' => 0,
	'MAX_CACHE_SIZE' => 0,
	'HIT_RATIO' => 2,
];

$rsData = CPerfomanceCache::GetList(
	[
		$by => $order
	],
	$arFilter,
	$group !== 'none',
	[
		'nPageSize' => CAdminResult::GetNavSize($sTableID),
	],
	$arSelectedFields
);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('PERFMON_CACHE_PAGE')));

$i = 0;
$max_display_url = COption::GetOptionInt('perfmon', 'max_display_url');
while ($arRes = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow(++$i, $arRes);
	$numbers = [];
	foreach ($arNumCols as $column_name => $precision)
	{
		if (isset($arRes[$column_name]))
		{
			$numbers[$column_name] = perfmon_NumberFormat($arRes[$column_name], $precision);
			$row->AddViewField($column_name, $numbers[$column_name]);
		}
	}
	$row->AddViewField('HIT_ID', '<a href="perfmon_hit_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_id=' . $arRes['HIT_ID'] . '">' . $arRes['HIT_ID'] . '</a>');
	if ($arRes['FILE_NAME'] !== '')
	{
		if ($arRes['FILE_PATH'] === '')
		{
			$arRes['FILE_PATH'] = $_SERVER['DOCUMENT_ROOT'] . $arRes['BASE_DIR'] . $arRes['INIT_DIR'] . $arRes['FILE_NAME'];
		}
		if (
			file_exists($arRes['FILE_PATH'])
			&& mb_substr($arRes['FILE_PATH'], 0, $DOCUMENT_ROOT_LEN) === $_SERVER['DOCUMENT_ROOT']
		)
		{
			$row->AddViewField('FILE_NAME', '<a target="blank" href="/bitrix/admin/fileman_file_view.php?path=' . urlencode(mb_substr($arRes['FILE_PATH'], $DOCUMENT_ROOT_LEN)) . '&lang=' . LANGUAGE_ID . '">' . $arRes['FILE_NAME'] . '</a>');
		}
	}
	if ($arRes['OP_MODE'] === 'R')
	{
		$row->AddViewField('OP_MODE', GetMessage('PERFMON_CACHE_OP_MODE_R'));
	}
	elseif ($arRes['OP_MODE'] === 'W')
	{
		$row->AddViewField('OP_MODE', GetMessage('PERFMON_CACHE_OP_MODE_W'));
	}
	elseif ($arRes['OP_MODE'] === 'C')
	{
		$row->AddViewField('OP_MODE', GetMessage('PERFMON_CACHE_OP_MODE_C'));
	}
	if ($group === 'comp')
	{
		if ($arRes['COUNT'] > 0 && $arRes['COMPONENT_NAME'] !== '')
		{
			$row->AddViewField('COUNT', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_component_name=' . urlencode($arRes['COMPONENT_NAME']) . '">' . $numbers['COUNT'] . '</a>');
		}
		if ($arRes['COUNT_R'] > 0 && $arRes['COMPONENT_NAME'] !== '')
		{
			$row->AddViewField('COUNT_R', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_component_name=' . urlencode($arRes['COMPONENT_NAME']) . '&amp;find_op_mode=R">' . $numbers['COUNT_R'] . '</a>');
		}
		if ($arRes['COUNT_W'] > 0 && $arRes['COMPONENT_NAME'] !== '')
		{
			$row->AddViewField('COUNT_W', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_component_name=' . urlencode($arRes['COMPONENT_NAME']) . '&amp;find_op_mode=W">' . $numbers['COUNT_W'] . '</a>');
		}
		if ($arRes['COUNT_C'] > 0 && $arRes['COMPONENT_NAME'] !== '')
		{
			$row->AddViewField('COUNT_C', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_component_name=' . urlencode($arRes['COMPONENT_NAME']) . '&amp;find_op_mode=C">' . $numbers['COUNT_C'] . '</a>');
		}
	}
	elseif ($group === 'type')
	{
		if ($arRes['COUNT'] > 0)
		{
			$row->AddViewField('COUNT', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '">' . $numbers['COUNT'] . '</a>');
		}
		if ($arRes['COUNT_R'] > 0)
		{
			$row->AddViewField('COUNT_R', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_op_mode=R">' . $numbers['COUNT_R'] . '</a>');
		}
		if ($arRes['COUNT_W'] > 0)
		{
			$row->AddViewField('COUNT_W', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_op_mode=W">' . $numbers['COUNT_W'] . '</a>');
		}
		if ($arRes['COUNT_C'] > 0)
		{
			$row->AddViewField('COUNT_C', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_op_mode=C">' . $numbers['COUNT_C'] . '</a>');
		}
	}
	elseif ($group === 'dir')
	{
		if ($arRes['COUNT'] > 0)
		{
			$row->AddViewField('COUNT', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_init_dir=' . urlencode($arRes['INIT_DIR']) . '">' . $numbers['COUNT'] . '</a>');
		}
		if ($arRes['COUNT_R'] > 0)
		{
			$row->AddViewField('COUNT_R', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_init_dir=' . urlencode($arRes['INIT_DIR']) . '&amp;find_op_mode=R">' . $numbers['COUNT_R'] . '</a>');
		}
		if ($arRes['COUNT_W'] > 0)
		{
			$row->AddViewField('COUNT_W', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_init_dir=' . urlencode($arRes['INIT_DIR']) . '&amp;find_op_mode=W">' . $numbers['COUNT_W'] . '</a>');
		}
		if ($arRes['COUNT_C'] > 0)
		{
			$row->AddViewField('COUNT_C', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_init_dir=' . urlencode($arRes['INIT_DIR']) . '&amp;find_op_mode=C">' . $numbers['COUNT_C'] . '</a>');
		}
	}
	elseif ($group === 'file')
	{
		if ($arRes['COUNT'] > 0)
		{
			$row->AddViewField('COUNT', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_init_dir=' . urlencode($arRes['INIT_DIR']) . '&amp;find_file_name=' . urlencode($arRes['FILE_NAME']) . '">' . $numbers['COUNT'] . '</a>');
		}
		if ($arRes['COUNT_R'] > 0)
		{
			$row->AddViewField('COUNT_R', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_init_dir=' . urlencode($arRes['INIT_DIR']) . '&amp;find_file_name=' . urlencode($arRes['FILE_NAME']) . '&amp;find_op_mode=R">' . $numbers['COUNT_R'] . '</a>');
		}
		if ($arRes['COUNT_W'] > 0)
		{
			$row->AddViewField('COUNT_W', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_init_dir=' . urlencode($arRes['INIT_DIR']) . '&amp;find_file_name=' . urlencode($arRes['FILE_NAME']) . '&amp;find_op_mode=W">' . $numbers['COUNT_W'] . '</a>');
		}
		if ($arRes['COUNT_C'] > 0)
		{
			$row->AddViewField('COUNT_C', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_init_dir=' . urlencode($arRes['INIT_DIR']) . '&amp;find_file_name=' . urlencode($arRes['FILE_NAME']) . '&amp;find_op_mode=C">' . $numbers['COUNT_C'] . '</a>');
		}
	}
	if ($arRes['BASE_DIR'] === '/bitrix/managed_cache/')
	{
		$BASE_DIR = GetMessage('PERFMON_CACHE_MANAGED');
	}
	elseif ($arRes['BASE_DIR'] === '/bitrix/cache/')
	{
		$BASE_DIR = GetMessage('PERFMON_CACHE_UNMANAGED');
	}
	else
	{
		$BASE_DIR = $arRes['BASE_DIR'];
	}
	if ($arRes['BASE_DIR'] !== '')
	{
		$row->AddViewField('BASE_DIR', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '">' . $BASE_DIR . '</a>');
	}
	if ($arRes['INIT_DIR'] != '')
	{
		$row->AddViewField('INIT_DIR', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;group=none&amp;set_filter=Y&amp;find_base_dir=' . urlencode($arRes['BASE_DIR']) . '&amp;find_init_dir=' . urlencode($arRes['INIT_DIR']) . '">' . $arRes['INIT_DIR'] . '</a>');
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

if ($group === 'comp')
{
	$group_title = GetMessage('PERFMON_CACHE_GROUP_COMP');
}
elseif ($group === 'type')
{
	$group_title = GetMessage('PERFMON_CACHE_GROUP_BASE_DIR');
}
elseif ($group === 'dir')
{
	$group_title = GetMessage('PERFMON_CACHE_GROUP_INIT_DIR');
}
elseif ($group === 'file')
{
	$group_title = GetMessage('PERFMON_CACHE_GROUP_FILE_NAME');
}
else
{
	$group_title = GetMessage('PERFMON_CACHE_GROUP_NONE');
}

$aContext = [
	[
		'TEXT' => $group_title,
		'MENU' => [
			[
				'TEXT' => GetMessage('PERFMON_CACHE_GROUP_NONE'),
				'ACTION' => $lAdmin->ActionRedirect('perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&group=none'),
				'ICON' => ($group === 'none' ? 'checked' : ''),
			],
			[
				'TEXT' => GetMessage('PERFMON_CACHE_GROUP_COMP'),
				'ACTION' => $lAdmin->ActionRedirect('perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&group=comp'),
				'ICON' => ($group === 'comp' ? 'checked' : ''),
			],
			[
				'TEXT' => GetMessage('PERFMON_CACHE_GROUP_BASE_DIR'),
				'ACTION' => $lAdmin->ActionRedirect('perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&group=type'),
				'ICON' => ($group === 'type' ? 'checked' : ''),
			],
			[
				'TEXT' => GetMessage('PERFMON_CACHE_GROUP_INIT_DIR'),
				'ACTION' => $lAdmin->ActionRedirect('perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&group=dir'),
				'ICON' => ($group === 'dir' ? 'checked' : ''),
			],
			[
				'TEXT' => GetMessage('PERFMON_CACHE_GROUP_FILE_NAME'),
				'ACTION' => $lAdmin->ActionRedirect('perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&group=file'),
				'ICON' => ($group === 'file' ? 'checked' : ''),
			],
		],
	],
];

$lAdmin->AddAdminContextMenu($aContext, false, $group === 'none');

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('PERFMON_CACHE_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

if ($group === 'none')
{
	$oFilter = new CAdminFilter(
		$sTableID . '_filter',
		[
			'find_component_name' => GetMessage('PERFMON_CACHE_COMPONENT_NAME'),
			'find_module_name' => GetMessage('PERFMON_CACHE_MODULE_NAME'),
			'find_hit_id' => GetMessage('PERFMON_CACHE_HIT_ID'),
			'find_component_id' => GetMessage('PERFMON_CACHE_COMPONENT_ID'),
			'find_op_mode' => GetMessage('PERFMON_CACHE_OP_MODE'),
			'find_base_dir' => GetMessage('PERFMON_CACHE_BASE_DIR'),
			'find_init_dir' => GetMessage('PERFMON_CACHE_INIT_DIR'),
			'find_file_name' => GetMessage('PERFMON_CACHE_FILE_NAME'),
		]
	);
	?>

	<form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage(); ?>">
		<?php $oFilter->Begin(); ?>
		<tr>
			<td><b><?=GetMessage('PERFMON_CACHE_FIND')?>:</b></td>
			<td>
				<input type="text" size="25" name="find" value="<?php echo htmlspecialcharsbx($currentFilter['find']) ?>"
					title="<?=GetMessage('PERFMON_CACHE_FIND')?>">
				<?php
				$arr = [
					'reference' => [
						GetMessage('PERFMON_CACHE_COMPONENT_NAME'),
						GetMessage('PERFMON_CACHE_HIT_ID'),
					],
					'reference_id' => [
						'component_name',
						'hit_id',
					]
				];
				echo SelectBoxFromArray('find_type', $arr, $currentFilter['find_type'], '', '');
				?>
			</td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_CACHE_COMPONENT_NAME')?></td>
			<td><input type="text" name="find_component_name" size="47"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_component_name']) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_CACHE_MODULE_NAME')?></td>
			<td><input type="text" name="find_module_name" size="47"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_module_name']) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_CACHE_HIT_ID')?></td>
			<td><input type="text" name="find_hit_id" size="47"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_hit_id']) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_CACHE_COMPONENT_ID')?></td>
			<td><input type="text" name="find_component_id" size="47"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_component_id']) ?>"></td>
		</tr>
		<tr>
			<td><?php echo GetMessage('PERFMON_CACHE_OP_MODE') ?>:</td>
			<td><?php
				$arr = [
					'reference' => [
						GetMessage('PERFMON_CACHE_OP_MODE_R'),
						GetMessage('PERFMON_CACHE_OP_MODE_W'),
						GetMessage('PERFMON_CACHE_OP_MODE_C'),
					],
					'reference_id' => [
						'R',
						'W',
						'C',
					],
				];
				echo SelectBoxFromArray('find_op_mode', $arr, htmlspecialcharsbx($currentFilter['find_op_mode']), GetMessage('MAIN_ALL'));
				?></td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_CACHE_BASE_DIR')?></td>
			<td><input type="text" name="find_base_dir" size="47"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_base_dir']) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_CACHE_INIT_DIR')?></td>
			<td><input type="text" name="find_init_dir" size="47"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_init_dir']) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage('PERFMON_CACHE_FILE_NAME')?></td>
			<td><input type="text" name="find_file_name" size="47"
				value="<?php echo htmlspecialcharsbx($currentFilter['find_file_name']) ?>"></td>
		</tr>
		<?php
		$oFilter->Buttons([
			'table_id' => $sTableID,
			'url' => $APPLICATION->GetCurPage(),
			'form' => 'find_form',
		]);
		$oFilter->End();
		?>
	</form>
<?php
}

$lAdmin->DisplayList();?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
