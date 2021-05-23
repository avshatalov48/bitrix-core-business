<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<div class="row mb-4" itemscope itemtype="http://schema.org/LocalBusiness">
	<div class="col">
		<div class="mb-2">
			<? if(isset($arResult["LIST_URL"])):?>
				<div class="mb-2">
					<a itemprop="url" href="<?=$arResult["LIST_URL"]?>"><?=GetMessage("BACK_STORE_LIST")?></a>
				</div>
			<?endif;?>
			<?if($arResult["TITLE"]):?>
				<strong><?=$arResult["TITLE"];?></strong>
			<?endif;?>
		</div>
		<div class="row mb-3 align-items-start">
			<div class="col">
				<?if($arResult["DESCRIPTION"]):?>
					<p class="mb-4" itemprop="description"><?=$arResult["DESCRIPTION"]?></p>
				<?endif;?>
				<? if(isset($arResult["PHONE"]) && $arResult["PHONE"] != ''):?>
					<div class="mb-2" itemprop="telephone">
						<div class="text-dark"><?=GetMessage("S_PHONE")?></div>
						<div class="text-muted"><?=$arResult["PHONE"]?></div>
					</div>
				<?endif;?>
				<? if(isset($arResult["SCHEDULE"]) && $arResult["PHONE"] != ''):?>
					<div class="mb-2">
						<div class="text-dark"><?=GetMessage("SCHEDULE")?></div>
						<div class="text-muted"><?=$arResult["SCHEDULE"]?></div>
					</div>
				<?endif;?>
				<? if(isset($arResult["ADDRESS"]) && $arResult["ADDRESS"] != ''):?>
					<div class="mb-2" itemprop="address">
						<div class="text-dark"><?=GetMessage("S_ADDRESS")?></div>
						<div class="text-muted"><?=$arResult["ADDRESS"]?></div>
					</div>
				<?endif;?>
			</div>
			<? if(intval($arResult["IMAGE_ID"]) > 0):?>
				<div class="col-sm-auto catalog-stores-item-info">
					<div class="mb-2 catalog-store-item-image-container">
						<?echo CFile::ShowImage($arResult["IMAGE_ID"], 250, 200, "border=0", "", true);?>
					</div>
				</div>
			<?endif;?>
		</div>

		<div id="map" class="catalog-detail-recommend">
			<?
			if(($arResult["GPS_N"]) != 0 && ($arResult["GPS_S"]) != 0)
			{
				$gpsN = mb_substr($arResult["GPS_N"], 0, 15);
				$gpsS = mb_substr($arResult["GPS_S"], 0, 15);
				$gpsText = $arResult["ADDRESS"];
				$gpsTextLen = mb_strlen($arResult["ADDRESS"]);
				if($arResult["MAP"] == 0)
				{
					$APPLICATION->IncludeComponent("bitrix:map.yandex.view", ".default", array(
						"INIT_MAP_TYPE" => "MAP",
						"MAP_DATA" => serialize(array("yandex_lat"=>$gpsN,"yandex_lon"=>$gpsS,"yandex_scale"=>11,"PLACEMARKS" => array( 0=>array("LON"=>$gpsS,"LAT"=>$gpsN,"TEXT"=>$arResult["ADDRESS"])))),
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
												   false
					);
				}
				else
				{
					$APPLICATION->IncludeComponent("bitrix:map.google.view", ".default", array(
						"INIT_MAP_TYPE" => "MAP",
						"MAP_DATA" => serialize(array("google_lat"=>$gpsN,"google_lon"=>$gpsS,"google_scale"=>11,"PLACEMARKS" => array( 0=>array("LON"=>$gpsS,"LAT"=>$gpsN,"TEXT"=>$arResult["ADDRESS"])))),
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
												   false
					);
				}
			}
			?>
		</div>
	</div>
</div>

