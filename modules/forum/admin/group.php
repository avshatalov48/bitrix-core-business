<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\Bitrix\Main\Loader::includeModule("forum");
/**
 * @var $APPLICATION CMain
 */
$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
if ($forumModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$sTableID = "tbl_forum_group";

$oSort = new CAdminSorting($sTableID, "LEFT_MARGIN", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilter = array();

if ($forumModulePermissions >= "W")
{
	if ($lAdmin->EditAction())
	{
		foreach ($_POST["FIELDS"] as $ID => $arFields)
		{
			if ($ID > 0 && $lAdmin->IsUpdated($ID) &&
				CForumGroup::CanUserUpdateGroup($ID, $USER->GetUserGroupArray()) &&
				CForumGroup::Update($ID, $arFields))
				BXClearCache(true, "bitrix/forum/group/");
		}
	}
	else if (($arID = $lAdmin->GroupAction()))
	{
		if ($_REQUEST['action_target']=='selected')
		{
			$arID = array();
			$dbResultList = CForumGroup::GetList( array($by => $order), $arFilter );
			while ($arResult = $dbResultList->Fetch())
				$arID[] = $arResult['ID'];
		}

		foreach ($arID as $ID)
		{
			if ($ID > 0)
			{
				switch ($_REQUEST['action'])
				{
					case "delete":

						@set_time_limit(0);

						$DB->StartTransaction();

						if (!CForumGroup::Delete($ID))
						{
							$DB->Rollback();

							if ($ex = $APPLICATION->GetException())
								$lAdmin->AddGroupError($ex->GetString(), $ID);
							else
								$lAdmin->AddGroupError(GetMessage("ERROR_DEL_GROUP"), $ID);
						}

						$DB->Commit();

						break;
				}
			}
		}
		BXClearCache(true, "/".LANG."/forum/group/");
	}
}

$dbResultList = new CAdminResult(CForumGroup::GetList(array($by => $order), $arFilter), $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("GROUP_NAV")));
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("GROUP_ID"), "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('FORUM_NAME'), "sort"=>"LEFT_MARGIN", "default"=>true),
	array("id"=>"SORT","content"=>GetMessage("GROUP_SORT"), "sort"=>"SORT", "default"=>true, "align"=>"right"),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

/*******************************************************************/
while ($group = $dbResultList->NavNext())
{
	$row =& $lAdmin->AddRow($group["ID"], $group);

	$row->AddField("ID", $group["ID"]);
	$row->AddInputField("SORT", array("size" => 5));

	if (in_array("NAME", $arVisibleColumns))
	{
		$arGroupLang = CForumGroup::GetLangByID($group["ID"], LANG);
		$fieldShow = ($by == "LEFT_MARGIN" ? str_pad("", ($group["DEPTH_LEVEL"] - 1), ".") : "").htmlspecialcharsbx($arGroupLang["NAME"]);
		$row->AddViewField("NAME", '<a title="'.GetMessage("FORUM_EDIT_DESCR").'" href="forum_group_edit.php?ID='.$group["ID"]."&lang=".LANG."&".GetFilterParams("filter_").'">'.$fieldShow.'</a>');
	}

	$arActions = Array();
	if (($forumModulePermissions>="R") && CForumGroup::CanUserUpdateGroup(0, $USER->GetUserGroupArray()))
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("FORUM_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("forum_group_edit.php?ID=".$group["ID"]."&lang=".LANG."&".GetFilterParams("filter_", false)), "DEFAULT"=>true);
	}
	if (($forumModulePermissions >= "W") && CForumGroup::CanUserDeleteGroup(0, $USER->GetUserGroupArray()))
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("FORUM_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('GROUP_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($group["ID"], "delete"));
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

if (($forumModulePermissions >= "W") && CForumGroup::CanUserAddGroup($USER->GetUserGroupArray()))
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("FGAN_ADD_NEW"),
			"LINK" => "forum_group_edit.php?lang=".LANG,
			"TITLE" => GetMessage("FGAN_ADD_NEW_ALT"),
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
$APPLICATION->SetTitle(GetMessage("GROUP_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>