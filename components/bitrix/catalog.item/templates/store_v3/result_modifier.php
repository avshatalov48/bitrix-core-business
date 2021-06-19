<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 */

if (!isset($arParams['USE_OFFER_NAME']) || $arParams['USE_OFFER_NAME'] !== 'Y')
{
	$arParams['USE_OFFER_NAME'] = 'N';
}

