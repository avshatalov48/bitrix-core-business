<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @var array $arParams
 * @var array $arResult
 * @var MainPostList $component
*/

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI;
use Bitrix\Main\Web\Json;

UI\Extension::load([
	'ui.design-tokens',
	'ui.animations',
	'main.rating',
	'ui.tooltip',
	'ui.icons.b24',
	'ui.urlpreview',
	'socialnetwork.livefeed',
	'popup',
	'ui.icon-set.main',
	'main.core',
	'ui.avatar'
]);

$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css");
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css");
Asset::getInstance()->addJs("/bitrix/components/bitrix/main.post.list/templates/.default/scripts_for_form.js");
Asset::getInstance()->addJs("/bitrix/components/bitrix/main.post.list/templates/.default/scripts_for_form2.js");
if (CModule::IncludeModule("im"))
{
	Asset::getInstance()->addJs("/bitrix/components/bitrix/main.post.list/templates/.default/scripts_for_im.js");
}

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
	Asset::getInstance()->addJs("/bitrix/js/main/rating_like.js");
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
			<div class="ui-icon ui-icon-common-user feed-com-avatar #AUTHOR_AVATAR_STYLE# feed-com-avatar-#AUTHOR_AVATAR_IS#"><i></i><img src="#AUTHOR_AVATAR#" width="<?=$arParams["AVATAR_SIZE"]?>" height="<?=$arParams["AVATAR_SIZE"]?>" alt="" /></div>
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
					#MOBILE_HINTS#
					<a class="feed-time feed-com-time" href="#VIEW_URL##com#ID#" rel="nofollow" target="_top">#DATE#</a>
				</div>
				#AFTER_HEADER#
				#BEFORE#
				<div class="feed-com-text">
					<div
					 class="feed-com-text-inner"
					 bx-content-view-xml-id="#CONTENT_ID#"
					 bx-content-view-save="N"
					 id="feed-com-text-inner-#CONTENT_ID#"
					 bx-mpl-block="body">
						<div class="feed-com-text-inner-inner" id="record-#FULL_ID#-text" bx-mpl-block="text">
							<div>#TEXT#</div>
						</div>
					</div>
					<div class="feed-post-text-more" bx-mpl-block="more-button" <?php
						?>onclick="BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'onExpandComment', [this]); return BX.PreventDefault(this);"
						 <?php
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
			<?php
			if ($arParams["SHOW_POST_FORM"] == "Y")
			{
				?><a href="javascript:void(0);" class="feed-com-reply feed-com-reply-#SHOW_POST_FORM#" <?php
				?>id="record-#FULL_ID#-actions-reply" <?php
				?>onclick="BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'onReply', [this, 'reply_button']);" <?php
				?>bx-mpl-author-id="#AUTHOR_ID#" <?php
				?>bx-mpl-author-gender="#AUTHOR_PERSONAL_GENDER#" <?php
				?>bx-mpl-author-name="#AUTHOR_NAME#" <?php
				?>data-slider-ignore-autobinding="true"><?= Loc::getMessage('BLOG_C_REPLY') ?></a><?php
			}

			if (!$arParams["bPublicPage"])
			{
				?><a href="#" <?php
				?> id="record-#FULL_ID#-actions" <?php
				?> bx-mpl-view-url="#VIEW_URL#" bx-mpl-view-show="#VIEW_SHOW#" <?php
				?> bx-mpl-edit-url="#EDIT_URL#" bx-mpl-edit-show="#EDIT_SHOW#" <?php
				?> bx-mpl-moderate-url="#MODERATE_URL#" bx-mpl-moderate-show="#MODERATE_SHOW#" bx-mpl-moderate-approved="#APPROVED#" <?php
				?> bx-mpl-delete-url="#DELETE_URL###ID#" bx-mpl-delete-show="#DELETE_SHOW#" <?php
				?> bx-mpl-createtask-show="#CREATETASK_SHOW#" <?php
				?> bx-mpl-createsubtask-show="#CREATESUBTASK_SHOW#" <?php
				?> bx-mpl-post-entity-type="#POST_ENTITY_TYPE#" <?php
				?> bx-mpl-post-entity-xml-id="#ENTITY_XML_ID#" <?php
				?> bx-mpl-comment-entity-type="#COMMENT_ENTITY_TYPE#" <?php
				?> onclick="BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'onShowActions', [this, '#ID#']); return BX.PreventDefault(this);" <?php
				?> class="feed-post-more-link feed-post-more-link-#VIEW_SHOW#-#EDIT_SHOW#-#MODERATE_SHOW#-#DELETE_SHOW#"><?php
					?><span class="feed-post-more-text"><?= Loc::getMessage('BLOG_C_BUTTON_MORE') ?></span><?php
				?></a><?php
			}
			?>
			#AFTER_ACTIONS#
		</div>
		#AFTER_RECORD#<?php
		?><script>BX.ready(function() { BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'OnUCCommentIsInDOM', ['#ID#', BX('<?=$eventNodeIdTemplate?>')]);});</script><?php
	?></div>
	<div id="record-#FULL_ID#-placeholder" bx-mpl-block="edit-placeholder" class="blog-comment-edit feed-com-add-block blog-post-edit feed-com-add-box" style="display:none;"></div>
	<!--RCRD_END_#FULL_ID#-->
<?php
$template = preg_replace("/[\t\n]/", "", ob_get_clean());
ob_start();
?>
	<script>
		BX.ready(function() {
			BX.onCustomEvent(BX('<?=$eventNodeIdTemplate?>'), 'OnUCBlankCommentIsInDOM', ['#ID#', BX('<?=$eventNodeIdTemplate?>')]);
		});
	</script>
	<div style="position: absolute; width: 1px; height: 1px; opacity: 0;"></div>
<?php
$blankTemplate =  preg_replace("/[\t\n]/", "", ob_get_clean());
?><div id="<?=$eventNodeId?>"><?php
if (empty($arParams["RECORDS"]))
{
	?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-corner" class="feed-com-corner"></div><?php
}
else
{
	if ($arParams["NAV_STRING"])
	{
		if ($arResult["NAV_STRING_COUNT_MORE"] > 0)
		{
			ob_start();

			if ($arParams["PREORDER"] === "Y")
			{
				?><div id="record-<?=$prefixNode?>-hidden" class="feed-hidden-post" style="display:none; overflow:hidden;"></div> <?php
			}
			?><div class="feed-com-header"><?php

				$navStringCaption = $arParams["PREORDER"] === "Y"
					? Loc::getMessage('BLOG_C_VIEW1_MSGVER_1', ['#COMMENTS_COUNT#' => $arResult["NAV_STRING_COUNT_MORE"]])
					: Loc::getMessage('BLOG_C_VIEW2_MSGVER_1', ['#COMMENTS_COUNT#' => $arResult["NAV_STRING_COUNT_MORE"]])
				;
				?><a class="feed-com-all" href="<?=$arParams["NAV_STRING"]?>"<?php
					?> id="<?= $prefixNode ?>_page_nav" <?php
					?> bx-mpl-comments-count="<?= $arResult["NAV_STRING_COUNT_MORE"] ?>"<?php
					?> data-slider-ignore-autobinding="true"><?php
					?><?= $navStringCaption ?><i></i><?php
				?></a><?php
				?><span class="feed-com-loader-informer" id="<?= $prefixNode ?>_page_nav_loader" style="display:none;"><?= Loc::getMessage('BLOG_C_LOADING')?></span><?php
			?></div><?php
			if ($arParams["PREORDER"] !== "Y")
			{
				?><div id="record-<?=$prefixNode?>-hidden" class="feed-hidden-post" style="display:none; overflow:hidden;"></div> <?php
			}
			$arParams["NAV_STRING"] = ob_get_clean();
		}
		else
		{
			$arParams["NAV_STRING"] = "";
		}
	}
	$tmp = reset($arParams["RECORDS"]);
	?><div class="feed-com-corner<?=($arParams["NAV_STRING"] === "" && $tmp["NEW"] === "Y" ? " feed-post-block-yellow-corner" : "")?>"></div><?php
	if ($arParams["PREORDER"] !== "Y")
	{
		?><?= $arParams["NAV_STRING"] ?><?php
	}
	?><!--RCRDLIST_<?=$arParams["ENTITY_XML_ID"]?>--><?php
	$collapsedMessages = 0;
	$collapsedMessagesBlockIsCollapsed = true;
	$collapsedMessagesBlock = null;

	foreach ($arParams["RECORDS"] as $res)
	{
		if ((int)$res["ID"] <= 0)
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
					<?= Loc::getMessage('MPL_SHOW_COLLAPSED_COMMENTS_MSGVER_1')?>
				</a>
			</label>
			<label for="collapsed_switcher_<?=$arParams["ENTITY_XML_ID"]?>_<?=$res["ID"]?>"
				data-bx-collapse-role="hide">
				<a class="feed-com-collapsed-btn">
					<?= Loc::getMessage('MPL_HIDE_COLLAPSED_COMMENTS_MSGVER_1')?>
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
		</div><?php
				$collapsedMessagesBlock = ob_get_clean();
				ob_start();
			}
		}
		else if ($collapsedMessagesBlock !== null)
		{
			?><?= str_replace([
					"#COLLAPSED_MESSAGES_BLOCK_IS_COLLAPSED#",
					"#COLLAPSED_MESSAGES_COUNT#",
					"#COLLAPSED_MESSAGES_BLOCK#"
				], [
					$collapsedMessagesBlockIsCollapsed ? "" : "checked class=\"feed-com-collapsed-once\" ",
					$collapsedMessages,
					ob_get_clean()
				],
				$collapsedMessagesBlock
			) ?><?php
			$collapsedMessagesBlock = null;
			$collapsedMessages = 0;
			$collapsedMessagesBlockIsCollapsed = true;
		}

		$res["AUTHOR"] = (is_array($res["AUTHOR"]) ? $res["AUTHOR"] : array());
		$isMessageBlank = !(array_key_exists("POST_MESSAGE_TEXT", $res) && $res["POST_MESSAGE_TEXT"] !== null);
		$collapsedMessagesBlockIsCollapsed = ($res["NEW"] === "Y" || $res["ID"] == $arParams["RESULT"] ? false : $collapsedMessagesBlockIsCollapsed);
		?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-<?=$res["ID"]?>-cover" <?php
			?>bx-mpl-xml-id="<?=$arParams["ENTITY_XML_ID"]?>" <?php
			?>bx-mpl-entity-id="<?=$res["ID"]?>" <?php
			?>bx-mpl-read-status="<?=(($res["NEW"] == "Y" ? "new" : "old"))?>" <?php
			?>bx-mpl-blank-status="<?=($isMessageBlank ? "blank" : "full")?>" <?php
			?>bx-mpl-block="main" <?php
			?>class="feed-com-block-cover"><?php
				?><?= $component->parseTemplate($res, $arParams, ($isMessageBlank ? $blankTemplate : $template)) ?>
			</div>
		<?php
	}
	if ($collapsedMessagesBlock !== null)
	{
		?><?= str_replace([
				"#COLLAPSED_MESSAGES_BLOCK_IS_COLLAPSED#",
				"#COLLAPSED_MESSAGES_COUNT#",
				"#COLLAPSED_MESSAGES_BLOCK#"
			], [
				$collapsedMessagesBlockIsCollapsed ? "" : "checked class=\"feed-com-collapsed-once\" ",
				$collapsedMessages,
				ob_get_clean()
			],
			$collapsedMessagesBlock
		) ?><?php
		$collapsedMessagesBlock = null;
		$collapsedMessages = 0;
		$collapsedMessagesBlockIsCollapsed = true;
	}

	?><!--RCRDLIST_END_<?=$arParams["ENTITY_XML_ID"]?>--><?php
	if ($arParams["PREORDER"] == "Y")
	{
		?><?= $arParams["NAV_STRING"] ?><?php
	}
}
$ajaxParams = [];
if ($component->__parent instanceof \Bitrix\Main\Engine\Contract\Controllerable)
{
	$ajaxParams = [
		"componentName" => $component->__parent->getName(),
		"processComment" => method_exists($component->__parent, "processCommentAction"),
		"navigateComment" => method_exists($component->__parent, "navigateCommentAction"),
		"getComment" => method_exists($component->__parent, "getCommentAction"),
		"readComment" => method_exists($component->__parent, "readCommentAction"),
		"params" => $component->__parent->getSignedParameters()
	];
}

//AddMessage2Log($arParams["FORM_ID"], '', 50);
?>
<script>
BX.ready(function(){
	window["UC"]["<?=$arParams["ENTITY_XML_ID"]?>"] = new FCList({
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

		order : '<?=($arParams["PREORDER"] == "N" ? "DESC" : "ASC")?>',
		mid : <?= (int)($arParams["LAST_RECORD"]["ID"] ?? 0) ?>,
		rights : {
				MODERATE : '<?=$arParams["RIGHTS"]["MODERATE"]?>',
				EDIT : '<?=$arParams["RIGHTS"]["EDIT"]?>',
				DELETE : '<?=$arParams["RIGHTS"]["DELETE"]?>',
				CREATETASK : '<?=$arParams['RIGHTS']['CREATETASK']?>',
				CREATESUBTASK : '<?= ($arParams['RIGHTS']['CREATESUBTASK'] ?? 'N') ?>',
			},
		sign : '<?=$arParams["SIGN"]?>',
		ajax : <?= Json::encode($ajaxParams) ?>
		},
		{
			VIEW_URL : '<?=CUtil::JSEscape($arParams["~VIEW_URL"] ?? '')?>',
			EDIT_URL : '<?=CUtil::JSEscape($arParams["~EDIT_URL"] ?? '')?>',
			MODERATE_URL : '<?=CUtil::JSEscape($arParams["~MODERATE_URL"] ?? '')?>',
			DELETE_URL : '<?=CUtil::JSEscape($arParams["~DELETE_URL"] ?? '')?>',
			AUTHOR_URL : '<?=CUtil::JSEscape($arParams["~AUTHOR_URL"] ?? '')?>',
			AUTHOR_URL_PARAMS: <?=(isset($arParams["AUTHOR_URL_PARAMS"]) ? Json::encode($arParams["AUTHOR_URL_PARAMS"]) : '{}') ?>,

			AVATAR_SIZE : '<?=CUtil::JSEscape($arParams["AVATAR_SIZE"])?>',
			NAME_TEMPLATE : '<?=CUtil::JSEscape($arParams["~NAME_TEMPLATE"])?>',
			SHOW_LOGIN : '<?=CUtil::JSEscape($arParams["SHOW_LOGIN"])?>',

			DATE_TIME_FORMAT : '<?=CUtil::JSEscape($arParams["~DATE_TIME_FORMAT"])?>',
			LAZYLOAD : '<?=$arParams["LAZYLOAD"]?>',
			NOTIFY_TAG : '<?=CUtil::JSEscape($arParams["~NOTIFY_TAG"])?>',
			NOTIFY_TEXT : '<?=CUtil::JSEscape($arParams["~NOTIFY_TEXT"])?>',
			SHOW_POST_FORM : '<?=CUtil::JSEscape($arParams["SHOW_POST_FORM"])?>',
			BIND_VIEWER : '<?=$arParams["BIND_VIEWER"]?>',
		}
	);
	<?php
	if ($arParams["BIND_VIEWER"] === "Y")
	{
		?>
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
		<?php
	}
	?>
});
</script>

<script>
	BX.ready(() => {
		new BX.Main.PostList.MobileButton({
			containerId: '<?=CUtil::JSEscape($eventNodeId)?>',
		});
	});
</script>
<div id="record-<?=$prefixNode?>-new"></div><?php
if (!empty($arParams["ERROR_MESSAGE"]))
{
	?><div class="feed-add-error"><span class="feed-add-info-text"><span class="feed-add-info-icon"></span>
		<b><?= Loc::getMessage('B_B_PC_COM_ERROR') ?></b><br /><?= $arParams["ERROR_MESSAGE"] ?></span></div><?php
}

?>
<script>
<? if (IsModuleInstalled("im")): ?>
if (window.SPC)
{
	SPC.notifyManagerShow();
}
<? endif ?>

<? if (IsModuleInstalled("socialnetwork") && $USER instanceof CUser): ?>
if (BX.CommentAux)
{
	BX.CommentAux.init({
		currentUserSonetGroupIdList: <?= Json::encode(\Bitrix\Socialnetwork\ComponentHelper::getUserSonetGroupIdList($USER->GetID(), SITE_ID)) ?>,
		mobile: false,
		publicSection: <?=(isset($arParams["bPublicPage"]) && $arParams["bPublicPage"] ? 'true' : 'false')?>,
		currentExtranetUser: <?=($arResult["currentExtranetUser"] ? 'true' : 'false')?>,
		availableUsersList: <?= Json::encode($arResult["availableUsersList"]) ?>,
	});
}
<? endif ?>

BX.message({
	MPL_HAVE_WRITTEN : '<?=GetMessageJS("MPL_HAVE_WRITTEN_MSGVER_1")?>',
	MPL_HAVE_WRITTEN_M : '<?=GetMessageJS("MPL_HAVE_WRITTEN_M_MSGVER_1")?>',
	MPL_HAVE_WRITTEN_F : '<?=GetMessageJS("MPL_HAVE_WRITTEN_F_MSGVER_1")?>',
	B_B_MS_LINK : '<?=GetMessageJS("B_B_MS_LINK2")?>',
	MPL_MES_HREF : '<?=GetMessageJS("MPL_MES_HREF")?>',
	BPC_MES_EDIT : '<?=GetMessageJS("BPC_MES_EDIT")?>',
	BPC_MES_HIDE : '<?=GetMessageJS("BPC_MES_HIDE")?>',
	BPC_MES_SHOW : '<?=GetMessageJS("BPC_MES_SHOW")?>',
	BPC_MES_DELETE : '<?=GetMessageJS("BPC_MES_DELETE")?>',
	BPC_MES_DELETE_POST_CONFIRM : '<?=GetMessageJS("BPC_MES_DELETE_POST_CONFIRM")?>',
	BPC_MES_CREATE_TASK_RESULT : '<?=GetMessageJS("BPC_MES_CREATE_TASK_RESULT")?>',
	BPC_MES_DELETE_TASK_RESULT : '<?=GetMessageJS("BPC_MES_DELETE_TASK_RESULT")?>',
	BPC_MES_CREATE_TASK : '<?=GetMessageJS("BPC_MES_CREATE_TASK")?>',
	BPC_MES_CREATE_SUBTASK : '<?=GetMessageJS("BPC_MES_CREATE_SUBTASK")?>',
	JERROR_NO_MESSAGE : '<?=GetMessageJS("JERROR_NO_MESSAGE")?>',
	BLOG_C_HIDE : '<?=GetMessageJS("BLOG_C_HIDE")?>',
	MPL_IS_EXTRANET_SITE: '<?=(CModule::IncludeModule("extranet") && CExtranet::IsExtranetSite() ? 'Y' : 'N')?>',
	JQOUTE_AUTHOR_WRITES : '<?=GetMessageJS("JQOUTE_AUTHOR_WRITES")?>',
	FC_ERROR : '<?=GetMessageJS("B_B_PC_COM_ERROR")?>',
	MPL_SAFE_EDIT : '<?=GetMessageJS('MPL_SAFE_EDIT')?>',
	MPL_ERROR_OCCURRED : '<?=GetMessageJS('MPL_ERROR_OCCURRED')?>',
	MPL_CLOSE : '<?=GetMessageJS('MPL_CLOSE')?>',
	MPL_MOBILE_HINTS : '<?=GetMessageJS('MPL_MOBILE_HINTS')?>',
	MPL_MOBILE_HINTS_DETAILS : '<?=GetMessageJS('MPL_MOBILE_HINTS_DETAILS')?>',
	MPL_MOBILE_POPUP_TITLE : '<?=GetMessageJS('MPL_MOBILE_POPUP_TITLE')?>',
	MPL_MOBILE_POPUP_BOTTOM_TEXT : '<?=GetMessageJS('MPL_MOBILE_POPUP_BOTTOM_TEXT')?>',
	MPL_LINK_COPIED : '<?=GetMessageJS('MPL_LINK_COPIED')?>'
	<?
		if (IsModuleInstalled("socialnetwork"))
		{
			?>
			, MPL_WORKGROUPS_PATH : '<?=CUtil::JSEscape(COption::GetOptionString("socialnetwork", "workgroups_page", SITE_DIR."workgroups/", SITE_ID))?>'
			<?
		}
	?>,
	MPL_QUOTE_COPILOT: '<?= GetMessageJS('MPL_QUOTE_COPILOT')?>',
	});
</script>
<?php

if ($arParams["SHOW_POST_FORM"] == "Y")
{
	$AUTHOR_AVATAR = $component->getAvatar();

	?><div class="feed-com-add-box-outer" id="record-<?= $prefixNode ?>-form-holder">

		<div
			class="
				ui-icon
				ui-icon-common-user
				feed-com-avatar
				<?= ($arResult['AUTHOR']['IS_COLLABER'] ?? false) ? 'feed-com-avatar-collaber' : '' ?>
				feed-com-avatar-<?= ($AUTHOR_AVATAR === '/bitrix/images/1.gif' ? "N" : "Y") ?>
			"
		>
			<i></i>
			<img width="37" height="37" src="<?= \Bitrix\Main\Web\Uri::urnEncode($AUTHOR_AVATAR) ?>" alt="">
			<?php
		?></div>

		<div class="feed-com-add-box">
			<div id="record-<?=$arParams["ENTITY_XML_ID"]?>-0-placeholder" class="blog-comment-edit feed-com-add-block blog-post-edit" style="display:none;"><?php
			?></div><?php

			$style = ($arParams['SHOW_MINIMIZED'] != "Y" ? 'style="display:none;"' : '');

			?><div class="feed-com-footer" onclick="BX.onCustomEvent(BX('<?=$eventNodeId?>'), 'onReply', [this]);" <?= $style ?>><?php
				?><div class="feed-com-add"><?php
					?><a class="feed-com-add-link" href="javascript:void(0);" style="outline: none;" hidefocus="true"><?= Loc::getMessage("B_B_MS_ADD_COMMENT") ?></a><?php
				?></div><?php
			?></div><?php
			?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-writers-block" class="feed-com-writers" style="display:none;">
				<div id="record-<?=$arParams["ENTITY_XML_ID"]?>-writers" class="feed-com-writers-wrap"></div>
				<div class="feed-com-writers-pen"></div>
			</div>
		</div>
		<div class="feed-com-add-box-dnd-notice">
			<div class="feed-com-add-box-dnd-notice-inner"></div>
		</div>
	</div>
	<?php
}
?></div><?php
