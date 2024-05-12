<?php
#############################################
# Bitrix Site Manager Forum					#
# Copyright (c) 2002-2009 Bitrix			#
# https://www.bitrixsoft.com					#
# mailto:admin@bitrixsoft.com				#
#############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if ($VOTE_RIGHT == "D")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");
ClearVars();
IncludeModuleLangFile(__FILE__);
/********************************************************************
				Actions 
********************************************************************/
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$EVENT_ID = intval($request->getQuery("EVENT_ID"));
if (
	$EVENT_ID <= 0 ||
	!(
		($event = \CVoteEvent::GetByID($EVENT_ID)->fetch()) &&
		$event &&
		GetVoteDataByID($event["VOTE_ID"], $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "N", $template, $res_template)
	)
)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo ShowError(GetMessage("VOTE_RESULT_NOT_FOUND"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
$VOTE_ID = intval($arVote["ID"]);
if ($VOTE_RIGHT=="W" && $request->getRequestMethod() == "GET" && ($request->getQuery("save") <> '' || $request->getQuery("apply") <> '') && check_bitrix_sessid())
{
	\CVoteEvent::SetValid($EVENT_ID, $valid);
	if ($save <> '')
		LocalRedirect("vote_user_votes_table.php?lang=".LANGUAGE_ID."&VOTE_ID=".$VOTE_ID);
}

$tabControl = new CAdminTabControl("tabControl", array(
	array("DIV" => "edit1", "TAB"=>GetMessage("VOTE_PARAMS"), "ICON"=>"main_vote_edit", "TITLE"=>GetMessage("VOTE_PARAMS_TITE")),
), true, true);

$APPLICATION->SetTitle(str_replace("#ID#", $EVENT_ID, GetMessage("VOTE_PAGE_TITLE")));

/********************************************************************
				Form 
********************************************************************/
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$context = new CAdminContextMenu(array(
	array(
		"TEXT"	=> GetMessage("VOTE_RESULTS_LIST"),
		"LINK"	=> "/bitrix/admin/vote_user_votes_table.php?lang=".LANGUAGE_ID."&VOTE_ID=".$VOTE_ID,
		"ICON" => "btn_list"
	)
));
$context->Show();

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
		<td><?=GetMessage("VOTE_DATE")?></td>
		<td><?=$event["DATE_VOTE"]?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_VOTE")?></td>
		<td> [<a class="tablebodylink" href="vote_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$arVote["ID"]?>" class="tablebodytext"> <?=$arVote["ID"]?> </a>]&nbsp;<?
		if ($arVote["TITLE"] <> '') echo $arVote["TITLE"];
		elseif ($arVote["DESCRIPTION_TYPE"]=="html")
			echo TruncateText(strip_tags($arVote["~DESCRIPTION"]),200);
		else
			echo TruncateText($arVote["DESCRIPTION"],200);
		?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_CHANNEL")?></td>
		<td><?="[<a class='tablebodylink' href='vote_channel_edit.php?ID=".$arChannel["ID"]."&lang=".LANGUAGE_ID."'> ".$arChannel["ID"]." </a>] ".$arChannel["TITLE"]?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_GUEST")?></td>
		<td><?php
			if ($event["AUTH_USER_ID"] > 0)
			{
				?>[<a class="tablebodylink" title="<?=GetMessage("VOTE_EDIT_USER")?>" href="user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$event["AUTH_USER_ID"]?>">
					<?=$event["AUTH_USER_ID"]?>
				</a>] (<?=htmlspecialcharsbx($event["LOGIN"])?>) <?=htmlspecialcharsbx($event["AUTH_USER_NAME"])?><?php
				if (CModule::IncludeModule("statistic"))
				{
					?> [ <a class="tablebodylink" href="guest_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$event["STAT_GUEST_ID"]?>&set_filter=Y"><?=$event["STAT_GUEST_ID"]?></a> ]<?php
				}
			}
			else
			{
				echo GetMessage("VOTE_NOT_AUTHORIZED");
			}
			?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_SESSION")?></td>
		<td><?
			if (CModule::IncludeModule("statistic")) : 
			?><a class="tablebodylink" href="session_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$event["STAT_SESSION_ID"]?>&set_filter=Y" ><?=$event["STAT_SESSION_ID"]?></a><?
			else:
			?><?=$event["STAT_SESSION_ID"]?><?
			endif;
			?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_F_IP")?></td>
		<td><?=GetWhoisLink($event["IP"])?></td>
	</tr>
	<tr> 
		<td><?=GetMessage("VOTE_VALID")?></td>
		<td><input type="checkbox" value="Y" name="valid" <?if ($event["VALID"]=="Y") echo "checked"?> /></td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled"=>($VOTE_RIGHT<"W"), "back_url"=>"vote_user_votes_table.php?lang=".LANGUAGE_ID."&VOTE_ID=".$VOTE_ID));
$tabControl->End();
?>
</form>
<?


$tabControl = new CAdminTabControl("tabControl2", array(
	array("DIV" => "edit2", "TAB"=> GetMessage("VOTE_VOTE1"), "TITLE"=>$arVote["TITLE"]),
), true, true);

$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td><?=GetMessage("VOTE_START_DATE")?></td>
		<td><?=$arVote["DATE_START"]?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_END_DATE")?></td>
		<td><?=$arVote["DATE_END"]?></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_ACTUALITY")?></td>
		<td><div class="lamp-<?=$arVote["LAMP"]?>"></div></td>
	</tr>
	<tr>
		<td><?=GetMessage("VOTE_VOTES")?></td>
		<td><?=$arVote["COUNTER"]?></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("VOTE_ANSWERS")?></td>
		<td>
			<ol>
				<?
				foreach ($arQuestions as $key => $arQuestion):
					$QUESTION_ID = $arQuestion["ID"];

					if (!array_key_exists($QUESTION_ID, $arAnswers))
						continue;

					$show_multiselect = "N";
					$show_dropdown = "N";
					?>
					<li>
						<p><?=ShowImage($arQuestion["IMAGE_ID"], 50, 50, "hspace='0' vspace='0' align='left' border='0'", "", true, GetMessage("VOTE_ENLARGE"));?>
						<b><?=$arQuestion["QUESTION"]?></b></p>
							<table cellspacing="0" cellpadding="3">
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
					</li>
				<?endforeach;?>
			</ol>
		</td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->End();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
