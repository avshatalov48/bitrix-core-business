<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogTopComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();
if (isset($arParams['~TEMPLATE_THEME']) && $arParams['~TEMPLATE_THEME'] == 'site')
{
	$arParams['TEMPLATE_THEME'] = '';
}

$arParams['~MESS_BTN_BUY'] ??= '';
$arParams['~MESS_BTN_DETAIL'] ??= '';
$arParams['~MESS_BTN_COMPARE'] ??= '';
$arParams['~MESS_BTN_SUBSCRIBE'] ??= '';
$arParams['~MESS_BTN_ADD_TO_BASKET'] ??= '';
$arParams['~MESS_NOT_AVAILABLE'] ??= '';
$arParams['~MESS_NOT_AVAILABLE_SERVICE'] ??= '';
$arParams['~MESS_SHOW_MAX_QUANTITY'] ??= '';
$arParams['~MESS_RELATIVE_QUANTITY_MANY'] ??= '';
$arParams['~MESS_RELATIVE_QUANTITY_FEW'] ??= '';