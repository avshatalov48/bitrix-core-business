<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// rearrange layout

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if(is_array($arResult['LAYOUT']) && is_array($arResult['LAYOUT']['']))
{
	$leftTopLevel = array(
		'RUSSIA' => 		'0000028023',
		'URKAIN' => 		'0000000364',
		'KAZAKHSTAN' => 	'0000000276',
		'BELARUS' => 		'0000000001'
	);

	$world = array();
	foreach($arResult['LAYOUT'][''] as $code => $item)
	{
		if(!in_array($item['CODE'], $leftTopLevel))
		{
			$world[$item['CODE']] = $item;
			unset($arResult['LAYOUT'][''][$code]);
		}
	}

	if(!empty($world))
	{
		$arResult['LAYOUT']['WORLD'] = $world;
		$arResult['LAYOUT'][''][] = array(
			'CODE' => 'WORLD',
			'PARENT_CODE' => '',
			'NAME' => array(
				ToUpper(LANGUAGE_ID) => array('NAME' => Loc::getMessage('SALE_SLI_WORLD_CATEGORY'))
			)
		);
	}
}