<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

define('ADMIN_MODULE_NAME', 'perfmon');

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
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$data = [
	'tuning' => [
		'NAME' => Loc::getMessage('PERFMON_PHP_TUNING_NAME'),
		'TITLE' => Loc::getMessage('PERFMON_PHP_TUNING_TITLE'),
		'HEADERS' => [
			[
				'id' => 'PARAMETER',
				'content' => Loc::getMessage('PERFMON_PHP_TUNING_PARAMETER'),
				'default' => true,
			],
			[
				'id' => 'VALUE',
				'content' => Loc::getMessage('PERFMON_PHP_TUNING_VALUE'),
				'align' => 'right',
				'default' => true,
			],
			[
				'id' => 'RECOMMENDATION',
				'content' => Loc::getMessage('PERFMON_PHP_TUNING_RECOMMENDATION'),
				'default' => true,
			],
		],
		'ITEMS' => [],
	],
];

$php_version = phpversion();
$is_ok = version_compare($php_version, '7.4.0', '>=');
$data['tuning']['ITEMS'][] = [
	'PARAMETER' => Loc::getMessage('PERFMON_PHP_VERSION'),
	'IS_OK' => $is_ok,
	'VALUE' => (
	$is_ok ?
		$php_version :
		'<span class="errortext">' . $php_version . '</span>'
	),
	'RECOMMENDATION' => Loc::getMessage('PERFMON_PHP_VERSION_REC', ['#value#' => '7.4.0']),
];


$open_basedir = ini_get('open_basedir');
$is_ok = $open_basedir == '';
$data['tuning']['ITEMS'][] = [
	'PARAMETER' => 'open_basedir',
	'IS_OK' => $is_ok,
	'VALUE' => '&nbsp;' . $open_basedir,
	'RECOMMENDATION' => Loc::getMessage('PERFMON_PHP_OPEN_BASEDIR_REC'),
];

$size = CPerfAccel::unformat(ini_get('realpath_cache_size'));
$is_ok = ($size >= 4 * 1024 * 1024);
$data['tuning']['ITEMS'][] = [
	'PARAMETER' => 'realpath_cache_size',
	'IS_OK' => $is_ok,
	'VALUE' => ini_get('realpath_cache_size'),
	'RECOMMENDATION' => Loc::getMessage('PERFMON_PHP_PATH_CACHE_REC2'),
];

$arKnownAccels = ['zendopcache' => '<a href="http://pecl.php.net/package/ZendOpcache">ZendOpcache</a>'];

$allAccelerators = CPerfomanceMeasure::GetAllAccelerators();
if (!$allAccelerators)
{
	$data['tuning']['ITEMS'][] = [
		'PARAMETER' => Loc::getMessage('PERFMON_PHP_PRECOMPILER'),
		'IS_OK' => false,
		'VALUE' => Loc::getMessage('PERFMON_PHP_PRECOMPILER_NOT_INSTALLED'),
		'RECOMMENDATION' => Loc::getMessage('PERFMON_PHP_PRECOMPILER_REC') . '<br>' . implode('<br>', $arKnownAccels),
	];
}
else
{
	$workingAccel = null;
	foreach ($allAccelerators as $accel)
	{
		if ($accel->IsWorking())
		{
			$workingAccel = $accel;
			$arRecommendations = $accel->GetRecommendations();
			foreach ($arRecommendations as $i => $ar)
			{
				$data['tuning']['ITEMS'][] = $ar;
			}
			break;
		}
	}

	if ($workingAccel === null)
	{
		foreach ($allAccelerators as $accel)
		{
			$arRecommendations = $accel->GetRecommendations();
			foreach ($arRecommendations as $i => $ar)
			{
				$data['tuning']['ITEMS'][] = $ar;
			}
		}
	}
}

$sTableID = 'tbl_perfmon_panel';

$APPLICATION->SetTitle(Loc::getMessage('PERFMON_PHP_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

foreach ($data as $i => $arTable)
{
	$lAdmin = new CAdminList($sTableID . $i);

	$lAdmin->BeginPrologContent();
	if (array_key_exists('TITLE', $arTable))
	{
		echo '<h4>' . $arTable['TITLE'] . "</h4>\n";
	}

	$lAdmin->EndPrologContent();
	$lAdmin->AddHeaders($arTable['HEADERS']);

	$rsData = new CDBResult;
	$rsData->InitFromArray($arTable['ITEMS']);
	$rsData = new CAdminResult($rsData, $sTableID . $i);

	$j = 0;
	while ($arRes = $rsData->Fetch())
	{
		$row =& $lAdmin->AddRow($j++, $arRes);
		$row->AddViewField('PARAMETER', $arRes['PARAMETER']);
		if ($arRes['IS_OK'])
		{
			$row->AddViewField('VALUE', $arRes['VALUE'] . '&nbsp;');
			$row->AddViewField('RECOMMENDATION', '&nbsp;');
		}
		else
		{
			$row->AddViewField('VALUE', '<span class="errortext">' . $arRes['VALUE'] . '&nbsp;</span>');
			$row->AddViewField('RECOMMENDATION', $arRes['RECOMMENDATION']);
		}
	}

	$lAdmin->CheckListMode();
	$lAdmin->DisplayList();
}

echo BeginNote(), '<a href="phpinfo.php?test_var1=AAA&amp;test_var2=BBB">' . Loc::getMessage('PERFMON_PHP_SETTINGS') . '</a>', EndNote();
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
