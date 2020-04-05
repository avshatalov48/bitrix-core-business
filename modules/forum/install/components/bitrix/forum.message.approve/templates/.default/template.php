<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
/************************ Default Params ***************************/
$iIndex = rand();
if ($_SERVER['REQUEST_METHOD'] == "POST"):
	$message = (empty($_POST["MID_ARRAY"]) ? $_POST["MID"] : $_POST["MID_ARRAY"]);
	$message = (empty($message) ? $_POST["message_id"] : $message);
	$action = strToUpper($_POST["ACTION"]);
else:
	$message = (empty($_GET["MID_ARRAY"]) ? $_GET["MID"] : $_GET["MID_ARRAY"]);
	$message = (empty($message) ? $_GET["message_id"] : $message);
	$action = strToUpper($_GET["ACTION"]);
endif;
$message = (is_array($message) ? $message : array($message));
$iCount = count($arResult["MESSAGE_LIST"]);
$iNumber = 1;
/*********************** /Default Params ***************************/
if (!empty($arResult["ERROR_MESSAGE"])):?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?endif;
if (!empty($arResult["OK_MESSAGE"])):?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?endif;
if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):?>
<div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?endif;
?>
<div class="forum-header-box">
	<div class="forum-header-options">
		<?if ($arParams["TID"] > 0):?><span class="forum-option-topic"><a href="<?=$arResult["read"]?>"><?=$arResult["TOPIC"]["TITLE"]?></a></span><?endif;?>
		<span class="forum-option-subscribe"><a href="<?=$arResult["list"]?>"><?=$arResult["FORUM"]["NAME"]?></a></span>
	</div>
	<div class="forum-header-title"><span><?=GetMessage("F_TITLE")?></span></div>
</div>
<?
if (empty($arResult["MESSAGE_LIST"])):?>
<div class="forum-info-box forum-posts-notapproved">
	<div class="forum-info-box-inner">
		<?=GetMessage("F_EMPTY_RESULT")?>
	</div>
</div>
<?
	return false;
endif;
?>
<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" onsubmit="return Validate(this)" name="MESSAGES" id="MESSAGES">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="PAGE_NAME" value="message_appr" />
	<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
	<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
<?
foreach ($arResult["MESSAGE_LIST"] as $res)
{
	?><?$GLOBALS["APPLICATION"]->IncludeComponent(
		"bitrix:forum.message.template", "",
		Array(
			"MESSAGE" => array_merge($res, array("NEW_TOPIC" => "N", "CHECKED" => (in_array($res["ID"], $message) ? "Y" : "N"))),
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
				<div class="forum-footer-inner">
					<div class="forum-post-moderate">
						<select name="ACTION">
							<option value=""><?=GetMessage("F_MANAGE_MESSAGES")?></option>
							<option value="SHOW" <?=($action == "SHOW" ? " selected='selected' " : "")?>><?=GetMessage("F_SHOW_MESSAGES")?></option>
							<?if ($arResult["USER"]["RIGHTS"]["EDIT"] == "Y"):?><option value="DEL" <?=($action == "DEL" ? " selected='selected' " : "")?>><?=GetMessage("F_DELETE_MESSAGES")?></option><?endif;?>
						</select>&nbsp;<input type="submit" value="OK" />
					</div>
				</div>
			</td>
		</tr>
	</tfoot>
</table>
		</div>
	</div>
</div>
</form>
<?if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?endif;?>
<script type="text/javascript">
BX.message({no_data: '<?=GetMessageJS("JS_NO_MESSAGES")?>', no_action: '<?=GetMessageJS("JS_NO_ACTION")?>', cdms: '<?=GetMessageJS("F_DELETE_MESSAGES_CONFIRM")?>'});
</script>