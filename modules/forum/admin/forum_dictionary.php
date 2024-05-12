<?
/********************************************************************
	Profanity dictionary.
********************************************************************/
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	\Bitrix\Main\Loader::includeModule("forum");
	$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumModulePermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
	$sTableID = "tbl_filter_dictionary";
	$oSort = new CAdminSorting($sTableID, "ID", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter(array("TITLE"));

/*******************************************************************/
	$arFilter = array();
	$ID = isset($ID) ? intval($ID) : null;
	$TITLE = isset($TITLE) ? trim($TITLE) : "";
    $TYPE = isset($_REQUEST["TYPE"]) ? mb_strtoupper(trim($_REQUEST["TYPE"])) : null;
	$arFilter = array("TYPE" => $TYPE);
	if ($TITLE <> '')
		$arFilter = array_merge($arFilter, array("%TITLE" => "%".$TITLE."%"));
/*******************************************************************/
	if ($lAdmin->EditAction() && $forumModulePermissions >= "W")
	{
		foreach ($FIELDS as $ID => $arFields)
		{
			$ID = intval($ID);
			if (!$lAdmin->IsUpdated($ID))
				continue;
			$DB->StartTransaction();
			if (!CFilterDictionary::Update($ID, array("TITLE" => $arFields["TITLE"])))
			{
				if ($ex = $APPLICATION->GetException())
					$lAdmin->AddUpdateError($ex->GetString(), $ID);
				else
					$lAdmin->AddUpdateError(str_replace("##", $ID, GetMessage("FLT_NOT_UPDATE")), $ID);
				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
			}
		}
	}
/*******************************************************************/
	if($arID = $lAdmin->GroupAction())
	{
		if(isset($_REQUEST['action_target']) && $_REQUEST['action_target']=='selected')
		{
			$rsData = CFilterDictionary::GetList(array($by=>$order), $arFilter);
			while($arRes = $rsData->Fetch())
				$arID[] = $arRes['ID'];
		}
		if(check_bitrix_sessid() && (CFilterUnquotableWords::FilterPerm()))
		{
			foreach($arID as $ID)
			{
				if($ID == '')
					continue;
				$ID = intval($ID);
				switch($_REQUEST['action'])
				{
					case "delete":
						CFilterDictionary::Delete($ID);
						break;
					case "generate":
							CFilterUnquotableWords::GenPatternAll($DICTIONARY_ID_W, $ID);
						break;

				}
			}
		}
	}
	$rsData = CFilterDictionary::GetList(array($by=>$order), $arFilter);
	$rsData = new CAdminResult($rsData, $sTableID);
	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FLT_TITLE")));
	$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
		array("id"=>"TITLE", "content"=>GetMessage("FLT_HEAD_TITLE"),  "sort"=>"TITLE", "default"=>true),
		));
/*******************************************************************/
	while ($arData = $rsData->NavNext(true, "t_"))
	{
		$row =& $lAdmin->AddRow($t_ID, $arData);
		if (!CFilterUnquotableWords::FilterPerm())
			$row->bReadOnly = True;
		$row->AddViewField("ID", '<a title="'.GetMessage("FLT_ACT_EDIT").'" href="'.($TYPE == "T"?"forum_letter.php":"forum_words.php")."?DICTIONARY_ID=".$t_ID."&amp;lang=".LANG.'">'.$t_ID.'</a>');
		$row->AddInputField("TITLE", array("size" => "50"));

		$arActions = Array();
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("FLT_ACT_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("forum_dictionary_edit.php?DICTIONARY_ID=".$t_ID."&lang=".LANG), "DEFAULT" => true);
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("FLT_ACT_DEL"), "ACTION"=>"if(confirm('".GetMessage("FLT_ACT_DEL_CONFIRM")."')) ".$lAdmin->ActionDoGroup($t_ID, "delete"),);
		$row->AddActions($arActions);
	}
/*******************************************************************/
	$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);
	$action_group = array("delete" => GetMessage("FLT_ACT_DEL"));
	if ($TYPE=="T")
	{
		$db_res = CFilterDictionary::GetList(array(), array("TYPE"=>"W"));
		$option = "";
		$active = COption::GetOptionString("forum", "FILTER_DICT_W", '', SITE_ID);
		while ($res = $db_res->GetNext())
			$option .= "<option value='".$res["ID"].($res["ID"] == $active ? " selected " : "")."'>".$res["TITLE"]."</option>";

		$action_group = array_merge($action_group,
			array("generate" => GetMessage("FLT_ACT_GEN"),
			"copy2" => array(
				"type" => "html",
				"value" => GetMessage("FLT_ACT_GEN_CONFIRM")
			),
			"copy1" => array(
				"type" => "html",
				"value" => "<select name='DICTIONARY_ID_W'>".$option."</select>"
			)));

	}
	$lAdmin->AddGroupActionTable($action_group);
	if ($forumModulePermissions >= "W")
	{
		$aContext = array(
			array(
				"TEXT" => GetMessage("FLT_ACT_ADD"),
				"LINK" => "forum_dictionary_edit.php?TYPE=".$TYPE."&lang=".LANG,
				"TITLE" => GetMessage("FLT_ACT_ADD"),
				"ICON" => "btn_new",
			),
		);
		$lAdmin->AddAdminContextMenu($aContext);
	}
/*******************************************************************/
		$lAdmin->CheckListMode();
/*******************************************************************/
	$APPLICATION->SetTitle(GetMessage("FLT_TITLE_".$TYPE));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
		)
	);
	?><form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="TYPE" value="<?=htmlspecialcharsbx($TYPE)?>">
	<?$oFilter->Begin();?>
	<tr valign="center">
		<td><b><?=GetMessage("FLT_HEAD_TITLE")?>:</b></td>
		<td><input type="text" name="TITLE" value="<?=htmlspecialcharsbx($TITLE)?>" size="47"></td>
	</tr><?
	$oFilter->Buttons(
		array(
			"table_id" => $sTableID,
			"url" => $APPLICATION->GetCurPage()."?TYPE=".$TYPE."&lang=".LANG,
			"form" => "find_form"
		)
	);

	$oFilter->End();
	?></form><?
	$lAdmin->DisplayList();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
