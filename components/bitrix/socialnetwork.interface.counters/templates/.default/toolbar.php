<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @global CMain $APPLICATION */
/* @var $arParams array */
/* @var $arResult array */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load([
	'ui.counterpanel',
	'ui.hint',
]);

$classList = [];

if (!empty($arResult['COUNTERS']))
{
	$classList[] = 'sonet-pagetitle-view';
	$bodyClass = $APPLICATION->getPageProperty('BodyClass');
	$APPLICATION->setPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'sonet-pagetitle-view');
}

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
// todo oh
?>

<?php if($arParams['ENTITY_TYPE'] === 'workgroup_list'): ?>

<div class="<?= implode(' ', $classList) ?>">
	<div class="sonet-interface-toolbar">
		<div
			class="sonet-interface-toolbar--item --visible"
			style="opacity: 0.7;"
			data-hint="<?= Loc::getMessage('SONET_SIC_COUNTER_TEMPLATE_HINT') ?>"
			data-hint-no-icon
			data-hint-center
		>
			<div class="ui-counter-panel ui-counter-panel__scope">

				<div class="ui-counter-panel__item --string --without-separator">
					<div class="ui-counter-panel__item-title">
						<?= Loc::getMessage('SONET_SIC_COUNTER_TEMPLATE_MY') . ':' ?>
					</div>
				</div>

				<div class="ui-counter-panel__item-separator --invisible"></div>

				<div class="ui-counter-panel__item --without-separator">
					<div class="ui-counter-panel__item-value">
						<div class="ui-counter ui-counter-md ui-counter-theme">
							<div class="ui-counter-inner">0</div>
						</div>
					</div>
					<div class="ui-counter-panel__item-title">
						<?= Loc::getMessage('SONET_SIC_COUNTER_TEMPLATE_OVERDUE') ?>
					</div>
					<div class="ui-counter-panel__item-cross"><i></i></div>
				</div>

				<div class="ui-counter-panel__item-separator --invisible"></div>

				<div class="ui-counter-panel__item">
					<div class="ui-counter-panel__item-value">
						<div class="ui-counter ui-counter-md ui-counter-theme">
							<div class="ui-counter-inner">0</div>
						</div>
					</div>
					<div class="ui-counter-panel__item-title">
						<?= Loc::getMessage('SONET_SIC_COUNTER_TEMPLATE_COMMUNICATIONS') ?>
					</div>
					<div class="ui-counter-panel__item-cross"><i></i></div>
				</div>

				<div class="ui-counter-panel__item --string --without-separator">
					<div class="ui-counter-panel__item-title">
						<?= Loc::getMessage('SONET_SIC_COUNTER_TEMPLATE_OTHER') . ':' ?>
					</div>
				</div>

				<div class="ui-counter-panel__item-separator --invisible"></div>

				<div class="ui-counter-panel__item --without-separator">
					<div class="ui-counter-panel__item-value">
						<div class="ui-counter ui-counter-md ui-counter-theme">
							<div class="ui-counter-inner">0</div>
						</div>
					</div>
					<div class="ui-counter-panel__item-title">
						<?= Loc::getMessage('SONET_SIC_COUNTER_TEMPLATE_OVERDUE') ?>
					</div>
					<div class="ui-counter-panel__item-cross"><i></i></div>
				</div>

				<div class="ui-counter-panel__item-separator --invisible"></div>

				<div class="ui-counter-panel__item">
					<div class="ui-counter-panel__item-value">
						<div class="ui-counter ui-counter-md ui-counter-theme">
							<div class="ui-counter-inner">0</div>
						</div>
					</div>
					<div class="ui-counter-panel__item-title">
						<?= Loc::getMessage('SONET_SIC_COUNTER_TEMPLATE_COMMUNICATIONS') ?>
					</div>
					<div class="ui-counter-panel__item-cross"><i></i></div>
				</div>

				<div class="ui-counter-panel__item-separator"></div>

				<div class="ui-counter-panel__item --string">
					<div class="ui-counter-panel__item-title">
						<?= Loc::getMessage('SONET_SIC_COUNTER_TEMPLATE_READ_ALL') ?>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<?php else: ?>

<div class="<?= implode(' ', $classList) ?>">
	<div class="sonet-interface-toolbar">
		<div class="sonet-interface-toolbar--item --visible" data-role="sonet-counters-container">
			<div></div>
		</div>
	</div>
</div>

<?php endif;?>

<script>
	BX.message(<?= \CUtil::PhpToJSObject(Loc::loadLanguageFile(__FILE__)) ?>);

	BX.ready(function() {
		BX.UI.Hint.init(document.querySelector('.sonet-interface-toolbar'));
		var counters = new BX.Socialnetwork.Interface.Counters({
			renderTo: document.querySelector('[data-role="sonet-counters-container"]'),
			filterId: '<?= CUtil::JSEscape($arParams['GRID_ID']) ?>',
			entityType: '<?= CUtil::JSEscape($arParams['ENTITY_TYPE']) ?>',
			entityId: <?= (int)$arParams['ENTITY_ID'] ?>,
			role: '<?= CUtil::JSEscape($arParams['ROLE']) ?>',
			entityTitle: '<?= CUtil::JSEscape($arResult['ENTITY_TITLE']) ?>',
			userId: <?= (int)$arParams['USER_ID'] ?>,
			targetUserId: <?= (int) ($arParams['TARGET_USER_ID'] ?? 0) ?>,
			counterTypes:  <?= CUtil::PhpToJSObject($arParams['COUNTERS']) ?>,
			counters:  <?= CUtil::PhpToJSObject($arResult['COUNTERS']) ?>,
			initialCounter: '<?= CUtil::JSEscape($arParams['CURRENT_COUNTER']) ?>',
			signedParameters: <?=CUtil::PhpToJSObject($this->getComponent()->getSignedParameters()) ?>
		});
		counters.render();
	});
</script>
