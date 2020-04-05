<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['JS_CONTROL_GLOBAL_ID'] = 		$this->__component->tryParseStringStrict($arParams['JS_CONTROL_GLOBAL_ID']);
$arParams['JS_CONTROL_DEFERRED_INIT'] = 	$this->__component->tryParseStringStrict($arParams['JS_CONTROL_DEFERRED_INIT']);
$arParams['JS_CALLBACK'] = 					$this->__component->tryParseStringStrict($arParams['JS_CALLBACK']);
$arParams['INITIALIZE_BY_GLOBAL_EVENT'] = 	$this->__component->tryParseStringStrict($arParams['INITIALIZE_BY_GLOBAL_EVENT']);

$arResult['PATH_NAMES'] = array(); // deprecated
$arResult['KNOWN_ITEMS'] = array();

if(is_array($arResult['LOCATION']) && !empty($arResult['LOCATION']))
{
	// form knownItems object here (later it will be taken from PRECACHED_POOL, but for now only from LOCATION and PATH)

	$arResult['KNOWN_ITEMS'] = array($arResult['LOCATION']['ID'] => $arResult['LOCATION']);

	if(is_array($arResult['PATH']))
	{
		$path = array();
		$pathNames = array();
		foreach($arResult['PATH'] as $location)
		{
			if($location['ID'] != $arResult['LOCATION']['ID'])
				$path[] = $location['ID'];
			$arResult['PATH_NAMES'][$location['ID']] = $location['NAME']; // deprecated
		}
		$path = array_reverse($path);
		$arResult['KNOWN_ITEMS'][$arResult['LOCATION']['ID']]['PATH'] = $path;

		// now add path items themselve
		foreach($arResult['PATH'] as $location)
		{
			if($location['ID'] == $arResult['LOCATION']['ID'])
				continue;

			array_shift($path);
			$location['PATH'] = $path;
			$arResult['KNOWN_ITEMS'][$location['ID']] = $location;
		}
	}

	$arResult['LOCATION']['VALUE'] = $arResult['LOCATION']['ID'];
	$arResult['LOCATION']['DISPLAY'] = $arResult['LOCATION']['NAME'];
	foreach($arResult['KNOWN_ITEMS'] as &$item)
	{
		$item['VALUE'] = $item['ID'];
		$item['DISPLAY'] = $item['NAME'];

		// prevent garbage from figuring at in-page JSON
		unset($item['LATITUDE']);
		unset($item['LONGITUDE']);
		unset($item['SORT']);
		unset($item['PARENT_ID']);
		unset($item['ID']);
		unset($item['NAME']);
		unset($item['SHORT_NAME']);

		unset($item['LEFT_MARGIN']);
		unset($item['RIGHT_MARGIN']);
	}
}

$arResult['RANDOM_TAG'] = rand(999, 99999);
$this->arResult['ADMIN_MODE'] = defined("ADMIN_SECTION") && ADMIN_SECTION == true;

// modes
$modes = array();
if((defined("ADMIN_SECTION") && ADMIN_SECTION == true) || $arParams['ADMIN_MODE'] == 'Y')
	$modes[] = 'admin';

if (defined("PUBLIC_MODE") && PUBLIC_MODE == 1)
{
	$modes[] = 'admin';
}

foreach($modes as &$mode)
	$mode = 'bx-'.$mode.'-mode';

$arResult['MODE_CLASSES'] = implode(' ', $modes);