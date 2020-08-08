<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['MAP_ID'] = 
	($arParams["MAP_ID"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MAP_ID"])) ?
	'MAP_'.$this->randString() : $arParams['MAP_ID'];

if (($strPositionInfo = $arParams['~MAP_DATA']) && CheckSerializedData($strPositionInfo) && ($arResult['POSITION'] = unserialize($strPositionInfo)))
{
	if (is_array($arResult['POSITION']) && is_array($arResult['POSITION']['PLACEMARKS']) && ($cnt = count($arResult['POSITION']['PLACEMARKS'])))
	{
		for ($i = 0; $i < $cnt; $i++)
		{
			$arResult['POSITION']['PLACEMARKS'][$i]['TEXT'] = str_replace('###RN###', "\r\n", $arResult['POSITION']['PLACEMARKS'][$i]['TEXT']);
		}
	}

}
else
{
	$arResult['POSITION'] = array(
		'google_lon' => 37.64,
		'google_lat' => 55.76,
		'google_scale' => 10,
	);
}

$this->IncludeComponentTemplate();
?>