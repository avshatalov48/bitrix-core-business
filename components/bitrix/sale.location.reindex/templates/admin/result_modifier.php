<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$smthSelected = false;
if(is_array($arResult['TYPES']))
{
	foreach($arResult['TYPES'] as $type)
	{
		if($type['SELECTED'])
		{
			$smthSelected = true;
			break;
		}
	}
}
if(!$smthSelected)
	$arResult['TYPES_UNSELECTED'] = true;

$smthSelected = false;
if(is_array($arResult['LANGS']))
{
	foreach($arResult['LANGS'] as $lang)
	{
		if($lang['SELECTED'])
		{
			$smthSelected = true;
			break;
		}
	}
}
if(!$smthSelected)
	$arResult['LANGS_UNSELECTED'] = true;