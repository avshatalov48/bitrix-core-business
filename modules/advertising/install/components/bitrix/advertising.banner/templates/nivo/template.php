<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (count($arResult['BANNERS']) > 0):?>
<?
	global $APPLICATION;
	$this->addExternalCss("/bitrix/components/bitrix/advertising.banner/templates/nivo/nivo-slider.css");
	$this->addExternalCss("/bitrix/components/bitrix/advertising.banner/templates/nivo/themes/default/default.css");
	$this->addExternalCss("/bitrix/themes/.default/banner.css");
	$this->addExternalJs("/bitrix/components/bitrix/advertising.banner/templates/nivo/jquery.nivo.slider.pack.js");

	$arParams['WIDTH'] = intval($arResult['SIZE']['WIDTH']);
	$arParams['HEIGHT'] = intval($arResult['SIZE']['HEIGHT']);
	$arParams['EFFECT'] = isset($arParams['EFFECT']) ? htmlspecialcharsbx($arParams['EFFECT']) : 'random';
	$arParams['SLICES'] = intval($arParams['SLICES']);
	$arParams['SPEED'] = isset($arParams['SPEED']) ? intval($arParams['SPEED']) : 500;
	$arParams['INTERVAL'] = isset($arParams['INTERVAL']) ? intval($arParams['INTERVAL']) : 5000;
	$arParams['DIRECTION_NAV'] = $arParams['DIRECTION_NAV'] == 'Y' || $arParams['PREVIEW'] == 'Y' ? 'true' : 'false';
	$arParams['CONTROL_NAV'] = $arParams['CONTROL_NAV'] == 'Y' ? 'true' : 'false';
	$arParams['PAUSE'] = $arParams['PAUSE'] == 'Y' ? 'true' : 'false';
	$arParams['CYCLING'] = $arParams['CYCLING'] == 'Y' ? 'false' : 'true';

	$frame = $this->createFrame()->begin("");
?>
<?if ($arParams['PREVIEW'] == 'Y'):?>
	<div id='tPreview' style="display:none;margin:auto">
<?endif;?>

<div class="slider-wrapper theme-default">
	<div id="slider-<?=$arResult['ID']?>" class="nivoSlider">
		<?foreach($arResult["BANNERS"] as $k => $banner):?>
			<?=$banner?>
		<?endforeach;?>
	</div>
</div>

<script>
	$(window).load(function() {
		$('#slider-<?=$arResult['ID']?>').nivoSlider({
			effect: '<?=$arParams['EFFECT']?>',
			slices: 15, // For slice animations
			boxCols: 8, // For box animations
			boxRows: 4, // For box animations
			animSpeed: <?=$arParams['SPEED']?>,
			pauseTime: <?=$arParams['INTERVAL']?>,
			directionNav: <?=$arParams['DIRECTION_NAV']?>,
			controlNav: <?=$arParams['CONTROL_NAV']?>,
			pauseOnHover: <?=$arParams['PAUSE']?>,
			manualAdvance: <?=$arParams['CYCLING']?>,
			beforeChange: function(){},
			afterChange: function(){},
			slideshowEnd: function(){},
			lastSlide: function(){},
			afterLoad: function(){}
		});
	});
</script>

<?if ($arParams['PREVIEW'] == 'Y'):?>
	</div>
	<script>
		(function(){
			if(top.cWidth/2 > <?=$arParams['WIDTH']?>)
			{
				BX('tPreview').style.width = '<?=$arParams['WIDTH']?>px';
				BX('tPreview').style.height = '<?=$arParams['HEIGHT']?>px';
			}
			else
			{
				BX('tPreview').style.width = top.cWidth/2 + 'px';
				BX('tPreview').style.height = top.cWidth/3.55 + 'px';
			}
			BX('tPreview').style.display = '';
		})();
	</script>
<?endif;?>

<?$frame->end();?>

<?endif;?>