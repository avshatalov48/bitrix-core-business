<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 */
$rights = "N";
if (
	\CSocNetUser::IsCurrentUserModuleAdmin()
	|| $APPLICATION->GetGroupRight("blog") >= "W"
)
{
	$rights = "ALL";
}
else if ($USER->IsAuthorized())
{
	$rights = "OWN";
}

$eventHandlerID = AddEventHandler('main', 'system.field.view.file', Array('CBlogTools', 'blogUFfileShow'));
$arResult["OUTPUT_LIST"] = $APPLICATION->IncludeComponent(
	"bitrix:main.post.list",
	"",
	array(
		"TEMPLATE_ID" => 'BLOG_COMMENT_BG_',
		"RATING_TYPE_ID" => ($arParams["SHOW_RATING"] == "Y" ? "BLOG_COMMENT" : ""),
		"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"],
		"RECORDS" => $arResult["RECORDS"],
		"NAV_STRING" => $arResult["NAV_STRING"],
		"NAV_RESULT" => $arResult["NAV_RESULT"],
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

		"ERROR_MESSAGE" => ($arResult["ERROR_MESSAGE"] ?: $arResult["COMMENT_ERROR"]),
		"OK_MESSAGE" => $arResult["MESSAGE"],
		"RESULT" => ($arResult["ajax_comment"] ?: $_GET["commentId"]),
		"PUSH&PULL" => $arResult["PUSH&PULL"],
		"VIEW_URL" => str_replace("#comment_id#", "#ID#", $arResult["urlMobileToComment"]),
		"EDIT_URL" => str_replace("#comment_id#", "#ID#", $arResult["urlMobileToComment"]),
		"MODERATE_URL" => str_replace(
			array("hide_comment_id=", "#comment_id#"),
			array("#action#_comment_id=", "#ID#"),
			$arResult["urlMobileToHide"]
		),
		"DELETE_URL" => str_replace("#comment_id#", "#ID#", $arResult["urlMobileToDelete"]),
		"AUTHOR_URL" => "/mobile/users/?user_id=#user_id#",

		"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams['SHOW_LOGIN'],

		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"LAZYLOAD" => $arParams["LAZYLOAD"],

		"NOTIFY_TAG" => ($arParams["bFromList"] ? "BLOG|COMMENT" : ""),
		"NOTIFY_TEXT" => ($arParams["bFromList"] ? TruncateText(str_replace(Array("\r\n", "\n"), " ", $arParams["POST_DATA"]["~TITLE"]), 100) : ""),
		"SHOW_MINIMIZED" => "Y",
		"SHOW_POST_FORM" => ($arResult["CanUserComment"] ? "Y" : "N"),

		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
		"mfi" => $arParams["mfi"],

		"FORM" => array(
			"ID" => $this->__component->__name,
			"URL" => $APPLICATION->GetCurPageParam("", array(
					"sessid", "comment_post_id", "act", "post", "comment",
					"decode", "ACTION", "ENTITY_TYPE_ID", "ENTITY_ID",
					"empty_get_form", "empty_get_comments"))
		),
		"AUTHOR_URL_PARAMS" => array(
			"entityType" => 'LOG_ENTRY',
			"entityId" => $arParams["LOG_ID"]
		),
	),
	$this->__component
);
if ($eventHandlerID > 0 )
	RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);

if ($arResult["CanUserComment"] == "Y")
{
	ob_start();
	include_once(__DIR__ . "/script.php");
	$arResult["OUTPUT_LIST"]["HTML"] .= ob_get_clean();
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
<script>
	app.setPageID('BLOG_POST_<?=$arParams["ID"]?>');
	BX.addCustomEvent(window, "OnUCFormSubmit", function(xml, id, obj, post) {
		if (xml=='<?=$arParams["ENTITY_XML_ID"]?>') {
			post['comment_post_id'] = '<?=$arParams["ID"]?>';
			post['logId'] = '<?=$arParams["LOG_ID"]?>';
	} });
</script>
<div class="post-comments-wrap" id="post-comments-wrap">
	<?=$arResult["OUTPUT_LIST"]["HTML"]?>
	<span id="post-comment-last-after"></span>
</div>