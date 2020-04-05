<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);


if ($arParams['ENABLED'] != 'Y' && $arParams['EDIT_MODE'] == 'Y')
{
	$APPLICATION->IncludeComponent(
		'bitrix:landing.blocks.message',
		'locked',
		array(
			'HEADER' => Loc::getMessage('LANDING_TPL_NOT_ENABLE_TITLE'),
			'MESSAGE' => Loc::getMessage('LANDING_TPL_NOT_ENABLE_TEXT'),
			'BUTTON' => Loc::getMessage('LANDING_TPL_NOT_ENABLE_LINK'),
			'LINK' => \Bitrix\Landing\Manager::BUY_LICENSE_PATH
		),
		$component
	);
}
else if ($arParams['EDIT_MODE'] == 'Y')
{
	?>
	<div class="g-min-height-200 g-flex-centered g-height-100">
		<div class="g-pa-10 g-brd-html-dashed g-bg-white">
			<?= Loc::getMessage('LANDING_TPL_NOT_IN_PREVIEW_MODE');?>
		</div>
	</div>
	<?
}
else if ($arParams['ENABLED'] == 'Y' || $arParams['PREVIEW_MODE'] == 'Y')
{
	echo $component->htmlspecialcharsback($arParams['~HTML_CODE']);
}



