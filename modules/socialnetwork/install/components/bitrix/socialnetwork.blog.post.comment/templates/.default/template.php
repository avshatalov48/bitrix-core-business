<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Blog\Item;
use Bitrix\Main\Loader;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 */
CJSCore::Init(array("tooltip", "popup", "fx", "viewer", "content_view", "videorecorder"));

if (!empty($arResult["FATAL_MESSAGE"]))
{
	?><div class="feed-add-error">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["FATAL_MESSAGE"]?></span>
	</div><?php
}
else if(($arResult["imageUploadFrame"] ?? '') === "Y")
{
	?>
	<script>
		<?php
		if (!empty($arResult["Image"]))
		{
			?>
			if (!top.arImagesId) { top.arImagesId = []; }
			if (!top.arImagesSrc) { top.arImagesSrc = []; }
			top.arImagesId.push('<?=$arResult["Image"]["ID"]?>');
			top.arImagesSrc.push('<?=CUtil::JSEscape($arResult["Image"]["SRC"])?>');
			top.bxBlogImageId = '<?=$arResult["Image"]["ID"]?>';
			top.bxBlogImageIdWidth = '<?=CUtil::JSEscape($arResult["Image"]["WIDTH"])?>';
			top.bxBlogImageIdSrc = '<?=CUtil::JSEscape($arResult["Image"]["SRC"])?>';
			<?php
		}
		elseif ((string)$arResult["ERROR_MESSAGE"] !== '')
		{
			?>
			top.bxBlogImageError = '<?=CUtil::JSEscape($arResult["ERROR_MESSAGE"])?>';
			<?php
		}
		?>
	</script>
	<?php
	die();
}

$rights = "N";
if ($arResult["Perm"] >= Item\Permissions::FULL)
{
	$rights = "ALL";
}
elseif ($USER->IsAuthorized())
{
	$rights = "OWN";
}

$eventHandlerID = AddEventHandler('main', 'system.field.view.file', Array('CBlogTools', 'blogUFfileShow'));
$commentUrl = (
	$arParams["bPublicPage"]
		? ''
		: str_replace(
			[ '##comment_id#', '#comment_id#' ],
			[ '', '#ID#' ],
			$arResult["commentUrl"] ?? ''
		)
);

$commentUrl = (new \Bitrix\Main\Web\Uri($commentUrl))->deleteParams([
	'sessid',
	'AJAX_POST',
	'ENTITY_XML_ID',
	'ENTITY_TYPE',
	'ENTITY_ID',
	'REVIEW_ACTION',
	'ACTION',
	'MODE',
	'FILTER',
	'result',
]);

$arResult["OUTPUT_LIST"] = $APPLICATION->IncludeComponent(
	"bitrix:main.post.list",
	"",
	array(
		"TEMPLATE_ID" => 'BLOG_COMMENT_BG_',
		"RATING_TYPE_ID" => ($arParams["SHOW_RATING"] === "Y" ? "BLOG_COMMENT" : ""),
		"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"],
		"RECORDS" => $arResult["RECORDS"],
		"NAV_STRING" => $arResult["NAV_STRING"] ?? '',
		"NAV_RESULT" => $arResult["NAV_RESULT"] ?? '',
		"PREORDER" => "N",
		"RIGHTS" => array(
			"MODERATE" => ($arResult["Perm"] >= BLOG_PERMS_MODERATE ? "Y" : "N"),
			"EDIT" => $rights,
			"DELETE" => $rights,
			"CREATETASK" => ($arResult["bTasksAvailable"] ? "Y" : "N")
		),
		"VISIBLE_RECORDS_COUNT" => (
			$arResult["newCount"] > $arParams["PAGE_SIZE"]
				? $arParams["PAGE_SIZE"]
				: (
					$arResult["newCount"] < $arParams["PAGE_SIZE_MIN"]
						? $arParams["PAGE_SIZE_MIN"]
						: $arResult["newCount"]
				)
		),

		"ERROR_MESSAGE" => (($arResult["ERROR_MESSAGE"] ?? null) ?: ($arResult["COMMENT_ERROR"] ?? '')),
		"OK_MESSAGE" => $arResult["MESSAGE"] ?? null,
		"RESULT" => ($arResult["ajax_comment"] ?: ($_GET["commentId"] ?? '')),
		'MODE' => $arResult['MODE'] ?? '',
		"PUSH&PULL" => $arResult["PUSH&PULL"],
		"VIEW_URL" => $commentUrl,
		"AUTHOR_URL" => ($arParams["bPublicPage"] ? "" : $arParams["PATH_TO_USER"]),

		"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams['SHOW_LOGIN'],

		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
		"LAZYLOAD" => $arParams["LAZYLOAD"],

		"NOTIFY_TAG" => ($arParams["bFromList"] ? "BLOG|COMMENT" : ""),
		"NOTIFY_TEXT" => ($arParams["bFromList"] ? TruncateText(str_replace(Array("\r\n", "\n"), " ", $arParams["POST_DATA"]["~TITLE"]), 100) : ""),
		"SHOW_MINIMIZED" => "Y",
		"SHOW_POST_FORM" => ($arResult["CanUserComment"] ? "Y" : "N"),
		'FORM_ID' => ($arResult["CanUserComment"] ? $arResult['FORM_ID'] : ''),

		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"] ?? 0,
		"mfi" => $arParams["mfi"] ?? '',
		"AUTHOR_URL_PARAMS" => array(
			"entityType" => 'LOG_ENTRY',
			"entityId" => $arParams["LOG_ID"]
		),
		"bPublicPage" => (isset($arParams["bPublicPage"]) && $arParams["bPublicPage"]),
	),
	$this->__component
);
if ($eventHandlerID > 0 )
{
	RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
}
//AddMessage2Log($arResult["OUTPUT_LIST"]);
$blockClassName = "feed-comments-block";
if (
	!empty($arResult["OUTPUT_LIST"]["DATA"])
	&& !empty($arResult["OUTPUT_LIST"]["DATA"]["NAV_STRING_COUNT_MORE"])
	&& (int)$arResult["OUTPUT_LIST"]["DATA"]["NAV_STRING_COUNT_MORE"] > 0
)
{
	$blockClassName .= " feed-comments-block-nav";
}

?><div
 class="<?=$blockClassName?>" id="blg-comment-<?=$arParams["ID"]?>"
 data-bx-comments-entity-xml-id="<?= \Bitrix\Main\Text\HtmlFilter::encode($arParams['ENTITY_XML_ID']) ?>"
 data-bx-follow="<?=($arParams['FOLLOW'] === 'Y' ? 'Y' : 'N')?>"><?php
	?><a name="comments"></a><?php
	?><?=$arResult["OUTPUT_LIST"]["HTML"]?><?php
?></div><?php
?><script>
BX.ready(function() {
	BX.bind(BX("blg-post-img-<?=$arResult["Post"]["ID"]?>"), "mouseup", function(e) {
		checkForQuote(e, this, '<?=$arParams["ENTITY_XML_ID"]?>', 'bp_<?=$arResult["Post"]["ID"]?>');
	});
	BX.addCustomEvent(window, 'OnUCAfterRecordAdd', function(ENTITY_XML_ID, response) {
		if (ENTITY_XML_ID == '<?=$arParams["ENTITY_XML_ID"]?>')
		{
			__blogOnUCAfterRecordAdd(ENTITY_XML_ID, response);
		}
	});
	BX.UserContentView.init();
	BX.SocialnetworkBlogPostComment.registerViewAreaList({
		containerId: 'blg-comment-<?=$arParams["ID"]?>',
		className: 'feed-com-text-inner',
		fullContentClassName: 'feed-com-text-inner-inner',
	});
});
</script>
<?php

if (
	$USER->IsAuthorized()
	&& Loader::includeModule("pull")
	&& CPullOptions::GetNginxStatus())
{
	?>
	<script>
		BX.addCustomEvent("onPullEvent-unicomments", function(command, params) {
			if (
				params["ENTITY_XML_ID"] == '<?=$arParams["ENTITY_XML_ID"]?>'
				&& BX('blg-comment-<?=$arParams["ID"]?>')
			)
			{
				BX.show(BX('blg-comment-<?=$arParams["ID"]?>'));
			}
		});
	</script>
	<?php
}
if ($arResult["CanUserComment"])
{
	?>
	<script>
		BX.viewElementBind(
			'blg-comment-<?=$arParams["ID"]?>',
			{},
			function(node)
			{
				return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
			}
		);
		top.postFollow<?=$arParams["ID"]?> = postFollow<?=$arParams["ID"]?> = '<?=$arParams["FOLLOW"]?>';
	</script>
	<?php
	if (
		(empty($_REQUEST["bxajaxid"]) && empty($_REQUEST["logajax"]))
		|| (($_REQUEST["RELOAD"] ?? '') === "Y" && !(empty($_REQUEST["bxajaxid"]) && empty($_REQUEST["logajax"])))
		|| (isset($_REQUEST["noblog"]) && $_REQUEST["noblog"] === "Y")
	)
	{
		include_once(__DIR__."/script.php");
	}
	__sbpc_bind_post_to_form($arParams["ENTITY_XML_ID"], null, $arParams);
}
