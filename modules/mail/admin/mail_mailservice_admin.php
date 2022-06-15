<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if ($MOD_RIGHT < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
Bitrix\Main\Loader::includeModule('mail');

$err_mess = "File: ".__FILE__."<br>Line: ";


$sTableID = "t_mailservice_admin";
$oSort = new CAdminSorting($sTableID, "id", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);


$filter = new CAdminFilter(
	$sTableID."_filter_id", 
	array(
		"ID",
		GetMessage("MAIL_MSERVICE_ADM_SITE_ID"),
		GetMessage("MAIL_MSERVICE_ADM_ACTIVE"),
		GetMessage("MAIL_MSERVICE_ADM_NAME"),
		GetMessage("MAIL_MSERVICE_ADM_SERVER")
	)
);


$arFilterFields = Array(
	"find_id",
	"find_site_id",
	"find_active",
	"find_name",
	"find_server"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	"ID"       => $find_id,
	"=SITE_ID" => $find_site_id,
	"ACTIVE"   => mb_strtoupper($find_active),
	"NAME"     => mb_strtoupper($find_name),
	"SERVER"   => mb_strtoupper($find_server)
);

if ($MOD_RIGHT == "W" && $lAdmin->EditAction())
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!Bitrix\Mail\MailServicesTable::update($ID, $arFields)->isSuccess())
		{
			$e = $APPLICATION->GetException();
			$lAdmin->AddUpdateError(GetMessage("MAIL_MSERVICE_SAVE_ERROR")." #".$ID.": ".$e->GetString(), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if ($MOD_RIGHT == "W" && $arID = $lAdmin->GroupAction())
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$rsData = Bitrix\Mail\MailServicesTable::getList(array('filter' => array_filter($arFilter), 'order' => array(mb_strtoupper($by) => $order)));
		while (($arRes = $rsData->fetch()) !== false)
			$arID[] = $arRes['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;
		$ID = intval($ID);

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if (!Bitrix\Mail\MailServicesTable::delete($ID)->isSuccess())
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("MAIL_MSERVICE_DELETE_ERROR"), $ID);
				}
				$DB->Commit();
			break;

			case "activate":
			case "deactivate":

			$arFields = array('ACTIVE' => $_REQUEST['action'] == 'activate' ? 'Y' : 'N');

			if (!Bitrix\Mail\MailServicesTable::update($ID, $arFields)->isSuccess())
			{
				if ($e = $APPLICATION->GetException())
				{
					$lAdmin->AddGroupError(GetMessage('SAVE_ERROR').$ID.": ".$e->GetString(), $ID);
				}
			}

			break;
		}
	}
}

$rsData = Bitrix\Mail\MailServicesTable::getList(array('filter' => array_filter($arFilter), 'order' => array(mb_strtoupper($by) => $order)));
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("MAIL_MSERVICE_ADM_TITLE")));

$arHeaders = Array();

$arHeaders[] = array('id' => 'NAME', 'content' => GetMessage('MAIL_MSERVICE_ADM_NAME'), 'default' => true, 'sort' => 'name');
$arHeaders[] = array('id' => 'ACTIVE', 'content' => GetMessage('MAIL_MSERVICE_ADM_ACTIVE'), 'default' => true, 'sort' => 'active');
$arHeaders[] = array('id' => 'SERVER', 'content' => GetMessage('MAIL_MSERVICE_ADM_SERVER'), 'default' => true, 'sort' => 'server');
$arHeaders[] = array('id' => 'SERVICE_TYPE', 'content' => GetMessage('MAIL_MSERVICE_ADM_TYPE'), 'default' => true, 'sort' => 'service_type');
$arHeaders[] = array('id' => 'ID', 'content' => 'ID', 'default' => true, 'sort' => 'id');

$lAdmin->AddHeaders($arHeaders);

while ($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME", Array("size" => "35"));
	$row->AddInputField("SERVER", Array("size" => "35"));

	$arActions = array(
		array(
			"ICON" => "edit",
			"DEFAULT" => "Y",
			"TEXT" => GetMessage("MAIL_MSERVICE_ADM_CHANGE"),
			"ACTION" => $lAdmin->ActionRedirect("mail_mailservice_edit.php?ID=".$f_ID."&lang=".LANG)
		)
	);

	if ($MOD_RIGHT=="W")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("MAIL_MSERVICE_ADM_DELETE"),
			"ACTION" => "if (confirm('".GetMessage('MAIL_MSERVICE_ADM_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(array(
	array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
	array("counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"),
));

if ($MOD_RIGHT == "W")
{
	$lAdmin->AddGroupActionTable(array(
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	));
}

$aContext = array(
	array(
		"ICON" => "btn_new",
		"TEXT" => GetMessage("MAIN_ADD"),
		"LINK" => "mail_mailservice_edit.php?lang=".LANGUAGE_ID,
		"TITLE" => GetMessage("MAIN_ADD")
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("MAIL_MSERVICE_ADM_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage(); ?>?">
<? $filter->Begin(); ?>

<tr>
	<td nowrap>ID:</td>
	<td nowrap><input type="text" name="find_id" value="<?=htmlspecialcharsbx($find_id); ?>" size="47"><?=ShowFilterLogicHelp(); ?></td>
</tr>
<tr>
	<td nowrap><?=GetMessage("MAIL_MSERVICE_ADM_SITE_ID"); ?>:</td>
	<td nowrap>
		<select name="find_site_id">
			<option value=""><?=GetMessage("MAIL_MSERVICE_ADM_FILT_ANY"); ?></option>
			<? $result = Bitrix\Main\SiteTable::getList(array('filter' => array('ACTIVE' => 'Y'), 'order' => array('SORT' => 'ASC'))); ?>
			<? while (($site = $result->fetch()) !== false): ?>
				<option value="<?=$site['LID'] ?>"<? if ($find_site_id == $site['LID']): ?> selected<? endif ?>><?=$site['NAME'] ?></option>
			<? endwhile ?>
		</select>
	</td>
</tr>
<tr>
	<td nowrap><?=GetMessage("MAIL_MSERVICE_ADM_ACTIVE"); ?>:</td>
	<td nowrap>
		<? $arr = array("reference" => array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id" => array("Y", "N")); ?>
		<?=SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("MAIL_MSERVICE_ADM_FILT_ANY")); ?>
	</td>
</tr>
<tr>
	<td nowrap><?=GetMessage("MAIL_MSERVICE_ADM_NAME"); ?>:</td>
	<td nowrap><input type="text" name="find_name" value="<?=htmlspecialcharsbx($find_name); ?>" size="47"><?=ShowFilterLogicHelp(); ?></td>
</tr>
<tr>
	<td nowrap><?=GetMessage("MAIL_MSERVICE_ADM_SERVER"); ?></td>
	<td nowrap><input type="text" name="find_server" value="<?=htmlspecialcharsbx($find_server); ?>" size="47"><?=ShowFilterLogicHelp(); ?></td>
</tr>

<? $filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1")); $filter->End(); ?>

</form>

<? $lAdmin->DisplayList(); ?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
