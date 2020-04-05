<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if (
	!\Bitrix\Main\Loader::includeModule('landing') ||
	\Bitrix\Landing\Landing::getEditMode()
)
{
	?>
	<div class="g-min-height-200 g-flex-centered">
		<div class="g-pa-10 g-brd-html-dashed g-bg-white">
			<?= Loc::getMessage('LANDING_TPL_NOT_IN_PREVIEW_MODE');?>
		</div>
	</div>
	<?
}
else
{
	echo \htmlspecialcharsback($arParams['~HTML_CODE']);
}



