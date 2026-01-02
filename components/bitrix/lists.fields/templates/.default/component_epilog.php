<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var array $arResult */

$returnButton = new Bitrix\UI\Buttons\Button([
	'color' => \Bitrix\UI\Buttons\Color::LINK,
	'text' => Loc::getMessage("CT_BLF_TOOLBAR_RETURN_LIST_ELEMENT_MSGVER_1"),
	'link' => $arResult["LIST_URL"],
	'icon' => Bitrix\UI\Buttons\Icon::BACK,
]);


$returnButton->setSize(Bitrix\UI\Buttons\Size::SMALL);
Bitrix\UI\Toolbar\Facade\Toolbar::addButton($returnButton);

$addFieldButton = new Bitrix\UI\Buttons\Button([
	'color' => \Bitrix\UI\Buttons\Color::PRIMARY,
	'text' => Loc::getMessage('CT_BLF_TOOLBAR_ADD'),
	'link' => $arResult['LIST_FIELD_EDIT_URL'],
	'icon' => Bitrix\UI\Buttons\Icon::ADD,
]);
$addFieldButton->setSize(Bitrix\UI\Buttons\Size::SMALL);
Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addFieldButton, Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
