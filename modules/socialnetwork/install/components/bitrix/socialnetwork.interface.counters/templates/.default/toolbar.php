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

$classList = [];

if (!empty($arResult['COUNTERS']))
{
	$classList[] = 'sonet-pagetitle-view';
	$bodyClass = $APPLICATION->getPageProperty('BodyClass');
	$APPLICATION->setPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'sonet-pagetitle-view');
}

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
?>
<div class="<?= implode(' ', $classList) ?>">
	<div class="sonet-interface-toolbar">
		<div class="sonet-interface-toolbar--item --visible" data-role="sonet-counters-container">
			<div></div>
		</div>
	</div>
</div>
<script>
	BX.message(<?= \CUtil::PhpToJSObject(Loc::loadLanguageFile(__FILE__)) ?>);
	BX.ready(function() {
		var counters = new BX.Socialnetwork.Interface.Counters({
			renderTo: document.querySelector('[data-role="sonet-counters-container"]'),
			filterId: '<?= CUtil::JSEscape($arParams['GRID_ID']) ?>',
			entityType: '<?= CUtil::JSEscape($arParams['ENTITY_TYPE']) ?>',
			entityId: <?= (int)$arParams['ENTITY_ID'] ?>,
			role: '<?= CUtil::JSEscape($arParams['ROLE']) ?>',
			entityTitle: '<?= CUtil::JSEscape($arResult['ENTITY_TITLE']) ?>',
			userId: <?= (int)$arParams['USER_ID'] ?>,
			targetUserId: <?= (int)$arParams['TARGET_USER_ID'] ?>,
			counterTypes:  <?= CUtil::PhpToJSObject($arParams['COUNTERS']) ?>,
			counters:  <?= CUtil::PhpToJSObject($arResult['COUNTERS']) ?>,
			initialCounter: '<?= CUtil::JSEscape($arParams['CURRENT_COUNTER']) ?>',
			signedParameters: <?=CUtil::PhpToJSObject($this->getComponent()->getSignedParameters()) ?>
		});
		counters.render();
	});
</script>