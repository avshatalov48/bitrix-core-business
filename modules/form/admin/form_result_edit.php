<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/
ob_start();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

define("HELP_FILE","form_result_list.php");
$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule("form");

ClearVars();

$strError = '';
$strNote = '';
$aMenu = array();

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";

$old_module_version = CForm::IsOldVersion();
$bSimple = (COption::GetOptionString("form", "SIMPLE", "Y") == "Y") ? true : false;

/***************************************************************************
							GET | POST processing
****************************************************************************/

// Wow.... It was funny....
// if (intval($WEB_FORM_ID)>0 && intval($WEB_FORM_ID)<=0) $WEB_FORM_ID = intval($WEB_FORM_ID);

$WEB_FORM_ID = intval($WEB_FORM_ID);
$RESULT_ID = intval($RESULT_ID);

if ($RESULT_ID > 0)
{
	$q = CFormResult::GetByID($RESULT_ID);
	if (!($arrResult=$q->Fetch()))
	{
		// result not found
		$title = str_replace("#FORM_ID#","$WEB_FORM_ID",GetMessage("FORM_RESULT_LIST"));
		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		echo "<p><a href='/bitrix/admin/form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."'>".$title."</a></p>";
		echo ShowError(GetMessage("FORM_RESULT_NOT_FOUND"));
		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}

	$WEB_FORM_ID = intval($arrResult["FORM_ID"]);
}
else
{
	$arrResult = array();
}

if($WEB_FORM_ID <= 0)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo ShowError(GetMessage("FORM_NOT_FOUND"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$F_RIGHT = intval(CForm::GetPermission($WEB_FORM_ID)); // form rights
if ($RESULT_ID > 0)
	$arrRESULT_PERMISSION = CFormResult::GetPermissions($RESULT_ID, $v=0); // result rights array
else
{
	$arrRESULT_PERMISSION = array();
	if ($F_RIGHT >= 20)
		$arrRESULT_PERMISSION[] = 'EDIT';
}

$EDIT_ADDITIONAL = "Y"; // whether to edit additional fields
$EDIT_STATUS = "Y"; // whether to edit status

if ($bSimple)
{
	$EDIT_ADDITIONAL = "N"; // whether to edit additional fields
	$EDIT_STATUS = "N"; // whether to edit status
}

// get access rights
$can_edit = false;
$can_view = false;
$can_delete = false;
$can_add = false;
if ($F_RIGHT>=20 || ($RESULT_ID > 0 && $F_RIGHT>=15 && $USER->GetID()==$arrResult["USER_ID"]))
{
	if (in_array("DELETE",$arrRESULT_PERMISSION)) $can_delete = true;
	if (in_array("EDIT",$arrRESULT_PERMISSION)) $can_edit = true;
	if (in_array("VIEW",$arrRESULT_PERMISSION)) $can_view = true;
}

if ($old_module_version!="Y" && $F_RIGHT>=10)
{
	$can_add = true;
}

// ============================ oldies and not goldies ========================
if ($old_module_version=="Y")
{
	// save chosen template
	if ($F_RIGHT>=30 && $_SERVER['REQUEST_METHOD']=="GET" && strlen($_REQUEST['save'])>0 && check_bitrix_sessid())
	{
		$DB->PrepareFields("b_form");
		$arFields = array(
			"TIMESTAMP_X"			=> $DB->GetNowFunction(),
			"EDIT_RESULT_TEMPLATE"	=> "'".$DB->ForSql($str_EDIT_RESULT_TEMPLATE)."'"
			);
		$DB->Update("b_form",$arFields,"WHERE ID='".$WEB_FORM_ID."'",$err_mess.__LINE__);
	}

	if ($can_edit)
	{
		if ($_SERVER['REQUEST_METHOD']=="POST" && intval($WEB_FORM_ID)>0 && (strlen($_REQUEST['web_form_submit'])>0 || strlen($_REQUEST['web_form_apply'])>0) || strlen($_REQUEST['apply'])>0)
		{
			$arrVALUES = $_REQUEST;
			$error = CForm::Check($WEB_FORM_ID, $arrVALUES, $RESULT_ID);

			if (strlen($error)<=0)
			{
				CFormResult::Update($RESULT_ID, $arrVALUES, $EDIT_ADDITIONAL);

				if (strlen($_REQUEST['web_form_submit'])>0) LocalRedirect("form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID);
			}
			else $strError .= $error;

		}
		else $arrVALUES = CFormResult::GetDataByIDForHTML($RESULT_ID, $EDIT_ADDITIONAL);
	}
}
// ============================ oldies finish ========================

$WEB_FORM_ID = intval($WEB_FORM_ID);
$arForm = CForm::GetByID_admin($WEB_FORM_ID);

// result changes saving
if ($old_module_version != 'Y' && $_SERVER['REQUEST_METHOD'] == "POST" && intval($WEB_FORM_ID)>0 && (strlen($_REQUEST['save'])>0 || strlen($_REQUEST['apply'])>0) && check_bitrix_sessid())
{
	$arrVALUES = $_REQUEST;

	$error = CForm::Check($WEB_FORM_ID, $arrVALUES, $RESULT_ID);

	if (strlen($error)<=0)
	{
		$bUpdate = true;
		if (!$RESULT_ID)
		{
			$default_status = CFormStatus::GetDefault($WEB_FORM_ID);
			$status_tmp = $arrVALUES['status_'.$arForm['SID']];

			$arrVALUES['status_'.$arForm['SID']] = $default_status;

			$RESULT_ID = CFormResult::Add($WEB_FORM_ID, $arrVALUES, 'Y', intval($arrVALUES['USER_ID']) > 0 ? intval($arrVALUES['USER_ID']) : false);

			$arrVALUES['status_'.$arForm['SID']] = $status_tmp == $default_status ? 'NOT_REF' : $status_tmp;

			$bUpdate = $RESULT_ID > 0 && $EDIT_ADDITIONAL == 'Y';

			// little hack to prevent doubling of status notification message
		}

		// second update needed to set additional fields
		if ($bUpdate && strlen($strError) <= 0)
			CFormResult::Update($RESULT_ID, $arrVALUES, $EDIT_ADDITIONAL);

		if (strlen($strError) <= 0)
		{
			if (strlen($_REQUEST['apply'])>0) LocalRedirect("/bitrix/admin/form_result_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&RESULT_ID=".$RESULT_ID);
			else LocalRedirect("/bitrix/admin/form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID);
		}
	}
	else $strError .= $error;
}

if ($EDIT_RESULT_TEMPLATE=="") $EDIT_RESULT_TEMPLATE=$arForm["EDIT_RESULT_TEMPLATE"];

$APPLICATION->SetTitle($RESULT_ID > 0 ? str_replace("#RESULT_ID#", $RESULT_ID, GetMessage("FORM_PAGE_TITLE")) : GetMessage('FORM_PAGE_TITLE_ADD'));

CJSCore::Init(array('date'));

$arTabs = array(array("DIV" => "edit1", "TAB" => GetMessage('FORM_RESULT_EDIT_TAB_TITLE'), "ICON" => "form_edit", "TITLE" => GetMessage('FORM_RESULT_EDIT_TAB_DESCRIPTION'.($RESULT_ID > 0 ? '' : '_ADD'))));

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/***************************************************************************
								HTML form
****************************************************************************/

$context = new CAdminContextMenuList($arForm['ADMIN_MENU']);
$context->Show();

echo BeginNote('width="100%"');
?>
<b><?=GetMessage("FORM_FORM_NAME")?></b> [<a title='<?=GetMessage("FORM_EDIT_FORM")?>' href='form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$WEB_FORM_ID?>'><?=$WEB_FORM_ID?></a>]&nbsp;(<?=htmlspecialcharsbx($arForm["SID"])?>)&nbsp;<?=htmlspecialcharsbx($arForm["NAME"])?>
<?
echo EndNote();

if ($can_add)
{
	$aMenu[] = array(
		"ICON"		=> "btn_new",
		"TEXT"		=> GetMessage("FORM_ADD"),
		"TITLE"		=> GetMessage("FORM_NEW_RESULT"),
		"LINK"		=> "/bitrix/admin/form_result_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID
		);
}

/*
if ($can_view)
{
	$aMenu[] = array(
		"TEXT"		=> GetMessage("FORM_VIEW"),
		"LINK"		=> "/bitrix/admin/form_result_view.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&RESULT_ID=".$RESULT_ID
		);
}
*/

if ($can_delete)
{
	$aMenu[] = array(
		"ICON"		=> "btn_delete",
		"TEXT"	=> GetMessage("FORM_DELETE_TITLE"),
		"TITLE"	=> GetMessage("FORM_DELETE_TITLE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("FORM_CONFIRM_DELETE")."'))window.location='form_result_list.php?action=delete&ID=".$RESULT_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		"WARNING"=>"Y"
		);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
if ($can_edit) :
	if ($old_module_version=="Y"):
		echo ShowError($strError);
		echo ShowNote($strNote);
		?>
		<br />
		<table cellspacing="0" cellpadding="2">
			<?if ($F_RIGHT>=25):?>
			<tr>
				<td><b><?=GetMessage("FORM_ID")?></b></td>
				<td><?=$arrResult["ID"]?></td>
			</tr>
			<tr>
				<td><b><?=GetMessage("FORM_FORM_NAME")?></b></td>
				<td><?
				echo "[<a href='form_edit.php?lang=".LANGUAGE_ID."&ID=".$WEB_FORM_ID."'>". $WEB_FORM_ID."</a>]&nbsp;(".htmlspecialcharsbx($arForm["SID"]).")&nbsp;".htmlspecialcharsbx($arForm["NAME"]);
				?></td>
			</tr>
			<?endif;?>
			<?
			if (intval($arrResult["USER_ID"])>0)
			{
				$rsUser = CUser::GetByID($arrResult["USER_ID"]);
				$arUser = $rsUser->Fetch();
				$arrResult["LOGIN"] = $arUser["LOGIN"];
				$arrResult["EMAIL"] = $arUser["USER_EMAIL"];
				$arrResult["USER_NAME"] = $arUser["NAME"]." ".$arUser["LAST_NAME"];
			}
			?>
			<tr>
				<td><b><?=GetMessage("FORM_DATE_CREATE")?></b></td>
				<td><?=$arrResult["DATE_CREATE"]?><?
					if ($F_RIGHT>=25):
						?>&nbsp;&nbsp;&nbsp;<?
						if (intval($arrResult["USER_ID"])>0) :
							echo "[<a title='".GetMessage("FORM_EDIT_USER")."' href='user_edit.php?lang=".LANGUAGE_ID."&ID=".$arrResult["USER_ID"]."'>".$arrResult["USER_ID"]."</a>] (".htmlspecialcharsbx($arrResult["LOGIN"]).") ".htmlspecialcharsbx($arrResult["USER_NAME"])."";
							echo ($arrResult["USER_AUTH"]=="N") ? " ".GetMessage("FORM_NOT_AUTH")."" : "";
						else :
							echo "".GetMessage("FORM_NOT_REGISTERED")."";
						endif;
					endif;
					?></td>
			</tr>
			<tr>
				<td><b><?=GetMessage("FORM_TIMESTAMP")?></b></td>
				<td><?=$arrResult["TIMESTAMP_X"]?></td>
			</tr>
			<?if ($F_RIGHT>=25):?>
			<?if (CModule::IncludeModule("statistic")):?>
			<?if (intval($arrResult["STAT_GUEST_ID"])>0):?>
			<tr>
				<td><b><?=GetMessage("FORM_GUEST")?></b></td>
				<td>[<a title="<?=GetMessage("FORM_GUEST_ALT")?>" href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$arrResult["STAT_GUEST_ID"]?>&set_filter=Y"><?=$arrResult["STAT_GUEST_ID"]?></a>]</td>
			</tr>
			<?endif;?>
			<?if (intval($arrResult["STAT_SESSION_ID"])>0):?>
			<tr>
				<td><b><?=GetMessage("FORM_SESSION")?></b></td>
				<td>[<a title="<?=GetMessage("FORM_SESSION_ALT")?>" href="/bitrix/admin/session_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$arrResult["STAT_SESSION_ID"]?>&set_filter=Y"><?=$arrResult["STAT_SESSION_ID"]?></a>]</td>
			</tr>
			<?endif;?>
			<?endif;?>
			<?endif;?>
		</table>
		<?if ($F_RIGHT>=25):?>
		<form name="form1" action="" method="GET">
		<?echo bitrix_sessid_post();?>
		<input type="hidden" name="WEB_FORM_ID" value="<?=intval($WEB_FORM_ID)?>">
		<input type="hidden" name="RESULT_ID" value="<?=intval($RESULT_ID)?>">
		<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
		<?=GetMessage("FORM_EDIT_RESULT_TEMPLATE")?><?
		echo SelectBoxFromArray("EDIT_RESULT_TEMPLATE", CForm::GetTemplateList("EDIT_RESULT"), htmlspecialcharsbx($EDIT_RESULT_TEMPLATE), "","class='typeselect'",true);
		?>&nbsp;<input <?if ($F_RIGHT<30) echo "disabled"?> type="submit" name="save" value="<?=GetMessage("FORM_SAVE")?>">
		</form>
		<?endif;?>
		<hr /><br />
		<?

		CFormResult::Edit($RESULT_ID, $arrVALUES, $EDIT_RESULT_TEMPLATE, $EDIT_ADDITIONAL, $EDIT_STATUS);

	else :

// *************************************** NORMAL FORM WITHOUT ARCHAISTIC PERVERSIONS ***********************
?>
<form name="form1" action="/bitrix/admin/form_result_edit.php?lang=<?echo LANG?>&WEB_FORM_ID=<?echo $WEB_FORM_ID?><?if($RESULT_ID>0): echo '&RESULT_ID='.$RESULT_ID; endif;?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
<?echo bitrix_sessid_post();?>
<?
	$WEB_FORM_ID = CForm::GetDataByID($WEB_FORM_ID, $arForm, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $EDIT_ADDITIONAL == 'Y' ? 'ALL' : 'N');

	if (!$strError && $RESULT_ID > 0)
		$arrVALUES = CFormResult::GetDataByIDForHTML($RESULT_ID, $EDIT_ADDITIONAL);

	$bResultStatusChangeAccess = in_array("EDIT", $arrRESULT_PERMISSION);

	$arUser = null;
	$rsUser = null;
	if (intval($arrVALUES['USER_ID'] <= 0))
	{
		if ($RESULT_ID > 0)
		{
			if (intval($arrResult["USER_ID"])>0)
			{
				$rsUser = CUser::GetByID($arrResult["USER_ID"]);
			}
		}
		else
		{
			$rsUser = CUser::GetByID($USER->GetID());
		}
	}
	else
	{
		$rsUser = CUser::GetByID($arrVALUES["USER_ID"]);
	}

	if (null != $rsUser)
		$arUser = $rsUser->Fetch();

	$RESULT_STATUS_FORM = '';
	if ($EDIT_STATUS == 'Y' && $bResultStatusChangeAccess)
	{
		$dbStatusList = CFormStatus::GetDropdown($WEB_FORM_ID, array("MOVE"), $arUser['ID']);

		if ($RESULT_ID > 0)
		{
			$RESULT_STATUS_FORM .= '<input type="radio" value="NOT_REF" id="status_'.$arForm['SID'].'_NOT_REF" name="status_'.$arForm['SID'].'" checked="checked" /><label for="status_'.$arForm['SID'].'_NOT_REF">'.GetMessage('FORM_RESULT_EDIT_STATUS_DONTCHANGE').'</label><br />';

			$i = 1;
		}
		else
		{
			$i = 0;
		}

		while ($arStatus = $dbStatusList->Fetch())
		{
			$arStatus['REFERENCE'] = str_replace(
				'['.$arStatus['REFERENCE_ID'].']',
				'[<a href="/bitrix/admin/form_status_edit.php?lang='.LANG.'&WEB_FORM_ID='.$WEB_FORM_ID.'&ID='.$arStatus['REFERENCE_ID'].'">'.$arStatus['REFERENCE_ID'].'</a>]',
				htmlspecialcharsEx($arStatus['REFERENCE'])
			);

			$RESULT_STATUS_FORM .= '<input type="radio" value="'.$arStatus['REFERENCE_ID'].'" id="status_'.$arForm['SID'].'_'.$arStatus['REFERENCE_ID'].'" name="status_'.$arForm['SID'].'" '.($RESULT_ID <= 0 && ($i++ == 0) ? 'checked="checked"' : '').' /><label for="status_'.$arForm['SID'].'_'.$arStatus['REFERENCE_ID'].'">'.$arStatus['REFERENCE'].'</label><br />';

		}
	}

	// start form output
	echo ShowError($strError);

	$tabControl = new CAdminTabControl("tabControl", $arTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage('FORM_RESULT_EDIT_COMMON')?></td>
	</tr>
<?
	if ($RESULT_ID > 0):
?>
	<tr>
		<td>ID:</td>
		<td><?=$RESULT_ID?></td>
	</tr>
<?
	endif;
?>
	<tr>
		<td><?echo GetMessage('FORM_RESULT_EDIT_FORM')?>: </td>
		<td>[<a href="/bitrix/admin/form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$WEB_FORM_ID?>"><?=$WEB_FORM_ID?></a>]&nbsp;<a href="/bitrix/admin/form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$WEB_FORM_ID?>"><?=htmlspecialcharsbx($arForm["NAME"])?> (<?=htmlspecialcharsbx($arForm["SID"])?>)</a></td>
	</tr>
	<tr>
		<td><?echo GetMessage('FORM_RESULT_EDIT_AUTHOR')?>:</td>
		<td>
<?
	if ($RESULT_ID <= 0):
		echo FindUserID("USER_ID", $arUser['ID']);
	elseif (is_array($arUser)):
?>
			[<a title="<?echo GetMessage('FORM_RESULT_EDIT_USER')?>" href='/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$arUser["ID"]?>'><?=$arUser['ID']?></a>] <a title="<?echo GetMessage('FORM_RESULT_EDIT_USER')?>" href='/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$arUser["ID"]?>'><?=htmlspecialcharsbx($arUser["NAME"])?> <?=htmlspecialcharsbx($arUser["LAST_NAME"])?> (<?=htmlspecialcharsbx($arUser['LOGIN'])?>)</a><?if($arrResult["RESULT_USER_AUTH"] == "N"): ?>&nbsp;<?echo GetMessage('FORM_RESULT_EDIT_USER_NOTAUTH')?><?endif;?>
<?
	else:
?>
			<?echo GetMessage('FORM_RESULT_EDIT_USER_NOTREG')?>
<?
	endif;
?>
		</td>
	</tr>
<?
	if ($RESULT_ID > 0):
?>
	<tr>
		<td><?echo GetMessage('FORM_RESULT_EDIT_CREATED')?>:</td>
		<td><?=$arrResult["DATE_CREATE"]?></td>
	</tr>
	<tr>
		<td><?echo GetMessage('FORM_RESULT_EDIT_CHANGED')?>:</td>
		<td><?=$arrResult["TIMESTAMP_X"]?></td>
	</tr>
<?
		if (IsModuleInstalled('statistic'))
		{
?>
	<tr>
		<td><?=GetMessage("FORM_GUEST")?></td>
		<td>[<a title="<?=GetMessage("FORM_GUEST_ALT")?>" href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$arrResult["STAT_GUEST_ID"]?>&find_id_exact_match=Y&set_filter=Y"><?=$arrResult["STAT_GUEST_ID"]?></a>]</td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_SESSION")?></td>
		<td>[<a href="/bitrix/admin/session_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$arrResult["STAT_SESSION_ID"]?>&find_id_exact_match=Y&set_filter=Y"><?=$arrResult["STAT_SESSION_ID"]?></a>]</td>
	</tr>
<?
		}
	endif; // RESULT_ID > 0
	if ($EDIT_STATUS == 'Y'):
?>
<tr class="heading">
	<td colspan="2"><?echo GetMessage('FORM_RESULT_EDIT_STATUS')?></td>
</tr>
<?
		if ($RESULT_ID > 0):
?>
<tr>
	<td valign="top"><?echo GetMessage('FORM_RESULT_EDIT_STATUS_CURRENT')?>: </td>
	<td><b><?echo htmlspecialcharsEx($arrResult["STATUS_TITLE"])?></b></td>
</tr>
<?
		endif;// ($RESULT_ID > 0):
?>
<tr>
	<td valign="top"><?echo GetMessage('FORM_RESULT_EDIT_STATUS_'.($RESULT_ID > 0 ? 'CHANGE' : 'SET'))?>: </td>
	<td><?echo $RESULT_STATUS_FORM?></td>
</tr>
<?
	endif;// EDIT_STATUS=Y

?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage('FORM_RESULT_EDIT_FIELDS')?></td>
	</tr>
<?
	if ($EDIT_ADDITIONAL == 'Y')
	{
		$arQuestionsNew = array();
		$arAdditionalFields = array();
		foreach ($arQuestions as $key => $arQuestion)
		{
			if ($arQuestion['ADDITIONAL'] == 'Y')
				$arQuestionsNew[$key] = $arQuestion;
			else
				$arAdditionalFields[$key] = $arQuestion;
		}

		$arQuestions = array_merge($arAdditionalFields, $arQuestionsNew);
	}

	$q = 0;
	foreach ($arQuestions as $key => $arQuestion)
	{
		$FIELD_SID = $arQuestion["SID"];
		$arQuestion['TITLE'] = trim($arQuestion['TITLE']);
		if (strlen($arQuestion['TITLE']) <= 0)
			$arQuestion['TITLE'] = $arQuestion['SID'];

		if ($arQuestion['ADDITIONAL'] == 'Y' && ($q++) == 0):
?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage('FORM_RESULT_EDIT_FIELDS_ADDITIONAL')?></td>
	</tr>
<?
			endif;
?>
	<tr<?=$arQuestion["REQUIRED"] == "Y" ? ' class="adm-detail-required-field"' : ''?>>
		<td valign="top">
<?
		echo $arQuestion["TITLE_TYPE"] == "html" ? $arQuestion["TITLE"] : nl2br(htmlspecialcharsbx(trim($arQuestion["TITLE"])));
?>
		</td><td>
<?
		if (is_array($arAnswers[$FIELD_SID]))
		{
			$show_dropdown = "N";
			$show_multiselect = "N";

			foreach ($arAnswers[$FIELD_SID] as $key => $arAnswer)
			{
				$arAnswer['MESSAGE'] = trim($arAnswer['MESSAGE']);

				if ($arAnswer["FIELD_TYPE"]=="dropdown" && $show_dropdown=="Y") continue;
				if ($arAnswer["FIELD_TYPE"]=="multiselect" && $show_multiselect=="Y") continue;

				switch ($arAnswer["FIELD_TYPE"])
				{
					case "radio":
						$arAnswer["FIELD_PARAM"] .= " id=\"".$arAnswer['ID']."\"";

						$value = CForm::GetRadioValue($FIELD_SID, $arAnswer, $arrVALUES);
						if (strlen($strError) > 0 || !$value || $value != $arAnswer["ID"])
						{
							if (
								strpos(strtolower($arAnswer["FIELD_PARAM"]), "selected")!==false
								||
								strpos(strtolower($arAnswer["FIELD_PARAM"]), "checked")!==false)
								{
									$arAnswer["FIELD_PARAM"] = preg_replace("/checked|selected/i", "", $arAnswer["FIELD_PARAM"]);
								}
						}

						$input = CForm::GetRadioField(
							$FIELD_SID,
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_PARAM"]);

						echo $input;
						echo "<label for=\"".$arAnswer['ID']."\">".htmlspecialcharsbx($arAnswer["MESSAGE"])."</label><br />";

					break;
					case "checkbox":
						$arAnswer["FIELD_PARAM"] .= " id=\"".$arAnswer['ID']."\"";

						$value = CForm::GetCheckBoxValue($FIELD_SID, $arAnswer, $arrVALUES);
						if (strlen($strError) > 0 || !$value)
						{
							if (
								strpos(strtolower($arAnswer["FIELD_PARAM"]), "selected")!==false
								||
								strpos(strtolower($arAnswer["FIELD_PARAM"]), "checked")!==false)
								{
									$arAnswer["FIELD_PARAM"] = preg_replace("/checked|selected/i", "", $arAnswer["FIELD_PARAM"]);
								}
						}

						$input = CForm::GetCheckBoxField(
							$FIELD_SID,
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_PARAM"]);

						echo $input."<label for=\"".$arAnswer['ID']."\">".htmlspecialcharsbx($arAnswer["MESSAGE"])."</label><br />";

					break;
					case "dropdown":
						if ($show_dropdown != "Y")
						{
							$value = CForm::GetDropDownValue($FIELD_SID, $arDropDown, $arrVALUES);

							echo CForm::GetDropDownField(
								$FIELD_SID,
								$arDropDown[$FIELD_SID],
								$value,
								$arAnswer["FIELD_PARAM"]).'<br />';
							$show_dropdown = "Y";
						}

					break;
					case "multiselect":
						if ($show_multiselect!="Y")
						{
							$value = CForm::GetMultiSelectValue($FIELD_SID, $arMultiSelect, $arrVALUES);

							echo CForm::GetMultiSelectField(
								$FIELD_SID,
								$arMultiSelect[$FIELD_SID],
								$value,
								$arAnswer["FIELD_HEIGHT"],
								$arAnswer["FIELD_PARAM"]).'<br />';
							$show_multiselect = "Y";
						}

					break;
					case "text":
						echo $arAnswer["MESSAGE"] ? htmlspecialcharsbx($arAnswer['MESSAGE']).'<br />' : '';

						$value = CForm::GetTextValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						echo CForm::GetTextField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]).'<br />';

					break;
					case "hidden":
						echo $arAnswer["MESSAGE"] ? htmlspecialcharsbx($arAnswer['MESSAGE']).'<br />' : '';

						$value = CForm::GetHiddenValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						$input = CForm::GetHiddenField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_PARAM"]);

						$input = str_replace('type="hidden"', 'type="text"', $input).'&nbsp;'.GetMessage('FORM_RESULT_EDIT_HIDDEN');

						echo $input.'<br />';

					break;
					case "password":
						echo $arAnswer["MESSAGE"] ? htmlspecialcharsbx($arAnswer['MESSAGE']).'<br />' : '';

						$value = CForm::GetPasswordValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						echo CForm::GetPasswordField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]).'<br />';

					break;
					case "email":
						echo $arAnswer["MESSAGE"] ? htmlspecialcharsbx($arAnswer['MESSAGE']).'<br />' : '';

						$value = CForm::GetEmailValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						echo CForm::GetEmailField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]).'<br />';
					break;
					case "url":
						echo $arAnswer["MESSAGE"] ? htmlspecialcharsbx($arAnswer['MESSAGE']).'<br />' : '';

						$value = CForm::GetUrlValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						echo CForm::GetUrlField(
							$arAnswer["ID"],
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]).'<br />';

						break;
					case "textarea":
						echo $arAnswer["MESSAGE"] ? htmlspecialcharsbx($arAnswer['MESSAGE']).'<br />' : '';

						if (intval($arAnswer["FIELD_WIDTH"]) <= 0) $arAnswer["FIELD_WIDTH"] = "40";
						if (intval($arAnswer["FIELD_HEIGHT"]) <= 0) $arAnswer["FIELD_HEIGHT"] = "5";

						$value = CForm::GetTextAreaValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						echo CForm::GetTextAreaField(
							$arAnswer["ID"],
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_HEIGHT"],
							$arAnswer["FIELD_PARAM"],
							$value
							).'<br />';

						break;
					case "date":
						echo $arAnswer["MESSAGE"] ? htmlspecialcharsbx($arAnswer['MESSAGE']).'<br />' : '';

						$value = CForm::GetDateValue($arAnswer["ID"], $arAnswer, $arrVALUES);
						echo CForm::GetDateField(
							$arAnswer["ID"],
							'form1',
							$value,
							$arAnswer["FIELD_WIDTH"],
							$arAnswer["FIELD_PARAM"]).'<br />';

						break;
					case "image":
						echo $arAnswer["MESSAGE"] ? htmlspecialcharsbx($arAnswer['MESSAGE']).'<br />' : '';

						if ($arFile = CFormResult::GetFileByAnswerID($RESULT_ID, $arAnswer["ID"]))
						{
							if (intval($arFile["USER_FILE_ID"])>0)
							{
								if ($arFile["USER_FILE_IS_IMAGE"]=="Y")
								{
									echo CFile::ShowImage($arFile["USER_FILE_ID"], 0, 0, "border=0", "", true);
									echo "<br />";
									echo '<input type="checkbox" value="Y" name="form_image_'.$arAnswer['ID'].'_del" id="form_image_'.$arAnswer['ID'].'_del" /><label for="form_image_'.$arAnswer['ID'].'_del">'.GetMessage('FORM_DELETE_FILE').'</label><br />';
								} //endif;
							} //endif;
						} // endif

						echo CForm::GetFileField(
							$arAnswer["ID"],
							$arAnswer["FIELD_WIDTH"],
							"IMAGE",
							0,
							"",
							$arAnswer["FIELD_PARAM"]).'<br />';

						break;
					case "file":
						echo $arAnswer["MESSAGE"] ? htmlspecialcharsbx($arAnswer['MESSAGE']).'<br />' : '';

						if ($arFile = CFormResult::GetFileByAnswerID($RESULT_ID, $arAnswer["ID"]))
						{
							if (intval($arFile["USER_FILE_ID"])>0)
							{
								echo "<a title=\"".GetMessage("FORM_VIEW_FILE")."\" target=\"_blank\" class=\"tablebodylink\" href=\"/bitrix/tools/form_show_file.php?rid=".$RESULT_ID."&hash=".$arFile["USER_FILE_HASH"]."&lang=".LANGUAGE_ID."\">".htmlspecialcharsbx($arFile["USER_FILE_NAME"])."</a>&nbsp;(";
								echo CFile::FormatSize($arFile["USER_FILE_SIZE"]);
								echo ")&nbsp;&nbsp;[&nbsp;<a title=\"".str_replace("#FILE_NAME#", $arFile["USER_FILE_NAME"], GetMessage("FORM_DOWNLOAD_FILE"))."\" class=\"tablebodylink\" href=\"/bitrix/tools/form_show_file.php?rid=".$RESULT_ID."&hash=".$arFile["USER_FILE_HASH"]."&lang=".LANGUAGE_ID."&action=download\">".GetMessage("FORM_DOWNLOAD")."</a>&nbsp;]<br />";
								echo '<input type="checkbox" value="Y" name="form_file_'.$arAnswer['ID'].'_del" id="form_file_'.$arAnswer['ID'].'_del" /><label for="form_file_'.$arAnswer['ID'].'_del">'.GetMessage('FORM_DELETE_FILE').'</label><br />';

								echo "<br />";
							} //endif;
						} //endif;


						echo CForm::GetFileField(
							$arAnswer["ID"],
							$arAnswer["FIELD_WIDTH"],
							"FILE",
							0,
							"",
							$arAnswer["FIELD_PARAM"]).'<br />';

						break;
				} //endswitch;
			} //endwhile;
		} //endif(is_array($arAnswers[$FIELD_SID]));
		elseif (is_array($arQuestions[$FIELD_SID]) && $arQuestions[$FIELD_SID]["ADDITIONAL"] == "Y")
		{
			switch ($arQuestions[$FIELD_SID]["FIELD_TYPE"])
			{
				case "text":
					$value = CForm::GetTextAreaValue("ADDITIONAL_".$arQuestions[$FIELD_SID]["ID"], array(), $arrVALUES);
					echo CForm::GetTextAreaField(
						"ADDITIONAL_".$arQuestions[$FIELD_SID]["ID"],
						"60",
						"5",
						"",
						$value
						).'<br />';

					break;
				case "integer":
					$value = CForm::GetTextValue("ADDITIONAL_".$arQuestions[$FIELD_SID]["ID"], array(), $arrVALUES);
					echo CForm::GetTextField(
						"ADDITIONAL_".$arQuestions[$FIELD_SID]["ID"],
						$value).'<br />';

					break;
				case "date":
					$value = CForm::GetDateValue("ADDITIONAL_".$arQuestions[$FIELD_SID]["ID"], array(), $arrVALUES);
					echo CForm::GetDateField(
						"ADDITIONAL_".$arQuestions[$FIELD_SID]["ID"],
						'form1',
						$value).'<br />';

					break;
			} //endswitch;
		}
?>
		</td>
	</tr>
<?
	}

	$tabControl->EndTab();
	$tabControl->Buttons(array("disabled"=>(!$can_edit), "back_url"=>"form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID));
	$tabControl->End();
?>
</form>
<?

	endif;

else:
	ShowError(GetMessage("FORM_ACCESS_DENIED_FOR_FORM_RESULTS_EDITING"));
endif;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
