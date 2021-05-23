<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\DateType;
use Bitrix\Main\UserField\Types\DateTimeType;
use Bitrix\Main\Page\Asset;

/**
 * @var $component DateUfComponent
 */

$component = $this->getComponent();

CJSCore::init(['uf']);

$i = 0;

foreach($arResult['value'] as $key => $value)
{
	$arResult['value'][$key] = [
		'value' => $value
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
			empty($arResult['additionalParameters']['DATE_TIME_FORMAT'])
			||
			$arResult['additionalParameters']['DATE_TIME_FORMAT'] === DateType::TYPE_FIXED
		)
		{
			$arResult['additionalParameters']['DATE_TIME_FORMAT'] =
				$DB::DateFormatToPHP(FORMAT_DATETIME);
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
		$format = ($type === DateTimeType::USER_TYPE_ID ?
			$arResult['additionalParameters']['DATE_TIME_FORMAT'] :
			($type === DateType::USER_TYPE_ID ?
				$arResult['additionalParameters']['DATE_FORMAT'] :
				$arResult['additionalParameters']['TIME_FORMAT']
			)
		);

		if($value)
		{
			$value = FormatDate($format, MakeTimeStamp($value));
		}

		$attrList = [];
		$attrList['type'] = 'hidden';
		$attrList['data-bx-type'] = $type;
		$attrList['name'] = $arResult['fieldName'];
		$attrList['placeholder'] = htmlspecialcharsbx(
			$arParams['userField']['placeholder'] ?: $arParams['userField']['name']
		);
		$attrList['id'] = $arParams['userField']['~id'] . '_' . $i++;
		$attrList['value'] = $value;
		$arResult['value'][$key]['attrList'] = $attrList;
	}
}