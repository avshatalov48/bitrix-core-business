<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();


$files = $arResult['additionalParameters']['VALUE'];

if (!is_array($files))
{
	$files = [$files];
}

foreach($files as $value)
{
	if ($value)
	{
		print CFile::ShowFile(
			$value,
			$arResult['userField']['SETTINGS']['MAX_SHOW_SIZE'],
			$arResult['userField']['SETTINGS']['LIST_WIDTH'],
			$arResult['userField']['SETTINGS']['LIST_HEIGHT'],
			true
		);
		print '<br>';
	}
}