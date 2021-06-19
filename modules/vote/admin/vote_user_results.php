<?
##############################################
# Bitrix Site Manager Forum					#
# Copyright (c) 2002-2009 Bitrix			#
# http://www.bitrixsoft.com					#
# mailto:admin@bitrixsoft.com				#
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if($VOTE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");
ClearVars();
IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";

/********************************************************************
				Actions 
********************************************************************/
$EVENT_ID = intval($EVENT_ID);

if ($REQUEST_METHOD=="GET" && ($save <> '' || $apply)&& $VOTE_RIGHT=="W" && $EVENT_ID>0 && check_bitrix_sessid())
{
	CVoteEvent::SetValid($EVENT_ID, $valid);
	if ($save <> '')
		LocalRedirect("vote_user_votes.php?lang=".LANGUAGE_ID);
}

if (!($event=CVoteEvent::GetByID($EVENT_ID)))
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?><a href="vote_user_list.php?lang=<?=LANGUAGE_ID?>" class="navchain"><?=GetMessage("VOTE_USER_LIST")?></a><font class="navchain">&nbsp;&raquo&nbsp;</font><a href="vote_user_votes.php?lang=<?=LANGUAGE_ID?>" class="navchain"><?=GetMessage("VOTE_RESULTS_LIST")?></a><?
	echo ShowError(GetMessage("VOTE_RESULT_NOT_FOUND"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
$event->ExtractFields();

$VOTE_ID = intval($str_VOTE_ID);
$z = $DB->Query("SELECT ID FROM b_vote WHERE ID='$VOTE_ID'", false, $err_mess.__LINE__);
if (!($zr=$z->Fetch())) 
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?><a href="vote_user_list.php?lang=<?=LANGUAGE_ID?>" class="navchain"><?=GetMessage("VOTE_USER_LIST")?></a><font class="navchain">&nbsp;&raquo&nbsp;</font><a href="vote_user_votes.php?lang=<?=LANGUAGE_ID?>" class="navchain"><?=GetMessage("VOTE_RESULTS_LIST")?></a><?
	echo ShowError(GetMessage("VOTE_NOT_FOUND"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$aTabs = array(
		array("DIV" => "edit1", "TAB"=>GetMessage("VOTE_PARAMS"), "ICON"=>"main_vote_edit", "TITLE"=>GetMessage("VOTE_PARAMS_TITE")),
	);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
GetVoteDataByID($VOTE_ID, $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "N", $template, $res_template);

/********************************************************************
				Form 
********************************************************************/
$APPLICATION->SetTitle(str_replace("#ID#","$EVENT_ID",GetMessage("VOTE_PAGE_TITLE")));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"	=> GetMessage("VOTE_RESULTS_LIST"),
		"LINK"	=> "/bitrix/admin/vote_user_votes.php?lang=".LANGUAGE_ID
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();
echo ShowError($strError);


$tabControl->Begin();

//********************
//General Tab
//********************
$tabControl->BeginNextTab();

?>
<form name="form1" action="" method="get">
<?=bitrix_sessid_post()?>
<input type="hidden" name="EVENT_ID" value="<?=intval($EVENT_ID)?>">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<tr>
		<td  ><?=GetMessage("VOTE_DATE")?></b></font></td>
		<td ><?=$str_DATE_VOTE?></font></td>
	</tr>
	<tr>
		<td  ><?=GetMessage("VOTE_VOTE")?></b></font></td>
		<td ><?
		?>[<a class="tablebodylink" href="vote_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$arVote["ID"]?>" class="tablebodytext"><?=$arVote["ID"]?></a>]&nbsp;<?
		if ($arVote["TITLE"] <> '') echo $arVote["TITLE"];
		elseif ($arVote["DESCRIPTION_TYPE"]=="html")
			echo TruncateText(strip_tags($arVote["~DESCRIPTION"]),200);
		else
			echo TruncateText($arVote["DESCRIPTION"],200);
		?></font></td>
	</tr>
	<tr>
		<td  ><?=GetMessage("VOTE_CHANNEL")?></b></font></td>
		<td ><?="[<a class='tablebodylink' href='vote_channel_edit.php?ID=".$arChannel["ID"]."&lang=".LANGUAGE_ID."'>".$arChannel["ID"]."</a>] ".$arChannel["TITLE"]?></font></td>
	</tr>
	<tr>
		<td  ><?=GetMessage("VOTE_GUEST")?></b></font></td>
		<td ><? 
			if ($str_AUTH_USER_ID>0) : 
				?>[<a class="tablebodylink" title="<?=GetMessage("VOTE_EDIT_USER")?>" href="user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$str_AUTH_USER_ID?>"><?echo $str_AUTH_USER_ID?></a>] (<?=$str_LOGIN?>) <?=$str_AUTH_USER_NAME?> [<?
					if (CModule::IncludeModule("statistic")) : 
						?><a class="tablebodylink" href="guest_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$str_STAT_GUEST_ID?>&set_filter=Y"><?=$str_STAT_GUEST_ID?></a><?
					else : 
						echo $str_STAT_GUEST_ID;
					endif;
				?>]</font><?
			else :
				?><?=GetMessage("VOTE_NOT_AUTHORIZED")?></font><?
			endif;
			?></td>
	</tr>
	<tr>
		<td  ><?=GetMessage("VOTE_SESSION")?></b></font></td>
		<td ><?
			if (CModule::IncludeModule("statistic")) : 
			?><a class="tablebodylink" href="session_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$str_STAT_SESSION_ID?>&set_filter=Y" ><?=$str_STAT_SESSION_ID?></a><?
			else:
			?><?=$str_STAT_SESSION_ID?></font><?
			endif;
			?></td>
	</tr>
	<tr>
		<td  ><?=GetMessage("VOTE_F_IP")?></b></font></td>
		<td ><?=GetWhoisLink($str_IP)?></td>
	</tr>
	<tr> 
		<td><?=GetMessage("VOTE_VALID")?></font></td>
		<td nowrap><input type="checkbox" value="Y" name="valid" <?if ($str_VALID=="Y") echo "checked"?>></td>
	</tr>
<?
$tabControl->EndTab();

$tabControl->Buttons(array("disabled"=>($VOTE_RIGHT<"W"), "back_url"=>"vote_user_votes.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>
<table cellspacing=0 cellpadding=0 width="100%">
	<tr>
		<td width="100%"><?

/********************************************************************
				Header 
********************************************************************/

		if ($arVote["TITLE"] <> ''):
		?><font class="h2"><b><?echo $arVote["TITLE"];?></b></font><br><img src="/bitrix/images/1.gif" width="1" height="6" border=0 alt=""><?
		endif;
		?><font class="smalltext"><?
		if ($arVote["DATE_START"]):
		?><br><?=GetMessage("VOTE_START_DATE")?>&nbsp;<?echo $arVote["DATE_START"]?><?
		endif;
		if ($arVote["DATE_END"] && $arVote["DATE_END"]!="31.12.2030 23:59:59"):
		?><br><?=GetMessage("VOTE_END_DATE")?>&nbsp;<?echo $arVote["DATE_END"]?><?
		endif;
		if ($arVote["LAMP"]=="green") :
		?><br><?=GetMessage("VOTE_VOTE_IS_ACTIVE")?><?
		elseif ($arVote["LAMP"]=="red") :
		?><br><?=GetMessage("VOTE_VOTE_IS_NOT_ACTIVE")?><?
		endif;
		?><br><?=GetMessage("VOTE_VOTES")?>&nbsp;<?=$arVote["COUNTER"]?><?
		?></p></font><font class="text">
		<?if ($arVote["IMAGE_ID"]):?>
		<table cellpadding="0" cellspacing="0" border="0" >
			<tr>
				<td><?echo ShowImage($arVote["IMAGE_ID"], 253, 300, "hspace='3' vspace='3' align='left' border='0'", "", true, GetMessage("VOTE_ENLARGE"));?></td>
				<td  width="0%"><img src="/images/1.gif" width="10" height="1"></td>
			</tr>
			<tr>
				<td colspan=2><img src="/images/1.gif" width="1" height="10"></td>
			</tr>
		</table>
		<? endif;
		echo $arVote["DESCRIPTION"];
		?></td>
	<tr>
		<td><?

/********************************************************************
				Questions
********************************************************************/

		?>
		<p>
		<table cellspacing="0" cellpadding="10" class="tablebody" width="100%">
			<?
			foreach ($arQuestions as $key => $arQuestion):
				$QUESTION_ID = $arQuestion["ID"];

				if (!array_key_exists($QUESTION_ID, $arAnswers))
					continue;

				$show_multiselect = "N";
				$show_dropdown = "N";
			?>
			<tr>
				<td>
					<table cellspacing="0" cellpadding="3">
						<tr>
							<td valign="center" width="0%"><?echo ShowImage($arQuestion["IMAGE_ID"], 50, 50, "hspace='0' vspace='0' align='left' border='0'", "", true, GetMessage("VOTE_ENLARGE"));?></td>
							<td valign="center" width="100%"><font class="text"><b><?=$arQuestion["QUESTION"]?></b></font></td>
						</tr>
						<? 
						foreach ($arAnswers[$QUESTION_ID] as $key => $arAnswer):
						?>
						<tr>
							<td colspan=2><?
							switch ($arAnswer["FIELD_TYPE"]) :
								case 0:
									$field_name = "vote_radio_".$QUESTION_ID;
									$checked = (CVoteEvent::GetAnswer($EVENT_ID,$arAnswer["ID"])) ? "checked" : "";
									?><input type="radio" name="<?=$field_name?>" value="<?=$arAnswer["ID"]?>" <?=$checked?>><font class="text">&nbsp;<?=$arAnswer["MESSAGE"]?></font><?
									break;
								case 1:
									$field_name = "vote_checkbox_".$QUESTION_ID;
									$checked = (CVoteEvent::GetAnswer($EVENT_ID,$arAnswer["ID"])) ? "checked" : "";
									?><input type="checkbox" name="<?=$field_name?>[]" value="<?=$arAnswer["ID"]?>" <?=$checked?>><font class="text">&nbsp;<?=$arAnswer["MESSAGE"]?></font><?
									break;
								case 2:
									if ($show_dropdown!="Y")
									{
										$field_name = "vote_dropdown_".$QUESTION_ID;
										$arDropDown[$QUESTION_ID]["reference"] = $arDropDown[$QUESTION_ID]["~reference"];
										foreach ($arDropDown[$QUESTION_ID]["reference_id"] as $q)
										{
											$selected = CVoteEvent::GetAnswer($EVENT_ID,$q);
											if (intval($selected)>0) break;
										}
										echo SelectBoxFromArray($field_name, $arDropDown[$QUESTION_ID], $selected, "", $arAnswer["FIELD_PARAM"]);
										$show_dropdown = "Y";
									}
									break;
								case 3:
									if ($show_multiselect!="Y")
									{
										$field_name = "vote_multiselect_".$QUESTION_ID;
										$arr = array();
										$arMultiSelect[$QUESTION_ID]["reference"] = $arMultiSelect[$QUESTION_ID]["~reference"];
										foreach ($arMultiSelect[$QUESTION_ID]["reference_id"] as $q)
										{
											$selected = CVoteEvent::GetAnswer($EVENT_ID,$q);
											if (intval($selected)>0) $arr[] = intval($selected);
										}
										echo SelectBoxMFromArray($field_name."[]", $arMultiSelect[$QUESTION_ID], $arr, "", false, $arAnswer["FIELD_HEIGHT"], $arAnswer["FIELD_PARAM"]);
										$show_multiselect = "Y";
									}
									break;
								case 4:
									$field_name = "vote_field_".$arAnswer["ID"];
									$value = CVoteEvent::GetAnswer($EVENT_ID,$arAnswer["ID"]);
									?><?if (trim($arAnswer["MESSAGE"]) <> ''):?><font class="text"><?=$arAnswer["MESSAGE"]?></font><br><?endif?><input type="text" name="<?=$field_name?>" value="<?=htmlspecialcharsbx($value)?>" size="<?=$arAnswer["FIELD_WIDTH"]?>" <?=$arAnswer["FIELD_PARAM"]?>><?
									break;
								case 5:
									$field_name = "vote_memo_".$arAnswer["ID"];
									$text = CVoteEvent::GetAnswer($EVENT_ID,$arAnswer["ID"]);
									?><font class="text"><?if (trim($arAnswer["MESSAGE"]) <> '') echo $arAnswer["MESSAGE"]."<br>"?></font><textarea name="<?=$field_name?>" <?=$arAnswer["FIELD_PARAM"]?> cols="<?=$arAnswer["FIELD_WIDTH"]?>" rows="<?=$arAnswer["FIELD_HEIGHT"]?>"><?=htmlspecialcharsbx($text)?></textarea><?
									break;
							endswitch;
							?></td>
						</tr>
						<? endforeach; ?>
					</table>
				</td>
			</tr>
			<?endforeach;?>
		</table></td>
	</tr>
</table>
<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>