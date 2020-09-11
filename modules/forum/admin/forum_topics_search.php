<?
//*****************************************************************************************************************
//	Topic manage
//************************************!****************************************************************************
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	\Bitrix\Main\Loader::includeModule("forum");
	$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumModulePermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
//************************************ Forums *********************************************************************
	$db_Forum = CForumNew::GetListEx(array("SORT"=>"ASC", "NAME"=>"ASC"));

	$FN = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST["FN"]);
	$FC = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST["FC"]);
	if ($FC == '') $FC = "TOPIC_ID";

	$arr = array();
	$arr["reference_id"][] = "";
	$arr["reference"][] = "";
	$arrForum = array();
	$arrSelect = "";
	while($dbForum = $db_Forum->Fetch())
	{
		$arrForum[$dbForum["ID"]] = htmlspecialcharsex($dbForum["NAME"]);
		$arrSelect .= "<option value='".$dbForum["ID"]."'>".htmlspecialcharsex($dbForum["NAME"])."</option>";
		$arr["reference_id"][] = $dbForum["ID"];
		$arr["reference"][] = htmlspecialcharsex($dbForum["NAME"]);
	}
//************************************ Filter *****************8***************************************************
	$sTableID = "tbl_topic";
	$oSort = new CAdminSorting($sTableID, "ID", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter(array("FORUM_ID", "DATE_FROM", "DATE_TO", "CREATE_DATE_FROM", "CREATE_DATE_TO"));
//************************************ Check filter ***************************************************************
	$arMsg = array();
	$err = false;

	$date1_create_stm = "";
	$date1_create_stm = "";
	$date1_stm = "";
	$date2_stm = "";

	$CREATE_DATE_FROM = trim($CREATE_DATE_FROM);
	$CREATE_DATE_TO = trim($CREATE_DATE_TO);
	$CREATE_DATE_FROM_DAYS_TO_BACK = intval($CREATE_DATE_FROM_DAYS_TO_BACK);
	if ($CREATE_DATE_FROM <> '' || $CREATE_DATE_TO <> '' || $CREATE_DATE_FROM_DAYS_TO_BACK>0)
	{
		$date1_create_stm = MkDateTime(ConvertDateTime($CREATE_DATE_FROM,"D.M.Y"),"d.m.Y");
		$date2_create_stm = MkDateTime(ConvertDateTime($CREATE_DATE_TO,"D.M.Y")." 23:59","d.m.Y H:i");

		if ($CREATE_DATE_FROM_DAYS_TO_BACK > 0)
		{
			$date1_create_stm = time()-86400*$CREATE_DATE_FROM_DAYS_TO_BACK;
			$date1_create_stm = GetTime($date1_create_stm);
		}
		if (!$date1_create_stm)
			$arMsg[] = array("id"=>">=START_DATE", "text"=> GetMessage("FM_WRONG_DATE_CREATE_FROM"));

		if (!$date2_create_stm && $CREATE_DATE_TO <> '')
			$arMsg[] = array("id"=>"<=START_DATE", "text"=> GetMessage("FM_WRONG_DATE_CREATE_FROM"));
		elseif ($date1_create_stm && $date2_create_stm && ($date2_create_stm <= $date1_create_stm))
			$arMsg[] = array("id"=>"find_date_create_timestamp2", "text"=> GetMessage("SUP_FROM_TILL_DATE_TIMESTAMP"));
	}

	// LAST TOPIC
	$DATE_FROM = trim($DATE_FROM);
	$DATE_TO = trim($DATE_TO);
	$DATE_FROM_DAYS_TO_BACK = intval($DATE_FROM_DAYS_TO_BACK);
	if ($DATE_FROM <> '' || $DATE_TO <> '' || $DATE_FROM_DAYS_TO_BACK>0)
	{
		$date1_stm = MkDateTime(ConvertDateTime($DATE_FROM,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(ConvertDateTime($DATE_TO,"D.M.Y")." 23:59","d.m.Y H:i");

		if ($DATE_FROM_DAYS_TO_BACK > 0)
		{
			$date1_stm = time()-86400*$DATE_FROM_DAYS_TO_BACK;
			$date1_stm = GetTime($date1_stm);
		}
		if (!$date1_stm)
			$arMsg[] = array("id"=>">=LAST_POST_DATE", "text"=> GetMessage("FM_WRONG_DATE_CREATE_FROM"));

		if (!$date2_stm && $DATE_TO <> '')
			$arMsg[] = array("id"=>"<=LAST_POST_DATE", "text"=> GetMessage("FM_WRONG_DATE_CREATE_FROM"));
		elseif ($date1_stm && $date2_stm && ($date2_stm <= $date1_stm))
			$arMsg[] = array("id"=>"find_date_timestamp2", "text"=> GetMessage("SUP_FROM_TILL_DATE_TIMESTAMP"));
	}

	$arFilter = array();
	$FORUM_ID = intval($FORUM_ID);
	if ($FORUM_ID>0)
		$arFilter = array("FORUM_ID" => $FORUM_ID);

	if ($date1_create_stm <> '')
		$arFilter = array_merge($arFilter, array(">=START_DATE" => $CREATE_DATE_FROM));
	if ($date2_create_stm <> '')
		$arFilter = array_merge($arFilter, array("<=START_DATE"	=> $CREATE_DATE_TO));

	if ($date1_stm <> '')
		$arFilter = array_merge($arFilter, array(">=LAST_POST_DATE" => $DATE_FROM));
	if ($date2_stm <> '')
		$arFilter = array_merge($arFilter, array("<=LAST_POST_DATE"	=> $DATE_TO));

	if (!empty($arMsg))
	{
		$err = new CAdminException($arMsg);
		$lAdmin->AddFilterError($err->GetString());
	}

	$rsData = CForumTopic::GetList(array($by=>$order), $arFilter);
	$rsData = new CAdminResult($rsData, $sTableID);
	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FM_TOPICS")));

//************************************ Headers ********************************************************************
	$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
		array("id"=>"TITLE", "content"=>GetMessage("FM_TITLE_NAME"), "sort"=>"TITLE", "default"=>true),
		array("id"=>"START_DATE","content"=>GetMessage("FM_TITLE_DATE_CREATE"), "sort"=>"START_DATE", "default"=>true),
		array("id"=>"USER_START_NAME","content"=>GetMessage("FM_TITLE_AUTHOR"), "sort"=>"USER_START_NAME", "default"=>true),
		array("id"=>"POSTS", "content"=>GetMessage("FM_TITLE_MESSAGES"),	"sort"=>"POSTS", "default"=>false),
		array("id"=>"VIEWS", "content"=>GetMessage("FM_TITLE_VIEWS"),  "sort"=>"VIEWS", "default"=>false),
		array("id"=>"FORUM_ID", "content"=>GetMessage("FM_TITLE_FORUM"),  "sort"=>"FORUM_ID", "default"=>true),
		array("id"=>"LAST_POST_DATE", "content"=>GetMessage("FM_TITLE_LAST_MESSAGE"),  "sort"=>"LAST_POST_DATE", "default"=>false),
		array("id"=>"ACTION",	"content"=>GetMessage("MAIN_ACTION"), "default"=>true),
		));
//************************************ Body ***********************************************************************
while ($arForum = $rsData->NavNext(true, "t_"))
{
	$row =& $lAdmin->AddRow($t_ID, $arForum);
	$row->bReadOnly = True;
	$row->AddViewField("ID", $t_ID);
	$row->AddViewField("TITLE", $t_TITLE);
	$row->AddViewField("START_DATE", $t_START_DATE);
	$row->AddViewField("USER_START_NAME", $t_USER_START_NAME);
	$row->AddViewField("POSTS", $t_POSTS);
	$row->AddViewField("VIEWS", $t_VIEWS);
	$row->AddViewField("FORUM_ID", $t_FORUM_ID);
	$row->AddViewField("LAST_POST_DATE", $t_LAST_POST_DATE);
	$row->AddViewField("ACTION", "<input type=\"button\" onClick=\"SetValue('".$t_ID."');\" value=\"".GetMessage("MAIN_SELECT")."\">");
}
//************************************ Footer *********************************************************************
	$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);

	$lAdmin->CheckListMode();

	$APPLICATION->SetTitle(GetMessage("FORUM_TOPICS"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
			GetMessage("FM_TITLE_DATE_CREATE"),
			GetMessage("FM_TITLE_DATE_LAST_POST")
		)
	);
	?>
	<script language="JavaScript">
	<!--
	function SetValue(id)
	{
		<?if ($FN == ''):?>
			window.opener.document.getElementById("<?echo $FC;?>").value=id;
		<?else:?>
			window.opener.document.<?echo $FN;?>["<?echo $FC;?>"].value=id;
		<?endif;?>
		window.close();
	}
	//-->
	</script>

	<form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>?">
	<?$oFilter->Begin();?>
	<tr valign="center">
		<td><b><?=GetMessage("FM_TITLE_FORUM")?>:</b></td>
		<td><?echo SelectBoxFromArray("FORUM_ID", $arr, $FORUM_ID)?></td>
	</tr>
	<tr valign="center">
		<td><?echo GetMessage("FM_TITLE_DATE_CREATE").":"?></td>
		<td><?echo CalendarPeriod("CREATE_DATE_FROM", $CREATE_DATE_FROM, "CREATE_DATE_TO", $CREATE_DATE_TO, "form1","Y")?></td>
	</tr>
	<tr valign="center">
		<td><?echo GetMessage("FM_TITLE_DATE_LAST_POST").":"?></td>
		<td><?echo CalendarPeriod("DATE_FROM", $DATE_FROM, "DATE_TO", $DATE_TO, "form1","Y")?></td>
	</tr>

	<?
	$oFilter->Buttons(array("table_id" => $sTableID,"url" => $APPLICATION->GetCurPage(),"form" => "find_form"));
	$oFilter->End();
	?></form><?
	$lAdmin->DisplayList();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
?>