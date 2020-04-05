<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var array $arParams
 */

$this->setFrameMode(true);
$rnd = $component->randString();

$arParams['PROPS']['VIDEO_MUTE'] = $arParams['PROPS']['VIDEO_MUTE'] == 'Y' ? 'muted' : '';
$arParams['PROPS']['STREAM_MUTE'] = $arParams['PROPS']['STREAM_MUTE'] == 'Y' ? '1' : '0';
$arParams['AUTOPLAY'] = $arParams['INDEX'] == '0' ? '&autoplay=1' : '';
$arParams['PROPS']['HEADING_FONT_SIZE'] = intval($arParams['PROPS']['HEADING_FONT_SIZE']);
$arParams['PROPS']['ANNOUNCEMENT_FONT_SIZE'] = intval($arParams['PROPS']['ANNOUNCEMENT_FONT_SIZE']);
$arParams['PROPS']['HEADING_BG_OPACITY'] = isset($arParams['PROPS']['HEADING_BG_OPACITY']) ? intval($arParams['PROPS']['HEADING_BG_OPACITY']) : 100;

$arParams['PROPS']['OVERLAY_COLOR'] = hexdec(substr($arParams['PROPS']['OVERLAY_COLOR'],0,2)).','
	.hexdec(substr($arParams['PROPS']['OVERLAY_COLOR'],2,2)).','
	.hexdec(substr($arParams['PROPS']['OVERLAY_COLOR'],4,2)).','
	.abs(100 - intval($arParams['PROPS']['OVERLAY_OPACITY']))/100;

$arParams['PROPS']['HEADING_BG_COLOR'] = hexdec(substr($arParams['PROPS']['HEADING_BG_COLOR'],0,2)).','
	.hexdec(substr($arParams['PROPS']['HEADING_BG_COLOR'],2,2)).','
	.hexdec(substr($arParams['PROPS']['HEADING_BG_COLOR'],4,2)).','
	.abs(100 - $arParams['PROPS']['HEADING_BG_OPACITY'])/100;

$arParams['PROPS']['ANNOUNCEMENT_BG_COLOR'] = hexdec(substr($arParams['PROPS']['ANNOUNCEMENT_BG_COLOR'],0,2)).','
	.hexdec(substr($arParams['PROPS']['ANNOUNCEMENT_BG_COLOR'],2,2)).','
	.hexdec(substr($arParams['PROPS']['ANNOUNCEMENT_BG_COLOR'],4,2)).','
	.abs(100 - intval($arParams['PROPS']['ANNOUNCEMENT_BG_OPACITY']))/100;

$arParams['PROPS']['BUTTON_BG_COLOR'] = hexdec(substr($arParams['PROPS']['BUTTON_BG_COLOR'],0,2)).','
	.hexdec(substr($arParams['PROPS']['BUTTON_BG_COLOR'],2,2)).','
	.hexdec(substr($arParams['PROPS']['BUTTON_BG_COLOR'],4,2));

$arParams['PROPS']['PRESET'] = intval($arParams['PROPS']['PRESET']);
$animation = $arParams['PROPS']['ANIMATION'] == 'Y' ? ' data-duration="'.intval($arParams['PROPS']['ANIMATION_DURATION']).'" data-delay="'.intval($arParams['PROPS']['ANIMATION_DELAY']).'"' : '';
$playClass = $arParams['PROPS']['ANIMATION'] == 'Y' ? ' play-caption' : '';
$mobileHide = $arParams['PROPS']['ANNOUNCEMENT_MOBILE_HIDE'] == 'Y' ? ' hidden-xs' : '';

$id = '';
if ($arParams['PROPS']['BACKGROUND'] == 'stream')
{
	if (strpos($arParams['PROPS']['STREAM'], 'watch?') !== false && ($v = strpos($arParams['PROPS']['STREAM'], 'v=')) !== false)
	{
		$id = substr($arParams['PROPS']['STREAM'], $v + 2, 11);
	}
	elseif ($v = strpos($arParams['PROPS']['STREAM'], 'youtu.be/'))
	{
		$id = substr($arParams['PROPS']['STREAM'], $v + 9, 11);
	}
	elseif ($v = strpos($arParams['PROPS']['STREAM'], 'embed/'))
	{
		$id = substr($arParams['PROPS']['STREAM'], $v + 6, 11);
	}
}

if (is_array($arParams['PROPS']['HEADING']))
{
	$headingText = $arParams['PROPS']['HEADING']['CODE'];
}
else
{
	$headingText = $arParams['PROPS']['HEADING'];
	$announcementText = $arParams['PROPS']['ANNOUNCEMENT'];
}
?>

<? if ($arParams['CASUAL_PROPERTIES']['TYPE'] == 'template'): ?>
<div class="bx-slider-preset-<?=$arParams['PROPS']['PRESET']?>">
	<? if ($arParams['PROPS']['LINK_URL'] != '' && !isset($arParams['PREVIEW'])): ?>
		<a href="<?=$arParams['PROPS']['LINK_URL']?>" title="<?=$arParams['PROPS']['LINK_TITLE']?>" target="<?=$arParams['PROPS']['LINK_TARGET']?>" style="display:block;">
	<? endif ?>

	<? if (isset($arParams['PROPS']['BACKGROUND']) && $arParams['PROPS']['BACKGROUND'] == 'video'): ?>
		<div align="center" class="embed-responsive embed-responsive-16by9">
			<video <?=$arParams['PROPS']['VIDEO_MUTE']?> loop class="embed-responsive-item">
			<? if (isset($arParams['FILES']['VIDEO_MP4']['SRC'])): ?>
				<source src="<?=$arParams['FILES']['VIDEO_MP4']['SRC']?>" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"'>
			<? endif ?>

			<? if (isset($arParams['FILES']['VIDEO_WEBM']['SRC'])): ?>
				<source src="<?=$arParams['FILES']['VIDEO_WEBM']['SRC']?>" type='video/webm; codecs="vp8, vorbis"'>
			<? endif ?>
			</video>
		</div>
	<? elseif (isset($arParams['PROPS']['BACKGROUND']) && $arParams['PROPS']['BACKGROUND'] == 'stream'): ?>
		<div class="embed-responsive embed-responsive-16by9">
			<iframe id='yt_player_<?=$rnd?>' class="embed-responsive-item" src="https://www.youtube.com/embed/<?=$id?>?enablejsapi=1&controls=0&showinfo=0&rel=0<?=$arParams['AUTOPLAY']?>"></iframe>

			<? if ($arParams['PROPS']['LINK_URL'] != '' && !isset($arParams['PREVIEW'])): ?>
				<a href="<?=$arParams['PROPS']['LINK_URL']?>" title="<?=$arParams['PROPS']['LINK_TITLE']?>"
					target="<?=$arParams['PROPS']['LINK_TARGET']?>"
					style="position:absolute; top:0; left:0; display:inline-block; width:100%; height:100%; z-index:2;">
				</a>
			<? endif ?>

			<script>
				if (!yt_player)
					var yt_player = {};
				yt_player['<?=$rnd?>'] = {id: 'yt_player_<?=$rnd?>', mute: '<?=$arParams['PROPS']['STREAM_MUTE']?>'};
			</script>
		</div>
	<? else: ?>
		<img src="<?=$arParams['FILES']['IMG']['SRC']?>" class="center-block img-responsive" alt="<?=$arParams['FILES']['IMG']['DESCRIPTION']?>" title="<?=$arParams['FILES']['IMG']['DESCRIPTION']?>">
	<? endif ?>

	<? if ($arParams['EXT_MODE'] == 'N'): ?>
		<? if ($arParams['PROPS']['OVERLAY'] == 'Y'): ?>
			<div class="bx-advertisingbanner-pattern" style="background:rgba(<?=$arParams['PROPS']['OVERLAY_COLOR']?>)"></div>
		<? endif ?>

		<? if ($arParams['PROPS']['HEADING_SHOW'] == 'Y' || $arParams['PROPS']['ANNOUNCEMENT_SHOW'] == 'Y' || $arParams['PROPS']['BUTTON'] == 'Y'): ?>
			<div class="bx-advertisingbanner-content<?=$playClass?>"<?=$animation?> <? if ($arParams['PROPS']['PRESET']==2 || $arParams['PROPS']['PRESET']==3){echo 'style="background:rgba('.$arParams['PROPS']['HEADING_BG_COLOR'].');"';}?>>
				<? if ($arParams['PROPS']['HEADING_SHOW'] == 'Y'): ?>
					<div id='text<?=$rnd?>' class="bx-advertisingbanner-text-title" style="display:inline-block;font-size:<?=$arParams['PROPS']['HEADING_FONT_SIZE']?>px;color:#<?=$arParams['PROPS']['HEADING_FONT_COLOR']?>;<? if ($arParams['PROPS']['PRESET']==1 || $arParams['PROPS']['PRESET']==4){echo 'background:rgba('.$arParams['PROPS']['HEADING_BG_COLOR'].');';}?>"><?=$headingText?></div>
				<? endif ?>

				<? if ($arParams['PROPS']['ANNOUNCEMENT_SHOW'] == 'Y'): ?>
					<div id='announce<?=$rnd?>' class="bx-advertisingbanner-text-block<?=$mobileHide?>" style="font-size:<?=$arParams['PROPS']['ANNOUNCEMENT_FONT_SIZE']?>px;color:#<?=$arParams['PROPS']['ANNOUNCEMENT_FONT_COLOR']?>;background:rgba(<?=$arParams['PROPS']['ANNOUNCEMENT_BG_COLOR']?>);"><?=$announcementText?></div>
				<? endif ?>

				<? if ($arParams['PROPS']['BUTTON'] == 'Y'): ?>
					<? if (isset($arParams['PREVIEW'])): ?>
						<button  class="bx-advertisingbanner-btn" style="background-color: rgb(<?=$arParams['PROPS']['BUTTON_BG_COLOR']?>);color:#<?=$arParams['PROPS']['BUTTON_FONT_COLOR']?>;border: 0;"><?=$arParams['PROPS']['BUTTON_TEXT']?></button>
					<? else: ?>
						<a class="bx-advertisingbanner-btn" href="<?=$arParams['PROPS']['BUTTON_LINK_URL']?>" title="<?=$arParams['PROPS']['BUTTON_LINK_TITLE']?>" target="<?=$arParams['PROPS']['BUTTON_LINK_TARGET']?>" style="background-color: rgb(<?=$arParams['PROPS']['BUTTON_BG_COLOR']?>);color:#<?=$arParams['PROPS']['BUTTON_FONT_COLOR']?>">
							<?=$arParams['PROPS']['BUTTON_TEXT']?>
						</a>
					<? endif ?>
				<? endif ?>
			</div>
		<? endif ?>
	<? elseif ($arParams['EXT_MODE'] == 'Y'): ?>
		<?=$headingText?>
	<? endif ?>
	<? if ($arParams['PROPS']['LINK_URL'] != '' && !isset($arParams['PREVIEW'])): ?>
		</a>
	<? endif ?>
	<script>
		var objList = [];
		if (BX('text<?=$rnd?>'))
			objList.push({node : BX('text<?=$rnd?>'), maxFontSize : <?=$arParams['PROPS']['HEADING_FONT_SIZE']?>, smallestValue : false});
		<? if (!isset($arParams['PREVIEW'])): ?>
			BX.FixFontSize.init({
				objList : objList,
				onresize : true
			});
		<? endif ?>
	</script>
</div>
<? else: ?>
	<? if ($arParams['CASUAL_PROPERTIES']['URL'] != '' && !isset($arParams['PREVIEW'])): ?>
		<a href="<?=$arParams['CASUAL_PROPERTIES']['URL']?>" title="<?=$arParams['CASUAL_PROPERTIES']['ALT']?>" target="<?=$arParams['CASUAL_PROPERTIES']['URL_TARGET']?>" style="display:block;">
	<? endif ?>
	<img src="<?=$arParams['FILES']['CASUAL_IMG']['SRC']?>" class="center-block img-responsive">
	<? if ($arParams['CASUAL_PROPERTIES']['URL'] != '' && !isset($arParams['PREVIEW'])): ?>
		</a>
	<? endif ?>
<? endif ?>