<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

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

		if (isset($templateData['TEMPLATE_THEME']))
		{
			$APPLICATION->SetAdditionalCSS('/bitrix/css/main/themes/'.$templateData['TEMPLATE_THEME'].'/style.css', true);
		}
		break;
}

if (isset($templateData['TEMPLATE_THEME']))
{
	$APPLICATION->SetAdditionalCSS($templateFolder.'/'.ToLower($arParams['VIEW_MODE']).'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css');
}

if (isset($templateData['TEMPLATE_LIBRARY']) && !empty($templateData['TEMPLATE_LIBRARY']))
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
		<script type="text/javascript">
			 BX.Currency.setCurrencies(<?=$templateData['CURRENCIES']?>);
		</script>
		<?
	}
}
?>