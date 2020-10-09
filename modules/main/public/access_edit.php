<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

IncludeModuleLangFile(__FILE__);

$popupWindow = new CJSPopup('', array("SUFFIX"=>($_GET['subdialog'] == 'Y'? 'subdialog':'')));

if (IsModuleInstalled("fileman"))
{
	if (!$USER->CanDoOperation('fileman_edit_existent_folders') && !$USER->CanDoOperation('fileman_admin_folders'))
		$popupWindow->ShowError(GetMessage("FOLDER_EDIT_ACCESS_DENIED"));
}

$io = CBXVirtualIo::GetInstance();

//Folder path
$path = "/";
if (isset($_REQUEST["path"]) && $_REQUEST["path"] <> '')
	$path = $io->CombinePath("/", $_REQUEST["path"]);

//Site ID
$site = SITE_ID;
if (isset($_REQUEST["site"]) && $_REQUEST["site"] <> '')
{
	$obSite = CSite::GetByID($_REQUEST["site"]);
	if ($arSite = $obSite->Fetch())
		$site = $_REQUEST["site"];
}

//Document Root
$documentRoot = CSite::GetSiteDocRoot($site);

//Check path permissions
if (!$io->FileExists($documentRoot.$path) && !$io->DirectoryExists($documentRoot.$path))
	$popupWindow->ShowError(GetMessage("ACCESS_EDIT_FILE_NOT_FOUND")." (".htmlspecialcharsbx($path).")");
elseif (!$USER->CanDoFileOperation('fm_edit_existent_folder', array($site, $path)))
	$popupWindow->ShowError(GetMessage("FOLDER_EDIT_ACCESS_DENIED"));
elseif (!$USER->CanDoFileOperation('fm_edit_permission', array($site, $path)))
	$popupWindow->ShowError(GetMessage("EDIT_ACCESS_TO_DENIED")." \"".htmlspecialcharsbx($path)."\"");

//Lang
if (!isset($_REQUEST["lang"]) || $_REQUEST["lang"] == '')
	$lang = LANGUAGE_ID;

//BackUrl
$back_url = (isset($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : "");

//Is a folder?
$isFolder = $io->DirectoryExists($documentRoot.$path);

//Get only used user group from .access.php file
$arUserGroupsID = array("*");

$assignFileName = "";
$assignFolderName = "";

$currentPath = $path;
while(true)
{
	//Cut / from the end
	$currentPath = rtrim($currentPath, "/");

	if ($currentPath == '')
	{
		$accessFile = "/.access.php";
		$name = "/";
	}
	else
	{
		//Find file or folder name
		$position = mb_strrpos($currentPath, "/");
		if ($position === false)
			break;

		$name = mb_substr($currentPath, $position + 1);
		$name = TrimUnsafe($name); //security fix: under Windows "my." == "my"

		//Find parent folder
		$currentPath = mb_substr($currentPath, 0, $position + 1);
		$accessFile = $currentPath.".access.php";
	}

	$PERM = Array();
	if ($io->FileExists($documentRoot.$accessFile))
		include($io->GetPhysicalName($documentRoot.$accessFile));

	if ($assignFileName == "")
	{
		$assignFileName = $name;
		$assignFolderName = ($name == "/" ? "/" : $currentPath);
	}

	if (isset($PERM[$name]) && is_array($PERM[$name]))
		$arUserGroupsID = array_merge($arUserGroupsID, array_keys($PERM[$name]));

	if ($currentPath == '')
		break;
}

foreach($arUserGroupsID as $key=>$val)
	if(preg_match('/^[0-9]+$/', $val))
		$arUserGroupsID[$key] = "G".$val;

$arUserGroupsID = array_unique($arUserGroupsID);

//Get all tasks
$arPermTypes = array();
$obTask = CTask::GetList(array("LETTER" => "ASC"), array("MODULE_ID" => "main", "BINDING" => "file"));
while($arTask = $obTask->Fetch())
	$arPermTypes[$arTask["ID"]] = CTask::GetLangTitle($arTask["NAME"], $arTask["MODULE_ID"]);

//Current file/folder permissions
$currentPermission = array();
if($io->FileExists($documentRoot.$assignFolderName.".access.php"))
{
	$PERM = array();
	include($io->GetPhysicalName($documentRoot.$assignFolderName.".access.php"));

	foreach($PERM as $file => $arPerm)
		foreach($arPerm as $code => $permission)
			$currentPermission[$file][(preg_match('/^[0-9]+$/', $code)? "G".$code : $code)] = $permission;
}

$strWarning = "";

//Save permissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	$strWarning = GetMessage("MAIN_SESSION_EXPIRED");
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["save"]))
{
	CUtil::JSPostUnescape();
	$arSavePermission = array();

	if($_POST["REMOVE_PERMISSIONS"] == "Y")
	{
		if($path != "/")
		{
			$APPLICATION->RemoveFileAccessPermission(array($site, $path));

			if ($e = $APPLICATION->GetException())
				$strWarning = $e->msg;
		}
	}
	else
	{
		if (isset($_POST["PERMISSION"]) && is_array($_POST["PERMISSION"]))
		{
			if (isset($currentPermission[$assignFileName]) && is_array($currentPermission[$assignFileName]))
				$arSavePermission = $currentPermission[$assignFileName];

			$isAdmin = $USER->IsAdmin();

			foreach ($_POST["PERMISSION"] as $groupID => $taskID)
			{
				if($groupID !== "*")
				{
					$groupID = trim($groupID);
					if($groupID == '')
						continue;
				}
				elseif (!$isAdmin)
					continue;

				// if not set task - delete permission
				$taskID = intval($taskID);
				if ($taskID <= 0)
				{
					unset($arSavePermission[$groupID]);
					continue;
				}

				$obTask = CTask::GetById($taskID);
				if ( ($arTask = $obTask->Fetch()) && $arTask["LETTER"] && $arTask["SYS"] == "Y")
					$permLetter = $arTask["LETTER"];
				else
					$permLetter = "T_".$taskID;

				$arSavePermission[$groupID] = $permLetter;
			}
		}

		$APPLICATION->SetFileAccessPermission(array($site, $path), $arSavePermission);

		if ($e = $APPLICATION->GetException())
			$strWarning = $e->msg;
	}

	//Close window
	if ($strWarning == "")
	{
		$popupWindow->Close($bReload=($_GET['subdialog'] <> 'Y'), $back_url);
		die();
	}
}

echo CJSCore::Init(array('access'), true);

//HTML output
if ($isFolder)
	$popupWindow->ShowTitlebar(GetMessage("EDIT_ACCESS_TO_FOLDER"));
else
	$popupWindow->ShowTitlebar(GetMessage("EDIT_ACCESS_TO_FILE"));

$popupWindow->StartDescription($isFolder ? "bx-access-folder" : "bx-access-page");

if ($strWarning != "")
	$popupWindow->ShowValidationError($strWarning);
?>

<p><b><?=($isFolder ? GetMessage("EDIT_ACCESS_TO_FOLDER") : GetMessage("EDIT_ACCESS_TO_FILE"))?></b> <?=htmlspecialcharsbx($path);?></p>

<?
$popupWindow->EndDescription();
$popupWindow->StartContent();
?>

<table class="bx-width100" id="bx_permission_table">
	<tr>
		<td width="45%"><b><?=GetMessage("EDIT_ACCESS_USER_GROUP")?></b></td>
		<td><b><?=GetMessage("EDIT_ACCESS_PERMISSION")?></b> </td>
	</tr>
	<tr class="empty">
		<td colspan="2"></td>
	</tr>

<?
//names for access codes
$access = new CAccess();
$arNames = $access->GetNames($arUserGroupsID, true);

//sort codes by sorted names
$positions = array_flip(array_keys($arNames));
usort($arUserGroupsID,
	function($a, $b) use ($positions)
	{
		if(!isset($positions[$a]) && !isset($positions[$b])) return 0;
		if(!isset($positions[$a])) return 1;
		if(!isset($positions[$b])) return -1;
		return ($positions[$a] > $positions[$b]? 1 : -1);
	}
);

//Javascript variables
$jsTaskArray = "window.BXTaskArray = {'0':'".CUtil::JSEscape(GetMessage("EDIT_ACCESS_SET_INHERIT"))."'";
foreach ($arPermTypes as $taskID => $taskTitle)
	$jsTaskArray .= ",'".$taskID."':'".CUtil::JSEscape($taskTitle)."'";
$jsTaskArray .= "};";

$jsInheritPerm = "";
$jsInheritPermID = "var jsInheritPermIDs = [";
$bWasCurrentPerm = false;

foreach($arUserGroupsID as $access_code):

	//Restore post value if error occured
	$errorOccured = ($strWarning != "" && isset($_POST["PERMISSION"]) && is_array($_POST["PERMISSION"]) && array_key_exists($access_code, $_POST["PERMISSION"]));

	//Inherit Task
	list ($inheritTaskID) = $APPLICATION->GetFileAccessPermission(Array($site, $assignFolderName), Array($access_code), true);

	if (!array_key_exists($inheritTaskID, $arPermTypes))
	{
		if ($access_code == "*")
			$inheritTaskID = CTask::GetIdByLetter("D", "main", "file");
		else
			continue;
	}

	//Current permission
	$currentPerm = false;

	if ($errorOccured)
	{
		//Restore post value if error occured
		$currentPerm = intval($_POST["PERMISSION"][$access_code]);
	}
	elseif (isset($currentPermission[$assignFileName]) && isset($currentPermission[$assignFileName][$access_code]))
	{
		$permLetter = $currentPermission[$assignFileName][$access_code];

		if (mb_substr($permLetter, 0, 2) == "T_")
		{
			$currentPerm = intval(mb_substr($permLetter, 2));
			if (!array_key_exists($currentPerm, $arPermTypes))
				$currentPerm = false;
		}
		else
			$currentPerm = CTask::GetIdByLetter($permLetter, "main", "file");
	}

	if ($currentPerm === false && $access_code == "*" && $path == "/")
		$currentPerm = $inheritTaskID;

	if ($access_code == "*")
		$jsInheritPerm = $inheritTaskID;

	$permissionID = $access_code."_".intval($currentPerm)."_".intval($inheritTaskID);?>

	<tr>
		<td><?=($access_code == "*"? GetMessage("EDIT_ACCESS_ALL_GROUPS") : ($arNames[$access_code]["provider"] <> ''? '<b>'.$arNames[$access_code]["provider"].':</b> ':'').$arNames[$access_code]["name"])?></td>
		<td>
			<?if ($currentPerm === false && $path != "/"): //Inherit permission
				$jsInheritPermID .= ",'".$permissionID."'";
			?>

				<div id="bx_permission_view_<?=$permissionID?>" onclick="BXEditPermission('<?=$permissionID?>')" class="edit-field" style="width:90%;">
					<?=GetMessage("EDIT_ACCESS_SET_INHERITED")." &quot;".htmlspecialcharsEx($arPermTypes[$inheritTaskID])."&quot;"?>
				</div>

				<div id="bx_permission_edit_<?=$permissionID?>" style="display:none;"></div>

			<?
			else: //Current permission
				$bWasCurrentPerm = true;
			?>

				<select name="PERMISSION[<?=$access_code?>]" style="width:90%;" id="bx_task_list_<?=$permissionID?>">

					<?if ($path == "/"):?>
						<option value="0"><?=GetMessage("EDIT_ACCESS_NOT_SET")?></option>
					<?else:?>
						<option value="0"><?=GetMessage("EDIT_ACCESS_SET_INHERIT")." &quot;".htmlspecialcharsEx($arPermTypes[$inheritTaskID])."&quot;"?></option>
					<?endif?>

					<?foreach ($arPermTypes as $taskID => $taskTitle):?>
						<option value="<?=$taskID?>"<?if ($currentPerm == $taskID):?> selected="selected"<?endif?>><?=htmlspecialcharsEx($taskTitle);?></option>
					<?endforeach?>

				</select>

			<?endif?>
		</td>
	</tr>

<?
endforeach;

$jsInheritPermID .= "];";
?>

</table>

<p><a href="javascript:void(0)" onclick="BX.Access.ShowForm({callback:BXAddNewPermission})"><?=GetMessage("EDIT_ACCESS_ADD_PERMISSION")?></a></p>

<?if($bWasCurrentPerm && $path != "/"):?>
	<p><b><a href="javascript:void(0)" onclick="BXClearPermission()"><?=($isFolder? GetMessage("EDIT_ACCESS_REMOVE_PERM"):GetMessage("EDIT_ACCESS_REMOVE_PERM_FILE"))?></a></b></p>
	<input type="hidden" name="REMOVE_PERMISSIONS" id="REMOVE_PERMISSIONS" value="">
<?endif?>

<input type="hidden" name="save" value="Y" />
<?
$popupWindow->EndContent();
$popupWindow->ShowStandardButtons();

$arSel = array();
foreach($arUserGroupsID as $code)
	$arSel[$code] = true;
?>

<script>
BX.Access.Init();
BX.Access.SetSelected(<?=CUtil::PhpToJSObject($arSel)?>);

<?=$jsTaskArray?>

window.BXAddNewPermission = function(arRights)
{
	var table = document.getElementById("bx_permission_table");

	for(var provider in arRights)
	{
		for(var id in arRights[provider])
		{
			//Create new row
			var tableRow = table.insertRow(table.rows.length);

			var groupTD = tableRow.insertCell(0);
			var currentTD = tableRow.insertCell(1);

			var pr = BX.Access.GetProviderName(provider);
			groupTD.innerHTML = (pr? '<b>'+pr+':</b> ':'')+arRights[provider][id].name;

			//Insert Task Select
			var permissionID = Math.round(Math.random() * 100000);
			var taskSelect = BXCreateTaskList(permissionID, 0, 0, id);
			taskSelect.onblur = "";

			currentTD.appendChild(taskSelect);
		}
	}

	return false;
};

window.BXCreateTaskList = function(permissionID, currentPermission, inheritPermission, userGroupID)
{
	var select = document.createElement("SELECT");
	select.name = "PERMISSION["+userGroupID+"]";
	select.style.width = "90%";
	select.onblur = function(){BXBlurEditPermission(select, permissionID)};
	select.id = "bx_task_list_" + permissionID;

	//For IE 5.0
	var selectDocument = select.ownerDocument;
	if (!selectDocument)
		selectDocument = select.document;

	var selectedIndex = 0;

	<?if ($path == "/"):?>
		window.BXTaskArray["0"] = "<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_NOT_SET"))?>";
	<?else:?>
		window.BXTaskArray["0"] = "<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_SET_INHERIT"))?>" + " \"" + window.BXTaskArray[(inheritPermission == 0 ? <?=intval($jsInheritPerm)?> : inheritPermission)] + "\"";
	<?endif?>

	for(var taskID in BXTaskArray)
	{
		var option = selectDocument.createElement("OPTION");
		option.text = window.BXTaskArray[taskID];
		option.value = taskID;

		select.options.add(option);

		if (taskID == currentPermission)
			selectedIndex = select.options.length - 1;
	}

	select.selectedIndex = selectedIndex;

	return select;
};

window.BXBlurEditPermission = function(select, permissionID)
{
	var viewPermission = document.getElementById("bx_permission_view_" + permissionID);
	var setPermission = select.options[select.selectedIndex].value;

	var arPermID = permissionID.split("_");
	var userGroupID = arPermID[0];
	var currentPermission = arPermID[1];

	if (setPermission == currentPermission)
	{
		var editPermission = document.getElementById("bx_permission_edit_" + permissionID);

		viewPermission.style.display = "block";
		editPermission.style.display = "none";

		while (editPermission.firstChild)
			editPermission.removeChild(editPermission.firstChild);
	}
};

window.BXEditPermission = function(permissionID)
{
	if (document.getElementById("bx_task_list_" + permissionID))
		return;

	var arPermID = permissionID.split("_"); //Format permissionID: UserGroup_CurrentPermission_InheritPermission

	var userGroupID = arPermID[0];
	var currentPermission = arPermID[1];
	var inheritPermission = arPermID[2];

	if (userGroupID == "0")
		userGroupID = "*";

	var editPermission = document.getElementById("bx_permission_edit_" + permissionID);
	var viewPermission = document.getElementById("bx_permission_view_" + permissionID);

	editPermission.style.display = "block";
	viewPermission.style.display = "none";

	var taskSelect = BXCreateTaskList(permissionID, currentPermission, inheritPermission, userGroupID);

	editPermission.appendChild(taskSelect);
	taskSelect.focus();
};


window.BXCreateAccessHint = function()
{
	var table = document.getElementById("bx_permission_table");
	var tableRow = table.rows[0];

	var groupTD = tableRow.cells[0];
	var currentTD = tableRow.cells[1];

	var oBXHint = new BXHint("<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_PERMISSION_INFO"))?>");
	currentTD.appendChild(oBXHint.oIcon);


	<?=$jsInheritPermID?>

	for (var index = 0; index < jsInheritPermIDs.length; index++)
		oBXHint = new BXHint("<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_SET_PERMISSION"))?>", document.getElementById("bx_permission_view_"+ jsInheritPermIDs[index]), {"width":200});
};

window.BXClearPermission = function()
{
	if(confirm('<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_REMOVE_PERM_CONF"))?>'))
	{
		BX("REMOVE_PERMISSIONS").value = "Y";
		BX.WindowManager.Get().PostParameters();
	}
};

window.BXCreateAccessHint();
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>