<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$this->setFrameMode(true);

if(($arParams['BX_EDITOR_RENDER_MODE'] ?? null) == 'Y')
{
	echo '<img src="/bitrix/components/bitrix/map.google.view/templates/.default/images/preview.png" border="0" />';
}
else
{
	$arTransParams = array(
		'INIT_MAP_TYPE' => $arParams['INIT_MAP_TYPE'] ?? null,
		'INIT_MAP_LON' => $arResult['POSITION']['google_lon'] ?? null,
		'INIT_MAP_LAT' => $arResult['POSITION']['google_lat'] ?? null,
		'INIT_MAP_SCALE' => $arResult['POSITION']['google_scale'] ?? null,
		'MAP_WIDTH' => $arParams['MAP_WIDTH'] ?? null,
		'MAP_HEIGHT' => $arParams['MAP_HEIGHT'] ?? null,
		'CONTROLS' => $arParams['CONTROLS'] ?? null,
		'OPTIONS' => $arParams['OPTIONS'] ?? null,
		'MAP_ID' => $arParams['MAP_ID'] ?? null,
		'API_KEY' => $arParams['API_KEY'] ?? null,
	);

	if (($arParams['DEV_MODE'] ?? null) == 'Y')
	{
		$arTransParams['DEV_MODE'] = 'Y';
		if ($arParams['WAIT_FOR_EVENT'] ?? false)
		{
			$arTransParams['WAIT_FOR_EVENT'] = $arParams['WAIT_FOR_EVENT'];
		}
	}
	?>
	<div class="bx-yandex-view-layout">
		<div class="bx-yandex-view-map">
	<?

	$APPLICATION->IncludeComponent('bitrix:map.google.system', '.default', $arTransParams, false, array('HIDE_ICONS' => 'Y'));
	?>
		</div>
	</div>
	<?if (is_array($arResult['POSITION']['PLACEMARKS'] ?? null) && ($cnt = count($arResult['POSITION']['PLACEMARKS']))):?>
	<script>

	function BX_SetPlacemarks_<?echo $arParams['MAP_ID']?>()
	{
	<?
		for($i = 0; $i < $cnt; $i++):
	?>
		BX_GMapAddPlacemark(<?echo CUtil::PhpToJsObject($arResult['POSITION']['PLACEMARKS'][$i])?>, '<?echo $arParams['MAP_ID']?>');
	<?
		endfor;
	?>
	}

	function BXShowMap_<?echo $arParams['MAP_ID']?>() {
		if(typeof window["BXWaitForMap_view"] == 'function')
		{
			BXWaitForMap_view('<?echo $arParams['MAP_ID']?>');
		}
		else
		{
			/* If component's result was cached as html,
			 * script.js will not been loaded next time.
			 * let's do it manualy.
			*/

			(function(d, s, id)
			{
				var js, bx_gm = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "<?=$templateFolder.'/script.js'?>";
				bx_gm.parentNode.insertBefore(js, bx_gm);
			}(document, 'script', 'bx-google-map-js'));

			var gmWaitIntervalId = setInterval( function(){

					if(typeof window["BXWaitForMap_view"] == 'function')
					{
						BXWaitForMap_view("<?echo $arParams['MAP_ID']?>");
						clearInterval(gmWaitIntervalId);
					}
				}, 300
			);
		}
	}

	BX.ready(BXShowMap_<?echo $arParams['MAP_ID']?>);
	</script>
	<?endif;

}
?>