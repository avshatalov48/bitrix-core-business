<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if(!$USER->IsAdmin() || !CModule::IncludeModule('socialnetwork'))
{
	echo GetMessage("CC_BSR_WRONG_RIGHTS");
	return;
}

$arWarnings = array();

$arSteps = array(
	"init" => GetMessage("CC_BSR_STEP_INIT"),
	"groups" => GetMessage("CC_BSR_STEP_GROUPS"),
);


if(strlen($arParams["PATH_TO_GROUP_BLOG"]) && strlen($arParams["PATH_TO_GROUP_BLOG_POST"]))
	$arSteps["group_blogs"] = GetMessage("CC_BSR_STEP_GROUPS_BLOGS");

if(strlen($arParams["PATH_TO_USER_BLOG"]) && strlen($arParams["PATH_TO_USER_BLOG_POST"]))
	$arSteps["user_blogs"] = GetMessage("CC_BSR_STEP_USERS_BLOGS");

if(intval($arParams["FORUM_ID"]) && CModule::IncludeModule('forum'))
{
	$arForum = CForumNew::GetByID($arParams["FORUM_ID"]);
	if ($arForum === false)
		$arWarnings[] = GetMessage("CC_BSR_WARN_FORUM_NOT_FOUND", array("#FORUM_ID#" => htmlspecialcharsbx($arParams["FORUM_ID"])));
	else if($arForum["INDEXATION"]!=="Y")
		$arWarnings[] = GetMessage("CC_BSR_WARN_FORUM", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/forum_edit.php?lang=".LANGUAGE_ID."&ID=".intval($arForum["ID"]))));
	
	if(strlen($arParams["PATH_TO_GROUP_FORUM_MESSAGE"]))
		$arSteps["group_forums"] = GetMessage("CC_BSR_STEP_GROUPS_FORUMS");
	if(strlen($arParams["PATH_TO_USER_FORUM_MESSAGE"]))
		$arSteps["user_forums"] = GetMessage("CC_BSR_STEP_USERS_FORUMS");
}
else
	$arWarnings[] = GetMessage("CC_BSR_WARN_FORUM_NOT_SET");

if(intval($arParams["PHOTO_GROUP_IBLOCK_ID"]) && CModule::IncludeModule('iblock'))
{
	$arIBlock = CIBlock::GetArrayByID($arParams["PHOTO_GROUP_IBLOCK_ID"]);
	if($arIBlock["INDEX_ELEMENT"]==="Y" || $arIBlock["INDEX_SECTION"]==="Y")
		$arWarnings[] = GetMessage("CC_BSR_WARN_PHOTO_GROUP_IBLOCK", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/iblock_edit.php?type=".urlencode($arIBlock["IBLOCK_TYPE_ID"])."&lang=".LANGUAGE_ID."&ID=".urlencode($arIBlock["ID"])."&admin=Y&return_url=".urlencode($APPLICATION->GetCurPageParam()))));

	if(strlen($arParams["PATH_TO_GROUP_PHOTO_ELEMENT"]))
		$arSteps["group_photos"] = GetMessage("CC_BSR_STEP_GROUPS_PHOTOS");
}

if(intval($arParams["PHOTO_USER_IBLOCK_ID"]) && CModule::IncludeModule('iblock'))
{
	$arIBlock = CIBlock::GetArrayByID($arParams["PHOTO_USER_IBLOCK_ID"]);
	if($arIBlock["INDEX_ELEMENT"]==="Y" || $arIBlock["INDEX_SECTION"]==="Y")
		$arWarnings[] = GetMessage("CC_BSR_WARN_PHOTO_USER_IBLOCK", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/iblock_edit.php?type=".urlencode($arIBlock["IBLOCK_TYPE_ID"])."&lang=".LANGUAGE_ID."&ID=".urlencode($arIBlock["ID"])."&admin=Y&return_url=".urlencode($APPLICATION->GetCurPageParam()))));

	if(strlen($arParams["PATH_TO_USER_PHOTO_ELEMENT"]))
		$arSteps["user_photos"] = GetMessage("CC_BSR_STEP_USERS_PHOTOS");
}

if(array_key_exists("group_photos", $arSteps) || array_key_exists("user_photos", $arSteps))
{
	if(intval($arParams["PHOTO_FORUM_ID"]) && CModule::IncludeModule('forum'))
	{
		$arForum = CForumNew::GetByID($arParams["PHOTO_FORUM_ID"]);
		if ($arForum === false)
			$arWarnings[] = GetMessage("CC_BSR_WARN_PHOTO_FORUM_NOT_FOUND", array("#FORUM_ID#" => htmlspecialcharsbx($arParams["PHOTO_FORUM_ID"])));
		else if($arForum["INDEXATION"]!=="Y")
			$arWarnings[] = GetMessage("CC_BSR_WARN_PHOTO_FORUM", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/forum_edit.php?lang=".LANGUAGE_ID."&ID=".intval($arForum["ID"]))));
	}
	else if ($arParams["PHOTO_USE_COMMENTS"] != "N")
		$arWarnings[] = GetMessage("CC_BSR_WARN_PHOTO_FORUM_NOT_SET");
}

if(intval($arParams["CALENDAR_GROUP_IBLOCK_ID"]) && CModule::IncludeModule('iblock'))
{
	$arIBlock = CIBlock::GetArrayByID($arParams["CALENDAR_GROUP_IBLOCK_ID"]);
	if($arIBlock["INDEX_ELEMENT"]==="Y" || $arIBlock["INDEX_SECTION"]==="Y")
		$arWarnings[] = GetMessage("CC_BSR_WARN_CALENDAR_GROUP_IBLOCK", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/iblock_edit.php?type=".urlencode($arIBlock["IBLOCK_TYPE_ID"])."&lang=".LANGUAGE_ID."&ID=".urlencode($arIBlock["ID"])."&admin=Y&return_url=".urlencode($APPLICATION->GetCurPageParam()))));

	if(strlen($arParams["PATH_TO_GROUP_CALENDAR_ELEMENT"]))
		$arSteps["group_calendars"] = GetMessage("CC_BSR_STEP_GROUPS_CALENDARS");
}

if (\Bitrix\Main\ModuleManager::isModuleInstalled('tasks'))
{
	if(intval($arParams["TASK_FORUM_ID"]) && CModule::IncludeModule('forum'))
	{
		$arForum = CForumNew::GetByID($arParams["TASK_FORUM_ID"]);
		if ($arForum === false)
		{
			$arWarnings[] = GetMessage("CC_BSR_WARN_TASK_FORUM_NOT_FOUND", array("#FORUM_ID#" => htmlspecialcharsbx($arParams["TASK_FORUM_ID"])));
		}
		else if($arForum["INDEXATION"]!=="Y")
		{
			$arWarnings[] = GetMessage("CC_BSR_WARN_TASK_FORUM", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/forum_edit.php?lang=".LANGUAGE_ID."&ID=".intval($arForum["ID"]))));
		}
	}
	else
	{
		$arWarnings[] = GetMessage("CC_BSR_WARN_TASK_FORUM_NOT_SET");
	}
}

if(intval($arParams["FILES_GROUP_IBLOCK_ID"]) && CModule::IncludeModule('iblock'))
{
	$arIBlock = CIBlock::GetArrayByID($arParams["FILES_GROUP_IBLOCK_ID"]);
	if($arIBlock["INDEX_ELEMENT"]==="Y" || $arIBlock["INDEX_SECTION"]==="Y")
		$arWarnings[] = GetMessage("CC_BSR_WARN_FILE_GROUP_IBLOCK", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/iblock_edit.php?type=".urlencode($arIBlock["IBLOCK_TYPE_ID"])."&lang=".LANGUAGE_ID."&ID=".urlencode($arIBlock["ID"])."&admin=Y&return_url=".urlencode($APPLICATION->GetCurPageParam()))));

	if(strlen($arParams["PATH_TO_GROUP_FILES_ELEMENT"]))
		$arSteps["group_files"] = GetMessage("CC_BSR_STEP_GROUPS_FILES");
}

if (
	is_array($arParams["TYPE"])
	&& in_array("groups", $arParams["TYPE"])
	&& CModule::IncludeModule('iblock')
	&& CModule::IncludeModule("wiki")
)
{
	$arIBlock = CIBlock::GetArrayByID(COption::GetOptionInt("wiki", "socnet_iblock_id"));
	if($arIBlock["INDEX_ELEMENT"]==="Y" || $arIBlock["INDEX_SECTION"]==="Y")
		$arWarnings[] = GetMessage("CC_BSR_WARN_WIKI_GROUP_IBLOCK", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/iblock_edit.php?type=".urlencode($arIBlock["IBLOCK_TYPE_ID"])."&lang=".LANGUAGE_ID."&ID=".urlencode($arIBlock["ID"])."&admin=Y&return_url=".urlencode($APPLICATION->GetCurPageParam()))));

	$arSteps["group_wiki"] = GetMessage("CC_BSR_STEP_GROUPS_WIKI");
}

if(intval($arParams["FILES_USER_IBLOCK_ID"]) && CModule::IncludeModule('iblock'))
{
	$arIBlock = CIBlock::GetArrayByID($arParams["FILES_USER_IBLOCK_ID"]);
	if($arIBlock["INDEX_ELEMENT"]==="Y" || $arIBlock["INDEX_SECTION"]==="Y")
		$arWarnings[] = GetMessage("CC_BSR_WARN_FILE_USER_IBLOCK", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/iblock_edit.php?type=".urlencode($arIBlock["IBLOCK_TYPE_ID"])."&lang=".LANGUAGE_ID."&ID=".urlencode($arIBlock["ID"])."&admin=Y&return_url=".urlencode($APPLICATION->GetCurPageParam()))));

	if(strlen($arParams["PATH_TO_USER_FILES_ELEMENT"]))
		$arSteps["user_files"] = GetMessage("CC_BSR_STEP_USERS_FILES");
}

if(array_key_exists("group_files", $arSteps) || array_key_exists("user_files", $arSteps))
{
	if(intval($arParams["FILES_FORUM_ID"]) && CModule::IncludeModule('forum'))
	{
		$arForum = CForumNew::GetByID($arParams["FILES_FORUM_ID"]);
		if ($arForum === false)
			$arWarnings[] = GetMessage("CC_BSR_WARN_FILES_FORUM_NOT_FOUND", array("#FORUM_ID#" => htmlspecialcharsbx($arParams["FILES_FORUM_ID"])));
		else if($arForum["INDEXATION"]!=="Y")
			$arWarnings[] = GetMessage("CC_BSR_WARN_FILES_FORUM", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/forum_edit.php?lang=".LANGUAGE_ID."&ID=".intval($arForum["ID"]))));
	}
	else if ($arParams["FILES_USE_COMMENTS"] != "N")
		$arWarnings[] = GetMessage("CC_BSR_WARN_FILES_FORUM_NOT_SET");
}

if(count($arWarnings) > 0)
	$arWarnings[] = GetMessage("CC_BSR_WARN_REINDEX", array("#href#" => htmlspecialcharsbx(BX_ROOT."/admin/search_reindex.php?lang=".LANGUAGE_ID)));

$arForums = array();

$FORUM_ID = intval($arParams["FORUM_ID"]);
if($FORUM_ID > 0)
	$arForums[$FORUM_ID] = true;

$PHOTO_FORUM_ID = intval($arParams["PHOTO_FORUM_ID"]);
if($PHOTO_FORUM_ID > 0)
{
	if(isset($arForums[$PHOTO_FORUM_ID]))
		$arWarnings[] = GetMessage("CC_BSR_WARN_DIST_PHOTO_FORUM");
	$arForums[$PHOTO_FORUM_ID] = true;
}

$TASK_FORUM_ID = intval($arParams["TASK_FORUM_ID"]);
if($TASK_FORUM_ID > 0)
{
	if(isset($arForums[$TASK_FORUM_ID]))
		$arWarnings[] = GetMessage("CC_BSR_WARN_DIST_TASK_FORUM");
	$arForums[$TASK_FORUM_ID] = true;
}

$FILES_FORUM_ID = intval($arParams["FILES_FORUM_ID"]);
if($FILES_FORUM_ID > 0)
{
	if(isset($arForums[$FILES_FORUM_ID]))
		$arWarnings[] = GetMessage("CC_BSR_WARN_DIST_FILES_FORUM");
	$arForums[$FILES_FORUM_ID] = true;
}

$arSteps["delete_old"] = GetMessage("CC_BSR_STEP_FINISH");

if($_GET["index"] == "y" && check_bitrix_sessid())
{
	$APPLICATION->RestartBuffer();
	@set_time_limit(0);

	if(array_key_exists($_GET["step"], $arSteps))
		$step = $_GET["step"];
	else
		$step = "init";

	if(preg_match('/^[a-zA-Z]\d+$/', $_GET["last_id"]))
		$last_id = $_GET["last_id"];
	else
		$last_id = intval($_GET["last_id"]);

	$obSearchReindex = new CSocNetSearchReindex(intval($arParams["USER_ID"]), intval($arParams["GROUP_ID"]), $arParams);
	if($step == "init")
		$obSearchReindex->InitSession($arParams["TYPE"]);
	$arResult = $obSearchReindex->StepIndex(array_keys($arSteps), $step, $last_id, 10);
	$cnt = intval($_GET["cnt"]) + $obSearchReindex->GetCounter();

	if($arResult["step"] == "end"):
	?><script>
		HighlightItem('');
		run = false;
		document.getElementById("btn_start").disabled = false;
		document.getElementById("btn_pause").disabled = true;
		document.getElementById("btn_continue").disabled = true;
	</script>
	<?echo GetMessage("CC_BSR_MESS_DONE");
	else:
	?><script>
		var url = url_template.replace(/#step#/, '<?echo $arResult["step"]?>');
		url = url.replace(/#last_id#/, '<?echo $arResult["last_id"]?>');
		url = url.replace(/#cnt#/, '<?echo $cnt?>');
		if(run)
		{
			HighlightItem('<?echo $arResult["step"]?>');
			BX.ajax.insertToNode(url, 'reindex_result');
		}
		else
		{
			continue_url = url;
			continue_item = '<?echo $arResult["step"]?>';
		}
	</script>
	<?echo GetMessage("CC_BSR_MESS_PROGRESS", array("#cnt#" => $cnt));
	endif;

	echo $APPLICATION->EndBufferContentMan();
	die();
}

$APPLICATION->SetTitle(GetMessage("CC_BSR_TITLE"));

if(count($arWarnings) > 0)
{
	echo "<ul class=\"errortext\">";
	foreach($arWarnings as $strWarning)
		echo "<li>",$strWarning,"</li>\n";
	echo "</ul>";
}
CUtil::InitJSCore(array('ajax'));
?>
<div id="reindex_result">
</div>
<ul>
<?foreach($arSteps as $id => $label):?>
	<li id="<?echo $id?>"><?echo $label?></li>
<?endforeach?>
</ul>
<script>
var run = false;
var steps = <?echo CUtil::PhpToJSObject($arSteps);?>;
var url_template = <?echo CUtil::PhpToJSObject($APPLICATION->GetCurPageParam(bitrix_sessid_get()."&index=y&step=#step#&last_id=#last_id#&cnt=#cnt#", array("step", "last_id", "cnt", "sessid", "index")));?>;
var continue_url = '';
var continue_item = '';

function HighlightItem(id)
{
	for(var x in steps)
	{
		var el = document.getElementById(x);
		if(el)
		{
			if(x == id)
				el.innerHTML = '<b>'+steps[x]+'</b>';
			else
				el.innerHTML = steps[x];
		}
	}
}
function StartIndex()
{
	document.getElementById("btn_start").disabled = true;
	document.getElementById("btn_pause").disabled = false;
	document.getElementById("btn_continue").disabled = true;

	run = true;
	continue_url = '';
	document.getElementById('reindex_result').innerHTML = '';
	HighlightItem('init');
	var url = url_template.replace(/#step#/, '');
	url = url.replace(/#last_id#/, '0');
	url = url.replace(/#cnt#/, '0');
	BX.ajax.insertToNode(url, 'reindex_result');
}
function PauseIndex()
{
	document.getElementById("btn_start").disabled = false;
	document.getElementById("btn_pause").disabled = true;
	document.getElementById("btn_continue").disabled = false;
	run = false;
}
function ContinueIndex()
{
	document.getElementById("btn_start").disabled = true;
	document.getElementById("btn_pause").disabled = false;
	document.getElementById("btn_continue").disabled = true;
	if(continue_url != '')
	{
		run = true;
		HighlightItem(continue_item);
		BX.ajax.insertToNode(continue_url, 'reindex_result');
	}
}
</script>
<form method="get">
<input type="button" id="btn_start" value="<?echo GetMessage("CC_BSR_BTN_START")?>" <?if(count($arWarnings)) echo "disabled=\"disabled\"";?> OnClick="StartIndex()">
<input type="button" id="btn_pause" value="<?echo GetMessage("CC_BSR_BTN_PAUSE")?>" disabled="disabled" OnClick="PauseIndex()">
<input type="button" id="btn_continue" value="<?echo GetMessage("CC_BSR_BTN_CONTINUE")?>" disabled="disabled" OnClick="ContinueIndex()">
</form>
<?
$this->IncludeComponentTemplate();
?>
