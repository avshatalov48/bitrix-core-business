<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$defaultValues = array(
	'USE_PAGE_SIZE' => 'N',
	'PAGE_SIZE_FROM_LINE_COUNT' => 'N',
	'PAGE_SIZES' => '5,10,15,20,25,30,40,50,100',
	'LINE_COUNT' => '3',
	'MIN_RATIO_LINE_COUNT' => '1',
	'MAX_RATIO_LINE_COUNT' => '10'
);

$arParams = array_merge($defaultValues, $arParams);

$arParams['USE_PAGE_SIZE'] = ($arParams['USE_PAGE_SIZE'] != 'Y' ? 'N' : 'Y');
$arParams['PAGE_SIZE_FROM_LINE_COUNT'] = ($arParams['PAGE_SIZE_FROM_LINE_COUNT'] != 'Y' ? 'N' : 'Y');

$arResult['TPL_DATA'] = array();
$arResult['TPL_DATA']['PAGE_SIZES'] = array();
if ($arParams['USE_PAGE_SIZE'] === 'Y')
{
	if ($arParams['PAGE_SIZE_FROM_LINE_COUNT'] === 'Y')
	{
		$arParams['LINE_COUNT'] = (int)$arParams['LINE_COUNT'];
		if ($arParams['LINE_COUNT'] <= 0)
			$arParams['LINE_COUNT'] = (int)$defaultValues['LINE_COUNT'];
		$arParams['MIN_RATIO_LINE_COUNT'] = (int)$arParams['MIN_RATIO_LINE_COUNT'];
		if ($arParams['MIN_RATIO_LINE_COUNT'] <= 0)
			$arParams['MIN_RATIO_LINE_COUNT'] = (int)$defaultValues['MIN_RATIO_LINE_COUNT'];
		if ($arParams['LINE_COUNT'] == 1 && $arParams['MIN_RATIO_LINE_COUNT'] < 3)
			$arParams['MIN_RATIO_LINE_COUNT'] = 3;
		$arParams['MAX_RATIO_LINE_COUNT'] = (int)$arParams['MAX_RATIO_LINE_COUNT'];
		if ($arParams['MAX_RATIO_LINE_COUNT'] <= 1)
			$arParams['MAX_RATIO_LINE_COUNT'] = (int)$defaultValues['MAX_RATIO_LINE_COUNT'];
		if ($arParams['MAX_RATIO_LINE_COUNT'] <= $arParams['MIN_RATIO_LINE_COUNT'])
			$arParams['MAX_RATIO_LINE_COUNT'] = $arParams['MIN_RATIO_LINE_COUNT']+1;
		for ($i = $arParams['MIN_RATIO_LINE_COUNT']; $i <= $arParams['MAX_RATIO_LINE_COUNT']; $i++)
		{
			$arResult['TPL_DATA']['PAGE_SIZES'][] = $i*$arParams['LINE_COUNT'];
		}
	}
	else
	{
		$arParams['PAGE_SIZES'] = str_replace(' ', '', trim($arParams['PAGE_SIZES']));
		if ($arParams['PAGE_SIZES'] == '')
			$arParams['PAGE_SIZES'] = $defaultValues['PAGE_SIZES'];
		$arParams['PAGE_SIZES'] = explode(',', $arParams['PAGE_SIZES']);

		foreach ($arParams['PAGE_SIZES'] as &$value)
		{
			$value = (int)$value;
			if ($value > 0)
				$arResult['TPL_DATA']['PAGE_SIZES'][] = $value;
		}
		if (isset($value))
			unset($value);
		if (empty($arResult['TPL_DATA']['PAGE_SIZES']))
			$arResult['TPL_DATA']['PAGE_SIZES'] = explode(',', $defaultValues['PAGE_SIZES']);
		sort($arResult['TPL_DATA']['PAGE_SIZES'], SORT_NUMERIC);
	}
}
?>