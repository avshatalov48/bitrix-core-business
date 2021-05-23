<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (is_array($arResult['VALUE']) && count($arResult['VALUE']) > 0)
{
	if(!CModule::IncludeModule("iblock"))
		return;

	$arValue = array();
	$dbRes = CIBlockElement::GetList(array('SORT' => 'DESC', 'NAME'=>'ASC'), array('ID' => $arResult['VALUE']), false);
	while ($arRes = $dbRes->GetNext())
	{
		$arValue[$arRes['ID']] = $arRes['NAME'];
	}
	$arResult['VALUE'] = $arValue;

}

?>