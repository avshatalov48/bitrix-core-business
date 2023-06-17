<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixLocationSelectorSystemComponent $component */
/** @var array $arParams */
/** @var array $arResult */

// prepare data for inline js, try to make it smaller
$pathNames = array();

// initial struct
$arResult['FOR_JS'] = array(
	'DATA' => array(
		'LOCATION' => array(),
		'PATH_NAMES' => array(),
	),
	'CONNECTED' => array(
		'LOCATION' => array(),
		'GROUP' => array()
	)
);

if(is_array($arResult['CONNECTIONS']['LOCATION']))
{
	$arResult['FOR_JS']['DATA']['LOCATION'] = $arResult['CONNECTIONS']['LOCATION'];
}

foreach($arResult['FOR_JS']['DATA']['LOCATION'] as &$location)
{
	$location['VALUE'] = $location['ID'];

	$pathIds = array();
	if(is_array($location['PATH']))
	{
		$name = current($location['PATH']);
		$location['DISPLAY'] = $name['NAME'];

		foreach($location['PATH'] as $id => $pathElem)
		{
			$pathIds[] = $id;
			$pathNames[$id] = $pathElem['NAME'];
		}

		array_shift($pathIds);
		$location['PATH'] = $pathIds;
	}
	//else PATH is supposed to be downloaded on-demand

	unset($location['SORT']);
}
unset($location);

$arResult['FOR_JS']['DATA']['PATH_NAMES'] = $pathNames;

// groups
if(isset($arResult['CONNECTIONS']['GROUP']))
{
	$arResult['FOR_JS']['DATA']['GROUPS'] = $arResult['CONNECTIONS']['GROUP'];
	foreach($arResult['FOR_JS']['DATA']['GROUPS'] as &$group)
	{
		$group['DISPLAY'] = $group['NAME'];
		$group['VALUE'] = $group['ID'];
	}
}

// connected

if (!empty($arResult['CONNECTIONS']['LOCATION']) && is_array($arResult['CONNECTIONS']['LOCATION']))
{
	$arResult['FOR_JS']['CONNECTED']['LOCATION'] = array_keys($arResult['CONNECTIONS']['LOCATION']);
}

if (!empty($arResult['CONNECTIONS']['GROUP']) && is_array($arResult['CONNECTIONS']['GROUP']))
{
	$arResult['FOR_JS']['CONNECTED']['GROUP'] = array_keys($arResult['CONNECTIONS']['GROUP']);
}
