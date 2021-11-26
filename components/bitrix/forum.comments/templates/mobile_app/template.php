<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var CBitrixComponentTemplate $this
 */

CUtil::InitJSCore(array('content_view'));
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$link = $APPLICATION->GetCurPageParam("MID=#ID#", array(
	"MID",
	"sessid",
	"AJAX_POST",
	"ENTITY_XML_ID",
	"ENTITY_TYPE",
	"ENTITY_ID",
	"REVIEW_ACTION",
	"ACTION",
	"MODE",
	"FILTER",
	"result",
	"clear_cache"));
$arResult["OUTPUT_LIST"] = $APPLICATION->IncludeComponent(
	"bitrix:main.post.list",
	"",
	array(
		"TEMPLATE_ID" => $arParams["tplID"],
		"RATING_TYPE_ID" => ($arParams["SHOW_RATING"] == "Y" ? "FORUM_POST" : ""),
		"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"],
		"POST_CONTENT_TYPE_ID" => (!empty($arParams["POST_CONTENT_TYPE_ID"]) ? $arParams["POST_CONTENT_TYPE_ID"] : false),
		"COMMENT_CONTENT_TYPE_ID" => "FORUM_POST",
		"RECORDS" => $arResult["MESSAGES"],
		"NAV_STRING" => $arResult["NAV_STRING"],
		"NAV_RESULT" => $arResult["NAV_RESULT"],
		"PREORDER" => $arParams["PREORDER"],
		"RIGHTS" => array(
			"MODERATE" =>  $arResult["PANELS"]["MODERATE"],
			"EDIT" => (
				$arResult["PANELS"]["EDIT"] == "N"
					? (
						$arParams["ALLOW_EDIT_OWN_MESSAGE"] === "ALL"
							? "OWN"
							: ($arParams["ALLOW_EDIT_OWN_MESSAGE"] === "LAST" ? "OWNLAST" : "N")
					)
					: "Y"
			),
			"DELETE" => (
				$arResult["PANELS"]["EDIT"] == "N"
					? (
						$arParams["ALLOW_EDIT_OWN_MESSAGE"] === "ALL"
							? "OWN"
							: ($arParams["ALLOW_EDIT_OWN_MESSAGE"] === "LAST" ? "OWNLAST" : "N")
					)
				: "Y"
			),
			"CREATETASK" => ($arResult["bTasksAvailable"] ? "Y" : "N")
		),
		"VISIBLE_RECORDS_COUNT" => $arResult["VISIBLE_RECORDS_COUNT"],

		"ERROR_MESSAGE" => $arResult["ERROR_MESSAGE"],
		"OK_MESSAGE" => $arResult["OK_MESSAGE"],
		"RESULT" => ($arResult["RESULT"] ?: $request->getQuery("MID")),
		"PUSH&PULL" => $arResult["PUSH&PULL"],
		"MODE" => $arResult["MODE"],
		"VIEW_URL" => ($arParams["SHOW_LINK_TO_MESSAGE"] == "Y" ? $link : ""),
		"EDIT_URL" => ForumAddPageParams($link, array("ACTION" => "GET"), false, false),
		"MODERATE_URL" => ForumAddPageParams($link, array("ACTION" => "#ACTION#"), false, false),
		"DELETE_URL" => ForumAddPageParams($link, array("ACTION" => "DEL"), false, false),
		"AUTHOR_URL" => $arParams["URL_TEMPLATES_PROFILE_VIEW"],

		"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams['SHOW_LOGIN'],

		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"LAZYLOAD" => $arParams["LAZYLOAD"],

		"NOTIFY_TAG" => ($arParams["bFromList"] ? "BLOG|COMMENT" : ""),
		"NOTIFY_TEXT" => ($arParams["bFromList"] ? TruncateText(str_replace(Array("\r\n", "\n"), " ", $arParams["POST_DATA"]["~TITLE"]), 100) : ""),
		"SHOW_MINIMIZED" => $arParams["SHOW_MINIMIZED"],
		"SHOW_POST_FORM" => $arResult["SHOW_POST_FORM"],

		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
		"mfi" => $arParams["mfi"],

		"FORM" => array(
			"ID" => $arParams["FORM_ID"],
			"URL" => $APPLICATION->GetCurPageParam("", array(
					"sessid", "comment_post_id", "act", "post", "comment",
					"decode", "ENTITY_TYPE_ID", "ENTITY_ID",
					"empty_get_comments")),
			"FIELDS" => array(
			)
		)
	),
	$this->__component
);
if ($arResult["SHOW_POST_FORM"] == "Y")
{
	ob_start();
	include(__DIR__."/form.php");
	$arResult["OUTPUT_LIST"]["HTML"] = ob_get_clean().$arResult["OUTPUT_LIST"]["HTML"];
}

if ($_REQUEST["empty_get_comments"] == "Y")
{
	$APPLICATION->RestartBuffer();
	while(ob_get_clean());
	echo CUtil::PhpToJSObject(array(
		"TEXT" => $arResult["OUTPUT_LIST"]["HTML"],
		"POST_NUM_COMMENTS" => intval($arResult["Post"]["NUM_COMMENTS"])
	));
	die();
}
?>
<div class="post-comments-wrap" id="post-comments-wrap">
	<?=$arResult["OUTPUT_LIST"]["HTML"]?>
	<span id="post-comment-last-after"></span>
</div>
<script>
	BX.ready(function() {

		BX.onCustomEvent(window, 'BX.UserContentView.onInitCall', [{
			mobile: true,
			ajaxUrl: '<?=SITE_DIR?>mobile/ajax.php',
			commentsContainerId: 'post-comments-wrap',
			commentsClassName: 'post-comment-wrap',
			context: 'forum.comments/mobile'
		}]);
	});

	BX.addCustomEvent(window, "OnUCFormSubmit", function(xml, id, obj, post) { if (post['comment_review']=="Y" && xml=='<?=$arParams["ENTITY_XML_ID"]?>' && id > 0) post['MID'] = id; });
</script>
