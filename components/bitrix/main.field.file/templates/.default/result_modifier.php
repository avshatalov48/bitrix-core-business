<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CJSCore::init(['uf']);

$arResult['targetBlank'] = ($arResult['userField']['SETTINGS']['TARGET_BLANK'] ?? 'Y');

foreach($arResult['value'] as $key => $value)
{
	if($value)
	{
		$value = (int)$value;
		$tag = '';

		$fileInfo = \CFile::GetFileArray($value);
		if($fileInfo)
		{
			$arResult['value'][$key] = $fileInfo;
		}
	}
}