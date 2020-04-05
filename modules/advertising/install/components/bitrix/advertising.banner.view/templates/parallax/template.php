<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var array $arParams
 */

$this->setFrameMode(true);
$rnd = $component->randString();

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
$arParams['HEIGHT'] = intval($arParams['HEIGHT']);

if (is_array($arParams['PROPS']['HEADING']))
{
	$headingText = $arParams['PROPS']['HEADING']['CODE'];
}
else
{
	$headingText = $arParams['PROPS']['HEADING'];
	$announcementText = $arParams['PROPS']['ANNOUNCEMENT'];
}

if ($arParams['CASUAL_PROPERTIES']['TYPE'] == 'template')
{
	$imgSrc = $arParams['FILES']['IMG']['SRC'];
	$showUrl = $arParams['PROPS']['LINK_URL'] != '' && !isset($arParams['PREVIEW']);
	$url = $arParams['PROPS']['LINK_URL'];
	$alt = $arParams['PROPS']['LINK_TITLE'];
	$urlTarget = $arParams['PROPS']['LINK_TARGET'];
}
else
{
	$imgSrc = $arParams['FILES']['CASUAL_IMG']['SRC'];
	$showUrl = $arParams['CASUAL_PROPERTIES']['URL'] != '' && !isset($arParams['PREVIEW']);
	$url = $arParams['CASUAL_PROPERTIES']['URL'];
	$alt = $arParams['CASUAL_PROPERTIES']['ALT'];
	$urlTarget = $arParams['CASUAL_PROPERTIES']['URL_TARGET'];
}
?>

<div class='bx-parallax' style="position:relative;height:<?=$arParams['HEIGHT']?>px;background-image:url('<?=$imgSrc?>');background-attachment:fixed;background-repeat: no-repeat;background-position: 50% 0;">
<? if ($showUrl): ?>
	<a href="<?=$url?>" title="<?=$alt?>" target="<?=$urlTarget?>" style="display:block;<? if ($arParams['PROPS']['PRESET']!=4): ?>height:100%;<? endif ?>">
<? endif ?>
	<div class="bx-slider-preset-<?=$arParams['PROPS']['PRESET']?>">
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
						<div id='announce<?=$rnd?>' class="bx-advertisingbanner-text-block" style="font-size:<?=$arParams['PROPS']['ANNOUNCEMENT_FONT_SIZE']?>px;color:#<?=$arParams['PROPS']['ANNOUNCEMENT_FONT_COLOR']?>;background:rgba(<?=$arParams['PROPS']['ANNOUNCEMENT_BG_COLOR']?>);"><?=$announcementText?></div>
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
<? if ($showUrl): ?>
	</a>
<? endif ?>
</div>