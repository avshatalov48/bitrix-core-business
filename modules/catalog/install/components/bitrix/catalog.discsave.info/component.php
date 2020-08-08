<?

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Catalog;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;
global $USER;

$arParams['SITE_ID'] = strval($arParams['SITE_ID']);
if ($arParams['SITE_ID'] == '')
	$arParams['SITE_ID'] = SITE_ID;

$arParams['USER_ID'] = intval($arParams['USER_ID']);
if (0 >= $arParams['USER_ID'])
	$arParams['USER_ID'] = intval($USER->GetID());

if (0 >= $arParams['USER_ID'])
	return;

$arParams['SHOW_NEXT_LEVEL'] = (isset($arParams['SHOW_NEXT_LEVEL']) && 'Y' == $arParams['SHOW_NEXT_LEVEL'] ? 'Y' : 'N');

if (!CModule::IncludeModule('catalog'))
	return;

if (!Catalog\Config\Feature::isCumulativeDiscountsEnabled())
{
	CCatalogDiscountSave::Disable();
	ShowError(GetMessage("CAT_FEATURE_NOT_ALLOW"));
	return;
}
$arFields = array(
	'USER_ID' => $arParams['USER_ID'],
	'SITE_ID' => $arParams['SITE_ID'],
);

$onlySaleDiscount = Loader::includeModule('sale') && Option::get('sale', 'use_sale_discount_only') === 'Y';
$cumulativePreset = null;
if ($onlySaleDiscount && class_exists('\Bitrix\Sale\Discount\Preset\Manager'))
{
	$manager = \Bitrix\Sale\Discount\Preset\Manager::getInstance();
	$manager->registerAutoLoader();
	$cumulativePreset = $manager->getPresetById('Sale\Handlers\DiscountPreset\Cumulative');
}

if ($onlySaleDiscount && $cumulativePreset)
{
	$userGroups = \CUser::getUserGroup($arFields['USER_ID']);
	\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($userGroups);

	$cumulativeDiscounts = \Bitrix\Sale\Internals\DiscountTable::getList(
		array(
			'filter' => array(
				'=ACTIVE' => 'Y',
				'=PRESET_ID' => \Sale\Handlers\DiscountPreset\Cumulative::className(),
				'=\Bitrix\Sale\Internals\DiscountGroupTable:DISCOUNT.ACTIVE' => 'Y',
				'@\Bitrix\Sale\Internals\DiscountGroupTable:DISCOUNT.GROUP_ID' => $userGroups,
			),
			'data_doubling' => false
		)
	);

	$currency = \CSaleLang::getLangCurrency($arParams['SITE_ID']);
	$cumulativeCalculator = new \Bitrix\Sale\Discount\CumulativeCalculator($arFields['USER_ID'], $arFields['SITE_ID']);
	$arResult = array();
	foreach ($cumulativeDiscounts as $cumulativeDiscount)
	{
		$state = $cumulativePreset->generateState($cumulativeDiscount);
		$cumulativeCalculator->setSumConfiguration(
			array(
				'type_sum_period' => $state['discount_type_sum_period'],
				'sum_period_data' => array(
					'order_start' => $state->get('discount_sum_order_start', function($value){
						return $value? makeTimeStamp($value) : null;
					}),
					'order_end' => $state->get('discount_sum_order_end', function($value){
						return $value? makeTimeStamp($value) : null;
					}),
					'period_value' => $state['discount_sum_period_value'],
					'period_type' => $state['discount_sum_period_type'],
				),
			)
		);
		$cumulativeOrderUserValue = $cumulativeCalculator->calculate();

		$rangeToApply = null;
		$nextRangeToApply = null;
		$ranges = $state->get('discount_ranges', array());
		foreach ($ranges as $range)
		{
			if ($cumulativeOrderUserValue >= $range['sum'])
			{
				$rangeToApply = $range;
			}
			if (!$nextRangeToApply && $rangeToApply && $range['sum'] > $rangeToApply['sum'])
			{
				$nextRangeToApply = $range;
			}
		}

		$arResult[] = array(
			'NAME' => htmlspecialcharsbx($cumulativeDiscount['NAME']),
			'VALUE_TYPE' => $rangeToApply['type'],
			'VALUE' => $rangeToApply['value'],
			'CURRENCY' => $currency,
			'NEXT_LEVEL' => $nextRangeToApply? array(
				'VALUE_TYPE' => $nextRangeToApply['type'],
				'VALUE' => $nextRangeToApply['value'],
				'RANGE_FROM' => $nextRangeToApply['sum'],
			) : null,
			'RANGE_SUMM' => $cumulativeOrderUserValue,
		);
	}
}
else
{
	$arResult = CCatalogDiscountSave::GetDiscount($arFields);
	if (!empty($arResult))
	{
		foreach ($arResult as $key => $arDiscountSave)
		{
			if ('Y' == $arParams['SHOW_NEXT_LEVEL'])
			{
				$rsRanges = CCatalogDiscountSave::GetRangeByDiscount(array('RANGE_FROM' => 'ASC'), array('DISCOUNT_ID' => $arDiscountSave['ID'], '>RANGE_FROM' => $arDiscountSave['RANGE_FROM'], false, array('nTopCount' => 1)));
				if ($arRange = $rsRanges->Fetch())
				{
					$arTempo = array(
						'RANGE_FROM' => $arRange['RANGE_FROM'],
						'VALUE' => $arRange['VALUE'],
						'VALUE_TYPE' => $arRange['TYPE']
					);
					$arDiscountSave['NEXT_LEVEL'] = $arTempo;
				}
			}
			$arDiscountSave['~NAME'] = $arDiscountSave['NAME'];
			$arDiscountSave['NAME'] = htmlspecialcharsex($arDiscountSave['NAME']);
			$arResult[$key] = $arDiscountSave;
		}
	}
}

$this->IncludeComponentTemplate();
?>