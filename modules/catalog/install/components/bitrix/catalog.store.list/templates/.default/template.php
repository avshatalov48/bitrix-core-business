<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
if(strlen($arResult["ERROR_MESSAGE"])>0)
	ShowError($arResult["ERROR_MESSAGE"]);
$arPlacemarks = array();
$gpsN = '';
$gpsS = '';
?>
<div class="catalog-detail-properties">
	<?if(is_array($arResult["STORES"]) && !empty($arResult["STORES"])):
	foreach($arResult["STORES"] as $pid=>$arProperty):?>
	<div class="catalog-detail-property">
		<p class="catalog-detail-properties-title"><a href="<?=$arProperty["URL"]?>"><?=$arProperty["TITLE"]?></a></p><?
		if (!empty($arProperty['DETAIL_IMG']))
		{
			?><img class="catalog-detail-image" src="<?=$arProperty['DETAIL_IMG']['SRC']; ?>"><?
		}
		if ($arProperty["DESCRIPTION"] != '')
		{
			?><p><?=$arProperty["DESCRIPTION"]; ?></p><?
		}
		if(isset($arProperty["PHONE"])):?>
		<span>&nbsp;&nbsp;<?=GetMessage('S_PHONE')?></span>
		<span><?=$arProperty["PHONE"]?></span>
		<?endif;
		if(isset($arProperty["SCHEDULE"])):?>
		<span>&nbsp;&nbsp;<?=GetMessage('S_SCHEDULE')?></span>
		<span><?=$arProperty["SCHEDULE"]?></span>
		<?endif;
		if($arProperty["GPS_S"]!=0 && $arProperty["GPS_N"]!=0)
		{
			$gpsN=substr(doubleval($arProperty["GPS_N"]),0,15);
			$gpsS=substr(doubleval($arProperty["GPS_S"]),0,15);
			$arPlacemarks[]=array("LON"=>$gpsS,"LAT"=>$gpsN,"TEXT"=>$arProperty["TITLE"]);
		}
		?>
	</div>
		<br>
	<?endforeach;
	endif;?>
</div><br><br>
<?
if ($arResult['VIEW_MAP'])
{
	if($arResult["MAP"]==0)
	{
		$APPLICATION->IncludeComponent("bitrix:map.yandex.view", ".default", array(
				"INIT_MAP_TYPE" => "MAP",
				"MAP_DATA" => serialize(array("yandex_lat"=>$gpsN,"yandex_lon"=>$gpsS,"yandex_scale"=>10,"PLACEMARKS" => $arPlacemarks)),
				"MAP_WIDTH" => "720",
				"MAP_HEIGHT" => "500",
				"CONTROLS" => array(
					0 => "ZOOM",
				),
				"OPTIONS" => array(
					0 => "ENABLE_SCROLL_ZOOM",
					1 => "ENABLE_DBLCLICK_ZOOM",
					2 => "ENABLE_DRAGGING",
				),
				"MAP_ID" => ""
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}
	else
	{
		$APPLICATION->IncludeComponent("bitrix:map.google.view", ".default", array(
				"INIT_MAP_TYPE" => "MAP",
				"MAP_DATA" => serialize(array("google_lat"=>$gpsN,"google_lon"=>$gpsS,"google_scale"=>10,"PLACEMARKS" => $arPlacemarks)),
				"MAP_WIDTH" => "720",
				"MAP_HEIGHT" => "500",
				"CONTROLS" => array(
					0 => "ZOOM",
				),
				"OPTIONS" => array(
					0 => "ENABLE_SCROLL_ZOOM",
					1 => "ENABLE_DBLCLICK_ZOOM",
					2 => "ENABLE_DRAGGING",
				),
				"MAP_ID" => ""
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}
}