<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var array $arResult */

$helpButton = new \Bitrix\UI\Buttons\Button([
	'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
	'click' => new \Bitrix\UI\Buttons\JsCode("top.BX.Helper.show('redirect=detail&code=14922854')"),
	'text' => Loc::getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_HEPL'),
]);

\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($helpButton);
\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
