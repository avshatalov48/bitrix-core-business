<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

$c = \Bitrix\Main\Text\Converter::getHtmlConverter();

$applicationIdList = array();
$applicationList = array();
foreach($arResult['APPLICATION_LIST'] as $app):
	$applicationIdList[] = $app['ID'];
	$applicationList[] = array_change_key_case($app, CASE_LOWER);
endforeach;
?>

<script>
	BX.rest.AppLayout.setPlacement('<?=$arResult['PLACEMENT']?>', new BX.rest.PlacementMenu({
		placement: '<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>',
		layout: 'rest_menu_<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>',
		node: 'rest_menu_<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>_#ID#',
		list: <?=\CUtil::PhpToJSObject($applicationIdList)?>,
		extendedList: <?=\CUtil::PhpToJSObject($applicationList)?>,
		current: <?=intval($arResult['APPLICATION_CURRENT'])?>,
		unload: false
	}));

<?php
if(isset($arParams['INTERFACE_EVENT']) && $arParams['INTERFACE_EVENT']):
?>
	BX.rest.AppLayout.initializePlacementByEvent('<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>', '<?=\CUtil::JSEscape($arParams['INTERFACE_EVENT'])?>');
<?php
endif;
?>
</script>
