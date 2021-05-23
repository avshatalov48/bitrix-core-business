<?
if (!function_exists("__wd_check_uf_use_bp_property"))
{
	function __wd_check_uf_use_bp_property($iblock_id)
	{
		$iblock_id = intval($iblock_id); 
		$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOCK_".$iblock_id."_SECTION", "FIELD_NAME" => "UF_USE_BP"));
		if (!$db_res || !($res = $db_res->GetNext()))
		{
			$arFields = Array(
				"ENTITY_ID" => "IBLOCK_".$iblock_id."_SECTION",
				"FIELD_NAME" => "UF_USE_BP",
				"USER_TYPE_ID" => "string",
				"MULTIPLE" => "N",
				"MANDATORY" => "N", 
				"SETTINGS" => array("DEFAULT_VALUE" => "Y"));
			$arFieldName = array();
			$rsLanguage = CLanguage::GetList($by, $order, array());
			while($arLanguage = $rsLanguage->Fetch()):
//				GetMessage("SONET_UF_USE_BP");
				$dir = str_replace(array("\\", "//"), "/", dirname(__FILE__)); 
				$dirs = explode("/", $dir); 
				array_pop($dirs); 
				$file = trim(implode("/", $dirs)."/lang/".$arLanguage["LID"]."/include/webdav_settings.php");
				$tmp_mess = __IncludeLang($file, true);
				$arFieldName[$arLanguage["LID"]] = (empty($tmp_mess["SONET_UF_USE_BP"]) ? "Use Business Process" : $tmp_mess["SONET_UF_USE_BP"]);
			endwhile;
			$arFields["EDIT_FORM_LABEL"] = $arFieldName;
			$obUserField  = new CUserTypeEntity;
			$obUserField->Add($arFields);
			$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
		}
	}
}

if (!function_exists("__wd_get_root_section"))
{
	function __wd_get_root_section($IBLOCK_ID, $object, $object_id)
	{

		$result = CIBlockWebdavSocnet::GetSectionID($IBLOCK_ID, $object, $object_id);
		if (intval($result) > 0)
		{
			return $result;
		}
		//elseif ($arParams["PERMISSION"] < "W" || ($object == "user" && $arResult["VARIABLES"]["user_id"] != $USER->GetID()))
		//{
			//$arParams["ERROR_MESSAGE"] = GetMessage("SONET_WEBDAV_NOT_EXISTS");
			//return 0;
		//}
		else // create new
		{
			__wd_check_uf_use_bp_property($arParams["IBLOCK_ID"]); 
			
			$arFields = Array(
				"IBLOCK_ID" => $IBLOCK_ID,
				"ACTIVE" => "Y",
				"SOCNET_GROUP_ID" => false,
				"IBLOCK_SECTION_ID" => 0, 
				"UF_USE_BP" => "N"
			);

			if ($object == "user")
			{
				$dbUser = CUser::GetByID($object_id);
				$arUser = $dbUser->Fetch();
				$arFields["NAME"] = trim($arUser['LAST_NAME']." ".$arUser['FIRST_NAME']);
				$arFields["NAME"] = trim(!empty($arFields["NAME"]) ? $arFields["NAME"] : $arUser['LOGIN']);
				$arFields['CREATED_BY'] = $arUser['ID'];
				$arFields['MODIFIED_BY'] = $arUser['ID'];

				if (CIBlock::GetArrayByID($IBLOCK_ID, "RIGHTS_MODE") === "E")
				{
					$arTasks = CWebDavIblock::GetTasks();
					$arFields['RIGHTS'] = array(
						'n0' => array('GROUP_CODE' => 'U'.$object_id, 'TASK_ID' => $arTasks['X'])
					);
				}
			}
			else 
			{
		/*		$res = CSocNetGroup::GetByID($arResult["VARIABLES"]["group_id"]);
				if (!$res)
				{
					$arParams["ERROR_MESSAGE"] = GetMessage("SONET_GROUP_NOT_EXISTS");
					return 0;
				} */

				$arFields["SOCNET_GROUP_ID"] = $object_id;
				$arGroup = CSocNetGroup::GetByID($object_id);
				$arFields["NAME"] = GetMessage("SONET_GROUP_PREFIX").$arGroup["NAME"];
				if (CIBlock::GetArrayByID($IBLOCK_ID, "RIGHTS_MODE") === "E")
				{
					$arTasks = CWebDavIblock::GetTasks();
					$arFields['RIGHTS'] = array(
						'n0' => array('GROUP_CODE' => 'SG'.$arFields["SOCNET_GROUP_ID"].'_A', 'TASK_ID' => $arTasks['X']),
						'n1' => array('GROUP_CODE' => 'SG'.$arFields["SOCNET_GROUP_ID"].'_E', 'TASK_ID' => $arTasks['W']),
						'n2' => array('GROUP_CODE' => 'SG'.$arFields["SOCNET_GROUP_ID"].'_K', 'TASK_ID' => $arTasks['W'])
					);
				}
			}

			$GLOBALS["UF_USE_BP"] = $arFields["UF_USE_BP"];
			$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$IBLOCK_ID."_SECTION", $arFields);
			$bs = new CIBlockSection;
			$sectionID = $bs->Add($arFields);
			if (!$sectionID)
			{
				$arParams["ERROR_MESSAGE"] = $bs->LAST_ERROR;
				return 0;
			}

			WDClearComponentCache(array(
				"webdav.element.edit", 
				"webdav.element.hist", 
				"webdav.element.upload", 
				"webdav.element.view", 
				"webdav.menu",
				"webdav.section.edit", 
				"webdav.section.list"));
			
			return true;
		/*
			if ($ob->workflow == 'bizproc')
			{
				__wd_create_default_bp_user_and_groups($arBizProcParameters);
			}
		*/	
		}
	}
}

if (defined("WEBDAV_SETTINGS_LIMIT_INCLUDE"))
	return true;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/admin_lib.php");

$dir = str_replace(array("\\", "//"), "/", dirname(__FILE__)); 
$dirs = explode("/", $dir); 
array_pop($dirs); 
$file = trim(implode("/", $dirs)."/lang/".LANGUAGE_ID."/include/webdav_settings.php");
__IncludeLang($file);
$documentType = explode("_", $_REQUEST["DOCUMENT_ID"]); 
$arParams = array();
$arParams["IBLOCK_ID"] = $IBLOCK_ID = intval($documentType[1]); 
$object = trim($documentType[2]); 
$object_id = intval($documentType[3]); 

$popupWindow = new CJSPopup('', '');

if (!CModule::IncludeModule("iblock"))
	$popupWindow->ShowError(GetMessage("SONET_IB_MODULE_IS_NOT_INSTALLED"));
elseif (!CModule::IncludeModule("webdav"))
	$popupWindow->ShowError(GetMessage("SONET_WD_MODULE_IS_NOT_INSTALLED"));
elseif ($IBLOCK_ID <= 0)
	$popupWindow->ShowError(GetMessage("SONET_IBLOCK_ID_EMPTY"));
elseif ($object_id <= 0 && ($object != "user" && $object != "group"))
	$popupWindow->ShowError(GetMessage("SONET_GROUP_NOT_EXISTS"));

$res = CIBlockWebdavSocnet::GetUserMaxPermission(
	$object, 
	$object_id, 
	$USER->GetID(), 
	$IBLOCK_ID);
$arParams["PERMISSION"] = $res["PERMISSION"];
$arParams["CHECK_CREATOR"] = $res["CHECK_CREATOR"];
if (
	$arParams["PERMISSION"] < "W" 
	|| $arParams["CHECK_CREATOR"] == "Y"
)
{
	$popupWindow->ShowError($object == "user" ? GetMessage("SONET_USER_FILES_ACCESS_DENIED") : GetMessage("SONET_GROUP_FILES_ACCESS_DENIED"));
}

$arFilter = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SOCNET_GROUP_ID" => false,
	"CHECK_PERMISSIONS" => "N",
	"SECTION_ID" => 0);
if ($object == "user")
	$arFilter["CREATED_BY"] = $object_id;
else
	$arFilter["SOCNET_GROUP_ID"] = $object_id;
$arLibrary = array();
$db_res = CIBlockSection::GetList(array(), $arFilter, false, array("ID", "UF_USE_BP"));
if (!($db_res && $arLibrary = $db_res->GetNext()))
{
	$popupWindow->ShowError(GetMessage("SONET_WEBDAV_NOT_EXISTS")); 
}
else
{
	$arLibrary["UF_USE_BP"] = ($arLibrary["UF_USE_BP"] == "N" ? "N" : "Y"); 
}

if (CIBlock::GetArrayByID($IBLOCK_ID, "RIGHTS_MODE") === "E")
{
	$sectionID = $arLibrary['ID'];
	$bSectionPerms = CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $sectionID, 'section_rights_edit');
}
else
{
	$bSectionPerms = ($arParams["PERMISSION"] > 'W');
}

if (!$bSectionPerms)
	return;

//Save permissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	$strWarning = GetMessage("MAIN_SESSION_EXPIRED");
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST")
{

	$arRequestParams = array(
		'SOCNET_GROUP_ID',
		'SOCNET_TYPE',
		'SOCNET_ID'
	);
	foreach ($arRequestParams as $param)
	{
		if (isset($_REQUEST[$param]))
		{
			$arParams[$param] = $_REQUEST[$param];
		}
	}

	$arParams['ENTITY_TYPE'] = 'SECTION';
	$arParams['ENTITY_ID'] = $arParams['IBLOCK_ID'];
	$arParams['ACTION'] = 'set_rights';
	$arParams['DO_NOT_REDIRECT'] = true;

	include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/components/bitrix/webdav.iblock.rights/action.php");

	$_REQUEST["UF_USE_BP"] = ($_REQUEST["UF_USE_BP"] == "Y" ? "Y" : "N"); 
	if ($_REQUEST["UF_USE_BP"] != $arLibrary["UF_USE_BP"])
	{
		if (!isset($arLibrary["~UF_USE_BP"]))
		{
			__wd_check_uf_use_bp_property($arParams["IBLOCK_ID"]); 
		}
		$arFields = Array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"UF_USE_BP" => $_REQUEST["UF_USE_BP"]);
		$GLOBALS["UF_USE_BP"] = $arFields["UF_USE_BP"];
		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
		$bs = new CIBlockSection(); 
		$res = $bs->Update($arLibrary["ID"], $arFields);
	}

	$popupWindow->Close($bReload = true, $_REQUEST["back_url"]);
	die();
}

//HTML output
$popupWindow->ShowTitlebar(GetMessage("SN_TITLE"));
$popupWindow->StartDescription("bx-access-folder");
if (isset($strWarning) && $strWarning != "")
	$popupWindow->ShowValidationError($strWarning);
?>

<p><b><?=GetMessage("SN_TITLE_TITLE")?></b></p>

<?
$popupWindow->EndDescription();
$popupWindow->StartContent();
?>
<? if ($object == 'group') { ?>
<p></p>
<table class="bx-width100" id="bx_permission_table">
	<tr>
		<td width="35%" align="right"><?=GetMessage("SN_BP")?></td>
		<td><input type="checkbox" name="UF_USE_BP" id="UF_USE_BP" value="Y" <?
			?><?=($arLibrary["UF_USE_BP"] == "N" ? '' : ' checked="checked" ')
			?> />&nbsp;<label for="UF_USE_BP"><?=GetMessage("SN_BP_LABEL")?></label> </td>
	</tr>
<? if (
	$USER->IsAdmin()
	&& $object === 'group'
) {

	$arParams["DOCUMENT_TYPE"] = array("webdav", "CIBlockDocumentWebdavSocnet", $documentType); 
	$arParams["ROOT_SECTION_ID"] = __wd_get_root_section($IBLOCK_ID, $object, $object_id);

	if ($arParams["ROOT_SECTION_ID"] === true) // created new
		$arParams["ROOT_SECTION_ID"] = __wd_get_root_section($IBLOCK_ID, $object, $object_id);

	$ob = new CWebDavIblock($IBLOCK_ID, '/',
		$arParams + array(
			"ATTRIBUTES" => ($object == "user" ? array('user_id' => $object_id) : array('group_id' => $object_id))
		)
	);
?>
	<tr class="section">
		<td colspan="2" align="center"><b><?=GetMessage("WD_TAB15_TITLE")?></b></td>
	</tr>
<?
	$UF_ENTITY = $ob->GetUfEntity();
	$arUserField = $ob->GetUfFields();

	$backUrl = "/";
	if (isset($_REQUEST["back_url"]))
		$backUrl = $_REQUEST["back_url"];

	foreach ($arUserField as $fieldCode => $field)
	{
		$name = $fieldCode;
		if (!empty($field['EDIT_FORM_LABEL']))
			$name = $field['EDIT_FORM_LABEL'];
		$type = '';
		if (!empty($field['USER_TYPE']['DESCRIPTION']))
			$type = $field['USER_TYPE']['DESCRIPTION'];

?>
	<tr>
		<td width="50%" align="right" valign="top">
			<a href="/bitrix/admin/userfield_edit.php?ID=<?=$field["ID"]?>&back_url=<?=htmlspecialcharsbx($backUrl)?>"><?=htmlspecialcharsbx($name)?></a>:
		</td>
		<td width="50%">
			<i><?=htmlspecialcharsbx($type);?></i>
		</td>
	</tr>
<?
	}
?>
	<tr>
		<td colspan="2" align="center" valign="top">
			<a href="/bitrix/admin/userfield_edit.php?ENTITY_ID=<?=htmlspecialcharsbx($UF_ENTITY)?>&back_url=<?=htmlspecialcharsbx($backUrl)?>"><?=GetMessage("IB_WDUF_ADD")?></a>
		</td>
	</tr>
<? } ?>
</table>
<script>
	function wdNoteShow(e)
	{
		var expand = (this.style.height == '3em');
		this.style.height = (expand ? 'auto' : '3em');
	}
	BX(function() {
		BX.bind(BX('wd_bp_notes'),'click', wdNoteShow);
	});
</script>
<div id='wd_bp_notes' style="background-color:#FEFDEA; margin-bottom:16px; margin-top:16px; border:1px solid #D7D6BA; width:679px; position: relative; display:block; padding: 0 4px; height:3em; cursor: pointer; overflow: hidden;">
<table class='notes' style='display:block;'>
<tr><td class='content'>
<?=GetMessage("SN_BP_NOTE")?>
</td></tr></table>
</div>
<? } ?>
<?
	$arWDRights = Array(
		"IBLOCK_ID"		=> $arParams["IBLOCK_ID"],
		"ENTITY_TYPE"	=> "SECTION",
		"ENTITY_ID"		=> $arLibrary['ID'],
		"TAB_ID" 		=> 'tab_permissions',
		"SOCNET_TYPE"	=> $object,
		"SOCNET_ID"		=> $object_id,
		"SET_TITLE"	=>	"N",
		"SET_NAV_CHAIN"	=>	"N",
	);
	if ($object == "group")
		$arWDRights["SOCNET_GROUP_ID"] = $object_id;
	$APPLICATION->IncludeComponent("bitrix:webdav.iblock.rights", ".default", $arWDRights, null, array("HIDE_ICONS" => "Y"));

$popupWindow->EndContent();
$popupWindow->ShowStandardButtons();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>
