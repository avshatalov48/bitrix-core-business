<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent("bitrix:voting.result", "main_page", 
	array(
		"VOTE_ID" => $arResult["VOTE_ID"],
		"PERMISSION" => $arParams["PERMISSION"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"ADDITIONAL_CACHE_ID" => $arResult["ADDITIONAL_CACHE_ID"],
		"VOTE_ALL_RESULTS" => $arParams["VOTE_ALL_RESULTS"],
	),
	($this->__component->__parent ? $this->__component->__parent : $component)
);
?>
