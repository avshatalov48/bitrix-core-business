<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/include.php");

ClearVars();

$isDemo = CAdvContract::IsDemo();
$isManager = CAdvContract::IsManager();
$isAdvertiser = CAdvContract::IsAdvertiser();
$isAdmin = CAdvContract::IsAdmin();

if(!$isAdmin && !$isDemo && !$isManager && !$isAdvertiser) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

/***************************************************************************
						Обработка GET | POST
****************************************************************************/
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("AD_TYPE"), "ICON"=>"banner_type_edit", "TITLE"=>GetMessage("AD_TYPE")),

);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$isEditMode = true;
if ((!$isAdmin && !$isDemo) || $action=="view") $isEditMode = false;

$SID = preg_replace("~[^A-Za-z_0-9]~", "", $SID);
$OLD_SID = preg_replace("~[^A-Za-z_0-9]~", "", $OLD_SID);
$strError = '';

if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && check_bitrix_sessid())
{
	if ($ACTIVE != "Y") $ACTIVE = "N";
	$arFields = array(
		"SID"				=> $SID,
		"ACTIVE"				=> $ACTIVE,
		"SORT"				=> $SORT,
		"NAME"				=> $NAME,
		"DESCRIPTION"			=> $DESCRIPTION
		);
	if ($SID = CAdvType::Set($arFields, $OLD_SID))
	{
		if (strlen($strError)<=0)
		{
			if (strlen($save)>0) LocalRedirect("adv_type_list.php?lang=".LANGUAGE_ID);
			else LocalRedirect("adv_type_edit.php?SID=".$SID."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
		}
	}
	$DB->PrepareFields("b_adv_type");
}
if (strlen($strError)>0)
{
	$original_SID = $SID;
	$SID = $OLD_SID;
}
$rsType = CAdvType::GetByID($SID);
if(!$rsType || !$rsType->ExtractFields())
{
	$str_SORT = CAdvType::GetNextSort();
	$str_ACTIVE = "Y";
}
if (strlen($strError)>0) $DB->InitTableVarsForEdit("b_adv_type", "", "str_");

$sDocTitle = (strlen($SID)>0) ? GetMessage("AD_EDIT_TYPE", array("#SID#" => $SID)) : GetMessage("AD_NEW_TYPE");
$APPLICATION->SetTitle($sDocTitle);

/***************************************************************************
								HTML form
****************************************************************************/

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$aMenu = array(
	array(
		"TEXT"	=> GetMessage("AD_BACK_TO_TYPE_LIST"),
		"LINK"	=> "adv_type_list.php?lang=".LANGUAGE_ID,
		"ICON"	=> "btn_list"
	)
);
if(strlen($SID)>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> GetMessage("AD_STATISTICS"),
		"LINK"	=> "adv_banner_graph.php?find_type_sid=".$SID."&find_what_show[]=ctr&find_banner_summa=Y&set_filter=Y&lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("AD_STATISTICS_TITILE"),
		"ICON"	=> "btn_adv_graph"
		);

	if ($isAdmin || $isDemo)
	{
		if ($action != "view")
		{
			$aMenu[] = array(
				"TEXT"	=> GetMessage("AD_TYPE_VIEW_SETTINGS"),
				"TITLE"	=> GetMessage("AD_TYPE_VIEW_SETTINGS_TITLE"),
				"LINK"	=> "adv_type_edit.php?SID=".$SID."&lang=".LANGUAGE_ID."&action=view",
				"ICON"	=> "btn_adv_view"
			);
		}
		else
		{
			$aMenu[] = array(
				"TEXT"	=> GetMessage("AD_TYPE_EDIT"),
				"TITLE"	=> GetMessage("AD_TYPE_EDIT_TITLE"),
				"LINK"	=> "adv_type_edit.php?SID=".$SID."&lang=".LANGUAGE_ID,
				"ICON"	=> "btn_adv_edit"
			);
		}

		$aMenu[] = array(
			"TEXT"	=> GetMessage("AD_ADD_NEW_TYPE"),
			"LINK"	=> "adv_type_edit.php?lang=".LANGUAGE_ID,
			"TITLE"	=> GetMessage("AD_ADD_NEW_TYPE_TITLE"),
			"ICON"	=> "btn_new"
			);

		$aMenu[] = array(
			"TEXT"	=> GetMessage("AD_DELETE_TYPE"),
			"LINK"	=> "javascript:if(confirm('".GetMessage("AD_DELETE_TYPE_CONFIRM")."'))window.location='adv_type_list.php?ID=".$SID."&lang=".LANGUAGE_ID."&action=delete&sessid=".bitrix_sessid()."';",
			"ICON"	=> "btn_delete"
			);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<?=CAdminMessage::ShowMessage($strError)?>
<form name="form1" method="POST" action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<input type="hidden" name="action" value="<?=htmlspecialcharsbx($action)?>">
<input type="hidden" name="OLD_SID" value="<?=$SID?>">
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>

	<?if (strlen($SID)>0):?>
	<?if (strlen($str_DATE_CREATE)>0) :?>
	<tr valign="top">
		<td><?=GetMessage("AD_CREATED")?></td>
		<td><?=$str_DATE_CREATE?><?
		if (intval($str_CREATED_BY)>0) :
			$rsUser = CUser::GetByID($str_CREATED_BY);
			$arUser = $rsUser->Fetch();
			echo "&nbsp;&nbsp;[<a href='/bitrix/admin/user_edit.php?ID=".$str_CREATED_BY."' title='".GetMessage("AD_USER_ALT")."'>".$str_CREATED_BY."</a>]&nbsp;(".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
		endif;
		?></td>
	</tr>
	<?endif;?>
	<?if (strlen($str_DATE_MODIFY)>0) :?>
	<tr valign="top">
		<td><?=GetMessage("AD_MODIFIED")?></td>
		<td><?=$str_DATE_MODIFY?><?
		if (intval($str_MODIFIED_BY)>0) :
			$rsUser = CUser::GetByID($str_MODIFIED_BY);
			$arUser = $rsUser->Fetch();
			echo "&nbsp;&nbsp;[<a href='/bitrix/admin/user_edit.php?ID=".$str_MODIFIED_BY."' title='".GetMessage("AD_USER_ALT")."'>".$str_MODIFIED_BY."</a>]&nbsp;(".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
		endif;
		?></td>
	</tr>
	<?endif;?>
	<?endif;?>

	<tr>
		<td width="40%"><label for="active"><?echo GetMessage("AD_ACTIVE")?></label></td>
		<td width="60%"><?
			if ($isEditMode) :
				echo InputType("checkbox", "ACTIVE", "Y", $str_ACTIVE, false, "", 'id="active"');
			else :
				echo ($str_ACTIVE=="Y") ? GetMessage("AD_YES") : GetMessage("AD_NO");
			endif;
			?></td>
	<tr>
		<td><?echo GetMessage("AD_SORT")?></td>
		<td><?
			if ($isEditMode) :
				?><input type="text" name="SORT" value="<?=$str_SORT?>" size="6" maxlength="18"><?
			else :
				echo $str_SORT;
			endif;
			?></td>
	</tr>
	<tr valign="top">
		<td><?
			if ($isEditMode) :
				?><span style="font-weight: bold;"><?=GetMessage("AD_SID")?><br><?=GetMessage("AD_SID_ALT")?></span><?
			else :
				echo GetMessage("AD_SID");
			endif;
			?></td>
		<td><?
			if ($isEditMode) :
				?><input maxlength="255" type="text" name="SID" size="20" value="<?echo (strlen($strError)>0) ? $original_SID : $str_SID?>"><?
			else :
				echo $str_SID;
			endif;
			?></td>
	</tr>
	<tr>
		<td><?=GetMessage("AD_NAME")?></td>
		<td><?
			if ($isEditMode) :
				?><input maxlength="255" type="text" name="NAME" size="40" value="<?=$str_NAME?>"><?
			else :
				echo $str_NAME;
			endif;
			?></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("AD_DESCRIPTION")?></td>
		<td><?
			if ($isEditMode) :
				?><textarea cols="45" name="DESCRIPTION" rows="8" maxlength="2000"><?=$str_DESCRIPTION?></textarea><?
			else :
				echo TxtToHTML($str_DESCRIPTION);
			endif;
			?></td>
	</tr>

<?
$disable = true;
if(($isAdmin || $isDemo) && $isEditMode)
		$disable = false;

$tabControl->Buttons(array("disabled" => $disable, "back_url"=>"adv_type_list.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
