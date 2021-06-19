<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/include.php");

$blogModulePermissions = $APPLICATION->GetGroupRight("blog");
if ($blogModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/prolog.php");

$sTableID = "tbl_blog_comment";

$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_id",
	"filter_date_create_to",
	"filter_date_create_from",
	"filter_author_id",
	"filter_author_name",
	"filter_author_email",
	"filter_post_text",
	"filter_author_ip",
	"filter_author_ip1",
	"filter_publish_status",
	"filter_blog_id",
	"filter_post_id",
	"filter_owner_id",
	"filter_socnet_group_id",
	"filter_blog_active",
	"filter_blog_group_id",
	"filter_blog_group_site_id",
	"filter_author_anonym",
);
$USER_FIELD_MANAGER->AdminListAddFilterFields("BLOG_COMMENT", $arFilterFields);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if ($filter_post_text <> '')
	$arFilter["~POST_TEXT"] = "%".$filter_post_text."%";
if ($filter_author_name <> '')
	$arFilter["~AUTHOR_NAME"] = "%".$filter_author_name."%";
if ($filter_author_email <> '')
	$arFilter["~AUTHOR_EMAIL"] = "%".$filter_author_email."%";
if ($filter_date_create_from <> '') $arFilter[">=DATE_CREATE"] = Trim($filter_date_create_from);
if ($filter_date_create_to <> '')
{
	if ($arDate = ParseDateTime($filter_date_create_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (mb_strlen($filter_date_create_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_create_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<DATE_CREATE"] = $filter_date_create_to;
	}
	else
	{
		$filter_date_create_to = "";
	}
}
if ($filter_author_anonym <> '')
{
	if($filter_author_anonym == "Y")
		$arFilter["AUTHOR_ID"] = false;
	else
		$arFilter["!AUTHOR_ID"] = false;
}


if (intval($filter_id) > 0)
	$arFilter["ID"] = intval($filter_id);
if (intval($filter_author_id) > 0)
	$arFilter["AUTHOR_ID"] = intval($filter_author_id);
if (intval($filter_blog_id) > 0)
	$arFilter["BLOG_ID"] = intval($filter_blog_id);
if (intval($filter_post_id) > 0)
	$arFilter["POST_ID"] = intval($filter_post_id);
if (intval($filter_owner_id) > 0)
	$arFilter["BLOG_OWNER_ID"] = intval($filter_owner_id);
if (intval($filter_socnet_group_id) > 0)
	$arFilter["BLOG_SOCNET_GROUP_ID"] = intval($filter_socnet_group_id);
if (intval($filter_blog_group_id) > 0)
	$arFilter["BLOG_GROUP_ID"] = intval($filter_blog_group_id);
		
if ($filter_author_ip <> '')
	$arFilter["AUTHOR_IP"] = $filter_author_ip;
if ($filter_author_ip1 <> '')
	$arFilter["AUTHOR_IP1"] = $filter_author_ip1;
if ($filter_publish_status <> '')
	$arFilter["PUBLISH_STATUS"] = $filter_publish_status;
if ($filter_blog_active <> '')
	$arFilter["BLOG_ACTIVE"] = $filter_blog_active;
if ($filter_blog_group_site_id <> '')
	$arFilter["BLOG_GROUP_SITE_ID"] = $filter_blog_group_site_id;

if (is_array($filter_blog_group_id) && count($filter_blog_group_id) > 0)
	$arFilter["BLOG_GROUP_ID"] = $filter_blog_group_id;
else
	$filter_blog_group_id = array();

$USER_FIELD_MANAGER->AdminListAddFilter("BLOG_COMMENT", $arFilter);

$arCacheInfo = Array();
if (($arID = $lAdmin->GroupAction()) && $blogModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CBlogComment::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
		while ($arResult = $dbResultList->Fetch())
		{
			$arID[] = $arResult['ID'];
		}
	}
	if(!empty($arID))
	{
		$dbResultList = CBlogComment::GetList(
			array($by => $order),
			Array("ID" => $arID),
			false,
			false,
			array("ID", "POST_ID", "BLOG_URL", "BLOG_GROUP_SITE_ID")
		);
		while ($arResult = $dbResultList->Fetch())
		{
			if(empty($arCacheInfo[$arResult["BLOG_GROUP_SITE_ID"]]))
				$arCacheInfo[$arResult["BLOG_GROUP_SITE_ID"]] = Array();
			if(empty($arCacheInfo[$arResult["BLOG_GROUP_SITE_ID"]][$arResult["BLOG_URL"]]))
				$arCacheInfo[$arResult["BLOG_GROUP_SITE_ID"]][$arResult["BLOG_URL"]] = Array();
			if(!in_array($arResult["POST_ID"], $arCacheInfo[$arResult["BLOG_GROUP_SITE_ID"]][$arResult["BLOG_URL"]]))
				$arCacheInfo[$arResult["BLOG_GROUP_SITE_ID"]][$arResult["BLOG_URL"]][] = $arResult["POST_ID"];
		}
	}


	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;
		switch ($_REQUEST['action'])
		{
			case "delete":
				if (!CBlogComment::Delete($ID))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("BLB_DELETE_ERROR"), $ID);
				}
				break;
			case "hide":
				if (!CBlogComment::Update($ID, Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("BLB_HIDE_ERROR"), $ID);
				}
				break;
			case "show":
				if (!CBlogComment::Update($ID, Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH)))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("BLB_SHOW_ERROR"), $ID);
				}
				break;
		}
	}

	if(!empty($arCacheInfo))
	{
		foreach($arCacheInfo as $site => $arB)
		{
			foreach($arB as $blogUrl => $v)
			{
				foreach($v as $postID)
				{
					BXClearCache(True, "/".$site."/blog/".$blogUrl."/first_page/");
					BXClearCache(True, "/".$site."/blog/".$blogUrl."/comment/".$postID."/");
					BXClearCache(True, "/".$site."/blog/".$blogUrl."/rss_out/".$postID."/C/");
					BXClearCache(True, "/blog/comment/".intval($postID / 100)."/".$postID."/");
				}
			}
			BXClearCache(True, "/".$site."/blog/last_comments/");
			BXClearCache(True, "/".$site."/blog/last_messages/");
			BXClearCache(True, "/".$site."/blog/commented_posts/");
			BXClearCache(True, "/".$site."/blog/popular_posts/");
		}
	}
}

$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"PUBLISH_STATUS", "content"=> GetMessage("BLB_PUBLISH_STATUS"), "sort"=>"PUBLISH_STATUS", "default"=>true),
	array("id"=>"DATE_CREATE", "content"=>GetMessage('BLB_DATE_CREATE'), "sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"AUTHOR_ID", "content"=>GetMessage('BLB_AUTHOR_ID'), "sort"=>"", "default"=>true, "sort" => "AUTHOR_ID"),
	array("id"=>"POST_TEXT", "content"=> GetMessage("BLB_COMMENT"), "sort"=>"POST_TEXT", "default"=>true),
	array("id"=>"POST_TITLE", "content"=> GetMessage("BLB_POST_ID"), "sort"=>"POST_TITLE", "default"=>true),
	array("id"=>"BLOG_ID", "content"=> GetMessage("BLB_BLOG_ID"), "sort"=>"BLOG_ID", "default"=>true),
	array("id"=>"AUTHOR_IP", "content"=>"IP", "sort"=>"AUTHOR_IP", "default"=>true),
);
$USER_FIELD_MANAGER->AdminListAddHeaders("BLOG_COMMENT", $arHeaders);
$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSelectedFields = array("ID", "BLOG_ID", "POST_ID", "PARENT_ID", "AUTHOR_ID", "AUTHOR_NAME", "AUTHOR_EMAIL", "AUTHOR_IP", "AUTHOR_IP1", "DATE_CREATE", "POST_TEXT", "PUBLISH_STATUS", "PATH", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "BLOG_URL", "BLOG_OWNER_ID", "BLOG_SOCNET_GROUP_ID", "BLOG_ACTIVE", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "BLOG_NAME", "POST_TITLE");

foreach($arVisibleColumns as $val)
	if(!in_array($val, $arSelectedFields))
		$arSelectedFields[] = $val;

$dbResultList = CBlogComment::GetList(
	array($by => $order),
	$arFilter,
	false,
	array("nPageSize"=>CAdminResult::GetNavSize($sTableID)),
	$arSelectedFields
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("BLB_GROUP_NAV")));
$arServerName = Array();
$dbSite = CSite::GetList();
while($arSite = $dbSite->Fetch())
{
	$serverName = $arSite["SERVER_NAME"];
	if ($serverName == '')
	{
		if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
			$serverName = SITE_SERVER_NAME;
		else
			$serverName = COption::GetOptionString("main", "server_name", "");
		if ($serverName == '')
			$serverName = $_SERVER["SERVER_NAME"];
	}
	$serverName = \Bitrix\Main\Text\HtmlFilter::encode($serverName);
	$arServerName[$arSite["ID"]] = "http://".$serverName;
}

while ($arComment = $dbResultList->Fetch())
{
	$path = str_replace("#comment_id#", $arComment["ID"], $arComment["PATH"]);
	if (!preg_match("/^[a-z]+:\\/\\//", $path))
		$path = $arServerName[$arComment["BLOG_GROUP_SITE_ID"]].$path;

	$row =& $lAdmin->AddRow($arComment["ID"], $arComment, $path, GetMessage("BLB_VIEW_ALT"));

	$row->AddField("ID", '<a href="'.$path.'" title="'.GetMessage("BLB_VIEW_ALT").'">'.$arComment["ID"].'</a>');
	
	$row->AddField("DATE_CREATE", $arComment["DATE_CREATE"]);
	$row->AddField("POST_TEXT", "<a href=\"".$path."\" title=\"".htmlspecialcharsEx($arComment["POST_TEXT"])."\">".htmlspecialcharsEx(TruncateText($arComment["POST_TEXT"], 150))."</a>");
	$row->AddField("POST_TITLE", "<span title=\"".htmlspecialcharsEx($arComment["POST_TITLE"])."\">".htmlspecialcharsEx(TruncateText($arComment["POST_TITLE"], 50))."</span>");
	
	$row->AddField("PUBLISH_STATUS", (($arComment["PUBLISH_STATUS"] == "P") ? GetMessage("BLB_YES") : GetMessage("BLB_NO")));
	if(intval($arComment["AUTHOR_ID"]) > 0)
		$row->AddField("AUTHOR_ID", "[<a href=\"/bitrix/admin/user_edit.php?ID=".$arComment["AUTHOR_ID"]."&lang=".LANG."\">".$arComment["AUTHOR_ID"]."</a>] ".htmlspecialcharsEx("(".$arComment["USER_LOGIN"].") ".$arComment["USER_NAME"]." ".$arComment["USER_LAST_NAME"]));
	elseif($arComment["AUTHOR_NAME"] <> '')
		$row->AddField("AUTHOR_ID", htmlspecialcharsEx($arComment["AUTHOR_NAME"]." (".$arComment["AUTHOR_EMAIL"].")"));
	if(intval($arComment["BLOG_ID"]) > 0)
		$row->AddField("BLOG_ID", "[<a href=\"/bitrix/admin/blog_blog_edit.php?ID=".$arComment["BLOG_ID"]."&lang=".LANG."\">".$arComment["BLOG_ID"]."</a>] ".htmlspecialcharsEx($arComment["BLOG_NAME"].""));
	if($arComment["AUTHOR_IP"] <> '')
	{
		$ip = GetWhoisLink($arComment["AUTHOR_IP"]).($arComment["AUTHOR_IP1"] <> '' ? " / ".GetWhoisLink($arComment["AUTHOR_IP1"]) : "");
		if(CModule::IncludeModule("statistic"))
		{
			$arr = explode(".", $arComment["AUTHOR_IP"]);
			if(count($arr)==4)
			{
				$ip .= '<br><a href="stoplist_edit.php?lang='.LANGUAGE_ID.'&amp;net1='.intval($arr[0]).'&amp;net2='.intval($arr[1]).'&amp;net3='.intval($arr[2]).'&amp;net4='.intval($arr[3]).'">['.GetMessage("BLB_STOP_LIST").']<a>';
			}
		}
		$row->AddField("AUTHOR_IP", $ip);

	}
	$USER_FIELD_MANAGER->AddUserFields("BLOG_COMMENT", $arComment, $row);
	
	$arActions = Array();

	if($arComment["PUBLISH_STATUS"] == "P")
		$arActions[] = array("ICON"=>"hide", "TEXT"=>GetMessage("BLB_HIDE_ALT"), "ACTION" => $lAdmin->ActionDoGroup($arComment["ID"], "hide"));
	else
		$arActions[] = array("ICON"=>"show", "TEXT"=>GetMessage("BLB_SHOW_ALT"), "ACTION" => $lAdmin->ActionDoGroup($arComment["ID"], "show"));
	$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("BLB_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('BLB_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($arComment["ID"], "delete"));

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"hide" => GetMessage("BLB_HIDE_ALT"),
		"show" => GetMessage("BLB_SHOW_ALT"),
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);
$lAdmin->AddAdminContextMenu(Array());
$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("BLB_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("BLB_DATE_CREATE"),
		GetMessage("BLB_AUTHOR_ID"),
		GetMessage("BLB_AUTHOR_NAME"),
		GetMessage("BLB_AUTHOR_EMAIL"),
		GetMessage("BLB_AUTHOR_ANONYM"),
		GetMessage("BLB_POST_TEXT"),
		GetMessage("BLB_AUTHOR_IP"),
		GetMessage("BLB_AUTHOR_IP1"),
		GetMessage("BLB_PUBLISH_STATUS"), 
		GetMessage("BLB_BLOG_ID"),
		GetMessage("BLB_POST_ID"),
		GetMessage("BLB_BLOG_OWNER_ID"),
		GetMessage("BLB_BLOG_SOCNET_GROUP_ID"),
		GetMessage("BLB_BLOG_ACTIVE"),
		GetMessage("BLB_BLOG_GROUP_ID"),
		GetMessage("BLB_BLOG_GROUP_SITE_ID"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td>ID:</td>
		<td><input type="text" name="filter_id" value="<?echo htmlspecialcharsbx($filter_id)?>" size="20"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_DATE_CREATE");?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_create_from", $filter_date_create_from, "filter_date_create_to", $filter_date_create_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_AUTHOR_ID");?>:</td>
		<td>
			<?echo FindUserID("filter_author_id", $filter_author_id, "", "find_form");?>
		</td>
	</tr>	
	<tr>
		<td><?echo GetMessage("BLB_AUTHOR_NAME")?>:</td>
		<td><input type="text" name="filter_author_name" value="<?echo htmlspecialcharsbx($filter_author_name)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>	
	<tr>
		<td><?echo GetMessage("BLB_AUTHOR_EMAIL")?>:</td>
		<td><input type="text" name="filter_author_email" value="<?echo htmlspecialcharsbx($filter_author_email)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>	
	<tr>
		<td><?echo GetMessage("BLB_AUTHOR_ANONYM")?>:</td>
		<td>
			<select name="filter_author_anonym">
				<option value=""><?echo GetMessage("BLB_F_ALL")?></option>
				<option value="Y"<?if ($filter_author_anonym=="Y") echo " selected"?>><?echo GetMessage("BLB_YES")?></option>
				<option value="N"<?if ($filter_author_anonym=="N") echo " selected"?>><?echo GetMessage("BLB_NO")?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("BLB_POST_TEXT")?>:</td>
		<td><input type="text" name="filter_post_text" value="<?echo htmlspecialcharsbx($filter_post_text)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>	
	<tr>
		<td><?echo GetMessage("BLB_AUTHOR_IP")?>:</td>
		<td><input type="text" name="filter_author_ip" value="<?echo htmlspecialcharsbx($filter_author_ip)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_AUTHOR_IP1")?>:</td>
		<td><input type="text" name="filter_author_ip1" value="<?echo htmlspecialcharsbx($filter_author_ip1)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_PUBLISH_STATUS")?>:</td>
		<td>
			<select name="filter_publish_status">
				<option value=""><?echo GetMessage("BLB_F_ALL")?></option>
				<option value="P"<?if ($filter_publish_status=="P") echo " selected"?>><?echo GetMessage("BLB_YES")?></option>
				<option value="K"<?if ($filter_publish_status=="K") echo " selected"?>><?echo GetMessage("BLB_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_BLOG_ID")?>:</td>
		<td><input type="text" name="filter_blog_id" value="<?echo htmlspecialcharsbx($filter_blog_id)?>" size="20"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_POST_ID")?>:</td>
		<td><input type="text" name="filter_post_id" value="<?echo htmlspecialcharsbx($filter_post_id)?>" size="20"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_BLOG_OWNER_ID");?>:</td>
		<td>
			<?echo FindUserID("filter_owner_id", $filter_owner_id, "", "find_form");?>
		</td>
	</tr>	
	<tr>
		<td><?echo GetMessage("BLB_BLOG_SOCNET_GROUP_ID")?>:</td>
		<td><input type="text" name="filter_socnet_group_id" value="<?echo htmlspecialcharsbx($filter_socnet_group_id)?>" size="20"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_BLOG_ACTIVE")?>:</td>
		<td>
			<select name="filter_blog_active">
				<option value=""><?echo GetMessage("BLB_F_ALL")?></option>
				<option value="Y"<?if ($filter_blog_active=="Y") echo " selected"?>><?echo GetMessage("BLB_YES")?></option>
				<option value="N"<?if ($filter_blog_active=="N") echo " selected"?>><?echo GetMessage("BLB_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("BLB_BLOG_GROUP_ID");?>:</td>
		<td>
			<select name="filter_blog_group_id[]" multiple size="5">
				<option value=""><?echo GetMessage("BLB_F_ALL")?></option>
				<?
				$dbGroup = CBlogGroup::GetList(array("NAME" => "ASC"), array());
				while ($arGroup = $dbGroup->GetNext())
				{
					?><option value="<?= $arGroup["ID"] ?>"<?if (in_array($arGroup["ID"], $filter_blog_group_id)) echo " selected"?>>[<?= $arGroup["ID"] ?>] <?= $arGroup["NAME"] ?> (<?= $arGroup["SITE_ID"] ?>)</option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_BLOG_GROUP_SITE_ID");?>:</td>
		<td>
			<select name="filter_blog_group_site_id">
				<option value=""><?= GetMessage("BLB_F_ALL") ?></option>
				<?
				$dbSitesList = CLang::GetList();
				while ($arSitesList = $dbSitesList->GetNext())
				{
					?><option value="<?= ($arSitesList["LID"])?>"<?if ($arSitesList["LID"] == $filter_blog_group_site_id) echo " selected";?>>[<?= ($arSitesList["LID"]) ?>]&nbsp;<?= ($arSitesList["NAME"]) ?></option><?
				}
				?>
			</select>
		</td>
	</tr>

<?
$USER_FIELD_MANAGER->AdminListShowFilter("BLOG_COMMENT");

$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();
?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>