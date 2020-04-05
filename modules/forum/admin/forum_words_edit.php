<?
/********************************************************************
	Profanity dictionary.
********************************************************************/
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
	ClearVars();
	$forumPermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumPermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
	$DICTIONARY_ID = intval($_REQUEST["DICTIONARY_ID"]);
	$bVarsFromForm = false;
	$ID = IntVal($ID);
	$arFields = array();
/*******************************************************************/
	if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && (CFilterUnquotableWords::FilterPerm()) && check_bitrix_sessid())
	{
		$PATTERN_CREATE = trim($PATTERN_CREATE);
		$WORDS = trim($WORDS);
		if ($PATTERN_CREATE && $WORDS)
		{
			$erMsg = array();
			$APPLICATION->ResetException();
			$arFields["DICTIONARY_ID"] = $DICTIONARY_ID;
			if ($PATTERN_CREATE == "WORDS")
			{				
				$arFields["WORDS"] = $WORDS;
				$arFields["PATTERN"] = CFilterUnquotableWords::CreatePattern($WORDS, -1);
				$arFields["PATTERN_CREATE"] = "WORDS";
			}
			elseif ($PATTERN_CREATE == "TRNSL")
			{
				$arFields["WORDS"] = trim($WORDS);
				$arFields["PATTERN"] = CFilterUnquotableWords::CreatePattern($WORDS, 0);
				$arFields["PATTERN_CREATE"] = "TRNSL";
			}
			elseif ($PATTERN_CREATE == "PTTRN")
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
				
			$arFields["REPLACEMENT"] = trim($REPLACEMENT);
			$arFields["DESCRIPTION"] = trim($DESCRIPTION);
			if ($USE_IT != "Y") $USE_IT = "N";
			$arFields["USE_IT"] = $USE_IT;
			if (($ID && CFilterUnquotableWords::Update($ID, $arFields)) || (!$ID && CFilterUnquotableWords::Add($arFields)))
				LocalRedirect("forum_words.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG);
			$err = $APPLICATION->GetException();
			if ($err)
				$APPLICATION->ThrowException($err->GetString()."\n".GetMessage("FLTR_NOT_SAVE"));
		}
		elseif(!$PATTERN_CREATE) 
			$APPLICATION->ThrowException(GetMessage("FLTR_NOT_ACTION"));
		else
			$APPLICATION->ThrowException(GetMessage("FLTR_NOT_WORDS"));
		$bVarsFromForm = true;
	}
	$sDocTitle = ($ID > 0) ? str_replace("#ID#", $ID, GetMessage("FLTR_EDIT")) : GetMessage("FLTR_NEW");
	$APPLICATION->SetTitle($sDocTitle);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/*******************************************************************/
	$str_DICTIONARY_ID = $DICTIONARY_ID;
	$str_DICTIONARY_ID = $DICTIONARY_ID;
	$str_WORDS = "";
	$str_PATTERN = "";
	$str_REPLACEMENT = "";
	$str_DESCRIPTION = "";
	$str_USE_IT = "Y";
	$str_PATTERN_CREATE = "TRNSL";
	if ($ID > 0)
	{
		$db_res = CFilterUnquotableWords::GetList(array(), array("ID" => $ID));
		$db_res->ExtractFields("str_", False);
	}
	if ($bVarsFromForm)
	{
		$DB->InitTableVarsForEdit("b_forum_filter", "", "str_");
	}
	$str_PATTERN_CREATE = strToUpper(trim($str_PATTERN_CREATE));
	$aMenu = array(
		array(
			"TEXT" => GetMessage("FLTR_LIST"),
			"LINK" => "/bitrix/admin/forum_words.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG,
			"ICON" => "btn_list",
		)
	);
	
	if ($ID > 0 && $forumPermissions == "W")
	{
		$aMenu[] = array("SEPARATOR" => "Y");
		$aMenu[] = array(
			"TEXT" => GetMessage("FLTR_NEW"),
			"LINK" => "/bitrix/admin/forum_words_edit.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG,
			"ICON" => "btn_new",
		);
		$aMenu[] = array(
			"TEXT" => GetMessage("FLTR_DEL"), 
			"LINK" => "javascript:if(confirm('".GetMessage("FLTR_DEL_CONFIRM")."')) window.location='/bitrix/admin/forum_words.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG."&action=delete&ID[]=".$ID."&".bitrix_sessid_get()."';",
			"ICON" => "btn_delete",
		);
	}
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
	if ($err = $APPLICATION->GetException())
		CAdminMessage::ShowMessage($err->GetString());
/*******************************************************************/
?><form method="POST" action="<?=$APPLICATION->GetCurPage()?>" name="forum_edit">
	<input type="hidden" name="Update" value="Y"><input type="hidden" name="lang" value="<?=LANG ?>">
	<input type="hidden" name="ID" value="<?=$ID ?>">
	<input type="hidden" name="DICTIONARY_ID" value="<?=htmlspecialcharsbx($str_DICTIONARY_ID)?>" />
	<?=bitrix_sessid_post()?><?
	$aTabs = array(array("DIV" => "edit", "TAB" => $sDocTitle, "ICON" => "forum", "TITLE" => "",));
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
?>
<tr class="adm-detail-required-field">
	<td width="40%"><?=GetMessage("FLTR_SEARCH")?>:</td>
	<td width="60%"><input type="text" name="WORDS" maxlength="255" value="<?=htmlspecialcharsbx($str_WORDS)?>"></td></tr>
<tr>
	<td><?=GetMessage("FLTR_USE_IT")?>: </td><td><input type="checkbox" name="USE_IT" value="Y" <?=$str_USE_IT == "Y" ? "checked" : ""?>></td></tr>
<tr>
	<td><?=GetMessage("FLTR_SEARCH_WHAT")?>:</td><td>
	<?
	$arr = array(
		"reference" => array(
			GetMessage("FLTR_SEARCH_0"),
			GetMessage("FLTR_SEARCH_1"),
			GetMessage("FLTR_SEARCH_2"),
		),
		"reference_id" => array(
			"WORDS",
			"TRNSL",
			"PTTRN",
		)
	);
	echo SelectBoxFromArray("PATTERN_CREATE", $arr, $str_PATTERN_CREATE, "", "");
	?>
</td></tr>
<tr><td><?=GetMessage("FLTR_REPLACEMENT")?>:</td>
	<td><input type="text" name="REPLACEMENT" maxlength="255"  value="<?=htmlspecialcharsbx($str_REPLACEMENT)?>"></td></tr>
<tr class="heading">
	<td colspan="2"><?=GetMessage("FLTR_DESCRIPTION")?>:</td>
</tr>
<tr valign="top">
	<td colspan="2" align="center">
		<textarea style="width:60%; height:150px;" name="DESCRIPTION" wrap="VIRTUAL"><?=htmlspecialcharsbx($str_DESCRIPTION)?></textarea>
	</td>
</tr>
<?$tabControl->EndTab();?>
<?$tabControl->Buttons(
		array(
				"disabled" => (!CFilterUnquotableWords::FilterPerm()),
				"back_url" => "/bitrix/admin/forum_words.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG
			)
	);?>
<?$tabControl->End();?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
