<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arViewModeList = array(
	'LIST' => GetMessage('CPT_BCSL_VIEW_MODE_LIST'),
	'LINE' => GetMessage('CPT_BCSL_VIEW_MODE_LINE'),
	'TEXT' => GetMessage('CPT_BCSL_VIEW_MODE_TEXT'),
	'TILE' => GetMessage('CPT_BCSL_VIEW_MODE_TILE')
);

$arTemplateParameters = array(
	'VIEW_MODE' => array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CPT_BCSL_VIEW_MODE'),
		'TYPE' => 'LIST',
		'VALUES' => $arViewModeList,
		'MULTIPLE' => 'N',
		'DEFAULT' => 'LINE',
		'REFRESH' => 'Y'
	),
	'SHOW_PARENT_NAME' => array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CPT_BCSL_SHOW_PARENT_NAME'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y'
	),
);

if (isset($arCurrentValues['VIEW_MODE']) && $arCurrentValues['VIEW_MODE'] == 'TILE')
{
	$arTemplateParameters['HIDE_SECTION_NAME'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CPT_BCSL_HIDE_SECTION_NAME'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N'
	);
}

if (
	isset($arCurrentValues['VIEW_MODE'])
	&& ($arCurrentValues['VIEW_MODE'] == 'TEXT' || $arCurrentValues['VIEW_MODE'] == 'TILE')
)
{
	$arTemplateParameters['LIST_COLUMNS_COUNT'] = array(
		'PARENT' => 'VISUAL',
		'NAME' => GetMessage('CPT_BCSL_LIST_COLUMNS_COUNT'),
		'TYPE' => 'LIST',
		'VALUES' => array(
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'6' => '6',
			'12' => '12'
		),
		'DEFAULT' => '6'
	);
}