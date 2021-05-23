<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var array $arParams
 */

$this->setFrameMode(true);
$rnd = $component->randString();

$arParams['PROPS']['PRESET'] = intval($arParams['PROPS']['PRESET']);
$arParams['PROPS']['HEADING_BG_OPACITY'] = isset($arParams['PROPS']['HEADING_BG_OPACITY']) ? intval($arParams['PROPS']['HEADING_BG_OPACITY']) : 100;

$arParams['PROPS']['HEADING_BG_COLOR'] = hexdec(mb_substr($arParams['PROPS']['HEADING_BG_COLOR'], 0, 2)).','
	.hexdec(mb_substr($arParams['PROPS']['HEADING_BG_COLOR'], 2, 2)).','
	.hexdec(mb_substr($arParams['PROPS']['HEADING_BG_COLOR'], 4, 2)).','
	.abs(1 - $arParams['PROPS']['HEADING_BG_OPACITY']/100);

$arParams['PROPS']['ANNOUNCEMENT_BG_COLOR'] = hexdec(mb_substr($arParams['PROPS']['ANNOUNCEMENT_BG_COLOR'], 0, 2)).','
	.hexdec(mb_substr($arParams['PROPS']['ANNOUNCEMENT_BG_COLOR'], 2, 2)).','
	.hexdec(mb_substr($arParams['PROPS']['ANNOUNCEMENT_BG_COLOR'], 4, 2)).','
	.abs(1 - intval($arParams['PROPS']['ANNOUNCEMENT_BG_OPACITY'])/100);

$arParams['PROPS']['HEADING_FONT_SIZE'] = intval($arParams['PROPS']['HEADING_FONT_SIZE']);
$arParams['PROPS']['ANNOUNCEMENT_FONT_SIZE'] = intval($arParams['PROPS']['ANNOUNCEMENT_FONT_SIZE']);

$arParams['PROPS']['OVERLAY_COLOR'] = hexdec(mb_substr($arParams['PROPS']['OVERLAY_COLOR'], 0, 2)).','
	.hexdec(mb_substr($arParams['PROPS']['OVERLAY_COLOR'], 2, 2)).','
	.hexdec(mb_substr($arParams['PROPS']['OVERLAY_COLOR'], 4, 2)).','
	.abs(1 - intval($arParams['PROPS']['OVERLAY_OPACITY'])/100);

$arParams['PROPS']['BUTTON_BG_COLOR'] = hexdec(mb_substr($arParams['PROPS']['BUTTON_BG_COLOR'], 0, 2)).','
	.hexdec(mb_substr($arParams['PROPS']['BUTTON_BG_COLOR'], 2, 2)).','
	.hexdec(mb_substr($arParams['PROPS']['BUTTON_BG_COLOR'], 4, 2));

$arParams['PROPS']['EFFECT'] = htmlspecialcharsbx($arParams['PROPS']['EFFECT']);

if (is_array($arParams['PROPS']['HEADING']))
{
	$headingText = $arParams['PROPS']['HEADING']['CODE'];
	$announcementText = '';
}
else
{
	$headingText = $arParams['PROPS']['HEADING'];
	$announcementText = $arParams['PROPS']['ANNOUNCEMENT'];
}
?>

<div>
<? if ($arParams['CASUAL_PROPERTIES']['TYPE'] == 'template'): ?>
	<? if (isset($arParams['FILES']['IMG']['SRC'])): ?>
		<? if ($arParams['PROPS']['LINK_URL'] != '' && !isset($arParams['PREVIEW'])): ?>
			<a href="<?=$arParams['PROPS']['LINK_URL']?>" target="<?=$arParams['PROPS']['LINK_TARGET']?>" title="<?=$arParams['PROPS']['LINK_TITLE']?>">
		<? endif ?>
		<img <? if ($arParams['PROPS']['EFFECT']!=''){echo 'data-transition="'.$arParams['PROPS']['EFFECT'].'"';}?> src="<?=$arParams['FILES']['IMG']['SRC']?>" alt="<?=$arParams['FILES']['IMG']['DESCRIPTION']?>" title="#htmlcaption<?=$rnd?>">
		<? if ($arParams['PROPS']['LINK_URL'] != '' && !isset($arParams['PREVIEW'])): ?>
			</a>
		<? endif ?>
		<div id="htmlcaption<?=$rnd?>" style="display:none">
			<? if ($arParams['EXT_MODE'] == 'N'): ?>
				<? if ($arParams['PROPS']['OVERLAY'] == 'Y'): ?>
					<div class="bx-advertisingbanner-pattern" style="background:rgba(<?=$arParams['PROPS']['OVERLAY_COLOR']?>)"></div>
				<? endif ?>
				<? if ($arParams['PROPS']['HEADING_SHOW'] == 'Y' || $arParams['PROPS']['ANNOUNCEMENT_SHOW'] == 'Y' || $arParams['PROPS']['BUTTON'] == 'Y'): ?>
				<div class="bx-slider-preset-<?=$arParams['PROPS']['PRESET']?>">
					<div class="bx-advertisingbanner-content" <? if ($arParams['PROPS']['PRESET']==2 || $arParams['PROPS']['PRESET']==3){echo 'style="background:rgba('.$arParams['PROPS']['HEADING_BG_COLOR'].');"';}?>>
					<? if ($arParams['PROPS']['HEADING_SHOW'] == 'Y'): ?>
						<div id="slider-caption-<?=$rnd?>" class="bx-advertisingbanner-text-title" style="font-size:<?=$arParams['PROPS']['HEADING_FONT_SIZE']?>px;color:#<?=$arParams['PROPS']['HEADING_FONT_COLOR']?>;<? if ($arParams['PROPS']['PRESET']==1 || $arParams['PROPS']['PRESET']==4){echo 'background:rgba('.$arParams['PROPS']['HEADING_BG_COLOR'].');';}?>">
							<?=$headingText?>
						</div>
					<? endif ?>
					<? if ($arParams['PROPS']['ANNOUNCEMENT_SHOW'] == 'Y'): ?>
						<div id="slider-caption2-<?=$rnd?>" class="bx-advertisingbanner-text-block" style="font-size:<?=$arParams['PROPS']['ANNOUNCEMENT_FONT_SIZE']?>px;color:#<?=$arParams['PROPS']['ANNOUNCEMENT_FONT_COLOR']?>;background:rgba(<?=$arParams['PROPS']['ANNOUNCEMENT_BG_COLOR']?>);">
							<?=$announcementText?>
						</div>
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
				</div>
				<? endif ?>
			<? elseif ($arParams['EXT_MODE'] == 'Y'): ?>
				<?=$headingText?>
			<? endif ?>
		</div>
	<? endif ?>
<? else: ?>
	<? if (isset($arParams['FILES']['CASUAL_IMG']['SRC'])): ?>
		<? if ($arParams['CASUAL_PROPERTIES']['URL'] != ''): ?>
			<a href="<?=$arParams['CASUAL_PROPERTIES']['URL']?>" target="<?=$arParams['CASUAL_PROPERTIES']['URL_TARGET']?>" title="<?=$arParams['CASUAL_PROPERTIES']['ALT']?>">
		<? endif ?>
		<img src="<?=$arParams['FILES']['CASUAL_IMG']['SRC']?>">
		<? if ($arParams['CASUAL_PROPERTIES']['URL'] != ''): ?>
			</a>
		<? endif ?>
	<? endif ?>
<? endif ?>
</div>