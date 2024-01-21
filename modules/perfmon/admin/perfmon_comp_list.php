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

$group = (string)$request->get('group') === 'Y' ? 'Y' : '';
$sTableID = 'tbl_perfmon_comp_list' . $group;
$oSort = new CAdminSorting($sTableID, 'NN', 'asc');
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = [
	'find',
	'find_type',
	'find_component_name',
	'find_hit_id',
	'find_cache_type',
	'find_hit_script_name',
];

$currentFilter = $lAdmin->InitFilter($FilterArr);
foreach ($FilterArr as $fieldName)
{
	$currentFilter[$fieldName] = ($currentFilter[$fieldName] ?? '');
}

$arFilter = [
	'COMPONENT_NAME' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'component_name' ? $currentFilter['find'] : $currentFilter['find_component_name']),
	'=HIT_ID' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'hit_id' ? $currentFilter['find'] : $currentFilter['find_hit_id']),
	'CACHE_TYPE' => $currentFilter['find_cache_type'],
	'HIT_SCRIPT_NAME' => $currentFilter['find_hit_script_name'],
];
foreach ($arFilter as $key => $value)
{
	if (!$value)
	{
		unset($arFilter[$key]);
	}
}

$arHeaders = [];

if ($group !== 'Y')
{
	$arHeaders[] = [
		'id' => 'ID',
		'content' => GetMessage('PERFMON_COMP_ID'),
		'sort' => 'ID',
		'align' => 'right',
	];
	$arHeaders[] = [
		'id' => 'HIT_ID',
		'content' => GetMessage('PERFMON_COMP_HIT_ID'),
		'sort' => 'HIT_ID',
		'align' => 'right',
	];
	$arHeaders[] = [
		'id' => 'NN',
		'content' => GetMessage('PERFMON_COMP_NN'),
		'sort' => 'NN',
		'align' => 'right',
		'default' => true,
	];
}

$arHeaders[] = [
	'id' => 'COMPONENT_NAME',
	'content' => GetMessage('PERFMON_COMP_COMPONENT_NAME'),
	'sort' => 'COMPONENT_NAME',
	'default' => true,
];

if ($group !== 'Y')
{
	$arHeaders[] = [
		'id' => 'COMPONENT_TIME',
		'content' => GetMessage('PERFMON_COMP_COMPONENT_TIME'),
		'sort' => 'COMPONENT_TIME',
		'align' => 'right',
		'default' => true,
	];
	$arHeaders[] = [
		'id' => 'QUERIES',
		'content' => GetMessage('PERFMON_COMP_QUERIES'),
		'sort' => 'QUERIES',
		'align' => 'right',
		'default' => true,
	];
	$arHeaders[] = [
		'id' => 'QUERIES_TIME',
		'content' => GetMessage('PERFMON_COMP_QUERIES_TIME'),
		'sort' => 'QUERIES_TIME',
		'align' => 'right',
		'default' => true,
	];
}

$arHeaders[] = [
	'id' => 'CACHE_TYPE',
	'content' => GetMessage('PERFMON_COMP_CACHE_TYPE'),
	'sort' => 'CACHE_TYPE',
	'align' => 'right',
	'default' => true,
];

if ($group !== 'Y')
{
	$arHeaders[] = [
		'id' => 'CACHE_SIZE',
		'content' => GetMessage('PERFMON_COMP_CACHE_SIZE'),
		'sort' => 'CACHE_SIZE',
		'align' => 'right',
	];
	$arHeaders[] = [
		'id' => 'CACHE_COUNT',
		'content' => GetMessage('PERFMON_COMP_CACHE_COUNT'),
		'sort' => 'CACHE_COUNT',
		'align' => 'right',
	];
	$arHeaders[] = [
		'id' => 'CACHE_COUNT_R',
		'content' => GetMessage('PERFMON_COMP_CACHE_COUNT_R'),
		'sort' => 'CACHE_COUNT_R',
		'align' => 'right',
	];
	$arHeaders[] = [
		'id' => 'CACHE_COUNT_W',
		'content' => GetMessage('PERFMON_COMP_CACHE_COUNT_W'),
		'sort' => 'CACHE_COUNT_W',
		'align' => 'right',
	];
	$arHeaders[] = [
		'id' => 'CACHE_COUNT_C',
		'content' => GetMessage('PERFMON_COMP_CACHE_COUNT_C'),
		'sort' => 'CACHE_COUNT_C',
		'align' => 'right',
	];
}

if ($group === 'Y')
{
	$arHeaders[] = [
		'id' => 'COUNT',
		'content' => GetMessage('PERFMON_COMP_COUNT'),
		'align' => 'right',
		'sort' => 'COUNT',
		'default' => true,
	];
}

$lAdmin->AddHeaders($arHeaders);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
{
	if ($group !== 'Y')
	{
		$arSelectedFields = [
			'ID',
			'HIT_ID',
			'NN',
			'CACHE_TYPE',
			'COMPONENT_NAME',
			'COMPONENT_TIME',
			'QUERIES',
			'QUERIES_TIME',
		];
	}
	else
	{
		$arSelectedFields = [
			'COMPONENT_NAME',
			'CACHE_TYPE',
			'COUNT',
		];
	}
}

$arSelectedFields[] = $group !== 'Y' ? 'ID' : 'COMPONENT_NAME';

$arNumCols = [
	'CACHE_SIZE' => 0,
	'COMPONENT_TIME' => 4,
	'QUERIES' => 0,
	'QUERIES_TIME' => 4,
	'CACHE_COUNT' => 0,
	'CACHE_COUNT_R' => 0,
	'CACHE_COUNT_W' => 0,
	'CACHE_COUNT_C' => 0,
];

if (isset($arFilter['CACHE_TYPE']) && $arFilter['CACHE_TYPE'] == 'N')
{
	$arFilter['CACHE_TYPE'] = [false, 'N'];
}

$rsData = CPerfomanceComponent::GetList(
	[$by => $order],
	$arFilter,
	$group === 'Y',
	['nPageSize' => CAdminResult::GetNavSize($sTableID)],
	$arSelectedFields
);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('PERFMON_COMP_PAGE')));

$max_display_url = COption::GetOptionInt('perfmon', 'max_display_url');
while ($arRes = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arRes['NAME'], $arRes);
	foreach ($arNumCols as $column_name => $precision)
	{
		$row->AddViewField($column_name, perfmon_NumberFormat($arRes[$column_name], $precision));
	}
	if ($group === 'Y' && $arRes['COMPONENT_NAME'])
	{
		$row->AddViewField('COMPONENT_NAME', '<a href="perfmon_comp_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_component_name=' . $arRes['COMPONENT_NAME'] . '">' . $arRes['COMPONENT_NAME'] . '</a>');
	}
	if ($arRes['QUERIES'] > 0)
	{
		$row->AddViewField('QUERIES', '<a href="perfmon_sql_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_component_id=' . $arRes['ID'] . '">' . $arRes['QUERIES'] . '</a>');
	}
	$row->AddViewField('HIT_ID', '<a href="perfmon_hit_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_id=' . $arRes['HIT_ID'] . '">' . $arRes['HIT_ID'] . '</a>');
	if ($arRes['CACHE_TYPE'] == 'A')
	{
		$row->AddViewField('CACHE_TYPE', GetMessage('PERFMON_COMP_CACHE_TYPE_AUTO'));
	}
	elseif ($arRes['CACHE_TYPE'] == 'Y')
	{
		$row->AddViewField('CACHE_TYPE', GetMessage('PERFMON_COMP_CACHE_TYPE_YES'));
	}
	else
	{
		$row->AddViewField('CACHE_TYPE', GetMessage('PERFMON_COMP_CACHE_TYPE_NO'));
	}
	if ($arRes['CACHE_COUNT'] > 0)
	{
		$row->AddViewField('CACHE_COUNT', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_component_id=' . $arRes['ID'] . '">' . $arRes['CACHE_COUNT'] . '</a>');
	}
	if ($arRes['CACHE_COUNT_R'] > 0)
	{
		$row->AddViewField('CACHE_COUNT_R', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_component_id=' . $arRes['ID'] . '&amp;find_op_mode=R">' . $arRes['CACHE_COUNT_R'] . '</a>');
	}
	if ($arRes['CACHE_COUNT_W'] > 0)
	{
		$row->AddViewField('CACHE_COUNT_W', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_component_id=' . $arRes['ID'] . '&amp;find_op_mode=W">' . $arRes['CACHE_COUNT_W'] . '</a>');
	}
	if ($arRes['CACHE_COUNT_C'] > 0)
	{
		$row->AddViewField('CACHE_COUNT_C', '<a href="perfmon_cache_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_component_id=' . $arRes['ID'] . '&amp;find_op_mode=C">' . $arRes['CACHE_COUNT_C'] . '</a>');
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

$aContext = [
	[
		'TEXT' => GetMessage('PERFMON_COMP_GROUP'),
		'MENU' => [
			[
				'TEXT' => GetMessage('PERFMON_COMP_GROUP_ON'),
				'ACTION' => $lAdmin->ActionDoGroup(0, '', 'group=Y&by=COUNT&order=DESC'),
				'ICON' => ($group === 'Y' ? 'checked' : ''),
			],
			[
				'TEXT' => GetMessage('PERFMON_COMP_GROUP_OFF'),
				'ACTION' => $lAdmin->ActionDoGroup(0, '', 'group=N'),
				'ICON' => ($group !== 'Y' ? 'checked' : ''),
			],
		],
	],
];

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->BeginPrologContent();
echo '<script>BX.ready(function(){BX("list_group_mode").value="' . CUtil::JSEscape($group) . '";});</script>';
$lAdmin->EndPrologContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('PERFMON_COMP_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$oFilter = new CAdminFilter(
	$sTableID . '_filter',
	[
		'find_component_name' => GetMessage('PERFMON_COMP_COMPONENT_NAME'),
		'find_hit_id' => GetMessage('PERFMON_COMP_HIT_ID'),
		'find_hit_script_name' => GetMessage('PERFMON_COMP_HIT_SCRIPT_NAME'),
		'find_cache_type' => GetMessage('PERFMON_COMP_CACHE_TYPE'),
	]
);
?>

<form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage(); ?>">
	<?php $oFilter->Begin(); ?>
	<tr>
		<td><b><?=GetMessage('PERFMON_COMP_FIND')?>:</b></td>
		<td>
			<input type="text" size="25" name="find" value="<?php echo htmlspecialcharsbx($currentFilter['find']) ?>"
				title="<?=GetMessage('PERFMON_COMP_FIND')?>">
			<?php
			$arr = [
				'reference' => [
					GetMessage('PERFMON_COMP_COMPONENT_NAME'),
					GetMessage('PERFMON_COMP_HIT_ID'),
				],
				'reference_id' => [
					'component_name',
					'hit_id',
				]
			];
			echo SelectBoxFromArray('find_type', $arr, $currentFilter['find_type'], '', '');
			?>
			<input type="hidden" id="list_group_mode" name="group" value="<?php echo htmlspecialcharsbx($group) ?>">
		</td>
	</tr>
	<tr>
		<td><?=GetMessage('PERFMON_COMP_COMPONENT_NAME')?></td>
		<td><input type="text" name="find_component_name" size="47"
			value="<?php echo htmlspecialcharsbx($currentFilter['find_component_name']) ?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage('PERFMON_COMP_HIT_ID')?></td>
		<td><input type="text" name="find_hit_id" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_hit_id']) ?>">
		</td>
	</tr>
	<tr>
		<td><?=GetMessage('PERFMON_COMP_HIT_SCRIPT_NAME')?></td>
		<td><input type="text" name="find_hit_script_name" size="47"
			value="<?php echo htmlspecialcharsbx($currentFilter['find_hit_script_name']) ?>"></td>
	</tr>
	<tr>
		<td><?php echo GetMessage('PERFMON_COMP_CACHE_TYPE') ?>:</td>
		<td><?php
			$arr = [
				'reference' => [
					GetMessage('PERFMON_COMP_CACHE_TYPE_YES'),
					GetMessage('PERFMON_COMP_CACHE_TYPE_NO'),
					GetMessage('PERFMON_COMP_CACHE_TYPE_AUTO'),
				],
				'reference_id' => [
					'Y',
					'N',
					'A',
				],
			];
			echo SelectBoxFromArray('find_cache_type', $arr, htmlspecialcharsbx($currentFilter['find_cache_type']), GetMessage('MAIN_ALL'));
			?></td>
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
$lAdmin->DisplayList();
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
