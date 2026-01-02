<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die;
}

/** @var array $arResult */
?>

<div class="rest-market-section">
	<?php if ($arResult['ITEMS']['import']): ?>
		<div class="rest-market-section__item rest-market-section__item_import">
			<img src="<?= $arResult['ITEMS']['import']['icon'] ?>" class="rest-market-section__item-img" alt=""/>
			<div class="rest-market-section__item-container">
				<div class="rest-market-section__item-title">
					<?= $arResult['ITEMS']['import']['title'] ?>
				</div>
				<div class="rest-market-section__item-description">
					<?= $arResult['ITEMS']['import']['description'] ?>
				</div>
				<div class="rest-market-section__item-action --ui-context-edge-dark">
					<a href="<?= $arResult['ITEMS']['import']['link'] ?>"
					   class="ui-btn --air ui-btn-sm --style-outline-accent-1 ui-btn-no-caps">
					<span class="ui-btn-text">
						<span class="ui-btn-text-inner">
							<?= $arResult['ITEMS']['import']['action'] ?>
						</span>
					</span>
					</a>
				</div>
			</div>
		</div>
	<?php
	endif;

	if ($arResult['ITEMS']['export']):?>
		<div class="rest-market-section__item rest-market-section__item_export">
			<img src="<?= $arResult['ITEMS']['export']['icon'] ?>" class="rest-market-section__item-img" alt=""/>
			<div class="rest-market-section__item-container">
				<div class="rest-market-section__item-title">
					<?= $arResult['ITEMS']['export']['title'] ?>
				</div>
				<div class="rest-market-section__item-description">
					<?= $arResult['ITEMS']['export']['description'] ?>
				</div>

				<div class="rest-market-section__item-action --ui-context-edge-dark">
					<a href="<?= $arResult['ITEMS']['export']['link'] ?>"
					   class="ui-btn --air ui-btn-sm --style-outline-accent-1 ui-btn-no-caps">
					<span class="ui-btn-text">
						<span class="ui-btn-text-inner">
							<?= $arResult['ITEMS']['export']['action'] ?>
						</span>
					</span>
					</a>
				</div>
			</div>
		</div>
	<?php
	endif
	?>
</div>
