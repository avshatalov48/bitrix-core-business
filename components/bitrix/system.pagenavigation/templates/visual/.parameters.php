<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arTemplateParameters['USE_PAGE_SIZE'] = array(
	'NAME' => GetMessage('CP_SPN_TPL_USE_PAGE_SIZE'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y'
);

if (isset($arCurrentValues['USE_PAGE_SIZE']) && $arCurrentValues['USE_PAGE_SIZE'] == 'Y')
{
	$arTemplateParameters['PAGE_SIZE_FROM_LINE_COUNT'] = array(
		'NAME' => GetMessage('CP_SPN_TPL_PAGE_SIZE_FROM_LINE_COUNT'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y'
	);

	if (!isset($arCurrentValues['PAGE_SIZE_FROM_LINE_COUNT']) || $arCurrentValues['PAGE_SIZE_FROM_LINE_COUNT'] == 'N')
	{
		$arTemplateParameters['PAGE_SIZES'] = array(
			'NAME' => GetMessage('CP_SPN_TPL_PAGE_SIZES'),
			'TYPE' => 'STRING',
			'DEFAULT' => '5,10,15,20,25,30,40,50,100'
		);
	}
	else
	{
		$arTemplateParameters['LINE_COUNT'] = array(
			'NAME' => GetMessage('CP_SPN_TPL_LINE_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '3'
		);
		$arTemplateParameters['MIN_RATIO_LINE_COUNT'] = array(
			'NAME' => GetMessage('CP_SPN_TPL_MIN_RATIO_LINE_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '1',
		);
		$arTemplateParameters['MAX_RATIO_LINE_COUNT'] = array(
			'NAME' => GetMessage('CP_SPN_TPL_MAX_RATIO_LINE_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '10',
		);
	}
}
?>