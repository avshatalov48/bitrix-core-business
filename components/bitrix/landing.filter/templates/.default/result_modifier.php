<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

$arResult['FILTER'] = [];

// rewrite filter fields for adding titles
foreach ($arResult['FILTER_FIELDS'] as $code => $field)
{
	$field['name'] = Loc::getMessage('LANDING_TPL_FLT_' . $code);
	if (!$field['name'])
	{
		$field['name'] = $field['id'];
	}
	if ($field['type'] == 'checkbox')
	{
		$field['type'] = 'list';
		$field['items'] = [
			'Y' => Loc::getMessage('LANDING_TPL_FLT_Y'),
			'N' => Loc::getMessage('LANDING_TPL_FLT_N')
		];
	}
	else if ($field['type'] == 'list')
	{
		if (!isset($field['items']))
		{
			$field['items'] = [];
		}
		if (!is_array($field['items']))
		{
			$field['items'] = (array) $field['items'];
		}
		$field['items'] = array_flip($field['items']);
		foreach ($field['items'] as $key => &$title)
		{
			$title = Loc::getMessage('LANDING_TPL_FLT_'.$code.'_'.mb_strtoupper($key));
			if (!$title)
			{
				$title = $key;
			}
		}
		unset($key, $title);
	}
	$arResult['FILTER'][$code] = $field;
}
unset($code, $field);

// rewrite filter fields for adding titles
foreach ($arResult['FILTER_PRESETS'] as $code => &$field)
{
	$name = Loc::getMessage('LANDING_TPL_PRS_'.mb_strtoupper($code));
	if (!$name)
	{
		$name = $code;
	}

	$field = [
		'name' => $name,
		'default' => isset($field['default'])
					? $field['default']
					: false,
		'fields' => $field['fields']
	];

	unset($name);
}
unset($code, $field);