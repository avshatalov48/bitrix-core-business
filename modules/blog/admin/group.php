<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/include.php");

$blogModulePermissions = $APPLICATION->GetGroupRight("blog");
if ($blogModulePermissions < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/prolog.php");

$sTableID = "tbl_blog_group";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_site_id",
	"filter_name",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if (strlen($filter_site_id) > 0 && $filter_site_id != "NOT_REF")
	$arFilter["SITE_ID"] = $filter_site_id;
if (strlen($filter_name) > 0)
	$arFilter["~NAME"] = "%".$filter_name."%";

if ($lAdmin->EditAction() && $blogModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		$arBlogGroupTmp = CBlogGroup::GetByID($ID);
		if (!CBlogGroup::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("BLG_ERROR_UPDATE"), $ID);

			$DB->Rollback();
		}

		BXClearCache(True, "/".$arFields["SITE_ID"]."/blog/");
		BXClearCache(True, "/".$arBlogGroupTmp["SITE_ID"]."/blog/");

		$DB->Commit();
	}
}

if (($arID = $lAdmin->GroupAction()) && $blogModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CBlogGroup::GetList(
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

				$DB->StartTransaction();

				$arBlogGroupTmp = CBlogGroup::GetByID($ID);
				if (!CBlogGroup::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("BLG_DELETE_ERROR"), $ID);
				}

				BXClearCache(True, "/".$arBlogGroupTmp["SITE_ID"]."/blog/");

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = CBlogGroup::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "SITE_ID", "NAME")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("BLG_GROUP_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("BLG_GROUP_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"SITE_ID", "content"=>GetMessage('BLG_GROUP_SITE_ID'), "sort"=>"SITE_ID", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSites = array();
$dbSitesList = CSite::GetList(($b = "sort"), ($o = "asc"));
while ($arSite = $dbSitesList->Fetch())
	$arSites[$arSite["LID"]] = "[".$arSite["LID"]."]&nbsp;".$arSite["NAME"];

while ($arGroup = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arGroup);

	$row->AddField("ID", '<a href="/bitrix/admin/blog_group_edit.php?ID='.$f_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage("BLG_UPDATE_ALT").'">'.$f_ID.'</a>');
	$row->AddInputField("NAME", array("size" => "35"));
	$row->AddSelectField("SITE_ID", $arSites, array());

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("BLG_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("blog_group_edit.php?ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($blogModulePermissions >= "U")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("BLG_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('BLG_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
			"TEXT" => GetMessage("BLG_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "blog_group_edit.php?lang=".LANG,
			"TITLE" => GetMessage("BLG_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("BLG_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("BLG_GROUP_NAME"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("BLG_FILTER_SITE_ID")?>:</td>
		<td><?echo CLang::SelectBox("filter_site_id", $filter_site_id, GetMessage("BLG_SPT_ALL")) ?>
	</tr>
	<tr>
		<td><?echo GetMessage("BLG_GROUP_NAME")?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsbx($filter_name)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>

<?
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