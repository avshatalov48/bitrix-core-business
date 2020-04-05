<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (count($arResult['BANNERS']) > 0):?>

<?
	$this->addExternalCss("/bitrix/themes/.default/banner.css");
	$this->addExternalJS("/bitrix/components/bitrix/advertising.banner/templates/jssor/jssor.slider.min.js");
	$arParams['WIDTH'] = intval($arResult['SIZE']['WIDTH']);
	$arParams['HEIGHT'] = intval($arResult['SIZE']['HEIGHT']);
	$arParams['CYCLING'] = $arParams['CYCLING'] == 'Y' ? 'true' : 'false';
	$arParams['PAUSE'] = $arParams['PAUSE'] == 'Y' ? 3 : 0;
	$arParams['INTERVAL'] = isset($arParams['INTERVAL']) ? intval($arParams['INTERVAL']) : 5000;
	$arParams['ANIMATION_DURATION'] = $arParams['PREVIEW'] == 'Y' ? 500 : intval($arParams['ANIMATION_DURATION']);
	$arParams['WRAP'] = intval($arParams['WRAP']);
	$arParams['ARROW_NAV'] = $arParams['PREVIEW'] == 'Y' ? 2 : intval($arParams['ARROW_NAV']);
	$arParams['BULLET_NAV'] =  $arParams['PREVIEW'] == 'Y' ? 2 : intval($arParams['BULLET_NAV']);
	$arParams['KEYBOARD'] = $arParams['KEYBOARD'] == 'Y' ? 'true' : 'false';
	$arParams['EFFECTS'] = is_array($arParams['EFFECTS']) ? htmlspecialcharsbx(stripslashes(implode(',', $arParams['EFFECTS']))) : '';

	$frame = $this->createFrame()->begin("");
?>

<?if ($arParams['PREVIEW'] == 'Y'):?>
	<div id='tPreview' style="display:none;margin:auto"">
<?endif;?>

<style>
	.jssorb21 {
		position: absolute;
		bottom: 26px;
		left: 6px;
	}
	.jssorb21 div, .jssorb21 div:hover, .jssorb21 .av {
		position: absolute;
		/* size of bullet element */
		width: 19px;
		height: 19px;
		text-align: center;
		line-height: 19px;
		color: white;
		font-size: 12px;
		background: url(/bitrix/components/bitrix/advertising.banner/templates/jssor/images/b21.png) no-repeat;
		overflow: hidden;
		cursor: pointer;
	}
	.jssorb21 div { background-position: -5px -5px; }
	.jssorb21 div:hover, .jssorb21 .av:hover { background-position: -35px -5px; }
	.jssorb21 .av { background-position: -65px -5px; }
	.jssorb21 .dn, .jssorb21 .dn:hover { background-position: -95px -5px; }

	.jssora21l, .jssora21r {
		display: block;
		position: absolute;
		/* size of arrow element */
		width: 55px;
		height: 55px;
		cursor: pointer;
		background: url(/bitrix/components/bitrix/advertising.banner/templates/jssor/images/a21.png) center no-repeat;
		overflow: hidden;
	}
	.jssora21l { background-position: -3px -33px; top: 123px; left: 8px; }
	.jssora21r { background-position: -63px -33px; top: 123px; right: 8px; }
	.jssora21l:hover { background-position: -123px -33px; }
	.jssora21r:hover { background-position: -183px -33px; }
	.jssora21l.jssora21ldn { background-position: -243px -33px; }
	.jssora21r.jssora21rdn { background-position: -303px -33px; }
</style>

<script>
	jssor_slider_starter_<?=$arResult['ID']?> = function (containerId) {
		var _SlideshowTransitions = [
			<?=$arParams['EFFECTS']?>
			];
		var _CaptionTransitions = [];
		_CaptionTransitions["L"] = {$Duration:900,x:0.6,$Easing:{$Left:$JssorEasing$.$EaseInOutSine},$Opacity:2};
		_CaptionTransitions["R"] = {$Duration:900,x:-0.6,$Easing:{$Left:$JssorEasing$.$EaseInOutSine},$Opacity:2};
		_CaptionTransitions["FADE"] = {$Duration:900,$Opacity:2};
		var options = {
			$FillMode: 5,                                       //[Optional] The way to fill image in slide, 0 stretch, 1 contain (keep aspect ratio and put all inside slide), 2 cover (keep aspect ratio and cover whole slide), 4 actual size, 5 contain for large image, actual size for small image, default value is 0
			$AutoPlay: <?=$arParams['CYCLING']?>,                                    //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
			$AutoPlayInterval: <?=$arParams['INTERVAL']?>,                            //[Optional] Interval (in milliseconds) to go for next slide since the previous stopped if the slider is auto playing, default value is 3000
			$PauseOnHover: <?=$arParams['PAUSE']?>,                                   //[Optional] Whether to pause when mouse over if a slider is auto playing, 0 no pause, 1 pause for desktop, 2 pause for touch device, 3 pause for desktop and touch device, 4 freeze for desktop, 8 freeze for touch device, 12 freeze for desktop and touch device, default value is 1
			$Loop: <?=$arParams['WRAP']?>,                                      // Enable loop(circular) of carousel or not, 0: stop, 1: loop, 2 rewind, default value is 1
			$StartIndex:0,
			$ShowLoading:true,
			$ArrowKeyNavigation: <?=$arParams['KEYBOARD']?>,   			            //[Optional] Allows keyboard (arrow key) navigation or not, default value is false
			$SlideEasing: $JssorEasing$.$EaseOutQuad,          //[Optional] Specifies easing for right to left animation, default value is $JssorEasing$.$EaseOutQuad
			$SlideDuration: <?=$arParams['ANIMATION_DURATION']?>,                                //[Optional] Specifies default duration (swipe) for slide in milliseconds, default value is 500
			$MinDragOffsetToSlide: 20,                          //[Optional] Minimum drag offset to trigger slide , default value is 20
			//$SlideWidth: 600,                                 //[Optional] Width of every slide in pixels, default value is width of 'slides' container
			//$SlideHeight: 300,                                //[Optional] Height of every slide in pixels, default value is height of 'slides' container
			$SlideSpacing: 0, 					                //[Optional] Space between each slide in pixels, default value is 0
			$DisplayPieces: 1,                                  //[Optional] Number of pieces to display (the slideshow would be disabled if the value is set to greater than 1), the default value is 1
			$ParkingPosition: 0,                                //[Optional] The offset position to park slide (this options applys only when slideshow disabled), default value is 0.
			$UISearchMode: 1,                                   //[Optional] The way (0 parellel, 1 recursive, default value is 1) to search UI components (slides container, loading screen, navigator container, arrow navigator container, thumbnail navigator container etc).
			$PlayOrientation: 1,                                //[Optional] Orientation to play slide (for auto play, navigation), 1 horizental, 2 vertical, 5 horizental reverse, 6 vertical reverse, default value is 1
			$DragOrientation: 1,                                //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)

			$BulletNavigatorOptions: {                                //[Optional] Options to specify and enable navigator or not
				$Class: $JssorBulletNavigator$,                       //[Required] Class to create navigator instance
				$ChanceToShow: <?=$arParams['BULLET_NAV']?>,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
				$AutoCenter: 1,                                 //[Optional] Auto center navigator in parent container, 0 None, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
				$Steps: 1,                                      //[Optional] Steps to go for each navigation request, default value is 1
				$Lanes: 1,                                      //[Optional] Specify lanes to arrange items, default value is 1
				$SpacingX: 8,                                   //[Optional] Horizontal space between each item in pixel, default value is 0
				$SpacingY: 8,                                   //[Optional] Vertical space between each item in pixel, default value is 0
				$Orientation: 1                                //[Optional] The orientation of the navigator, 1 horizontal, 2 vertical, default value is 1
			},

			$ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
				$Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
				$ChanceToShow: <?=$arParams['ARROW_NAV']?>,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
				$AutoCenter: 2,                                 //[Optional] Auto center arrows in parent container, 0 No, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
				$Steps: 1                                       //[Optional] Steps to go for each navigation request, default value is 1
			},

			$SlideshowOptions: {                                //[Optional] Options to specify and enable slideshow or not
				$Class: $JssorSlideshowRunner$,                 //[Required] Class to create instance of slideshow
				$Transitions: _SlideshowTransitions,            //[Required] An array of slideshow transitions to play slideshow
				$TransitionsOrder: 1,                           //[Optional] The way to choose transition to play slide, 1 Sequence, 0 Random
				$ShowLink: true                                    //[Optional] Whether to bring slide link on top of the slider when slideshow is running, default value is false
			},

			$CaptionSliderOptions: {
				$Class: $JssorCaptionSlider$,
				$CaptionTransitions: _CaptionTransitions,
				$PlayInMode: 1,
				$PlayOutMode: 3
			}
		};

		var jssor_slider_<?=$arResult['ID']?> = new $JssorSlider$("slider_container_<?=$arResult['ID']?>", options);

		<?if($arParams['SCALE'] == 'Y'):?>
		//responsive code begin
		function ScaleSlider() {
			var width = jssor_slider_<?=$arResult['ID']?>.$Elmt.parentNode.clientWidth;
			if (width)
				jssor_slider_<?=$arResult['ID']?>.$ScaleWidth(Math.min(width, 1920));
			else
				window.setTimeout(ScaleSlider, 30);
		}
		ScaleSlider();
		$(window).bind("load", ScaleSlider);
		$(window).bind("resize", ScaleSlider);
		$(window).bind("orientationchange", ScaleSlider);
		//responsive code end
		<?endif;?>
	};
</script>
<div id="slider_container_<?=$arResult['ID']?>" style="position: relative; margin: 0 auto;
	top: 0px; left: 0px; width: <?=$arParams['WIDTH']?>px; height: <?=$arParams['HEIGHT']?>px; overflow: hidden;">
	<div data-u="loading" style="position: absolute; top: 0px; left: 0px;">
		<div style="filter: alpha(opacity=70); opacity:0.7; display: block;
             position: absolute; top: 0px; left: 0px; width: 100%; height: 100%;
             background-color: #000;">
		</div>
		<div style="display: block;
            position: absolute; top: 0px; left: 0px;width: 100%;height:100%;
            background: url(/bitrix/components/bitrix/advertising.banner/templates/jssor/images/loading.gif) no-repeat center center;">
		</div>
	</div>
	<div data-u="slides" style="cursor: move; position: absolute; left: 0px; top: 0px; width: <?=$arParams['WIDTH']?>px;
		height: <?=$arParams['HEIGHT']?>px; overflow: hidden;">
		<?foreach($arResult["BANNERS"] as $k => $banner):?>
			<?=$banner?>
		<?endforeach;?>
	</div>

	<div data-u="navigator" class="jssorb21">
		<div data-u="prototype"></div>
	</div>

	<span data-u="arrowleft" class="jssora21l"></span>
	<span data-u="arrowright" class="jssora21r"></span>
</div>
<script>
	BX.ready(function(){
		jssor_slider_starter_<?=$arResult['ID']?>('slider_container_<?=$arResult['ID']?>');
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