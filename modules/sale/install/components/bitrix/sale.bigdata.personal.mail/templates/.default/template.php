<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arResult */

\Bitrix\Main\Mail\EventMessageThemeCompiler::includeComponent(
	"bitrix:catalog.show.products.mail",
	$this->getName(),
	array(
		"LIST_ITEM_ID" => $arResult['ITEMS']
	)
);