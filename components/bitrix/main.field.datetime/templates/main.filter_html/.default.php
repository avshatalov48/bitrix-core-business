<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arResult
 */

print CalendarPeriod(
	$arResult['additionalParameters']['NAME'] . '_from',
	$GLOBALS[$arResult['additionalParameters']['NAME'] . '_from'],
	$arResult['additionalParameters']['NAME'] . '_to',
	$GLOBALS[$arResult['additionalParameters']['NAME'] . '_to'],
	'find_form',
	'Y'
);
