<?
/********************************************************************
	Subscribes
**************************************!*****************************/
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
	$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumModulePermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
/*******************************************************************/
	$db_res = CForumNew::GetListEx(array("SORT"=>"ASC", "NAME"=>"ASC"));
	$arForum = array();
	if ($db_res && $res = $db_res->Fetch())
	{
		do
		{
			$arForum[$res["ID"]] = $res["NAME"];
		}while ($res = $db_res->Fetch());
	}
	asort($arForum);
	array_unshift($arForum, GetMessage('FM_SPACE'));
/*******************************************************************/
	$sTableID = "tbl_subscribe";
	$oSort = new CAdminSorting($sTableID, "ID", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter(array("FilterType", "Filter", "FORUM_ID", "DATE_FROM", "DATE_TO", "SUBSCR_TYPE"));
	global $order, $by, $SUBSCR_TYPE;
/*******************************************************************/
	$arFilter = array("SUBSC"=>true);
	$arMsg = array();
	$err = false;

	$date1_stm = "";
	$date2_stm = "";

	$DATE_FROM = trim($DATE_FROM);
	$DATE_TO = trim($DATE_TO);
	$DATE_FROM_DAYS_TO_BACK = intval($DATE_FROM_DAYS_TO_BACK);
	if (strlen($DATE_FROM)>0 || strlen($DATE_TO)>0 || $DATE_FROM_DAYS_TO_BACK>0)
	{
		$date1_stm = MkDateTime(ConvertDateTime($DATE_FROM,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(ConvertDateTime($DATE_TO,"D.M.Y")." 23:59","d.m.Y H:i");

		if ($DATE_FROM_DAYS_TO_BACK > 0)
		{
			$date1_stm = time()-86400*$DATE_FROM_DAYS_TO_BACK;
			$date1_stm = GetTime($date1_stm);
		}

		if (!$date1_stm)
			$arMsg[] = array("id"=>">=START_DATE", "text"=> GetMessage("FM_WRONG_DATE_FROM"));

		if (!$date2_stm && strlen($DATE_TO)>0)
			$arMsg[] = array("id"=>"<=START_DATE", "text"=> GetMessage("FM_WRONG_DATE_TO"));
		elseif ($date1_stm && $date2_stm && ($date2_stm <= $date1_stm))
			$arMsg[] = array("id"=>"find_date_timestamp2", "text"=> GetMessage("FM_WRONG_PERIOD"));
	}
	$Filter = trim($Filter);
	$FilterType = strtolower(trim($FilterType));
	if ((strLen($Filter) > 0) && in_array($FilterType, array("login", "email", "name")))
		$arFilter[strToUpper($FilterType)] = $Filter;

	$FORUM_ID = intval($FORUM_ID);
	if ($FORUM_ID>0)
		$arFilter["SUBSC_FORUM_ID"] = $FORUM_ID;

	if (strlen($date1_stm)>0)
		$arFilter[">=SUBSC_START_DATE"] = $DATE_FROM;
	if (strlen($date2_stm)>0)
		$arFilter["<=SUBSC_START_DATE"] = $DATE_TO;

	if (strLen($SUBSCR_TYPE) > 0)
	{
		switch ($SUBSCR_TYPE)
		{
			case "new_topic_only":
				$arFilter["SUBSC_NEW_TOPIC_ONLY"] = "Y";
				$arFilter["SUBSC_TOPIC_ID"] = "";
				break;
			case "all_message":
				$arFilter["SUBSC_NEW_TOPIC_ONLY"] = "N";
				$arFilter["SUBSC_TOPIC_ID"] = "";
				break;
			case "typical":
				$arFilter[">SUBSC_FORUM_ID"] = 0;
				$arFilter[">SUBSC_TOPIC_ID"] = 0;
				break;
		}
	}

	if (!empty($arMsg))
	{
		$err = new CAdminException($arMsg);
		$lAdmin->AddFilterError($err->GetString());
	}
/*******************************************************************/
	if($arID = $lAdmin->GroupAction())
	{
		$candelete = false;
		if($_REQUEST['action_target']=='selected')
		{
			$rsData = CForumUser::GetListEx(array($by=>$order), $arFilter);
			while($arRes = $rsData->Fetch())
				$arID[] = $arRes['USER_ID'];
		}
		if(check_bitrix_sessid())
		{
			foreach($arID as $ID)
			{
				if(strlen($ID)<=0)
					continue;
				$ID = intval($ID);

				switch($_REQUEST['action'])
				{
					case "delete":
						if (CForumUser::IsAdmin())
						{
							CForumSubscribe::DeleteUSERSubscribe($ID);
						}
						break;
				}
			}
		}
	}

	$rsData = CForumUser::GetListEx(array($by=>$order), $arFilter);
	$rsData = new CAdminResult($rsData, $sTableID);
	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FM_TITLE_PAGE")));

/*******************************************************************/
	$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>GetMessage("FM_HEAD_FORUM_USER_ID"), "sort"=>"ID", "default"=>false),
		array("id"=>"USER_ID", "content"=>GetMessage("FM_HEAD_USER_ID"), "sort"=>"USER_ID", "default"=>true),
		array("id"=>"EMAIL", "content"=>GetMessage("FM_HEAD_EMAIL"), "sort"=>"EMAIL", "default"=>true),
		array("id"=>"LOGIN", "content"=>GetMessage("FM_HEAD_LOGIN"), "sort"=>"LOGIN", "default"=>true),
		array("id"=>"NAME", "content"=>GetMessage("FM_HEAD_NAME"), "sort"=>"NAME", "default"=>true),
		array("id"=>"LAST_NAME", "content"=>GetMessage("FM_HEAD_LAST_NAME"), "sort"=>"LAST_NAME", "default"=>true),
		array("id"=>"SUBSC_COUNT", "content"=>GetMessage("FM_HEAD_SUBSC"), "sort"=>"SUBSC_COUNT", "default"=>true),
		array("id"=>"SUBSC_START_DATE", "content"=>GetMessage("FM_HEAD_START_DATE"), "sort"=>"SUBSC_START_DATE", "default"=>true)
		));
/*******************************************************************/
while ($arRes = $rsData->NavNext(true, "t_"))
{
	$row =& $lAdmin->AddRow($t_USER_ID, $arRes);
	$row->AddViewField("USER_ID", "<a href='user_edit.php?lang=".LANGUAGE_ID."&ID=".$t_USER_ID."' title='".GetMessage("FM_MAIN_EDIT_TITLE")."'>".$t_USER_ID."</a>");
	$row->AddViewField("EMAIL", TxtToHtml($arRes["EMAIL"]));
	$row->AddViewField("SUBSC_COUNT", $t_SUBSC_COUNT <=0 ? GetMessage("FM_NO") : $t_SUBSC_COUNT);
	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("FM_ACT_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("forum_subscribe_edit.php?lang=".LANG."&USER_ID=".$t_USER_ID), "DEFAULT" => true);
	$arActions[] = array("SEPARATOR" => true);
	$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("FM_ACT_DELETE"), "ACTION"=>"if(confirm('".GetMessage("FM_ACT_DEL_CONFIRM")."')) ".$lAdmin->ActionDoGroup($t_USER_ID, "delete", "lang=".LANG),);
	$row->AddActions($arActions);

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
			"delete" => GetMessage("FM_ACT_DELETE")
			)
	);

		$lAdmin->AddAdminContextMenu();

/*******************************************************************/
	$lAdmin->CheckListMode();
/*******************************************************************/
	$APPLICATION->SetTitle(GetMessage("FM_TITLE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$oFilter = new CAdminFilter(
		$sTableID."_subscribe",
		array(
			GetMessage("FM_FLT_START_DATE_ALT"),
			GetMessage("FM_FLT_FORUM_ALT"),
			GetMessage("FM_FLT_SUBSCR_TYPE_ALT")
		)
	);
	?>
	<form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>?">
	<?$oFilter->Begin();?>
	<tr valign="center">
		<td><b><?=GetMessage("FM_FLT_SEARCH")?>:</b></td>
		<td nowrap>
		<input type="text" size="25" name="Filter" value="<?=htmlspecialcharsbx($Filter)?>" title="<?=GetMessage("FM_FLT_SEARCH_TITLE")?>">
		<select name="FilterType">
			<option value="login"<?if($_REQUEST["find_type"]=="login") echo " selected"?>><?=GetMessage('FM_FLT_LOGIN')?></option>
			<option value="email"<?if($_REQUEST["find_type"]=="email") echo " selected"?>><?=GetMessage('FM_FLT_EMAIL')?></option>
			<option value="name"<?if($_REQUEST["find_type"]=="name") echo " selected"?>><?=GetMessage('FM_FLT_FIO')?></option>
		</select>
		</td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_FLT_START_DATE").":"?></td>
		<td><?echo CalendarPeriod("DATE_FROM", $DATE_FROM, "DATE_TO", $DATE_TO, "form1","Y")?></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_FLT_FORUM")?>:</td>
		<td><?=SelectBoxFromArray("FORUM_ID", array("reference"=>array_values($arForum), "reference_id"=>array_keys($arForum)), $FORUM_ID)?></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_FLT_SUBSCR_TYPE")?>:</td>
		<td>
		<select name="SUBSCR_TYPE">
			<option value=""<?if($SUBSCR_TYPE=="") echo " selected"?>><?=GetMessage('FM_SPACE')?></option>
			<option value="new_topic_only"<?if($SUBSCR_TYPE=="new_topic_only") echo " selected"?>><?=GetMessage('FM_NEW_TOPIC_ONLY')?></option>
			<option value="all_message"<?if($SUBSCR_TYPE=="all_message") echo " selected"?>><?=GetMessage('FM_ALL_MESSAGE')?></option>
			<option value="typical"<?if($SUBSCR_TYPE=="typical") echo " selected"?>><?=GetMessage('FM_TYPICAL')?></option>
		</select>
		</td>
	</tr>

	<?
	$oFilter->Buttons(array("table_id" => $sTableID,"url" => $APPLICATION->GetCurPage(),"form" => "find_form"));
	$oFilter->End();
	?></form><?
	$lAdmin->DisplayList();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
