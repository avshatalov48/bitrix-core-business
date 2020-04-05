<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var StoreCatalogFilterBlock $classBlock
 */
?>
<section class="landing-block g-pt-0 g-pb-0">
	<div class="bx-sidebar-block g-pt-0 g-pb-0">
		<?if ($classBlock->get('SHOW_FILTER')):?>
			<?$APPLICATION->IncludeComponent(
				'bitrix:catalog.smart.filter',
				'bootstrap_v4',
				array(
					'IBLOCK_TYPE' => '',
					'IBLOCK_ID' => $classBlock->get('IBLOCK_ID'),
					'SECTION_ID' => $classBlock->get('SECTION_ID'),
					'FILTER_NAME' => 'arrFilter',
					'CACHE_TYPE' => 'A',
					'CACHE_TIME' => '36000000',
					'CACHE_GROUPS' => 'N',
					'SAVE_IN_SESSION' => 'N',
					'FILTER_VIEW_MODE' => 'VERTICAL',
					'XML_EXPORT' => 'Y',
					'SECTION_TITLE' => 'NAME',
					'SECTION_DESCRIPTION' => 'DESCRIPTION',
					'HIDE_NOT_AVAILABLE' => 'N',
					'TEMPLATE_THEME' => 'vendor',
					'CONVERT_CURRENCY' => 'N',
					'CURRENCY_ID' => '',
					'SEF_MODE' => 'N',
					'SEF_RULE' => '',
					//'SEF_RULE' => '/store/catalog/#SECTION_CODE#/filter/#SMART_FILTER_PATH#/apply/'
					//'SMART_FILTER_PATH' => $arResult['VARIABLES']['SMART_FILTER_PATH'],
					'PAGER_PARAMS_NAME' => '',
					'INSTANT_RELOAD' => 'N',
					'PRICE_CODE' => $classBlock->get('PRICE_CODE'),
					'CONTEXT_SITE_ID' => $classBlock->get('SITE_ID')
				),
				false
			);
			?>
		<?endif;?>
	</div>
</section>