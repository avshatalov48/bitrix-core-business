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
	$forumPermWrite = CFilterUnquotableWords::FilterPerm();
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
	
	$bVarsFromForm = false;
	$ID = IntVal($ID);
	$ID = ($ID < 0) ? 0 : $ID;
	$DICTIONARY_ID = intVal($_REQUEST["DICTIONARY_ID"]);
	$DICTIONARY_ID = ($DICTIONARY_ID < 0) ? 0 : $DICTIONARY_ID;
	$arFields = array();
/*******************************************************************/
	if ($REQUEST_METHOD=="POST" && ($Update = 'Y') && $forumPermWrite && check_bitrix_sessid())
	{
		$erMsg = array();
		$APPLICATION->ResetException();
		$arFields["LETTER"] = trim($LETTER);
		$arFields["REPLACEMENT"] = trim($REPLACEMENT);
		$arFields["DICTIONARY_ID"] = $DICTIONARY_ID;
		if ((($ID>0) && (CFilterLetter::Update($ID, $arFields))) || (CFilterLetter::Add($arFields)))
			LocalRedirect("forum_letter.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG);
		
		if ($ex = $APPLICATION->GetException())
			$APPLICATION->ThrowException($ex->GetString());
		else
			$APPLICATION->ThrowException(GetMessage("FLTR_NOT_SAVE"));
	}
	$bVarsFromForm = true;
	$sDocTitle = ($ID > 0) ? str_replace("#ID#", $ID, GetMessage("FLTR_EDIT")) : GetMessage("FLTR_NEW");
	$APPLICATION->SetTitle($sDocTitle);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/*******************************************************************/
	$str_LETTER = "";
	$str_REPLACEMENT = "";
	$str_DICTIONARY_ID = $DICTIONARY_ID;
	if ($ID > 0)
	{
		$db_res = CFilterLetter::GetList(array(), array("ID" => $ID));
		$db_res->ExtractFields("str_", False);
	}
	
	if ($bVarsFromForm)
		$DB->InitTableVarsForEdit("b_forum_letter", "", "str_");
	
	$aMenu = array(
		array(
			"TEXT" => GetMessage("FLTR_LIST"),
			"LINK" => "/bitrix/admin/forum_letter.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG,
			"ICON" => "btn_list",
		)
	);
	
	if ($ID > 0 && $forumPermWrite)
	{
		$aMenu[] = array("SEPARATOR" => "Y");
		$aMenu[] = array(
			"TEXT" => GetMessage("FLTR_NEW"),
			"LINK" => "/bitrix/admin/forum_dictionary_edit.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG,
			"ICON" => "btn_new",
		);
		$aMenu[] = array(
			"TEXT" => GetMessage("FLTR_DEL"), 
			"LINK" => "javascript:if(confirm('".GetMessage("FLTR_DEL_CONFIRM")."')) window.location='/bitrix/admin/forum_letter.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG."&action=delete&ID[]=".$ID."&".bitrix_sessid_get()."';",
			"ICON" => "btn_delete",
		);
	}
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
	if ($err = $APPLICATION->GetException())
		CAdminMessage::ShowMessage($err->GetString());
/*******************************************************************/
	$db_res =  CFilterDictionary::GetList(array(), array("TYPE" => "T"));
	$Dict = array();
	while ($res = $db_res->Fetch())
	{
		$Dict["reference_id"][] = $res["ID"];
		$Dict["reference"][] = $res["TITLE"];
	}

?><form method="POST" action="<?=$APPLICATION->GetCurPage()?>" name="forum_edit">
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?=LANG ?>">
	<input type="hidden" name="ID" value="<?=$ID ?>">
	<?=bitrix_sessid_post()?><?
	$aTabs = array(array("DIV" => "edit", "TAB" => GetMessage("FLTR_TITLE"), "ICON" => "forum", "TITLE" => $sDocTitle,));
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("FLTR_DICTIONARY")?>:</td>
		<td width="60%"><?=SelectBoxFromArray("DICTIONARY_ID", $Dict, $str_DICTIONARY_ID)?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("FLTR_LETTER")?>:</td>
		<td width="60%">
			<input type="text" name="LETTER" size="40" maxlength="145" value="<?=htmlspecialcharsbx($str_LETTER)?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("FLTR_REPLACEMENT")?>:</td>
		<td width="60%">
			<input type="text" name="REPLACEMENT" size="40" maxlength="255" value="<?=htmlspecialcharsbx($str_REPLACEMENT)?>">
		</td>
	</tr>
<?$tabControl->EndTab();?>
<?$tabControl->Buttons(
		array(
				"disabled" => (!$forumPermWrite),
				"back_url" => "/bitrix/admin/forum_letter.php?DICTIONARY_ID=".$DICTIONARY_ID."&lang=".LANG
			)
	);?>
<?$tabControl->End();?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
