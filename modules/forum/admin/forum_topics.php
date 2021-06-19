<?
/********************************************************************
	Topics
**************************************!*****************************/
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	\Bitrix\Main\Loader::includeModule("forum");
	$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumModulePermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
/*******************************************************************/
	$db_Forum = CForumNew::GetListEx(array("SORT"=>"ASC", "NAME"=>"ASC"));

	$arr = array();
	$arr["reference_id"][] = "";
	$arr["reference"][] = "";
	$arrForum = array();
	$arrSelect = "";
	while($dbForum = $db_Forum->Fetch())
	{
		$arrForum[$dbForum["ID"]] = ($dbForum["NAME"]);
		$arrSelect .= "<option value='".$dbForum["ID"]."'>".htmlspecialcharsex($dbForum["NAME"])."</option>";
		$arr["reference_id"][] = $dbForum["ID"];
		$arr["reference"][] = ($dbForum["NAME"]);
	}
/*******************************************************************/
	$sTableID = "tbl_topic";
	$oSort = new CAdminSorting($sTableID, "ID", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter(array(
		"FORUM_ID", "TITLE", "DESCRIPTION", "USER_START_ID",
		"ACTIVE", "PINNED", "OPENED",
		"DATE_FROM", "DATE_TO",
		"CREATE_DATE_FROM", "CREATE_DATE_TO"));
/*******************************************************************/
	$arMsg = array();
	$err = false;

	$date1_create_stm = 0;
	$date2_create_stm = 0;
	$date1_stm = "";
	$date2_stm = "";

	$CREATE_DATE_FROM = trim($CREATE_DATE_FROM);
	$CREATE_DATE_TO = trim($CREATE_DATE_TO);
	$CREATE_DATE_FROM_DAYS_TO_BACK = intval($CREATE_DATE_FROM_DAYS_TO_BACK);
	if (!empty($CREATE_DATE_FROM) || !empty($CREATE_DATE_TO) || $CREATE_DATE_FROM_DAYS_TO_BACK > 0)
	{
		$date1_create_stm = MkDateTime(ConvertDateTime($CREATE_DATE_FROM, "D.M.Y"), "d.m.Y");
		$date2_create_stm = MkDateTime(ConvertDateTime($CREATE_DATE_TO, "D.M.Y"), "d.m.Y");

		if (!empty($CREATE_DATE_FROM)) {
			if (!empty($CREATE_DATE_TO)) {
				$date2_create_stm = MkDateTime(ConvertDateTime($CREATE_DATE_TO, "D.M.Y")." 23:59:59", "d.m.Y H:i:s");
				$CREATE_DATE_TO .= " 23:59:59";
			} else if ($CREATE_DATE_FROM_FILTER_PERIOD == "after") {
				$date1_create_stm = MkDateTime(ConvertDateTime($CREATE_DATE_FROM, "D.M.Y")." 23:59:59", "d.m.Y H:i:s");
				$CREATE_DATE_FROM .= " 23:59:59";
			}
		}

		if ($CREATE_DATE_FROM_DAYS_TO_BACK > 0) {
			$date1_create_stm = time()-86400*$CREATE_DATE_FROM_DAYS_TO_BACK;
			$date1_create_stm = GetTime($date1_create_stm);
		}
		if (!$date1_create_stm && !empty($CREATE_DATE_FROM))
			$arMsg[] = array("id" => ">=START_DATE", "text" => GetMessage("FM_WRONG_DATE_CREATE_FROM"));
		if (!$date2_create_stm && !empty($CREATE_DATE_TO))
			$arMsg[] = array("id" => "<=START_DATE", "text" => GetMessage("FM_WRONG_DATE_CREATE_TILL"));
		elseif ($date1_create_stm && $date2_create_stm && ($date2_create_stm <= $date1_create_stm))
			$arMsg[] = array("id" => "find_date_create_timestamp2", "text" => GetMessage("FM_FROM_TILL_TIMESTAMP"));
	}

	// LAST TOPIC
	$DATE_FROM = trim($DATE_FROM);
	$DATE_TO = trim($DATE_TO);
	$DATE_FROM_DAYS_TO_BACK = intval($DATE_FROM_DAYS_TO_BACK);
	if (!empty($DATE_FROM) || !empty($DATE_TO) || $DATE_FROM_DAYS_TO_BACK > 0)
	{
		$date1_stm = MkDateTime(ConvertDateTime($DATE_FROM, "D.M.Y"), "d.m.Y");
		$date2_stm = MkDateTime(ConvertDateTime($DATE_TO, "D.M.Y"), "d.m.Y");

		if (!empty($DATE_FROM)) {
			if (!empty($DATE_TO)) {
				$date2_stm = MkDateTime(ConvertDateTime($DATE_TO, "D.M.Y")." 23:59:59", "d.m.Y H:i:s");
				$DATE_TO .= " 23:59:59";
			} else if ($DATE_FROM_FILTER_PERIOD == "after") {
				$date1_stm = MkDateTime(ConvertDateTime($DATE_FROM, "D.M.Y")." 23:59:59", "d.m.Y H:i:s");
				$DATE_FROM .= " 23:59:59";
			}
		}

		if ($DATE_FROM_DAYS_TO_BACK > 0)
		{
			$date1_stm = time()-86400*$DATE_FROM_DAYS_TO_BACK;
			$date1_stm = GetTime($date1_stm);
		}
		if (!$date1_stm && !empty($DATE_FROM))
			$arMsg[] = array("id"=>">=LAST_POST_DATE", "text"=> GetMessage("FM_WRONG_DATE_FROM"));
		if (!$date2_stm && !empty($DATE_TO))
			$arMsg[] = array("id"=>"<=LAST_POST_DATE", "text"=> GetMessage("FM_WRONG_DATE_TO"));
		elseif ($date1_stm && $date2_stm && ($date2_stm <= $date1_stm))
			$arMsg[] = array("id"=>"find_date_timestamp2", "text"=> GetMessage("FM_FROM_TILL_TIMESTAMP"));
	}

	$arFilter = array();
	$FORUM_ID = intval($FORUM_ID);
	if ($FORUM_ID > 0):
		$arFilter["FORUM_ID"] = $FORUM_ID;
	endif;
	$TITLE = trim($TITLE);
	if ($TITLE <> ''):
		$arFilter["TITLE"] = $TITLE;
	endif;
	$DESCRIPTION = trim($DESCRIPTION);
	if ($DESCRIPTION <> ''):
		$arFilter["DESCRIPTION"] = $DESCRIPTION;
	endif;
	$USER_START_ID = intval($USER_START_ID);
	if ($USER_START_ID > 0):
		$arFilter["USER_START_ID"] = $USER_START_ID;
	elseif (trim($_REQUEST["USER_START_ID"]) == "0"):
		$arFilter["USER_START_ID"] = 0;
	else:
		$USER_START_ID = "";
	endif;
	if ($APPROVED == "Y" || $APPROVED == "N"):
		$arFilter["APPROVED"] = $APPROVED;
	else:
		$APPROVED = "";
	endif;
	if ($PINNED == "Y" || $PINNED == "N"):
		$arFilter["SORT"] = ($PINNED == "Y" ? 100 : 150);
	else:
		$PINNED = "";
	endif;
	if ($STATE == "Y" || $STATE == "N" || $STATE == "L"):
		$arFilter["STATE"] = $STATE;
	else:
		$STATE = "";
	endif;

	if (!empty($date1_create_stm))
		$arFilter[">=START_DATE"] = $CREATE_DATE_FROM;
	if (!empty($date2_create_stm))
		$arFilter["<=START_DATE"] = $CREATE_DATE_TO;

	if (!empty($date1_stm))
		$arFilter[">=LAST_POST_DATE"] = $DATE_FROM;
	if (!empty($date2_stm))
		$arFilter["<=LAST_POST_DATE"] = $DATE_TO;

	if (!empty($arMsg))
	{
		$err = new CAdminException($arMsg);
		$lAdmin->AddFilterError($err->GetString());
	}


/*******************************************************************/
$clearCache = false;
if ($lAdmin->EditAction() && $forumModulePermissions >= "R")
{
	$sError = ""; $sOk = "";
	foreach ($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID)):
			continue;
		elseif (!CForumTopic::CanUserUpdateTopic($ID, $USER->GetUserGroupArray(), $USER->GetID())):
			continue;
		endif;

		$res = CForumTopic::GetById($ID, array("NoFilter" => true));

		if (is_set($arFields, "APPROVED")):
			$arFields["APPROVED"] = ($arFields["APPROVED"] == "N" ? "N" : "Y");
			if ($res["APPROVED"] != $arFields["APPROVED"]):
				ForumActions(($arFields["APPROVED"] == "Y" ? "SHOW_TOPIC" : "HIDE_TOPIC"), array("TID" => $ID), $sError, $sOk);
			endif;
			unset($arFields["APPROVED"]);
		endif;
		if (is_set($arFields, "FORUM_ID")):
			if ($res["FORUM_ID"] != $arFields["FORUM_ID"]):
				$result = CForumTopic::MoveTopic2Forum(array($res["ID"]), $arFields["FORUM_ID"], "N");
			endif;
			unset($arFields["FORUM_ID"]);
		endif;

		foreach ($arFields as $key => $val):
			if ($val == $res[$key]):
				unset($arFields[$key]);
			endif;
		endforeach;
		if (empty($arFields)):
			continue;
		endif;

		if (!CForumTopic::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("FM_WRONG_UPDATE"), $ID);
		}
		else
		{
			$clearCache = true;
			if (is_set($arFields, "STATE") && $arFields["STATE"] != $res["STATE"])
			{
				CForumEventLog::Log("topic", ($arFields["STATE"] == "Y" ? "open" : "close"), $ID, serialize($res));
				unset($arFields["STATE"]);
			}
			if (is_set($arFields, "SORT") && $arFields["SORT"] != $res["SORT"])
			{
				CForumEventLog::Log("topic", (intval($arFields["SORT"]) == 100 ? "stick" : "unstick"), $ID, serialize($res));
				unset($arFields["SORT"]);
			}
			if (!empty($arFields))
			{
				foreach ($arFields as $key => $val):
					if ($arFields[$key] != $res[$key]):
						$res_log[$key] =  $arFields[$key];
						$res_log["before".$key] =  $res[$key];
					endif;
				endforeach;
				if (!empty($res_log)):
					$arTopic = CForumTopic::GetByID($TID);
					$res_log['FORUM_ID'] = $arTopic['FORUM_ID'];
					CForumEventLog::Log("topic", "edit", $TID, serialize($res_log));
				endif;
			}
		}
	}
}
/*******************************************************************/
if($arID = $lAdmin->GroupAction())
{
	$sError = ""; $sOk = "";
	if (!check_bitrix_sessid())
	{

	}
	elseif ($_REQUEST['action'] == "move" && intval($_REQUEST['move_to']) <= 0)
	{
		$lAdmin->AddFilterError(GetMessage("FM_WRONG_FORUM_ID"));
	}
	else
	{
		$arFilterAction = $arFilter;
		if ($_REQUEST['action'] == "move"):
			$arFilterAction["!FORUM_ID"] = $_REQUEST['move_to'];
		endif;
		if ($_REQUEST['action_target'] != 'selected'):
			$arFilterAction["@ID"] = $arID;
		endif;
		if ($APPLICATION->GetGroupRight("forum") < "W"):
			$arFilterAction["PERMISSION_STRONG"] = "Y";
		endif;
		$rsData = CForumTopic::GetListEx(array($by=>$order), $arFilterAction);
		$arID = array();
		while($res = $rsData->Fetch())
			$arID[] = $res['ID'];

		if (empty($arID))
		{

		}
		else
		{
			$clearCache = true;
			switch($_REQUEST['action'])
			{
				case "delete":
					ForumDeleteTopic($arID, $sError, $sOk);
					break;
				case "move":
					if (!CForumTopic::MoveTopic2Forum($arID, intval($_REQUEST['move_to']))):
						$ex = $APPLICATION->GetException();
						if ($ex && $err = $ex->GetString()):
							$lAdmin->AddUpdateError($err, $ID);
						else:
							$lAdmin->AddUpdateError(GetMessage("FM_WRONG_UPDATE"), $ID);
						endif;
					endif;
					break;
			}
		}
	}
	if (!empty($sError))
	{
		$lAdmin->AddFilterError($sError);
	}
}
if ($clearCache)
{
	// Clear cache.
	$arSites = array();
	if ($db_res = CSite::GetList())
	{
		while ($res = $db_res->GetNext())
		{
			$arSites[] = $res["LID"];
		}
	}
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
		if ($componentRelativePath == '' || !is_array($arComponentDescription))
			continue;
		elseif (!array_key_exists("CACHE_PATH", $arComponentDescription))
			continue;
		foreach ($arSites as $siteId)
		{
			$path = $componentRelativePath;
			if ($arComponentDescription["CACHE_PATH"] == "Y")
				$path = "/".$siteId.$path;
			if (!empty($path))
				BXClearCache(true, $path);
		}
	}
}

	$rsData = CForumTopic::GetListEx(array($by => $order), $arFilter, false, 0, array("NoFilter" => true));
	$rsData = new CAdminResult($rsData, $sTableID);
	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FM_TOPICS")));

/*******************************************************************/
	$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
		array("id"=>"TITLE", "content"=>GetMessage("FM_TITLE_NAME"), "sort"=>"TITLE", "default"=>true),
		array("id"=>"DESCRIPTION", "content"=>GetMessage("FM_TITLE_DESCRIPTION"), "sort"=>"DESCRIPTION", "default"=>false),
		array("id"=>"STATE", "content"=>GetMessage("FM_TITLE_STATE"), "sort"=>"STATE", "default"=>true),
		array("id"=>"APPROVED", "content"=>GetMessage("FM_TITLE_APPROVED"), "sort"=>"APPROVED", "default"=>true),
		array("id"=>"SORT", "content"=>GetMessage("FM_TITLE_SORT"), "sort"=>"SORT", "default"=>true),
		array("id"=>"USER_START_NAME", "content"=>GetMessage("FM_TITLE_AUTHOR"), "sort"=>"USER_START_NAME", "default"=>true),
		array("id"=>"START_DATE", "content"=>GetMessage("FM_TITLE_DATE_CREATE"), "sort"=>"START_DATE", "default"=>true),
		array("id"=>"POSTS", "content"=>GetMessage("FM_TITLE_MESSAGES"),	"sort"=>"POSTS", "default"=>false),
		array("id"=>"VIEWS", "content"=>GetMessage("FM_TITLE_VIEWS"),  "sort"=>"VIEWS", "default"=>false),
		array("id"=>"FORUM_ID", "content"=>GetMessage("FM_TITLE_FORUM"),  "sort"=>"FORUM_NAME", "default"=>true),
		array("id"=>"LAST_POST_DATE", "content"=>GetMessage("FM_TITLE_LAST_MESSAGE"),  "sort"=>"LAST_POST_DATE", "default"=>false)
		));
/*******************************************************************/
while ($res = $rsData->NavNext(true, "t_"))
{
	$row =& $lAdmin->AddRow($t_ID, $res);
	$bCanUpdateForum = CForumTopic::CanUserUpdateTopic($t_ID, $USER->GetUserGroupArray(), $USER->GetID());
	$bCanDeleteForum = CForumTopic::CanUserDeleteTopic($t_ID, $USER->GetUserGroupArray(), $USER->GetID());
	$row->bReadOnly = (!$bCanUpdateForum || !$bCanDeleteForum ? true : false);

	$row->AddField("ID", $t_ID);
	$row->AddInputField("TITLE", array("size" => "35"));
	$row->AddInputField("DESCRIPTION", array("size" => "35"));
	if ($t_STATE != "L")
		$row->AddSelectField("STATE", array("Y" => GetMessage("F_OPEN"), "N" => GetMessage("F_CLOSE")));
	else
		$row->AddField("STATE", "Link");

	$row->AddSelectField("APPROVED", array("Y" => GetMessage("F_SHOW"), "N" => GetMessage("F_HIDE")));
	$row->AddSelectField("SORT", array("100" => GetMessage("F_PINN"), "150" => GetMessage("F_UNPINN")));
	$row->AddInputField("USER_START_NAME", array("size" => "20"));
	$row->AddInputField("START_DATE", array("size" => "16"));
	$row->AddField("POSTS", $t_POSTS);
	$row->AddInputField("VIEWS", array("size" => "2"));
	$row->AddSelectField("FORUM_ID", $arrForum);
	$row->AddInputField("LAST_POST_DATE", array("size" => "16"));
}
/*******************************************************************/
	$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("FM_ACT_DELETE"),
			"move" => GetMessage("FM_ACT_MOVE"),
			"space" => array(
				"type" => "html",
				"value" => "&nbsp;"),
			"move_to" => array(
				"type" => "html",
				"value" =>
					"<select name=\"move_to\" id=\"move_to\" disabled>".$arrSelect."</select>".
					"<input type=\"hidden\" name=\"copy_to_site\" value=\"\">"
			)
		),
		array("select_onchange"=>"this.form.move_to.disabled=this.form.action.value=='move'? false : true;")
	);

		$lAdmin->AddAdminContextMenu();

/*******************************************************************/
	$lAdmin->CheckListMode();
/*******************************************************************/
	$APPLICATION->SetTitle(GetMessage("FORUM_TOPICS"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
			GetMessage("FM_TITLE_NAME"),
			GetMessage("FM_TITLE_DESCRIPTION"),
			GetMessage("FM_TITLE_APPROVED"),
			GetMessage("FM_TITLE_SORT"),
			GetMessage("FM_TITLE_STATE"),

			GetMessage("FM_TITLE_DATE_CREATE"),
			GetMessage("FM_TITLE_DATE_LAST_POST"),
			GetMessage("FM_TITLE_USER_START_ID")
		)
	);
	?>
	<form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>?">
	<?$oFilter->Begin();?>
	<tr valign="center">
		<td><b><?=GetMessage("FM_TITLE_FORUM")?>:</b></td>
		<td><?echo SelectBoxFromArray("FORUM_ID", $arr, $FORUM_ID)?></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_TITLE_NAME")?>:</td>
		<td><input type="text" name="TITLE" value="<?=htmlspecialcharsbx($TITLE)?>" /></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_TITLE_DESCRIPTION")?>:</td>
		<td><input type="text" name="DESCRIPTION" value="<?=htmlspecialcharsbx($DESCRIPTION)?>" /></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_TITLE_APPROVED")?>:</td>
		<td><select name="APPROVED">
				<option value="" <?=($APPROVED == "" ? " selected='selected'" : "")?>><?=GetMessage("F_ALL")?></option>
				<option value="Y"<?=($APPROVED == "Y"? " selected='selected'" : "")?>><?=GetMessage("F_SHOW")?></option>
				<option value="N"<?=($APPROVED == "N"? " selected='selected'" : "")?>><?=GetMessage("F_HIDE")?></option>
			</select></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_TITLE_SORT")?>:</td>
		<td><select name="PINNED">
				<option value="" <?=($PINNED == "" ? " selected='selected'" : "")?>><?=GetMessage("F_ALL")?></option>
				<option value="Y"<?=($PINNED == "Y"? " selected='selected'" : "")?>><?=GetMessage("F_PINN")?></option>
				<option value="N"<?=($PINNED == "N"? " selected='selected'" : "")?>><?=GetMessage("F_UNPINN")?></option>
			</select></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_TITLE_STATE")?>:</td>
		<td><select name="STATE">
				<option value="" <?=($STATE == "" ? " selected='selected'" : "")?>><?=GetMessage("F_ALL")?></option>
				<option value="Y"<?=($STATE == "Y"? " selected='selected'" : "")?>><?=GetMessage("F_OPEN")?></option>
				<option value="N"<?=($STATE == "N"? " selected='selected'" : "")?>><?=GetMessage("F_CLOSE")?></option>
				<option value="L"<?=($STATE == "L"? " selected='selected'" : "")?>><?=GetMessage("F_LINK")?></option>
			</select></td>
	</tr>
	<tr valign="center">
		<td><?echo GetMessage("FM_TITLE_DATE_CREATE").":"?></td>
		<td><?echo CalendarPeriod("CREATE_DATE_FROM", $CREATE_DATE_FROM, "CREATE_DATE_TO", $CREATE_DATE_TO, "form1","Y")?></td>
	</tr>
	<tr valign="center">
		<td><?echo GetMessage("FM_TITLE_DATE_LAST_POST").":"?></td>
		<td><?echo CalendarPeriod("DATE_FROM", $DATE_FROM, "DATE_TO", $DATE_TO, "form1","Y")?></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_TITLE_USER_START_ID")?>:</td>
		<td><input type="text" name="USER_START_ID" value="<?=$USER_START_ID?>" /></td>
	</tr>

	<?
	$oFilter->Buttons(array("table_id" => $sTableID,"url" => $APPLICATION->GetCurPage(),"form" => "find_form"));
	$oFilter->End();
	?>
	</form>
	<script language="JavaScript">
		function Select_Move()
		{
			var form = document.getElementById('form_tbl_topic');
			if (form.action == 'move')
				form.move_to.disabled = false;
			else
				form.move_to.disabled = true;
			return;
		}
	</script>
	<?
	$lAdmin->DisplayList();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
