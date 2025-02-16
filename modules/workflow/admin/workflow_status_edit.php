<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/prolog.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight('workflow');
if ($WORKFLOW_RIGHT == 'D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

/* @var $request \Bitrix\Main\HttpRequest */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/include.php';
IncludeModuleLangFile(__FILE__);
define('HELP_FILE', 'workflow_status_list.php');

$ID = intval($request['ID']);
$message = false;
$bVarsFromForm = false;

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => GetMessage('FLOW_EDIT_RECORD'),
		'ICON' => 'workflow_edit',
		'TITLE' => GetMessage('FLOW_EDIT_RECORD'),
	],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs);

if (
	$request->isPost()
	&& (
		(string)$request['save'] !== ''
		|| (string)$request['apply'] !== ''
	)
	&& $WORKFLOW_RIGHT == 'W'
	&& check_bitrix_sessid()
)
{
	$obWorkflowStatus = new CWorkflowStatus();

	$arFields = [
		'~TIMESTAMP_X' => $DB->GetNowFunction(),
		'C_SORT' => $request['C_SORT'],
		'ACTIVE' => $request['ACTIVE'] !== 'Y' ? 'N' : 'Y',
		'TITLE' => $request['TITLE'],
		'DESCRIPTION' => $request['DESCRIPTION'],
		'NOTIFY' => $request['NOTIFY'] !== 'Y' ? 'N' : 'Y',
	];
	if ($ID > 0)
	{
		$res = $obWorkflowStatus->Update($ID, $arFields);
	}
	else
	{
		$ID = $obWorkflowStatus->Add($arFields);
		$res = ($ID > 0);
	}

	if ($res)
	{
		$arPERMISSION_M = is_array($request['arPERMISSION_M']) ? $request['arPERMISSION_M'] : [];
		$obWorkflowStatus->SetPermissions($ID, $arPERMISSION_M, 1);
		$arPERMISSION_E = is_array($request['arPERMISSION_E']) ? $request['arPERMISSION_E'] : [];
		$obWorkflowStatus->SetPermissions($ID, $arPERMISSION_E, 2);

		if ($request['apply'])
		{
			LocalRedirect('/bitrix/admin/workflow_status_edit.php?ID=' . $ID . '&lang=' . LANG . '&' . $tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect('/bitrix/admin/workflow_status_list.php?lang=' . LANG);
		}
	}
	else
	{
		if ($e = $APPLICATION->GetException())
		{
			$message = new CAdminMessage(GetMessage('FLOW_ERROR'), $e);
		}
		$bVarsFromForm = true;
	}
}

ClearVars();
$str_ACTIVE = 'Y';
$str_C_SORT = CWorkflowStatus::GetNextSort();
$str_TIMESTAMP_X = '';
$str_TITLE = '';
$str_DESCRIPTION = '';
$str_NOTIFY = '';

if ($ID > 0)
{
	$status = CWorkflowStatus::GetByID($ID);
	if (!$status->ExtractFields('str_'))
	{
		$ID = 0;
	}
}

$arPERMISSION_M = [];
$arPERMISSION_E = [];
if ($bVarsFromForm)
{
	$DB->InitTableVarsForEdit('b_workflow_status', '', 'str_');
	if (is_array($request->getPost('arPERMISSION_M')))
	{
		$arPERMISSION_M = $request->getPost('arPERMISSION_M');
	}
	if (is_array($request->getPost('arPERMISSION_E')))
	{
		$arPERMISSION_E = $request->getPost('arPERMISSION_E');
	}
}
elseif ($ID > 0)
{
	$strSql = '
		SELECT
			GROUP_ID,
			PERMISSION_TYPE
		FROM
			b_workflow_status2group
		WHERE
			STATUS_ID=' . $ID . '
		';
	$z = $DB->Query($strSql);
	while ($zr = $z->Fetch())
	{
		if ($zr['PERMISSION_TYPE'] == '1')
		{
			$arPERMISSION_M[] = $zr['GROUP_ID'];
		}
		elseif ($zr['PERMISSION_TYPE'] == '2')
		{
			$arPERMISSION_E[] = $zr['GROUP_ID'];
		}
	}
}

$sDocTitle = ($ID > 0) ? GetMessage('FLOW_EDIT_RECORD', ['#ID#' => $ID]) : GetMessage('FLOW_NEW_RECORD');
$APPLICATION->SetTitle($sDocTitle);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aMenu = [
	[
		'ICON' => 'btn_list',
		'TEXT' => GetMessage('FLOW_RECORDS_LIST'),
		'LINK' => 'workflow_status_list.php?lang=' . LANGUAGE_ID,
	],
];
if (intval($ID) > 0)
{
	$aMenu[] = [
		'SEPARATOR' => 'Y',
	];
	$aMenu[] = [
		'ICON' => 'btn_new',
		'TEXT' => GetMessage('FLOW_NEW_STATUS'),
		'LINK' => 'workflow_status_edit.php?lang=' . LANGUAGE_ID,
	];
	if ($WORKFLOW_RIGHT == 'W' && intval($ID) > 1)
	{
		$aMenu[] = [
			'ICON' => 'btn_delete',
			'TEXT' => GetMessage('FLOW_DELETE_STATUS'),
			'LINK' => "javascript:if(confirm('" . GetMessage('FLOW_DELETE_STATUS_CONFIRM') . "')) window.location='workflow_status_list.php?action=delete&ID=" . $ID . '&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get() . "';",
		];
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
{
	echo $message->Show();
}
?>
<form method="POST" name="form1" action="<?php echo $APPLICATION->GetCurPage()?>?" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?>>
<input type="hidden" name="lang" value="<?=LANG?>">
<?php

$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<?php if ($str_TIMESTAMP_X <> '' && $str_TIMESTAMP_X != '00.00.0000 00:00:00') : ?>
	<tr>
		<td><?=GetMessage('FLOW_TIMESTAMP')?></td>
		<td><?=$str_TIMESTAMP_X?></td>
	</tr>
<?php endif; ?>
<?php if ($ID > 0) : ?>
	<tr>
		<td><?=GetMessage('FLOW_DOCUMENTS')?></td>
		<td><a href="workflow_list.php?lang=<?=LANG?>&find_status=<?=$ID?>&set_filter=Y" title="<?=GetMessage('FLOW_DOCUMENTS_ALT')?>"><?php echo intval($str_DOCUMENTS)?></a></td>
	</tr>
<?php endif;?>
<?php if ($ID != 1):?>
	<tr>
		<td><label for="active"><?=GetMessage('FLOW_ACTIVE')?></label></td>
		<td><?=InputType('checkbox', 'ACTIVE', 'Y', $str_ACTIVE, false, '', 'id="active"')?></td>
	</tr>
<?php endif;?>
	<tr>
		<td width="40%"><?=GetMessage('FLOW_SORTING')?></td>
		<td width="60%"><input type="text" name="C_SORT" size="5" value="<?=$str_C_SORT?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage('FLOW_TITLE')?></td>
		<td><input type="text" name="TITLE" maxlength="255" value="<?=$str_TITLE?>" style="width:60%"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage('FLOW_DESCRIPTION')?></td>
		<td><textarea name="DESCRIPTION" rows="5" style="width:60%"><?php echo $str_DESCRIPTION?></textarea></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage('FLOW_MOVE_RIGHTS');?><br><img src="/bitrix/images/workflow/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?php echo SelectBoxM('arPERMISSION_M[]', CGroup::GetDropDownList(''), $arPERMISSION_M, '', true, 8);?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?php echo GetMessage('FLOW_EDIT_RIGHTS');?><br><img src="/bitrix/images/workflow/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?php echo SelectBoxM('arPERMISSION_E[]', CGroup::GetDropDownList(''), $arPERMISSION_E, '', true, 8);?></td>
	</tr>
	<tr>
		<td><label for="notify"><?=GetMessage('FLOW_NOTIFY')?></label></td>
		<td><?=InputType('checkbox', 'NOTIFY', 'Y', $str_NOTIFY, false, '', 'id="notify"')?></td>
	</tr>
<?php
$tabControl->Buttons([
	'disabled' => $WORKFLOW_RIGHT < 'W',
	'back_url' => 'workflow_status_list.php?lang=' . LANGUAGE_ID,
]);
$tabControl->End();
?>
</form>
<?php $tabControl->ShowWarnings('form1', $message);?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
