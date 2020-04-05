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