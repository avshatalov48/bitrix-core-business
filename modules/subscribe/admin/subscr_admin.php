<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/prolog.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = CMain::GetUserRight('subscribe');
if ($POST_RIGHT <= 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$sTableID = 'tbl_subscr';
$oSort = new CAdminSorting($sTableID, 'ID', 'desc');
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$lAdmin = new CAdminList($sTableID, $oSort);

function SubsrAdminCheckFilter(CAdminList $lAdmin, $find_update_1, $find_update_2, $find_insert_1, $find_insert_2)
{
	$find_update_1 = trim($find_update_1);
	$find_update_2 = trim($find_update_2);
	if ($find_update_1 !== '' || $find_update_2 !== '')
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_update_1,'D.M.Y'),'d.m.Y');
		$date2_stm = MkDateTime(FmtDate($find_update_2,'D.M.Y') . ' 23:59','d.m.Y H:i');

		if (!$date1_stm && $find_update_1 !== '')
		{
			$lAdmin->AddFilterError(GetMessage('POST_WRONG_UPDATE_FROM'));
		}
		else
		{
			$date_1_ok = true;
		}

		if (!$date2_stm && $find_update_2 !== '')
		{
			$lAdmin->AddFilterError(GetMessage('POST_WRONG_UPDATE_TILL'));
		}
		elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
		{
			$lAdmin->AddFilterError(GetMessage('POST_FROM_TILL_UPDATE'));
		}
	}

	$find_insert_1 = trim($find_insert_1);
	$find_insert_2 = trim($find_insert_2);
	if ($find_insert_1 !== '' || $find_insert_2 !== '')
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_insert_1,'D.M.Y'),'d.m.Y');
		$date2_stm = MkDateTime(FmtDate($find_insert_2,'D.M.Y') . ' 23:59','d.m.Y H:i');

		if (!$date1_stm && $find_insert_1 !== '')
		{
			$lAdmin->AddFilterError(GetMessage('POST_WRONG_INSERT_FROM'));
		}
		else
		{
			$date_1_ok = true;
		}

		if (!$date2_stm && $find_insert_2 !== '')
		{
			$lAdmin->AddFilterError(GetMessage('POST_WRONG_INSERT_TILL'));
		}
		elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
		{
			$lAdmin->AddFilterError(GetMessage('POST_FROM_TILL_INSERT'));
		}
	}

	return count($lAdmin->arFilterErrors) == 0;
}

$FilterArr = [
	'find',
	'find_type',
	'find_id',
	'find_update_1',
	'find_update_2',
	'find_insert_1',
	'find_insert_2',
	'find_user',
	'find_user_id',
	'find_anonymous',
	'find_active',
	'find_email',
	'find_format',
	'find_confirmed',
	'find_distribution',
];

$currentFilter = $lAdmin->InitFilter($FilterArr);
foreach ($FilterArr as $fieldName)
{
	$currentFilter[$fieldName] = ($currentFilter[$fieldName] ?? '');
}

$arFilter = [];
if (SubsrAdminCheckFilter($lAdmin, $request['find_update_1'], $request['find_update_2'], $request['find_insert_1'], $request['find_insert_2']))
{
	$arFilter = [
		'ID' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'id' ? $currentFilter['find'] : $currentFilter['find_id']),
		'EMAIL'  => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'email' ? $currentFilter['find'] : $currentFilter['find_email']),
		'UPDATE_1' => $currentFilter['find_update_1'],
		'UPDATE_2' => $currentFilter['find_update_2'],
		'INSERT_1' => $currentFilter['find_insert_1'],
		'INSERT_2' => $currentFilter['find_insert_2'],
		'USER_ID' => $currentFilter['find_user_id'],
		'USER' => ($currentFilter['find'] != '' && $currentFilter['find_type'] == 'user' ? $currentFilter['find'] : $currentFilter['find_user']),
		'ANONYMOUS' => $currentFilter['find_anonymous'],
		'CONFIRMED' => $currentFilter['find_confirmed'],
		'ACTIVE' => $currentFilter['find_active'],
		'FORMAT' => $currentFilter['find_format'],
		'DISTRIBUTION' => $currentFilter['find_distribution'],
	];
}

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
		$ob = new CSubscription;
		if (!$ob->Update($ID, $arFields))
		{
			$lAdmin->AddUpdateError(GetMessage('POST_SAVE_ERROR') . $ID . ': ' . $ob->LAST_ERROR, $ID);
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

$strError = $strOk = '';
$arID = $lAdmin->GroupAction();
if ($arID && $POST_RIGHT == 'W')
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$rsData = CSubscription::GetList([$by => $order], $arFilter);
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
			if (!CSubscription::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage('subscr_del_err'), $ID);
			}
			else
			{
				$DB->Commit();
			}
			break;
		case 'activate':
		case 'deactivate':
			$ob = new CSubscription;
			$arFields = ['ACTIVE' => ($lAdmin->GetAction() == 'activate' ? 'Y' : 'N')];
			if (!$ob->Update($ID, $arFields))
			{
				$lAdmin->AddGroupError(GetMessage('subscr_save_error') . $ob->LAST_ERROR, $ID);
			}
			break;
		case 'confirm':
			$ob = new CSubscription;
			$arFields = ['CONFIRMED' => 'Y'];
			if (!$ob->Update($ID, $arFields))
			{
				$lAdmin->AddGroupError(GetMessage('subscr_save_error') . $ob->LAST_ERROR, $ID);
			}
			break;
		}
	}
}

$rsData = CSubscription::GetList([$by => $order], $arFilter, ['nPageSize' => CAdminResult::GetNavSize($sTableID)]);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('subscr_nav')));

$lAdmin->AddHeaders([
	[
		'id' => 'ID',
		'content' => 'ID',
		'sort' => 'id',
		'align' => 'right',
		'default' => true,
	],
	[
		'id' => 'DATE_INSERT',
		'content' => GetMessage('POST_DATE_INSERT'),
		'sort' => 'date_insert',
		'default' => true,
	],
	[
		'id' => 'EMAIL',
		'content' => GetMessage('subscr_addr'),
		'sort' => 'email',
		'default' => true,
	],
	[
		'id' => 'USER_ID',
		'content' => GetMessage('subscr_user'),
		'sort' => 'user',
		'default' => true,
	],
	[
		'id' => 'CONFIRMED',
		'content' => GetMessage('subscr_conf'),
		'sort' => 'conf',
		'default' => true,
	],
	[
		'id' => 'ACTIVE',
		'content' => GetMessage('subscr_act'),
		'sort' => 'act',
		'default' => true,
	],
	[
		'id' => 'FORMAT',
		'content' => GetMessage('subscr_fmt'),
		'sort' => 'fmt',
		'default' => true,
	],
	[
		'id' => 'DATE_UPDATE',
		'content' => GetMessage('subscr_updated'),
		'sort' => 'date_update',
		'default' => false,
	],
	[
		'id' => 'DATE_CONFIRM',
		'content' => GetMessage('subscr_conf_time'),
		'sort' => 'date_confirm',
		'default' => false,
	],
	[
		'id' => 'CONFIRM_CODE',
		'content' => GetMessage('subscr_conf_code'),
		'sort' => 'confirm_code',
		'default' => false,
	],
]);

while ($arRes = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arRes['ID'], $arRes);

	if ($arRes['USER_ID'] > 0)
	{
		$strUser = "[<a class='tablebodylink' href=\"/bitrix/admin/user_edit.php?ID=" . $arRes['USER_ID'] . '&amp;lang=' . LANGUAGE_ID . '" title="' . GetMessage('subscr_user_edit_title') . '">' . $arRes['USER_ID'] . '</a>] (' . $arRes['USER_LOGIN'] . ') ' . $arRes['USER_NAME'] . ' ' . $arRes['USER_LAST_NAME'];
	}
	else
	{
		$strUser = GetMessage('subscr_adm_anon');
	}
	$row->AddViewField('USER_ID', $strUser);
	$row->AddCheckField('ACTIVE');
	$row->AddInputField('EMAIL', ['size' => 20]);
	$row->AddViewField('EMAIL', '<a href="subscr_edit.php?ID=' . $arRes['ID'] . '&amp;lang=' . LANGUAGE_ID . '" title="' . GetMessage('subscr_upd') . '">' . $arRes['EMAIL'] . '</a>');
	$row->AddSelectField('FORMAT',['text' => GetMessage('POST_TEXT'),'html' => GetMessage('POST_HTML')]);
	$row->AddCheckField('CONFIRMED');

	$arActions = [];

	$arActions[] = [
		'ICON' => 'edit',
		'DEFAULT' => true,
		'TEXT' => GetMessage('subscr_upd'),
		'ACTION' => $lAdmin->ActionRedirect('subscr_edit.php?ID=' . $arRes['ID'])
	];
	if ($POST_RIGHT >= 'W')
	{
		$arActions[] = [
			'ICON' => 'delete',
			'TEXT' => GetMessage('subscr_del'),
			'ACTION' => "if(confirm('" . GetMessage('subscr_del_conf') . "')) " . $lAdmin->ActionDoGroup($arRes['ID'], 'delete')
		];
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
	'confirm' => GetMessage('subscr_confirm'),
]);

$aContext = [
	[
		'TEXT' => GetMessage('MAIN_ADD'),
		'LINK' => 'subscr_edit.php?lang=' . LANGUAGE_ID,
		'TITLE' => GetMessage('subscr_add_title'),
		'ICON' => 'btn_new',
	],
];
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage('subscr_title'));

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$oFilter = new CAdminFilter(
	$sTableID . '_filter',
	[
		GetMessage('POST_F_ID'),
		GetMessage('POST_F_INSERT'),
		GetMessage('POST_F_UPDATE'),
		GetMessage('POST_F_EMAIL'),
		GetMessage('POST_F_ANONYMOUS'),
		GetMessage('POST_F_USER_ID'),
		GetMessage('POST_F_USER'),
		GetMessage('POST_F_CONFIRMED'),
		GetMessage('POST_F_ACTIVE'),
		GetMessage('POST_F_FORMAT'),
		GetMessage('POST_F_DISTRIBUTION'),
	]
);
?>
<form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage();?>">
<?php $oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage('POST_F_FIND')?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?php echo htmlspecialcharsbx($currentFilter['find'])?>" title="<?=GetMessage('POST_F_FIND_TITLE')?>">
		<?php
		$arr = [
			'reference' => [
				GetMessage('POST_F_EMAIL'),
				GetMessage('POST_F_ID'),
				GetMessage('POST_F_USER'),
			],
			'reference_id' => [
				'email',
				'id',
				'user',
			]
		];
		echo SelectBoxFromArray('find_type', $arr, $currentFilter['find_type'], '', '');
		?>
	</td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_ID')?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_id'])?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_INSERT') . ' (' . FORMAT_DATE . '):'?></td>
	<td><?php echo CalendarPeriod('find_insert_1', htmlspecialcharsbx($currentFilter['find_insert_1']), 'find_insert_2', htmlspecialcharsbx($currentFilter['find_insert_2']), 'find_form','Y')?></td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_UPDATE') . ' (' . FORMAT_DATE . '):'?></td>
	<td><?php echo CalendarPeriod('find_update_1', htmlspecialcharsbx($currentFilter['find_update_1']), 'find_update_2', htmlspecialcharsbx($currentFilter['find_update_2']), 'find_form','Y')?></td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_EMAIL')?>:</td>
	<td><input type="text" name="find_email" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_email'])?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage('POST_F_ANONYMOUS')?>:</td>
	<td><?php
		$arr = ['reference' => [GetMessage('MAIN_YES'), GetMessage('MAIN_NO')], 'reference_id' => ['Y','N']];
		echo SelectBoxFromArray('find_anonymous', $arr, htmlspecialcharsbx($currentFilter['find_anonymous']), GetMessage('MAIN_ALL'));
	?></td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_USER_ID')?>:</td>
	<td><input type="text" name="find_user_id" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_user_id'])?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?php echo GetMessage('POST_F_USER')?>:</td>
	<td><input type="text" name="find_user" size="47" value="<?php echo htmlspecialcharsbx($currentFilter['find_user'])?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage('POST_F_CONFIRMED')?>:</td>
	<td><?php
		$arr = ['reference' => [GetMessage('MAIN_YES'), GetMessage('MAIN_NO')], 'reference_id' => ['Y','N']];
		echo SelectBoxFromArray('find_confirmed', $arr, htmlspecialcharsbx($currentFilter['find_confirmed']), GetMessage('MAIN_ALL'));
	?></td>
</tr>
<tr>
	<td><?=GetMessage('POST_F_ACTIVE')?>:</td>
	<td><?php
		$arr = ['reference' => [GetMessage('MAIN_YES'), GetMessage('MAIN_NO')], 'reference_id' => ['Y','N']];
		echo SelectBoxFromArray('find_active', $arr, htmlspecialcharsbx($currentFilter['find_active']), GetMessage('MAIN_ALL'));
	?></td>
</tr>
<tr>
	<td><?=GetMessage('POST_F_FORMAT')?>:</td>
	<td><?php
		$arr = ['reference' => [GetMessage('POST_TEXT'), GetMessage('POST_HTML')], 'reference_id' => ['text','html']];
		echo SelectBoxFromArray('find_format', $arr, htmlspecialcharsbx($currentFilter['find_format']), GetMessage('MAIN_ALL'));
	?></td>
</tr>
<tr valign="top">
	<td><?=GetMessage('POST_F_DISTRIBUTION')?>:</td>
	<td><?php
		$ref = [];
		$ref_id = [];
		$rsRubric = CRubric::GetList(['LID' => 'ASC', 'SORT' => 'ASC', 'NAME' => 'ASC'], ['ACTIVE' => 'Y']);
		while ($arRubric = $rsRubric->Fetch())
		{
			$ref[] = '[' . $arRubric['ID'] . '] (' . $arRubric['LID'] . ') ' . $arRubric['NAME'];
			$ref_id[] = $arRubric['ID'];
		}
		$arr = [
			'reference' => $ref,
			'reference_id' => $ref_id
		];
		echo SelectBoxMFromArray('find_distribution[]', $arr, $currentFilter['find_distribution'], '', false, 5);
	?></td>
</tr>
<?php
$oFilter->Buttons(['table_id' => $sTableID,'url' => $APPLICATION->GetCurPage(),'form' => 'find_form']);
$oFilter->End();
?>
</form>

<?php $lAdmin->DisplayList();?>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
