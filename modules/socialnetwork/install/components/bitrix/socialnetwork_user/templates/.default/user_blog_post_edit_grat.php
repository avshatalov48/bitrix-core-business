<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "user_blog_post_edit_grat";

?><?
$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		'POPUP_COMPONENT_NAME' => "bitrix:socialnetwork.blog.post.edit",
		"POPUP_COMPONENT_TEMPLATE_NAME" => "",
		"POPUP_COMPONENT_PARAMS" => array(
			"ID" => $arResult["VARIABLES"]["post_id"],
			"PATH_TO_BLOG" => $arResult["PATH_TO_USER_BLOG"],
			"PATH_TO_POST" => $arResult["PATH_TO_USER_BLOG_POST"],
			"PATH_TO_POST_EDIT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT"],
			"PATH_TO_POST_EDIT_PROFILE" => $arResult["PATH_TO_USER_BLOG_POST_EDIT_PROFILE"],
			"PATH_TO_POST_EDIT_GRAT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT_GRAT"],
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			"PATH_TO_DRAFT" => $arResult["PATH_TO_USER_BLOG_DRAFT"],
			"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
			"POST_PROPERTY" => $arParams["POST_PROPERTY"],
			"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
			"USER_ID" => $arResult["VARIABLES"]["user_id"],
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
			"POST_VAR" => $arResult["ALIASES"]["post_id"],
			"PAGE_ID" => $pageId,
			"SET_NAV_CHAIN" => "N",
			"USE_SOCNET" => "Y",
			"ALLOW_POST_MOVE" => $arParams["ALLOW_POST_MOVE"],
			"PATH_TO_BLOG_POST" => $arParams["PATH_TO_BLOG_POST"],
			"PATH_TO_BLOG_POST_EDIT" => $arParams["PATH_TO_BLOG_POST_EDIT"],
			"PATH_TO_BLOG_DRAFT" => $arParams["PATH_TO_BLOG_DRAFT"],
			"PATH_TO_BLOG_BLOG" => $arParams["PATH_TO_BLOG_BLOG"],
			"PATH_TO_USER_POST" => $arResult["PATH_TO_USER_BLOG_POST"],
			"PATH_TO_USER_POST_EDIT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT"],
			"PATH_TO_USER_DRAFT" => $arResult["PATH_TO_USER_BLOG_DRAFT"],
			"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
			"PATH_TO_GROUP_POST" => $arParams["PATH_TO_GROUP_POST"],
			"PATH_TO_GROUP_POST_EDIT" => $arParams["PATH_TO_GROUP_POST_EDIT"],
			"PATH_TO_GROUP_DRAFT" => $arParams["PATH_TO_GROUP_DRAFT"],
			"PATH_TO_GROUP_BLOG" => $arParams["PATH_TO_GROUP_BLOG"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"IMAGE_MAX_WIDTH" => $arParams["BLOG_IMAGE_MAX_WIDTH"],
			"IMAGE_MAX_HEIGHT" => $arParams["BLOG_IMAGE_MAX_HEIGHT"],
			"ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"],
			"USE_GOOGLE_CODE" => $arParams["BLOG_USE_GOOGLE_CODE"],
			"USE_CUT" => $arParams["BLOG_USE_CUT"],
			"SELECTOR_VERSION" => 2,
		),
		"POPUP_COMPONENT_PARENT" => $this->getComponent(),
		"CLOSE_AFTER_SAVE" => true,
		"RELOAD_PAGE_AFTER_SAVE" => false
	)
);
?>
