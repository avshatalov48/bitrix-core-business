<?
/********************************************************************
	Unquotable words.
********************************************************************/
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
	$forumPermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumPermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
	
	$bVarsFromForm = false;
	$sError = false;
	$TYPE = ($TYPE == "T" ? "T" : "W");
/*******************************************************************/
	if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && (CFilterUnquotableWords::FilterPerm()) && check_bitrix_sessid())
	{
		$erMsg = array(); $arFields = array();
		$APPLICATION->ResetException();
		
		$arFields = array("TITLE" => $_REQUEST["TITLE"]);
		
		if ($_REQUEST["DICTIONARY_ID"] > 0)
		{
			if (!CFilterDictionary::Update($_REQUEST["DICTIONARY_ID"], $arFields))
				$erMsg[] = GetMessage("FLTR_IS_NOT_UPDATE");
			else 
			{
				$db_res = CFilterDictionary::GetList(array(), array("ID" => $_REQUEST["DICTIONARY_ID"]));
				if ($db_res && $res = $db_res->Fetch())
				{
					$arFields["TYPE"] = $res["TYPE"];
				}
			}
		}
		else 
		{
			$arFields["TYPE"] = ($_REQUEST["TYPE"] == "T" ? "T" : "W");
			if (!CFilterDictionary::Add($arFields))
				$erMsg[] = GetMessage("FLTR_IS_NOT_ADD");
		}
		
		$err = $APPLICATION->GetException();
		if (!$err && !empty($_REQUEST['save']))
			LocalRedirect("forum_dictionary.php?TYPE=".$arFields["TYPE"]."&lang=".LANG);
		elseif ($err)
		{
			$bVarsFromForm = true;
			if ($err = $APPLICATION->GetException())
				$sError = $err->GetString();
		}
	}
	$arFields = array();
	$bAdd = true;
	if ($_REQUEST["DICTIONARY_ID"] > 0)
	{
		$db_res = CFilterDictionary::GetList(array(), array("ID" => $_REQUEST["DICTIONARY_ID"]));
		if ($db_res && $res = $db_res->Fetch())
		{
			$arFields = array(
				"ID" => $res["ID"],
				"TYPE" => $res["TYPE"],
				"TITLE" => $res["TITLE"]);
			$bAdd = false;
		}
	}
	if ($bAdd)
	{
		$arFields = array(
			"ID" => 0,
			"TYPE" => "",
			"TITLE" => "");
	}
	if ($bVarsFromForm)
	{
		$arFields = array(
			"ID" => $_REQUEST["DICTIONARY_ID"],
			"TYPE" => $_REQUEST["TYPE"],
			"TITLE" => $_REQUEST["TITLE"]);
	}
	if ($bAdd)
		$APPLICATION->SetTitle(GetMessage("FLTR_NEW"));
	else 
		$APPLICATION->SetTitle(GetMessage("FLTR_UPDATE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/*******************************************************************/
	$aMenu = array(
		array(
			"TEXT" => GetMessage("FLTR_LIST"),
			"LINK" => "/bitrix/admin/forum_dictionary.php?TYPE=".$TYPE."&lang=".LANG,
			"ICON" => "btn_list"));
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
	if ($sError)
		CAdminMessage::ShowMessage($sError);
	
/*******************************************************************/
?><form method="POST" action="<?=$APPLICATION->GetCurPage()?>?" name="forum_edit">
	<input type="hidden" name="Update" value="Y" />
	<input type="hidden" name="lang" value="<?=LANG ?>" />
	<input type="hidden" name="DICTIONARY_ID" value="<?=htmlspecialcharsbx($arFields["ID"])?>" />
	<?=bitrix_sessid_post()?><?
	$aTabs = array(array("DIV" => "edit", "TAB" => GetMessage("FLTR_NEW"), "ICON" => "forum", "TITLE" => ""));
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
?>	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("FLTR_HEAD_TITLE")?>:</td>
		<td width="60%">
			<input type="text" name="TITLE" size="40" maxlength="255" value="<?=htmlspecialcharsbx($arFields["TITLE"])?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("FLTR_HEAD_TYPE")?>:</td>
		<td width="60%">
			<select name="TYPE" <?=(!empty($arFields["ID"]) ? "disabled=\"disabled\"" : "")?>>
				<option value="T" <?=($arFields["TYPE"]=="T"?" selected":"")?>><?=GetMessage("FLTR_HEAD_TYPE_T")?></option>
				<option value="W" <?=($arFields["TYPE"]=="T"?"":" selected")?>><?=GetMessage("FLTR_HEAD_TYPE_W")?></option>
			</select>
		</td>
	</tr>
<?$tabControl->EndTab();?>
<?$tabControl->Buttons(
		array(
				"disabled" => (!CFilterUnquotableWords::FilterPerm()),
				"back_url" => "/bitrix/admin/forum_dictionary.php?TYPE=".$TYPE."&lang=".LANG
			)
	);?>
<?$tabControl->End();?>
</form><br>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>