<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.pm.edit",
	"",
	array(
		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"] ?? null,
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"URL_TEMPLATES_PM_FOLDER" => $arResult["URL_TEMPLATES_PM_FOLDER"] ?? null,
		"URL_TEMPLATES_PM_LIST" => $arResult["URL_TEMPLATES_PM_LIST"] ?? null,
		"URL_TEMPLATES_PM_READ" => $arResult["URL_TEMPLATES_PM_READ"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_PM_SEARCH" => $arResult["URL_TEMPLATES_PM_SEARCH"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

		"MID" => $arResult["MID"] ?? null,
		"FID" => $arResult["FID"] ?? null,
		"UID" =>  $arResult["UID"] ?? null,
		"mode" =>  $arResult["mode"] ?? null,

		"SMILES_COUNT" => $arParams["SMILES_COUNT"] ?? null,
		"EDITOR_CODE_DEFAULT" => $arParams["EDITOR_CODE_DEFAULT"] ?? null,
		"SEO_USER" => $arParams["SEO_USER"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null
	),
	$component
);
?>
