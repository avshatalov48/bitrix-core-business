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
	$sTableID = "tbl_filter";
	$oSort = new CAdminSorting($sTableID, "ID", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);
	$lAdmin->InitFilter(array("ID", "find_pattern", "find_type", "USE_IT", "PATTERN_CREATE"));

/*******************************************************************/
	$arFilter = array();
	$DICTIONARY_ID = intVal($_REQUEST["DICTIONARY_ID"]);
	
	if ($DICTIONARY_ID <= 0)
	{
		$DICTIONARY_ID = 0;
		$lAdmin->AddFilterError(GetMessage("FLT_NOT_DICTIONARY")); 
	}
	$arFilter = array("DICTIONARY_ID" => $DICTIONARY_ID);
	$find_pattern = trim($find_pattern);
	if (strLen($find_pattern)>0)
		$arFilter = array_merge($arFilter, array("%".htmlspecialcharsbx(strToUpper($find_type)) => "%".$find_pattern."%"));
	if (($USE_IT) && $USE_IT != "all")
		$arFilter = array_merge($arFilter,  array("USE_IT"	=> (trim($USE_IT) == "Y"? "Y" : "N")));	
	if ($PATTERN_CREATE && $PATTERN_CREATE != "ALL")
		$arFilter = array_merge($arFilter, array("PATTERN_CREATE" => trim($PATTERN_CREATE)));
/*******************************************************************/
	if ($lAdmin->EditAction() && $forumModulePermissions >= "W")
	{
		foreach ($FIELDS as $ID => $arFields)
		{
			$DB->StartTransaction();
			$ID = IntVal($ID);
	
			if (!$lAdmin->IsUpdated($ID))
				continue;
			if (is_set($arFields, "PATTERN_CREATE"))
			{
				$WORDS = trim($arFields["WORDS"]);
				if ($arFields["PATTERN_CREATE"] == "WORDS")
				{				
					$arFields["WORDS"] = $WORDS;
					$arFields["PATTERN"] = CFilterUnquotableWords::CreatePattern($WORDS, -1);
					$arFields["PATTERN_CREATE"] = "WORDS";
				}
				elseif ($arFields["PATTERN_CREATE"] == "TRNSL")
				{
					$arFields["WORDS"] = trim($WORDS);
					$arFields["PATTERN"] = CFilterUnquotableWords::CreatePattern($WORDS, 0);
					$arFields["PATTERN_CREATE"] = "TRNSL";
				}
				elseif ($arFields["PATTERN_CREATE"] == "PTTRN")
				{
					$arFields["WORDS"] = $WORDS;
					$arFields["PATTERN"] = $WORDS;
					$arFields["PATTERN_CREATE"] = "PTTRN";
				}
				else 
				{
					$arFields["WORDS"] = "";
					$arFields["PATTERN"] = "";
				}
			}
			
	
			if (!CFilterUnquotableWords::Update($ID, $arFields))
			{
				if ($ex = $APPLICATION->GetException())
					$lAdmin->AddUpdateError($ex->GetString(), $ID);
				else
					$lAdmin->AddUpdateError(GetMessage("FLT_NOT_UPDATE"));
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
			$rsData = CFilterUnquotableWords::GetList(array($by=>$order), $arFilter);
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
						CFilterUnquotableWords::Delete($ID);
						break;
					case "generate": 
						CFilterUnquotableWords::GenPattern($ID, intVal($DICTIONARY_ID_T));
						break;
				}
			}
		}
	}
	$rsData = CFilterUnquotableWords::GetList(array($by=>$order), $arFilter);
	$rsData = new CAdminResult($rsData, $sTableID);
	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FLT_TITLE_NAV")));
	$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
		array("id"=>"USE_IT", "content"=>GetMessage("FLT_USE_IT"),  "sort"=>"USE_IT", "default"=>true),
		array("id"=>"PATTERN_CREATE", "content"=>GetMessage("FLT_PATTERN_CREATE"),  "sort"=>"PATTERN_CREATE", "default"=>true),
		array("id"=>"WORDS", "content"=>GetMessage("FLT_WORDS"), "sort"=>"WORDS", "default"=>true),
		array("id"=>"PATTERN", "content"=>GetMessage("FLT_PATTERN"), "sort"=>"PATTERN", "default"=>false),
		array("id"=>"REPLACEMENT","content"=>GetMessage("FLT_REPLACEMENT"), "sort"=>"REPLACEMENT", "default"=>true),
		array("id"=>"DESCRIPTION", "content"=>GetMessage("FLT_DESCRIPTION"),  "sort"=>"DESCRIPTION", "default"=>true),
		));
/*******************************************************************/
	while ($arData = $rsData->NavNext(true, "t_"))
	{
		$row =& $lAdmin->AddRow($t_ID, $arData);
		if (!CFilterUnquotableWords::FilterPerm())
			$row->bReadOnly = True;
		$row->AddViewField("ID", '<a title="'.GetMessage("FLT_ACT_EDIT").'" href="'."forum_words_edit.php?DICTIONARY_ID=".$t_DICTIONARY_ID."&ID=".$t_ID."&amp;lang=".LANG.'">'.$t_ID.'</a>');
		$row->AddInputField("WORDS", array("size"=>"20"));
		$row->AddInputField("PATTERN", array());
		$row->AddInputField("REPLACEMENT", array("maxlength"=>"255", "size"=>"10%"));
		$row->AddInputField("DESCRIPTION", array("size"=>"80%"));
		$row->AddCheckField("USE_IT", array("Y"=>GetMessage("FLT_ACT_USE_IT_Y"), "N"=>GetMessage("FLT_ACT_USE_IT_N")));
		$row->AddSelectField("PATTERN_CREATE", array("WORDS"=>GetMessage("FLT_FLT_WORDS"), "TRNSL"=>GetMessage("FLT_FLT_TRNSL"), "PTTRN"=>GetMessage("FLT_FLT_PTTRN")));
		
		$arActions = Array();
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("FLT_ACT_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("forum_words_edit.php?DICTIONARY_ID=".$t_DICTIONARY_ID."&lang=".LANG."&ID=".$t_ID.GetFilterParams("filter_", false).""), "DEFAULT" => true);
//		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("FLT_ACT_GEN"), "ACTION"=>$lAdmin->ActionDoGroup($t_ID, "generate", "DICTIONARY_ID=".$t_DICTIONARY_ID."&lang=".LANG));
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("FLT_ACT_DEL"), "ACTION"=>"if(confirm('".GetMessage("FLT_ACT_DEL_CONFIRM")."')) ".$lAdmin->ActionDoGroup($t_ID, "delete", "DICTIONARY_ID=".$t_DICTIONARY_ID."&lang=".LANG),);
		$row->AddActions($arActions);
	}
/*******************************************************************/
	$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);
	$db_res = CFilterDictionary::GetList(array(), array("TYPE"=>"T"));
	$option = "";
	$active = COption::GetOptionString("forum", "FILTER_DICT_T", '', SITE);
	while ($res = $db_res->GetNext())
		$option .= "<option value='".$res["ID"].($res["ID"] == $active ? " selected " : "")."'>".$res["TITLE"]."</option>";
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
			"generate" => GetMessage("FLT_ACT_GEN"),
			"copy2" => array(
				"type" => "html",
				"value" => GetMessage("FLT_ACT_GEN_CONFIRM")
			),
			"copy1" => array(
				"type" => "html",
				"value" => "<select name='DICTIONARY_ID_T'>".$option."</select>"
			),
			)
		);
	if (($forumModulePermissions >= "W") && ($DICTIONARY_ID))
	{
		$aContext = array(
			array(
				"TEXT" => GetMessage("FLT_ACT_ADD"),
				"LINK" => "forum_words_edit.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG,
				"TITLE" => GetMessage("FLT_ACT_ADD"),
				"ICON" => "btn_new")
		);
		$lAdmin->AddAdminContextMenu($aContext);
	}	
/*******************************************************************/
		$lAdmin->CheckListMode();
/*******************************************************************/
	$APPLICATION->SetTitle(GetMessage("FLT_TITLE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
			GetMessage("FLT_USE_IT"), 
			GetMessage("FLT_PATTERN_CREATE"), 
		)
	);
	?><form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>?">
	<input type="hidden" name="DICTIONARY_ID" value="<?=$DICTIONARY_ID?>">
	<?$oFilter->Begin();?>
	
	<tr valign="center">
		<td><b><?=GetMessage("MAIN_FIND")?>:</b></td>
		<td>
			<input type="text" size="47" name="find_pattern" value="<?=htmlspecialcharsbx($find_pattern)?>" title="<?=GetMessage("MAIN_FIND_TITLE")?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage("FLT_WORDS"),
				GetMessage("FLT_PATTERN"),
				GetMessage("FLT_REPLACEMENT"),
				GetMessage("FLT_DESCRIPTION"),
			),
			"reference_id" => array(
				"WORDS",
				"PATTERN",
				"REPLACEMENT",
				"DESCRIPTION",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
		</td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FLT_USE_IT")?>:</td>
		<td><?echo SelectBoxFromArray("USE_IT", array("REFERENCE" => array("", GetMessage("FLT_ACT_USE_IT_Y"), GetMessage("FLT_ACT_USE_IT_N")), "REFERENCE_ID" => array("all", "Y", "N")), $USE_IT)?></td>
	</tr>
	<tr valign="center">
		<td><?=GetMessage("FLT_PATTERN_CREATE")?>:</td>
		<td><?echo SelectBoxFromArray("PATTERN_CREATE", array("REFERENCE" => array("", GetMessage("FLT_FLT_WORDS"), GetMessage("FLT_FLT_TRNSL"), GetMessage("FLT_FLT_PTTRN")), "REFERENCE_ID" => array("ALL", "WORDS", "TRNSL", "PTTRN")), $PATTERN_CREATE)?></td>
	</tr>
	<?
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