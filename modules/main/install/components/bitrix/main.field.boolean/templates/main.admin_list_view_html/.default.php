<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BooleanType;

$label = BooleanType::getLabels($arResult['userField']);

print (
$arResult['additionalParameters']['VALUE'] ?
	HtmlFilter::encode($label[1]) : HtmlFilter::encode($label[0])
);

