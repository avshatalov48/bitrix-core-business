<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 * @var string $templateFolder
 * @var CUser $USER
 * @var MainPostList $this->__component
 */
use Bitrix\Main\Localization\Loc;

global $USER;

\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/components/bitrix/main.post.list/templates/.default/script.js");
\Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder."/script.js");
\Bitrix\Main\Page\Asset::getInstance()->addString('<link href="'.CUtil::GetAdditionalFileURL('/bitrix/js/ui/icons/base/ui.icons.base.css').'" type="text/css" rel="stylesheet" />');
\Bitrix\Main\Page\Asset::getInstance()->addString('<link href="'.CUtil::GetAdditionalFileURL('/bitrix/js/ui/icons/b24/ui.icons.b24.css').'" type="text/css" rel="stylesheet" />');

$extensionsList = [ 'uploader', 'date', 'fx', 'ls' ];
if (CModule::IncludeModule('socialnetwork'))
{
	$extensionsList[] = 'comment_aux';
}
CUtil::InitJSCore($extensionsList); // does not work

$prefixNode = $arParams["ENTITY_XML_ID"].'-'.$arParams["EXEMPLAR_ID"];
$eventNodeId = $prefixNode."_main";
$eventNodeIdTemplate = "#ENTITY_XML_ID#-#EXEMPLAR_ID#_main";

ob_start();
?>
<!--RCRD_#FULL_ID#-->
<a id="com#ID#" name="com#ID#" bx-mpl-full-id="#FULL_ID#"></a>
<div id="record-#FULL_ID#" class="post-comment-block post-comment-block-#NEW# post-comment-block-#APPROVED# #RATING_NONEMPTY_CLASS# mobile-longtap-menu#CLASSNAME#" <?=($arResult["ajax_comment"] == $comment["ID"] ? ' data-send="Y"' : '')?> <?
	?>bx-mpl-id="#FULL_ID#" <?
	?>bx-mpl-menu-show="#SHOW_MENU#" <?
	?>bx-mpl-reply-show="#SHOW_POST_FORM#" <?
	?>bx-mpl-view-url="#VIEW_URL###ID#" bx-mpl-view-show="#VIEW_SHOW#" <?
	?>bx-mpl-edit-url="#EDIT_URL#" bx-mpl-edit-show="#EDIT_SHOW#" <?
	?>bx-mpl-moderate-url="#MODERATE_URL#" bx-mpl-moderate-show="#MODERATE_SHOW#" bx-mpl-moderate-approved="#APPROVED#" <?
	?>bx-mpl-delete-url="#DELETE_URL###ID#" bx-mpl-delete-show="#DELETE_SHOW#" <?
	?>bx-mpl-createtask-show="#CREATETASK_SHOW#" <?
	?>bx-mpl-post-entity-type="#POST_ENTITY_TYPE#" <?
	?>bx-mpl-comment-entity-type="#COMMENT_ENTITY_TYPE#" <?
	?>bx-mpl-vote-id="#VOTE_ID#" <?
	?>bx-longtap-menu-eventname="BX.MPL:onGetMenuItems" <?
	?>bx-mpl-entity-xml-id="#ENTITY_XML_ID#" <?
	?>bx-mpl-comment-id="#ID#" <?
?>>
	#BEFORE_RECORD#
	<script>
	BX.ready(function()
	{
		BX.MSL.viewImageBind('record-#FULL_ID#', { tag: 'IMG', attr: 'data-bx-image' });
	});
	</script>
	<div class="ui-icon ui-icon-common-user post-comment-block-avatar"><i style="#AUTHOR_AVATAR_BG#"></i></div>
	<div class="post-comment-detail">
		<div class="post-comment-balloon" onclick="mobileShowActions('#ENTITY_XML_ID#', '#ID#', arguments[0])">
			#BEFORE_HEADER#
			<!--/noindex-->
			<div class="post-comment-cont">
				<a href="#AUTHOR_URL#" class="post-comment-author #AUTHOR_EXTRANET_STYLE#" id="record-#FULL_ID#-author" bx-mpl-author-id="#AUTHOR_ID#">#AUTHOR_NAME#</a>
				<div class="post-comment-time">#DATE#</div>
				#LIKE_REACT#
			</div>
			<!--/noindex-->
			#AFTER_HEADER#
			#BEFORE#
			<div class="post-comment-wrap-outer">
				<div
				 class="post-comment-wrap"
				 bx-content-view-xml-id="#CONTENT_ID#"
				 bx-content-view-save="N" bx-mpl-block="body"
				 bx-content-view-key="#CONTENT_VIEW_KEY#"
				 bx-content-view-key-signed="#CONTENT_VIEW_KEY_SIGNED#"
				 id="post-comment-wrap-#CONTENT_ID#">
					<div class="post-comment-text" id="record-#FULL_ID#-text" bx-mpl-block="text">#TEXT#</div>
				</div>
				<div class="post-comment-more" onclick="mobileExpand(this, event)" bx-mpl-block="more-button"><div class="post-comment-more-but"></div></div>
			</div>
		</div>
		#AFTER#
		<div class="post-comment-control-box">
			#BEFORE_ACTIONS#
			<?
			if (
				!isset($arParams["SHOW_POST_FORM"])
				|| $arParams["SHOW_POST_FORM"] != 'N'
				|| !empty($arParams["REPLY_ACTION"])
			)
			{
				$action = (
					!isset($arParams["SHOW_POST_FORM"])
					|| $arParams["SHOW_POST_FORM"] != 'N'
						? "return mobileReply('#ENTITY_XML_ID#', event)"
						: $arParams["REPLY_ACTION"]
				);
				?><div class="post-comment-control-item" id="record-#FULL_ID#-reply-action" onclick="<?=$action?>" <?
					?>bx-mpl-author-id="#AUTHOR_ID#" <?
					?>bx-mpl-author-name="#AUTHOR_NAME#"><?
					?><?=Loc::getMessage('BLOG_C_REPLY')?><?
				?></div><?
			}
			?>
		</div>
	</div>
	#AFTER_RECORD#<?
	?><script>BX.ready(function() { BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'OnUCCommentIsInDOM', ['#ID#', BX('<?=$eventNodeIdTemplate?>')]);});</script><?
?></div>
<!--RCRD_END_#FULL_ID#-->
<? // post-comment-block
$template = preg_replace("/[\t\n]/", "", ob_get_clean());

ob_start();

?><div class="post-comment-block">
	<div class="ui-icon ui-icon-common-user post-comment-block-avatar"><i style="#AUTHOR_AVATAR_BG#"></i></div>
	<div class="post-comment-detail">
		<div class="post-comment-balloon">
			<div class="post-comment-cont">
				<div class="post-comment-author">#AUTHOR_NAME#</div>
				<div class="post-comment-time">#DATE#</div>
			</div>
			<!--/noindex-->
			<div class="post-comment-wrap-outer">
				<div class="post-comment-wrap">
					<div class="post-comment-text" id="record-#FULL_ID#-text">#TEXT#</div>
				</div>
				<div class="post-comment-more" onclick="mobileExpand(this, event)"><div class="post-comment-more-but"></div></div>
			</div>
			<!--/noindex-->
		</div>
		<div class="post-comment-control-box">
			<div class="post-comment-control-item"><?=Loc::getMessage('BLOG_C_REPLY')?></div>
		</div>
		<div class="post-comment-publication">
			<div class="post-comment-publication-icon"></div>
			<div class="post-comment-publication-text"><?=Loc::getMessage('MPL_MOBILE_PUBLISHING')?></div>
		</div>
		<div class="post-comment-error">
			<div class="post-comment-error-icon"></div>
			<div class="post-comment-error-text"></div>
		</div>
	</div>
</div><?

$avatar = \CFile::ResizeImageGet(
	$USER->GetParam("PERSONAL_PHOTO"),
	array(
		"width" => $arParams["AVATAR_SIZE"],
		"height" => $arParams["AVATAR_SIZE"]
	),
	BX_RESIZE_IMAGE_EXACT
);
$name = CUser::FormatName(
	$arParams["NAME_TEMPLATE"],
	array(
		"NAME" => $USER->getFirstName(),
		"LAST_NAME" => $USER->getLastName(),
		"SECOND_NAME" => $USER->getSecondName(),
		"LOGIN" => $USER->getLogin(),
		"NAME_LIST_FORMATTED" => "",
	),
	($arParams["SHOW_LOGIN"] != "N"),
	false
);

$thumb = preg_replace(array(
		"/[\t\n]/",
		"/\\#AUTHOR_ID\\#/",
		"/\\#AUTHOR_AVATAR_IS\\#/",
		"/\\#AUTHOR_AVATAR\\#/",
		"/\\#AUTHOR_AVATAR_BG\\#/",
		"/\\#AUTHOR_NAME\\#/"

	), array(
		"",
		$USER->getId(),
		($avatar ? "Y" : "N"),
		($avatar ? $avatar["src"] : ''),
		($avatar ? "background-image:url('".$avatar["src"]."')" : ''),
		htmlspecialcharsbx($name)
	), ob_get_clean());

?><div id="<?=$eventNodeId?>"><?
if (empty($arParams["RECORDS"]))
{
	// For the future developing
}
else
{
	if ($arParams["NAV_STRING"])
	{
		if ($arResult["NAV_STRING_COUNT_MORE"] > 0)
		{
			ob_start();

			if ($arParams["PREORDER"] == "Y")
			{
				?><div id="record-<?=$prefixNode?>-hidden" class="feed-hidden-post" style="display:none; overflow:hidden;"></div> <?
			}

			?><div class="post-comments-link-cont">
				<a href="<?=$arParams["NAV_STRING"]?>" id="<?=$prefixNode?>_page_nav" class="post-comments-link" bx-mpl-comments-count="<?=$arResult["NAV_STRING_COUNT_MORE"]?>"><?
					?><?=Loc::getMessage("BLOG_C_VIEW")?><?
					?><span class="post-comments-link-count"><?=$arResult["NAV_STRING_COUNT_MORE"]?></span><?
				?></a>
				<span class="post-comments-link-loader-informer" id="<?=$prefixNode?>_page_nav_loader" style='display: none;'><?=Loc::getMessage("BLOG_C_LOADING")?></span>
			</div><?
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
	reset($arParams["RECORDS"]);

	if ($arParams["PREORDER"] != "Y"): ?><?=$arParams["NAV_STRING"]?><? endif;
	$iCount = 0;
	?><!--RCRDLIST_<?=$arParams["ENTITY_XML_ID"]?>--><?
	$collapsedMessages = 0;
	$collapsedMessagesBlockIsCollapsed = true;
	$collapsedMessagesBlock = null;

	foreach ($arParams["RECORDS"] as $key => $res)
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
				<label for="collapsed_switcher_<?=$arParams["ENTITY_XML_ID"]?>_<?=$res["ID"]?>" data-bx-collapse-role="show">
					<div class="post-comment-control-item">
						<?=GetMessage("MPL_SHOW_COLLAPSED_COMMENTS")?> (#COLLAPSED_MESSAGES_COUNT#)
					</div>
				</label>
				<label for="collapsed_switcher_<?=$arParams["ENTITY_XML_ID"]?>_<?=$res["ID"]?>" data-bx-collapse-role="hide">
					<div class="post-comment-control-item">
						<?=GetMessage("MPL_HIDE_COLLAPSED_COMMENTS")?> (#COLLAPSED_MESSAGES_COUNT#)
					</div>
				</label>
				<div class="feed-com-collapsed-block">
					#COLLAPSED_MESSAGES_BLOCK#
				</div>
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
			$collapsedMessagesBlockIsCollapsed ? "" : "checked",
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
		$collapsedMessagesBlockIsCollapsed = ($res["NEW"] == "Y" ? false : $collapsedMessagesBlockIsCollapsed);
		$iCount++;
		?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-<?=$res["ID"]?>-cover" <?
			?>bx-mpl-xml-id="<?=$arParams["ENTITY_XML_ID"]?>" <?
			?>bx-mpl-entity-id="<?=$res["ID"]?>" <?
			?>bx-mpl-read-status="<?=(($res["NEW"] == "Y" ? "new" : "old"))?>" <?
			?>bx-mpl-blank-status="<?=($isMessageBlank ? "blank" : "full")?>" <?
			?>bx-mpl-block="main" <?
		?>class="feed-com-block-cover"><?
		?><?=$this->__component->parseTemplate($res, $arParams, $template)?>
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
		$collapsedMessagesBlockIsCollapsed ? "" : "checked",
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
?><div id="record-<?=$prefixNode?>-new"></div><?
include_once(__DIR__."/messages.php");
if ($arParams["SHOW_POST_FORM"] == "Y")
{
	?><div id="record-<?=$prefixNode?>-form-holder" style="display:none;"></div><?
}
$ajaxParams = [];
if ($this->__component->__parent instanceof \Bitrix\Main\Engine\Contract\Controllerable)
{
	$ajaxParams = [
			"componentName" => $this->__component->__parent->getName(),
			"processComment" => method_exists($this->__component->__parent, "processCommentAction"),
			"navigateComment" => method_exists($this->__component->__parent, "navigateCommentAction"),
			"readComment" => method_exists($this->__component->__parent, "readCommentAction"),
			"params" => $this->__component->__parent->getSignedParameters()
	];
}
?>
<script>
	BX.ready(function(){

		var collapsedblocks = document.querySelectorAll('.feed-com-collapsed-block');
		for (const block of collapsedblocks) {
			block.addEventListener('transitionend', function(event){
				var position = BX.pos(event.target);
				var input = block.parentNode.querySelector('input');
				var duration;

				if (block.offsetHeight < 200 )
				{
					duration =  1500;
				}
				else
				{
					duration =  800;
				}

				var easing = new BX.easing({
					duration : duration,
					start : { scroll : window.pageYOffset || document.documentElement.scrollTop },
					finish : { scroll : position.top },
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
					step : function(state) {
						window.scrollTo(0, state.scroll);
					}
				});

				if (input.checked)
				{
					easing.animate();
				}

			});
		}

		var f = function() {
			BX.MPL.createInstance({
					EXEMPLAR_ID : '<?=CUtil::JSEscape(htmlspecialcharsbx($arParams["EXEMPLAR_ID"]))?>',
					ENTITY_XML_ID : '<?=CUtil::JSEscape($arParams["ENTITY_XML_ID"])?>',
					FORM_ID : '<?=CUtil::JSEscape($arParams["FORM_ID"])?>',
					template : '<?=CUtil::JSEscape($template)?>',

					mainNode : BX('<?=$eventNodeId?>'),
					navigationNode : BX('<?=$prefixNode?>_page_nav'),
					navigationNodeLoader : BX('<?=$prefixNode?>_page_nav_loader'),
					nodeForOldMessages : BX('record-<?=$prefixNode?>-hidden'),
					nodeForNewMessages : BX('record-<?=$prefixNode?>-new'),
					nodeFormHolder : BX('record-<?=$prefixNode?>-form-holder'),

					container : BX('record-<?=$prefixNode?>-hidden'),
					nav : BX('<?=$prefixNode?>_page_nav'),

					mid : <?=(!!$arParams["LAST_RECORD"] ? $arParams["LAST_RECORD"]["ID"] : 0)?>,
					order : '<?=($arParams["PREORDER"] == "N" ? "DESC" : "ASC")?>',
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

				SHOW_POST_FORM : '<?=CUtil::JSEscape($arParams["SHOW_POST_FORM"])?>',
				BIND_VIEWER : '<?=$arParams["BIND_VIEWER"]?>',
				USE_LIVE : <?=(isset($arParams["USE_LIVE"]) && !$arParams["USE_LIVE"] ? 'false' : 'true')?>,

				CONTENT_VIEW_KEY : '<?= CUtil::JSEscape($arParams['CONTENT_VIEW_KEY'] ?? '') ?>',
				CONTENT_VIEW_KEY_SIGNED : '<?= CUtil::JSEscape($arParams['CONTENT_VIEW_KEY_SIGNED'] ?? '') ?>',
			},
			{
				id : '<?=CUtil::JSEscape($arParams["FORM"]["ID"])?>',
				url : '<?=CUtil::JSEscape($arParams["FORM"]["URL"])?>',
				fields : <?=CUtil::PhpToJSObject($arParams["FORM"]["FIELDS"])?>
			}
			);
			BX.removeCustomEvent("main.post.list/mobile", f);
		}, scripts = [];
		BX.addCustomEvent("main.post.list/mobile", f);
		if (BX["MPL"])
		{
			f();
			return;
		}
		if (!window["FCList"])
			scripts.push('<?=\CUtil::GetAdditionalFileURL("/bitrix/components/bitrix/main.post.list/templates/.default/script.js", true)?>');
		if (!BX["MPL"])
			scripts.push('<?=\CUtil::GetAdditionalFileURL($templateFolder."/script.js", true)?>');
		BX.loadScript(scripts);
	});
</script>
</div>