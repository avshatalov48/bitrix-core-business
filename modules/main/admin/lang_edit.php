<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

require_once(__DIR__."/../include/prolog_admin_before.php");
define("HELP_FILE", "settings/lang_edit.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

Loc::loadMessages(__FILE__);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => Loc::getMessage("MAIN_PARAM"), "ICON" => "lang_edit", "TITLE" => Loc::getMessage("MAIN_PARAM_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$message = null;
$bVarsFromForm = false;
$ID = intval($_REQUEST['ID'] ?? 0);

if($_SERVER["REQUEST_METHOD"] == "POST" && (!empty($_POST['save']) || !empty($_POST['apply'])) && $isAdmin && check_bitrix_sessid())
{
	$arFields = array(
		"ACTIVE" => $_POST['ACTIVE'],
		"SORT" => $_POST['SORT'],
		"DEF" => $_POST['DEF'],
		"NAME" => $_POST['NAME'],
		"CODE" => $_POST['CODE'],
		"CULTURE_ID" => $_POST['CULTURE_ID'],
	);

	if($ID <= 0)
		$arFields["LID"] = $_POST["LID"];

	$langs = new CLanguage;
	if($ID > 0)
	{
		$res = $langs->Update($_POST["LID"], $arFields);
	}
	else
	{
		$res = ($langs->Add($arFields) <> '');
	}

	if(!$res)
	{
		$bVarsFromForm = true;
	}
	else
	{
		if (!empty($_POST["save"]))
			LocalRedirect(BX_ROOT."/admin/lang_admin.php?lang=".LANGUAGE_ID);
		else
			LocalRedirect(BX_ROOT."/admin/lang_edit.php?lang=".LANGUAGE_ID."&LID=".$_POST["LID"]."&".$tabControl->ActiveTabParam());
	}
}

if($bVarsFromForm == false)
{
	$ID = 0;
	$language = false;
	if (!empty($_REQUEST["COPY_ID"]))
	{
		$lng = CLanguage::GetByID($_REQUEST["COPY_ID"]);
		$language = $lng->Fetch();
	}
	elseif (!empty($_REQUEST["LID"]))
	{
		$lng = CLanguage::GetByID($_REQUEST["LID"]);
		if(($language = $lng->Fetch()))
			$ID = 1;
	}
	if($language === false)
	{
		$language = array(
			"ACTIVE" => "Y",
		);
	}
}
else
{
	$language = $_POST;
}

$langField = array();
foreach($language as $key => $val)
	$langField[$key] = HtmlFilter::encode($val);

$strTitle = ($ID > 0? Loc::getMessage("EDIT_LANG_TITLE", array("#ID#" => $langField["LID"])) : Loc::getMessage("NEW_LANG_TITLE"));
$APPLICATION->SetTitle($strTitle);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"	=> Loc::getMessage("RECORD_LIST"),
		"LINK"	=> "/bitrix/admin/lang_admin.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("RECORD_LIST_TITLE"),
		"ICON"	=> "btn_list"
	)
);

if ($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("MAIN_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/lang_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("MAIN_NEW_RECORD_TITLE"),
		"ICON"	=> "btn_new"
	);
	if($isAdmin)
	{
		$aMenu[] = array(
			"TEXT"	=> Loc::getMessage("MAIN_COPY_RECORD"),
			"LINK"	=> "/bitrix/admin/lang_edit.php?lang=".LANGUAGE_ID."&amp;COPY_ID=".urlencode($language["LID"]),
			"TITLE"	=> Loc::getMessage("MAIN_COPY_RECORD_TITLE"),
			"ICON"	=> "btn_copy"
		);
		$aMenu[] = array(
			"TEXT"	=> Loc::getMessage("MAIN_DELETE_RECORD"),
			"LINK"	=> "javascript:if(confirm('".Loc::getMessage("MAIN_DELETE_RECORD_CONF")."')) window.location='/bitrix/admin/lang_admin.php?ID=".urlencode(urlencode($language["LID"]))."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action=delete';",
			"TITLE"	=> Loc::getMessage("MAIN_DELETE_RECORD_TITLE"),
			"ICON"	=> "btn_delete"
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
if ($e = $APPLICATION->GetException())
	$message = new CAdminMessage(Loc::getMessage("MAIN_ERROR_SAVING"), $e);

if($message)
	echo $message->Show();

?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?if (!empty($_REQUEST["COPY_ID"])):?><input type="hidden" name="COPY_ID" value="<?echo HtmlFilter::encode($_REQUEST["COPY_ID"])?>"><?endif?>
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%">ID:</td>
		<td width="60%"><?
			if($ID > 0):
				echo $langField["LID"];
				?><input type="hidden" name="LID" value="<? echo $langField["LID"]?>"><?
			else:
				?><input type="text" name="LID" size="2" maxlength="2" value="<? echo $langField["LID"]?>"><?
			endif;
				?></td>
	</tr>
	<tr>
		<td><label for="active"><?echo Loc::getMessage('ACTIVE')?></label></td>
		<td><input type="checkbox" name="ACTIVE" id="active" value="Y"<?if($language["ACTIVE"] == "Y") echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo Loc::getMessage('NAME')?></td>
		<td><input type="text" name="NAME" size="30" maxlength="50" value="<? echo $langField["NAME"]?>"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('lang_edit_code') ?></td>
		<td><input type="text" name="CODE" size="30" maxlength="50" value="<? echo $langField["CODE"]?>"></td>
	</tr>
	<tr>
		<td><label for="def"><?echo Loc::getMessage('DEF')?></label></td>
		<td><input type="checkbox" name="DEF" id="def" value="Y"<?if($language["DEF"] == "Y") echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo Loc::getMessage('SORT')?></td>
		<td><input type="text" name="SORT" size="10" maxlength="10" value="<? echo $langField["SORT"]?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo Loc::getMessage("lang_edit_culture")?></td>
		<td>
<?
$cultureRes = \Bitrix\Main\Localization\CultureTable::getList(array('order'=>array('NAME'=>'ASC')));
$cultures = array();
while($cult = $cultureRes->fetch())
{
	$cult["WEEK_START"] = Loc::getMessage('DAY_OF_WEEK_'.$cult["WEEK_START"]);
	$cult["DIRECTION"] = ($cult["DIRECTION"] == "Y"? Loc::getMessage('DIRECTION_LTR') : Loc::getMessage('DIRECTION_RTL'));
	$cultures[] = $cult;
}
?>
<script>
function BXSetCulture()
{
	var selObj = BX('bx_culture_select');
	var form = selObj.form;
	var cultures = <?= Json::encode($cultures) ?>;
	//noinspection JSUnusedAssignment
	var culture = cultures[selObj.selectedIndex];

	if(!culture)
		return;

	form.FORMAT_DATE.value = culture.FORMAT_DATE;
	form.FORMAT_DATETIME.value = culture.FORMAT_DATETIME;
	form.WEEK_START.value = culture.WEEK_START;
	form.FORMAT_NAME.value = culture.FORMAT_NAME;
	form.CHARSET.value = culture.CHARSET;
	form.DIRECTION.value = culture.DIRECTION;

	BX('bx_culture_link').href = 'culture_edit.php?ID='+culture.ID+'&lang=<?=LANGUAGE_ID?>';
}
BX.ready(BXSetCulture);
</script>
			<select name="CULTURE_ID" onchange="BXSetCulture()" id="bx_culture_select">
<?
foreach($cultures as $cult):
?>
				<option value="<?=$cult["ID"]?>"<?if($cult["ID"] == $language["CULTURE_ID"]) echo " selected"?>><?=HtmlFilter::encode($cult["NAME"])?></option>
<?
endforeach;
?>
			</select>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><a href="culture_edit.php?lang=<?=LANGUAGE_ID?>" id="bx_culture_link"><?echo Loc::getMessage("lang_edit_culture_edit")?></a></td>
	</tr>
	<tr>
		<td><? echo Loc::getMessage('FORMAT_DATE')?></td>
		<td><input type="text" name="FORMAT_DATE" size="30" disabled="disabled"></td>
	</tr>
	<tr>
		<td><? echo Loc::getMessage('FORMAT_DATETIME')?></td>
		<td><input type="text" name="FORMAT_DATETIME" size="30" disabled="disabled"></td>
	</tr>
	<tr>
		<td><? echo Loc::getMessage('LANG_EDIT_WEEK_START')?></td>
		<td><input type="text" name="WEEK_START" size="30" disabled="disabled"></td>
	</tr>
	<tr>
		<td><? echo Loc::getMessage('FORMAT_NAME')?></td>
		<td><input type="text" name="FORMAT_NAME" size="30" disabled="disabled"></td>
	</tr>
	<tr>
		<td><? echo Loc::getMessage('CHARSET')?></td>
		<td><input type="text" name="CHARSET" size="30" disabled="disabled"></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage('DIRECTION')?></td>
		<td><input type="text" name="DIRECTION" size="30" disabled="disabled"></td>
	</tr>
<?$tabControl->Buttons(array("disabled"=>!$isAdmin, "back_url"=>"lang_admin.php?lang=".LANGUAGE_ID));
$tabControl->End();
$tabControl->ShowWarnings("form1", $message);
?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
