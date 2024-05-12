<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\Bitrix\Main\Loader::includeModule("forum");

$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
if ($forumModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$sTableID = "tbl_forum_points";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

/*******************************************************************/
if ($lAdmin->EditAction() && $forumModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();

		if (!CForumPoints::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("FPAN_UPDATE_ERROR"), $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

/*******************************************************************/
if (($arID = $lAdmin->GroupAction()) && $forumModulePermissions >= "W")
{
	if (isset($_REQUEST['action_target']) && $_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CForumPoints::GetList(
			array($by => $order),
			$arFilter
		);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":

				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CForumPoints::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("FORUM_P_ERROR_DEL"), $ID);
				}
				else
				{
					$DB->Commit();
				}

				break;
		}
	}
}

$dbResultList = CForumPoints::GetList(
	array($by => $order),
	$arFilter
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

/*******************************************************************/
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("FORUM_P_RANKS")));

/*******************************************************************/
$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('FORUM_P_NAME'),"sort"=>"", "default"=>true)
);
if (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y")
{
	$arHeaders[] = array("id"=>"MIN_POINTS","content"=>GetMessage("FORUM_P_MIN_POINTS"), "sort"=>"MIN_POINTS", "default"=>true, "align"=>"right");
	$arHeaders[] = array("id"=>"VOTES", "content"=>GetMessage("FORUM_P_VOTES"), "sort"=>"VOTES", "default"=>true, "align"=>"right");
}
else
{
	$sRatingWeightType = COption::GetOptionString("main", "rating_weight_type", "auto");
	$arHeaders[] = array("id"=>"MIN_POINTS", "content"=>$sRatingWeightType=='auto'? GetMessage("FORUM_P_RATING_VOTES"): GetMessage("FORUM_P_RATING_VALUE"), "sort"=>"MIN_POINTS", "default"=>true, "align"=>"right");
}
$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

/*******************************************************************/
while ($arForum = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arForum);

	$row->AddField("ID", $f_ID);
	$row->AddInputField("MIN_POINTS", array("size" => "4"));

	if (in_array("NAME", $arVisibleColumns))
	{
		$arPointsLang = CForumPoints::GetLangByID($f_ID, LANG);
		$fieldShow = htmlspecialcharsbx($arPointsLang["NAME"]);
		$row->AddViewField("NAME", '<a title="'.GetMessage("FORUM_P_EDIT_DESC").'" href="'."forum_points_edit.php?ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").'">'.$fieldShow.'</a>');
	}

	$row->AddInputField("VOTES", array("size" => "4"));

	$arActions = Array();
	if ($forumModulePermissions >= "R")
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("FORUM_P_EDIT_DESC"), "ACTION"=>$lAdmin->ActionRedirect("forum_points_edit.php?ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_", false).""), "DEFAULT"=>true);
	}
	if ($forumModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("FORUM_P_DELETE_DESC"), "ACTION"=>"if(confirm('".GetMessage('FORUM_P_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

/*******************************************************************/
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

/*******************************************************************/
$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);

if ($forumModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("FPAN_ADD_NEW"),
			"LINK" => "forum_points_edit.php?lang=".LANG,
			"TITLE" => GetMessage("FPAN_ADD_NEW_ALT"),
			"ICON" => "btn_new",
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

/*******************************************************************/
$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("FORUM_P_POINTS"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
