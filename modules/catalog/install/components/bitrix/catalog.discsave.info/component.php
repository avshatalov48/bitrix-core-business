<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Catalog;
use Bitrix\Sale;

global $APPLICATION;
global $USER;

$arParams['SITE_ID'] = (string)($arParams['SITE_ID'] ?? '');
if ($arParams['SITE_ID'] === '')
{
	$arParams['SITE_ID'] = SITE_ID;
}

$arParams['USER_ID'] = (int)($arParams['USER_ID'] ?? 0);
if ($arParams['USER_ID'] <= 0)
{
	$arParams['USER_ID'] = (int)$USER->GetID();
}

if ($arParams['USER_ID'] <= 0)
{
	return;
}

$arParams['SHOW_NEXT_LEVEL'] = ($arParams['SHOW_NEXT_LEVEL'] ?? 'Y') === 'Y' ? 'Y' : 'N';

if (!Loader::includeModule('catalog'))
{
	return;
}

if (!Catalog\Config\Feature::isCumulativeDiscountsEnabled())
{
	CCatalogDiscountSave::Disable();
	ShowError(GetMessage('CAT_FEATURE_NOT_ALLOW'));
	return;
}
$arFields = [
	'USER_ID' => $arParams['USER_ID'],
	'SITE_ID' => $arParams['SITE_ID'],
];

$onlySaleDiscount = Loader::includeModule('sale') && Option::get('sale', 'use_sale_discount_only') === 'Y';
if ($onlySaleDiscount)
{
	$manager = Sale\Discount\Preset\Manager::getInstance();
	$manager->registerAutoLoader();
	$cumulativePreset = $manager->getPresetById('Sale\Handlers\DiscountPreset\Cumulative');

	if ($cumulativePreset)
	{
		$userGroups = CUser::getUserGroup($arFields['USER_ID']);
		\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($userGroups);

		$cumulativeDiscounts = Sale\Internals\DiscountTable::getList([
			'filter' => [
				'=ACTIVE' => 'Y',
				'=PRESET_ID' => \Sale\Handlers\DiscountPreset\Cumulative::className(),
				'=\Bitrix\Sale\Internals\DiscountGroupTable:DISCOUNT.ACTIVE' => 'Y',
				'@\Bitrix\Sale\Internals\DiscountGroupTable:DISCOUNT.GROUP_ID' => $userGroups,
			],
			'data_doubling' => false,
		]);

		$currency = Sale\Internals\SiteCurrencyTable::getSiteCurrency($arParams['SITE_ID']);
		$cumulativeCalculator = new Sale\Discount\CumulativeCalculator(
			$arFields['USER_ID'],
			$arFields['SITE_ID']
		);
		$arResult = [];
		foreach ($cumulativeDiscounts as $cumulativeDiscount)
		{
			$state = $cumulativePreset->generateState($cumulativeDiscount);
			$cumulativeCalculator->setSumConfiguration([
				'type_sum_period' => $state['discount_type_sum_period'],
				'sum_period_data' => [
					'order_start' => $state->get(
						'discount_sum_order_start',
						function ($value)
						{
							return $value ? makeTimeStamp($value) : null;
						}
					),
					'order_end' => $state->get(
						'discount_sum_order_end',
						function ($value)
						{
							return $value ? makeTimeStamp($value) : null;
						}
					),
					'period_value' => $state['discount_sum_period_value'],
					'period_type' => $state['discount_sum_period_type'],
				],
			]);
			$cumulativeOrderUserValue = $cumulativeCalculator->calculate();

			$rangeToApply = null;
			$indexRangeToApply = null;
			$nextRangeToApply = null;
			$ranges = $state->get('discount_ranges', []);
			if (empty($ranges))
			{
				continue;
			}
			foreach ($ranges as $index => $range)
			{
				if ($cumulativeOrderUserValue >= $range['sum'])
				{
					$rangeToApply = $range;
					$indexRangeToApply = $index;
				}
			}
			if ($indexRangeToApply === null)
			{
				$rangeToApply = [
					'type' => Catalog\DiscountTable::VALUE_TYPE_PERCENT,
					'value' => 0,
				];
				$indexNextRangeToApply = 0;
			}
			else
			{
				$indexNextRangeToApply = $indexRangeToApply + 1;
			}
			if (isset($ranges[$indexNextRangeToApply]))
			{
				$nextRangeToApply = $ranges[$indexNextRangeToApply];
			}

			$arResult[] = [
				'~NAME' => $cumulativeDiscount['NAME'],
				'NAME' => htmlspecialcharsbx($cumulativeDiscount['NAME']),
				'VALUE_TYPE' => $rangeToApply['type'],
				'VALUE' => $rangeToApply['value'],
				'CURRENCY' => $currency,
				'NEXT_LEVEL' =>
					$nextRangeToApply
						? [
						'VALUE_TYPE' => $nextRangeToApply['type'],
						'VALUE' => $nextRangeToApply['value'],
						'RANGE_FROM' => $nextRangeToApply['sum'],
					]
						: null
				,
				'RANGE_SUMM' => $cumulativeOrderUserValue,
			];
		}
	}
}
else
{
	$arResult = CCatalogDiscountSave::GetDiscount($arFields);
	if (!empty($arResult))
	{
		foreach ($arResult as $key => $arDiscountSave)
		{
			if ($arParams['SHOW_NEXT_LEVEL'] === 'Y')
			{
				$rsRanges = CCatalogDiscountSave::GetRangeByDiscount(
					[
						'RANGE_FROM' => 'ASC',
					],
					[
						'DISCOUNT_ID' => $arDiscountSave['ID'],
						'>RANGE_FROM' => $arDiscountSave['RANGE_FROM'],
					],
					false,
					[
						'nTopCount' => 1,
					]
				);
				$arRange = $rsRanges->Fetch();
				unset($rsRange);
				if ($arRange)
				{
					$arDiscountSave['NEXT_LEVEL'] = [
						'RANGE_FROM' => $arRange['RANGE_FROM'],
						'VALUE' => $arRange['VALUE'],
						'VALUE_TYPE' => $arRange['TYPE'],
					];
				}
				unset($arRange);
			}
			$arDiscountSave['~NAME'] = $arDiscountSave['NAME'];
			$arDiscountSave['NAME'] = htmlspecialcharsbx($arDiscountSave['NAME']);
			$arResult[$key] = $arDiscountSave;
		}
	}
}

$this->IncludeComponentTemplate();
