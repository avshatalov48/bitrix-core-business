<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var CMain $APPLICATION
 * @var CUser $USER
 */
use \Bitrix\Main\UI;

UI\Extension::load(['ui.animations', 'main.rating' , 'ui.tooltip', 'ui.icons.b24']);

$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css");
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css");
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/components/bitrix/main.post.list/templates/.default/scripts_for_form.js");
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/components/bitrix/main.post.list/templates/.default/scripts_for_form2.js");
if (CModule::IncludeModule("im"))
	\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/components/bitrix/main.post.list/templates/.default/scripts_for_im.js");
if (!empty($arParams["RATING_TYPE_ID"]))
{
	$likeTemplate = (
		\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
			? 'like_react'
			: 'like'
	);
	//http://hg.office.bitrix.ru/repos/modules/rev/6377a7cfcd73
	$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/".$likeTemplate."/popup.css");
	$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/".$likeTemplate."/style.css");
	\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/main/rating_like.js");
}

CUtil::InitJSCore(array("date", "fx", "popup", "viewer", "clipboard", "tooltip"));
if (CModule::IncludeModule('socialnetwork'))
{
	CUtil::InitJSCore(array("comment_aux"));
}
if (CModule::IncludeModule('landing'))
{
	CUtil::InitJSCore(array("landing_note"));
}
$prefixNode = $arParams["ENTITY_XML_ID"].'-'.$arParams["EXEMPLAR_ID"];
$eventNodeId = $prefixNode."_main";
$eventNodeIdTemplate = "#ENTITY_XML_ID#-#EXEMPLAR_ID#_main";
ob_start();
?>
	<!--RCRD_#FULL_ID#-->
	<a id="com#ID#" name="com#ID#" bx-mpl-full-id="#FULL_ID#"></a>
	<div id="record-#FULL_ID#" class="feed-com-block-outer">
		#BEFORE_RECORD#
		<div class="feed-com-block blog-comment-user-#AUTHOR_ID# sonet-log-comment-createdby-#AUTHOR_ID# feed-com-block-#APPROVED##CLASSNAME#">
			#BEFORE_HEADER#
			<div class="feed-com-avatar feed-com-avatar-#AUTHOR_AVATAR_IS#"><img src="#AUTHOR_AVATAR#" width="<?=$arParams["AVATAR_SIZE"]?>" height="<?=$arParams["AVATAR_SIZE"]?>" /></div>
			<!--/noindex-->
			<div class="feed-com-main-content feed-com-block-#NEW#">
				<span class="feed-com-name #AUTHOR_EXTRANET_STYLE# feed-author-name feed-author-name-#AUTHOR_ID#">#AUTHOR_NAME#</span>
				<div class="feed-com-user-box">
					<a
					 target="_top"
					 class="feed-com-name #AUTHOR_EXTRANET_STYLE# feed-author-name feed-author-name-#AUTHOR_ID#"
					 id="bpc_#FULL_ID#"
					 bx-tooltip-user-id="#AUTHOR_ID#"
					 bx-tooltip-params="#AUTHOR_TOOLTIP_PARAMS#"
					 href="<?=($arParams["AUTHOR_URL"] != "" ? "#AUTHOR_URL#" : "javascript:void(0);")?>">#AUTHOR_NAME#</a>
					<a class="feed-time feed-com-time" href="#VIEW_URL##com#ID#" rel="nofollow" target="_top">#DATE#</a>
				</div>
				#AFTER_HEADER#
				#BEFORE#
				<div class="feed-com-text">
					<div class="feed-com-text-inner" bx-content-view-xml-id="#CONTENT_ID#" id="feed-com-text-inner-#CONTENT_ID#" bx-content-view-save="N" bx-mpl-block="body">
						<div class="feed-com-text-inner-inner" id="record-#FULL_ID#-text" bx-mpl-block="text">
							<div>#TEXT#</div>
						</div>
					</div>
					<div class="feed-post-text-more" bx-mpl-block="more-button" <?
						?>onclick="BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'onExpandComment', [this]); return BX.PreventDefault(this);" <?
						?>id="record-#FULL_ID#-more">
						<div class="feed-post-text-more-but"><div class="feed-post-text-more-left"></div><div class="feed-post-text-more-right"></div></div>
					</div>
				</div>
				#AFTER#
			</div>
			#LIKE_REACT#
			<!--/noindex-->
		</div>
		<div class="feed-com-informers-bottom">
			#BEFORE_ACTIONS#
			<?if ( $arParams["SHOW_POST_FORM"] == "Y" )
			{
				?><a href="javascript:void(0);" class="feed-com-reply feed-com-reply-#SHOW_POST_FORM#" <?
				?>id="record-#FULL_ID#-actions-reply" <?
				?>onclick="BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'onReply', [this]);" <?
				?>bx-mpl-author-id="#AUTHOR_ID#" <?
				?>bx-mpl-author-gender="#AUTHOR_PERSONAL_GENDER#" <?
				?>bx-mpl-author-name="#AUTHOR_NAME#"><?=GetMessage("BLOG_C_REPLY")?></a><?
			}


			if (!$arParams["bPublicPage"])
			{
				?><a href="#" <?
				?> id="record-#FULL_ID#-actions" <?
				?> bx-mpl-view-url="#VIEW_URL#" bx-mpl-view-show="#VIEW_SHOW#" <?
				?> bx-mpl-edit-url="#EDIT_URL#" bx-mpl-edit-show="#EDIT_SHOW#" <?
				?> bx-mpl-moderate-url="#MODERATE_URL#" bx-mpl-moderate-show="#MODERATE_SHOW#" bx-mpl-moderate-approved="#APPROVED#" <?
				?> bx-mpl-delete-url="#DELETE_URL###ID#" bx-mpl-delete-show="#DELETE_SHOW#" <?
				?> bx-mpl-createtask-show="#CREATETASK_SHOW#" <?
				?> bx-mpl-post-entity-type="#POST_ENTITY_TYPE#" <?
				?> bx-mpl-comment-entity-type="#COMMENT_ENTITY_TYPE#" <?
				?> onclick="BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'onShowActions', [this, '#ID#']); return BX.PreventDefault(this);" <?
				?> class="feed-post-more-link feed-post-more-link-#VIEW_SHOW#-#EDIT_SHOW#-#MODERATE_SHOW#-#DELETE_SHOW#"><?
					?><span class="feed-post-more-text"><?=GetMessage("BLOG_C_BUTTON_MORE")?></span><?
				?></a><?
			}
			?>
			#AFTER_ACTIONS#
		</div>
		#AFTER_RECORD#<?
		?><script>BX.ready(function() { BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'OnUCCommentIsInDOM', ['#ID#', BX('<?=$eventNodeIdTemplate?>')]);});</script><?
	?></div>
	<div id="record-#FULL_ID#-placeholder" bx-mpl-block="edit-placeholder" class="blog-comment-edit feed-com-add-block blog-post-edit feed-com-add-box" style="display:none;"></div>
	<!--RCRD_END_#FULL_ID#-->
<?
$template = preg_replace("/[\t\n]/", "", ob_get_clean());
ob_start();
?>
	<script>
		BX.ready(function() {
			BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'OnUCBlankCommentIsInDOM', ['#ID#', BX('<?=$eventNodeIdTemplate?>')]);
		});
	</script>
	<div style="position: absolute;width:1px;height:1px;opasity:0;"></div>
<?
$blankTemplate =  preg_replace("/[\t\n]/", "", ob_get_clean());
?><div id="<?=$eventNodeId?>"><?
if (empty($arParams["RECORDS"]))
{
	?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-corner" class="feed-com-corner"></div><?
}
else
{
	if (!!$arParams["NAV_STRING"])
	{
		if ($arResult["NAV_STRING_COUNT_MORE"] > 0)
		{
			ob_start();

			if ($arParams["PREORDER"] == "Y")
			{
				?><div id="record-<?=$prefixNode?>-hidden" class="feed-hidden-post" style="display:none; overflow:hidden;"></div> <?
			}
			?><div class="feed-com-header"><?
				?><a class="feed-com-all" href="<?=$arParams["NAV_STRING"]?>" id="<?=$prefixNode?>_page_nav" bx-mpl-comments-count="<?=$arResult["NAV_STRING_COUNT_MORE"]?>"><?
					?><?=($arParams["PREORDER"] == "Y" ? GetMessage("BLOG_C_VIEW1") : GetMessage("BLOG_C_VIEW2"))?> <span class="feed-com-all-count"><?=$arResult["NAV_STRING_COUNT_MORE"]?></span><i></i><?
				?></a><?
			?></div><?
			if ($arParams["PREORDER"] != "Y")
			{
				?><div id="record-<?=$prefixNode?>-hidden" class="feed-hidden-post" style="display:none; overflow:hidden;"></div> <?
			}
			$arParams["NAV_STRING"] = ob_get_clean();
		}
		else
		{
			$arParams["NAV_STRING"] = "";
		}
	}
	$tmp = reset($arParams["RECORDS"]);
	?><div class="feed-com-corner<?=($arParams["NAV_STRING"] === "" && $tmp["NEW"] == "Y" ? " feed-post-block-yellow-corner" : "")?>"></div><?
	if ($arParams["PREORDER"] != "Y"): ?><?=$arParams["NAV_STRING"]?><? endif;
	?><!--RCRDLIST_<?=$arParams["ENTITY_XML_ID"]?>--><?
	$collapsedMessages = 0;
	$collapsedMessagesBlockIsCollapsed = true;
	$collapsedMessagesBlock = null;

	foreach ($arParams["RECORDS"] as $res)
	{
		if (intval($res["ID"]) <= 0)
		{
			continue;
		}

		if ($res["COLLAPSED"] === "Y")
		{
			$collapsedMessages++;
			if ($collapsedMessagesBlock === null)
			{
				ob_start();
	?>
		<div class="feed-com-collapsed" data-bx-role="collapsed-block">
			<input type="checkbox" id="collapsed_switcher_<?=$arParams["ENTITY_XML_ID"]?>_<?=$res["ID"]?>" #COLLAPSED_MESSAGES_BLOCK_IS_COLLAPSED#>
			<label for="collapsed_switcher_<?=$arParams["ENTITY_XML_ID"]?>_<?=$res["ID"]?>"
				data-bx-collapse-role="show">
				<a class="feed-com-collapsed-btn">
					<?=GetMessage("MPL_SHOW_COLLAPSED_COMMENTS")?> (#COLLAPSED_MESSAGES_COUNT#)
				</a>
			</label>
			<label for="collapsed_switcher_<?=$arParams["ENTITY_XML_ID"]?>_<?=$res["ID"]?>"
				data-bx-collapse-role="hide">
				<a class="feed-com-collapsed-btn">
					<?=GetMessage("MPL_HIDE_COLLAPSED_COMMENTS")?> (#COLLAPSED_MESSAGES_COUNT#)
				</a>
			</label>
			<div class="feed-com-collapsed-block">
				<div class="feed-com-collapsed-block-inner">
					#COLLAPSED_MESSAGES_BLOCK#
				</div>
			</div>
<script>
	BX.ready(function() {
		BX("collapsed_switcher_<?=$arParams["ENTITY_XML_ID"]?>_<?=$res["ID"]?>").addEventListener("click", function() {
			var serviceBlock = this.parentNode.querySelector("div.feed-com-collapsed-block");
			serviceBlock.style.height = this.checked ? (serviceBlock.children[0].offsetHeight + "px") : 0;
			serviceBlock.style.opacity = this.checked ? 1 : 0;
			this.classList.remove("feed-com-collapsed-once");
		});
	});
</script>
		</div><?
				$collapsedMessagesBlock = ob_get_clean();
				ob_start();
			}
		}
		else if ($collapsedMessagesBlock !== null)
		{
			?><?=str_replace([
					"#COLLAPSED_MESSAGES_BLOCK_IS_COLLAPSED#",
					"#COLLAPSED_MESSAGES_COUNT#",
					"#COLLAPSED_MESSAGES_BLOCK#"
				], [
					$collapsedMessagesBlockIsCollapsed ? "" : "checked class=\"feed-com-collapsed-once\" ",
					$collapsedMessages,
					ob_get_clean()
				],
				$collapsedMessagesBlock
			);
			$collapsedMessagesBlock = null;
			$collapsedMessages = 0;
			$collapsedMessagesBlockIsCollapsed = true;
		}

		$res["AUTHOR"] = (is_array($res["AUTHOR"]) ? $res["AUTHOR"] : array());
		$isMessageBlank = !(array_key_exists("POST_MESSAGE_TEXT", $res) && $res["POST_MESSAGE_TEXT"] !== null);
		$collapsedMessagesBlockIsCollapsed = ($res["NEW"] == "Y" || $res["ID"] == $arParams["RESULT"] ? false : $collapsedMessagesBlockIsCollapsed);
		?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-<?=$res["ID"]?>-cover" <?
			?>bx-mpl-xml-id="<?=$arParams["ENTITY_XML_ID"]?>" <?
			?>bx-mpl-entity-id="<?=$res["ID"]?>" <?
			?>bx-mpl-read-status="<?=(($res["NEW"] == "Y" ? "new" : "old"))?>" <?
			?>bx-mpl-blank-status="<?=($isMessageBlank ? "blank" : "full")?>" <?
			?>bx-mpl-block="main" <?
			?>class="feed-com-block-cover"><?
				?><?=$this->__component->parseTemplate($res, $arParams, ($isMessageBlank ? $blankTemplate : $template));?>
			</div>
		<?
	}
	if ($collapsedMessagesBlock !== null)
	{
		?><?=str_replace([
				"#COLLAPSED_MESSAGES_BLOCK_IS_COLLAPSED#",
				"#COLLAPSED_MESSAGES_COUNT#",
				"#COLLAPSED_MESSAGES_BLOCK#"
			], [
				$collapsedMessagesBlockIsCollapsed ? "" : "checked class=\"feed-com-collapsed-once\" ",
				$collapsedMessages,
				ob_get_clean()
			],
			$collapsedMessagesBlock
		);
		$collapsedMessagesBlock = null;
		$collapsedMessages = 0;
		$collapsedMessagesBlockIsCollapsed = true;
	}

	?><!--RCRDLIST_END_<?=$arParams["ENTITY_XML_ID"]?>--><?
	if ($arParams["PREORDER"] == "Y"): ?><?=$arParams["NAV_STRING"]?><? endif;
}
$ajaxParams = [];
if ($this->__component->__parent instanceof \Bitrix\Main\Engine\Contract\Controllerable)
{
	$ajaxParams = [
		"componentName" => $this->__component->__parent->getName(),
		"processComment" => method_exists($this->__component->__parent, "processCommentAction"),
		"navigateComment" => method_exists($this->__component->__parent, "navigateCommentAction"),
		"getComment" => method_exists($this->__component->__parent, "getCommentAction"),
		"readComment" => method_exists($this->__component->__parent, "readCommentAction"),
		"params" => $this->__component->__parent->getSignedParameters()
	];
}
?>
<script type="text/javascript">
BX.ready(function(){
	window["UC"]["<?=$arParams["ENTITY_XML_ID"]?>"] = new FCList({
		EXEMPLAR_ID : '<?=CUtil::JSEscape(htmlspecialcharsbx($arParams["EXEMPLAR_ID"]))?>',
		ENTITY_XML_ID : '<?=CUtil::JSEscape($arParams["ENTITY_XML_ID"])?>',
		FORM_ID : '<?=CUtil::JSEscape($arParams["FORM_ID"])?>',
		template : '<?=CUtil::JSEscape($template)?>',

		mainNode : BX('<?=$eventNodeId?>'),
		navigationNode : BX('<?=$prefixNode?>_page_nav'),
		nodeForOldMessages : BX('record-<?=$prefixNode?>-hidden'),
		nodeForNewMessages : BX('record-<?=$prefixNode?>-new'),
		nodeFormHolder : BX('record-<?=$prefixNode?>-form-holder'),

		order : '<?=($arParams["PREORDER"] == "N" ? "DESC" : "ASC")?>',
		mid : <?=intval($arParams["LAST_RECORD"]["ID"])?>,
		rights : {
				MODERATE : '<?=$arParams["RIGHTS"]["MODERATE"]?>',
				EDIT : '<?=$arParams["RIGHTS"]["EDIT"]?>',
				DELETE : '<?=$arParams["RIGHTS"]["DELETE"]?>',
				CREATETASK : '<?=$arParams["RIGHTS"]["CREATETASK"]?>'
			},
		sign : '<?=$arParams["SIGN"]?>',
		ajax : <?=CUtil::PhpToJSObject($ajaxParams)?>
		},
		{
			VIEW_URL : '<?=CUtil::JSEscape($arParams["~VIEW_URL"])?>',
			EDIT_URL : '<?=CUtil::JSEscape($arParams["~EDIT_URL"])?>',
			MODERATE_URL : '<?=CUtil::JSEscape($arParams["~MODERATE_URL"])?>',
			DELETE_URL : '<?=CUtil::JSEscape($arParams["~DELETE_URL"])?>',
			AUTHOR_URL : '<?=CUtil::JSEscape($arParams["~AUTHOR_URL"])?>',
			AUTHOR_URL_PARAMS: <?=(isset($arParams["AUTHOR_URL_PARAMS"]) ? CUtil::PhpToJSObject($arParams["AUTHOR_URL_PARAMS"]) : '{}')?>,

			AVATAR_SIZE : '<?=CUtil::JSEscape($arParams["AVATAR_SIZE"])?>',
			NAME_TEMPLATE : '<?=CUtil::JSEscape($arParams["~NAME_TEMPLATE"])?>',
			SHOW_LOGIN : '<?=CUtil::JSEscape($arParams["SHOW_LOGIN"])?>',

			DATE_TIME_FORMAT : '<?=CUtil::JSEscape($arParams["~DATE_TIME_FORMAT"])?>',
			LAZYLOAD : '<?=$arParams["LAZYLOAD"]?>',
			NOTIFY_TAG : '<?=CUtil::JSEscape($arParams["~NOTIFY_TAG"])?>',
			NOTIFY_TEXT : '<?=CUtil::JSEscape($arParams["~NOTIFY_TEXT"])?>',
			SHOW_POST_FORM : '<?=CUtil::JSEscape($arParams["SHOW_POST_FORM"])?>',
			BIND_VIEWER : '<?=$arParams["BIND_VIEWER"]?>'
		}
	);
	<?if ($arParams["BIND_VIEWER"] === "Y")
	{?>
	setTimeout(function(){
		if (BX["viewElementBind"])
		{
			BX.viewElementBind(
				BX('<?=$eventNodeId?>'), {},
				function(node){
					return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
				}
			);
		}
	}, 500);
	<?}?>
});
</script>
<div id="record-<?=$prefixNode?>-new"></div><?
if (!empty($arParams["ERROR_MESSAGE"]))
{
	?><div class="feed-add-error"><span class="feed-add-info-text"><span class="feed-add-info-icon"></span>
		<b><?=GetMessage("B_B_PC_COM_ERROR")?></b><br /><?=$arParams["ERROR_MESSAGE"]?></span></div><?
}
include_once(__DIR__."/messages.php");
if ($arParams["SHOW_POST_FORM"] == "Y")
{
	$AUTHOR_AVATAR = __mpl_get_avatar();

	?><div class="feed-com-add-box-outer" id="record-<?=$prefixNode?>-form-holder">

		<div class="ui-icon ui-icon-common-user feed-com-avatar feed-com-avatar-<?=($AUTHOR_AVATAR == '/bitrix/images/1.gif' ? "N" : "Y")?>"><?
			?>
			<i></i>
			<img width="37" height="37" src="<?=\CHTTP::urnEncode($AUTHOR_AVATAR)?>">
			<?
		?></div>

		<div class="feed-com-add-box">
			<div id="record-<?=$arParams["ENTITY_XML_ID"]?>-0-placeholder" class="blog-comment-edit feed-com-add-block blog-post-edit" style="display:none;"><?
			?></div><?
			?><div class="feed-com-footer" onclick="BX.onCustomEvent(BX('<?=$eventNodeId?>'), 'onReply', [this]);" <?
			?><?if ($arParams['SHOW_MINIMIZED'] != "Y"): ?> style="display:none;" <? endif; ?>><?
				?><div class="feed-com-add"><?
					?><a class="feed-com-add-link" href="javascript:void(0);" style="outline: none;" hidefocus="true"><?=GetMessage("B_B_MS_ADD_COMMENT")?></a><?
				?></div><?
			?></div><?
			?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-writers-block" class="feed-com-writers" style="display:none;">
				<div id="record-<?=$arParams["ENTITY_XML_ID"]?>-writers" class="feed-com-writers-wrap"></div>
				<div class="feed-com-writers-pen"></div>
			</div>
		</div>
	</div>
<?
}
?></div><?
