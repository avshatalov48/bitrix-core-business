<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var string $this $templateFolder */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;
global $APPLICATION;
switch ($arParams['VIEW_MODE'])
{
	case 'BANNER':
		$APPLICATION->AddHeadScript($templateFolder.'/banner/script.js');
		$APPLICATION->SetAdditionalCSS($templateFolder.'/banner/style.css');
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
if (isset($templateData['TEMPLATE_THEME']))
{
	$APPLICATION->SetAdditionalCSS($templateData['TEMPLATE_THEME']);
}
if (isset($templateData['TEMPLATE_LIBRARY']) && !empty($templateData['TEMPLATE_LIBRARY']))
{
	$loadCurrency = false;
	if (!empty($templateData['CURRENCIES']))
		$loadCurrency = Loader::includeModule('currency');
	CJSCore::Init($templateData['TEMPLATE_LIBRARY']);
	if ($loadCurrency)
	{
	?>
	<script type="text/javascript">
		BX.Currency.setCurrencies(<? echo $templateData['CURRENCIES']; ?>);
	</script>
<?
	}
}
?>