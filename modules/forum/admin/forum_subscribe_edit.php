<?
//*****************************************************************************************************************
//	Topic manage
//************************************!****************************************************************************
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
	$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumModulePermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
//************************************!Filter *********************************************************************
	$sTableID = "tbl_subscribe_edit";
	$oSort = new CAdminSorting($sTableID, "ID", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter(array("FilterType_S", "Filter_S", "FORUM_ID_S", "DATE_FROM_S", "DATE_TO_S", "SUBSCR_TYPE_S"));
//************************************!Check filter ***************************************************************
	$USER_ID = intVal($USER_ID);
	$arFilter = array("USER_ID"=>$USER_ID);
	$arMsg = array();
	$err = false;

	if ($USER_ID<= 0)
		$arMsg[] = array("id"=>"USER_ID", "text" => GetMessage("FM_WRONG_USER_ID"));

	$date1_stm = "";
	$date2_stm = "";

	$DATE_FROM_S = trim($DATE_FROM_S);
	$DATE_TO_S = trim($DATE_TO_S);
	$DATE_FROM_S_DAYS_TO_BACK = intval($DATE_FROM_S_DAYS_TO_BACK);
	if (strlen($DATE_FROM_S)>0 || strlen($DATE_TO_S)>0 || $DATE_FROM_S_DAYS_TO_BACK>0)
	{
		$date1_stm = MkDateTime(ConvertDateTime($DATE_FROM_S,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(ConvertDateTime($DATE_TO_S,"D.M.Y")." 23:59","d.m.Y H:i");

		if ($DATE_FROM_S_DAYS_TO_BACK > 0)
		{
			$date1_stm = time()-86400*$DATE_FROM_S_DAYS_TO_BACK;
			$date1_stm = GetTime($date1_stm);
		}

		if (!$date1_stm)
			$arMsg[] = array("id"=>">=START_DATE", "text"=> GetMessage("FM_WRONG_DATE_FROM"));

		if (!$date2_stm && strlen($DATE_TO_S)>0)
			$arMsg[] = array("id"=>"<=START_DATE", "text"=> GetMessage("FM_WRONG_DATE_TO"));
		elseif ($date1_stm && $date2_stm && ($date2_stm <= $date1_stm))
			$arMsg[] = array("id"=>"find_date_timestamp2", "text"=> GetMessage("FM_WRONG_PERIOD"));
	}
	$Filter_S = trim($Filter_S);
	$FilterType_S = strtolower(trim($FilterType_S));
	if ((strLen($Filter_S) > 0) && in_array($FilterType_S, array("forum", "topic")))
		$arFilter["".strToUpper($FilterType_S)] = $Filter_S;

	$FORUM_ID_S = intval($FORUM_ID_S);
	if ($FORUM_ID_S>0)
		$arFilter["FORUM_ID"] = $FORUM_ID_S;

	if (strlen($date1_stm)>0)
		$arFilter[">=START_DATE"] = $DATE_FROM_S;
	if (strlen($date2_stm)>0)
		$arFilter["<=START_DATE"] = $DATE_TO_S;

	if (strLen($SUBSCR_TYPE_S) > 0)
	{
		switch ($SUBSCR_TYPE_S)
		{
			case "new_topic_only":
				$arFilter["NEW_TOPIC_ONLY"] = "Y";
				$arFilter["TOPIC_ID"] = "";
				break;
			case "all_message":
				$arFilter["NEW_TOPIC_ONLY"] = "N";
				$arFilter["TOPIC_ID"] = "";
				break;
			case "typical":
				$arFilter["!FORUM_ID"] = 0;
				$arFilter["!TOPIC_ID"] = 0;
				break;
		}
	}

//************************************ Actions ********************************************************************
	if($arID = $lAdmin->GroupAction())
	{
		$candelete = false;
		if($_REQUEST['action_target']=='selected')
		{
			$rsData = CForumSubscribe::GetListEx(array($by=>$order), $arFilter);
			while($arRes = $rsData->Fetch())
				$arID[] = $arRes['ID'];
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
						if (CForumSubscribe::CanUserDeleteSubscribe($ID, $USER->GetUserGroupArray(), $USER->GetID()))
							CForumSubscribe::Delete($ID);
						else
							$arMsg[] = array("id" => "NO_PERMS", "text" => GetMessage("FSUBSC_NO_SPERMS"));
						break;
				}
			}
		}
	}
//************************************/Actions ********************************************************************
	if (!empty($arMsg))
	{
		$err = new CAdminException($arMsg);
		$lAdmin->AddFilterError($err->GetString());
	}

	$rsData = CForumSubscribe::GetListEx(array($by=>$order), $arFilter);
	$rsData = new CAdminResult($rsData, $sTableID);
	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FM_TITLE_PAGE")));
//************************************ Headers ********************************************************************
	$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>GetMessage("FM_HEAD_ID"), "sort"=>"ID", "default"=>true),
		array("id"=>"FORUM_NAME", "content"=>GetMessage("FM_HEAD_FORUM"), "sort"=>"FORUM_NAME", "default"=>true),
		array("id"=>"TITLE", "content"=>GetMessage("FM_HEAD_TOPIC"), "sort"=>"TITLE", "default"=>true),
		array("id"=>"START_DATE", "content"=>GetMessage("FM_HEAD_START_DATE"), "sort"=>"START_DATE", "default"=>true),
		array("id"=>"LAST_SEND", "content"=>GetMessage("FM_HEAD_LAST_SEND"), "sort"=>"LAST_SEND", "default"=>true)
		));
//************************************ Body ***********************************************************************
while ($arRes = $rsData->NavNext(true, "t_"))
{
	$row =& $lAdmin->AddRow($t_ID, $arRes);
	$LOGIN = $arRes["LOGIN"];
	if($t_TOPIC_ID <= 0)
		$t_TITLE = $t_NEW_TOPIC_ONLY == "Y" ? GetMessage("FM_NEW_TOPIC_ONLY") : GetMessage("FM_ALL_MESSAGE");
	$row->AddViewField("TITLE", $t_TITLE);
	$arActions = array();
	$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("FM_ACT_DELETE"), "ACTION"=>"if(confirm('".GetMessage("FM_ACT_DEL_CONFIRM")."')) ".$lAdmin->ActionDoGroup($t_ID, "delete", "USER_ID=".$USER_ID."&lang=".LANG));
	$row->AddActions($arActions);

}
//************************************ Footer *********************************************************************
	$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage('FM_ACT_DELETE')
			)
	);
	$aMenu = array(
		array(
			"TEXT" => GetMessage("FM_LIST_USER"),
			"LINK" => "/bitrix/admin/forum_subscribe.php?lang=".LANG,
			"ICON" => "btn_list",
		)
	);

	$lAdmin->AddAdminContextMenu($aMenu);

	$lAdmin->CheckListMode();

//************************************ Page ***********************************************************************
	$APPLICATION->SetTitle(GetMessage("FM_TITLE").$LOGIN);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$oFilter = new CAdminFilter(
		$sTableID."_subscribe",
		array(
			GetMessage("FM_FLT_START_DATE_ALT"),
			GetMessage("FM_FLT_SUBSCR_TYPE_ALT")
		)
	);
	?>
	<form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>?">
	<input type="hidden" name="USER_ID" value="<?=$USER_ID?>">
	<?$oFilter->Begin();?>
	<tr valign="center">
		<td><b><?=GetMessage("FM_FLT_SEARCH")?>:</b></td>
		<td nowrap>
		<input type="text" size="25" name="Filter_S" value="<?=htmlspecialcharsbx($Filter_S)?>" title="<?=GetMessage("FM_FLT_SEARCH_TITLE")?>">
		<select name="FilterType_S">
			<option value="forum"<?if($find_type=="forum") echo " selected"?>><?=GetMessage("FM_FLT_FORUM")?></option>
			<option value="topic"<?if($find_type=="topic") echo " selected"?>><?=GetMessage("FM_FLT_TOPIC")?></option>
		</select>
		</td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_FLT_START_DATE").":"?></td>
		<td><?echo CalendarPeriod("DATE_FROM_S", $DATE_FROM_S, "DATE_TO_S", $DATE_TO_S, "form1","Y")?></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FM_FLT_SUBSCR_TYPE")?>:</td>
		<td>
		<select name="SUBSCR_TYPE_S">
			<option value=""<?if($SUBSCR_TYPE_S=="") echo " selected"?>><?=GetMessage('FM_SPACE')?></option>
			<option value="new_topic_only"<?if($SUBSCR_TYPE_S=="new_topic_only") echo " selected"?>><?=GetMessage('FM_NEW_TOPIC_ONLY')?></option>
			<option value="all_message"<?if($SUBSCR_TYPE_S=="all_message") echo " selected"?>><?=GetMessage('FM_ALL_MESSAGE')?></option>
			<option value="typical"<?if($SUBSCR_TYPE_S=="typical") echo " selected"?>><?=GetMessage('FM_TYPICAL')?></option>
		</select>
		</td>
	</tr>
	<?
	$oFilter->Buttons(array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage()."?USER_ID=".$USER_ID."&lang=".LANG,
		"form" => "find_form"));
	$oFilter->End();
	?></form><?
	$lAdmin->DisplayList();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>