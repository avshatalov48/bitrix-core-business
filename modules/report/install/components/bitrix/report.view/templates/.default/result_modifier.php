<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if ($arResult['allowHorizontalScroll'])
{
	Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/components/bitrix/main.ui.grid/templates/.default/js/utils.js');
	Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/components/bitrix/main.ui.grid/templates/.default/js/fader.js');
}
