<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var CBitrixComponentTemplate $this
 */

$arResult["OUTPUT_LIST"] = $APPLICATION->IncludeComponent(
	"bitrix:main.post.list",
	"mail",
	array(
		"RECORDS" => $arResult["MESSAGES"],
		"PREORDER" => "N",
		"AVATAR_SIZE" => 39,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"RECIPIENT_ID" => $arParams["RECIPIENT_ID"],
		"SITE_ID" => $arParams["SITE_ID"],
		"SHOW_LOGIN" => "Y",
		"COMMENTS_COUNT" => $arResult["COMMENTS_COUNT"],
		"COMMENTS_ALL_COUNT" => $arResult["COMMENTS_ALL_COUNT"],
		"POST_URL" => $arParams["~ENTITY_URL"],
		"HIGHLIGHT" => "Y"
	)
);

?><?=$arResult["OUTPUT_LIST"]["HTML"]?><?
?>