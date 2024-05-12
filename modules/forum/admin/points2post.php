<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\Bitrix\Main\Loader::includeModule("forum");

$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
if ($forumModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$sTableID = "tbl_forum_points2post";

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

		if (!CForumPoints2Post::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("FP2PAN_UPDATE_ERROR"), $ID);

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
		$dbResultList = CForumPoints2Post::GetList(
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

				if (!CForumPoints2Post::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("FORUM_PP_ERROR_DELETE"), $ID);
				}
				else
				{
					$DB->Commit();
				}

				break;
		}
	}
}

$dbResultList = CForumPoints2Post::GetList(
	array($by => $order),
	$arFilter
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

/*******************************************************************/
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("FORUM_PP_POINTS_PER_MES")));

/*******************************************************************/
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"MIN_NUM_POSTS","content"=>GetMessage("FORUM_PP_MIN_MES"), "sort"=>"MIN_NUM_POSTS", "default"=>true, "align"=>"right"),
	array("id"=>"POINTS_PER_POST", "content"=>GetMessage('FORUM_PP_POINTS'), "sort"=>"POINTS_PER_POST", "default"=>true, "align"=>"right"),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

/*******************************************************************/
while ($arForum = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arForum);

	$row->AddField("ID", $f_ID);
	$row->AddInputField("MIN_NUM_POSTS", array("size" => "10"));
	$row->AddInputField("POINTS_PER_POST", array("size" => "10"));

	$arActions = Array();
	if ($forumModulePermissions >= "R")
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("FORUM_PP_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("forum_points2post_edit.php?ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_", false).""), "DEFAULT"=>true);
	}
	if ($forumModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("FORUM_PP_DEL_DESCR"), "ACTION"=>"if(confirm('".GetMessage('FORUM_PP_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
			"TEXT" => GetMessage("FP2PAN_ADD_NEW"),
			"LINK" => "forum_points2post_edit.php?lang=".LANG,
			"TITLE" => GetMessage("FP2PAN_ADD_NEW_ALT"),
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
$APPLICATION->SetTitle(GetMessage("FORUM_PP_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
