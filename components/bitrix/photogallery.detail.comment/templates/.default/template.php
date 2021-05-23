<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div id="photo_comments">
<?
if ($arParams["COMMENTS_TYPE"] == "blog")
{
	$APPLICATION->IncludeComponent(
		"bitrix:blog.post.comment",
		$arParams["POPUP_MODE"] == "Y" ? "photogallery" : "",
		Array(
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"],
			"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
			"BLOG_URL" => $arParams["BLOG_URL"],
			"ID" => $arResult["COMMENT_ID"],
			"PATH_TO_USER" => $arParams["PATH_TO_USER"],
			"PATH_TO_BLOG" => $arParams["PATH_TO_BLOG"],
			"PATH_TO_POST" => $arResult["ELEMENT"]["~DETAIL_PAGE_URL"],
			"SIMPLE_COMMENT" => "Y",
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"AJAX_POST" => "Y",
			"NOT_USE_COMMENT_TITLE" => "Y",
			"EDITOR_DEFAULT_HEIGHT" => "100",
			"EDITOR_WIDTH" => "400",
			"DATE_TIME_FORMAT" => $DB->DateFormatToPhp(FORMAT_DATETIME),
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"RATING_TYPE" => $arParams["RATING_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"FETCH_USER_ALIAS" => $arParams["FETCH_USER_ALIAS"]
		),
		($this->__component->__parent ? $this->__component->__parent : $component),
		array("HIDE_ICONS" => "Y")
	);
}
else
{
	if ($_REQUEST["photo_list_action"] == 'load_comments' && $_REQUEST["AJAX_CALL"] == "Y")
		$APPLICATION->RestartBuffer();

	$APPLICATION->IncludeComponent(
		"bitrix:forum.topic.reviews",
		$arParams["POPUP_MODE"] == "Y" ? "photogallery" : "",
		Array(
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"MESSAGES_PER_PAGE" => $arParams["COMMENTS_COUNT"],
			"USE_CAPTCHA" => $arParams["USE_CAPTCHA"],
			"PREORDER" => $arParams["PREORDER"],
			"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
			"FORUM_ID" => $arParams["FORUM_ID"],
			"URL_TEMPLATES_READ" => $arParams["~URL_TEMPLATES_READ"],
			"URL_TEMPLATES_DETAIL" => $arParams["~DETAIL_URL"],
			"URL_TEMPLATES_PROFILE_VIEW" => $arParams["~URL_TEMPLATES_PROFILE_VIEW"],
			"SHOW_LINK_TO_FORUM" => $arParams["SHOW_LINK_TO_FORUM"],
			"ELEMENT_ID" => $arParams["ELEMENT_ID"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"POST_FIRST_MESSAGE" => $arParams["POST_FIRST_MESSAGE"],
			"DATE_TIME_FORMAT" => $DB->DateFormatToPhp(FORMAT_DATETIME),
			"NO_REDIRECT_AFTER_SUBMIT" => "Y",
			"~ACTION_URL" => $arParams["ACTION_URL"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"PATH_TO_USER" => $arParams["PATH_TO_USER"],
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"RATING_TYPE" => $arParams["RATING_TYPE"],
			"AUTOSAVE" => false,
			"FETCH_USER_ALIAS" => $arParams["FETCH_USER_ALIAS"]
		),
		($this->__component->__parent ? $this->__component->__parent : $component),
		array("HIDE_ICONS" => "Y")
	);
}
?>
</div>