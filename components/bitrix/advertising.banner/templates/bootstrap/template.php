<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (count($arResult['BANNERS']) > 0):?>

<?
	$this->addExternalCss("/bitrix/css/main/bootstrap.css");
	$this->addExternalCss("/bitrix/css/main/font-awesome.css");
	$this->addExternalCss("/bitrix/themes/.default/banner.css");
	$this->addExternalJs("/bitrix/components/bitrix/advertising.banner/templates/bootstrap/bxcarousel.js");
	$arParams['WIDTH'] = intval($arResult['SIZE']['WIDTH']);
	$arParams['HEIGHT'] = intval($arResult['SIZE']['HEIGHT']);
	if($arParams['BS_CYCLING'] == 'Y')
		$arParams['BS_INTERVAL'] = intval($arParams['BS_INTERVAL']);
	else
		$arParams['BS_INTERVAL'] = 'false';
	$arParams['BS_WRAP'] = ($arParams['BS_WRAP'] == 'Y' || $arParams['PREVIEW'] == 'Y') ? 'true' : 'false';
	$arParams['BS_PAUSE'] = $arParams['BS_PAUSE'] == 'Y' ? 'true' : 'false';
	$arParams['BS_KEYBOARD'] = $arParams['BS_KEYBOARD'] == 'Y' ? 'true' : 'false';
	$arParams['BS_HIDE_FOR_TABLETS'] = $arParams['BS_HIDE_FOR_TABLETS'] == 'Y' ? ' hidden-sm' : '';
	$arParams['BS_HIDE_FOR_PHONES'] = $arParams['BS_HIDE_FOR_PHONES'] == 'Y' ? ' hidden-xs' : '';

	$frame = $this->createFrame()->begin("");
?>

<?if ($arParams['PREVIEW'] == 'Y'):?>
	<div id='tPreview' style="display:none;margin:auto;">
<?endif;?>

<div id="carousel-<?=$arResult['ID']?>" class="carousel <?=$arParams['BS_EFFECT']?><?=$arParams['BS_HIDE_FOR_TABLETS']?><?=$arParams['BS_HIDE_FOR_PHONES']?>" data-interval="<?=$arParams['BS_INTERVAL']?>" data-wrap="<?=$arParams['BS_WRAP']?>" data-pause="<?=$arParams['BS_PAUSE']?>" data-keyboard="<?=$arParams['BS_KEYBOARD']?>" data-ride="carousel">
	<style>
		<?if($arParams['BS_EFFECT']=='fade'):?>
		.carousel.fade {opacity: 1;}
		.carousel.fade .item {-moz-transition: opacity ease-in-out .7s;-o-transition: opacity ease-in-out .7s;-webkit-transition: opacity ease-in-out .7s;transition: opacity ease-in-out .7s;
			left: 0 !important;opacity: 0;top:0;position:absolute;width: 100%;display:block !important;z-index:1;}
		.carousel.fade .item:first-child {top:auto;position:relative;}
		.carousel.fade .item.active {opacity: 1;-moz-transition: opacity ease-in-out .7s;-o-transition: opacity ease-in-out .7s;-webkit-transition: opacity ease-in-out .7s;transition: opacity ease-in-out .7s;z-index:2;}
		<?endif;?>
		.carousel .carousel-control{z-index:4}
		.carousel-control .icon-prev:before{content: '';}
		.carousel-control .icon-next:before{content: '';}
		.carousel-control .icon-prev{margin-top: -30px;}
		.carousel-control .icon-next{margin-top: -30px;}
		.carousel-control.right{background-image:none}
		.carousel-control.left{background-image:none}
	</style>
	<?if($arParams['BS_BULLET_NAV'] == 'Y' || $arParams['BS_PREVIEW'] == 'Y'):?>
		<!-- Indicators -->
		<ol class="carousel-indicators">
		<?$i = 0;?>
		<?while($i < count($arResult['BANNERS'])):?>
			<li data-target="#carousel-<?=$arResult['ID']?>" data-slide-to="<?=$i?>" <?if($i==0) echo 'class="active"';$i++?>></li>
		<?endwhile;?>
		</ol>
	<?endif;?>

	<!-- Wrapper for slides -->
	<div class="carousel-inner" role="listbox">
	<?foreach($arResult["BANNERS"] as $k => $banner):?>
		<div class="item <?if($k==0) echo 'active';?>">
			<?=$banner?>
		</div>
	<?endforeach;?>
	</div>

	<?if($arParams['BS_ARROW_NAV'] == 'Y' || $arParams['PREVIEW'] == 'Y'):?>
		<!-- Controls -->
		<a href="#carousel-<?=$arResult['ID']?>" class="left carousel-control" data-slide="prev">
		<span class="icon-prev fa-stack fa-lg">
			<i class="fa fa-angle-left fa-stack-2x"></i>
		</span>
		</a>
		<a href="#carousel-<?=$arResult['ID']?>" class="right carousel-control" data-slide="next">
		<span class="icon-next fa-stack fa-lg">
			<i class="fa fa-angle-right fa-stack-2x"></i>
		</span>
		</a>
	<?endif;?>

	<script>
		BX("carousel-<?=$arResult['ID']?>").addEventListener("slid.bs.carousel", function (e) {
			var item = e.detail.curSlide.querySelector('.play-caption');
			if (!!item)
			{
				item.style.display = 'none';
				item.style.left = '-100%';
				item.style.opacity = 0;
			}
		}, false);
		BX("carousel-<?=$arResult['ID']?>").addEventListener("slide.bs.carousel", function (e) {
			var item = e.detail.curSlide.querySelector('.play-caption');
			if (!!item)
			{
				var duration = item.getAttribute('data-duration') || 500,
					delay = item.getAttribute('data-delay') || 0;

				setTimeout(function() {
					item.style.display = '';
					var easing = new BX.easing({
						duration : duration,
						start : {left: -100, opacity : 0},
						finish : {left: 0, opacity: 100},
						transition : BX.easing.transitions.quart,
						step : function(state){
							item.style.opacity = state.opacity/100;
							item.style.left = state.left + '%';
						},
						complete : function() {
						}
					});
					easing.animate();
				}, delay);
			}
		}, false);
		BX.ready(function(){
			var tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			var firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		});
		function mutePlayer(e) {
			e.target.mute();
		}
		function loopPlayer(e) {
			if (e.data === YT.PlayerState.ENDED)
				e.target.playVideo();
		}
		function onYouTubePlayerAPIReady() {
			if (typeof yt_player !== 'undefined')
			{
				for (var i in yt_player)
				{
					window[yt_player[i].id] = new YT.Player(
							yt_player[i].id, {
								events: {
									'onStateChange': loopPlayer
								}
							}
					);
					if (yt_player[i].mute == true)
						window[yt_player[i].id].addEventListener('onReady', mutePlayer);
				}
				delete yt_player;
			}
		}
	</script>
</div>
<?if ($arParams['PREVIEW'] == 'Y'):?>
	</div>
	<script>
		(function(){
			if (<?=$arParams['WIDTH']?> == 0)
			{
				BX('tPreview').style.width = top.cWidth/2 + 'px';
				BX('tPreview').style.height = top.cWidth/3.55 + 'px';
			}
			else if(top.cWidth/2 > <?=$arParams['WIDTH']?>)
			{
				BX('tPreview').style.width = '<?=$arParams['WIDTH']?>px';
				BX('tPreview').style.height = '<?=$arParams['HEIGHT']?>px';
			}
			else
			{
				BX('tPreview').style.width = top.cWidth/2 + 'px';
				BX('tPreview').style.height = top.cWidth/3.55 + 'px';
			}
			document.body.style.backgroundColor = 'transparent';
			BX('tPreview').style.display = '';
		})();
	</script>
<?endif;?>

<?$frame->end();?>

<?endif;?>