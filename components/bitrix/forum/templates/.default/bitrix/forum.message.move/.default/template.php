<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
CUtil::InitJSCore(array('translit'));
$_REQUEST["ACTION"] = (isset($_REQUEST["ACTION"]) && $_REQUEST["ACTION"] == "MOVE_TO_NEW" ? "MOVE_TO_NEW" : "MOVE_TO_TOPIC");
if (!empty($arResult["ERROR_MESSAGE"])):?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?endif;
if (!empty($arResult["OK_MESSAGE"])):?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?endif;?>
<form method="POST" name="MESSAGES" id="MESSAGES" action="<?=POST_FORM_ACTION_URI?>" onsubmit="this.send_form.disabled=true; return true;" class="forum-form">
	<input type="hidden" name="PAGE_NAME" value="message_move" />
	<?=$arResult["sessid"]?>
	<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
	<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
	<input type="hidden" name="step" value="1" />
<div class="forum-info-box forum-post-move">
	<div class="forum-info-box-inner">
		<div class="forum-post-entry">
			<?=GetMessage("F_MOVE_TO")?>
			<input type="radio" name="ACTION" value="MOVE_TO_TOPIC" id="MOVE_TO_TOPIC" <?=($_REQUEST["ACTION"] == "MOVE_TO_TOPIC" ? "checked='checked'" : "")?> <?
				?>onclick="BX('MOVE_TO_TOPIC_DIV').style.display=(this.checked ? '' : 'none'); BX('MOVE_TO_NEW_DIV').style.display=(this.checked ? 'none' : '');" />
			<label for="MOVE_TO_TOPIC"><?=GetMessage("F_HEAD_TO_EXIST_TOPIC")?></label>
			<input type="radio" name="ACTION" value="MOVE_TO_NEW" id="MOVE_TO_NEW" <?=($_REQUEST["ACTION"] == "MOVE_TO_NEW" ? "checked='checked'" : "")?> <?
				?>onclick="BX('MOVE_TO_TOPIC_DIV').style.display=(this.checked ? 'none' : ''); BX('MOVE_TO_NEW_DIV').style.display=(this.checked ? '' : 'none');" />
			<label for="MOVE_TO_NEW"><?=GetMessage("F_HEAD_TO_NEW_TOPIC")?></label>
		</div>

		<div id="MOVE_TO_TOPIC_DIV" <?=($_REQUEST["ACTION"] == "MOVE_TO_NEW" ? "style='display:none;'" : "")?> class="forum-post-move-to-topic">
	<div class="forum-reply-fields">
		<div class="forum-reply-field forum-reply-field-topic">
			<label for="newTID"><?=GetMessage("F_TOPIC_ID")?><span class="forum-required-field">*</span></label>
			<input type="text" name="newTID" id="newTID" value="<?=isset($_REQUEST["newTID"]) ? intval($_REQUEST["newTID"]) : null?>" <?
				?> onfocus="ForumSearchTopic(this, 'Y');" onblur="ForumSearchTopic(this, 'N');" size="2" />
			<input type="button" name="search" value="..." onClick="window.open('<?=CUtil::JSEscape($arResult["topic_search"])?>', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));" />
			<span id="TOPIC_INFO"><?
				if (!empty($arResult["NEW_TOPIC"]["TOPIC"])):
					?>&laquo;<?=$arResult["NEW_TOPIC"]["TOPIC"]["TITLE"]?>&raquo; ( <?=GetMessage("F_TITLE_ON_FORUM")?>: <?=$arResult["NEW_TOPIC"]["FORUM"]["NAME"]?>)<?
				elseif ((isset($_REQUEST["newTID"]) ? intval($_REQUEST["newTID"]) : 0) > 0):
					?><?=GetMessage("F_TOPIC_NOT_FOUND")?><?
				else:
				endif;
			?></span>
		</div>
		<div class="forum-reply-field forum-reply-field-info">
			<i>(<?=GetMessage("F_TOPIC_SEARCH_TITLE")?>)</i>
		</div>
	</div>
		</div>
		<div id="MOVE_TO_NEW_DIV" <?=($_REQUEST["ACTION"] == "MOVE_TO_NEW" ? "" : "style='display:none;'")?> class="forum-post-move-to-new-topic">
	<div class="forum-reply-fields">
		<div class="forum-reply-field forum-reply-field-title">
			<label for="TITLE"><?=GetMessage("F_TOPIC_NAME")?><span class="forum-required-field">*</span></label>
			<input name="TITLE" id="TITLE" type="text" value="<?=(isset($_REQUEST["TITLE"]) ? htmlspecialcharsbx($_REQUEST["TITLE"]) : null)?>" size="70"<?if($arParams["SEO_USE_AN_EXTERNAL_SERVICE"] == "Y"){ ?>onfocus="BX.Forum.transliterate(this);"<? }?> /><?
			if($arParams["SEO_USE_AN_EXTERNAL_SERVICE"] == "Y"){ ?><input name="TITLE_SEO" type="hidden" value="<?=(isset($_REQUEST["TITLE_SEO"]) ? htmlspecialcharsbx($_REQUEST["TITLE_SEO"]) : null)?>" /><? }
		?></div>
		<div class="forum-reply-field forum-reply-field-desc">
			<label for="DESCRIPTION"><?=GetMessage("F_TOPIC_DESCR")?></label>
			<input name="DESCRIPTION" id="DESCRIPTION" type="text" value="<?=(isset($_REQUEST["DESCRIPTION"]) ? htmlspecialcharsbx($_REQUEST["DESCRIPTION"]) : null)?>" size="70"/></div>
<?
if ($arParams["SHOW_TAGS"] == "Y"):
?>
		<div class="forum-reply-field forum-reply-field-tags" style="display:block;">
			<label for="TAGS"><?=GetMessage("F_TOPIC_TAGS")?></label>
<?
		if (IsModuleInstalled("search")):
		$APPLICATION->IncludeComponent(
			"bitrix:search.tags.input",
			"",
			array(
				"VALUE" => isset($_REQUEST["TAGS"]) ? htmlspecialcharsbx($_REQUEST["TAGS"]) : null,
				"NAME" => "TAGS",
				"TEXT" => ' size="70" '),
			$component,
			array("HIDE_ICONS" => "Y"));
		else:
			?><input name="TAGS" type="text" value="<?=(isset($_REQUEST["TAGS"]) ? htmlspecialcharsbx($_REQUEST["TAGS"]) : null)?>"  size="70"/><?
		endif;
?>
		</div>
<?
endif;
?>
	</div>
		</div>

		<div class="forum-reply-buttons">
			<input type="submit" name="send_form" value="<?=GetMessage("F_BUTTON_MOVE")?>" />
		</div>
	</div>
</div>
<div class="forum-header-box">
	<div class="forum-header-options">
		<span class="forum-option-topic"><a href="<?=$arResult["TOPIC"]["read"]?>"><?=$arResult["TOPIC"]["TITLE"]?></a></span>&nbsp;&nbsp;
		<span class="forum-option-forum"><a href="<?=$arResult["FORUM"]["list"]?>"><?=$arResult["FORUM"]["NAME"]?></a></span>
	</div>
	<div class="forum-header-title"><span><?=GetMessage("F_TITLE")?></span></div>
</div>

<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
<?
$iNumber = 1;
$iCount = count($arResult["MESSAGE_LIST"]);
foreach ($arResult["MESSAGE_LIST"] as $res)
{
	?><?$GLOBALS["APPLICATION"]->IncludeComponent(
		"bitrix:forum.message.template", "",
		Array(
			"MESSAGE" => array_merge($res, array("NEW_TOPIC" => "N", "SHOW_CONTROL" => "N")),
			"ATTACH_MODE" => $arParams["ATTACH_MODE"],
			"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
			"COUNT" => $iCount,
			"NUMBER" => $iNumber++,
			"SEO_USER" => $arParams["SEO_USER"],
			"SHOW_RATING" => "N",
			"RATING_ID" => "",
			"RATING_TYPE" => "",
			"arRatingVote" => "",
			"arRatingResult" => "",
			"arResult" => $arResult,
			"arParams" => $arParams
		),
		$component->__parent,
		array("HIDE_ICONS" => "Y")
	);?><?
}
?>
				<tfoot>
					<tr>
						<td colspan="5" class="forum-column-footer">
							&nbsp;
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
</form>
<script>
BX.Forum = (BX.Forum || {});
BX.Forum['topic_search'] = {
	url : '<?=CUtil::JSEscape($arResult["topic_search"])?>',
	object : false,
	value : '<?=isset($arResult["newTID"]) ? intval($arResult["newTID"]) : null?>',
	action : 'search',
	fined : {}};

BX.message({
	topic_not_found: '<?=GetMessageJS("F_BAD_TOPIC")?>',
	topic_bad: '<span class="starrequired"><?=GetMessageJS("F_BAD_NEW_TOPIC")?></span>',
	topic_wait: '<i><?=GetMessageJS("FORUM_MAIN_WAIT")?></i>'});
</script>

