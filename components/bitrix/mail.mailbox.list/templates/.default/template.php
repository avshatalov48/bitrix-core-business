<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die;
}

use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\Main\UI\Extension;

/** @var array $arParams */
/** @var array $arResult */
/** @var $APPLICATION */

$component = $this->getComponent();

Extension::load([
	'ui.buttons',
	'ui.forms',
	'main.grid',
	'main.popup',
	'mail.grid.mailbox-grid',
	'ui.mail.provider-showcase',
]);

$APPLICATION->SetTitle($arResult['TITLE']);

Toolbar::deleteFavoriteStar();

Toolbar::addFilter(\Bitrix\Main\Filter\Component\ComponentParams::get($arResult['GRID_FILTER'],
	[
		'GRID_ID' => $arResult['GRID_ID'],
		'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
		'ENABLE_LIVE_SEARCH' => true,
		'ENABLE_LABEL' => true,
		'CONFIG' => [
			'AUTOFOCUS' => false,
		],
	],
));

$gridContainerId = 'bx-mml-' . $arResult['GRID_ID'] . '-container';

?><span class="mail-mailbox-list-grid-container --ui-context-content-light" id="<?= htmlspecialcharsbx($gridContainerId)?>"><?php
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.grid',
		'',
		$arResult['GRID_PARAMS'],
		$component,
	);
?></span>
<script>
	BX.ready(function()
	{
		const gridId = '<?= CUtil::JSEscape($arResult['GRID_ID']) ?>'

		new BX.Mail.MailboxList.Manager({
			gridId,
		});
	});
</script>
