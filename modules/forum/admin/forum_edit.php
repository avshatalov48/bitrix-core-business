<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/**
 * @global CMain $APPLICATION
 * @global array $aSortDirection
 * @global array $aSortTypes
 * @param CBitrixComponent $this
 */
$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
/********************************************************************
				Simple text
********************************************************************/
	
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arError = array();
$bVarsFromForm = false;
$arFields = array();
$message = false;
$ID = intVal($_REQUEST["ID"]);
$arSites = array();
$db_res = CSite::GetList($by = "sort", $order = "asc");
while ($res = $db_res->GetNext())
	$arSites[$res["LID"]] = $res;
$arGroups = CForumGroup::GetByLang(LANGUAGE_ID);
array_unshift($arGroups, array("ID" => 0, "NAME" => GetMessage("FE_ROOT_GROUP")));
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Action
********************************************************************/
if ($_SERVER['REQUEST_METHOD'] == "POST" && $forumPermissions >= "W" && $_REQUEST["Update"] == "Y" && check_bitrix_sessid())
{
	if ($ID > 0 && !CForumNew::CanUserUpdateForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
	{
		$arError[] = array(
			"code" => "not_right_for_edit",
			"title" => GetMessage("FE_NO_PERMS2UPDATE"));
	}
	elseif ($ID <= 0 && !CForumNew::CanUserAddForum($USER->GetUserGroupArray(), $USER->GetID()))
	{
		$arError[] = array(
			"code" => "not_right_for_add",
			"title" => GetMessage("FE_NO_PERMS2ADD"));
	}
	else
	{
		$arFields = Array(
			"NAME" => $_REQUEST["NAME"],
			"DESCRIPTION" => $_REQUEST["DESCRIPTION"],
			"FORUM_GROUP_ID" => $_REQUEST["FORUM_GROUP_ID"],
			"GROUP_ID" => $_REQUEST["GROUP"],
			
			"SITES" => array(), 
			"PATH2FORUM_MESSAGE" => $_REQUEST["PATH2FORUM_MESSAGE"],
			
			"ACTIVE" => ($_REQUEST["ACTIVE"] == "Y" ? "Y" : "N"),
			"MODERATION" => ($_REQUEST["MODERATION"] == "Y" ? "Y" : "N"),
			"INDEXATION" => ($_REQUEST["INDEXATION"] == "Y" ? "Y" : "N"),
			"DEDUPLICATION" => ($_REQUEST["DEDUPLICATION"] == "Y" ? "Y" : "N"),
			
			"SORT" => (intVal($_REQUEST["SORT"]) <= 0 ? 150 : $_REQUEST["SORT"]),
			"ORDER_BY" => $_REQUEST["ORDER_BY"],
			"ORDER_DIRECTION" => $_REQUEST["ORDER_DIRECTION"],
			
			"ASK_GUEST_EMAIL" => ($_REQUEST["ASK_GUEST_EMAIL"] == "Y" ? "Y" : "N"),
			"USE_CAPTCHA" => ($_REQUEST["USE_CAPTCHA"] == "Y" ? "Y" : "N"),
			
			"ALLOW_HTML" => ($_REQUEST["ALLOW_HTML"] == "Y" ? "Y" : "N"),
			"ALLOW_ANCHOR" => ($_REQUEST["ALLOW_ANCHOR"] == "Y" ? "Y" : "N"),
			"ALLOW_BIU" => ($_REQUEST["ALLOW_BIU"] == "Y" ? "Y" : "N"),
			"ALLOW_IMG" => ($_REQUEST["ALLOW_IMG"] == "Y" ? "Y" : "N"),
			"ALLOW_VIDEO" => ($_REQUEST["ALLOW_VIDEO"] == "Y" ? "Y" : "N"),
			"ALLOW_LIST" => ($_REQUEST["ALLOW_LIST"] == "Y" ? "Y" : "N"),
			"ALLOW_QUOTE" => ($_REQUEST["ALLOW_QUOTE"] == "Y" ? "Y" : "N"),
			"ALLOW_CODE" => ($_REQUEST["ALLOW_CODE"] == "Y" ? "Y" : "N"),
			"ALLOW_TABLE" => ($_REQUEST["ALLOW_TABLE"] == "Y" ? "Y" : "N"),
			"ALLOW_ALIGN" => ($_REQUEST["ALLOW_ALIGN"] == "Y" ? "Y" : "N"),
			"ALLOW_FONT" => ($_REQUEST["ALLOW_FONT"] == "Y" ? "Y" : "N"),
			"ALLOW_SMILES" => ($_REQUEST["ALLOW_SMILES"] == "Y" ? "Y" : "N"),
			"ALLOW_UPLOAD" => (in_array($_REQUEST["ALLOW_UPLOAD"], array("Y", "A", "F")) ? $_REQUEST["ALLOW_UPLOAD"] : "N"), 
			"ALLOW_UPLOAD_EXT" => $_REQUEST["ALLOW_UPLOAD_EXT"],
			"ALLOW_TOPIC_TITLED" => ($_REQUEST["ALLOW_TOPIC_TITLED"] == "Y" ? "Y" : "N"),
			"ALLOW_NL2BR" => ($_REQUEST["ALLOW_NL2BR"] == "Y" ? "Y" : "N"),
			"ALLOW_MOVE_TOPIC" => ($_REQUEST["ALLOW_MOVE_TOPIC"] == "Y" ? "Y" : "N"),
			"ALLOW_SIGNATURE" => ($_REQUEST["ALLOW_SIGNATURE"] == "Y" ? "Y" : "N")
		);
		
		$db_res = CSite::GetList($lby="sort", $lorder="asc");
		while ($res = $db_res->Fetch())
		{
			if ($_REQUEST["SITE"][$res["LID"]] == "Y")
			{
				$arFields["SITES"][$res["LID"]] = $_REQUEST["SITE_PATH"][$res["LID"]];
			}
		}
		if (CModule::IncludeModule("statistic"))
		{
			$arFields["EVENT1"] = $_REQUEST["EVENT1"];
			$arFields["EVENT2"] = $_REQUEST["EVENT2"];
			$arFields["EVENT3"] = $_REQUEST["EVENT3"];
		}
		if (!IsModuleInstalled("search"))
			unset($arFields["INDEXATION"]);
	
		$res = false;
		
		if ($ID > 0)
		{
			$res = CForumNew::Update($ID, $arFields, false);
		}
		else
		{
			$ID = CForumNew::Add($arFields);
		}
	}
	// Clear cache.
	$nameSpace = "bitrix";
	$arComponentPath = array(
		$nameSpace.":forum.index", 
		$nameSpace.":forum.rss",
		$nameSpace.":forum.search", 
		$nameSpace.":forum.statistic", 
		$nameSpace.":forum.topic.active", 
		$nameSpace.":forum.topic.move", 
		$nameSpace.":forum.topic.reviews", 
		$nameSpace.":forum.topic.search", 
		$nameSpace.":forum.user.list", 
		$nameSpace.":forum.user.post");
	foreach ($arComponentPath as $path)
	{
		$componentRelativePath = CComponentEngine::MakeComponentPath($path);
		$arComponentDescription = CComponentUtil::GetComponentDescr($path);
		if (strLen($componentRelativePath) <= 0 || !is_array($arComponentDescription))
			continue;
		elseif (!array_key_exists("CACHE_PATH", $arComponentDescription))
			continue;
		foreach ($arSites as $res)
		{
			$path = $componentRelativePath;
			if ($arComponentDescription["CACHE_PATH"] == "Y")
				$path = "/".$res["LID"].$path;
			if (!empty($path))
				BXClearCache(true, $path);
		}
	}
	
	if (!empty($arError) || $e = $APPLICATION->GetException())
	{
		$message = new CAdminMessage(($ID > 0 ? GetMessage("FE_ERROR_UPDATE") : GetMessage("FE_ERROR_ADD")), $e);
		$bVarsFromForm = true;
	}
	else
	{
		if (strLen($_REQUEST["apply"]) <= 0)
			LocalRedirect("forum_admin.php?lang=".LANG."&".GetFilterParams("filter_", false));
		else 
			LocalRedirect("forum_edit.php?lang=".LANG."&ID=".$ID);
	}
}
/********************************************************************
				/Action
********************************************************************/
$APPLICATION->SetTitle(($ID > 0 ? str_replace("#ID#", $ID, GetMessage("FE_PAGE_TITLE1")) : GetMessage("FE_PAGE_TITLE2")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/********************************************************************
				Data
********************************************************************/
$arForum = array(
	"NAME" => "",
	"DESCRIPTION" => "",
	"FORUM_GROUP_ID" => "",
	"GROUP_ID" => "", 

	"SITES" => array(),
	"PATH2FORUM_MESSAGE" => "/".SITE_DIR."/forum/message.php?FID=#FORUM_ID#&TID=#TOPIC_ID#&MID=#MESSAGE_ID#",
	
	"MODERATION" => "N",
	"ACTIVE" => "Y", 
	"INDEXATION" => "Y", 
	"DEDUPLICATION" => "Y", 

	"SORT" => 150, 
	"ORDER_BY" => "P",
	"ORDER_DIRECTION" => "DESC",
	
	"ASK_GUEST_EMAIL" => "N",
	"USE_CAPTCHA" => "Y",
	
	"ALLOW_HTML" => "N", 
	"ALLOW_ANCHOR" => "Y", 
	"ALLOW_BIU" => "Y",
	"ALLOW_IMG" => "Y",
	"ALLOW_VIDEO" => "Y",
	"ALLOW_LIST" => "Y",
	"ALLOW_QUOTE" => "Y",
	"ALLOW_CODE" => "Y",
	"ALLOW_TABLE" => "Y",
	"ALLOW_ALIGN" => "Y",
	"ALLOW_FONT" => "Y",
	"ALLOW_SMILES" => "Y",
	"ALLOW_UPLOAD" => "N",
	"ALLOW_TOPIC_TITLED" => "N",
	"ALLOW_NL2BR" => "N",
	"ALLOW_MOVE_TOPIC" => "N",
	"ALLOW_SIGNATURE" => "Y",
	
	"EVENT1" => "forum",
	"EVENT2" => "message",
	"EVENT3" => ""); 

if ($ID > 0)
{
	$db_res = CForumNew::GetList(array(), array("ID" => $ID));
	$arForum = $db_res->Fetch();
	$arForum["SITES"] = CForumNew::GetSites($ID);
	$arForum["GROUP_ID"] = CForumNew::GetAccessPermissions($ID, "TWO");
}
if ($bVarsFromForm)
{
	$arForum = $arFields;
}
if (!function_exists("__recursive_htmlspecialcharsbx"))
{
	function __recursive_htmlspecialcharsbx(&$res)
	{
		if (is_array($res))
		{
			foreach ($res as $key => $val)
				$res[$key] = __recursive_htmlspecialcharsbx($val);
		}
		elseif (is_string($res))
		{
			$res = htmlspecialcharsbx($res);
		}
		return $res;
	}
}
$res = $arForum;
foreach ($res as $key => $val)
{
	$arForum["~".$key] = $val;
	__recursive_htmlspecialcharsbx($arForum[$key]);
}

/********************************************************************
				/Data
********************************************************************/

/********************************************************************
				Show
********************************************************************/
$aMenu = array(
	array(
		"TEXT" => GetMessage("FEN_2FLIST"),
		"LINK" => "/bitrix/admin/forum_admin.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_list",
	)
);

if ($ID > 0 && $forumPermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("FEN_NEW_FORUM"),
		"LINK" => "/bitrix/admin/forum_edit.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_new",
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("FEN_DELETE_FORUM"), 
		"LINK" => "javascript:if(confirm('".GetMessage("FEN_DELETE_FORUM_CONFIRM")."')) window.location='/bitrix/admin/forum_admin.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON" => "btn_delete",
	);
}

$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("FEN_TAB_FORUM"), "ICON" => "forum", "TITLE" => GetMessage("FEN_TAB_FORUM_DESCR")),
		array("DIV" => "edit2", "TAB" => GetMessage("FEN_TAB_SETTINGS"), "ICON" => "forum", "TITLE" => GetMessage("FEN_TAB_SETTINGS_DESCR")),
		array("DIV" => "edit3", "TAB" => GetMessage("FEN_TAB_ACCESS"), "ICON"=>"forum", "TITLE" => GetMessage("FEN_TAB_ACCESS_DESCR")));

$context = new CAdminContextMenu($aMenu);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$context->Show();
if ($message)
	echo $message->Show();
?>
<form method="POST" action="<?=$APPLICATION->GetCurPageParam()?>?" name="forum_edit">
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="ID" value="<?=$ID?>">
	<?=bitrix_sessid_post()?>
<?

$tabControl->Begin();
$tabControl->BeginNextTab();

?>
	<tr>
		<td width="40%"><?=GetMessage("ACTIVE")?><?
			if(IsModuleInstalled("search"))
			{
				?><span class="required"><sup>1</sup></span><?
			}
			?>:</td>
		<td width="60%">
			<input type="checkbox" name="ACTIVE" id="ACTIVE" value="Y" <?=($arForum["ACTIVE"]=="Y" ? "checked='checked'" : "")?> />
			<label for="ACTIVE"><?=GetMessage("ACTIVE_TITLE")?></label>
		</td>
	</tr>
	<tr>
		<td><label for="FORUM_GROUP_ID"><?=GetMessage("FE_FORUM_GROUP")?>:</label></td>
		<td>
			<select name="FORUM_GROUP_ID" id="FORUM_GROUP_ID">
				<option value="">(<?=GetMessage("FE_NOT_SET")?>)</option>
				<?
				foreach ($arGroups as $res)
				{
					?><option value="<?=$res["ID"]?>" <?=($arForum["FORUM_GROUP_ID"] == $res["ID"] ? "selected='selected'" : "")?>><?
						?><?=str_pad("", ($res["DEPTH_LEVEL"] - 1), ".")?><?=$res["NAME"]?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SORT")?>:</td>
		<td>
			<input type="text" name="SORT" size="10" maxlength="10" value="<?=$arForum["SORT"]?>" />
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("NAME")?>:</td>
		<td>
			<input type="text" name="NAME" size="40" maxlength="255" value="<?=$arForum["NAME"]?>" />
		</td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("DESCR")?>:</td>
		<td valign="top">
			<textarea name="DESCRIPTION" rows="3" cols="40"><?=$arForum["DESCRIPTION"]; ?></textarea>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?=GetMessage("FE_SITES_PATHS")?><span class="required"><sup>1</sup></span><input type="hidden" name="SITES" /></td>
	</tr>
	<?
	foreach ($arSites as $key => $res)
	{
		?>
		<tr class="adm-detail-required-field">
			<td valign="top">
				<label for="SITE_<?=$res["LID"]?>_"><?=$res["NAME"]?> [<?=$res["LID"]?>]</label>
				<input type="checkbox" name="SITE[<?=$res["LID"]?>]" id="SITE_<?=$res["LID"]?>_" value="Y"<?if (array_key_exists($res["LID"], $arForum["SITES"]))echo " checked"?> OnClick="on_site_checkbox_click('<?=$res["LID"]?>', '<?=$res["DIR"]?>')">
			</td>
			<td valign="top">
				<textarea rows="2" cols="40" name="SITE_PATH[<?=$res["LID"]?>]" size="40"><?
					?><?if (array_key_exists($res["LID"], $arForum["SITES"])) echo $arForum["SITES"][$res["LID"]]?><?
				?></textarea>
			</td>
		</tr>
		<?
	}
	?>

<?
$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();
?>
<?
if (IsModuleInstalled("search"))
{
?>
	<tr>
		<td width="40%"><?=GetMessage("INDEX")?><span class="required"><sup>1</sup></span>:</td>
		<td width="60%">
			<input type="checkbox" name="INDEXATION" id="INDEXATION" value="Y" <?=($arForum["INDEXATION"]=="Y" ? "checked='checked'" : "")?> />
			<label for="INDEXATION"><?=GetMessage("INDEX_TITLE")?></label>
		</td>
	</tr>
<?
}
?>
	<tr>
		<td width="40%">
			<?=GetMessage("MODERATION")?>:
		</td>
		<td width="60%">
			<input type="checkbox" name="MODERATION" id="MODERATION" value="Y" <?=($arForum["MODERATION"]=="Y" ? "checked='checked'" : "")?> />
			<label for="MODERATION"><?=GetMessage("MODERATION_TITLE")?></label>
		</td>
	</tr>
	<tr>
		<td>
			<?=GetMessage("DEDUPLICATION")?>:
		</td>
		<td>
			<input type="checkbox" name="DEDUPLICATION" id="DEDUPLICATION" value="Y" <?=($arForum["DEDUPLICATION"]=="Y" ? "checked='checked'" : "")?> />
			<label for="DEDUPLICATION"><?=GetMessage("DEDUPLICATION_TITLE")?></label>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ORDER_BY")?>:</td>
		<td>
			<?=SelectBoxFromArray("ORDER_BY", $aSortTypes, $arForum["ORDER_BY"])?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ORDER_DIRECTION")?>:</td>
		<td>
			<?=SelectBoxFromArray("ORDER_DIRECTION", $aSortDirection, $arForum["ORDER_DIRECTION"])?>
		</td>
	</tr>
	<tr>
		<td>
			<?=GetMessage("ALLOW_TOPIC_TITLED")?>:
		</td>
		<td>
			<input type="checkbox" name="ALLOW_TOPIC_TITLED" id="ALLOW_TOPIC_TITLED" value="Y" <?=($arForum["ALLOW_TOPIC_TITLED"]=="Y" ? "checked='checked'" : "")?> />
			<label for="ALLOW_TOPIC_TITLED"><?=GetMessage("ALLOW_TOPIC_TITLED_TITLE")?></label>
		</td>
	</tr>
	<tr>
		<td>
			<?=GetMessage("ALLOW_SIGNATURE")?>:
		</td>
		<td>
			<input type="checkbox" name="ALLOW_SIGNATURE" id="ALLOW_SIGNATURE" value="Y" <?=($arForum["ALLOW_SIGNATURE"]=="Y" ? "checked='checked'" : "")?> />
			<label for="ALLOW_SIGNATURE"><?=GetMessage("ALLOW_SIGNATURE_TITLE")?></label>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ALLOW_UPLOAD")?>:</td>
		<td>
			<select name="ALLOW_UPLOAD">
				<option value="N" <?=(!in_array($arForum["ALLOW_UPLOAD"], array("Y", "F", "A")) ? "selected" : "")?>><?=GetMessage("FE_NOT")?></option>
				<option value="Y" <?if ($arForum["ALLOW_UPLOAD"]=="Y") echo " selected"?>><?=GetMessage("FE_IMAGEY")?></option>
				<option value="F" <?if ($arForum["ALLOW_UPLOAD"]=="F") echo " selected"?>><?=GetMessage("FE_FILEY")?></option>
				<option value="A" <?if ($arForum["ALLOW_UPLOAD"]=="A") echo " selected"?>><?=GetMessage("FE_ANY_FILEY")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("FE_ALLOWED_EXTS")?>:</td>
		<td>
			<input type="text" name="ALLOW_UPLOAD_EXT" size="40" maxlength="255" value="<?=$arForum["ALLOW_UPLOAD_EXT"] ?>">
		</td>
	</tr>
	
	<tr class="heading">
		<td colspan="2"><?=GetMessage("USER_SETTINGS")?></td>
	</tr>
	<tr>
		<td><?=GetMessage("GUEST_SETTINGS")?>:</td>
		<td>
			<input type="checkbox" name="ASK_GUEST_EMAIL" id="ASK_GUEST_EMAIL" value="Y" <?=
				($arForum["ASK_GUEST_EMAIL"]=="Y" ? "checked='checked'" : "")?> />
			<label for="ASK_GUEST_EMAIL"><?=GetMessage("ASK_GUEST_EMAIL")?></label><br />
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="USE_CAPTCHA" id="USE_CAPTCHA" value="Y" <?=($arForum["USE_CAPTCHA"]=="Y" ? "checked='checked'" : "")?> />
			<label for="USE_CAPTCHA"><?=GetMessage("FE_USE_CAPTCHA")?></label>
		</td>
	</tr>
	<?
	if (CModule::IncludeModule("statistic"))
	{
	?>
		<tr class="heading">
			<td colspan="2"><?=GetMessage("FORUM_EVENT_PARAMS")?></td>
		</tr>
		<tr>
			<td>event1:</td>
			<td><input type="text" name="EVENT1" maxlength="255" size="30" value="<?=$arForum["EVENT1"]?>"></td>
		</tr>
		<tr>
			<td>event2:</td>
			<td><input type="text" name="EVENT2" maxlength="255" size="30" value="<?=$arForum["EVENT2"]?>"><br><?=GetMessage("FORUM_EVENT12")?></td>
		</tr>
		<tr>
			<td>event3:</td>
			<td><input type="text" name="EVENT3" maxlength="255" size="30" value="<?=$arForum["EVENT3"]?>"><br><?=GetMessage("FORUM_EVENT3")?></td>
		</tr>
	<?
	}
	?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("PARSER_SETTINGS")?></td>
	</tr>
	<tr>
		<td><?=GetMessage("ALLOW_SMILES")?>:</td>
		<td>
			<input type="checkbox" name="ALLOW_SMILES" id="ALLOW_SMILES" value="Y" <?=($arForum["ALLOW_SMILES"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_SMILES"><?=GetMessage("ALLOW_SMILES_TITLE")?></label>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ALLOW_HTML")?>:</td>
		<td>
			<input type="checkbox" name="ALLOW_HTML" id="ALLOW_HTML" value="Y" <?=($arForum["ALLOW_HTML"]=="Y" ? "checked='checked'" : "")?> <?
				?>onclick="document.getElementById('forum_allow_nl2br').style.display = (this.checked ? '' : 'none');" />
			<label for="ALLOW_HTML"><?=GetMessage("ALLOW_HTML_TITLE")?></label>
		</td>
	</tr>
	<tr id="forum_allow_nl2br" style="<?=(($arForum["ALLOW_HTML"]=="Y") ? "" : "display:none;color:red;")?>">
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_NL2BR" id="ALLOW_NL2BR" value="Y" <?=($arForum["ALLOW_NL2BR"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_NL2BR"><?=GetMessage("ALLOW_NL2BR_TITLE")?></label>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("ADDITIONAL_SETTINGS")?>:</td>
		<td>
			<input type="checkbox" name="ALLOW_ANCHOR" id="ALLOW_ANCHOR" value="Y" <?=($arForum["ALLOW_ANCHOR"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_ANCHOR"><?=GetMessage("ALLOW_ANCHOR_TITLE")?> <small>(&lt;a&nbsp;href=...&gt;)</small></label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_BIU" id="ALLOW_BIU" value="Y" <?=($arForum["ALLOW_BIU"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_BIU"><?=GetMessage("ALLOW_BIU_TITLE")?> <small>(&lt;b&gt;&nbsp;&lt;u&gt;&nbsp;&lt;i&gt;&nbsp;&lt;s&gt;)</small></label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_IMG" id="ALLOW_IMG" value="Y" <?=($arForum["ALLOW_IMG"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_IMG"><?=GetMessage("ALLOW_IMG_TITLE")?> <small>(&lt;img&nbsp;src=...&gt;)</small></label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_VIDEO" id="ALLOW_VIDEO" value="Y" <?=($arForum["ALLOW_VIDEO"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_VIDEO"><?=GetMessage("ALLOW_VIDEO_TITLE")?></label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_LIST" id="ALLOW_LIST" value="Y" <?=($arForum["ALLOW_LIST"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_LIST"><?=GetMessage("ALLOW_LIST_TITLE")?> <small>(&lt;ul&gt;&lt;li&gt;)</small></label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_TABLE" id="ALLOW_TABLE" value="Y" <?=($arForum["ALLOW_TABLE"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_TABLE"><?=GetMessage("ALLOW_TABLE_TITLE")?> <small>(&lt;table&gt;)</small></label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_ALIGN" id="ALLOW_ALIGN" value="Y" <?=($arForum["ALLOW_ALIGN"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_ALIGN"><?=GetMessage("ALLOW_ALIGN_TITLE")?></label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_QUOTE" id="ALLOW_QUOTE" value="Y" <?=($arForum["ALLOW_QUOTE"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_QUOTE"><?=GetMessage("ALLOW_QUOTE_TITLE")?> <small>(&lt;quote&gt;)</small></label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_CODE" id="ALLOW_CODE" value="Y" <?=($arForum["ALLOW_CODE"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_CODE"><?=GetMessage("ALLOW_CODE_TITLE")?> <small>(&lt;code&gt;)</small></label>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="ALLOW_FONT" id="ALLOW_FONT" value="Y" <?=($arForum["ALLOW_FONT"]=="Y" ? "checked='checked'" : "")?>>
			<label for="ALLOW_FONT"><?=GetMessage("ALLOW_FONT_TITLE")?> <small>(&lt;font&nbsp;color=...&gt;)</small></label>
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();
?>

<?

	$arPerm = ($ID > 0 ? $arForum["GROUP_ID"] : array());
	
	$db_res = CGroup::GetList($by = "sort", $order = "asc", Array("ADMIN"=>"N"));
	while ($res = $db_res->GetNext())
	{
		$strSelected = $arForum["GROUP_ID"][$res["ID"]];
		$strSelected = (!in_array(strtoupper($strSelected), $aForumPermissions["reference_id"]) ? "A" : $strSelected);
		?>
		<tr>
			<td width="40%"><?=$res["NAME"]?>&nbsp;[<a  href="/bitrix/admin/group_edit.php?ID=<?=$res["ID"]?>&lang=<?=LANGUAGE_ID?>"><?=$res["ID"]?></a>]:</td>
			<td width="60%">
				<select name="GROUP[<?=$res["ID"]?>]">
				<?
				foreach ($aForumPermissions["reference_id"] as $fi => $val)
				{
					?><option value="<?=$aForumPermissions["reference_id"][$fi]?>"<?if ($strSelected == $aForumPermissions["reference_id"][$fi]) echo " selected"?>><?
						?><?=htmlspecialcharsbx($aForumPermissions["reference"][$fi])?></option><?
				}
				?>
				</select>
			</td>
		</tr>
		<?
	}
?>

<?
$tabControl->EndTab();
?>

<?
$editable = True;
if ($ID > 0 && !CForumNew::CanUserUpdateForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
{
	$editable = False;
}
elseif ($ID <= 0 && !CForumNew::CanUserAddForum($USER->GetUserGroupArray(), $USER->GetID()))
{
	$editable = False;
}

$tabControl->Buttons(
		array(
				"disabled" => (!$editable || $forumPermissions < "W"),
				"back_url" => "/bitrix/admin/forum_admin.php?lang=".LANG."&".GetFilterParams("filter_", false)
			)
	);
?>

<?
$tabControl->End();
$tabControl->ShowWarnings("forum_edit", $message);
?>

</form>
<script language="JavaScript">
<!--
function on_site_checkbox_click(lid, dir)
{
	siteCheck = document.forum_edit["SITE[" + lid + "]"];
	sitePath = document.forum_edit["SITE_PATH[" + lid + "]"];
	if (siteCheck.checked && sitePath.value.length <= 0)
	{
		var res = dir + "/forum/index.php?PAGE_NAME=message&FID=#FORUM_ID#&TID=#TOPIC_ID#&MID=#MESSAGE_ID#";
		sitePath.value = res.replace(/\/\//g, "/");
	}
}
//-->
</script>
<?=BeginNote()?>
<span class="required"><sup>1</sup></span> -
<?
if(IsModuleInstalled("search"))
{
	?><?=GetMessage("REQUIRE_REINDEX",array("#LINK#" => "/bitrix/admin/search_reindex.php"))?> <?
}
$res = CForumNew::PreparePath2Message(null);
?>
<?=GetMessage("FE_SAMPLE_SITEPATH")?>: /forum/index.php?PAGE_NAME=message&FID=#FORUM_ID#&TID=#TOPIC_ID#&MID=#MESSAGE_ID#
<br /><?=implode(', ', $res)?><br />
<?=EndNote(); ?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>