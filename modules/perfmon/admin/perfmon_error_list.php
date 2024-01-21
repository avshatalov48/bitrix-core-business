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

$bFileMan = CModule::IncludeModule('fileman');

$arErrorCodes = [
	1 => 'E_ERROR',
	2 => 'E_WARNING',
	4 => 'E_PARSE',
	8 => 'E_NOTICE',
	16 => 'E_CORE_ERROR',
	32 => 'E_CORE_WARNING',
	64 => 'E_COMPILE_ERROR',
	128 => 'E_COMPILE_WARNING',
	256 => 'E_USER_ERROR',
	512 => 'E_USER_WARNING',
	1024 => 'E_USER_NOTICE',
	2048 => 'E_STRICT',
	4096 => 'E_RECOVERABLE_ERROR',
	8192 => 'E_DEPRECATED',
	16384 => 'E_USER_DEPRECATED',
	6143 => 'E_ALL',
];

$sTableID = 'tbl_perfmon_error_list';
$oSort = new CAdminSorting($sTableID, 'ID', 'desc');
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminList($sTableID, $oSort);

if (($arID = $lAdmin->GroupAction()) && $RIGHT >= 'W')
{
	if ($_REQUEST['action'] === 'delete')
	{
		CPerfomanceError::Delete(['=ERRFILE' => $_REQUEST['file'], '=ERRLINE' => $_REQUEST['line']]);
	}
}

$FilterArr = [
	'find',
	'find_type',
	'find_hit_id',
	'find_errno',
	'find_errfile',
	'find_errstr',
];

$currentFilter = $lAdmin->InitFilter($FilterArr);
foreach ($FilterArr as $fieldName)
{
	$currentFilter[$fieldName] = ($currentFilter[$fieldName] ?? '');
}

$arFilter = [
	'=HIT_ID' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'hit_id' ? $currentFilter['find'] : $currentFilter['find_hit_id']),
	'=ERRNO' => $currentFilter['find_errno'],
	'%ERRFILE' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'file' ? $currentFilter['find'] : $currentFilter['find_errfile']),
	'%ERRSTR' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'file' ? $currentFilter['find'] : $currentFilter['find_errstr']),
];
foreach ($arFilter as $key => $value)
{
	if (!$value)
	{
		unset($arFilter[$key]);
	}
}

$group = $_REQUEST['group'] ?? '';

$arHeaders = [];
if ($group !== 'Y')
{
	$arHeaders[] = [
		'id' => 'ID',
		'content' => GetMessage('PERFMON_ERR_ID'),
		'align' => 'right',
		'sort' => 'ID',
		'default' => true,
	];
	$arHeaders[] = [
		'id' => 'HIT_ID',
		'content' => GetMessage('PERFMON_ERR_HIT_ID'),
		'align' => 'right',
		'sort' => 'HIT_ID',
		'default' => true,
	];
}
$arHeaders[] = [
	'id' => 'ERRNO',
	'content' => GetMessage('PERFMON_ERR_NO'),
	'align' => 'right',
	'sort' => 'ERRNO',
	'default' => true,
];
$arHeaders[] = [
	'id' => 'ERRFILE',
	'content' => GetMessage('PERFMON_ERR_FILE'),
	'sort' => 'ERRFILE',
	'default' => true,
];
$arHeaders[] = [
	'id' => 'ERRLINE',
	'content' => GetMessage('PERFMON_ERR_LINE'),
	'align' => 'right',
	'sort' => 'ERRLINE',
	'default' => true,
];
$arHeaders[] = [
	'id' => 'ERRSTR',
	'content' => GetMessage('PERFMON_ERR_TEXT'),
	'sort' => 'ERRSTR',
	'default' => true,
];
if ($group === 'Y')
{
	$arHeaders[] = [
		'id' => 'COUNT',
		'content' => GetMessage('PERFMON_ERR_COUNT'),
		'align' => 'right',
		'sort' => 'COUNT',
		'default' => true,
	];
}

$lAdmin->AddHeaders($arHeaders);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
{
	$arSelectedFields = [
		'ID',
		'HIT_ID',
		'ERRNO',
		'ERRFILE',
		'ERRLINE',
		'ERRSTR',
	];
}

$rsData = CPerfomanceError::GetList($arSelectedFields, $arFilter, [$by => $order], $group === 'Y');

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('PERFMON_ERR_PAGE')));

while ($arRes = $rsData->GetNext())
{
	if ($group === 'Y')
	{
		$ID = md5($arRes['ERRFILE'] . '|' . $arRes['ERRLINE']);
	}
	else
	{
		$ID = $arRes['ID'];
	}

	$row = $lAdmin->AddRow($ID, $arRes);

	$row->AddViewField('ERRNO', $arErrorCodes[$arRes['ERRNO']]);

	if ($bFileMan)
	{
		$row->AddViewField('ERRFILE', '<a href="fileman_file_edit.php?lang=' . LANGUAGE_ID . '&amp;full_src=Y&amp;site=&amp;set_filter=Y&amp;filter=&amp;path=' . urlencode(mb_substr($arRes['ERRFILE'], mb_strlen($_SERVER['DOCUMENT_ROOT']))) . '">' . $arRes['ERRFILE'] . '</a>');
	}

	$row->AddViewField('HIT_ID', '<a href="perfmon_hit_list.php?lang=' . LANGUAGE_ID . '&amp;set_filter=Y&amp;find_id=' . $arRes['HIT_ID'] . '">' . $arRes['HIT_ID'] . '</a>');

	$row->AddViewField('ERRSTR', $arRes['ERRSTR']);

	if ($group === 'Y')
	{
		$arActions = [];
		$arActions[] = [
			'ICON' => 'delete',
			'DEFAULT' => false,
			'TEXT' => GetMessage('PERFMON_ERR_ACTION_DELETE'),
			'ACTION' => $lAdmin->ActionDoGroup($ID, 'delete', 'group=Y&file=' . $arRes['ERRFILE'] . '&line=' . $arRes['ERRLINE']),
		];
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

$aContext = [
	[
		'TEXT' => GetMessage('PERFMON_ERR_GROUP'),
		'MENU' => [
			[
				'TEXT' => GetMessage('PERFMON_ERR_GROUP_ON'),
				'ACTION' => $lAdmin->ActionDoGroup(0, '', 'group=Y&by=COUNT&order=DESC'),
				'ICON' => ($group === 'Y' ? 'checked' : ''),
			],
			[
				'TEXT' => GetMessage('PERFMON_ERR_GROUP_OFF'),
				'ACTION' => $lAdmin->ActionDoGroup(0, '', 'group=N'),
				'ICON' => ($group !== 'Y' ? 'checked' : ''),
			],
		],
	],
];

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('PERFMON_ERR_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
$oFilter = new CAdminFilter(
	$sTableID . '_filter',
	[
		'find_hit_id' => GetMessage('PERFMON_ERR_HIT_ID'),
		'find_errno' => GetMessage('PERFMON_ERR_NO'),
		'find_errfile' => GetMessage('PERFMON_ERR_FILE'),
		'find_errstr' => GetMessage('PERFMON_ERR_TEXT'),
	]
);
?>

<form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage(); ?>">
	<?php $oFilter->Begin(); ?>
	<tr>
		<td><b><?=GetMessage('PERFMON_ERR_FIND')?>:</b></td>
		<td>
			<input type="text" size="25" name="find" value="<?php echo htmlspecialcharsbx($currentFilter['find']) ?>"
				title="<?=GetMessage('PERFMON_ERR_FIND')?>">
			<?php
			$arr = [
				'reference' => [
					GetMessage('PERFMON_ERR_HIT_ID'),
					GetMessage('PERFMON_ERR_FILE'),
					GetMessage('PERFMON_ERR_TEXT'),
				],
				'reference_id' => [
					'hit_id',
					'file',
					'text',
				]
			];
			echo SelectBoxFromArray('find_type', $arr, $currentFilter['find_type'], '', '');
			?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage('PERFMON_ERR_HIT_ID')?></td>
		<td><input type="text" name="find_hit_id" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_hit_id']) ?>">
		</td>
	</tr>
	<tr>
		<td><?=GetMessage('PERFMON_ERR_NO')?></td>
		<td>
			<div class="adm-list">
			<?php foreach ($arErrorCodes as $key => $value): ?>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							id="ck_<?php echo $key ?>"
							value="<?php echo $key ?>"
							name="find_errno[]" <?php if (is_array($currentFilter['find_errno']) && in_array($key, $currentFilter['find_errno']))
{
							echo 'checked'; }
?>
						/>
					</div>
					<div class="adm-list-label">
						<label for="ck_<?php echo $key ?>"><?php echo $value ?></label>
					</div>
				</div>
			<?php endforeach ?>
			</div>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage('PERFMON_ERR_FILE')?></td>
		<td><input type="text" name="find_errfile" size="47"
			value="<?php echo htmlspecialcharsbx($currentFilter['find_errfile']) ?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage('PERFMON_ERR_TEXT')?></td>
		<td><input type="text" name="find_errstr" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_errstr']) ?>">
		</td>
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

<?php $lAdmin->DisplayList(); ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
