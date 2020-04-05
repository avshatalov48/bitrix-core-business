<?
/********************************************************************
	Profanity dictionary.
********************************************************************/
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
	$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumModulePermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
	
	$sTableID = "tbl_filter_dictionary_letter";
	$oSort = new CAdminSorting($sTableID, "ID", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter(array("DICTIONARY_ID", "find_type", "find"));

/*******************************************************************/
	$arFilter = array();
	$find = trim($find);
	if (strLen($find) > 0)
		$arFilter["%".htmlspecialcharsbx($find_type)] = "%".$find."%";
	$DICTIONARY_ID = intVal($_REQUEST["DICTIONARY_ID"]);
	if ($DICTIONARY_ID <= 0)
		$lAdmin->AddFilterError(GetMessage("FLT_NOT_DICT")); 
	$arFilter["DICTIONARY_ID"] = $DICTIONARY_ID;
/*******************************************************************/
	if ($lAdmin->EditAction())
		{
		foreach ($FIELDS as $ID => $arFields)
		{
			$arFields = array_merge($arFields, array("DICTIONARY_ID"=>$DICTIONARY_ID));
			$DB->StartTransaction();
			$ID = IntVal($ID);
			if (!$lAdmin->IsUpdated($ID))
				continue;
	
			if (!CFilterLetter::Update($ID, $arFields))
			{
				if ($ex = $APPLICATION->GetException())
				{
					$lAdmin->AddUpdateError($ex->GetString(), $ID);
				}	
				else
					$lAdmin->AddUpdateError(GetMessage("FLT_NOT_UPDATE"), $ID);
				$DB->Rollback();
			}
			$DB->Commit();
		}
	}
/*******************************************************************/
	if($arID = $lAdmin->GroupAction())
	{
		if($_REQUEST['action_target']=='selected')
		{
			$rsData = CFilterLetter::GetList(array($by=>$order), $arFilter);
			while($arRes = $rsData->Fetch())
				$arID[] = $arRes['ID'];
		}
		if(check_bitrix_sessid() && (CFilterUnquotableWords::FilterPerm()))
		{
			foreach($arID as $ID)
			{
				if(strlen($ID)<=0)
					continue;
				$ID = intval($ID);
				switch($_REQUEST['action'])
				{
					case "delete": 
						CFilterLetter::Delete($ID);
						break;
				}
			}
		}
	}
	$rsData = CFilterLetter::GetList(array($by=>$order), $arFilter);
	$rsData = new CAdminResult($rsData, $sTableID);
	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FLT_LETTERS")));
	$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
		array("id"=>"LETTER", "content"=>GetMessage("FLT_LETTER"),  "sort"=>"LETTER", "default"=>true),
		array("id"=>"REPLACEMENT", "content"=>GetMessage("FLT_REPLACE"), "sort"=>"REPLACEMENT", "default"=>true),
		));
/*******************************************************************/
	while ($arData = $rsData->NavNext(true, "t_"))
	{
		$row =& $lAdmin->AddRow($t_ID, $arData);
		if (!CFilterUnquotableWords::FilterPerm())
			$row->bReadOnly = True;
		$row->AddViewField("ID", $t_ID);
		$row->AddInputField("LETTER", array("size" => "35"));
		$row->AddInputField("REPLACEMENT", array("size" => "150"));
//		$row->AddViewField("DICTIONARY_ID", $DICTIONARY_ID);
	}
/*******************************************************************/
	$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);
	$lAdmin->AddGroupActionTable(array("delete" => GetMessage("FLT_ACT_DEL")));
	if ($forumModulePermissions >= "W")
	{
		$aContext = array(
			array(
				"TEXT" => GetMessage("FLT_ACT_ADD"),
				"LINK" => "forum_letter_edit.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG,
				"TITLE" => GetMessage("FLT_ACT_ADD"),
				"ICON" => "btn_new",
			),
		);
		$lAdmin->AddAdminContextMenu($aContext);
	}	
/*******************************************************************/
		$lAdmin->CheckListMode();
/*******************************************************************/
	$APPLICATION->SetTitle(GetMessage("FORUM_MENU_FILTER_DT"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$oFilter = new CAdminFilter($sTableID."filter_dictionary_letter", array());
	?><form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="DICTIONARY_ID" value="<?=$DICTIONARY_ID?>">
	<?$oFilter->Begin();?>
	<tr valign="center">
		<td><b><?=GetMessage("MAIN_FIND")?>:</b></td>
		<td><input type="text" name="find" size="47" value="<?=htmlspecialcharsbx($find)?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage("FLT_LETTER"),
				GetMessage("FLT_REPLACE"),
			),
			"reference_id" => array(
				"LETTER",
				"REPLACEMENT",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
		</td>	
	</tr><?
	$oFilter->Buttons(
		array(
			"table_id" => $sTableID,
			"url" => $APPLICATION->GetCurPage()."?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG,
			"form" => "find_form"
		)
	);
	$oFilter->End();
	?></form><?
	$lAdmin->DisplayList();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>