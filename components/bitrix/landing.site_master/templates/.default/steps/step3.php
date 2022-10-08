<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Help;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

/** @var \LandingSiteMasterComponent $component */
/** @var array $arResult */

$siteId = $arResult['SITE']['ID'];

if (\Bitrix\Main\Loader::includeModule('pull'))
{
	\CPullWatch::add(Manager::getUserId(), 'CRM_ENTITY_ORDER');
	\CPullWatch::add(Manager::getUserId(), 'LANDING_ENTITY_LANDING');
}
?>
<div class="landing-sm-content-page-list">
	<div class="landing-sm-content-page landing-sm-content-page--hover<?= $component->siteHasViewsAction($siteId) ? ' landing-sm-content-page--check' : '';?>" data-method="siteHasViews" data-role="landing-sm-phone-qr">
		<div class="landing-sm-content-page-title"><?= Loc::getMessage('LANDING_TPL_SHOW_SHOP');?></div>
		<div class="landing-sm-content-page-edit landing-sm-content-page-edit--text"><?= Loc::getMessage('LANDING_TPL_ORDER_CAMERA');?></div>
	</div>
	<div class="landing-sm-content-page <?= $component->siteHasOrdersAction($siteId) ? ' landing-sm-content-page--check' : '';?>" data-method="siteHasOrders" data-role="landing-sm-shop-page-step-4">
		<div class="landing-sm-content-page-title"><?= Loc::getMessage('LANDING_TPL_FIRST_ORDER');?></div>
		<div class="landing-sm-content-page-edit landing-sm-content-page-edit--text"><?= Loc::getMessage('LANDING_TPL_ORDER_CREATE');?></div>
	</div>
</div>
<?php if (Manager::getZone() === 'by'):?>
	<div class="landing-sm-content-text landing-sm-content-text--italic">
		<?= Loc::getMessage('LANDING_TPL_FIRST_ORDER_REQUIREMENTS', [
			'#LINK_HELP1#' => '<a href="' . Help::getHelpUrl('FIRST_ORDER_REQUIREMENTS') . '">',
			'#LINK_HELP2#' => '</a>'
		])?>
	</div>
<?php endif;?>
<?php if (Manager::getZone() === 'ru'):?>
	<div class="landing-sm-content-text landing-sm-content-text--italic">
		<?= Loc::getMessage('LANDING_TPL_FIRST_ORDER_STEPS_1', [
			'#LINK_HELP1#' => '<a href="' . Help::getHelpUrl('FREE_MESSAGES') . '">',
			'#LINK_HELP2#' => '</a>'
		])?>
	</div>
	<div class="landing-sm-content-text landing-sm-content-text--italic">
		<?= Loc::getMessage('LANDING_TPL_FIRST_ORDER_STEPS_2')?>
	</div>
<?php endif;?>
<script>
	BX.ready(function() {

		function adjustFinalStyle()
		{
			var steps = document.querySelectorAll('.landing-sm-content-page');
			var checkedSteps = document.querySelectorAll('.landing-sm-content-page--check');
			if(steps.length === checkedSteps.length) {
				var buttonNextStep = document.getElementById('landing-master-next');
				BX.addClass(buttonNextStep, 'ui-btn-success');
				BX.removeClass(buttonNextStep, 'ui-btn-light-border');
				checkStepImage(document.querySelector('[data-index="landing-sm-shop-page-step-5"]'));
			}
		}

		function checkStepImage(itemNode)
		{
			var currentCheckImage = document.querySelector('.landing-sm-phone-pic-item--show');
			if(currentCheckImage)
			{
				BX.removeClass(currentCheckImage, 'landing-sm-phone-pic-item--show');
			}
			BX.addClass(itemNode, 'landing-sm-phone-pic-item--show');
		}

		function selectStepItem(itemNode)
		{
			var currentSelectItem = document.querySelector('.landing-sm-content-page--hover');
			if(currentSelectItem)
			{
				BX.removeClass(currentSelectItem, 'landing-sm-content-page--hover');
			}
			BX.addClass(itemNode, 'landing-sm-content-page--hover');
		}

		function checkStepItem(itemNode)
		{
			BX.addClass(itemNode, 'landing-sm-content-page--check');
		}

		function pushReaction(action)
		{
			var item = document.querySelector('[data-method="' + action + '"]');
			if (item)
			{
				checkStepItem(item);
				checkStepImage(document.querySelector('[data-index="' + item.getAttribute('data-role') + '"]'));
				BX.removeClass(item, 'landing-sm-content-page--hover');

				var otherItemId = (action === 'siteHasOrders')
					? 'siteHasViews'
					: 'siteHasOrders';

				var otherItem = document.querySelector('[data-method="' + otherItemId +'"]');
				if(otherItemId === 'siteHasOrders')
				{
					selectStepItem(otherItem);
					checkStepImage(document.querySelector('[data-index="' + otherItem.getAttribute('data-role') + '"]'));
				}
			}
			adjustFinalStyle();
		}

		top.BX.addCustomEvent('onPullEvent', function(module, command, params)
		{
			if (module !== 'crm' && module !== 'landing')
			{
				return;
			}
			if (command === 'onOrderSave' || command === 'onLandingFirstView')
			{
				var action = (command === 'onOrderSave')
					? 'siteHasOrders'
					: 'siteHasViews';
				if (command === 'onLandingFirstView')
				{
					pushReaction(action);
					return;
				}
				BX.ajax.runComponentAction('bitrix:landing.site_master',
					action,
					{
						mode: 'class',
						data: { siteId: <?= $siteId;?> }
					})
					.then(function(response) {
						if (response.data === true)
						{
							pushReaction(action);
						}
					})
			}
		});
	});
</script>