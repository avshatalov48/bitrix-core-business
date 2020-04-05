<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogTopComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);

if (!empty($arResult['ITEMS']))
{
	$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
	$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
	$elementDeleteParams = array('CONFIRM' => GetMessage('CT_BCT_ELEMENT_DELETE_CONFIRM'));

	$fullPath = \Bitrix\Main\Application::getDocumentRoot().$templateFolder;
	$templateLibrary = array('popup');
	$currencyList = '';

	if (!empty($arResult['CURRENCIES']))
	{
		$templateLibrary[] = 'currency';
		$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
	}

	$templateData = array(
		'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
		'TEMPLATE_CLASS' => 'bx_'.$arParams['TEMPLATE_THEME'],
		'TEMPLATE_LIBRARY' => $templateLibrary,
		'CURRENCIES' => $currencyList
	);
	unset($currencyList, $templateLibrary);

	switch ($arParams['VIEW_MODE'])
	{
		case 'BANNER':
			include($fullPath.'/banner/template.php');
			break;
		case 'SLIDER':
			include($fullPath.'/slider/template.php');
			break;
		case 'SECTION':
			include($fullPath.'/section/template.php');
			break;
	}
	?>
	<script type='text/javascript'>
	   BX.message({
		   BTN_MESSAGE_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCT_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
		   BASKET_URL: '<?=$arParams['BASKET_URL']?>',
		   ADD_TO_BASKET_OK: '<?=GetMessageJS('ADD_TO_BASKET_OK')?>',
		   TITLE_ERROR: '<?=GetMessageJS('CT_BCT_CATALOG_TITLE_ERROR')?>',
		   TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCT_CATALOG_TITLE_BASKET_PROPS')?>',
		   TITLE_SUCCESSFUL: '<?=GetMessageJS('ADD_TO_BASKET_OK')?>',
		   BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCT_CATALOG_BASKET_UNKNOWN_ERROR')?>',
		   BTN_MESSAGE_SEND_PROPS: '<?=GetMessageJS('CT_BCT_CATALOG_BTN_MESSAGE_SEND_PROPS')?>',
		   BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCT_CATALOG_BTN_MESSAGE_CLOSE')?>',
		   BTN_MESSAGE_CLOSE_POPUP: '<?=GetMessageJS('CT_BCT_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
		   COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCT_CATALOG_MESS_COMPARE_OK')?>',
		   COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCT_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
		   COMPARE_TITLE: '<?=GetMessageJS('CT_BCT_CATALOG_MESS_COMPARE_TITLE')?>',
		   PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCT_CATALOG_PRICE_TOTAL_PREFIX')?>',
		   BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCT_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
		   SITE_ID: '<?=SITE_ID?>'
	   });
	</script>
	<?
}
?>