<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 */

use Bitrix\Main\Mail\Internal\EventTypeTable;

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/mail_events/messagetype_edit.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$arFields = array();
$arParams = array("ACTION" => "ADD");
$strError = "";
$bVarsFromForm = false;
$message = null;
$arLangs = array();

$db_res = CLanguage::GetList();
if ($db_res && $res = $db_res->GetNext())
{
	do 
	{
		$arParams["LANGUAGE"][$res["LID"]] = $res;
		$arLangs[$res["LID"]] = true;
	}
	while ($res = $db_res->GetNext());
}

if($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["save"] <> '' || $_POST["apply"] <> '') && $isAdmin && check_bitrix_sessid())
{
	$_POST["EVENT_NAME"] = trim($_POST["EVENT_NAME"]);

	$res = array();
	$DB->StartTransaction();
	if($_POST["EVENT_NAME"] <> '')
	{
		$db_res = CEventType::GetListEx(array(), array("EVENT_NAME" => $_POST["EVENT_NAME"]), array("type" => "full"));
		if(!($db_res) || !($res = $db_res->Fetch()))
		{
			$res["EVENT_NAME"] = $_POST["EVENT_NAME"];
		}
	}
	
	foreach ($arParams["LANGUAGE"] as $idLang => $arLang)
	{
		$arType = array(
			"ID" => $_POST["FIELDS"][$idLang]["ID"],
			"SORT" => $_POST["FIELDS"][$idLang]["SORT"],
			"NAME" => $_POST["FIELDS"][$idLang]["NAME"],
			"DESCRIPTION" => $_POST["FIELDS"][$idLang]["DESCRIPTION"],
			"LID" => $idLang,
			"EVENT_NAME" => $res["EVENT_NAME"],
			"EVENT_TYPE" => ($_POST["EVENT_TYPE"] == EventTypeTable::TYPE_SMS? EventTypeTable::TYPE_SMS : EventTypeTable::TYPE_EMAIL),
		);
		$admList = new CAdminList("dummy");
		if ($admList->IsUpdated($idLang) && $_REQUEST[$idLang] == "Y")
		{
			if ((intval($arType["ID"]) > 0 && (!CEventType::Update(array("ID" => $arType["ID"]), $arType))) ||
				((intval($arType["ID"]) <= 0) && !CEventType::Add($arType)))
			{
				$bVarsFromForm = true;
			}
		}
		if ($_REQUEST[$idLang] != "Y")
		{
			unset($arLangs[$idLang]);
			
			if (intval($arType["ID"]) > 0)
			{
				if (!CEventType::Delete(array("ID" => $arType["ID"])))
					$bVarsFromForm = true;
			}
		}
		if ($bVarsFromForm)
			break;
	}
	
	if (empty($arLangs))
	{
		$arMsg = array();
		if ($res["EVENT_NAME"] == '')
			$arMsg[] = array("id" => "EVENT_NAME_EMPTY", "text" => GetMessage("EVENT_NAME_EMPTY"));
		$arMsg[] = array("id" => "LID_EMPTY", "text" => GetMessage("ERROR_LANG_EMPTY"));
		
		$e = new CAdminException($arMsg);
		$APPLICATION->ThrowException($e);
		$bVarsFromForm = true;
	}
	if ($bVarsFromForm)
	{
		$DB->Rollback();
	}
	else 
	{
		$DB->Commit();
		if ($_POST["save"] <> '')
			LocalRedirect(BX_ROOT."/admin/type_admin.php?lang=".LANGUAGE_ID);
		else
			LocalRedirect(BX_ROOT."/admin/type_edit.php?EVENT_NAME=".$res["EVENT_NAME"]."&lang=".LANGUAGE_ID);
	}
}
if ($bVarsFromForm && ($e = $APPLICATION->GetException()))
	$message = new CAdminMessage(GetMessage("MAIN_ERROR_SAVING"), $e);

$arParams["EVENT_NAME"] = $_REQUEST["EVENT_NAME"];

if ($arParams["EVENT_NAME"] <> '')
{
	$db_res = CEventType::GetListEx(array(), array("EVENT_NAME" => $arParams["EVENT_NAME"]), array("type" => "full"));
	if ($db_res && ($res = $db_res->Fetch()))
	{
		$arParams["DATA"] = $res;
		if (is_array($res["TYPE"]))
		{
			foreach ($res["TYPE"] as $r)
				$arParams["DATA"][$r["LID"]] = $r;
		}
		$arParams["ACTION"] = "UPDATE";
		$arParams["DATA_OLD"] = $arParams["DATA"];
	}
}

$aTabs = array(array("DIV" => "edit1", "TAB" => GetMessage("EVENT_NAME_TITLE"), "ICON" => "mail", "TITLE" => GetMessage("EVENT_NAME_DESCR1")));
if ($arParams["ACTION"] == "UPDATE" && $arParams["DATA"]["EVENT_TYPE"] == EventTypeTable::TYPE_EMAIL)
	$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("TEMPLATES_TITLE"), "ICON" => "mail", "TITLE" => GetMessage("TEMPLATES_DESCR"));
	
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($bVarsFromForm)
{
	foreach ($_REQUEST["FIELDS"] as $k => $v)
	{
		$arParams["DATA"][$k] = $_REQUEST["FIELDS"][$k];
	}
}

if ($arParams["ACTION"]=="ADD")
{
	$APPLICATION->SetTitle(GetMessage("NEW_TITLE"));
	$context = new CAdminContextMenu(
		array(
			array(
				"TEXT"	=> GetMessage("RECORD_LIST"),
				"LINK"	=> "/bitrix/admin/type_admin.php?lang=".LANGUAGE_ID,
				"TITLE"	=> GetMessage("RECORD_LIST_TITLE"),
				"ICON"	=> "btn_list"
			), 
		)
	);
}
else
{
	$APPLICATION->SetTitle(str_replace("#TYPE#", $arParams["EVENT_NAME"], GetMessage("EDIT_TITLE")));
	$context = new CAdminContextMenu(
		array(
			array(
				"TEXT"	=> GetMessage("RECORD_LIST"),
				"LINK"	=> "/bitrix/admin/type_admin.php?lang=".LANGUAGE_ID,
				"TITLE"	=> GetMessage("RECORD_LIST_TITLE"),
				"ICON"	=> "btn_list"
			), 
			array(
				"TEXT"	=> GetMessage("MAIN_NEW_RECORD"),
				"LINK"	=> "/bitrix/admin/type_edit.php?lang=".LANGUAGE_ID,
				"TITLE"	=> GetMessage("MAIN_NEW_RECORD_TITLE"),
				"ICON"	=> "btn_new"
			),
			array(
				"TEXT"	=> GetMessage("MAIN_DELETE_RECORD"),
				"LINK"	=> "javascript:if(confirm('".GetMessage("MAIN_DELETE_RECORD_CONF")."')) window.location='/bitrix/admin/type_admin.php?ID=".urlencode($arParams["EVENT_NAME"])."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action=delete';",
				"TITLE"	=> GetMessage("MAIN_DELETE_RECORD_TITLE"),
				"ICON"	=> "btn_delete"
			),
		)
	);
}
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$context->Show();

if($message)
	echo $message->Show();

$arParams["EVENT_NAME"] = htmlspecialcharsbx($arParams["EVENT_NAME"]);
?>

<form method="POST" action="<?=$APPLICATION->GetCurPage()?>" name="form1">
<?=bitrix_sessid_post()?>
<?$tabControl->Begin();?>
<?$tabControl->BeginNextTab();?>
<tr class="adm-detail-required-field">
	<td width="40%"><?=GetMessage('EVENT_NAME1')?>:</td>
	<td width="0%">
	<?if ($arParams["ACTION"] == "ADD"):?>
		<input type="text" name="EVENT_NAME" value="<?=$arParams["EVENT_NAME"]?>" size="50">
	<?else:?>
		<input type="hidden" name="EVENT_NAME" value="<?=$arParams["EVENT_NAME"]?>"> 
		<?=$arParams["EVENT_NAME"]?>
	<?endif;?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("type_edit_event_type")?></td>
	<td>
		<select name="EVENT_TYPE">
			<option value="<?=EventTypeTable::TYPE_EMAIL?>"<?if($arParams["DATA"]["EVENT_TYPE"] == EventTypeTable::TYPE_EMAIL) echo " selected"?>><?echo GetMessage("type_edit_event_type_email")?></option>
			<option value="<?=EventTypeTable::TYPE_SMS?>"<?if($arParams["DATA"]["EVENT_TYPE"] == EventTypeTable::TYPE_SMS) echo " selected"?>><?echo GetMessage("type_edit_event_type_sms")?></option>
		</select>
	</td>
</tr>
<?
	foreach ($arParams["LANGUAGE"] as $idLang => $arLang):
?>
<tr class="heading">
	<td colspan="2">
		<input type="hidden" name="<?=$idLang?>" value="N">
		<input type="checkbox" id="box_<?=$idLang?>" name="<?=$idLang?>" <?=($arParams["DATA"][$idLang]["ID"] <> '' && $_REQUEST[$idLang] <> "N" || $_REQUEST[$idLang] == "Y" || $arParams["EVENT_NAME"] == ''? " checked" : "")?> value="Y">
		<label for="box_<?=$idLang?>" >[<?=$arLang["ID"]?>] <?=$arLang["NAME"]?></label></td>
</tr>
<?if ($arParams["DATA"][$arLang["ID"]]["ID"] > 0):?>
<tr><td>ID:</td><td>
<?=htmlspecialcharsEx($arParams["DATA"][$arLang["ID"]]["ID"])?>
<input type="hidden" name="FIELDS[<?=$arLang["ID"]?>][ID]" value="<?=htmlspecialcharsbx($arParams["DATA"][$arLang["ID"]]["ID"])?>">
</td></tr>
<?endif;?>
<tr>
	<td><?=GetMessage("EVENT_SORT_LANG")?>:</td>
	<td>
		<input type="hidden" name="FIELDS_OLD[<?=$arLang["ID"]?>][SORT]" value="<?=(int)$arParams["DATA_OLD"][$arLang["ID"]]["SORT"]?>">
		<input type="text" name="FIELDS[<?=$arLang["ID"]?>][SORT]" value="<?=(int)($arParams["DATA"][$arLang["ID"]]["SORT"] ?: "150")?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("EVENT_NAME_LANG")?>:</td>
	<td>
		<input type="hidden" name="FIELDS_OLD[<?=$arLang["ID"]?>][NAME]" value="<?=htmlspecialcharsbx($arParams["DATA_OLD"][$arLang["ID"]]["NAME"])?>">
		<input type="text" name="FIELDS[<?=$arLang["ID"]?>][NAME]" value="<?=htmlspecialcharsbx($arParams["DATA"][$arLang["ID"]]["NAME"])?>" style="width:100%;">
	</td>
</tr>
<tr>
	<td class="adm-detail-valign-top"><?=GetMessage("EVENT_DESCR_LANG")?>:</td>
	<td>
		<input type="hidden" name="FIELDS_OLD[<?=$arLang["ID"]?>][DESCRIPTION]" value="<?=htmlspecialcharsbx($arParams["DATA_OLD"][$arLang["ID"]]["DESCRIPTION"])?>">
		<textarea name="FIELDS[<?=$arLang["ID"]?>][DESCRIPTION]" style="width:100%;" rows="10"><?=htmlspecialcharsbx($arParams["DATA"][$arLang["ID"]]["DESCRIPTION"])?></textarea>
	</td>
</tr>
<?endforeach;?>
<?
if ($arParams["ACTION"] == "UPDATE" && $arParams["DATA"]["EVENT_TYPE"] == EventTypeTable::TYPE_EMAIL):
	$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2">
		<a href="message_edit.php?lang=<?=LANGUAGE_ID?>&amp;EVENT_NAME=<?=urlencode($arParams["EVENT_NAME"])?>"><?echo GetMessage("type_edit_add_message_template")?></a>
	</td>
</tr>
<?
	if (is_array($arParams["DATA"]["TEMPLATES"])):
		foreach ($arParams["DATA"]["TEMPLATES"] as $k => $v):
?><tr>
	<td colspan="2">[<a href="/bitrix/admin/message_edit.php?ID=<?=(int)$v["ID"]?>&amp;lang=<?=LANGUAGE_ID?>"><?=(int)$v["ID"]?></a>]<?=(trim($v["SUBJECT"]) <> '' ? " " : "").htmlspecialcharsEx($v["SUBJECT"])?>
	<?
	$arLID = array();
	$db_LID = CEventMessage::GetLang($v["ID"]);
	while($arrLID = $db_LID->Fetch())
		$arLID[] = $arrLID["LID"];
	if(!empty($arLID))
	{
		echo " (".implode(", ", $arLID).")";
	}

?></td>
</tr><?
		endforeach;
	endif;
endif;
$tabControl->Buttons(array("disabled"=>!$isAdmin, "back_url"=>"type_admin.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>
<?
$tabControl->ShowWarnings(
	"form1", $message, 
	array(
		"EVENT_NAME_EMPTY" => "EVENT_NAME", 
		"LID_EMPTY" => "LID",
		"EVENT_NAME_EXIST" => "EVENT_NAME", 
		"EVENT_ID_EMPTY" => "EVENT_NAME", 
	)
);
?>

<?echo BeginNote();?>
<?=GetMessage("LANG_FIELDS")?>
<?echo EndNote();?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
