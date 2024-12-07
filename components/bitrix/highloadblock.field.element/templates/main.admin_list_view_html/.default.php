<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

echo (
	!empty($arResult['additionalParameters']['VALUE'])
		? $arResult['additionalParameters']['VALUE']
		: '&nbsp;'
);
