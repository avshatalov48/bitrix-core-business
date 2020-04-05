<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$defaultParams = array(
	'POSITION_FIXED' => 'Y',
	'POSITION' => 'top left'
);

$arParams = array_merge($defaultParams, $arParams);
unset($defaultParams);
if ($arParams['POSITION_FIXED'] != 'N')
	$arParams['POSITION_FIXED'] = 'Y';

$arParams['POSITION'] = trim($arParams['POSITION']);
$arParams['POSITION'] = explode(' ', $arParams['POSITION']);
if (empty($arParams['POSITION']) || count($arParams['POSITION']) != 2)
	$arParams['POSITION'] = array('top', 'left');
if ($arParams['POSITION'][0] != 'bottom')
	$arParams['POSITION'][0] = 'top';
if ($arParams['POSITION'][1] != 'right')
	$arParams['POSITION'][1] = 'left';
?>