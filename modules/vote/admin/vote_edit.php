<?
/*
##############################################
# Bitrix: SiteManager						 #
# Copyright (c) 2004 - 2016 Bitrix			 #
# https://www.bitrixsoft.com				 #
# mailto:admin@bitrix.ru					 #
##############################################
*/
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
use \Bitrix\Vote\Vote\EventLimits;
use \Bitrix\Vote\Vote\Option;
use \Bitrix\Main\Localization\Loc;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
CModule::IncludeModule("vote");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");
IncludeModuleLangFile(__FILE__);
Loc::loadMessages(__FILE__);

$err_mess = "File: ".__FILE__."<br>Line: ";
$old_module_version = \CVote::IsOldVersion();

$tabControl = new CAdminTabControl("tabControl", array(
	array("DIV" => "edit1", "TAB" => GetMessage("VOTE_VOTE"), "ICON" => "main_vote_edit", "TITLE" => GetMessage("VOTE_VOTE")),
	array("DIV" => "edit2", "TAB" => GetMessage("VOTE_PROP"), "ICON" => "main_vote_edit", "TITLE" => GetMessage("VOTE_PARAMS")),
	array("DIV" => "edit3", "TAB" => GetMessage("VOTE_UNIQUE_PARAMS"), "ICON" => "main_vote_edit", "TITLE" => GetMessage("VOTE_UNIQUE_PARAMS"))));

//region Check access
/* @var $request \Bitrix\Main\HttpRequest */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$message = false;
$channels = array();
$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
$db_res = \Bitrix\Vote\Channel::getList(array(
	"select" => array("*"),
	"filter" => ($VOTE_RIGHT < "W" ? array(
		"ACTIVE" => "Y",
		"HIDDEN" => "N",
		">=PERMISSION.PERMISSION" => 4,
		"PERMISSION.GROUP_ID" => $USER->GetUserGroupArray()
	) : array()),
	"order" => array(
		"TITLE" => "ASC"
	),
	"group" => array("ID")
));
while ($res = $db_res->GetNext())
{
	$channels[$res["ID"]] = $res;
}
if (empty($channels))
{
	$APPLICATION->SetTitle(GetMessage("VOTE_NEW_RECORD"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("VOTE_CHANNEL_NOT_FOUND"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
//endregion
//region Actions
try
{
	$voteId = $request->getQuery("ID");
	$copyVoteId = $request->getQuery("COPY_ID");
	$channelId = $request->getQuery("channelId") ?: $request->getQuery("CHANNEL_ID");
	$saveAction = false;
	$fields = array(
		"AUTHOR_ID"		    => 0,
		"CHANNEL_ID"		=> $channelId,
		"C_SORT"			=> null,
		"ACTIVE"			=> "Y",
		"ANONYMITY"			=> \Bitrix\Vote\Vote\Anonymity::PUBLICLY,
		"DATE_START"		=> null,
		"DATE_END"			=> null,
		"TITLE"				=> null,
		"DESCRIPTION"		=> null,
		"DESCRIPTION_TYPE"	=> "text",
		"IMAGE_ID"			=> null,
		"EVENT1"			=> "vote",
		"EVENT2"			=> null,
		"EVENT3"			=> null,
		"UNIQUE_TYPE"		=> EventLimits::BY_USER_ID|EventLimits::BY_IP,
		"KEEP_IP_SEC"		=> null,
		"OPTIONS"			=> Option::ALLOW_REVOTE,
		"NOTIFY"			=> null,
		"URL"				=> null,
		"TEMPLATE" => null,
		"RESULT_TEMPLATE" => null
	);

	if ($request->getRequestMethod() == "POST" && (
		($request->getPost("save") || $request->getPost("apply"))))
	{
		if (!check_bitrix_sessid())
			throw new \Bitrix\Main\ArgumentException("Bad sessid.");
		$saveAction = true;
		$voteId = $request->getPost("ID");
		$copyVoteId = $request->getPost("COPY_ID");
		$channelId = $request->getPost("CHANNEL_ID");
	}
	if ($voteId > 0)
	{
		$vote = \Bitrix\Vote\Vote::loadFromId($voteId);
		if (!$vote->canEdit($USER->GetID()))
			throw new \Bitrix\Main\ArgumentException(GetMessage("ACCESS_DENIED"), "Access denied.");
		$channelId = ($saveAction ? $channelId : ($channelId ?: $vote->get("CHANNEL_ID")));
	}
	else if ($copyVoteId > 0)
	{
		$copyVote = \Bitrix\Vote\Vote::loadFromId($copyVoteId);
		global $USER;
		if (!$copyVote->canRead($USER->GetID()))
			throw new \Bitrix\Main\ArgumentException(GetMessage("ACCESS_DENIED"), "Access denied.");
		$channelId = ($saveAction ? $channelId : ($channelId ?: $copyVote->get("CHANNEL_ID")));
	}
	$fields["CHANNEL_ID"] = $channelId;
	/* @var \Bitrix\Vote\Channel $channel */
	$channel = \Bitrix\Vote\Channel::loadFromId($channelId);
	if (!isset($vote) && !$channel->canEditVote($USER->getId()))
		throw new \Bitrix\Main\ArgumentException(GetMessage("ACCESS_DENIED"), "Access denied.");

	$t = (isset($vote)? $vote : (isset($copyVote) ? $copyVote : null));
	if ($t)
	{
		foreach ($fields as $key => &$value)
			$value = $t->get($key);
	}
	if ($request->getRequestMethod() == "GET" && $request->get("action") == "reset")
	{
		if (!check_bitrix_sessid())
			throw new \Bitrix\Main\ArgumentException("Bad sessid.");
		\CVote::Reset($voteId);
		$url = $APPLICATION->GetCurPage() . "?lang=" . LANGUAGE_ID . "&ID=" . $ID . "&" . $tabControl->ActiveTabParam() .
			(!empty($request->get("return_url")) ? "&return_url=" . urlencode($request->get("return_url")) : "");
		LocalRedirect($url);
	}
	if ($saveAction)
	{
		foreach ($fields as $key => &$value)
		{
			if ($request->getPost($key) !== null)
				$value = $request->getPost($key);
		}

		$arIMAGE_ID = array();
		if (array_key_exists("IMAGE_ID", $_FILES))
			$arIMAGE_ID = $_FILES["IMAGE_ID"];
		elseif ($request->getPost("IMAGE_ID"))
		{
			$arIMAGE_ID = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$request->getPost("IMAGE_ID"));
			$arIMAGE_ID["COPY_FILE"] = "Y";
		}
		$arIMAGE_ID["del"] = $request->getPost("IMAGE_ID_del");
		$arIMAGE_ID["description"] = $request->getPost("IMAGE_ID_descr");

		$fields["IMAGE_ID"] = $arIMAGE_ID;
		foreach (["UNIQUE_TYPE", "OPTIONS"] as $k)
		{
			$fields[$k] = 0;
			if (is_array($request->getPost($k)))
			{
				foreach ($request->getPost($k) as $v)
				{
					$fields[$k] |= $v;
				}
			}
		}
		$ID = isset($vote) ? $vote->getId() : 0;
		if ($ID <= 0)
		{
			$fields["AUTHOR_ID"] = $GLOBALS["USER"]->GetId();
			$result = $ID = CVote::Add($fields);
		}
		else
		{
			$result = CVote::Update($ID, $fields);
		}
		if ($result)
		{
			if (isset($copyVote))
			{
				global $DB;
				$newID = $ID;
				$DB->Update("b_vote", array("COUNTER" => "0"), "WHERE ID=" . $newID, $err_mess . __LINE__);
				if ($copyVote->get("IMAGE_ID") > 0 &&
					empty($arIMAGE_ID["name"]) &&
					$arIMAGE_ID["del"] != "Y"
				)
				{
					$newImageId = CFile::CopyFile($copyVote->get("IMAGE_ID"));
					if ($newImageId)
					{
						$DB->Update("b_vote", array("IMAGE_ID" => $newImageId), "WHERE ID=" . $newID, $err_mess . __LINE__);
					}
				}

				$state = true;
				$rQuestions = CVoteQuestion::GetList($copyVote->getId());
				while ($arQuestion = $rQuestions->Fetch())
				{
					$state = $state && (CVoteQuestion::Copy($arQuestion["ID"], $newID) !== false);
				}
			}

			$url = $APPLICATION->GetCurPage() . "?lang=" . LANGUAGE_ID . "&ID=" . $ID . "&" . $tabControl->ActiveTabParam() .
				(!empty($_REQUEST["return_url"]) ? "&return_url=" . urlencode($_REQUEST["return_url"]) : "");
			if ($request->getPost("save") !== null)
			{
				$url = ($request->getPost("return_url") ?: "vote_list.php?lang=" . LANGUAGE_ID . "&find_channel_id=" . $channelId . "&set_filter=Y");
			}
			LocalRedirect($url);
		}
		$e = $APPLICATION->GetException();
		$message = new CAdminMessage(GetMessage("VOTE_GOT_ERROR"), $e);
	}
}
catch(Exception $e)
{
	$APPLICATION->SetTitle(GetMessage("VOTE_NEW_RECORD"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($e->getMessage());
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
//endregion

$APPLICATION->SetTitle(isset($vote) ? GetMessage("VOTE_EDIT_RECORD", array("#ID#" => $vote->getId())) : GetMessage("VOTE_NEW_RECORD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (is_null($fields["C_SORT"]))
	$fields["C_SORT"] = CVote::GetNextSort($channel->getId());
if (is_null($fields["DATE_START"]))
	$fields["DATE_START"] = ($channel->get("VOTE_SINGLE") != "N" ? CVote::GetNextStartDate($channel->get("VOTE_SINGLE")) : "");
if (is_null($fields["EVENT2"]))
	$fields["EVENT2"] = $channel->get("SYMBOLIC_NAME");
$tmp = $fields;

foreach ($tmp as $key => $val):
	$fields["~".$key] = $val;
	$fields[$key] = htmlspecialcharsEx($val);
endforeach;
//region Top menu
/* @var $vote \Bitrix\Vote\Vote */
if (isset($vote))
{
	$ID = $vote->getId();
	$toolbar = array(
		array(
			"TEXT"	=> GetMessage("VOTE_VOTE_LIST"),
			"TITLE"	=> GetMessage("VOTE_VOTE_LIST"),
			"LINK"	=> "vote_list.php?lang=".LANGUAGE_ID."&amp;find_channel_id=".$fields["CHANNEL_ID"],
			"ICON" => "btn_list"),
		array(
			"TEXT"	=> GetMessage("VOTE_COPY"),
			"TITLE"	=> GetMessage("VOTE_COPY_TITLE"),
			"LINK"	=> "vote_edit.php?lang=".LANGUAGE_ID."&amp;COPY_ID=$ID&".bitrix_sessid_get(),
			"ICON" => "btn_copy"),
		array(
			"TEXT"	=> GetMessage("VOTE_DELETE"),
			"TITLE"	=> GetMessage("VOTE_DELETE_RECORD"),
			"LINK"	=> "javascript:if(confirm('".GetMessage("VOTE_DELETE_RECORD_CONFIRM")."')) window.location='/bitrix/admin/vote_list.php?action=delete&ID=".$ID."&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
			"ICON" => "btn_delete"));
	if ($vote["COUNTER"] > 0)
	{
		array_push($toolbar, array(
			"TEXT" => GetMessage("VOTE_VOTES_DROPDOWN", array("COUNTER" => $vote["COUNTER"])),
			"MENU" => array(
				array(
					"TEXT"	=> GetMessage("VOTE_VOTES_GOTO_VIEW"),
					"LINK"	=> "/bitrix/admin/vote_results.php?lang=".LANGUAGE_ID."&VOTE_ID=".$ID),
				array(
					"TEXT"	=> GetMessage("VOTE_VOTES_GOTO_LIST"),
					"LINK"	=> "/bitrix/admin/vote_user_votes_table.php?lang=".LANGUAGE_ID."&VOTE_ID=".$ID,
					"ICON" => "btn_list"),
				array(
					"TEXT"	=> GetMessage("VOTE_VOTES_RESET"),
					"LINK"	=> "javascript:if(confirm('".GetMessage("VOTE_RESET_RECORD_CONFIRM")."')) window.location='/bitrix/admin/vote_edit.php?ID=".$ID."&action=reset&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
					"ICON" => "btn_refresh"),
				array(
					"TEXT"	=> GetMessage("VOTE_VOTES_EXPORT"),
					"LINK"	=> "vote_user_votes.php?lang=".LANGUAGE_ID."&find_vote_id=$ID&export=xls",
					"ICON" => "btn_excel"),
				array(
					"TEXT"	=> GetMessage("VOTE_VOTES_EXPORT_2"),
					"LINK"	=> "vote_user_votes_table.php?lang=".LANGUAGE_ID."&VOTE_ID=$ID&mode=excel",
					"ICON" => "btn_excel")
			))
		);
	}
	(new CAdminContextMenu($toolbar))->Show();

	$cnt = count($vote->getQuestions());
	$context = new CAdminContextMenu(array(
		array(
			"TEXT"	=> GetMessage("VOTE_QUESTIONS").($cnt > 0 ?" [".$cnt."]":""),
			"TITLE"	=> GetMessage("VOTE_QUESTIONS_TITLE"),
			"LINK"	=> "/bitrix/admin/vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID=".$ID),
		array(
			"TEXT"	=> GetMessage("VOTE_QUESTIONS_ADD"),
			"TITLE"	=> GetMessage("VOTE_QUESTIONS_ADD_TITLE"),
			"LINK"	=> "/bitrix/admin/vote_question_edit.php?lang=".LANGUAGE_ID."&VOTE_ID=$ID",
			"ICON" => "btn_new")));
	$context->Show();
}
//endregion

if ($message)
	echo $message->Show();
?>
	<form name="form1" method="POST" action=""	enctype="multipart/form-data">
		<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
		<?=bitrix_sessid_post()?><?
		$tabControl->Begin();

		//region General Tab
		$tabControl->BeginNextTab();

		if (isset($vote))
		{
			?><input type="hidden" name="ID" value="<?=$vote->getId()?>" /><?
		}
		else if (isset($copyVote))
		{
			?><input type="hidden" name="COPY_ID" value="<?=$copyVote->getId()?>" /><?
		}
		?>
		<tr>
			<td><?=GetMessage("VOTE_TITLE")?></td>
			<td><input type="text" name="TITLE" size="45" maxlength="255" value="<?=$fields["TITLE"]?>" /></td>
		</tr>
		<tr>
			<td><?=GetMessage("VOTE_DATE").":"?></td>
			<td><?=CalendarPeriod("DATE_START", $fields["~DATE_START"], "DATE_END", $fields["~DATE_END"], "form1", "N", false, false, "19")?></td>
		</tr>
		<?

		$str_PREVIEW_PICTURE = is_scalar($fields["IMAGE_ID"]) ? intval($fields["IMAGE_ID"]) : 0;
		$bFileman = CModule::IncludeModule("fileman");
		?>
		<tr class="adm-detail-file-row">
			<td width="40%"><?=GetMessage("VOTE_IMAGE")?></td>
			<td width="60%">
				<?
				if ($bFileman)
				{
					echo CMedialib::InputFile(
						"IMAGE_ID", $str_PREVIEW_PICTURE,
						array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
							"IMAGE_POPUP" => "Y", "MAX_SIZE" => array("W" => 200, "H" => 200)), //info
						array(), //file
						array(), //server
						array(), //media lib
						array(), //descr
						array(), //delete
						'' //scale hint
					);
				}
				else
				{
					CFile::InputFile("IMAGE_ID", 20, $fields["IMAGE_ID"]);
					if ($fields["IMAGE_ID"] <> ''):
						echo "<br />";
						CFile::ShowImage($fields["IMAGE_ID"], 200, 200, "border=0", "", true);
					endif;
				}
				?>
			</td>
		</tr>
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("VOTE_DESCR")?></td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<?
				if (COption::GetOptionString("vote", "USE_HTML_EDIT")=="Y" && CModule::IncludeModule("fileman")):
					CFileMan::AddHTMLEditorFrame("DESCRIPTION", $fields["DESCRIPTION"], "DESCRIPTION_TYPE", $fields["DESCRIPTION_TYPE"], array('height' => '200', 'width' => '100%'));
				else:
					?>
					<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE_TEXT" value="text" <?=($fields["DESCRIPTION_TYPE"] == "text" ? " checked" : "")?> />
					<label for="DESCRIPTION_TYPE_TEXT">Text</label>&nbsp;/&nbsp;
					<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE_HTML" value="html" <?=($fields["DESCRIPTION_TYPE"] == "html" ? " checked" : "")?> />
					<label for="DESCRIPTION_TYPE_HTML">HTML</label><br />

					<textarea name="DESCRIPTION" style="width:100%" rows="23"><?=$fields["DESCRIPTION"]?></textarea>
				<?
				endif;
				?>
			</td>
		</tr>
		<?
		//endregion

		//region Descr Tab
		$tabControl->BeginNextTab();
		if (isset($vote))
		{
			if ($vote->get("TIMESTAMP_X") <> '' && $vote->get("TIMESTAMP_X") != "00.00.0000 00:00:00")
			{
				?><tr><td><?= GetMessage("VOTE_TIMESTAMP") ?></td><td><?= $vote->get("TIMESTAMP_X") ?></td></tr><?
			}
		}
		?>
		<tr>
			<td><?=GetMessage("VOTE_ACTIVE_TITLE")?></td>
			<td><input type="hidden" name="ACTIVE" value="N" /><input type="checkbox" name="ACTIVE" id="ACTIVE" value="Y" <?=($fields["ACTIVE"] == "Y" ? " checked" : "")?> />
				<label for="ACTIVE"><?=GetMessage("VOTE_ACTIVE")?></label></td>
		</tr>
		<?

		if ($fields["AUTHOR_ID"] > 0 && ($arAuthor = CUser::GetByID($fields["AUTHOR_ID"])->Fetch()))
		{
			$arAuthor["NAME"] = CUser::FormatName('#NAME# #LAST_NAME#', $arAuthor, true, true);
			?>
			<tr>
				<td><?=GetMessage("VOTE_AUTHOR")?></td>
				<td>
					<a href="/bitrix/admin/user_edit.php?ID=<?=$fields["AUTHOR_ID"]?>&lang=<?=LANG?>"> [<?=$fields["AUTHOR_ID"]?>] <?=$arAuthor["NAME"]?></a>
				</td>
			</tr>
			<?
		}

		?>
		<tr>
			<td><?=GetMessage("VOTE_NOTIFY")?></td>
			<td width="60%"><?
				$ref = array("reference_id" => array(), "reference" => array());
				$fields["NOTIFY"] = ($fields["NOTIFY"] != "I" && $fields["NOTIFY"] != "Y" ? "N" : $fields["NOTIFY"]);
				if (IsModuleInstalled("im") && IsModuleInstalled("search"))
				{
					$ref["reference_id"][] = "I"; $ref["reference"][] = GetMessage("VOTE_NOTIFY_IM");
				}
				else
				{
					$fields["NOTIFY"] = ($fields["NOTIFY"] == "I" ? "N" : $fields["NOTIFY"]);
				}
				$ref["reference_id"][] = "Y"; $ref["reference"][] = GetMessage("VOTE_NOTIFY_EMAIL");
				$ref["reference_id"][] = "N"; $ref["reference"][] = GetMessage("VOTE_NOTIFY_N");
				?><?=SelectBoxFromArray("NOTIFY", $ref, $fields["NOTIFY"]);?></td>
		</tr>
		<tr>
			<td><?=GetMessage("VOTE_SORTING")?></td>
			<td><input type="text" name="C_SORT" size="5" value="<?=$fields["C_SORT"]?>" /></td>
		</tr>
		<tr>
			<td><?=GetMessage("VOTE_CHANNEL")?></td>
			<td><select name="CHANNEL_ID"><?
					foreach ($channels as $res):
						?><option value="<?=$res["ID"]?>" <?=($fields["CHANNEL_ID"] == $res["ID"] ? " selected" : "")?><?
						?>> [ <?=$res["ID"]?> ] <?=$res["TITLE"]?></option><?
					endforeach;
					?></select>
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("VOTE_URL")?></td>
			<td><input type="text" name="URL" size="45" maxlength="255" value="<?=$fields["URL"]?>" /></td>
		</tr>

		<?
		if (IsModuleInstalled("statistic")) :
//********************
//Statistic Data
//********************
			?>
			<tr class="heading">
				<td colspan="2"><?=GetMessage("VOTE_STATISTIC_PARAMS")?></td>
			</tr>
			<tr>
				<td>event1:</td>
				<td><input type="text" id="event1" name="EVENT1" size="15" value="<?=$fields["EVENT1"]?>" <?=$fields["EVENTS_disabled"]?> /></td>
			</tr>
			<tr>
				<td>event2:</td>
				<td><input type="text" id="event2" name="EVENT2" size="15" value="<?=$fields["EVENT2"]?>" <?=$fields["EVENTS_disabled"]?> /></td>
			</tr>
			<tr>
				<td>event3:</td>
				<td><input type="text" id="event3" name="EVENT3" size="15" value="<?=$fields["EVENT3"]?>" <?=$fields["EVENTS_disabled"]?> /></td>
			</tr>
		<?
		endif;
		if ($old_module_version == "Y"):
			?>
			<tr>
				<td><?=GetMessage("VOTE_TEMPLATE")?></td>
				<td><?=SelectBoxFromArray("TEMPLATE", GetTemplateList(), $fields["TEMPLATE"]);
					?>&nbsp;[&nbsp;<a title="<?echo GetMessage("VOTE_CHOOSE_TITLE")?>" href="vote_preview.php?lang=<?=LANGUAGE_ID?>&VOTE_ID=<?=$ID?>" class="tablebodylink"><?=GetMessage("VOTE_CHOOSE")?></a>&nbsp;]</td>
			</tr>
			<tr>
				<td><?=GetMessage("VOTE_RESULT_TEMPLATE")?></td>
				<td><?echo SelectBoxFromArray("RESULT_TEMPLATE", GetTemplateList("RV"), $fields["RESULT_TEMPLATE"]);
					?>&nbsp;[&nbsp;<a title="<?echo GetMessage("VOTE_CHOOSE_RESULT_TITLE")?>" href="vote_results.php?lang=<?=LANGUAGE_ID?>&VOTE_ID=<?=$ID?>" class="tablebodylink"><?=GetMessage("VOTE_CHOOSE")?></a>&nbsp;]</td>
			</tr>
		<?
		endif;
		//endregion


		//region Restriction Tab
		$tabControl->BeginNextTab();
		$uniqType = $fields["UNIQUE_TYPE"];
		?>
		<tr class="heading"><td colspan="2"><?=\Bitrix\Main\Localization\Loc::getMessage("VOTE_LIMITS")?></td></tr>
		<? if (IsModuleInstalled('statistic'))
		{ ?>
			<tr>
				<td><input type="checkbox" id="UNIQUE_TYPE_SESSION" name="UNIQUE_TYPE[]" value="<?=EventLimits::BY_SESSION?>" <?=($uniqType & EventLimits::BY_SESSION)?" checked":""?> /></td>
				<td><label for="UNIQUE_TYPE_SESSION"><?=GetMessage("VOTE_UNIQUE_SESSION")?></label></td>
			</tr>
		<? } ?>
		<tr>
			<td><input type="checkbox" id="UNIQUE_TYPE_COOKIE" name="UNIQUE_TYPE[]" value="<?=EventLimits::BY_COOKIE?>"	<?=($uniqType & EventLimits::BY_COOKIE)?" checked":""?> /></td>
			<td><label for="UNIQUE_TYPE_COOKIE"><?=GetMessage("VOTE_UNIQUE_COOKIE_ONLY")?></label></td>
		</tr>
		<tr>
			<td><input type="checkbox" id="UNIQUE_TYPE_IP" name="UNIQUE_TYPE[]" value="<?=EventLimits::BY_IP?>"  <?=($uniqType & EventLimits::BY_IP)?" checked":""?> /></td>
			<td><label for="UNIQUE_TYPE_IP"><?=GetMessage("VOTE_UNIQUE_IP")?></label></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="hidden" name="KEEP_IP_SEC" id="KEEP_IP_SEC" value="<?=$fields["KEEP_IP_SEC"]?>" />
				<input type="text" name="D1" id="D1" size="5" value="0" />&nbsp;&nbsp;<?
				echo SelectBoxFromArray(
					"D2",
					array(
						"reference" => array(
							GetMessage("VOTE_SECOND"),
							GetMessage("VOTE_MINUTE"),
							GetMessage("VOTE_HOUR"),
							GetMessage("VOTE_DAY")),
						"reference_id" => array("S","M","H","D")
					),
					"S",
					"",
					"class=\"typeselect\"");
				?></td>
		</tr>
		<tr>
			<td><input type="checkbox" id="UNIQUE_TYPE_USER_ID" name="UNIQUE_TYPE[]" value="<?=EventLimits::BY_USER_ID?>"	<?=($uniqType & EventLimits::BY_USER_ID)?" checked":""?> /></td>
			<td><label for="UNIQUE_TYPE_USER_ID"><?=GetMessage("VOTE_UNIQUE_USER_ID_ONLY")?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="checkbox" id="UNIQUE_TYPE_USER_ID_NEW" name="UNIQUE_TYPE[]" value="<?=EventLimits::BY_USER_DATE_REGISTER?>" <?=($uniqType & EventLimits::BY_USER_DATE_REGISTER)?" checked":""?> />&nbsp;
				<label for="UNIQUE_TYPE_USER_ID_NEW"><?=GetMessage("VOTE_UNIQUE_ID_NEW_MSG")?></label><br />
			</td>
		</tr>
		<tr class="heading"><td colspan="2"><?=Loc::getMessage("VOTE_ADDITIONAL")?></td></tr>
		<tr>
			<td><input type="checkbox" id="OPTION_ALLOW_REVOTE" name="OPTIONS[]" value="<?=Option::ALLOW_REVOTE?>" <?=($fields["OPTIONS"] & Option::ALLOW_REVOTE)?" checked":""?> /></td>
			<td><label for="OPTION_ALLOW_REVOTE"><?=Loc::getMessage("VOTE_ALLOW_CHANGE_BALLOT")?></td>
		</tr>
		<tr style="display:none;">
			<td><input type="checkbox" id="OPTION_HIDE_RESULT" name="OPTIONS[]" value="<?=Option::HIDE_RESULT?>" <?=($fields["OPTIONS"] & Option::HIDE_RESULT)?" checked":""?> /></td>
			<td><label for="OPTION_HIDE_RESULT"><?=Loc::getMessage("VOTE_FORBID_TO_SEE_RESULTS")?></td>
		</tr>
		<tr>
			<td colspan="2"><?=\Bitrix\Vote\Vote\Anonymity::getTitle()?>: <?
				$ref = array("reference_id" => array(), "reference" => array());
				foreach(\Bitrix\Vote\Vote\Anonymity::getTitledList() as $key => $val)
				{
					$ref["reference_id"][] = $key;
					$ref["reference"][] = $val;
				}
				?><?=SelectBoxFromArray("ANONYMITY", $ref, $fields["ANONYMITY"]);
				?></td>
		</tr>
		<?
		//endregion

		$tabControl->Buttons(array("back_url" => "vote_list.php?lang=".LANGUAGE_ID.($channelId > 0 ? "&find_channel_id=" . $channelId . "&set_filter=Y" : "")));
		$tabControl->End();
		?>

	</form>
<?
$tabControl->ShowWarnings("form1", $message);
?>
	<script>
		BX.ready(function(){
			function UNIQUE_TYPE_CHANGE()
			{
				var ip = BX("UNIQUE_TYPE_IP").checked;
				BX("D1").disabled = BX("D2").disabled = (! ip);

				var id = BX("UNIQUE_TYPE_USER_ID").checked;
				BX("UNIQUE_TYPE_USER_ID_NEW").disabled = (! id);
			}
			UNIQUE_TYPE_CHANGE();
			BX.bind(BX("UNIQUE_TYPE_IP"), "click", UNIQUE_TYPE_CHANGE);
			BX.bind(BX("UNIQUE_TYPE_USER_ID"), "click", UNIQUE_TYPE_CHANGE);

			var multiplier = {
				D : 86400,
				H : 3600,
				M : 60,
				S : 1
			};
			function changeDelay(e)
			{
				var d1 = parseInt(BX("D1").value),
					d2 = multiplier[BX("D2").value],
					d3 = 0;
				if (d1 > 0 && d2 > 0)
					d3 = d1 * d2;
				BX("KEEP_IP_SEC").value = d3
			}
			BX.bind(BX("D1"), "click", changeDelay);
			BX.bind(BX("D1"), "keyup", changeDelay);
			BX.bind(BX("D1"), "change", changeDelay);
			BX.bind(BX("D2"), "click", changeDelay);
			BX.bind(BX("D2"), "keyup", changeDelay);
			BX.bind(BX("D2"), "change", changeDelay);
			var v = parseInt(BX("KEEP_IP_SEC").value),
				i, n = 0, m = "S";
			if (v > 0)
			{
				for (i in multiplier)
				{
					if(multiplier.hasOwnProperty(i))
					{
						if ((v % multiplier[i]) <= 0)
						{
							m = i;
							n = v / multiplier[i];
							break;
						}
					}
				}
			}
			BX("D1").value = n;
			BX("D2").value = m;
		});
	</script>

<?
if (isset($vote))
{
	?><h2><?=GetMessage("VOTE_QUESTIONS")?></h2><?
	?><?$APPLICATION->IncludeComponent("bitrix:voting.admin.questions", ".default",
	array(
		"VOTE_ID" => $vote->getId(),
		"SHOW_FILTER" => "N"
	));?><?
}
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
