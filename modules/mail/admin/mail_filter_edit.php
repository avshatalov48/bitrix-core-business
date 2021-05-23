<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2004 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/prolog.php");

ClearVars();
unset($ACTION_VARS);

$message = null;
$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::includeModule('mail');

$aTabs = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("MAIL_FLT_EDT_PARAMS"), "ICON"=>"mail_filter_edit", "TITLE"=>GetMessage("MAIL_FLT_EDT_PARAMS"));

$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("MAIL_FLT_EDT_CONDITIONS"), "ICON"=>"mail_filter_edit", "TITLE"=>GetMessage("MAIL_FLT_EDT_CONDITIONS"));

$aTabs[] = array("DIV" => "edit3", "TAB" =>GetMessage("MAIL_FLT_EDT_ACTIONS"), "ICON"=>"mail_filter_edit", "TITLE"=>GetMessage("MAIL_FLT_EDT_ACTIONS"));


$tabControl = new CAdminTabControl("tabControl", $aTabs);



$err_mess = "File: ".__FILE__."<br>Line: ";

$arModFilter = false;
if($filter_type!="")
{
	$res = CMailFilter::GetFilterList($filter_type);
	$arModFilter = $res->Fetch();
}

$ID=intval($ID);
if($REQUEST_METHOD=="POST" && ($save <> '' || $apply <> '') && $MOD_RIGHT>="W" && check_bitrix_sessid())
{
	$arFields = Array(
		"ACTIVE"			=> $ACTIVE,
		"MAILBOX_ID"		=> $MAILBOX_ID,
		"PARENT_FILTER_ID"	=> false,
		"NAME"				=> $NAME,
		"SORT"				=> $SORT,
		"WHEN_MAIL_RECEIVED"=> $WHEN_MAIL_RECEIVED,
		"WHEN_MANUALLY_RUN"	=> $WHEN_MANUALLY_RUN,
		"SPAM_RATING"		=> $SPAM_RATING,
		"SPAM_RATING_TYPE"	=> $SPAM_RATING_TYPE,
		"MESSAGE_SIZE"		=> $MESSAGE_SIZE,
		"MESSAGE_SIZE_TYPE"	=> $MESSAGE_SIZE_TYPE,
		"MESSAGE_SIZE_UNIT"	=> $MESSAGE_SIZE_UNIT,
		"DESCRIPTION"		=> $DESCRIPTION,
		"CONDITIONS"		=> $CONDITIONS,
		"ACTION_STOP_EXEC"	=> $ACTION_STOP_EXEC,
		"ACTION_DELETE_MESSAGE"=> $ACTION_DELETE_MESSAGE,
		"ACTION_READ"		=> $ACTION_READ,
		"ACTION_SPAM"		=> $ACTION_SPAM,
		"ACTION_TYPE"		=> ""
		);

	if($USER->IsAdmin())
	{
		$arFields["PHP_CONDITION"] = $PHP_CONDITION;
		$arFields["ACTION_PHP"]	= $ACTION_PHP;
	}

	if($arModFilter)
	{
		$arFields["ACTION_TYPE"] = $arModFilter["ID"];
		$ACTION_VARS = call_user_func($arModFilter["PREPARE_RESULT_FUNC"]);
		$arFields["ACTION_VARS"] = $ACTION_VARS;
	}

	if($ID>0)
		$res = CMailFilter::Update($ID, $arFields);
	else
	{
		$ID = CMailFilter::Add($arFields);
		$res = ($ID>0);
	}


	if(!$res)
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("MAIL_FLT_EDT_ERROR"), $e);
	}
	else
	{
		//$strError .= CMailError::GetErrorsText();
		//if(strlen($strError)<=0)
		//{
			if($save <> '')
				LocalRedirect("mail_filter_admin.php?lang=".LANG);
			else
				LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ID=".$ID."&tabControl_active_tab=".urlencode($tabControl_active_tab));
		//}
	}
}

if($ID !== 0)
{
	$mf = CMailFilter::GetByID($ID);
	if (!$ar_res = $mf->ExtractFields("str_"))
	{
		$ID = 0;
	}
	else
	{
		$filter_type = $ar_res["ACTION_TYPE"];
		if ($filter_type <> '')
		{
			$res = CMailFilter::GetFilterList($filter_type);
			$arModFilter = $res->Fetch();
		}
	}
}

if(!$message)
{
	if(!isset($ACTIVE))
		$ACTIVE="Y";
	if(!isset($PORT))
		$PORT="110";
	if(!isset($SORT))
		$SORT="500";
	if(!isset($MAILBOX_ID))
		$MAILBOX_ID = $find_mailbox_id;
	if($ID>0)
		$ACTION_VARS = $ar_res["ACTION_VARS"];
}

if($message || $ID==0)
{
	$DB->InitTableVarsForEdit("b_mail_filter", "", "str_", "", true);
	$ar_CONDITIONS = $CONDITIONS;
}
else
{
	$ar_CONDITIONS = Array();
	if($ID>0)
	{
		$res = CMailFilterCondition::GetList(Array("id"=>"asc"), Array("FILTER_ID"=>$ID));
		while($ar = $res->Fetch())
			$ar_CONDITIONS[$ar["ID"]] = $ar;
	}
}

if(!is_array($ar_CONDITIONS))
	$ar_CONDITIONS = Array();

if(!$message)
{
	$ar_CONDITIONS["n1"] = Array();
	$ar_CONDITIONS["n2"] = Array();
	$ar_CONDITIONS["n3"] = Array();
}

$sDocTitle = ($ID>0) ? preg_replace("'#ID#'i", $ID, GetMessage("MAIL_FLT_EDT_TITILE_1")) : GetMessage("MAIL_FLT_EDT_TITILE_2");

$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIL_FLT_EDT_BACK_LINK"),
		"LINK"=>"mail_filter_admin.php?lang=".LANG
	)
);

if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIL_FLT_EDT_NEW"),
		"LINK"=>"mail_filter_edit.php?lang=".LANG
	);

	if ($MOD_RIGHT=="W")
	{
		$aMenu[] = array(
			"ICON" => "btn_delete",
			"TEXT"=>GetMessage("MAIL_FLT_EDT_DEL"),
			"LINK"=>"javascript:if(confirm('".GetMessage("MAIL_FLT_EDT_DEL_CONFIRM")."'))window.location='mail_filter_admin.php?action=delete&ID=".$ID."&lang=".LANG."&".bitrix_sessid_get()."';",
		);
	}
}
//echo ShowSubMenu($aMenu);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
	echo $message->Show();

$tabControl->Begin();
?>
<form name="form1" method="POST" action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?echo GetFilterHiddens("find_");?>
<?$tabControl->BeginNextTab();?>

	<?if($ID>0):?>
	<tr>
		<td><?echo GetMessage("MAIL_FLT_EDT_ID")?></td>
		<td><?echo $str_ID?></td>
	</tr>
	<?endif?>
	<?if($str_TIMESTAMP_X <> ''):?>
	<tr>
		<td><?echo GetMessage("MAIL_FLT_EDT_DATECH")?></td>
		<td><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<? endif; ?>
	<tr>
		<td width="40%"><?echo GetMessage("MAIL_FLT_EDT_MBOX")?> </td>
		<td width="60%">
		<?$mb = CMailBox::GetList(Array("NAME"=>"ASC", "ID"=>"ASC"), array('USER_ID' => 0)); ?>
		<select name="MAILBOX_ID">
		<?
			ClearVars("mb_");
			while($mb->ExtractFields("mb_")):
				?><option value="<?echo $mb_ID?>"<?if($str_MAILBOX_ID==$mb_ID)echo " selected"?>><?echo $mb_NAME?> [<?echo $mb_ID?>]</option><?
			endwhile;
		?>
		</select>
		
		<a href="mail_mailbox_edit.php?lang=<?=LANG?>" title="<?echo GetMessage("MAIL_FLT_EDT_MBOX_NEW")?>"><?echo GetMessage("MAIL_FLT_EDT_MBOX_NEW_LINK")?></a>
		
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIL_FLT_EDT_ACT")?></td>
		<td><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("MAIL_FLT_EDT_NAME")?></td>
		<td><input type="text" name="NAME" size="53" maxlength="255" value="<?=$str_NAME?>"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIL_FLT_EDT_DESC")?></td>
		<td><textarea name="DESCRIPTION" cols="40" rows="5"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIL_FLT_EDT_SORT")?></td>
		<td><input type="text" name="SORT" size="4" maxlength="5" value="<?=$str_SORT?>"></td>
	</tr>

	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIL_FLT_EDT_WHEN")?></td>
		<td>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" name="WHEN_MAIL_RECEIVED" value="Y" id="WHEN_MAIL_RECEIVED"<?if($str_WHEN_MAIL_RECEIVED=="Y")echo " checked"?>></div>
					<div class="adm-list-label"><label for="WHEN_MAIL_RECEIVED"><?echo GetMessage("MAIL_FLT_EDT_WHEN_RETR")?></label></div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" name="WHEN_MANUALLY_RUN" value="Y" id="WHEN_MANUALLY_RUN"<?if($str_WHEN_MANUALLY_RUN=="Y")echo " checked"?>></div>
					<div class="adm-list-label"><label for="WHEN_MANUALLY_RUN"><?echo GetMessage("MAIL_FLT_EDT_WHEN_MANUAL")?></label></div>
				</div>
			</div>
		</td>
	</tr>
<?$tabControl->BeginNextTab();?>
	<tr>
		<td align="center" colspan="2">
		<?echo GetMessage("MAIL_FLT_EDT_CONDITIONS_DESC")?></td>
	</tr>
	<?foreach($ar_CONDITIONS as $key=>$COND):?>
	<tr>
		<td class="adm-detail-valign-top">
		<?//if(intval($key)>0)echo intval($key).".";else echo GetMessage("MAIL_FLT_EDT_NEW_COND"); ?>
		<select name="CONDITIONS[<?=htmlspecialcharsbx($key)?>][TYPE]" style="width:120px">
			<option value=""><?echo GetMessage("MAIL_FLT_EDT_COND_TYPE")?></option>
			<option value="SENDER"<?if($COND["TYPE"]=="SENDER")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_TYPE_SENDER")?></option>
			<option value="RECIPIENT"<?if($COND["TYPE"]=="RECIPIENT")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_TYPE_RECIPIENT")?></option>
			<option value="SUBJECT"<?if($COND["TYPE"]=="SUBJECT")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_TYPE_SUBJECT")?></option>
			<option value="BODY"<?if($COND["TYPE"]=="BODY")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_TYPE_BODY")?></option>
			<option value="HEADER"<?if($COND["TYPE"]=="HEADER")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_TYPE_HEADER")?></option>
			<option value="ALL"<?if($COND["TYPE"]=="ALL")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_TYPE_ALL")?></option>
			<option value="ATTACHMENT"<?if($COND["TYPE"]=="ATTACHMENT")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_TYPE_ATTACH")?></option>
		</select>

		<select name="CONDITIONS[<?=htmlspecialcharsbx($key)?>][COMPARE_TYPE]" style="width:120px">
			<option value="CONTAIN"<?if($COND["COMPARE_TYPE"]!="EQUAL" && $COND["COMPARE_TYPE"]!="NOT_CONTAIN" && $COND["COMPARE_TYPE"]!="NOT_EQUAL")echo " selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_CONTAIN")?></option>
			<option value="NOT_CONTAIN"<?if($COND["COMPARE_TYPE"]=="NOT_CONTAIN")echo " selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_NOTCONTAIN")?></option>
			<option value="EQUAL"<?if($COND["COMPARE_TYPE"]=="EQUAL")echo " selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_EQUAL")?></option>
			<option value="NOT_EQUAL"<?if($COND["COMPARE_TYPE"]=="NOT_EQUAL")echo " selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_NOTEQUAL")?></option>
			<option value="REGEXP"<?if($COND["COMPARE_TYPE"]=="REGEXP")echo " selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_REGEXP")?></option>
		</select>
		</td>
		<td class="adm-detail-valign-top">
			<textarea name="CONDITIONS[<?=htmlspecialcharsbx($key)?>][STRINGS]" style="width:70%" rows="3"><?echo htmlspecialcharsbx($COND["STRINGS"])?></textarea><br>
		</td>
	</tr>
	<?endforeach?>
	<tr>
		<td width="40%"><?echo GetMessage("MAIL_FLT_EDT_COND_SPAM")?></td>
		<td width="60%">
		<select name="SPAM_RATING_TYPE">
			<option value="&lt;"><?echo GetMessage("MAIL_FLT_EDT_COND_LESS")?></option>
			<option value="&gt;"<?if($str_SPAM_RATING_TYPE=="&gt;")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_GREATER")?></option>
		</select>
		<input type="text" name="SPAM_RATING" size="4" maxlength="5" value="<?=$str_SPAM_RATING?>">%</td>
	</tr>

	<tr>
		<td><?echo GetMessage("MAIL_FLT_EDT_COND_SIZE")?></td>
		<td>
		<select name="MESSAGE_SIZE_TYPE">
			<option value="&lt;"><?echo GetMessage("MAIL_FLT_EDT_COND_LESS")?></option>
			<option value="&gt;"<?if($str_MESSAGE_SIZE_TYPE=="&gt;")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_GREATER")?></option>
		</select>
		<input type="text" name="MESSAGE_SIZE" size="10" maxlength="18" value="<?=$str_MESSAGE_SIZE?>"><select name="MESSAGE_SIZE_UNIT">
			<option value="b"><?echo GetMessage("MAIL_FLT_EDT_COND_SIZE_B")?></option>
			<option value="k"<?if($str_MESSAGE_SIZE_UNIT=="k")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_SIZE_KB")?></option>
			<option value="m"<?if($str_MESSAGE_SIZE_UNIT=="m")echo" selected"?>><?echo GetMessage("MAIL_FLT_EDT_COND_SIZE_MB")?></option>
		</select>
		</td>
	</tr>
<?if($USER->IsAdmin()):?>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIL_FLT_EDT_COND_PHP")?></td>
		<td><textarea style="width:70%" rows="5" wrap="off" name="PHP_CONDITION"><?=$str_PHP_CONDITION?></textarea></td>
	</tr>
<?endif?>
	<?
	if($arModFilter && $arModFilter["ACTION_INTERFACE"] <> ''):

		$arACTION_VARS = explode("&", $ACTION_VARS);
		for($i = 0, $n = count($arACTION_VARS); $i < $n; $i++)
		{
			$v = $arACTION_VARS[$i];
			if($pos = mb_strpos($v, "="))
				${mb_substr($v, 0, $pos)} = urldecode(mb_substr($v, $pos + 1));
		}

		$MAILBOX_LID = "";
		if($str_MAILBOX_ID!="")
		{
			$dbmb = CMailBox::GetByID($str_MAILBOX_ID);
			if($armb = $dbmb->Fetch())
				$MAILBOX_LID = $armb["LID"];
		}
	?>

		<tr class="heading">
			<td align="center" colspan="2"><b><?echo GetMessage("MAIL_FLT_EDT_SETTINGS")?></b><br>
			<input type="hidden" name="filter_type" value="<?=htmlspecialcharsbx($filter_type);?>">
			<?=htmlspecialcharsbx($arModFilter["NAME"])?></td>
		</tr>

		<?include($arModFilter["ACTION_INTERFACE"]);?>
	<?endif?>

<?$tabControl->BeginNextTab();?>


	<tr>
		<td width="40%"><?echo GetMessage("MAIL_FLT_EDT_ACT_STATUS")?></td>
		<td width="60%">
			<select name="ACTION_READ">
				<option value=""><?echo GetMessage("MAIL_FLT_EDT_ACT_NC")?></option>
				<option value="Y"<?if($str_ACTION_READ=="Y")echo " selected"?>><?echo GetMessage("MAIL_FLT_EDT_ACT_STATUS_READ")?></option>
				<option value="N"<?if($str_ACTION_READ=="N")echo " selected"?>><?echo GetMessage("MAIL_FLT_EDT_ACT_STATUS_NOTREAD")?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("MAIL_FLT_EDT_ACT_MARK")?></td>
		<td>
			<select name="ACTION_SPAM">
				<option value=""><?echo GetMessage("MAIL_FLT_EDT_ACT_NC")?></option>
				<option value="Y"<?if($str_ACTION_SPAM=="Y")echo " selected"?>><?echo GetMessage("MAIL_FLT_EDT_ACT_MARK_SPAM")?></option>
				<option value="N"<?if($str_ACTION_SPAM=="N")echo " selected"?>><?echo GetMessage("MAIL_FLT_EDT_ACT_MARK_NOTSPAM")?></option>
			</select>
		</td>
	</tr>

<?if($USER->IsAdmin()):?>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIL_FLT_EDT_ACT_PHP")?></td>
		<td><textarea cols="40" rows="8" name="ACTION_PHP" wrap="off"><?=$str_ACTION_PHP?></textarea></td>
	</tr>
<?endif?>
	<tr>
		<td><?echo GetMessage("MAIL_FLT_EDT_ACT_DELETE")?></td>
		<td><input type="checkbox" name="ACTION_DELETE_MESSAGE" value="Y"<?if($str_ACTION_DELETE_MESSAGE=="Y")echo " checked"?>></td>
	</tr>

	<tr>
		<td><?echo GetMessage("MAIL_FLT_EDT_ACT_CANCEL")?></td>
		<td><input type="checkbox" name="ACTION_STOP_EXEC" value="Y"<?if($str_ACTION_STOP_EXEC=="Y")echo " checked"?>></td>
	</tr>
<?
$tabControl->Buttons(Array("disabled"=>$MOD_RIGHT<"W", "back_url" =>"/bitrix/admin/mail_filter_admin.php?lang=".LANG));
$tabControl->End();
?>

</form>
<?$tabControl->ShowWarnings("form1", $message);?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>