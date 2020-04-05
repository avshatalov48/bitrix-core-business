<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
//FCForm
include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script.php");
?>

<?
$arResult["OUTPUT_LIST"] = $APPLICATION->IncludeComponent(
	"bitrix:main.post.list",
	"",
	array(
		"TEMPLATE_ID" => 'BLOG_COMMENT_BG_',
		"RATING_TYPE_ID" => ($arParams["SHOW_RATING"] == "Y" ? "BLOG_COMMENT" : ""),
		"ENTITY_XML_ID" => $arParams["ENTITY_XML_ID"],
		"RECORDS" => $arResult["CommentsResult"],
		"NAV_STRING" => $arResult["NAV_STRING"],
		"NAV_RESULT" => $arResult["NAV_RESULT"],
		"PREORDER" => "N",
		"RIGHTS" => array(
			"MODERATE" => ($arResult["canModerate"] ? "Y" : "N"),
			"EDIT" => ($arResult["canModerate"] ? "Y" : "N"),
			"DELETE" => ($arResult["canModerate"] ? "Y" : "N"),
		),
		"VISIBLE_RECORDS_COUNT" => min($arParams["PAGE_SIZE_MIN"], $arParams["PAGE_SIZE"]),
		"ERROR_MESSAGE" => ($arResult["ERROR_MESSAGE"] ?: $arResult["COMMENT_ERROR"]),
		"OK_MESSAGE" => $arResult["MESSAGE"],
		"RESULT" => ($arResult["ajax_comment"] ?: $_GET["commentId"]),
		"PUSH&PULL" => $arResult["PUSH&PULL"],
		"VIEW_URL" => $arResult["urlToShow"],
		"EDIT_URL" => "__blogEditComment('#ID#', '".$arParams["ID"]."')",
//		%23 - it encoded #. Dirt hack ((
		"MODERATE_URL" => str_replace(
			array("%23source_post_id%23", "%23post_id%23", "%23comment_id%23", "&".bitrix_sessid_get(), "hide_comment_id="),
			array($arParams["ID"], $arParams["ID"], "#ID#", "", "#action#_comment_id="),
			$arResult["urlToHide"]
		),
		"DELETE_URL" => str_replace(
			array("%23source_post_id%23", "%23post_id%23", "%23comment_id%23", "&".bitrix_sessid_get()),
			array($arParams["ID"], $arParams["ID"], "#ID#", ""),
			$arResult["urlToDelete"]
		),
		"AUTHOR_URL" => ($arParams["bPublicPage"] ? "" : $arParams["PATH_TO_USER"]),
		
		"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams['SHOW_LOGIN'],
		
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"LAZYLOAD" => $arResult["LAZYLOAD"],
		
		"SHOW_MINIMIZED" => "Y",
		"SHOW_POST_FORM" => ($arResult["CanUserComment"] ? "Y" : "N"),
		
		"IMAGE_SIZE" => max($arParams["IMAGE_MAX_WIDTH"], $arParams["IMAGE_MAX_HEIGHT"]),
		"mfi" => $arParams["mfi"],
	),
	$this->__component
);
?>
<div class="feed-comments-block feed-comments-block-blog" id="blg-comment-<?=$arParams["ID"]?>">
	<a name="comments"></a>
	<?=$arResult["OUTPUT_LIST"]["HTML"]?>
</div>

<?
//init comment form, is user can comment
if ($arResult["CanUserComment"])
{
?>
<script>
BX.viewElementBind(
	'blg-comment-<?=$arParams["ID"]?>',
	{},
	function(node){
		return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
	}
);
top.postFollow<?=$arParams["ID"]?> = postFollow<?=$arParams["ID"]?> = '<?=$arParams["FOLLOW"]?>';
</script>
	<?
	if (
		(empty($_REQUEST["bxajaxid"]) && empty($_REQUEST["logajax"]))
		|| ($_REQUEST["RELOAD"] == "Y" && !(empty($_REQUEST["bxajaxid"]) && empty($_REQUEST["logajax"])))
		|| (isset($_REQUEST["noblog"]) && $_REQUEST["noblog"] == "Y")
	)
	{
		include_once(__DIR__ . "/form.php");
	}
//	__sbpc_bind_post_to_form($arParams["ENTITY_XML_ID"], null, $arParams);
	//bind entity to new editor js object
	echo $component->bindPostToEditorForm($arParams["ENTITY_XML_ID"], null, $arParams);
}
?>