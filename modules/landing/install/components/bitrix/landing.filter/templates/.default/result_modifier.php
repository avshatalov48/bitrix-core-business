<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

$arResult['FILTER'] = [];

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
	$arResult['FILTER'][$code] = $field;
}
