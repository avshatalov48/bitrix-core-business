<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUserTypeManager $USER_FIELD_MANAGER
 */
use Bitrix\Main;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "settings/userfield_edit.php");

IncludeModuleLangFile(__FILE__);

$ID = intval($_REQUEST['ID'] ?? 0);
$back_url = $_REQUEST["back_url"] ?? '';
$list_url = $_REQUEST["list_url"] ?? '';

$selfFolderUrl = $adminPage->getSelfFolderUrl();
if ($adminSidePanelHelper->isPublicFrame())
{
	$back_url = $adminSidePanelHelper->setDefaultQueryParams($back_url);
}

$RIGHTS = $USER_FIELD_MANAGER->GetRights(false, $ID);
if($RIGHTS < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("USER_TYPE_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("USER_TYPE_TAB_TITLE"),
	)
);

/** @var CUserFieldEnum $obEnum */
$obEnum = null;
if($ID>0)
{
	if($arUserField = CUserTypeEntity::GetByID($ID))
	{
		if($arType = $USER_FIELD_MANAGER->GetUserType($arUserField["USER_TYPE_ID"]))
		{
			if($arType["BASE_TYPE"] == "enum")
			{
				$obEnum = new CUserFieldEnum;
				$aTabs[] = array(
					"DIV" => "edit2",
					"TAB" => GetMessage("USER_TYPE_TAB2"),
					"ICON"=>"main_user_edit",
					"TITLE"=>GetMessage("USER_TYPE_TAB2_TITLE"),
				);
			}
		}
	}
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$message = null;
$bVarsFromForm = false;

if($_SERVER["REQUEST_METHOD"] == "POST" && (!empty($_POST["save"]) || !empty($_POST["apply"])) && ($RIGHTS >= "W") && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();

	$arFields = array(
		"ENTITY_ID" => $_REQUEST["ENTITY_ID"] ?? '',
		"FIELD_NAME" => $_REQUEST["FIELD_NAME"] ?? '',
		"USER_TYPE_ID" => $_REQUEST["USER_TYPE_ID"] ?? '',
		"XML_ID" => $_REQUEST["XML_ID"] ?? '',
		"SORT" => $_REQUEST["SORT"] ?? '',
		"MULTIPLE" => $_REQUEST["MULTIPLE"] ?? '',
		"MANDATORY" => $_REQUEST["MANDATORY"] ?? '',
		"SHOW_FILTER" => $_REQUEST["SHOW_FILTER"] ?? '',
		"SHOW_IN_LIST" => $_REQUEST["SHOW_IN_LIST"] ?? '',
		"EDIT_IN_LIST" => $_REQUEST["EDIT_IN_LIST"] ?? '',
		"IS_SEARCHABLE" => $_REQUEST["IS_SEARCHABLE"] ?? '',
		"SETTINGS" => $_REQUEST["SETTINGS"] ?? '',
		"EDIT_FORM_LABEL" => $_REQUEST["EDIT_FORM_LABEL"] ?? '',
		"LIST_COLUMN_LABEL" => $_REQUEST["LIST_COLUMN_LABEL"] ?? '',
		"LIST_FILTER_LABEL" => $_REQUEST["LIST_FILTER_LABEL"] ?? '',
		"ERROR_MESSAGE" => $_REQUEST["ERROR_MESSAGE"] ?? '',
		"HELP_MESSAGE" => $_REQUEST["HELP_MESSAGE"] ?? '',
	);

	$obUserField  = new CUserTypeEntity;
	if($ID > 0)
	{
		$res = $obUserField->Update($ID, $arFields);
	}
	else
	{
		$ID = $obUserField->Add($arFields);
		$res = ($ID > 0);
	}

	if(is_object($obEnum))
	{
		$LIST = $_REQUEST["LIST"] ?? [];
		if(is_array($LIST))
		{
			foreach($LIST as $id => $value)
				if(is_array($value))
					$LIST[$id]["DEF"] = "N";
		}
		if(isset($LIST["DEF"]) && is_array($LIST["DEF"]))
		{
			foreach($LIST["DEF"] as $value)
				if(is_array($LIST[$value]))
					$LIST[$value]["DEF"] = "Y";
			unset($LIST["DEF"]);
		}
		$res = $obEnum->SetEnumValues($ID, $LIST);
	}

	if($res)
	{
		if ($adminSidePanelHelper->isAjaxRequest())
		{
			$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $ID));
		}
		else
		{
			if (!empty($_POST["apply"]))
			{
				$applyUrl = $selfFolderUrl."userfield_edit.php?ID=".$ID."&lang=".LANG."&back_url=".
					urlencode($back_url)."&".$tabControl->ActiveTabParam();
				$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
				LocalRedirect($applyUrl);
			}
			elseif ($back_url)
			{
				$back_url = $adminSidePanelHelper->editUrlToPublicPage($back_url);
				$adminSidePanelHelper->localRedirect($back_url);
				LocalRedirect($back_url);
			}
			else
			{
				$redirectUrl = $selfFolderUrl."userfield_admin.php?lang=".LANG;
				$redirectUrl = $adminSidePanelHelper->editUrlToPublicPage($redirectUrl);
				$adminSidePanelHelper->localRedirect($redirectUrl);
				LocalRedirect($redirectUrl);
			}
		}
	}
	else
	{
		if($e = $APPLICATION->GetException())
		{
			$message = new CAdminMessage(GetMessage("USER_TYPE_SAVE_ERROR"), $e);
			$adminSidePanelHelper->sendJsonErrorResponse($e->GetString());
		}
		$bVarsFromForm = true;
	}

}

if($ID>0)
{
	$arUserField = CUserTypeEntity::GetByID($ID);
	if(!$arUserField)
		$ID=0;
}
else
{
	$arUserField = array(
		"ENTITY_ID" => $_GET["ENTITY_ID"] ?? "",
		"FIELD_NAME" => $_GET["FIELD_NAME"] ?? "UF_",
		"USER_TYPE_ID" => $_GET["USER_TYPE_ID"] ?? "",
		"XML_ID" => "",
		"SORT" => 100,
		"MULTIPLE" => "N",
		"MANDATORY" => "N",
		"SHOW_FILTER" => "N",
		"SHOW_IN_LIST" => "Y",
		"EDIT_IN_LIST" => "Y",
		"IS_SEARCHABLE" => "N",
		"SETTINGS" => array(),
	);
}

if($bVarsFromForm)
{
	$ENTITY_ID = htmlspecialcharsbx($_REQUEST["ENTITY_ID"] ?? '');
	$FIELD_NAME = htmlspecialcharsbx($_REQUEST["FIELD_NAME"] ?? '');
	$USER_TYPE_ID = htmlspecialcharsbx($_REQUEST["USER_TYPE_ID"] ?? '');
	$XML_ID = htmlspecialcharsbx($_REQUEST["XML_ID"] ?? '');
	$SORT = htmlspecialcharsbx($_REQUEST["SORT"] ?? '');
	$MULTIPLE = htmlspecialcharsbx($_REQUEST["MULTIPLE"] ?? '');
	$MANDATORY = htmlspecialcharsbx($_REQUEST["MANDATORY"] ?? '');
	$SHOW_FILTER = htmlspecialcharsbx($_REQUEST["SHOW_FILTER"] ?? '');
	$SHOW_IN_LIST = htmlspecialcharsbx($_REQUEST["SHOW_IN_LIST"] ?? '');
	$EDIT_IN_LIST = htmlspecialcharsbx($_REQUEST["EDIT_IN_LIST"] ?? '');
	$IS_SEARCHABLE = htmlspecialcharsbx($_REQUEST["IS_SEARCHABLE"] ?? '');
}
else
{
	$ENTITY_ID = htmlspecialcharsbx($arUserField["ENTITY_ID"]);
	$FIELD_NAME = htmlspecialcharsbx($arUserField["FIELD_NAME"]);
	$USER_TYPE_ID = htmlspecialcharsbx($arUserField["USER_TYPE_ID"]);
	$XML_ID = htmlspecialcharsbx($arUserField["XML_ID"]);
	$SORT = htmlspecialcharsbx($arUserField["SORT"]);
	$MULTIPLE = htmlspecialcharsbx($arUserField["MULTIPLE"]);
	$MANDATORY = htmlspecialcharsbx($arUserField["MANDATORY"]);
	$SHOW_FILTER = htmlspecialcharsbx($arUserField["SHOW_FILTER"]);
	$SHOW_IN_LIST = htmlspecialcharsbx($arUserField["SHOW_IN_LIST"]);
	$EDIT_IN_LIST = htmlspecialcharsbx($arUserField["EDIT_IN_LIST"]);
	$IS_SEARCHABLE = htmlspecialcharsbx($arUserField["IS_SEARCHABLE"]);
}

$arUserTypes = $USER_FIELD_MANAGER->GetUserType();
Main\Type\Collection::sortByColumn($arUserTypes, 'DESCRIPTION', '', null, true);
$arUserType = $USER_FIELD_MANAGER->GetUserType($USER_TYPE_ID);
if(!$arUserType)
{
	$arUserType = reset($arUserTypes);
}
/** @var Main\UserField\Types\BaseType $userTypeClass */
$userTypeClass = $arUserType['CLASS_NAME'];
if (!(is_a($userTypeClass, Main\UserField\Types\BaseType::class, true)))
{
	$userTypeClass = Main\UserField\Types\BaseType::class;
}

$APPLICATION->SetTitle(($ID>0? GetMessage("USER_TYPE_EDIT_TITLE", array("#ID#"=>$ID)) : GetMessage("USER_TYPE_ADD_TITLE")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// validate list_url
if (!empty($list_url))
{
	$list_url = mb_substr($list_url, 0, 1) === '/' ? $list_url : '/'.$list_url;
}

$aMenu = array();
if (!$adminSidePanelHelper->isPublicFrame())
{
	$aMenu[] = array(
		"TEXT"=>GetMessage("USER_TYPE_LIST"),
		"TITLE"=>GetMessage("USER_TYPE_LIST_TITLE"),
		"LINK"=>!empty($list_url)? $list_url : "userfield_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	);
}
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("MAIN_ADD"),
		"TITLE"=>GetMessage("USER_TYPE_ADD"),
		"LINK"=>"userfield_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("MAIN_DELETE"),
		"TITLE"=>GetMessage("USER_TYPE_DELETE"),
		"LINK"=>"javascript:if(confirm('".GetMessage("USER_TYPE_DELETE_CONF")."'))window.location='userfield_admin.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if($message)
	echo $message->Show();
?>
<script language="JavaScript">
<!--
function addNewRow(tableID)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length;
	var oRow = tbl.insertRow(cnt);
	for(var i=0;i<6;i++)
	{
		var oCell = oRow.insertCell(i);
		var sHTML=tbl.rows[cnt-1].cells[i].innerHTML;
		var p = 0;
		while(true)
		{
			var s = sHTML.indexOf('[n',p);
			if(s<0)break;
			var e = sHTML.indexOf(']',s);
			if(e<0)break;
			var n = parseInt(sHTML.substr(s+2,e-s));
			sHTML = sHTML.substr(0, s)+'[n'+(++n)+']'+sHTML.substr(e+1);
			p=s+1;
		}
		while(true)
		{
			s = sHTML.indexOf('\"n',p);
			if(s<0)break;
			e = sHTML.indexOf('\"',s+1);
			if(e<0)break;
			n = parseInt(sHTML.substr(s+2,e-s));
			sHTML = sHTML.substr(0, s)+'\"n'+(++n)+'\"'+sHTML.substr(e+1);
			p=s+1;
		}
		oCell.innerHTML = sHTML;
	}

	setTimeout(function() {
		var r = BX.findChildren(oCell.parentNode, {tag: /^(input|select|textarea)$/i}, true);
		if (r && r.length > 0)
		{
			for (var i=0,l=r.length;i<l;i++)
			{
				if (r[i].form && r[i].form.BXAUTOSAVE)
					r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
				else
					break;
			}
		}
	}, 10);
}

BX.ready(function(){
	BX.addCustomEvent(document.forms.post_form, 'onAutoSaveRestore', function(ob, data)
	{
		for(var i in data)
		{
			var r = /^LIST\[n([\d]+)\]\[XML_ID\]$/.exec(i);
			if (r && r[1] > 0)
			{
				addNewRow('list_table');
			}
		}

	});

});
//-->
</script>
<?
$formAction = $APPLICATION->GetCurPage();
$formAction = $adminSidePanelHelper->setDefaultQueryParams($formAction);
?>
<form method="POST" action="<?=$formAction?>" ENCTYPE="multipart/form-data" name="post_form">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
	<?if($ID):?>
	<tr>
		<td width="40%">ID:</td>
		<td width="60%"><?=$ID?></td>
	</tr>
	<?endif?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("USERTYPE_USER_TYPE_ID")?>:</td>
		<td width="60%">
			<?
			if($ID > 0)
			{
				echo htmlspecialcharsbx($arUserType["DESCRIPTION"]);
			}
			else
			{
				$arr = array("reference"=>array(), "reference_id"=>array());
				foreach($arUserTypes as $userType)
				{
					$arr["reference"][] = $userType["DESCRIPTION"];
					$arr["reference_id"][] = $userType["USER_TYPE_ID"];
				}
				echo SelectBoxFromArray("USER_TYPE_ID", $arr, $USER_TYPE_ID, "", 'OnChange="'.htmlspecialcharsbx('window.location=\''.CUtil::JSEscape($APPLICATION->GetCurPageParam("", array("USER_TYPE_ID")).'&back_url='.urlencode($back_url).'&list_url='.urlencode($list_url).'&ENTITY_ID='.$ENTITY_ID.'&USER_TYPE_ID=').'\' + this.value').'"');
			}
			?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("USERTYPE_ENTITY_ID")?>:</td>
		<td>
			<?if($ID>0 || ($ENTITY_ID!="" && !$message)):?>
				<?=$ENTITY_ID?>
				<input type="hidden" name="ENTITY_ID" value="<?=$ENTITY_ID?>">
			<?else:?>
				<input type="text" name="ENTITY_ID" value="<?=$ENTITY_ID?>" maxlength="50">
			<?endif?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("USERTYPE_FIELD_NAME")?>:</td>
		<td>
			<?if($ID>0):?>
				<?=$FIELD_NAME?>
			<?else:?>
				<input type="text" name="FIELD_NAME" value="<?=$FIELD_NAME?>" maxlength="50">
			<?endif?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_XML_ID")?>:</td>
		<td><input type="text" name="XML_ID" value="<?=$XML_ID?>" maxlength="255"></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_SORT")?>:</td>
		<td><input type="text" name="SORT" value="<?=$SORT?>"></td>
	</tr>
	<?php if ($userTypeClass::isMultiplicitySupported()):?>
	<tr>
		<td><?=GetMessage("USERTYPE_MULTIPLE")?>:</td>
		<td>
			<?if($ID>0):?>
				<?=$MULTIPLE == "Y"? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?>
			<?else:?>
				<input type="checkbox" name="MULTIPLE" value="Y"<?if($MULTIPLE == "Y") echo " checked"?> >
			<?endif?>
		</td>
	</tr>
	<?php endif;?>
	<?php if ($userTypeClass::isMandatorySupported()):?>
	<tr>
		<td><?=GetMessage("USERTYPE_MANDATORY")?>:</td>
		<td><input type="checkbox" name="MANDATORY" value="Y"<?if($MANDATORY == "Y") echo " checked"?> ></td>
	</tr>
	<?php endif;?>
	<tr>
		<td><?=GetMessage("USERTYPE_SHOW_FILTER")?>:</td>
		<td><?
			$arr = array(
				"reference" => array(
					GetMessage("USER_TYPE_FILTER_N"),
					GetMessage("USER_TYPE_FILTER_I"),
					GetMessage("USER_TYPE_FILTER_E"),
					GetMessage("USER_TYPE_FILTER_S"),
				),
				"reference_id" => array(
					"N",
					"I",
					"E",
					"S",
				),
			);
			echo SelectBoxFromArray("SHOW_FILTER", $arr, $SHOW_FILTER);
		?></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_SHOW_IN_LIST")?>:</td>
		<td><input type="checkbox" name="SHOW_IN_LIST" value="N"<?if($SHOW_IN_LIST == "N") echo " checked"?> ></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_EDIT_IN_LIST")?>:</td>
		<td><input type="checkbox" name="EDIT_IN_LIST" value="N"<?if($EDIT_IN_LIST == "N") echo " checked"?> ></td>
	</tr>
	<tr>
		<td><?=GetMessage("USERTYPE_IS_SEARCHABLE")?>:</td>
		<td><input type="checkbox" name="IS_SEARCHABLE" value="Y"<?if($IS_SEARCHABLE == "Y") echo " checked"?> ></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("USERTYPE_SETTINGS")?></td>
	</tr>
	<?if($ID > 0):
		echo $USER_FIELD_MANAGER->GetSettingsHTML($arUserField, $bVarsFromForm);
	else:
		echo $USER_FIELD_MANAGER->GetSettingsHTML($arUserType["USER_TYPE_ID"], $bVarsFromForm);
	endif;?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("USERTYPE_LANG_SETTINGS")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table border="0" cellspacing="10" cellpadding="2">
				<tr>
					<td align="right"><?echo GetMessage("USER_TYPE_LANG");?></td>
					<td align="center" width="200"><?echo GetMessage("USER_TYPE_EDIT_FORM_LABEL");?></td>
					<td align="center" width="200"><?echo GetMessage("USER_TYPE_LIST_COLUMN_LABEL");?></td>
					<td align="center" width="200"><?echo GetMessage("USER_TYPE_LIST_FILTER_LABEL");?></td>
					<td align="center" width="200"><?echo GetMessage("USER_TYPE_ERROR_MESSAGE");?></td>
					<td align="center" width="200"><?echo GetMessage("USER_TYPE_HELP_MESSAGE");?></td>
				</tr>
				<?
				$rsLanguage = CLanguage::GetList();
				while($arLanguage = $rsLanguage->Fetch()):
					$htmlLID = htmlspecialcharsbx($arLanguage["LID"]);
				?>
				<tr>
					<td align="right"><?echo htmlspecialcharsbx($arLanguage["NAME"])?>:</td>
					<td align="center"><input type="text" name="EDIT_FORM_LABEL[<?echo $htmlLID?>]" size="20" maxlength="255" value="<?echo htmlspecialcharsbx($bVarsFromForm? ($_REQUEST["EDIT_FORM_LABEL"][$arLanguage["LID"]] ?? '') : ($arUserField["EDIT_FORM_LABEL"][$arLanguage["LID"]] ?? ''))?>"></td>
					<td align="center"><input type="text" name="LIST_COLUMN_LABEL[<?echo $htmlLID?>]" size="20" maxlength="255" value="<?echo htmlspecialcharsbx($bVarsFromForm? ($_REQUEST["LIST_COLUMN_LABEL"][$arLanguage["LID"]] ?? '') : ($arUserField["LIST_COLUMN_LABEL"][$arLanguage["LID"]] ?? ''))?>"></td>
					<td align="center"><input type="text" name="LIST_FILTER_LABEL[<?echo $htmlLID?>]" size="20" maxlength="255" value="<?echo htmlspecialcharsbx($bVarsFromForm? ($_REQUEST["LIST_FILTER_LABEL"][$arLanguage["LID"]] ?? '') : ($arUserField["LIST_FILTER_LABEL"][$arLanguage["LID"]] ?? ''))?>"></td>
					<td align="center"><input type="text" name="ERROR_MESSAGE[<?echo $htmlLID?>]" size="20" maxlength="255" value="<?echo htmlspecialcharsbx($bVarsFromForm? ($_REQUEST["ERROR_MESSAGE"][$arLanguage["LID"]] ?? '') : ($arUserField["ERROR_MESSAGE"][$arLanguage["LID"]] ?? ''))?>"></td>
					<td align="center"><input type="text" name="HELP_MESSAGE[<?echo $htmlLID?>]" size="20" maxlength="255" value="<?echo htmlspecialcharsbx($bVarsFromForm? ($_REQUEST["HELP_MESSAGE"][$arLanguage["LID"]] ?? '') : ($arUserField["HELP_MESSAGE"][$arLanguage["LID"]] ?? ''))?>"></td>
				</tr>
				<?endwhile?>
			</table>
		</td>
	</tr>
<?if(is_object($obEnum)):
	$tabControl->BeginNextTab();
?>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("USER_TYPE_LIST_LABEL")?></td>
		<td>
	<table border="0" cellspacing="0" cellpadding="0" class="internal" id="list_table">
	<tr class="heading">
		<td><?=GetMessage("USER_TYPE_LIST_ID")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_XML_ID")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_VALUE")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_SORT")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_DEF")?></td>
		<td><?=GetMessage("USER_TYPE_LIST_DEL")?></td>
	</tr>
<?if($MULTIPLE=="N"):?>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><?=GetMessage("USER_TYPE_LIST_NO_DEF")?></td>
		<td>&nbsp;</td>
		<td><input type="radio" name="LIST[DEF][]" value="0"></td>
		<td>&nbsp;</td>
	</tr>
<?endif?>
<?
	$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => $ID));
	while($arEnum = $rsEnum->GetNext()):

		if($bVarsFromForm && is_array($_REQUEST['LIST'][$arEnum["ID"]]))
			foreach($_REQUEST['LIST'][$arEnum["ID"]] as $key=>$val)
				$arEnum[$key] = htmlspecialcharsbx($val);
?>
	<tr>
		<td><?=$arEnum["ID"]?></td>
		<td><input type="text" name="LIST[<?=$arEnum["ID"]?>][XML_ID]" value="<?=$arEnum["XML_ID"]?>" size="15" maxlength="255"></td>
		<td><input type="text" name="LIST[<?=$arEnum["ID"]?>][VALUE]" value="<?=$arEnum["VALUE"]?>" size="35" maxlength="255"></td>
		<td><input type="text" name="LIST[<?=$arEnum["ID"]?>][SORT]" value="<?=$arEnum["SORT"]?>" size="5" maxlength="10"></td>
		<td><input type="<?=($MULTIPLE=="Y"? "checkbox": "radio")?>" name="LIST[DEF][]" value="<?=$arEnum["ID"]?>" <?=($arEnum["DEF"]=="Y"? "checked": "")?>></td>
		<td><input type="checkbox" name="LIST[<?=$arEnum["ID"]?>][DEL]" value="Y"<?if($arEnum["DEL"] == "Y") echo " checked"?>></td>
	</tr>
<?
	endwhile;
?>
<?
if($bVarsFromForm):
	$n = 0;
	foreach($_REQUEST['LIST'] as $key=>$val):
		if(strncmp($key, "n", 1)===0):
?>
	<tr>
		<td>&nbsp;</td>
		<td><input type="text" name="LIST[n<?=$n?>][XML_ID]" value="<?=htmlspecialcharsbx($val["XML_ID"])?>" size="15" maxlength="255"></td>
		<td><input type="text" name="LIST[n<?=$n?>][VALUE]" value="<?=htmlspecialcharsbx($val["VALUE"])?>" size="35" maxlength="255"></td>
		<td><input type="text" name="LIST[n<?=$n?>][SORT]" value="<?=htmlspecialcharsbx($val["SORT"])?>" size="5" maxlength="10"></td>
		<td><input type="<?=($MULTIPLE=="Y"? "checkbox": "radio")?>" name="LIST[DEF][]" value="n<?=$n?>"></td>
		<td><input type="checkbox" name="LIST[n<?=$n?>][DEL]" value="Y"<?if($val["DEL"] == "Y") echo " checked"?>></td>
	</tr>
<?
			$n++;
		endif;
	endforeach;
else:
?>
	<tr>
		<td>&nbsp;</td>
		<td><input type="text" name="LIST[n0][XML_ID]" value="" size="15" maxlength="255"></td>
		<td><input type="text" name="LIST[n0][VALUE]" value="" size="35" maxlength="255"></td>
		<td><input type="text" name="LIST[n0][SORT]" value="500" size="5" maxlength="10"></td>
		<td><input type="<?=($MULTIPLE=="Y"? "checkbox": "radio")?>" name="LIST[DEF][]" value="n0"></td>
		<td><input type="checkbox" name="LIST[n0][DEL]" value="Y"></td>
	</tr>
<?
endif;
?>
	</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="button" value="<?=GetMessage("USER_TYPE_LIST_MORE")?>" OnClick="addNewRow('list_table')" ></td>
	</tr>
<?endif?>
<?
$tabControl->Buttons(
	array(
		"disabled" => ($RIGHTS < "W"),
		"back_url" => !empty($back_url) ? $back_url : "userfield_admin.php?lang=".LANG
	)
);
?>
<?echo bitrix_sessid_post();?>
<?if($ID>0 && !$bCopy):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<input type="hidden" name="back_url" value="<?=htmlspecialcharsbx($back_url)?>">
<input type="hidden" name="list_url" value="<?=htmlspecialcharsbx($list_url)?>">

<?
$tabControl->End();
?>

<?
$tabControl->ShowWarnings("post_form", $message);
?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>