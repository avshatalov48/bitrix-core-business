<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="catalog-detail" itemscope itemtype = "http://schema.org/Product">

	<table class="catalog-detail" cellspacing="0">
		<tr>
			<?
			if(intval($arResult["IMAGE_ID"]) > 0)
			{
				?>
				<td class="catalog-detail-image">
					<div class="catalog-detail-image" id="catalog-detail-main-image">
						<?echo CFile::ShowImage($arResult["IMAGE_ID"], 250, 200, "border=0", "", true);?>
					</div>
				</td>
				<?
			}
			?>
			<?
			if(isset($arResult["LIST_URL"]))
			{
				?>
				<div class="catalog-item-links">
					<a href="<?=$arResult["LIST_URL"]?>"><?=GetMessage("BACK_STORE_LIST")?>  </a>
				</div>
				<?
			}
			?>

			<td class="catalog-detail-desc">
				<?if($arResult["TITLE"]):?>
				<span itemprop = "description"><?=GetMessage("S_NAME")." ".$arResult["TITLE"];?></span>
				<div class="catalog-detail-line"></div>
				<?endif;?>
				<?if($arResult["DESCRIPTION"]):?>
				<span itemprop = "description"><?=$arResult["DESCRIPTION"];?></span>
				<div class="catalog-detail-line"></div>
				<?endif;?>
				<?if($arResult["ADDRESS"]):?>
				<span itemprop = "description"><?=GetMessage("S_ADDRESS")." ".$arResult["ADDRESS"];?></span>
				<div class="catalog-detail-line"></div>
				<?endif;?>
				<?if($arResult["PHONE"] != ''):?>
				<span itemprop = "description"><?=GetMessage("S_PHONE")." ".$arResult["PHONE"];?></span>
				<div class="catalog-detail-line"></div>
				<?endif;?>
				<?if ($arResult["SCHEDULE"] != ''):?>
				<span itemprop = "description"><?=GetMessage("S_SCHEDULE")." ".$arResult["SCHEDULE"];?></span>
				<div class="catalog-detail-line"></div>
				<?endif;?>
			</td>
		</tr>
	</table>
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
					false
				);
			}
			else
			{
				$APPLICATION->IncludeComponent("bitrix:map.google.view", ".default", array(
						"INIT_MAP_TYPE" => "MAP",
						"MAP_DATA" => serialize(array("google_lat"=>$gpsN,"google_lon"=>$gpsS,"google_scale"=>11,"PLACEMARKS" => array( 0=>array("LON"=>$gpsS,"LAT"=>$gpsN,"TEXT"=>$arResult["ADDRESS"])))),
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
					false
				);
			}
		}
		?>
	</div>
</div>