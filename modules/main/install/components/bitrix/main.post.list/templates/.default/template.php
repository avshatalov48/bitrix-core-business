<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var CMain $APPLICATION
 * @var CUser $USER
 */
use \Bitrix\Main\UI;

UI\Extension::load("ui.animations");
UI\Extension::load("main.rating");
UI\Extension::load("ui.tooltip");

$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css");
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css");
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/components/bitrix/main.post.list/templates/.default/scripts_for_form.js");
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
					<a class="feed-time feed-com-time" href="#VIEW_URL##com#ID#" rel="nofollow">#DATE#</a>
				</div>
				#AFTER_HEADER#
				#BEFORE#
				<div class="feed-com-text">
					<div class="feed-com-text-inner" bx-content-view-xml-id="#CONTENT_ID#" id="feed-com-text-inner-#CONTENT_ID#" bx-content-view-save="N">
						<div class="feed-com-text-inner-inner" id="record-#FULL_ID#-text">
							<div>#TEXT#</div>
						</div>
					</div>
					<div class="feed-post-text-more" onclick="fcCommentExpand(this);" id="record-#FULL_ID#-more">
						<div class="feed-post-text-more-but"><div class="feed-post-text-more-left"></div><div class="feed-post-text-more-right"></div></div>
					</div><?
					?><script>
						BX.ready(function() {
							BX.onCustomEvent(window, 'OnUCMoreButtonAdd', [{
								bodyBlockID : 'record-#FULL_ID#-text',
								moreButtonBlockID : 'record-#FULL_ID#-more'
							}]);
						});
					</script><?
					?></div>
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
				?>onclick="window['UC']['#ENTITY_XML_ID#'].reply(this)" <?
				?>bx-mpl-author-id="#AUTHOR_ID#" <?
				?>bx-mpl-author-gender="#AUTHOR_PERSONAL_GENDER#" <?
				?>bx-mpl-author-name="#AUTHOR_NAME#"><?=GetMessage("BLOG_C_REPLY")?></a><?
			} ?>
			<a href="#" <?
			?>id="record-#FULL_ID#-actions" <?
			   ?>bx-mpl-view-url="#VIEW_URL#" bx-mpl-view-show="#VIEW_SHOW#" <?
			   ?>bx-mpl-edit-url="#EDIT_URL#" bx-mpl-edit-show="#EDIT_SHOW#" <?
			   ?>bx-mpl-moderate-url="#MODERATE_URL#" bx-mpl-moderate-show="#MODERATE_SHOW#" bx-mpl-moderate-approved="#APPROVED#" <?
			   ?>bx-mpl-delete-url="#DELETE_URL###ID#" bx-mpl-delete-show="#DELETE_SHOW#" <?
			   ?>bx-mpl-createtask-show="#CREATETASK_SHOW#" <?
			   ?>bx-mpl-post-entity-type="#POST_ENTITY_TYPE#" <?
			   ?>bx-mpl-comment-entity-type="#COMMENT_ENTITY_TYPE#" <?
			   if (!!$arParams["bPublicPage"])
			   {
			   ?>onclick="javascript:void(0); return BX.PreventDefault(this);" <?
			   }
			   else
			   {
			   ?>onclick="fcShowActions('#ENTITY_XML_ID#', '#ID#', this); return BX.PreventDefault(this);" <?
			   }
			   ?>class="feed-post-more-link feed-post-more-link-#VIEW_SHOW#-#EDIT_SHOW#-#MODERATE_SHOW#-#DELETE_SHOW#"><?
				?><span class="feed-post-more-text"><?=GetMessage("BLOG_C_BUTTON_MORE")?></span><?
			?></a>
			#AFTER_ACTIONS#
		</div>
		#AFTER_RECORD#
	</div>
	<div id="record-#FULL_ID#-placeholder" class="blog-comment-edit feed-com-add-block blog-post-edit feed-com-add-box" style="display:none;"></div>
	<!--RCRD_END_#FULL_ID#-->
<?
$template = preg_replace("/[\t\n]/", "", ob_get_clean());


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
				?><div id="<?=$arParams["ENTITY_XML_ID"]?>_hidden_records" class="feed-hidden-post" style="display:none; overflow:hidden;"></div> <?
			}
			?><div class="feed-com-header"><?
				?><a class="feed-com-all" href="<?=$arParams["NAV_STRING"]?>" id="<?=$arParams["ENTITY_XML_ID"]?>_page_nav"><?
					?><?=($arParams["PREORDER"] == "Y" ? GetMessage("BLOG_C_VIEW1") : GetMessage("BLOG_C_VIEW2"))?> <span class="feed-com-all-count"><?=$arResult["NAV_STRING_COUNT_MORE"]?></span><i></i><?
				?></a><?
			?></div><?
			if ($arParams["PREORDER"] != "Y")
			{
				?><div id="<?=$arParams["ENTITY_XML_ID"]?>_hidden_records" class="feed-hidden-post" style="display:none; overflow:hidden;"></div> <?
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
	$iCount = 0;
	?><!--RCRDLIST_<?=$arParams["ENTITY_XML_ID"]?>--><?
	foreach ($arParams["RECORDS"] as $res)
	{
		if (intval($res["ID"]) <= 0)
		{
			continue;
		}

		$res["AUTHOR"] = (is_array($res["AUTHOR"]) ? $res["AUTHOR"] : array());
		$iCount++;
		?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-<?=$res["ID"]?>-cover" class="feed-com-block-cover"><?
		?><?=$this->__component->parseTemplate($res, $arParams, $template)?>
		</div>
	<?
	}
	?><!--RCRDLIST_END_<?=$arParams["ENTITY_XML_ID"]?>--><?
	if ($arParams["PREORDER"] == "Y"): ?><?=$arParams["NAV_STRING"]?><? endif;
}
?>
<script type="text/javascript">
BX.ready(function(){
	window["UC"]["<?=$arParams["ENTITY_XML_ID"]?>"] = new FCList({
			ENTITY_XML_ID : '<?=$arParams["ENTITY_XML_ID"]?>',
			container : BX('<?=$arParams["ENTITY_XML_ID"]?>_hidden_records'),
			nav : BX('<?=$arParams["ENTITY_XML_ID"]?>_page_nav'),
			mid : <?=(!!$arParams["LAST_RECORD"] ? $arParams["LAST_RECORD"]["ID"] : 0)?>,
			order : '<?=($arParams["PREORDER"] == "N" ? "DESC" : "ASC")?>',
			rights : {
				MODERATE : '<?=$arParams["RIGHTS"]["MODERATE"]?>',
				EDIT : '<?=$arParams["RIGHTS"]["EDIT"]?>',
				DELETE : '<?=$arParams["RIGHTS"]["DELETE"]?>',
				CREATETASK : '<?=$arParams["RIGHTS"]["CREATETASK"]?>'
			},
			sign : '<?=$arParams["SIGN"]?>'
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
});
</script>
<div id="record-<?=$arParams["ENTITY_XML_ID"]?>-new"></div><?
if (!empty($arParams["ERROR_MESSAGE"]))
{
	?><div class="feed-add-error"><span class="feed-add-info-text"><span class="feed-add-info-icon"></span>
		<b><?=GetMessage("B_B_PC_COM_ERROR")?></b><br /><?=$arParams["ERROR_MESSAGE"]?></span></div><?
}
include_once(__DIR__."/messages.php");
if ($arParams["SHOW_POST_FORM"] == "Y")
{
	$AUTHOR_AVATAR = __mpl_get_avatar();

	?><div class="feed-com-add-box-outer">

		<div class="feed-com-avatar feed-com-avatar-<?=($AUTHOR_AVATAR == '/bitrix/images/1.gif' ? "N" : "Y")?>"><?
			?><img width="37" height="37" src="<?=$AUTHOR_AVATAR?>"><?
		?></div>

		<div class="feed-com-add-box">
			<div id="record-<?=$arParams["ENTITY_XML_ID"]?>-0-placeholder" class="blog-comment-edit feed-com-add-block blog-post-edit" style="display:none;"><?
			?></div><?
			?><div class="feed-com-footer" id="record-<?=$arParams["ENTITY_XML_ID"]?>-switcher" onclick="window['UC']['<?=$arParams["ENTITY_XML_ID"]?>'].reply();" <?
			?><?if ($arParams['SHOW_MINIMIZED'] != "Y"): ?> style="display:none;" <? endif; ?>><?
				?><div class="feed-com-add"><?
					?><a class="feed-com-add-link" href="javascript:void(0);" style="outline: none;" hidefocus="true"><?=GetMessage("B_B_MS_ADD_COMMENT")?></a><?
				?></div><?
			?></div>
		</div>
	</div>
	<?
}
?>