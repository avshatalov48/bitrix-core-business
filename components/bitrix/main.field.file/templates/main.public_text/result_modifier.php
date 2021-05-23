<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

$result = [];
foreach($arResult['value'] as $value)
{
	$fileInfo = \CFile::GetFileArray($value);
	if(is_array($fileInfo))
	{
		$result[] = $fileInfo['ORIGINAL_NAME'];
	}
}
$arResult['value'] = HtmlFilter::encode(implode(', ', $result));