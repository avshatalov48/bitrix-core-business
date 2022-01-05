<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

Loc::loadMessages(__FILE__);

if (
	empty($arResult['TAB'])
	|| $arResult['TAB'] === 'edit'
)
{
	?><input type="hidden" value="<?= $arResult['POST']['INITIATE_PERMS'] ?>" name="GROUP_INITIATE_PERMS"><?php
}
