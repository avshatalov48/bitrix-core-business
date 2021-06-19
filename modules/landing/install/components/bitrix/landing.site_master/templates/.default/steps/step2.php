<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

/** @var \LandingSiteMasterComponent $component */
/** @var array $arResult */

$productUrl = $component->getProductUrl(
	$arResult['SITE']['ID']
);
?>

<div class="landing-sm-content-step-shop">
	<div class="landing-sm-content-arrow-wrapper">
		<div>
			<div class="landing-sm-content-text landing-sm-content-text--title"><?= Loc::getMessage('LANDING_TPL_DEMO_PRODUCTS_IN_TRADE_CATALOG_TITLE');?></div>
			<div class="landing-sm-content-text"><?= Loc::getMessage('LANDING_TPL_DEMO_PRODUCTS_IN_TRADE_CATALOG');?></div>
		</div>
	</div>
	<div class="landing-sm-content-text-br"></div>
	<div class="landing-sm-content-text" style="margin-bottom: 32px;"><?= Loc::getMessage('LANDING_TPL_DEMO_PRODUCTS_IN_TRADE_CATALOG_LOOK');?></div>
	<?php
	if (\Bitrix\Main\Loader::includeModule('bitrix24')):
	?>
		<a href="<?= $productUrl;?>" data-role="landing-sm-content-demo-products"
			onclick="BX.PreventDefault(); BX.SidePanel.Instance.open('<?= \htmlspecialcharsbx(\CUtil::jsEscape($productUrl));?>', {data: {rightBoundary: 0}});"
			target="_blank" class="ui-btn ui-btn-lg ui-btn-success ui-btn-round"
		><?= Loc::getMessage('LANDING_TPL_SHOW_DEMO_PRODUCTS');?></a>
	<?php
	else:
	?>
		<a href="<?= $productUrl;?>" data-role="landing-sm-content-demo-products"
			target="_blank" class="ui-btn ui-btn-lg ui-btn-success ui-btn-round"
			><?= Loc::getMessage('LANDING_TPL_SHOW_DEMO_PRODUCTS');?></a>
	<?php
	endif;
	?>
</div>

<script>
	BX.ready(function()
	{
		var buttonShowStore = document.querySelector('[data-role="landing-sm-content-demo-products"]');
		var buttonNextStep = document.getElementById('landing-master-next');

		function adjustButtonStyle()
		{
			buttonShowStore.classList.remove('ui-btn-success');
			buttonShowStore.classList.add('ui-btn-light-border');
			buttonNextStep.classList.remove('ui-btn-light-border');
			buttonNextStep.classList.add('ui-btn-success');
			buttonShowStore.removeEventListener('click', adjustButtonStyle);
		}

		buttonShowStore.addEventListener('click', adjustButtonStyle);
	});
</script>