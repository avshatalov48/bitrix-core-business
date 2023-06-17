<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arParams["~MESSAGE"]))
	return false;
/************************ Default Params ***************************/
$res = $arParams["~MESSAGE"];
$iNumber = intval($arParams["NUMBER"] > 0 ? $arParams["NUMBER"] : 1); // message number in list
$iCount = intval($arParams["COUNT"] > 0 ? $arParams["COUNT"] : 0); // messages count

$arRatingResult = $arParams["~arRatingResult"];
$arRatingVote = $arParams["~arRatingVote"];
CJSCore::Init(array("viewer"));
$arRes = $arParams["~arResult"];
$arRes = (is_array($arRes) ? $arRes : array($arRes)); // info about topic, forum, user $arResult form main component
/*********************** /Default Params ***************************/
include_once(__DIR__."/script.php");
?>
<!--MSG_<?=$res["ID"]?>-->
<table cellspacing="0" border="0" class="forum-post-table <?=($iNumber == 1 ? "forum-post-first " : "")?><?
	?><?=($iNumber == $iCount || $iCount == 0 ? "forum-post-last " : "")?><?
	?><?=($iNumber%2 == 1 ? "forum-post-odd " : "forum-post-even ")?><?
	?><?=($res["APPROVED"] == "Y" ? "" : " forum-post-hidden ")?><?
	?><?=($res["CHECKED"] == "Y" ? " forum-post-selected " : "")?>" <?
	?>id="message_block_<?=$res["ID"]?>" bx-author-name="<?=htmlspecialcharsbx($res["~AUTHOR_NAME"])?>" bx-author-id="<?=$res["AUTHOR_ID"]?>">
	<tbody>
		<tr>
			<td class="forum-cell-user">
				<span style='position:absolute;'><a <?/*?>style="display:none;"<?*/?> id="message<?=$res["ID"]?>">&nbsp;</a></span><? /* IE9 */ ?>
				<div class="forum-user-info">
<?
		if ($res["AUTHOR_ID"] > 0):
?>
					<div class="forum-user-name"><?
						?><?=str_replace(array("#URL#", "#NAME#"), array($res["URL"]["AUTHOR"], $res["AUTHOR_NAME"]), $arParams["USER_TMPL"])
					?></div>
<?
			if (is_array($res["AVATAR"]) && !empty($res["AVATAR"]["HTML"])):
?>
					<div class="forum-user-avatar"><?
						?><?=str_replace(array("#URL#", "#NAME#"), array($res["URL"]["AUTHOR"], $res["AVATAR"]["HTML"]), $arParams["USER_TMPL"])
					?></div>
<?
			else:
?>
					<div class="forum-user-register-avatar"><?
						?><?=str_replace(array("#URL#", "#NAME#"), array($res["URL"]["AUTHOR"], '<span><!-- ie --></span>'), $arParams["USER_TMPL"])
					?></div>
<?
			endif;
		else:
?>
					<div class="forum-user-name"><span><?=$res["AUTHOR_NAME"]?></span></div>
					<div class="forum-user-guest-avatar"><!-- ie --></div>
<?
		endif;

if(trim($res["AUTHOR_STATUS"]) <> ''):
	?>
	<div
		class="forum-user-status <?= (!empty($res["AUTHOR_STATUS_CODE"])? "forum-user-".$res["AUTHOR_STATUS_CODE"]."-status" : "") ?>"><?
		?><span><?= htmlspecialcharsbx($res["AUTHOR_STATUS"]) ?></span></div>
<?
endif;

?>
					<div class="forum-user-additional">
<?
		if (intval($res["NUM_POSTS"]) > 0):
?>
						<span><?=GetMessage("F_NUM_MESS")?> <span><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR_POSTS"]?>"><?
							?><?=$res["NUM_POSTS"]?></a></noindex></span></span>
<?
		endif;

		if (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y" && $res["AUTHOR_ID"] > 0 &&
			($res["NUM_POINTS"] > 0 || $res["VOTES"]["ACTION"] == "VOTE" || $res["VOTES"]["ACTION"] == "UNVOTE")):
?>
						<span><?=GetMessage("F_POINTS")?> <span><?=$res["NUM_POINTS"]?></span><?
			if ($res["VOTES"]["ACTION"] == "VOTE" || $res["VOTES"]["ACTION"] == "UNVOTE"):
				$res["URL"]["AUTHOR_VOTE"] = (!!$res["URL"]["~AUTHOR_VOTE"] ? $res["URL"]["~AUTHOR_VOTE"] : $res["URL"]["AUTHOR_VOTE"]);
						?>&nbsp;(<span class="forum-vote-user"><?
							?><noindex><a rel="nofollow" <?
								if (mb_strpos($res["URL"]["AUTHOR_VOTE"], "sessid=") === false):?>onclick="this.href+='<?=(mb_strpos($res["URL"]["AUTHOR_VOTE"], "?") === false?"?":"&").bitrix_sessid_get()?>';return true;" <?endif;
								?>href="<?=$res["URL"]["AUTHOR_VOTE"]?>" title="<?
								?><?=($res["VOTES"]["ACTION"] == "VOTE" ? GetMessage("F_NO_VOTE_DO") : GetMessage("F_NO_VOTE_UNDO"));?>"><?
								?><?=($res["VOTES"]["ACTION"] == "VOTE" ? "+" : "-");?></a></noindex></span>)<?
			endif;
						?></span>
<?
		endif;
	if ($arParams["SHOW_RATING"] == 'Y' && $res["AUTHOR_ID"] > 0)
	{
		foreach($arParams["RATING_ID"] as $ratingId)
		{
			?><span><?$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:rating.result", "",
				Array(
					"RATING_ID" => $ratingId,
					"ENTITY_ID" => $arRatingResult[$ratingId][$res['AUTHOR_ID']]['ENTITY_ID'],
					"CURRENT_VALUE" => $arRatingResult[$ratingId][$res['AUTHOR_ID']]['CURRENT_VALUE'],
					"PREVIOUS_VALUE" => $arRatingResult[$ratingId][$res['AUTHOR_ID']]['PREVIOUS_VALUE'],
//					"LINK" => $res["URL"]["~AUTHOR"],
				),
				null,
				array("HIDE_ICONS" => "Y")
			);
			?></span><?
		}
	}
		if ($res["~DATE_REG"] <> ''):
?>
						<span><?=GetMessage("F_DATE_REGISTER")?> <span><?=$res["DATE_REG"]?></span></span>
<?
		endif;
?>
					</div>
<?
		if ($res["DESCRIPTION"] <> ''):
?>
					<div class="forum-user-description"><span><?=$res["DESCRIPTION"]?></span></div>
<?
		endif;

?>
				</div>
			</td>
			<td class="forum-cell-post">
				<div class="forum-post-date">
					<div class="forum-post-number"><noindex><a rel="nofollow" href="<?=$res["URL"]["MESSAGE"]?>#message<?=$res["ID"]?>" <?
						?>onclick="prompt(this.title + ' [' + this.innerHTML + ']', (location.protocol + '//' + location.host + this.getAttribute('href'))); return false;" title="<?=GetMessage("F_ANCHOR")?>">#<?=$res["NUMBER"]?></a></noindex><?
				if ($arRes["USER"]["PERMISSION"] >= "Q" && $res["SHOW_CONTROL"] != "N"):
					?>&nbsp;<input type="checkbox" name="message_id[]" value="<?=$res["ID"]?>" id="message_id_<?=$res["ID"]?>_" <?
					if ($res["CHECKED"] == "Y"):
					?> checked="checked" <?
					endif;
						?> onclick="SelectPost(this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode)" /><?
				endif;
					?></div>
					<?if ($arParams["SHOW_RATING"] == 'Y'):?>
					<div class="forum-post-rating">
					<?
					$voteEntityType = $res['NEW_TOPIC'] == "Y" ? "FORUM_TOPIC" : "FORUM_POST";
					$voteEntityId = $res['NEW_TOPIC'] == "Y" ? $res['TOPIC_ID'] : $res['ID'];
					$GLOBALS["APPLICATION"]->IncludeComponent(
						"bitrix:rating.vote", $arParams["RATING_TYPE"],
						Array(
							"ENTITY_TYPE_ID" => $voteEntityType,
							"ENTITY_ID" => $voteEntityId,
							"OWNER_ID" => $res['AUTHOR_ID'],
							"USER_VOTE" => $arRatingVote[$voteEntityType][$voteEntityId]['USER_VOTE'],
							"USER_HAS_VOTED" => $arRatingVote[$voteEntityType][$voteEntityId]['USER_HAS_VOTED'],
							"TOTAL_VOTES" => $arRatingVote[$voteEntityType][$voteEntityId]['TOTAL_VOTES'],
							"TOTAL_POSITIVE_VOTES" => $arRatingVote[$voteEntityType][$voteEntityId]['TOTAL_POSITIVE_VOTES'],
							"TOTAL_NEGATIVE_VOTES" => $arRatingVote[$voteEntityType][$voteEntityId]['TOTAL_NEGATIVE_VOTES'],
							"TOTAL_VALUE" => $arRatingVote[$voteEntityType][$voteEntityId]['TOTAL_VALUE'],
							"PATH_TO_USER_PROFILE" => $arParams["~URL_TEMPLATES_PROFILE_VIEW"]
						),
						($component->__parent ? $component->__parent : $component),
						array("HIDE_ICONS" => "Y")
					);?>
					</div>
					<?endif;?>
					<span><?=$res["POST_DATE"]?></span>
				</div>
				<div class="forum-post-entry">
					<div class="forum-post-text" id="message_text_<?=$res["ID"]?>"><?=$res["POST_MESSAGE_TEXT"]?></div>
<?
if (!empty($res["FILES"]))
{
	$arFilesHTML = array("thumb" => array(), "files" => array());

	foreach ($res["FILES"] as $arFile)
	{
		if (!in_array($arFile["FILE_ID"], $res["FILES_PARSED"]))
		{
			$arFileTemplate = $GLOBALS["APPLICATION"]->IncludeComponent("bitrix:forum.interface", "show_file",
				Array(
					"FILE" => $arFile,
					"SHOW_MODE" => $arParams["ATTACH_MODE"],
					"WIDTH" => $arParams["ATTACH_SIZE"],
					"HEIGHT" => $arParams["ATTACH_SIZE"],
					"CONVERT" => "N",
					"FAMILY" => "FORUM",
					"SINGLE" => "Y",
					"RETURN" => "ARRAY",
					"SHOW_LINK" => "Y"
				),
				null,
				array("HIDE_ICONS" => "Y")
			);
			if (!empty($arFileTemplate["DATA"]))
				$arFilesHTML["thumb"][] = $arFileTemplate["RETURN_DATA"];
			else
				$arFilesHTML["files"][] = $arFileTemplate["RETURN_DATA"];
		}
	}

	if (!empty($arFilesHTML["thumb"]) || !empty($arFilesHTML["files"]))
	{
?>
					<div class="forum-post-attachments">
						<label><?=GetMessage("F_ATTACH_FILES")?></label>
<?
		if (!empty($arFilesHTML["thumb"]))
		{
			?><div class="forum-post-attachment forum-post-attachment-thumb"><fieldset><?=implode("", $arFilesHTML["thumb"])?></fieldset></div><?;
		}
		if (!empty($arFilesHTML["files"]))
		{
			?><div class="forum-post-attachment forum-post-attachment-files"><ul><li><?=implode("</li><li>", $arFilesHTML["files"])?></li></ul></div><?;
		}
?>
					</div>
<?
	}
}
				if (is_array($res["PROPS"]))
				{
					foreach ($res["PROPS"] as $arPostField)
					{
						if(!empty($arPostField["VALUE"]))
						{
							if (!empty($arPostField["EDIT_FORM_LABEL"]))
							{
								$arPostField["EDIT_FORM_LABEL"] = "<span>".$arPostField["EDIT_FORM_LABEL"].": </span>";
							}

							?><div class="forum-post-userfield"><?=$arPostField["EDIT_FORM_LABEL"]
								?><?$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:system.field.view", $arPostField["USER_TYPE"]["USER_TYPE_ID"],
								array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?></div><?
						}
					}
				}

				if (!empty($res["EDITOR_NAME"])):
				?><div class="forum-post-lastedit">
					<span class="forum-post-lastedit"><?=GetMessage("F_EDIT_HEAD")?>
						<span class="forum-post-lastedit-user"><?
					if (!empty($res["URL"]["EDITOR"])):
						?><?=str_replace(array("#URL#", "#NAME#"), array($res["URL"]["EDITOR"], $res["EDITOR_NAME"]), $arParams["USER_TMPL"]);
					else:
						?><?=$res["EDITOR_NAME"]?><?
					endif;
						?></span> - <span class="forum-post-lastedit-date"><?=$res["EDIT_DATE"]?></span>
<?
					if (!empty($res["EDIT_REASON"])):
?>
					<span class="forum-post-lastedit-reason">(<span><?=$res["EDIT_REASON"]?></span>)</span>
<?
					endif;
?>
				</span></div><?
				endif;

				if ($res["SIGNATURE"] <> ''):
?>
					<div class="forum-user-signature">
						<div class="forum-signature-line"></div>
						<span><?=$res["SIGNATURE"]?></span>
					</div>
<?
				endif;
?>
				</div>
<?
		if ($arRes["USER"]["PERMISSION"] >= "Q"):
?>
				<div class="forum-post-entry forum-user-additional forum-user-moderate-info">
<?
			if ($res["IP_IS_DIFFER"] == "Y"):
?>
					<span>IP<?=GetMessage("F_REAL_IP")?>: <span><?=$res["AUTHOR_IP"];?> / <?=$res["AUTHOR_REAL_IP"];?></span></span>
<?
			else:
?>
					<span>IP: <span><?=$res["AUTHOR_IP"];?></span></span>
<?
			endif;
			if ($res["PANELS"]["STATISTIC"] == "Y"):
?>
					<span><?=GetMessage("F_USER_ID")?>: <span><a href="/bitrix/admin/guest_list.php?lang=<?=LANG_ADMIN_LID?><?
						?>&amp;find_id=<?=$res["GUEST_ID"]?>&amp;set_filter=Y"><?=$res["GUEST_ID"];?></a></span></span>
<?
			endif;

			if ($res["PANELS"]["MAIN"] == "Y"):
?>
					<span><?=GetMessage("F_USER_ID_USER")?>: <span><?
						?><a href="/bitrix/admin/user_edit.php?lang=<?=LANG_ADMIN_LID?>&amp;ID=<?=$res["AUTHOR_ID"]?>"><?=$res["AUTHOR_ID"];?></a></span></span>
<?
			endif;
?>
				</div>
<?
		endif;
?>
			</td>
		</tr>
		<tr>
			<td class="forum-cell-contact">
				<div class="forum-contact-links">
<?
		if ($arParams["SHOW_PM"] == "Y" && $res["AUTHOR_ID"] > 0):
?>
			<span class="forum-contact-message"><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR_PM"]?>" title="<?=GetMessage("F_PRIVATE_MESSAGE_TITLE")?>"><?
				?><?=GetMessage("F_PRIVATE_MESSAGE")?></a></noindex></span>&nbsp;&nbsp;
<?
		endif;
		if ($arParams["SHOW_MAIL"] == "Y" && !empty($res["EMAIL"])):
?>
				<span class="forum-contact-email"><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR_EMAIL"]?>" <?
					?>title="<?=GetMessage("F_EMAIL_TITLE")?>">E-mail</a></noindex></span>&nbsp;&nbsp;
<?
		endif;
		if ($arParams["SHOW_ICQ"] == "Y" && !empty($res["PERSONAL_ICQ"])):
			$bEmptyCell = false;
?>
			<span class="forum-contact-icq">
				<noindex><a rel="nofollow" href="javascript:void(0);" onclick="prompt('ICQ', '<?=CUtil::JSEscape($res["PERSONAL_ICQ"])?>')">ICQ</a></noindex></span>
<?
		elseif (!($res["AUTHOR_ID"] > 0 && $GLOBALS["USER"]->IsAuthorized())):
?>
				&nbsp;
<?
		endif;
?>
				</div>
			</td>
			<td class="forum-cell-actions">
				<div class="forum-action-links">
<?
foreach(array("MODERATE", "EDIT", "DELETE") as $k)
{
	if (array_key_exists($k, $res["URL"]))
	{
		$res["URL"][$k] = (array_key_exists("~".$k, $res["URL"]) ? htmlspecialcharsbx($res["URL"]["~".$k]) :
			replace(array("&amp;".bitrix_sessid_get(), bitrix_sessid_get()), "", $res["URL"][$k]));
	}
}
	if ($res["NEW_TOPIC"] == "Y"):
		if ($res["PANELS"]["MODERATE"] == "Y" && $arRes["TOPIC"]["APPROVED"] != "Y"):
?>
					<span class="forum-action-show"><noindex><a rel="nofollow" <?
						?>onclick="return fAddSId(this);" <?
						?>href="<?=$GLOBALS["APPLICATION"]->GetCurPageParam("ACTION=SHOW_TOPIC", array("ACTION", "sessid"))?>"><?
						?><?=GetMessage("F_SHOW_TOPIC")?></a></noindex></span>
<?
		endif;
		if ($res["PANELS"]["DELETE"] == "Y"):
?>
					&nbsp;&nbsp;<span class="forum-action-delete"><noindex><a rel="nofollow" <?
						?>onclick="if(confirm(BX.message('cdt'))) return fAddSId(this); else return false;" <?
						?>href="<?=$GLOBALS["APPLICATION"]->GetCurPageParam("ACTION=DEL_TOPIC", array("ACTION", "sessid"))?>" <?
						?>><?=GetMessage("F_DELETE_TOPIC")?></a></noindex></span>
<?
		endif;
		if ($res["PANELS"]["EDIT"] == "Y" && $arRes["USER"]["PERMISSION"] >= "U"):
?>
					&nbsp;&nbsp;<span class="forum-action-edit"><?
						?><noindex><a rel="nofollow" href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT_TOPIC")?></a></noindex></span>
<?
		elseif ($res["PANELS"]["EDIT"] == "Y"):
?>
					&nbsp;&nbsp;<span class="forum-action-edit"><?
						?><noindex><a rel="nofollow" href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT")?></a></noindex></span>
<?
		endif;
	else:
		if ($res["PANELS"]["MODERATE"] == "Y"):
			if ($res["APPROVED"] == "Y"):
?>
					<span class="forum-action-hide"><?
						?><noindex><a rel="nofollow" <?
							if ($arParams['AJAX_POST'] == 'Y'): ?>onclick="return forumActionComment(this, 'MODERATE');"<?
							else: ?>onclick="return fAddSId(this);"<? endif;
							?> href="<?=$res["URL"]["MODERATE"]?>"><?=GetMessage("F_HIDE")?></a></noindex></span>&nbsp;&nbsp;
<?
			else:
?>
					<span class="forum-action-show"><?
						?><noindex><a rel="nofollow" <?
							if ($arParams['AJAX_POST'] == 'Y'): ?>onclick="return forumActionComment(this, 'MODERATE');"<?
							else: ?>onclick="return fAddSId(this);"<? endif;
							?> href="<?=$res["URL"]["MODERATE"]?>"><?=GetMessage("F_SHOW")?></a></noindex></span>&nbsp;&nbsp;
<?
			endif;
		endif;
		if ($res["PANELS"]["DELETE"] == "Y"):
?>
					<span class="forum-action-delete"><?
						?><noindex><a rel="nofollow" <?
							if ($arParams['AJAX_POST'] == 'Y'): ?>onclick="return forumActionComment(this, 'DEL');" <?
							else: ?>onclick="if(confirm(BX.message('cdm'))) return fAddSId(this); else return false;" <? endif;
							?> href="<?=$res["URL"]["DELETE"]?>"><?=GetMessage("F_DELETE")?></a></noindex></span>&nbsp;&nbsp;
<?
		endif;
		if ($res["PANELS"]["EDIT"] == "Y"):
?>
					<span class="forum-action-edit"><noindex><a rel="nofollow" href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT")?></a></noindex></span>&nbsp;&nbsp;
<?
		endif;
		if (($res["PANELS"]["GOTO"] ?? 'N') === "Y"):
	?>
					<span class="forum-action-edit"><noindex><a rel="nofollow" href="<?=$res["URL"]["MESSAGE"]?>#message<?=$res["ID"]?>"><?=GetMessage("F_GOTO")?></a></noindex></span>&nbsp;&nbsp;
<?
		endif;
endif;

if ($arRes["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y"):
	if ($res["NUMBER"] == 1):
		?>&nbsp;&nbsp;<?
	endif;

	if ($arRes["FORUM"]["ALLOW_QUOTE"] == "Y"): ?>
					<span class="forum-action-quote"><a title="<?=GetMessage("F_QUOTE_HINT")?>" href="#postform" <?
						?> onmousedown="if (window['quoteMessageEx']){quoteMessageEx(<?=$res["ID"]?>)}; return false;"><?
						?><?=GetMessage("F_QUOTE")?></a></span><?
	endif;

	if ($arRes["FORUM"]["ALLOW_QUOTE"] == "Y" && $arParams["SHOW_NAME_LINK"] == "Y"):
		?>&nbsp;&nbsp;<?
	endif;

	if ($arParams["SHOW_NAME_LINK"] == "Y"):?>
					<span class="forum-action-reply"><a href="#postform" title="<?=GetMessage("F_INSERT_NAME")?>" <?
						?> onmousedown="if(window['reply2author']){reply2author(<?=$res["ID"]?>);} return false;"><?
						?><?=GetMessage("F_NAME")?></a></span><?
	endif;
else:
	?>&nbsp;<?
endif;
?>
				</div>
			</td>
		</tr>
	</tbody>
<?
if ($iNumber < $iCount || ($iCount == 0)):
?>
</table><!--MSG_END_<?=$res["ID"]?>-->
<?
endif;

?><script type="text/javascript">
<?
if ($arRes["USER"]["PERMISSION"] >= "Q" && ForumGetEntity($templateFolder) === false)
{
?>
;(function(window){
if (window.SelectPost) return;
BX.message({cdm: '<?=GetMessageJS("F_DELETE_MESSAGES_CONFIRM")?>', cdt: '<?=GetMessageJS("F_DELETE_TOPIC_CONFIRM")?>'});
window.SelectPost = function(table)
{
	if (table == null) { return; }
	if(table.className.match(/forum-post-selected/)) {table.className = table.className.replace(/\s*forum-post-selected/gi, '');}
	else {table.className += ' forum-post-selected';}
}

})(window);
<?
}
?>
BX.viewElementBind(
	'message_block_<?=$res["ID"]?>',
	{showTitle: false},
	function(node){
		return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
	}
);
</script>