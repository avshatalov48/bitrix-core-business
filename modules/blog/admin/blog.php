<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/include.php");

$blogModulePermissions = $APPLICATION->GetGroupRight("blog");
if ($blogModulePermissions < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/prolog.php");

$sTableID = "tbl_blog_blog";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_name",
	"filter_active",
	"filter_url",
	"filter_group_id",
	//"filter_use_socnet",
	"filter_owner",
	"filter_id"
);
$USER_FIELD_MANAGER->AdminListAddFilterFields("BLOG_BLOG", $arFilterFields);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if (strlen($filter_name) > 0)
	$arFilter["~NAME"] = "%".$filter_name."%";
if (strlen($filter_active) > 0)
	$arFilter["ACTIVE"] = $filter_active;
if (strlen($filter_url) > 0)
	$arFilter["URL"] = $filter_url;
if (is_array($filter_group_id))
	$arFilter["GROUP_ID"] = $filter_group_id;
else
	$filter_group_id = array();
if (strlen($filter_owner) > 0)
	$arFilter["%OWNER"] = $filter_owner;
if (strlen($filter_id) > 0)
	$arFilter["ID"] = $filter_id;

$USER_FIELD_MANAGER->AdminListAddFilter("BLOG_BLOG", $arFilter);

if (($arID = $lAdmin->GroupAction()) && $blogModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CBlog::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				$dbBlog = CBlog::GetList(
						array(),
						array("ID" => $ID),
						false,
						false,
						array("ID", "GROUP_SITE_ID", "GROUP_ID")
					);
				$arBlogOld = $dbBlog->Fetch();

				$DB->StartTransaction();

				if (!CBlog::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("BLB_DELETE_ERROR"), $ID);
				}

				$DB->Commit();

				if (!empty($arBlogOld))
				{
					BXClearCache(True, "/".$arBlogOld["GROUP_SITE_ID"]."/blog/");
					BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
					BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
					BXClearCache(True, "/".SITE_ID."/blog/popular_blogs/");
				}

				break;
		}
	}
}

$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("BLB_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"DATE_CREATE", "content"=>GetMessage('BLB_DATE_CREATE'), "sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"DATE_UPDATE", "content"=>GetMessage('BLB_DATE_UPDATE'), "sort"=>"DATE_UPDATE", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage('BLB_ACTIVE'), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"OWNER_INFO", "content"=>GetMessage('BLB_OWNER_ID'), "sort"=>"", "default"=>true),
	array("id"=>"URL", "content"=>GetMessage('BLB_URL'), "sort"=>"URL", "default"=>true),
	array("id"=>"GROUP_ID", "content"=>GetMessage('BLB_GROUP_ID'), "sort"=>"GROUP_ID", "default"=>true),
	array("id"=>"USE_SOCNET", "content"=>GetMessage('BLB_USE_SOCNET'), "sort"=>"USE_SOCNET", "default"=>false),
);
$USER_FIELD_MANAGER->AdminListAddHeaders("BLOG_BLOG", $arHeaders);
$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSelectedFields = array("ID", "NAME", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "OWNER_ID", "URL", "REAL_URL", "GROUP_ID", "OWNER_LOGIN", "OWNER_NAME", "OWNER_LAST_NAME", "OWNER_EMAIL", "GROUP_NAME", "GROUP_SITE_ID", "USE_SOCNET");

foreach($arVisibleColumns as $val)
	if(!in_array($val, $arSelectedFields))
		$arSelectedFields[] = $val;

$dbResultList = CBlog::GetList(
	array($by => $order),
	$arFilter,
	false,
	array("nPageSize"=>CAdminResult::GetNavSize($sTableID)),
	$arSelectedFields
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("BLB_GROUP_NAV")));

while ($arBlog = $dbResultList->NavNext(true, "f_"))
{      
	$row =& $lAdmin->AddRow($f_ID, $arBlog, "/bitrix/admin/blog_blog_edit.php?ID=".$f_ID."&lang=".LANGUAGE_ID, GetMessage("BLB_UPDATE_ALT"));

	$row->AddField("ID", '<a href="/bitrix/admin/blog_blog_edit.php?ID='.$f_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage("BLB_UPDATE_ALT").'">'.$f_ID.'</a>');
	$row->AddField("NAME", "<a href=\"".CBlog::PreparePath($f_URL, $f_GROUP_SITE_ID, false, $f_OWNER_ID, $f_SOCNET_GROUP_ID)."\">".$f_NAME."</a>");
	$row->AddField("DATE_CREATE", $f_DATE_CREATE);
	$row->AddField("DATE_UPDATE", $f_DATE_UPDATE);
	$row->AddField("ACTIVE", (($f_ACTIVE == "Y") ? GetMessage("BLB_YES") : GetMessage("BLB_NO")));
	if(IntVal($f_OWNER_ID) > 0)
		$row->AddField("OWNER_INFO", "<a href=\"/bitrix/admin/user_edit.php?ID=".$f_OWNER_ID."&lang=".LANG."\">[".$f_OWNER_ID."] ".$f_OWNER_NAME." ".$f_OWNER_LAST_NAME." (".$f_OWNER_LOGIN.")</a>");
	if(IntVal($f_SOCNET_GROUP_ID) > 0)
	{
		$row->AddField("SOCNET_GROUP_ID", $f_SOCNET_GROUP_ID);	
		if(CModule::IncludeModule("socialnetwork"))
		{
			$arGroupSo = CSocNetGroup::GetByID($f_SOCNET_GROUP_ID);
			if(!empty($arGroupSo))
			{
				$row->AddField("SOCNET_GROUP_ID", "[".$f_SOCNET_GROUP_ID."] ".$arGroupSo["NAME"]);
			}
		}
		
	}
	$row->AddField("URL", $f_URL);
	$row->AddField("GROUP_ID", "<a href=\"/bitrix/admin/blog_group_edit.php?ID=".$f_GROUP_ID."&lang=".LANG."\">[".$f_GROUP_SITE_ID."] ".$f_GROUP_NAME."</a>");
	$row->AddField("USE_SOCNET", (($f_USE_SOCNET == "Y") ? GetMessage("BLB_YES") : GetMessage("BLB_NO")));
	
	$USER_FIELD_MANAGER->AddUserFields("BLOG_BLOG", $arBlog, $row);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("BLB_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("blog_blog_edit.php?ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($blogModulePermissions >= "U")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("BLB_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('BLB_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

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
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);

if ($blogModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("BLB_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "blog_blog_edit.php?lang=".LANG,
			"TITLE" => GetMessage("BLB_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

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
		GetMessage("BLB_FILTER_ACTIVE"),
		GetMessage("BLB_FILTER_URL"),
		GetMessage("BLB_FILTER_GROUP_ID"),
		GetMessage("BLB_FILTER_OWNER"),
		"ID"
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("BLB_FILTER_NAME")?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsbx($filter_name)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_FILTER_ACTIVE")?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?echo GetMessage("BLB_F_ALL")?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?echo GetMessage("BLB_YES")?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?echo GetMessage("BLB_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_FILTER_URL")?>:</td>
		<td><input type="text" name="filter_url" value="<?echo htmlspecialcharsbx($filter_url)?>" size="40"></td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("BLB_FILTER_GROUP_ID");?>:</td>
		<td>
			<select name="filter_group_id[]" multiple size="5">
				<?
				
				$dbGroup = CBlogGroup::GetList(array("NAME" => "ASC"), array());
				while ($arGroup = $dbGroup->GetNext())
				{
					?><option value="<?= $arGroup["ID"] ?>"<?if (in_array($arGroup["ID"], $filter_group_id)) echo " selected"?>>[<?= $arGroup["ID"] ?>] <?= $arGroup["NAME"] ?> (<?= $arGroup["SITE_ID"] ?>)</option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLB_FILTER_OWNER")?>:</td>
		<td><input type="text" name="filter_owner" value="<?echo htmlspecialcharsbx($filter_owner)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td>ID:</td>
		<td><input type="text" name="filter_id" value="<?echo htmlspecialcharsbx($filter_id)?>" size="40"></td>
	</tr>
<?
$USER_FIELD_MANAGER->AdminListShowFilter("BLOG_BLOG");

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