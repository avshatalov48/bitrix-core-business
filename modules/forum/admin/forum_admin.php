<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use \Bitrix\Main\Localization\Loc;

\Bitrix\Main\Loader::includeModule("forum");
$userOpt = \CUserOptions::getOption("admin_panel", "forum");
if (!isset($userOpt["forum_admin"]) || $userOpt["forum_admin"] != "old")
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?><?$APPLICATION->IncludeComponent("bitrix:forum.admin.forums", ".default", []);?><?
	?><?=BeginNote();?><a href="javascript:void(0);" onclick="BX.userOptions.save('admin_panel', 'forum', 'forum_admin', 'old');BX.reload();return false;"><?
	?><?=Loc::getMessage("FORUM_ADMIN_GO_BACK_TO_OLD_VIEW")?></a><?=EndNote();
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	return;
}

$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
if ($forumModulePermissions == "D"):
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
endif;

IncludeModuleLangFile(__FILE__);
ClearVars();

global $by, $order;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$arSites = array();
$db_res = CSite::GetList();
if ($db_res && $res = $db_res->GetNext()):
	do
	{
		$arSites[$res["ID"]] = $res;
	} while ($res = $db_res->GetNext());
endif;

$arForumGroups = CForumGroup::GetByLang(LANGUAGE_ID);
$arForumGroupsTitle = array();
array_unshift($arForumGroups, array("ID" => 0, "NAME" => "..."));
foreach ($arForumGroups as $key => $res)
{
	$depthLevel = isset($res["DEPTH_LEVEL"]) ? $res["DEPTH_LEVEL"] : 0;
	$name = isset($res["~NAME"]) ? $res["~NAME"] : "";
	$arForumGroups[$res["ID"]] = $res;
	$arForumGroupsTitle[$res["ID"]] = str_pad("", ($depthLevel-1), "."). $name ." [".$res["ID"]."]";
}
$arForumGroupsTitle[0] = "...";

$arForumSort = array();
for ($ii = 0; $ii < count($aSortTypes["reference_id"]); $ii++):
	$arForumSort[$aSortTypes["reference_id"][$ii]] = $aSortTypes["reference"][$ii];
endfor;
$arForumSortDirection = array();
for ($ii = 0; $ii < count($aSortDirection["reference_id"]); $ii++):
	$arForumSortDirection[$aSortDirection["reference_id"][$ii]] = $aSortDirection["reference"][$ii];
endfor;

$sTableID = "tbl_forum_forums";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_site_id",
	"filter_active",
	"filter_group_id");


$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if ($filter_site_id <> '' && $filter_site_id != "NOT_REF")
	$arFilter["SITE_ID"] = $filter_site_id;
if ($filter_active <> '')
	$arFilter["ACTIVE"] = $filter_active;
if ($filter_group_id <> '')
	$arFilter["FORUM_GROUP_ID"] = $filter_group_id;

if (check_bitrix_sessid() && $forumModulePermissions >= "R"):
	if ($lAdmin->EditAction())
	{
		foreach ($FIELDS as $ID => $arFields)
		{
			$ID = intval($ID);

			if (!$lAdmin->IsUpdated($ID))
				continue;

			if (!CForumNew::CanUserUpdateForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
			{
				$lAdmin->AddUpdateError(GetMessage("FA_NO_PERMS2UPDATE")." ".$ID."", $ID);
				continue;
			}

			$DB->StartTransaction();

			if (!CForumNew::Update($ID, $arFields))
			{
				if ($ex = $APPLICATION->GetException())
					$lAdmin->AddUpdateError($ex->GetString(), $ID);
				else
					$lAdmin->AddUpdateError(GetMessage("FA_ERROR_UPDATE")." ".$ID."", $ID);

				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
			}
		}
	}

	if ($arID = $lAdmin->GroupAction())
	{
		if (isset($_REQUEST['action_target']) && $_REQUEST['action_target']=='selected')
		{
			$arID = array();
			$dbResultList = CForumNew::GetList(
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

					if (!CForumNew::CanUserDeleteForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
					{
						$lAdmin->AddGroupError(GetMessage("FA_DELETE_NO_PERMS"), $ID);
					}
					else
					{
						@set_time_limit(0);

						$DB->StartTransaction();

						if (!CForumNew::Delete($ID))
						{
							$DB->Rollback();

							if ($ex = $APPLICATION->GetException())
								$lAdmin->AddGroupError($ex->GetString(), $ID);
							else
								$lAdmin->AddGroupError(GetMessage("FA_DELETE_ERROR"), $ID);
						}
						else
						{
							$DB->Commit();
						}
					}
					break;
				case "activate":
				case "deactivate":
					if (!CForumNew::CanUserUpdateForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
					{
						$lAdmin->AddUpdateError(GetMessage("FA_NO_PERMS2UPDATE")." ".$ID."", $ID);
					}
					else
					{
						$arFields = array(
							"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
						);

						if (!CForumNew::Update($ID, $arFields))
						{
							if ($ex = $APPLICATION->GetException())
								$lAdmin->AddGroupError($ex->GetString(), $ID);
							else
								$lAdmin->AddGroupError(GetMessage("FA_ERROR_UPDATE")." ".$ID."", $ID);
						}
					}
					break;
				case "clear_html":
					$DB->StartTransaction();
					if (!CForumNew::ClearHTML($ID))
					{
						$DB->Rollback();
						$lAdmin->AddGroupError(GetMessage("FA_ERROR_UPDATE")." ".$ID."", $ID);
					}
					else
					{
						$DB->Commit();
					}
					break;
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
			if ($componentRelativePath == '' || !is_array($arComponentDescription)):
				continue;
			elseif (!array_key_exists("CACHE_PATH", $arComponentDescription)):
				continue;
			endif;
			foreach ($arSites as $res):
				$path = $componentRelativePath;
			if ($arComponentDescription["CACHE_PATH"] == "Y")
				$path = "/".$res["LID"].$path;
			if (!empty($path))
				BXClearCache(true, $path);
			endforeach;
		}
	}
endif;
$dbResultList = CForumNew::GetListEx(
	array($by => $order),
	$arFilter);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>false),
	array("id"=>"FORUM_GROUP_ID", "content"=>GetMessage("FORUM_GROUP_ID"), "sort"=>"FORUM_GROUP_LEFT_MARGIN", "default"=>true,),
	array("id"=>"NAME", "content"=>GetMessage("NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"ACTIVE","content"=>GetMessage("ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"LAND", "content"=>GetMessage('LAND'), "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("SORT"),  "sort"=>"SORT", "align"=>"right"),
	array("id"=>"MODERATION","content"=>GetMessage("MODERATION"), "sort"=>"MODERATION"),
	array("id"=>"INDEXATION","content"=>GetMessage("INDEXATION"), "sort"=>"INDEXATION"),
	array("id"=>"ORDER_BY","content"=>GetMessage("ORDER_BY"), "sort"=>"ORDER_BY"),
	array("id"=>"ORDER_DIRECTION","content"=>GetMessage("ORDER_DIRECTION"), "sort"=>"ORDER_DIRECTION"),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arForum = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arForum);

	$bCanUpdateForum = CForumNew::CanUserUpdateForum($f_ID, $USER->GetUserGroupArray(), $USER->GetID());
	$bCanDeleteForum = CForumNew::CanUserDeleteForum($f_ID, $USER->GetUserGroupArray(), $USER->GetID());

	$row->bReadOnly = ($bCanUpdateForum ? false : true);

	$row->AddField("ID", $f_ID);
	$row->AddViewField("NAME", '<a title="'.GetMessage("FORUM_EDIT").'" href="forum_edit.php?ID='.$f_ID.'&amp;lang='.
		LANG.GetFilterParams("filter_").'">'.$f_NAME.'</a>');
	$row->AddInputField("NAME", ($bCanUpdateForum ? array("size" => "30") : false));

	$row->AddCheckField("ACTIVE", ($bCanUpdateForum ? array() : false));
	$res = array();
	$res2 = array();
	if (in_array("LAND", $arVisibleColumns))
	{
		$arForumSite = CForumNew::GetSites($f_ID);
		foreach ($arSites as $lid => $site)
		{
			if (array_key_exists($lid, $arForumSite))
				$res[] = $site["NAME"]." [".$lid."]";
			$class = (empty($arForumSite[$lid]) ? "empty-path" : "");
			$text = isset($arForumSite[$lid]) ? $arForumSite[$lid] : "";
			$res2[] = <<<HTML
<dt class="$class" id="site_lid_{$key}">{$site["NAME"]} [{$key}]</dt>
<dd>
	<textarea id="site_path_{$key}" rows="2" cols="40" name="SITE_PATH[{$key}]"
		onfocus="BX.removeClass(BX('site_lid_{$key}'), 'empty-path')"
		onblur="if(!BX.type.isNotEmptyString(this.value)){ BX.addClass(BX('site_lid_{$key}'), 'empty-path')}"
		>{$text}</textarea>
</dd>
HTML;
		}
	}
	$row->AddField("LAND", implode("<br />", $res));
	$row->AddEditField("LAND", '<dl>'.implode("", $res2).'</dl>');
	$row->AddInputField("SORT", ($bCanUpdateForum? array("size" => "3") : false ));
	$row->AddViewField("FORUM_GROUP_ID", isset($f_FORUM_GROUP_ID) ? $arForumGroups[$f_FORUM_GROUP_ID]["NAME"] : null);
	$row->AddSelectField("FORUM_GROUP_ID", ($bCanUpdateForum ? $arForumGroupsTitle : false));

	$row->AddCheckField("MODERATION", ($bCanUpdateForum ? array() : false));
	$row->AddCheckField("INDEXATION", ($bCanUpdateForum ? array() : false));

	$row->AddSelectField("ORDER_BY", ($bCanUpdateForum ? $arForumSort: false));
	$row->AddSelectField("ORDER_DIRECTION", ($bCanUpdateForum ? $arForumSortDirection: false));

	$arActions = Array();
	if ($bCanUpdateForum)
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("FORUM_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("forum_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_", false).""), "DEFAULT"=>true);
	}
	if ($bCanDeleteForum)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("FORUM_DELETE"), "ACTION"=>"if(confirm('".GetMessage('DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"clear_html" => GetMessage("MAIN_ADMIN_LIST_CLEAR_HTML"),
	)
);

if ($forumModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("FFAN_ADD_NEW"),
			"LINK" => "forum_edit.php?lang=".LANG,
			"TITLE" => GetMessage("FFAN_ADD_NEW_ALT"),
			"ICON" => "btn_new",
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("FORUMS"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("FFAN_ACTIVE"),
		GetMessage("FFAN_GROUP_ID"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><b><?= GetMessage("FFAN_SITE_ID") ?>:</b></td>
		<td>
			<?echo CSite::SelectBox("filter_site_id", $filter_site_id, "(".GetMessage("FFAN_ALL").")"); ?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("FFAN_ACTIVE") ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?= htmlspecialcharsex("(".GetMessage("FFAN_ALL").")") ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?=htmlspecialcharsex(GetMessage("FFAN_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?=htmlspecialcharsex(GetMessage("FFAN_NO")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><label><?= GetMessage("FFAN_GROUP_ID") ?>:</label></td>
		<td>

				<select name="filter_group_id">
					<option value="">(<?=GetMessage("FFAN_ALL");?>)</option>
					<?
				foreach ($arForumGroupsTitle as $key => $val):
					?>
					<option value="<?=$key?>"
					<?=(intval($filter_group_id)==intval($key) ? " selected" : "")?>
					><?=htmlspecialcharsbx($val)?></option><?
				endforeach;
				?>
				</select>

		</td>
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
<?=\Bitrix\Main\Update\Stepper::getHtml("forum");?><?
$lAdmin->DisplayList();
?><?=BeginNote();?><a href="javascript:void(0);" onclick="BX.userOptions.save('admin_panel', 'forum', 'forum_admin', 'new');BX.reload();return false;"><?=Loc::getMessage("FORUM_ADMIN_GO_TO_NEW_VIEW")?></a><?=EndNote();
?>
<style>
dl, dd, dt { margin: 0; }
dt.empty-path { text-decoration: line-through; }
</style>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
