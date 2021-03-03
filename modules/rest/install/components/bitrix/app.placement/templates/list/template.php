<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent $component
 * @global CMain $APPLICATION
 *
 */
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Text\Converter;

Extension::load(
	[
		'ui.tilegrid',
		'ui.buttons',
		'ui.sidepanel-content',
	]
);

if ($arParams['IS_SLIDER'])
{
	$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
	$bodyClasses = 'app-placement-list-slider-modifier';
	$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));
}

$c = Converter::getHtmlConverter();
$containerId = 'rest_block_' . $c->encode($arResult['PLACEMENT']);
?>
<div class="rest-placement-section" id="<?=$containerId?>">
	<?php if (!empty($arResult['APPLICATION_LIST'])):?>
		<?php
			$applicationIdList = array();
			foreach($arResult['APPLICATION_LIST'] as $app):
		?>
			<div
				id="rest_placement_block_<?=$c->encode($arResult['PLACEMENT'].'_'.$app['ID'])?>"
				class="rest-placement-item"
			>
				<?php $APPLICATION->IncludeComponent(
						'bitrix:app.layout',
						'',
						array(
							'ID' => $app['APP_ID'],
							'PLACEMENT' => $arResult['PLACEMENT'],
							'PLACEMENT_ID' => $app['ID'],
							'PLACEMENT_OPTIONS' => $arResult['PLACEMENT_OPTIONS'],
							'PARAM' => $arParams['PARAM']
						),
						$component,
						array('HIDE_ICONS' => 'Y')
					);
				?>
			</div>
		<?
			endforeach;
		?>
		<script>
			<?php
				if ($arParams['INTERFACE_EVENT']):
			?>
				BX.rest.AppLayout.initializePlacementByEvent('<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>', '<?=\CUtil::JSEscape($arParams['INTERFACE_EVENT'])?>');
			<?php
				endif;
			?>
		</script>
	<?php
	elseif ($arResult['SHOW_MARKET_EMPTY_COUNT'] > 0): ?>
		<?php
		$APPLICATION->IncludeComponent(
			'bitrix:rest.marketplace.category',
			'list',
			array(
				'TAG' => $arResult['APPLICATION_TAGS'],
				'FILTER_ID' => '_list_' . $containerId,
				'SHOW_LAST_BLOCK' => 'Y',
				'BLOCK_COUNT' => $arResult['SHOW_MARKET_EMPTY_COUNT'],
				'SET_TITLE' => 'N',
				'DETAIL_URL_TPL' => $arResult['MP_DETAIL_URL_TPL'],
				'INDEX_URL_PATH' => $arResult['MP_INDEX_PATH'],
				'SECTION_URL_PATH' => $arResult['MP_TAG_PATH'],
			),
			$component
		)
		?>
	<?php endif;?>
</div>