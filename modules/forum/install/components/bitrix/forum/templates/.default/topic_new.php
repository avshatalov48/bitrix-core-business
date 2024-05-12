<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arInfo = $APPLICATION->IncludeComponent("bitrix:forum.topic.new", "",
		array(
			"FID" => $arResult["FID"] ?? null,
			"MID" => $arResult["MID"] ?? null,
			"MESSAGE_TYPE" => $arResult["MESSAGE_TYPE"] ?? null,

			"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"] ?? null,
			"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"] ?? null,
			"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
			"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
			"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
			"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

			"USER_FIELDS" => $arParams["USER_FIELDS"] ?? null,
			"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"] ?? null,
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
			"IMAGE_SIZE" => $arParams["IMAGE_SIZE"] ?? null,
			"ATTACH_MODE" => $arParams["ATTACH_MODE"] ?? null,
			"ATTACH_SIZE" => $arParams["ATTACH_SIZE"] ?? null,
			"SHOW_VOTE" => $arParams["SHOW_VOTE"] ?? null,
			"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"] ?? null,
			"VOTE_GROUP_ID" => $arParams["VOTE_GROUP_ID"] ?? null,
			"VOTE_UNIQUE" => $arParams["VOTE_UNIQUE"] ?? null,
			"VOTE_UNIQUE_IP_DELAY" => $arParams["VOTE_UNIQUE_IP_DELAY"] ?? null,

			"SET_NAVIGATION" => $arResult["SET_NAVIGATION"] ?? null,
			"AJAX_TYPE" => $arParams["AJAX_TYPE"] ?? null,
			"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
			"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
			"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
			"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
		),
		$component
	);
if ($arInfo === false):
	return false;
endif;
$APPLICATION->IncludeComponent("bitrix:forum.post_form", "",
	Array(
		"FID"	=>	$arResult["FID"] ?? null,
		"TID"	=>	$arResult["TID"] ?? null,
		"TITLE_SEO"	=>	$arResult["TITLE_SEO"] ?? null,
		"MID"	=>	$arResult["MID"] ?? null,
		"PAGE_NAME"	=>	"topic_new",
		"MESSAGE_TYPE"	=>	$arInfo["MESSAGE_TYPE"] ?? null,
		"FORUM" => $arInfo["FORUM"] ?? null,
		"bVarsFromForm" => $arInfo["bVarsFromForm"] ?? null,

		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_HELP" =>  $arResult["URL_TEMPLATES_HELP"] ?? null,
		"URL_TEMPLATES_RULES" =>  $arResult["URL_TEMPLATES_RULES"] ?? null,

		"USER_FIELDS" =>  $arParams["USER_FIELDS"] ?? null,
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"] ?? null,
		"ATTACH_MODE" => $arParams["ATTACH_MODE"] ?? null,
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"] ?? null,
		"EDITOR_CODE_DEFAULT" => $arParams["EDITOR_CODE_DEFAULT"] ?? null,
		"SEO_USE_AN_EXTERNAL_SERVICE" => $arParams["SEO_USE_AN_EXTERNAL_SERVICE"] ?? null,
		"SHOW_VOTE" => $arParams["SHOW_VOTE"] ?? null,
		"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"] ?? null,
		"VOTE_GROUP_ID" => $arParams["VOTE_GROUP_ID"] ?? null,

		"AJAX_TYPE" => $arParams["AJAX_TYPE"] ?? null,
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,

		"SHOW_TAGS" => $arParams["SHOW_TAGS"] ?? null,
		"ERROR_MESSAGE" => $arInfo["ERROR_MESSAGE"] ?? null
	),
	$component
);
?>
