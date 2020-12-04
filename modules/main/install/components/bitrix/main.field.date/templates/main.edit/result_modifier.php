<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserField\Types\DateType;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var $component DateUfComponent
 */

$component = $this->getComponent();

CJSCore::init(['uf', 'date']);

$attrList = [];

if($arResult['userField']['EDIT_IN_LIST'] !== 'Y')
{
	$attrList['readonly'] = 'readonly';
}
else
{
	$attrList['onclick'] = 'BX.calendar({node: this, field: this, bTime: false, bSetFocus: false})';
}

if(array_key_exists('attribute', $arResult['additionalParameters']))
{
	$attrList = array_merge($attrList, $arResult['additionalParameters']['attribute']);
}

if(isset($attrList['class']) && is_array($attrList['class']))
{
	$attrList['class'] = implode(' ', $attrList['class']);
}

$attrList['name'] = $arResult['fieldName'];

$attrList['type'] = 'text';
$attrList['tabindex'] = '0';

foreach($arResult['value'] as $key => $value)
{
	$attrList['value'] = HtmlFilter::encode($value);
	$arResult['value'][$key] = [
		'attrList' => $attrList,
		'value' => HtmlFilter::encode($value)
	];

	if($component->isMobileMode())
	{
		Asset::getInstance()->addJs(
			'/bitrix/js/mobile/userfield/mobile_field.js'
		);
		Asset::getInstance()->addJs(
			'/bitrix/components/bitrix/main.field.date/templates/main.view/mobile.js'
		);

		if(
			empty($arResult['additionalParameters']['DATE_TIME_FORMAT']) ||
			$arResult['additionalParameters']['DATE_TIME_FORMAT'] === DateType::FORMAT_TYPE_FULL
		)
		{
			$arResult['additionalParameters']['DATE_TIME_FORMAT'] =
				$DB::DateFormatToPHP(FORMAT_DATE);
		}

		$arResult['additionalParameters']['DATE_TIME_FORMAT'] =
			preg_replace(
				'/[\/.,\s:][s]/',
				'',
				$arResult['additionalParameters']['DATE_TIME_FORMAT']
			);

		if(!$arResult['additionalParameters']['TIME_FORMAT'])
		{
			$arResult['additionalParameters']['TIME_FORMAT'] =
				preg_replace(
					['/[dDjlFmMnYyo]/', '/^[\/.,\s]+/', '/[\/.,\s]+$/'],
					'',
					$arResult['additionalParameters']['DATE_TIME_FORMAT']
				);
		}

		if(!$arResult['additionalParameters']['DATE_FORMAT'])
		{
			$arResult['additionalParameters']['DATE_FORMAT'] =
				trim(
					str_replace(
						$arResult['additionalParameters']['TIME_FORMAT'],
						'',
						$arResult['additionalParameters']['DATE_TIME_FORMAT']
					)
				);
		}

		$type = $arResult['userField']['USER_TYPE_ID'];
		$format = $arResult['additionalParameters']['DATE_FORMAT'];

		if($value)
		{
			$value = FormatDate($format, MakeTimeStamp($value));
		}

		$attrList = [];
		$attrList['type'] = 'hidden';
		$attrList['data-bx-type'] = $type;
		$attrList['name'] = str_replace('[]', '[' . $key . ']', $arResult['fieldName']);
		$attrList['placeholder'] = HtmlFilter::encode(
			$arParams['userField']['placeholder'] ?: $arParams['userField']['EDIT_FORM_LABEL']
		);
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['value'] = $value;
		$attrList['data-user-field-type-name'] = 'BX.Mobile.Field.Date';

		$arResult['value'][$key]['attrList'] = $attrList;
	}
}