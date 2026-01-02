<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die;
}

/** @var array $arResult */

use Bitrix\Main\Localization\Loc; ?>

<div class="rest-market-solution-section">
	<div class="rest-market-solution-main">
		<div class="rest-market-solution-main__banner rest-market-solution-main__banner--<?= $arResult['CURRENT_LANG'] === 'ru' ? 'ru' : 'other' ?>">
			<div class="rest-market-solution-main__banner-wrapper">
				<div class="rest-market-solution-main__banner-title">
					<?= Loc::getMessage("REST_MARKETPLACE_SOLUTION_BANNER_TITLE") ?>
				</div>
				<div class="rest-market-solution-main__banner-description">
					<?= Loc::getMessage("REST_MARKETPLACE_SOLUTION_BANNER_DESCRIPTION") ?>
				</div>
				<div class="rest-market-solution-main__banner-action">
					<a href="/market/category/vertical_crm/"
					   class="ui-btn --air ui-btn-md --style-filled ui-btn-no-caps">
						<span class="ui-btn-text">
							<span class="ui-btn-text-inner">
								<?= Loc::getMessage("REST_MARKETPLACE_SOLUTION_BANNER_ACTION") ?>
							</span>
						</span>
					</a>
				</div>
			</div>
		</div>
	</div>

	<?php if (!empty($arResult['tags'])): ?>
		<div class="rest-market-solution-tag">
			<?php foreach ($arResult['tags'] as $tag): ?>
				<a href="/market/category/vertical_crm/?tag=<?= $tag['tag'] ?>" class="rest-market-solution-tag__item">
					<div class="rest-market-solution-tag__item-wrapper">
						<div class="rest-market-solution-tag__item-title">
							<?= $tag['title'] ?>
						</div>
						<div class="rest-market-solution-tag__item-description">
							<?= $tag['description'] ?>
						</div>
					</div>
					<div class="rest-market-solution-tag__item-bg">
						<img src="<?= $tag['image'] ?>" class="rest-market-solution__item-img" alt=""/>
					</div>
				</a>
			<?php
			endforeach;
			?>
		</div>
	<?php endif; ?>

	<div class="rest-market-solution-all">
		<a href="/market/category/vertical_crm/" class="ui-btn --air ui-btn-lg --style-outline ui-btn-no-caps">
			<span class="ui-btn-text">
				<span class="ui-btn-text-inner">
					<?= Loc::getMessage("REST_MARKETPLACE_SOLUTION_BTN_ALL") ?>
				</span>
			</span>
		</a>
	</div>
</div>