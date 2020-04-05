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

?>
<div id="rest_carousel_<?=$c->encode($arResult['PLACEMENT'])?>" class="rest-placement-carousel">
<?php
$applicationIdList = array();
foreach($arResult['APPLICATION_LIST'] as $app):
	$current = $app['ID'] == $arResult['APPLICATION_CURRENT'];
	$applicationIdList[] = $app['ID'];
?>
	<div id="rest_carousel_<?=$c->encode($arResult['PLACEMENT'].'_'.$app['ID'])?>" class="rest-placement-carousel-app"<?if(!$current):?> style="display: none"<?endif;?>>
<?
	if($current):
		require_once('layout.php');
	endif;
?>
	</div>
<?
endforeach;

?>

</div>
<script>
	BX.rest.AppLayout.setPlacement('<?=$arResult['PLACEMENT']?>', new BX.rest.PlacementCarousel({
		placement: '<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>',
		layout: 'rest_carousel_<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>',
		node: 'rest_carousel_<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>_#ID#',
		list: <?=\CUtil::PhpToJSObject($applicationIdList)?>,
		current: <?=intval($arResult['APPLICATION_CURRENT'])?>,
		ajaxUrl: '<?=\CUtil::JSEscape($arResult['AJAX_URL'])?>',
		unload: false
	}));

<?php
if($arParams['INTERFACE_EVENT']):
?>
	BX.rest.AppLayout.initializePlacementByEvent('<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>', '<?=\CUtil::JSEscape($arParams['INTERFACE_EVENT'])?>');
<?php
endif;
?>
</script>
