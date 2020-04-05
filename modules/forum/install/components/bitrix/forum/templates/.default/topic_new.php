<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arInfo = $APPLICATION->IncludeComponent("bitrix:forum.topic.new", "",
		array(
			"FID" => $arResult["FID"],
			"MID" => $arResult["MID"],
			"MESSAGE_TYPE" => $arResult["MESSAGE_TYPE"],
			
			"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"],
			"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"],
			"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
			"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
			"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
			"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"],

			"USER_FIELDS" => $arParams["USER_FIELDS"],
			"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
			"ATTACH_MODE" => $arParams["ATTACH_MODE"],
			"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
			"SHOW_VOTE" => $arParams["SHOW_VOTE"],
			"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"], 
			"VOTE_GROUP_ID" => $arParams["VOTE_GROUP_ID"], 
			"VOTE_UNIQUE" => $arParams["VOTE_UNIQUE"],
			"VOTE_UNIQUE_IP_DELAY" => $arParams["VOTE_UNIQUE_IP_DELAY"],

			"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
			"AJAX_TYPE" => $arParams["AJAX_TYPE"],
			"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"CACHE_TIME" => $arResult["CACHE_TIME"],
			"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		),
		$component 
	);
if ($arInfo === false):
	return false;
endif;
$APPLICATION->IncludeComponent("bitrix:forum.post_form", "", 
	Array(
		"FID"	=>	$arResult["FID"],
		"TID"	=>	$arResult["TID"],
		"TITLE_SEO"	=>	$arResult["TITLE_SEO"],
		"MID"	=>	$arResult["MID"],
		"PAGE_NAME"	=>	"topic_new",
		"MESSAGE_TYPE"	=>	$arInfo["MESSAGE_TYPE"],
		"FORUM" => $arInfo["FORUM"],
		"bVarsFromForm" => $arInfo["bVarsFromForm"],
		
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_HELP" =>  $arResult["URL_TEMPLATES_HELP"],
		"URL_TEMPLATES_RULES" =>  $arResult["URL_TEMPLATES_RULES"],

		"USER_FIELDS" =>  $arParams["USER_FIELDS"],
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
		"ATTACH_MODE" => $arParams["ATTACH_MODE"],
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
		"EDITOR_CODE_DEFAULT" => $arParams["EDITOR_CODE_DEFAULT"],
		"SEO_USE_AN_EXTERNAL_SERVICE" => $arParams["SEO_USE_AN_EXTERNAL_SERVICE"],
		"SHOW_VOTE" => $arParams["SHOW_VOTE"], 
		"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"], 
		"VOTE_GROUP_ID" => $arParams["VOTE_GROUP_ID"], 

		"AJAX_TYPE" => $arParams["AJAX_TYPE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
		"ERROR_MESSAGE" => $arInfo["ERROR_MESSAGE"]
	),
	$component
);
?>
