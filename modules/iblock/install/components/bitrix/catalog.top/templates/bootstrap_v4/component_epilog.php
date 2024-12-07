<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var string  $templateFolder
 * @var array $templateData
 * @var CatalogSectionComponent $component
 */

global $APPLICATION;

switch ($arParams['VIEW_MODE'])
{
	case 'BANNER':
		$APPLICATION->AddHeadScript($templateFolder.'/banner/script.js');
		$APPLICATION->SetAdditionalCSS($templateFolder.'/banner/style.css');
		break;
	case 'SLIDER':
		$APPLICATION->AddHeadScript($templateFolder.'/slider/script.js');
		$APPLICATION->SetAdditionalCSS($templateFolder.'/slider/style.css');
		break;
	case 'SECTION':
	default:
		$APPLICATION->AddHeadScript($templateFolder.'/section/script.js');
		$APPLICATION->SetAdditionalCSS($templateFolder.'/section/style.css');
		break;
}

if (!empty($templateData['TEMPLATE_LIBRARY']))
{
	$loadCurrency = false;

	if (!empty($templateData['CURRENCIES']))
	{
		$loadCurrency = \Bitrix\Main\Loader::includeModule('currency');
	}

	CJSCore::Init($templateData['TEMPLATE_LIBRARY']);

	if ($loadCurrency)
	{
		?>
		<script>
			BX.Currency.setCurrencies(<?=$templateData['CURRENCIES']?>);
		</script>
		<?php
	}
}
