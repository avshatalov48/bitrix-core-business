<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 * @var string $templateFolder
 * @var CUser $USER
 * @var MainPostList $this->__component
 */
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/components/bitrix/main.post.list/templates/.default/script.js");
\Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder."/script.js");
\Bitrix\Main\Page\Asset::getInstance()->addString('<link href="'.CUtil::GetAdditionalFileURL('/bitrix/js/ui/icons/base/ui.icons.base.css').'" type="text/css" rel="stylesheet" />');
\Bitrix\Main\Page\Asset::getInstance()->addString('<link href="'.CUtil::GetAdditionalFileURL('/bitrix/js/ui/icons/b24/ui.icons.b24.css').'" type="text/css" rel="stylesheet" />');

CUtil::InitJSCore(array("uploader", "date", "fx", "ls")); // does not work
ob_start();
?>
<!--RCRD_#FULL_ID#-->
<a id="com#ID#" name="com#ID#" bx-mpl-full-id="#FULL_ID#"></a>
<div id="record-#FULL_ID#" class="post-comment-block post-comment-block-#NEW# post-comment-block-#APPROVED# #RATING_NONEMPTY_CLASS#" <?=($arResult["ajax_comment"] == $comment["ID"] ? ' data-send="Y"' : '')?> <?
	?>bx-mpl-id="#FULL_ID#" <?
	?>bx-mpl-reply-show="#SHOW_POST_FORM#" <?
	?>bx-mpl-view-url="#VIEW_URL###ID#" bx-mpl-view-show="#VIEW_SHOW#" <?
	?>bx-mpl-edit-url="#EDIT_URL#" bx-mpl-edit-show="#EDIT_SHOW#" <?
	?>bx-mpl-moderate-url="#MODERATE_URL#" bx-mpl-moderate-show="#MODERATE_SHOW#" bx-mpl-moderate-approved="#APPROVED#" <?
	?>bx-mpl-delete-url="#DELETE_URL###ID#" bx-mpl-delete-show="#DELETE_SHOW#" <?
	?>bx-mpl-createtask-show="#CREATETASK_SHOW#" <?
	?>bx-mpl-post-entity-type="#POST_ENTITY_TYPE#" <?
	?>bx-mpl-comment-entity-type="#COMMENT_ENTITY_TYPE#" <?
	?>bx-mpl-vote-id="#VOTE_ID#" <?
	?>onclick="return mobileShowActions('#ENTITY_XML_ID#', '#ID#', arguments[0])" <?
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
		<div class="post-comment-balloon">
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
				<div class="post-comment-wrap" bx-content-view-xml-id="#CONTENT_ID#" id="post-comment-wrap-#CONTENT_ID#" bx-content-view-save="N">
					<div class="post-comment-text" id="record-#FULL_ID#-text">#TEXT#</div>
				</div>
				<div class="post-comment-more" onclick="mobileExpand(this, event)"><div class="post-comment-more-but"></div></div>
			</div>
		</div>
		#AFTER#
		<div class="post-comment-control-box">
			#BEFORE_ACTIONS#
			<?
			if (
				!isset($arParams["SHOW_POST_FORM"])
				|| $arParams["SHOW_POST_FORM"] != 'N'
			)
			{
				?><div class="post-comment-control-item" id="record-#FULL_ID#-reply-action" onclick="return mobileReply('#ENTITY_XML_ID#', event)" <?
					?>bx-mpl-author-id="#AUTHOR_ID#" <?
					?>bx-mpl-author-name="#AUTHOR_NAME#"><?
					?><?=Loc::getMessage('BLOG_C_REPLY')?><?
				?></div><?
			}
			?>
		</div>
	</div>
	#AFTER_RECORD#
</div><? // post-comment-block
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
	</div>
</div><?

$avatar = \CFile::ResizeImageGet(
	$_SESSION["SESS_AUTH"]["PERSONAL_PHOTO"],
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
ob_start();

?><div class="post-comment-block">
	<div class="ui-icon ui-icon-common-user post-comment-block-avatar"><i style="#AUTHOR_AVATAR_BG#"></i></div>
	<div class="post-comment-detail">
		<div class="post-comment-balloon">
			<!--/noindex-->
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
			<div class="post-comment-files">
				<div class="comment-loading">
					<div class="newpost-progress-label"></div>
					<div id="record-#FULL_ID#-ind" class="newpost-progress-indicator"></div>
				</div>
			</div>
		</div>
		<div class="post-comment-control-box">
			<div class="post-comment-control-item"><?=Loc::getMessage('BLOG_C_REPLY')?></div>
		</div>
		<script>
			BX.ready(function()
			{
				BX.addClass(BX("record-#FULL_ID#-ind"), "animate");
			});
		</script>
	</div>
</div><?

$thumbFile = preg_replace(array(
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
		($avatar ? $avatar["src"] : ""),
		($avatar ? "background-image:url('".$avatar["src"]."')" : ''),
		htmlspecialcharsbx($name),
	), ob_get_clean());
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
				?><div id="<?=$arParams["ENTITY_XML_ID"]?>_hidden_records" class="feed-hidden-post" style="display:none; overflow:hidden;"></div> <?
			}

			?><a href="<?=$arParams["NAV_STRING"]?>" id="<?=$arParams["ENTITY_XML_ID"]?>_page_nav" class="post-comments-link"><?=Loc::getMessage("BLOG_C_VIEW")?> <span class="post-comments-link-count"><?=$arResult["NAV_STRING_COUNT_MORE"]?></span><?
				?><span class="post-comments-button-waiter"><svg class="post-comments-button-waiter-circular" viewBox="25 25 50 50">
					<circle class="post-comments-button-waiter-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
					<circle class="post-comments-button-waiter-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
				</svg></span><?
			?></a><?

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
	reset($arParams["RECORDS"]);

	if ($arParams["PREORDER"] != "Y"): ?><?=$arParams["NAV_STRING"]?><? endif;
	$iCount = 0;
	?><!--RCRDLIST_<?=$arParams["ENTITY_XML_ID"]?>--><?
	foreach ($arParams["RECORDS"] as $key => $res)
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
?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-new"></div><?
include_once(__DIR__."/messages.php");
if ($arParams["SHOW_POST_FORM"] == "Y")
{
	?><div id="record-<?=$arParams["ENTITY_XML_ID"]?>-0-placeholder" style="display:none;"></div><?
}
?>
<script>
	BX.ready(function(){
		var f = function() {
			BX.MPL.createInstance({
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

				SHOW_POST_FORM : '<?=CUtil::JSEscape($arParams["SHOW_POST_FORM"])?>',
				BIND_VIEWER : '<?=$arParams["BIND_VIEWER"]?>'
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