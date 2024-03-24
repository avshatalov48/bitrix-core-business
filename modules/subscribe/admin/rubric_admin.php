<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/prolog.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = CMain::GetUserRight('subscribe');
if ($POST_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$sTableID = 'tbl_rubric';
$oSort = new CAdminSorting($sTableID, 'ID', 'desc');
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = [
	'find',
	'find_type',
	'find_id',
	'find_name',
	'find_lid',
	'find_active',
	'find_visible',
	'find_auto',
	'find_code',
];

$currentFilter = $lAdmin->InitFilter($FilterArr);
foreach ($FilterArr as $fieldName)
{
	$currentFilter[$fieldName] = ($currentFilter[$fieldName] ?? '');
}

$arFilter = [
	'ID' => ($currentFilter['find'] !== '' && $currentFilter['find_type'] === 'id' ? $currentFilter['find'] : $currentFilter['find_id']),
	'NAME' => ($currentFilter['find'] !== '' && $currentFilter['find_type'] === 'name' ? $currentFilter['find'] : $currentFilter['find_name']),
	'LID' => $currentFilter['find_lid'],
	'ACTIVE' => $currentFilter['find_active'],
	'VISIBLE' => $currentFilter['find_visible'],
	'AUTO' => $currentFilter['find_auto'],
	'CODE' => $currentFilter['find_code'],
];

if ($lAdmin->EditAction() && $POST_RIGHT == 'W')
{
	foreach ($request['FIELDS'] as $ID => $arFields)
	{
		if (!$lAdmin->IsUpdated($ID))
		{
			continue;
		}
		$DB->StartTransaction();
		$ID = intval($ID);
		$cData = new CRubric;
		if (($rsData = CRubric::GetByID($ID)) && ($arData = $rsData->Fetch()))
		{
			foreach ($arFields as $key => $value)
			{
				$arData[$key] = $value;
			}
			if (!$cData->Update($ID, $arData))
			{
				$lAdmin->AddGroupError(GetMessage('rub_save_error') . ' ' . $cData->LAST_ERROR, $ID);
				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
			}
		}
		else
		{
			$lAdmin->AddGroupError(GetMessage('rub_save_error') . ' ' . GetMessage('rub_no_rubric'), $ID);
			$DB->Rollback();
		}
	}
}

$arID = $lAdmin->GroupAction();
if ($arID && $POST_RIGHT == 'W')
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$rsData = CRubric::GetList([$by => $order], $arFilter);
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
		switch ($lAdmin->GetAction())
		{
		case 'delete':
			@set_time_limit(0);
			$DB->StartTransaction();
			if (!CRubric::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage('rub_del_err'), $ID);
			}
			else
			{
				$DB->Commit();
			}
			break;
		case 'activate':
		case 'deactivate':
			if (($rsData = CRubric::GetByID($ID)) && ($arFields = $rsData->Fetch()))
			{
				$cData = new CRubric;
				$arFields['ACTIVE'] = ($lAdmin->GetAction() == 'activate' ? 'Y' : 'N');
				if (!$cData->Update($ID, $arFields))
				{
					$lAdmin->AddGroupError(GetMessage('rub_save_error') . $cData->LAST_ERROR, $ID);
				}
			}
			else
			{
				$lAdmin->AddGroupError(GetMessage('rub_save_error') . ' ' . GetMessage('rub_no_rubric'), $ID);
			}
			break;
		}
	}
}

$rsData = CRubric::GetList([$by => $order], $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('rub_nav')));

$lAdmin->AddHeaders([
	[
		'id' => 'ID',
		'content' => 'ID',
		'sort' => 'id',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'NAME',
		'content' => GetMessage('rub_name'),
		'sort' => 'name',
		'default' => true,
	],
	[
		'id' => 'LID',
		'content' => GetMessage('rub_site'),
		'sort' => 'lid',
		'default' => true,
	],
	[
		'id' => 'SORT',
		'content' => GetMessage('rub_sort'),
		'sort' => 'sort',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'CODE',
		'content' => GetMessage('rub_code'),
		'sort' => 'name',
		'default' => false,
	],
	[
		'id' => 'ACTIVE',
		'content' => GetMessage('rub_act'),
		'sort' => 'act',
		'default' => true,
	],
	[
		'id' => 'VISIBLE',
		'content' => GetMessage('rub_visible'),
		'sort' => 'visible',
		'default' => true,
	],
	[
		'id' => 'AUTO',
		'content' => GetMessage('rub_auto'),
		'sort' => 'auto',
		'default' => true,
	],
	[
		'id' => 'LAST_EXECUTED',
		'content' => GetMessage('rub_last_exec'),
		'sort' => 'last_executed',
		'default' => true,
	],
]);

while ($arRes = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arRes['ID'], $arRes);

	$row->AddInputField('NAME', ['size' => 20]);
	$row->AddViewField('NAME', '<a href="rubric_edit.php?ID=' . $arRes['ID'] . '&amp;lang=' . LANGUAGE_ID . '">' . $arRes['NAME'] . '</a>');
	$row->AddEditField('LID', CLang::SelectBox('FIELDS[' . $arRes['ID'] . '][LID]', $arRes['LID']));
	$row->AddInputField('SORT', ['size' => 6]);
	$row->AddInputField('CODE', ['size' => 20]);
	$row->AddCheckField('ACTIVE');
	$row->AddCheckField('VISIBLE');
	$row->AddViewField('AUTO', $arRes['AUTO'] == 'Y' ? GetMessage('POST_U_YES') : GetMessage('POST_U_NO'));

	$arActions = [];

	$arActions[] = [
		'ICON' => 'edit',
		'DEFAULT' => true,
		'TEXT' => GetMessage('rub_edit'),
		'ACTION' => $lAdmin->ActionRedirect('rubric_edit.php?ID=' . $arRes['ID'])
	];
	if ($POST_RIGHT >= 'W')
	{
		$arActions[] = [
			'ICON' => 'delete',
			'TEXT' => GetMessage('rub_del'),
			'ACTION' => "if(confirm('" . GetMessage('rub_del_conf') . "')) " . $lAdmin->ActionDoGroup($arRes['ID'], 'delete')
		];
	}

	$arActions[] = ['SEPARATOR' => true];

	if ($arRes['TEMPLATE'] <> '' && $arRes['AUTO'] == 'Y')
	{
		$arActions[] = [
			'ICON' => '',
			'TEXT' => GetMessage('rub_check'),
			'ACTION' => $lAdmin->ActionRedirect('template_test.php?ID=' . $arRes['ID'])
		];
	}

	if (is_set($arActions[count($arActions) - 1], 'SEPARATOR'))
	{
		unset($arActions[count($arActions) - 1]);
	}
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	[
		['title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'), 'value' => $rsData->SelectedRowsCount()],
		['counter' => true, 'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'), 'value' => '0'],
	]
);
$lAdmin->AddGroupActionTable([
	'delete' => GetMessage('MAIN_ADMIN_LIST_DELETE'),
	'activate' => GetMessage('MAIN_ADMIN_LIST_ACTIVATE'),
	'deactivate' => GetMessage('MAIN_ADMIN_LIST_DEACTIVATE'),
]);

$aContext = [
	[
		'TEXT' => GetMessage('MAIN_ADD'),
		'LINK' => 'rubric_edit.php?lang=' . LANGUAGE_ID,
		'TITLE' => GetMessage('POST_ADD_TITLE'),
		'ICON' => 'btn_new',
	],
];
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('rub_title'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$oFilter = new CAdminFilter(
	$sTableID . '_filter',
	[
		'ID',
		GetMessage('rub_f_name'),
		GetMessage('rub_f_site'),
		GetMessage('rub_f_active'),
		GetMessage('rub_f_public'),
		GetMessage('rub_f_auto'),
		GetMessage('rub_f_code'),
	]
);
?>
<form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage();?>">
<?php $oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage('rub_f_find')?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?php echo htmlspecialcharsbx($currentFilter['find'])?>" title="<?=GetMessage('rub_f_find_title')?>">
		<?php
		$arr = [
			'reference' => [
				'ID',
				GetMessage('rub_f_name'),
			],
			'reference_id' => [
				'id',
				'name',
			]
		];
		echo SelectBoxFromArray('find_type', $arr, $currentFilter['find_type'], '', '');
		?>
	</td>
</tr>
<tr>
	<td><?='ID'?>:</td>
	<td>
		<input type="text" name="find_id" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_id'])?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage('rub_f_name')?>:</td>
	<td>
		<input type="text" name="find_name" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_name'])?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage('rub_f_site') . ':'?></td>
	<td><select name="find_lid">
		<option value=""<?php echo ($currentFilter['find_lid'] == '' ? ' selected' : '') ?>><?php echo GetMessage('MAIN_ALL')?></option>
		<?php
		$dbSites = CSite::GetList('NAME', 'asc');
		while ($arSites = $dbSites->Fetch())
		{
			?><option value="<?php echo htmlspecialcharsbx($arSites['ID']) ?>"<?php echo ($currentFilter['find_lid'] == $arSites['ID'] ? ' selected' : '') ?>>(<?php echo htmlspecialcharsbx($arSites['ID']) ?>) <?php echo htmlspecialcharsbx($arSites['NAME']) ?></option><?php
		}
		?>
	</select></td>
</tr>
<tr>
	<td><?=GetMessage('rub_f_active')?>:</td>
	<td>
		<?php
		$arr = [
			'reference' => [
				GetMessage('MAIN_YES'),
				GetMessage('MAIN_NO'),
			],
			'reference_id' => [
				'Y',
				'N',
			]
		];
		echo SelectBoxFromArray('find_active', $arr, $currentFilter['find_active'], GetMessage('MAIN_ALL'), '');
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage('rub_f_public')?>:</td>
	<td><?php echo SelectBoxFromArray('find_visible', $arr, $currentFilter['find_visible'], GetMessage('MAIN_ALL'), '');?></td>
</tr>
<tr>
	<td><?=GetMessage('rub_f_auto')?>:</td>
	<td><?php echo SelectBoxFromArray('find_auto', $arr, $currentFilter['find_auto'], GetMessage('MAIN_ALL'), '');?></td>
</tr>
<tr>
	<td><?=GetMessage('rub_f_code')?>:</td>
	<td>
		<input type="text" name="find_code" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_code'])?>">
	</td>
</tr>
<?php
$oFilter->Buttons(['table_id' => $sTableID,'url' => $APPLICATION->GetCurPage(),'form' => 'find_form']);
$oFilter->End();
?>
</form>

<?php $lAdmin->DisplayList();?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
