<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arResult['IS_FREE_DOMAIN'] = false;

if ($arResult['B24_DOMAIN_NAME'])
{
	$arResult['DOMAIN_NAME'] = '';
	$arResult['~DOMAIN_NAME'] = '';
}
else
{
	$arResult['IS_FREE_DOMAIN'] = $arResult['REGISTER']->getCode()
							 == $arResult['DOMAIN_PROVIDER'];
}