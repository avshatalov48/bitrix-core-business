<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param array $arParams
 * @param array $arResult
 * @param string $componentName
 * @param CBitrixComponent $this
 */

/*************** Default data **************************************/
$arParams["iIndex"] = $iIndex = rand();
$message = ($_SERVER['REQUEST_METHOD'] == "POST" ? $_POST["message_id"] : $_GET["message_id"]);
$message = (is_array($message) ? $message : array($message));

$arUserSettings = array("first_post" => "show");
if ($arParams["SHOW_FIRST_POST"] == "Y" && $GLOBALS["USER"]->IsAuthorized())
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
	$arUserSettings = CUserOptions::GetOption("forum", "default_template", "");
	$arUserSettings = (CheckSerializedData($arUserSettings) ? @unserialize($arUserSettings) : array());

	$arUserSettings["first_post"] = ($arUserSettings["first_post"] == "hide" ? "hide" : "show");
}
$bShowedHeader = false;

$arAuthorId = array();
$arPostId = array();
$arTopicId = array();
$arRatingResult = array();
$arRatingVote = array();
if ($arParams["SHOW_RATING"] == 'Y')
{
	$tmp = (!empty($arResult["MESSAGE_FIRST"]) ?
		(array($arResult["MESSAGE_FIRST"]["ID"] => $arResult["MESSAGE_FIRST"]) + $arResult["MESSAGE_LIST"]) : $arResult["MESSAGE_LIST"]);
	foreach ($tmp as $res)
	{
		$arAuthorId[] = $res['AUTHOR_ID'];
		if ($res['NEW_TOPIC'] == "Y")
			$arTopicId[] = $res['TOPIC_ID'];
		else
			$arPostId[] = $res['ID'];
	}
	if (!empty($arAuthorId))
	{
		foreach($arParams["RATING_ID"] as $key => $ratingId)
		{
			$arParams["RATING_ID"][$key] = intval($ratingId);
			$arRatingResult[$arParams["RATING_ID"][$key]] = CRatings::GetRatingResult($arParams["RATING_ID"][$key], array_unique($arAuthorId));
		}
	}

	if (!empty($arPostId))
		$arRatingVote['FORUM_POST'] = CRatings::GetRatingVoteResult('FORUM_POST', $arPostId);

	if (!empty($arTopicId))
		$arRatingVote['FORUM_TOPIC'] = CRatings::GetRatingVoteResult('FORUM_TOPIC', $arTopicId);
}
/*************** Default data **************************************/
if (!empty($arResult["ERROR_MESSAGE"])):
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
if (!empty($arResult["OK_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?
endif;

if ($arParams["SHOW_FIRST_POST"] == "Y" && $arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageNomer > 1 &&
	!($arParams['AJAX_POST'] == 'Y' && $arParams['ACTION'] == 'REPLY'))
{
	$bShowedHeader = true;
?>
<div class="forum-header-box">
	<div class="forum-header-options">
<?
	if ($GLOBALS["USER"]->IsAuthorized())
	{
		?><span class="forum-option-additional"><a href="#postform" onclick="ShowFirstPost(this); return false;"><?=(
			$arUserSettings["first_post"] == "show" ? GetMessage("F_COLLAPSE") : GetMessage("F_SHOW"))?></a></span><?
	}
	if ($arParams["SHOW_RSS"] == "Y")
	{
		?>&nbsp;&nbsp;<span class="forum-option-feed">
			<noindex><a rel="nofollow" href="<?=$arResult["URL"]["RSS_DEFAULT"]?>" onclick="window.location='<?=CUtil::JSEscape($arResult["URL"]["~RSS"]);?>'; return false;">RSS</a></noindex>
		</span><?
	}
	if ($USER->IsAuthorized())
	{
		if (empty($arResult["USER"]["SUBSCRIBE"]))
		{
			?>&nbsp;&nbsp;<span class="forum-option-subscribe forum-option-do-subscribe">
			<noindex><a rel="nofollow" title="<?=GetMessage("F_SUBSCRIBE_TITLE")?>" href="<?
			?><?=$APPLICATION->GetCurPageParam("TOPIC_SUBSCRIBE=Y&".bitrix_sessid_get(), array("FORUM_SUBSCRIBE", "FORUM_SUBSCRIBE_TOPIC", "sessid"))?><?
				?>"><?=GetMessage("F_SUBSCRIBE")?></a></noindex></span><?
		}
		else
		{
			?>&nbsp;&nbsp;<span class="forum-option-subscribe forum-option-do-unsubscribe"><noindex><a rel="nofollow" title="<?=GetMessage("F_UNSUBSCRIBE_TITLE")?>" href="<?
			?><?=$APPLICATION->GetCurPageParam("TOPIC_UNSUBSCRIBE=Y&".bitrix_sessid_get(), array("FORUM_UNSUBSCRIBE", "FORUM_UNSUBSCRIBE_TOPIC", "sessid"))?><?
				?>"><?=GetMessage("F_UNSUBSCRIBE")?></a></noindex></span><?
		}
	}?>
	</div>
	<div class="forum-header-title"><span><?
	if ($arResult["TOPIC"]["STATE"] != "Y"):
		?><span class="forum-header-title-closed">[ <span><?=GetMessage("F_CLOSED")?></span> ]</span> <?
	endif;
	?><?=trim($arResult["TOPIC"]["TITLE"])?><?
		if (strlen($arResult["TOPIC"]["DESCRIPTION"])>0): ?>, <?=trim($arResult["TOPIC"]["DESCRIPTION"])?><? endif;
	?></span></div>
</div><?

?><div class="forum-block-container forum-first-post"><?
	?><div class="forum-block-outer" style="display:<?=($arUserSettings["first_post"] == "show" ? "block" : "none")?>">
		<div class="forum-block-inner">
<?
		$res = $arResult["MESSAGE_FIRST"];
			if ($arParams["SHOW_VOTE"] == "Y" && $res["PARAM1"] == "VT" && intVal($res["PARAM2"]) > 0 && IsModuleInstalled("vote"))
			{
				?>
				<div class="forum-info-box forum-post-vote">
					<div class="forum-info-box-inner">
						<span style='position:absolute;'><a style="display:none;" id="message<?=$res["ID"]?>">&nbsp;</a></span><? /* IE9 */ ?>
						<a name="message<?=$res["ID"]?>"></a>
						<?$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:voting.current", $arParams["VOTE_TEMPLATE"],
							array(
								"VOTE_ID" => $res["PARAM2"],
								"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"],
								"VOTE_RESULT_TEMPLATE" => $arResult["~CURRENT_PAGE"],
								"CACHE_TIME" => $arParams["CACHE_TIME"],
								"NEED_SORT" => "N",
								"SHOW_RESULTS" => "Y"),
							null,
							array("HIDE_ICONS" => "Y"));?>
					</div>
				</div>
				<?
			}

			?><?$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:forum.message.template", "",
				Array(
					"MESSAGE" => $res + array(
						"CHECKED" => (in_array($res["ID"], $message) ? "Y" : "N"),
						"SHOW_CONTROL" => "N"),
					"ATTACH_MODE" => $arParams["ATTACH_MODE"],
					"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
					"COUNT" => 0,
					"NUMBER" => $iCount,
					"SEO_USER" => $arParams["SEO_USER"],
					"SHOW_RATING" => $arParams["SHOW_RATING"],
					"RATING_ID" => $arParams["RATING_ID"],
					"RATING_TYPE" => $arParams["RATING_TYPE"],
					"arRatingVote" => $arRatingVote,
					"arRatingResult" => $arRatingResult,
					"arResult" => $arResult,
					"arParams" => $arParams
				),
				(($this && $this->__component && $this->__component->__parent) ? $this->__component->__parent : null),
				array("HIDE_ICONS" => "Y")
			);?>
		</div>
	</div>
</div>
<?
}

if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
<?
	if ($arResult["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y"):
?>
	<div class="forum-new-post">
		<a href="#postform" onclick="return fReplyForm();"><span><?=GetMessage("F_REPLY")?></span></a>
	</div>
<?
	endif;
?>
	<div class="forum-clear-float"></div>
</div>
<?
endif;

?>
<div class="forum-header-box">
<?
if (!$bShowedHeader)
{
?>
	<div class="forum-header-options">
<?
	if ($arParams["SHOW_RSS"] == "Y")
	{
		?><span class="forum-option-feed"><?
			?><noindex><a rel="nofollow" href="<?=$arResult["URL"]["RSS_DEFAULT"]?>" onclick="window.location='<?=CUtil::JSEscape($arResult["URL"]["~RSS"])?>'; return false;">RSS</a></noindex><?
		?></span><?
	}
	if ($USER->IsAuthorized())
	{
		if ($arParams["SHOW_RSS"] == "Y"): ?>&nbsp;&nbsp;<? endif;

		if (empty($arResult["USER"]["SUBSCRIBE"]))
		{
			?><span class="forum-option-subscribe forum-option-do-subscribe"><noindex><a rel="nofollow" title="<?=GetMessage("F_SUBSCRIBE_TITLE")?>" href="<?
				?><?=$APPLICATION->GetCurPageParam("TOPIC_SUBSCRIBE=Y&".bitrix_sessid_get(), array("FORUM_SUBSCRIBE", "FORUM_SUBSCRIBE_TOPIC", "sessid"))?><?
					?>"><?=GetMessage("F_SUBSCRIBE")?></a></noindex></span><?
		}
		else
		{
			?><span class="forum-option-subscribe forum-option-do-unsubscribe"><noindex><a rel="nofollow" title="<?=GetMessage("F_UNSUBSCRIBE_TITLE")?>" href="<?
				?><?=$APPLICATION->GetCurPageParam("TOPIC_UNSUBSCRIBE=Y&".bitrix_sessid_get(), array("FORUM_UNSUBSCRIBE", "FORUM_UNSUBSCRIBE_TOPIC", "sessid"))?><?
					?>"><?=GetMessage("F_UNSUBSCRIBE")?></a></noindex></span><?
		}
	}?>
	</div>
	<div class="forum-header-title"><span>
<?
	if ($arResult["TOPIC"]["STATE"] != "Y")
	{
		?><span class="forum-header-title-closed">[ <span><?=GetMessage("F_CLOSED")?></span> ]</span> <?
	}
	?><?=trim($arResult["TOPIC"]["TITLE"])?><?
		if (strlen($arResult["TOPIC"]["DESCRIPTION"])>0): ?>, <?=trim($arResult["TOPIC"]["DESCRIPTION"])?><? endif;
?>
	</span></div>
<?
}
else
{
?>
	<div class="forum-header-title"><span><?=GetMessage("F_POSTS")?></span></div>
<?
}
?>
</div>

<div class="forum-block-container">
	<div class="forum-block-outer">
	<!--FORUM_INNER--><div class="forum-block-inner">
<?
if (!empty($arResult["MESSAGE_LIST"]))
{
	$iCount = 0;
	foreach ($arResult["MESSAGE_LIST"] as $res)
	{
		$iCount++;
		if ($arParams["SHOW_VOTE"] == "Y" && $res["PARAM1"] == "VT" && intVal($res["PARAM2"]) > 0 && IsModuleInstalled("vote"))
		{
			?>
			<div class="forum-info-box forum-post-vote">
				<div class="forum-info-box-inner">
					<span style='position:absolute;'><a style="display:none;" id="message<?=$res["ID"]?>">&nbsp;</a></span><? /* IE9 */ ?>
					<a name="message<?=$res["ID"]?>"></a>
					<?$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:voting.current", $arParams["VOTE_TEMPLATE"],
						array(
							"VOTE_ID" => $res["PARAM2"],
							"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"],
							"VOTE_RESULT_TEMPLATE" => $arResult["~CURRENT_PAGE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"NEED_SORT" => "N",
							"SHOW_RESULTS" => "Y"),
						null,
						array("HIDE_ICONS" => "Y"));?>
				</div>
			</div>
			<?}
if ($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y" && $iCount <= 1) :
?>
<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" onsubmit="return Validate(this)" <?
	?>name="MESSAGES_<?=$arParams["iIndex"]?>" id="MESSAGES_<?=$arParams["iIndex"]?>">
<?
endif;

		?><?$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.message.template", "",
			Array(
				"MESSAGE" => $res + array("CHECKED" => (in_array($res["ID"], $message) ? "Y" : "N")),
				"ATTACH_MODE" => $arParams["ATTACH_MODE"],
				"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
				"COUNT" => count($arResult["MESSAGE_LIST"]),
				"NUMBER" => $iCount,
				"SEO_USER" => $arParams["SEO_USER"],
				"SHOW_RATING" => $arParams["SHOW_RATING"],
				"RATING_ID" => $arParams["RATING_ID"],
				"RATING_TYPE" => $arParams["RATING_TYPE"],
				"arRatingVote" => $arRatingVote,
				"arRatingResult" => $arRatingResult,
				"arResult" => $arResult,
				"arParams" => $arParams
			),
			(($this && $this->__component && $this->__component->__parent) ? $this->__component->__parent : null),
			array("HIDE_ICONS" => "Y")
		);?><?
	}
?>
				<tfoot>
					<tr>
						<td colspan="5" class="forum-column-footer">
							<div class="forum-footer-inner"><?
if ($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y"):
?>
								<?=bitrix_sessid_post()?>
								<input type="hidden" name="type" value="messages" />
								<input type="hidden" name="PAGE_NAME" value="read" />
								<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
								<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
								<input type="hidden" name="ACTION" value="" />
								<div class="forum-post-moderate">
									&nbsp;&nbsp;<span class="forum-footer-option forum-footer-selectall forum-footer-option-first"><?
								?><noindex><a rel="nofollow" href="javascript:void(0);" onclick="SelectPosts('<?=$arParams["iIndex"]?>');" name=""><?=GetMessage("F_SELECT_ALL")?></a></noindex></span>
								</div>
								<div class="forum-post-moderate">
									<select name="ACTION_MESSAGE">
										<option value=""><?=GetMessage("F_MANAGE_MESSAGES")?></option>
										<option value="HIDE"><?=GetMessage("F_HIDE_MESSAGES")?></option>
										<option value="SHOW"><?=GetMessage("F_SHOW_MESSAGES")?></option>
										<option value="MOVE"><?=GetMessage("F_MOVE_MESSAGES")?></option>
<?
	if ($arResult["USER"]["RIGHTS"]["EDIT"] == "Y"):
?>
										<option value="DEL"><?=GetMessage("F_DELETE_MESSAGES")?></option>
<?
	endif;
?>
									</select>&nbsp;<input onmousedown="this.form.type.value='messages';this.form.ACTION.value=this.form.ACTION_MESSAGE.value;" <?
										?>type="submit" value="OK" />
								</div>
								<div class="forum-topic-moderate">
									<select name="ACTION_TOPIC">
										<option value=""><?=GetMessage("F_MANAGE_TOPIC")?></option>
										<option value="<?=($arResult["TOPIC"]["APPROVED"] == "Y" ? "HIDE_TOPIC" : "SHOW_TOPIC")?>"><?
											?><?=($arResult["TOPIC"]["APPROVED"] == "Y" ? GetMessage("F_HIDE_TOPIC") : GetMessage("F_SHOW_TOPIC"))?></option>
										<option value="<?=($arResult["TOPIC"]["SORT"] != 150 ? "SET_ORDINARY" : "SET_TOP")?>"><?
											?><?=($arResult["TOPIC"]["SORT"] != 150 ? GetMessage("F_UNPINN_TOPIC") : GetMessage("F_PINN_TOPIC"))?></option>
										<option value="<?=($arResult["TOPIC"]["STATE"] == "Y" ? "STATE_N" : "STATE_Y")?>"><?
											?><?=($arResult["TOPIC"]["STATE"] == "Y" ? GetMessage("F_CLOSE_TOPIC") : GetMessage("F_OPEN_TOPIC"))?></option>
										<option value="MOVE_TOPIC"><?=GetMessage("F_MOVE_TOPIC")?></option>
<?
	if ($arResult["USER"]["RIGHTS"]["EDIT"] == "Y"):
?>
										<option value="EDIT_TOPIC"><?=GetMessage("F_EDIT_TOPIC")?></option>
										<option value="DEL_TOPIC"><?=GetMessage("F_DELETE_TOPIC")?></option>
<?
	endif;
?>
									</select>&nbsp;<input onmousedown="this.form.type.value='topic';this.form.ACTION.value=this.form.ACTION_TOPIC.value;" <?
										?>type="submit" value="OK" />
								</div>
<?
else:
?>
							&nbsp;
<?
endif;
							?></div>
						</td>
					</tr>
				</tfoot>
<?$lastMessage = end($arResult['MESSAGE_LIST']);?>
			</table><!--MSG_END_<?=$lastMessage['ID']?>-->
<?
}

if ($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y"):
?>
</form>
<?
endif;
?>
		</div><!--FORUM_INNER_END-->
	</div>
</div>
<?

if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
<?
if ($arResult["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y"):
?>
	<div class="forum-new-post">
		<a href="#postform" onclick="return fReplyForm();"><span><?=GetMessage("F_REPLY")?></span></a>
	</div>
<?
endif;
?>
	<div class="forum-clear-float"></div>
</div>

<?
endif;

if (!empty($arResult["ERROR_MESSAGE"])):
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
if (!empty($arResult["OK_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?
endif;

// View new posts
if ($arResult["VIEW"] == "Y"):
?><?$GLOBALS["APPLICATION"]->IncludeComponent(
	"bitrix:forum.message.template",
	".preview",
	Array(
		"MESSAGE" => $arResult["MESSAGE_VIEW"],
		"ATTACH_MODE" => $arParams["ATTACH_MODE"],
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
		"arResult" => $arResult,
		"arParams" => $arParams
	),
	$component->__parent,
	array("HIDE_ICONS" => "Y")
);?><?
endif;

?><script type="text/javascript">
<?if (intVal($arParams["MID"]) > 0):?>
location.hash = 'message<?=$arParams["MID"]?>';
<?endif;?>
if (typeof oText != "object")
	var oText = {};
oText['cdt'] = '<?=GetMessageJS("F_DELETE_TOPIC_CONFIRM")?>';
oText['cdm'] = '<?=GetMessageJS("F_DELETE_CONFIRM")?>';
oText['cdms'] = '<?=GetMessageJS("F_DELETE_MESSAGES_CONFIRM")?>';
oText['no_data'] = '<?=GetMessageJS('JS_NO_MESSAGES')?>';
oText['no_action'] = '<?=GetMessageJS('JS_NO_ACTION')?>';
oText['quote_text'] = '<?=GetMessageJS("JQOUTE_AUTHOR_WRITES");?>';
oText['show'] = '<?=GetMessageJS("F_SHOW")?>';
oText['hide'] = '<?=GetMessageJS("F_HIDE")?>';
oText['wait'] = '<?=GetMessageJS("F_WAIT")?>';

BX.message({
	topic_read_url : '<?=CUtil::JSUrlEscape($arResult['CURRENT_PAGE']);?>',
	page_number : '<?=intval($arResult['PAGE_NUMBER']);?>'
});
<?
if ($GLOBALS["USER"]->IsAuthorized() && $bShowedHeader):
?>
function ShowFirstPost(oA)
{
	var div = oA.parentNode.parentNode.parentNode.nextSibling.firstChild;
	div.style.display = (div.style.display == 'none' ? '' : 'none');
	oA.innerHTML = (div.style.display == 'none' ? '<?=GetMessageJS("F_COLLAPSE")?>' : '<?=GetMessageJS("F_SHOW")?>');
	BX.ajax.get(
			'/bitrix/components/bitrix/forum/templates/.default/user_settings.php',
			{'save': 'first_post', 'value' :(div.style.display == 'none' ? 'hide' : 'show'), 'sessid': '<?=bitrix_sessid()?>'}
	);
	return false;
}
<?
endif;
?>
</script>