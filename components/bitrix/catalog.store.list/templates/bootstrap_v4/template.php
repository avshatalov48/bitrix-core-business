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
if($arResult["ERROR_MESSAGE"] <> '')
	ShowError($arResult["ERROR_MESSAGE"]);
$arPlacemarks = array();
$gpsN = '';
$gpsS = '';
?>
<? if(is_array($arResult["STORES"]) && !empty($arResult["STORES"])){?>
	<?foreach($arResult["STORES"] as $pid=>$arProperty):?>
		<div class="row mb-4">
			<div class="col">
				<div class="mb-2">
					<a class="catalog-stores-item-title-link" href="<?=$arProperty["URL"]?>"><?=$arProperty["TITLE"]?></a>
				</div>
				<div class="row">
					<? if($arProperty["DESCRIPTION"] != ''):?>
						<div class="col-sm">
							<p><?=$arProperty["DESCRIPTION"]?></p>
						</div>
					<?endif;?>
					<? if(isset($arProperty["DETAIL_IMG"]["SRC"]) || isset($arProperty["PHONE"]) || isset($arProperty["SCHEDULE"])) { ?>
						<div class="col-sm-auto catalog-stores-item-info">
							<? if(isset($arProperty["DETAIL_IMG"]["SRC"])):?>
								<div class="mb-2">
									<img class="catalog-store-item-image" src="<?=$arProperty["DETAIL_IMG"]["SRC"]?>">
								</div>
							<?endif;?>
							<? if(isset($arProperty["PHONE"]) && $arProperty["PHONE"] != ''):?>
								<div class="mb-2" itemprop="telephone">
									<div class="text-muted"><?=$arProperty["PHONE"]?></div>
								</div>
							<?endif;?>
							<? if(isset($arProperty["SCHEDULE"]) && $arProperty["PHONE"] != ''):?>
								<div class="mb-2">
									<div class="text-muted"><?=$arProperty["SCHEDULE"]?></div>
								</div>
							<?endif;?>
						</div>
					<? } ?>
				</div>
			</div>
		</div>
	<?endforeach;?>
<? } ?>
<div class="row">
	<div class="col">
		<? if($arProperty["GPS_S"]!=0 && $arProperty["GPS_N"]!=0)
		{
			$gpsN = mb_substr(doubleval($arProperty["GPS_N"]), 0, 15);
			$gpsS = mb_substr(doubleval($arProperty["GPS_S"]), 0, 15);
			$arPlacemarks[]=array("LON"=>$gpsS,"LAT"=>$gpsN,"TEXT"=>$arProperty["TITLE"]);
		}
		if ($arResult['VIEW_MAP'])
		{
			if($arResult["MAP"]==0)
			{
				$APPLICATION->IncludeComponent("bitrix:map.yandex.view", ".default", array(
						"INIT_MAP_TYPE" => "MAP",
						"MAP_DATA" => serialize(array("yandex_lat"=>$gpsN,"yandex_lon"=>$gpsS,"yandex_scale"=>10,"PLACEMARKS" => $arPlacemarks)),
						"MAP_WIDTH" => "100%",
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
						"MAP_WIDTH" => "100%",
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
		?>
	</div>
</div>