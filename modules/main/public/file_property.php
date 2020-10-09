<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

$popupWindow = new CJSPopup(GetMessage("PAGE_PROP_WINDOW_TITLE"), array("SUFFIX"=>($_GET['subdialog'] == 'Y'? 'subdialog':'')));

if (IsModuleInstalled("fileman"))
{
	if (!$USER->CanDoOperation('fileman_admin_files') && !$USER->CanDoOperation('fileman_edit_existent_files'))
		$popupWindow->ShowError(GetMessage("PAGE_PROP_ACCESS_DENIED"));
}

$io = CBXVirtualIo::GetInstance();

//Page path
$path = "/";
if (isset($_REQUEST["path"]) && $_REQUEST["path"] <> '')
	$path = $io->CombinePath("/", $_REQUEST["path"]);

//Lang
if (!isset($_REQUEST["lang"]) || $_REQUEST["lang"] == '')
	$lang = LANGUAGE_ID;

//BackUrl
$back_url = (isset($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : "");

//Site ID
$site = SITE_ID;
if (isset($_REQUEST["site"]) && $_REQUEST["site"] <> '')
{
	$obSite = CSite::GetByID($_REQUEST["site"]);
	if ($arSite = $obSite->Fetch())
		$site = $_REQUEST["site"];
}

$documentRoot = CSite::GetSiteDocRoot($site);
$absoluteFilePath = $documentRoot.$path;

//Check permissions
if (!$io->FileExists($absoluteFilePath) && !$io->DirectoryExists($absoluteFilePath))
	$popupWindow->ShowError(GetMessage("PAGE_PROP_FILE_NOT_FOUND")." (".htmlspecialcharsbx($path).")");
elseif (!$USER->CanDoFileOperation('fm_edit_existent_file',Array($site, $path)))
	$popupWindow->ShowError(GetMessage("PAGE_PROP_ACCESS_DENIED"));

$f = $io->GetFile($absoluteFilePath);
$fileContent = $f->GetContents();

$strWarning = "";

//Save page settings
if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	$strWarning = GetMessage("MAIN_SESSION_EXPIRED");
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["save"]))
{
	CUtil::JSPostUnescape();

	//Title
	if (isset($_POST["pageTitle"]) && $_POST["pageTitle"] <> '')
		$fileContent = SetPrologTitle($fileContent, $_POST["pageTitle"]);

	//Properties
	if (isset($_POST["PROPERTY"]) && is_array($_POST["PROPERTY"]))
	{
		foreach ($_POST["PROPERTY"] as $arProperty)
		{
			$arProperty["CODE"] = (isset($arProperty["CODE"]) ? trim($arProperty["CODE"]) : "");
			$arProperty["VALUE"] = (isset($arProperty["VALUE"]) ? trim($arProperty["VALUE"]) : "");

			if (preg_match("/[a-zA-Z_-~]+/i", $arProperty["CODE"]))
				$fileContent = SetPrologProperty($fileContent, $arProperty["CODE"], $arProperty["VALUE"]);
		}
	}

	//Tags
	if (isset($_POST["TAGS"]) && IsModuleInstalled("search"))
		$fileContent = SetPrologProperty($fileContent, COption::GetOptionString("search", "page_tag_property","tags"), $_POST["TAGS"]);

	$f = $io->GetFile($absoluteFilePath);
	$arUndoParams = array(
		'module' => 'fileman',
		'undoType' => 'edit_file',
		'undoHandler' => 'CFileman::UndoEditFile',
		'arContent' => array(
			'absPath' => $absoluteFilePath,
			'content' => $f->GetContents()
		)
	);

	$success = $APPLICATION->SaveFileContent($absoluteFilePath, $fileContent);

	if ($success === false && ($exception = $APPLICATION->GetException()))
		$strWarning = $exception->msg;
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

//Properties from page
$arDirProperties = Array();
if ($strWarning != "")
{
	//Restore post values if error occured
	$pageTitle = (isset($_POST["pageTitle"]) && $_POST["pageTitle"] <> '' ? $_POST["pageTitle"] : "");

	if (isset($_POST["PROPERTY"]) && is_array($_POST["PROPERTY"]))
	{
		foreach ($_POST["PROPERTY"] as $arProperty)
		{
			if (isset($arProperty["VALUE"]) && $arProperty["VALUE"] <> '')
				$arDirProperties[$arProperty["CODE"]] = $arProperty["VALUE"];
		}
	}
}
else
{
	$arPageSlice = ParseFileContent($fileContent);
	$arDirProperties = $arPageSlice["PROPERTIES"];
	$pageTitle = $arPageSlice["TITLE"];
}

//All properties for file. Includes properties from root folders
$arInheritProperties = $APPLICATION->GetDirPropertyList(Array($site, $path));
if ($arInheritProperties === false)
	$arInheritProperties = Array();

//Tags
if (IsModuleInstalled("search"))
{
	$tagPropertyCode = COption::GetOptionString("search", "page_tag_property","tags");
	$tagPropertyValue = "";

	if ($strWarning != "" && isset($_POST["TAGS"]) && $_POST["TAGS"] <> '') //Restore post value if error occured
		$tagPropertyValue = $_POST["TAGS"];
	elseif (array_key_exists($tagPropertyCode, $arDirProperties))
		$tagPropertyValue = $arDirProperties[$tagPropertyCode];

	unset($arFilemanProperties[$tagPropertyCode]);
	unset($arDirProperties[$tagPropertyCode]);
	unset($arInheritProperties[mb_strtoupper($tagPropertyCode)]);
}

//Delete equal properties
$arGlobalProperties = Array();
foreach ($arFilemanProperties as $propertyCode => $propertyDesc)
{
	if (array_key_exists($propertyCode, $arDirProperties))
		$arGlobalProperties[$propertyCode] = $arDirProperties[$propertyCode];
	else
		$arGlobalProperties[$propertyCode] = "";

	unset($arDirProperties[$propertyCode]);
	unset($arInheritProperties[mb_strtoupper($propertyCode)]);
}

foreach ($arDirProperties as $propertyCode => $propertyValue)
	unset($arInheritProperties[mb_strtoupper($propertyCode)]);
?>


<?
//HTML Output
$popupWindow->ShowTitlebar(GetMessage("PAGE_PROP_WINDOW_TITLE"));
$popupWindow->StartDescription("bx-property-page");

if ($strWarning != "")
	$popupWindow->ShowValidationError($strWarning);
?>

<p><?=GetMessage("PAGE_PROP_WINDOW_TITLE")?> <b><?=htmlspecialcharsbx($path)?></b></p>

<?if (IsModuleInstalled("fileman")):?>
	<p><a href="/bitrix/admin/fileman_html_edit.php?lang=<?=urlencode($lang)?>&site=<?=urlencode($site)?>&path=<?=urlencode($path)?>&back_url=<?=urlencode($back_url)?>"><?=GetMessage("PAGE_PROP_EDIT_IN_ADMIN")?></a></p>
<?endif?>

<?
$popupWindow->EndDescription();
$popupWindow->StartContent();
?>

<table class="bx-width100" id="bx_page_properties">

	<tr class="section">
		<td colspan="2"><?=GetMessage("PAGE_PROP_FOLDER_NAME")?></td>
	</tr>

	<tr>
		<td class="bx-popup-label bx-width30"><?=GetMessage("PAGE_PROP_NAME")?>:</td>
		<td><input type="text" style="width:90%;" name="pageTitle" value="<?=htmlspecialcharsEx($pageTitle)?>"></td>
	</tr>

	<tr class="empty">
		<td colspan="2"><div class="empty"></div></td>
	</tr>

<?if (!empty($arGlobalProperties) || !empty($arDirProperties) || !empty($arInheritProperties)):?>

	<tr class="section">
		<td colspan="2">
			<table cellspacing="0">
				<tr>
					<td><?=GetMessage("PAGE_PROP_WINDOW_TITLE")?></td>
					<td id="bx_page_prop_name">&nbsp;</td>
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
			$arFilemanProperties[$propertyCode] <> '' ? 
				htmlspecialcharsEx($arFilemanProperties[$propertyCode]) : 
				htmlspecialcharsEx($propertyCode))
		?>:</td>
		<td>

		<?$inheritValue = $APPLICATION->GetDirProperty($propertyCode, Array($site, $path));?>

		<?if ($inheritValue <> '' && $propertyValue == ''):
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

<?$propertyIndex++; endforeach;?>

<?foreach ($arInheritProperties as $propertyCode => $propertyValue): $jsInheritPropIds .= ",".$propertyIndex;?>

	<tr style="height:30px;">
		<td class="bx-popup-label bx-width30"><?=htmlspecialcharsEx($propertyCode)?>:</td>
		<td>

			<input type="hidden" name="PROPERTY[<?=$propertyIndex?>][CODE]" value="<?=htmlspecialcharsEx($propertyCode)?>" /> 

			<div id="bx_view_property_<?=$propertyIndex?>" style="overflow:hidden;padding:2px 12px 2px 2px; border:1px solid white; width:90%; cursor:text; box-sizing:border-box; -moz-box-sizing:border-box;background-color:transparent; background-position:right; background-repeat:no-repeat;" onclick="BXEditProperty(<?=$propertyIndex?>)" onmouseover="this.style.borderColor = '#434B50 #ADC0CF #ADC0CF #434B50'" onmouseout="this.style.borderColor = 'white'" class="edit-field"><?=htmlspecialcharsEx($propertyValue)?></div>

			<div id="bx_edit_property_<?=$propertyIndex?>" style="display:none;"></div>

		</td>
	</tr>

<?$propertyIndex++; endforeach; ?>

<?foreach ($arDirProperties as $propertyCode => $propertyValue):?>

		<tr id="bx_user_property_<?=$propertyIndex?>">
			<td class="bx-popup-label bx-width30"><?=htmlspecialcharsEx(ToUpper($propertyCode))?><input type="hidden" name="PROPERTY[<?=$propertyIndex?>][CODE]" value="<?=htmlspecialcharsEx(ToUpper($propertyCode))?>" />:</td>
			<td><input type="text" name="PROPERTY[<?=$propertyIndex?>][VALUE]" value="<?=htmlspecialcharsEx($propertyValue)?>" style="width:90%;"></td>
		</tr>

<?
$propertyIndex++; 
endforeach;
$jsInheritPropIds .= "];"
?>

<?if (CModule::IncludeModule("search") && isset($tagPropertyCode)):?>

	<tr class="empty">
		<td colspan="2"><div class="empty"></div></td>
	</tr>
	<tr class="section">
		<td colspan="2">
			<table cellspacing="0">
				<tr>
					<td><?=GetMessage("PAGE_PROP_TAGS_NAME")?></td>
					<td id="bx_page_tags">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>

		<tr>
			<td class="bx-popup-label bx-width30"><?=GetMessage("PAGE_PROP_TAGS")?>:</td>
			<td><?=InputTags("TAGS", $tagPropertyValue, array($site), 'style="width:90%;"');?></td>
		</tr> 
<?endif?>

</table>
<input type="hidden" name="save" value="Y" />
<?
$popupWindow->EndContent();
$popupWindow->ShowStandardButtons();
?>

<script>
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
	var td = document.getElementById("bx_page_prop_name");
	if (td)
	{
		oBXHint = new BXHint("<?=GetMessage("PAGE_PROP_DESCIPTION")?>");
		td.appendChild(oBXHint.oIcon);
	}

	var td = document.getElementById("bx_page_tags");
	if (td)
	{
		oBXHint = new BXHint("<?=GetMessage("PAGE_PROP_TAGS_DESCIPTION")?>");
		td.appendChild(oBXHint.oIcon);
	}

	<?=$jsInheritPropIds?>
	
	for (var index = 0; index < jsInheritProps.length; index++)
		oBXHint = new BXHint("<?=GetMessage("PAGE_PROP_INHERIT_TITLE")?>", document.getElementById("bx_view_property_"+ jsInheritProps[index]), {"width":200});
}

window.BXFolderEditHint();

</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>