<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$isFirst = true;
foreach($arResult['value'] as $value)
{
	if(!$isFirst)
	{
		print '<br>';
	}
	$isFirst = false;
	print (!empty($value) ? $value : '');
}
