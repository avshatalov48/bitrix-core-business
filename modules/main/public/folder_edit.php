<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

$popupWindow = new CJSPopup(GetMessage('FOLDER_EDIT_WINDOW_TITLE'), array("SUFFIX"=>($_GET['subdialog'] == 'Y'? 'subdialog':'')));

if (IsModuleInstalled("fileman"))
{
	if (!$USER->CanDoOperation('fileman_edit_existent_folders') && !$USER->CanDoOperation('fileman_admin_folders'))
		$popupWindow->ShowError(GetMessage("FOLDER_EDIT_ACCESS_DENIED"));
}

//Site ID
$site = SITE_ID;
if (isset($_REQUEST["site"]) && strlen($_REQUEST["site"]) > 0)
{
	$obSite = CSite::GetByID($_REQUEST["site"]);
	if ($arSite = $obSite->Fetch())
		$site = $_REQUEST["site"];
}

$io = CBXVirtualIo::GetInstance();

//Folder path
$path = "";
$documentRoot = CSite::GetSiteDocRoot($site);
if (isset($_REQUEST["path"]) && strlen($_REQUEST["path"]) > 0)
{
	$path = $io->CombinePath("/", $_REQUEST["path"]);

	//Find real folder
	while($path != "/" && !$io->DirectoryExists($documentRoot.$path))
	{
		$position = strrpos($path, "/");
		if ($position === false)
			break;

		$path = substr($path, 0, $position);
	}

	//Cut / from the end
	$path = rtrim($path, "/");
}

if (strlen($path) <= 0 || !$io->DirectoryExists($documentRoot.$path))
	$path = "/";

//Absolute path
$absolutePath = $documentRoot.($path != "/" ? $path : "");

//Lang
if (!isset($_REQUEST["lang"]) || strlen($_REQUEST["lang"]) <= 0)
	$lang = LANGUAGE_ID;

//BackUrl
$back_url = (isset($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : "");

//Check permissions
if(!$USER->CanDoFileOperation('fm_edit_existent_folder',Array($site, $path)))
	$popupWindow->ShowError(GetMessage("FOLDER_EDIT_ACCESS_DENIED"));

$strWarning = "";

//Save folder settings
if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	$strWarning = GetMessage("MAIN_SESSION_EXPIRED");
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["save"]))
{
	CUtil::JSPostUnescape();

	$bNeedSectionFile = false;
	$strSectionName = "";
	if (isset($_POST["sSectionName"]) && strlen($_POST["sSectionName"]) > 0)
	{
		$strSectionName = "\$sSectionName = \"".EscapePHPString($_POST["sSectionName"])."\";\n";
		$bNeedSectionFile = true;
	}

	$strDirProperties = "\$arDirProperties = Array(\n";

	if (isset($_POST["PROPERTY"]) && is_array($_POST["PROPERTY"]))
	{
		$bNeedComma = false;
		foreach ($_POST["PROPERTY"] as $arProperty)
		{
			$arProperty["CODE"] = (isset($arProperty["CODE"]) ? trim($arProperty["CODE"]) : "");
			$arProperty["VALUE"] = (isset($arProperty["VALUE"]) ? trim($arProperty["VALUE"]) : "");

			if (strlen($arProperty["VALUE"]) > 0 && preg_match("/[a-zA-Z_-~]+/i", $arProperty["CODE"]) )
			{
				if($bNeedComma)
					$strDirProperties .= ",\n";

				$strDirProperties .= "   \"".EscapePHPString($arProperty["CODE"])."\" => \"".EscapePHPString($arProperty["VALUE"])."\"";
				$bNeedComma = true;
				$bNeedSectionFile = true;
			}
		}
	}

	$strDirProperties .= "\n);\n";

	$f = $io->GetFile($documentRoot.$path."/.section.php");
	$arUndoParams = array(
		'module' => 'fileman',
		'undoType' => 'edit_section',
		'undoHandler' => 'CFileman::UndoEditFile',
		'arContent' => array(
			'absPath' => $documentRoot.$path."/.section.php",
			'content' => $f->GetContents()
		)
	);

	//Save or delete data file
	if($bNeedSectionFile)
	{
		$APPLICATION->SaveFileContent($absolutePath."/.section.php", "<"."?\n".$strSectionName.$strDirProperties."?".">");
		
		$module_id = "fileman";
		if(COption::GetOptionString($module_id, "log_page", "Y")=="Y")
		{
			$res_log['path'] = substr($path, 1);
			CEventLog::Log(
				"content",
				"SECTION_EDIT",
				"main",
				"",
				serialize($res_log)
			);
		}
	}
	else
		$io->Delete($documentRoot.$path."/.section.php");

	if ($e = $APPLICATION->GetException())
	{
		$strWarning = $e->msg;
	}
	else
	{
		CUndo::ShowUndoMessage(CUndo::Add($arUndoParams));

		if($_GET['subdialog'] == 'Y')
			echo "<script>structReload('".urlencode($_REQUEST["path"])."');</script>";
		
		$popupWindow->Close($bReload=($_GET['subdialog'] <> 'Y'), $back_url);
		die();
	}
}

//Properties from fileman settings
$arFilemanProperties = Array();
if (CModule::IncludeModule("fileman") && is_callable(Array("CFileMan", "GetPropstypes")))
	$arFilemanProperties = CFileMan::GetPropstypes($site);

//Properties from current .section.php
$arDirProperties = Array(); 

if ($strWarning != "")
{
	//Restore post values if error occured
	$sSectionName = (isset($_POST["sSectionName"]) && strlen($_POST["sSectionName"]) > 0 ? $_POST["sSectionName"] : "");

	if (isset($_POST["PROPERTY"]) && is_array($_POST["PROPERTY"]))
	{
		foreach ($_POST["PROPERTY"] as $arProperty)
		{
			if (isset($arProperty["VALUE"]) && strlen($arProperty["VALUE"]) > 0)
				$arDirProperties[$arProperty["CODE"]] = $arProperty["VALUE"];
		}
	}
}
else
{
	$sSectionName = "";
	if($io->FileExists($absolutePath."/.section.php"))
		include($io->GetPhysicalName($absolutePath."/.section.php"));
}

$arInheritProperties = $APPLICATION->GetDirPropertyList(Array($site, $path)); //All properties for folder. Includes properties from root folders
if ($arInheritProperties === false)
	$arInheritProperties = Array();

$arGlobalProperties = Array();
foreach ($arFilemanProperties as $propertyCode => $propertyDesc)
{
	if (array_key_exists($propertyCode, $arDirProperties))
		$arGlobalProperties[$propertyCode] = $arDirProperties[$propertyCode];
	else
		$arGlobalProperties[$propertyCode] = "";

	unset($arDirProperties[$propertyCode]);
	unset($arInheritProperties[strtoupper($propertyCode)]);
}

foreach ($arDirProperties as $propertyCode => $propertyValue)
	unset($arInheritProperties[strtoupper($propertyCode)]);

$popupWindow->ShowTitlebar(GetMessage('FOLDER_EDIT_WINDOW_TITLE'));
$popupWindow->StartDescription("bx-property-folder");

if($strWarning != "")
	$popupWindow->ShowValidationError($strWarning);
?>

	<p><b><?=GetMessage("FOLDER_EDIT_WINDOW_TITLE");?> <?=htmlspecialcharsbx($path);?></b></p>

	<?if (IsModuleInstalled("fileman")):?>
		<p><a href="/bitrix/admin/fileman_folder.php?lang=<?=urlencode($lang)?>&site=<?=urlencode($site)?>&path=<?=urlencode($path)?>&back_url=<?=urlencode($back_url)?>"><?=GetMessage("FOLDER_EDIT_IN_ADMIN_SECTION")?></a></p>
	<?endif?>

<?
$popupWindow->EndDescription();
$popupWindow->StartContent();
?>

<table class="bx-width100" id="bx_folder_properties">

	<tr class="section">
		<td colspan="2">
			<table cellspacing="0">
				<tr>
					<td><?=GetMessage("FOLDER_EDIT_FOLDER_NAME")?></td>
					<td id="bx_folder_name">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td class="bx-popup-label bx-width30"><?=GetMessage("FOLDER_EDIT_NAME")?>:</td>
		<td><input type="text" style="width:90%;" name="sSectionName" value="<?=htmlspecialcharsEx($sSectionName)?>"></td>
	</tr>

	<tr class="empty">
		<td colspan="2"><div class="empty"></div></td>
	</tr>

<?if (!empty($arGlobalProperties) || !empty($arDirProperties) || !empty($arInheritProperties)):?>

	<tr class="section">
		<td colspan="2">
			<table cellspacing="0">
				<tr>
					<td><?=GetMessage("FOLDER_EDIT_WINDOW_TITLE");?></td>
					<td id="bx_folder_prop_name">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>

<?endif?>


<?
$propertyIndex = 0;
$jsInheritPropIds = "var jsInheritProps = [";

foreach ($arGlobalProperties as $propertyCode => $propertyValue):?>

	<tr style="height:30px;">
		<td class="bx-popup-label bx-width30"><?=(
			strlen($arFilemanProperties[$propertyCode]) > 0 ? 
				htmlspecialcharsEx($arFilemanProperties[$propertyCode]) : 
				htmlspecialcharsEx($propertyCode))
		?>:</td>
		<td>

		<?$inheritValue = $APPLICATION->GetDirProperty($propertyCode, Array($site, $path));?>

		<?if (strlen($inheritValue) > 0 && strlen($propertyValue) <= 0):
			$jsInheritPropIds .= ",".$propertyIndex;
		?>

			<input type="hidden" name="PROPERTY[<?=$propertyIndex?>][CODE]" value="<?=htmlspecialcharsEx($propertyCode)?>" /> 

			<div id="bx_view_property_<?=$propertyIndex?>" style="overflow:hidden;padding:2px 12px 2px 2px; border:1px solid white; width:90%; cursor:text; box-sizing:border-box; -moz-box-sizing:border-box;background-color:transparent; background-position:right; background-repeat:no-repeat;" onclick="BXEditProperty(<?=$propertyIndex?>)" onmouseover="this.style.borderColor = '#434B50 #ADC0CF #ADC0CF #434B50';" onmouseout="this.style.borderColor = 'white'" class="edit-field"><?=htmlspecialcharsEx($inheritValue)?></div>

			<div id="bx_edit_property_<?=$propertyIndex?>" style="display:none;"></div>

		<?else:?>

			<input type="text" name="PROPERTY[<?=$propertyIndex?>][VALUE]" value="<?=htmlspecialcharsEx($propertyValue)?>" style="width:90%;"><input type="hidden" name="PROPERTY[<?=$propertyIndex?>][CODE]" value="<?=htmlspecialcharsEx($propertyCode)?>" />

		<?endif?>
		</td>
	</tr>

<?
	$propertyIndex++;
	endforeach;
?>

<?
	foreach ($arInheritProperties as $propertyCode => $propertyValue):
	$jsInheritPropIds .= ",".$propertyIndex;
?>

	<tr style="height:30px;">
		<td class="bx-popup-label bx-width30"><?=htmlspecialcharsEx($propertyCode)?>:</td>
		<td>

			<input type="hidden" name="PROPERTY[<?=$propertyIndex?>][CODE]" value="<?=htmlspecialcharsEx($propertyCode)?>" /> 

			<div id="bx_view_property_<?=$propertyIndex?>" style="overflow:hidden;padding:2px 12px 2px 2px; border:1px solid white; width:90%; cursor:text; box-sizing:border-box; -moz-box-sizing:border-box;background-color:transparent; background-position:right; background-repeat:no-repeat;" onclick="BXEditProperty(<?=$propertyIndex?>)" onmouseover="this.style.borderColor = '#434B50 #ADC0CF #ADC0CF #434B50'" onmouseout="this.style.borderColor = 'white'" class="edit-field"><?=htmlspecialcharsEx($propertyValue)?></div>

			<div id="bx_edit_property_<?=$propertyIndex?>" style="display:none;"></div>

		</td>
	</tr>

<?
	$propertyIndex++;
	endforeach;
	$jsInheritPropIds .= "];";
?>

<?foreach ($arDirProperties as $propertyCode => $propertyValue):?>

		<tr id="bx_user_property_<?=$propertyIndex?>">
			<td class="bx-popup-label bx-width30"><?=htmlspecialcharsEx(ToUpper($propertyCode))?><input type="hidden" name="PROPERTY[<?=$propertyIndex?>][CODE]" value="<?=htmlspecialcharsEx(ToUpper($propertyCode))?>" />:</td>
			<td><input type="text" name="PROPERTY[<?=$propertyIndex?>][VALUE]" value="<?=htmlspecialcharsEx($propertyValue)?>" style="width:90%;"></td>
		</tr>

<?
	$propertyIndex++;
	endforeach;
?>
</table>
<input type="hidden" name="save" value="Y" />
<?
$popupWindow->EndContent();
$popupWindow->ShowStandardButtons();
?>

<script>
window.BXAddNewProperty = function(linkhref, propertyIndex)
{
	propertyIndex++;

	linkhref.onclick = function () { return BXAddNewProperty(linkhref, propertyIndex)}

	var propertyTable = document.getElementById("bx_folder_properties");
	var tableRow = propertyTable.insertRow(document.getElementById("bx_user_property_end").rowIndex);
	var codeTableCell = tableRow.insertCell(0);
	var valueTableCell = tableRow.insertCell(1);

	tableRow.id = "bx_user_property_" + propertyIndex;
	codeTableCell.align = "right";
	codeTableCell.innerHTML = '<input type="text" style="width:110px;" name="PROPERTY['+propertyIndex+'][CODE]" value="" />:'
	valueTableCell.innerHTML = '<input type="text" name="PROPERTY['+propertyIndex+'][VALUE]" value="" style="width:90%;">' +
										'&nbsp;<a href="" onclick="return BXDeleteProperty('+propertyIndex+');">x</a>';

	return false;
}

window.BXDeleteProperty = function(propertyIndex)
{
	var propertyTable = document.getElementById("bx_folder_properties");
	var propertyCaption = document.getElementById("bx_additional_properties");

	if (!propertyTable || !propertyCaption)
		return;

	propertyTable.deleteRow(document.getElementById("bx_user_property_" + propertyIndex).rowIndex);

	if (propertyCaption.rowIndex == propertyTable.rows.length - 1)
		propertyTable.deleteRow(propertyCaption.rowIndex);

	return false;
}

window.BXBlurProperty = function(element, propertyIndex)
{
	var viewProperty = document.getElementById("bx_view_property_" + propertyIndex);

	if (element.value == "" || element.value == viewProperty.innerHTML)
	{
		var editProperty = document.getElementById("bx_edit_property_" + propertyIndex);

		viewProperty.style.display = "block";
		editProperty.style.display = "none";

		while (editProperty.firstChild)
			editProperty.removeChild(editProperty.firstChild);
	}
}

window.BXEditProperty = function(propertyIndex)
{
	if (document.getElementById("bx_property_input_" + propertyIndex))
		return;

	var editProperty = document.getElementById("bx_edit_property_" + propertyIndex);
	var viewProperty = document.getElementById("bx_view_property_" + propertyIndex);

	viewProperty.style.display = "none";
	editProperty.style.display = "block";

	var input = document.createElement("INPUT");

	input.type = "text";
	input.name = "PROPERTY["+propertyIndex+"][VALUE]";
	input.style.width = "90%";
	input.style.padding = "2px";
	input.id = "bx_property_input_" + propertyIndex;
	input.onblur = function () {BXBlurProperty(input,propertyIndex)};
	input.value = viewProperty.innerHTML;

	editProperty.appendChild(input);
	input.focus();
	input.select();

}

window.BXFolderEditHint = function()
{
	var td = document.getElementById("bx_folder_name");
	oBXHint = new BXHint("<?=GetMessage("FOLDER_NAME_TITLE")?>");
	td.appendChild(oBXHint.oIcon);

	var td = document.getElementById("bx_folder_prop_name");
	if (td)
	{
		oBXHint = new BXHint("<?=GetMessage("FOLDER_PROP_TITLE")?>");
		td.appendChild(oBXHint.oIcon);
	}

	<?=$jsInheritPropIds?>
	
	for (var index = 0; index < jsInheritProps.length; index++)
		oBXHint = new BXHint("<?=GetMessage("FOLDER_EDIT_PROP_TITLE")?>", document.getElementById("bx_view_property_"+ jsInheritProps[index]), {"width":200});
}

window.BXFolderEditHint();


</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>