<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arCurrentValues
 * @var array $arTemplateParameters
 */

$arTemplateParameters['USE_OFFER_NAME'] = [
	'PARENT' => 'VISUAL',
	'NAME' => Loc::getMessage('CP_BCI_TPL_USE_OFFER_NAME'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N'
];