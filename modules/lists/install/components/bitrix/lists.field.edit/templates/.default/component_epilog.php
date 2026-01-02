<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var array $arResult */

$jsClass = 'ListsFieldEditClass_' . $arResult['RAND_STRING'];
$listAction = [];
if($arResult["FIELD_ID"] && $arResult["FIELD_ID"] !== 'NAME')
{
	$listAction[] = [
		'text' => Loc::getMessage('CT_BLFE_TOOLBAR_DELETE'),
		'onclick' => new \Bitrix\UI\Buttons\JsCode(
			"BX.Lists['" . $jsClass . "'].deleteField('form_" . $arResult["FORM_ID"] . "', '" .
			Loc::getMessage("CT_BLFE_TOOLBAR_DELETE_WARNING") . "')"
		),
	];
}

$returnButton = new Bitrix\UI\Buttons\Button([
	'color' => \Bitrix\UI\Buttons\Color::LINK,
	'text' => Loc::getMessage("CT_BLFE_TOOLBAR_RETURN_LIST_ELEMENT_MSGVER_1"),
	'link' => $arResult["LIST_FIELDS_URL"],
	'icon' => Bitrix\UI\Buttons\Icon::BACK,
]);

$returnButton->setSize(Bitrix\UI\Buttons\Size::SMALL);
Bitrix\UI\Toolbar\Facade\Toolbar::addButton($returnButton);

if ($listAction)
{
	$actionsButton = new \Bitrix\UI\Buttons\Button([
		'color' => Bitrix\UI\Buttons\Color::LIGHT_BORDER,
		'text' => Loc::getMessage('CT_BLFE_TOOLBAR_ACTION'),
		'menu' => [
			'items' => [
				[
					'text' => Loc::getMessage('CT_BLFE_TOOLBAR_DELETE'),
					'onclick' => new \Bitrix\UI\Buttons\JsCode(
						"BX.Lists['" . $jsClass . "'].deleteField('form_" . $arResult["FORM_ID"] . "',
						'" . Loc::getMessage("CT_BLFE_TOOLBAR_DELETE_WARNING") . "')"
					),
				],
			],
		],
	]);
	$actionsButton->setSize(Bitrix\UI\Buttons\Size::SMALL);
	Bitrix\UI\Toolbar\Facade\Toolbar::addButton($actionsButton);
}
