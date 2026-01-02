<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var array $arResult */

$button = new \Bitrix\UI\Buttons\Button([
	'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
	'click' => new \Bitrix\UI\Buttons\JsCode("top.BX.Helper.show('redirect=detail&code=21290220')"),
	'text' => Loc::getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_HELP_BUTTON'),
]);

\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($button);
\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
