<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$arResult["isIntranetInstalled"] = \Bitrix\Main\ModuleManager::isModuleInstalled('intranet');

if (
	$arResult["isIntranetInstalled"]
	&& empty($arResult['TOP_RATING_DATA'])
)
{
	$ratingData = \CRatings::getEntityRatingData(array(
		'entityTypeId' => "IBLOCK_ELEMENT",
		'entityId' => array($arResult['ELEMENT']['ID']),
	));

	if (
		!empty($ratingData)
		&& !empty($ratingData[$arResult['ELEMENT']['ID']])
	)
	{
		$arResult['TOP_RATING_DATA'] = $ratingData[$arResult['ELEMENT']['ID']];
	}
}

