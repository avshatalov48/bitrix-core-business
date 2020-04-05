<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

CUtil::InitJSCore(array('tooltip'));

/********************************************************************
				Input params
********************************************************************/
$arParams["iIndex"] = $iIndex = rand();
$message = ($_SERVER['REQUEST_METHOD'] == "POST" ? $_POST["message_id"] : $_GET["message_id"]);
$action = strToUpper($_SERVER['REQUEST_METHOD'] == "POST" ? $_POST["ACTION"] : $_GET["ACTION"]);
$message = (is_array($message) ? $message : array($message));

$res = false;
$arUserSettings = array("first_post" => "show");
if (!empty($arResult["MESSAGE_FIRST"]) && $GLOBALS["USER"]->IsAuthorized())
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
	$arUserSettings = CUserOptions::GetOption("forum", "default_template", "");
	$arUserSettings = (CheckSerializedData($arUserSettings) ? @unserialize($arUserSettings) : array());
	$arUserSettings["first_post"] = ($arUserSettings["first_post"] == "hide" ? "hide" : "show");
}
/********************************************************************
				/Input params
********************************************************************/

if (!empty($arResult["ERROR_MESSAGE"])) { ?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div><?}

if (!empty($arResult["OK_MESSAGE"])) { ?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<? }

$bShowedHeader = false;
if (!empty($arResult["MESSAGE_FIRST"]))
{
	$bShowedHeader = true;
?>
<!--MSG_START--><div><div class="forum-header-box"><?
	if ($GLOBALS["USER"]->IsAuthorized()) {?>
		<div class="forum-header-options">
			<span class="forum-option-additional">
				<a href="#postform" onclick="return ShowFirstPost(this);"><?=(
				$arUserSettings["first_post"] == "show" ? GetMessage("F_COLLAPSE") : GetMessage("F_SHOW"))?></a>
			</span>
		</div><?
	}?>
	<div class="forum-header-title"><span><?
		if ($arResult["TOPIC"]["STATE"] != "Y") {?><span class="forum-header-title-closed">[ <span><?=GetMessage("F_CLOSED")?></span> ]</span> <? }
		?><?=trim($arResult["TOPIC"]["TITLE"])?><?
		if (!empty($arResult["TOPIC"]["DESCRIPTION"])) { ?>, <?=trim($arResult["TOPIC"]["DESCRIPTION"])?><? }
	?></span></div>
</div><?
?><div class="forum-block-container forum-first-post"><?
	?><div class="forum-block-outer" style="position:relative;width:100%;<?if($arUserSettings["first_post"] != "show"){ ?>display:none;<? }?>">
		<div class="forum-block-inner"><?
	__forum_default_template_show_message(
		array($arResult["MESSAGE_FIRST"] + array("SHOW_CONTROL" => "N")),
		$message,
		$arResult,
		$arParams,
		$this);?>
		</div>
	</div>
</div>
</div><!--MSG_START_END-->
<?
}

if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0) { ?>
<div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div><?
		if ($arResult["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y")
		{
?>
			<div class="forum-new-post">
				<a href="#postform" onclick="return fReplyForm();"><span><?=GetMessage("F_REPLY")?></span></a>
			</div>
<?
		}
?>
		<div class="forum-clear-float"></div>
	</div>
<?
}
?>
<div class="forum-header-box">
	<div class="forum-header-title">
		<span><?
	if (!$bShowedHeader) {
		if ($arResult["TOPIC"]["STATE"] != "Y") {?><span class="forum-header-title-closed">[ <span><?=GetMessage("F_CLOSED")?></span> ]</span> <? }
		?><?=trim($arResult["TOPIC"]["TITLE"])?><?
		if (!empty($arResult["TOPIC"]["DESCRIPTION"])) { ?>, <?=trim($arResult["TOPIC"]["DESCRIPTION"])?><? }
	} else {?>
	<?=GetMessage("F_POSTS")?><?
	}?>
		</span>
	</div>
</div>
<div class="forum-block-container" id="forum-block-container-<?=$arResult["TOPIC"]["ID"]?>">
	<div class="forum-block-outer">
		<!--FORUM_INNER--><div class="forum-block-inner">
<?
		if (!empty($arResult["MESSAGE_LIST"]))
		{
			__forum_default_template_show_message($arResult["MESSAGE_LIST"], $message, $arResult, $arParams, $this);
?>
				<tfoot>
					<tr>
						<td colspan="5" class="forum-column-footer">
							<div class="forum-footer-inner">
<?
			if ($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y")
			{
?>
	<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" <?
		?>onsubmit="return Validate(this)" name="MESSAGES_<?=$iIndex?>" id="MESSAGES_<?=$iIndex?>">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="PAGE_NAME" value="read" />
		<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
		<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
		<div class="forum-post-moderate">
		<select name="ACTION">
			<option value=""><?=GetMessage("F_MANAGE_MESSAGES")?></option>
			<option value="HIDE"><?=GetMessage("F_HIDE_MESSAGES")?></option>
			<option value="SHOW"><?=GetMessage("F_SHOW_MESSAGES")?></option>
			<?if ($arResult["USER"]["RIGHTS"]["EDIT"] == "Y"){?>
			<option value="DEL"><?=GetMessage("F_DELETE_MESSAGES")?></option>
			<?}?>
		</select>&nbsp;<input type="submit" value="OK" />
		</div>
	</form>
	<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" <?
		?>onsubmit="return Validate(this)" name="TOPIC_<?=$iIndex?>" id="TOPIC_<?=$iIndex?>">
		<div class="forum-topic-moderate">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="PAGE_NAME" value="read" />
		<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
		<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />

		<select name="ACTION">
			<option value=""><?=GetMessage("F_MANAGE_TOPIC")?></option>
			<option value="<?=($arResult["TOPIC"]["APPROVED"] == "Y" ? "HIDE_TOPIC" : "SHOW_TOPIC")?>"><?
			?><?=($arResult["TOPIC"]["APPROVED"] == "Y" ? GetMessage("F_HIDE_TOPIC") : GetMessage("F_SHOW_TOPIC"))?></option>
			<option value="<?=($arResult["TOPIC"]["SORT"] != 150 ? "SET_ORDINARY" : "SET_TOP")?>"><?
			?><?=($arResult["TOPIC"]["SORT"] != 150 ? GetMessage("F_UNPINN_TOPIC") : GetMessage("F_PINN_TOPIC"))?></option>
			<option value="<?=($arResult["TOPIC"]["STATE"] == "Y" ? "STATE_N" : "STATE_Y")?>"><?
			?><?=($arResult["TOPIC"]["STATE"] == "Y" ? GetMessage("F_CLOSE_TOPIC") : GetMessage("F_OPEN_TOPIC"))?></option>
			<?if ($arResult["USER"]["RIGHTS"]["EDIT"] == "Y"){?>
			<option value="EDIT_TOPIC"><?=GetMessage("F_EDIT_TOPIC")?></option>
			<option value="DEL_TOPIC"><?=GetMessage("F_DELETE_TOPIC")?></option>
			<?}?>
		</select>&nbsp;<input type="submit" value="OK" />
		</div>
	</form>
<?
			} else {
?>
							&nbsp;
<?
			}
?>
							</div>
						</td>
					</tr>
				</tfoot>
<?$lastMessage = end($arResult['MESSAGE_LIST']);?>
			</table><!--MSG_END_<?=$lastMessage['ID']?>-->
<?
		}
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
	BX.ready(function(){
		BX.viewElementBind(
			'forum-block-container-<?=$arResult["TOPIC"]["ID"]?>',
			{showTitle: true},
			function(node){ return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image')); }
		);
	});
<?if (intVal($arParams["MID"]) > 0):?>
location.hash = 'message<?=$arParams["MID"]?>';
<?endif;?>
if (typeof oText != "object")
	var oText = {};
oText['cdt'] = '<?=CUtil::addslashes(GetMessage("F_DELETE_TOPIC_CONFIRM"))?>';
oText['cdm'] = '<?=CUtil::addslashes(GetMessage("F_DELETE_CONFIRM"))?>';
oText['cdms'] = '<?=CUtil::addslashes(GetMessage("F_DELETE_MESSAGES_CONFIRM"))?>';
oText['ml'] = '<?=CUtil::addslashes(GetMessage("F_ANCHOR_TITLE"))?>';
oText['no_data'] = '<?=CUtil::addslashes(GetMessage('JS_NO_MESSAGES'))?>';
oText['no_action'] = '<?=CUtil::addslashes(GetMessage('JS_NO_ACTION'))?>';
oText['quote_text'] = '<?=CUtil::addslashes(GetMessage("JQOUTE_AUTHOR_WRITES"));?>';
oText['show'] = '<?=CUtil::addslashes(GetMessage("F_SHOW"))?>';
oText['hide'] = '<?=CUtil::addslashes(GetMessage("F_HIDE"))?>';
oText['wait'] = '<?=CUtil::addslashes(GetMessage("F_WAIT"))?>';
if (typeof phpVars != "object")
	var phpVars = {};
phpVars.bitrix_sessid = '<?=bitrix_sessid()?>';

if (typeof oForum != "object")
	var oForum = {};
oForum.page_number = <?=intval($arResult['PAGE_NUMBER']);?>;
oForum.topic_read_url = '<?=CUtil::JSUrlEscape($arResult['CURRENT_PAGE']);?>';
<?if ($GLOBALS["USER"]->IsAuthorized() && $bShowedHeader):?>
function ShowFirstPost(oA)
{
	var div = BX.findChild(document, {'className': 'forum-first-post'}, true);
	div = div.firstChild;
	if (!div) return;
	var status = (div.style.display == 'none' ? "show" : "hide");
	if (status == 'hide')
	{
		BX.fx.hide(
			div,
			'scroll',
			{
				time: 0.35,
				callback_complete: function() {
					var el = BX.findChild(document, {'className': 'forum-first-post'}, true);
					el = BX.findChild(el.previousSibling, {'tagName': 'A'}, true);
					el.innerHTML = '<?=GetMessageJS("F_SHOW")?>';}
			}
		);

	}
	else
	{
		BX.fx.show(
			div,
			'scroll',
			{
				time: 0.35,
				callback_complete : function() {
					var el = BX.findChild(document, {'className': 'forum-first-post'}, true);
					el.firstChild.style.height = 'auto';
					el = BX.findChild(el.previousSibling, {'tagName': 'A'}, true);
					el.innerHTML = '<?=GetMessageJS("F_COLLAPSE")?>';
				}
			}
		);
	}
	BX.ajax.get(
			'/bitrix/components/bitrix/forum/templates/.default/user_settings.php',
			{'save': 'first_post', 'value': status, 'sessid': '<?=bitrix_sessid()?>'}
	);
	return false;
}
<?endif;?>

</script>
