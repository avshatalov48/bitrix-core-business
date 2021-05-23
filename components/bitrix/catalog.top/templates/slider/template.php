<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
$this->setFrameMode(true);

CJSCore::Init(array("fx"));
$randID = $this->randString();
$strContID = 'bx_catalog_slider_'.$randID;
$itemsCount = count($arResult["ITEMS"]);
$arRowIDs = array();
$boolFirst = true;
$strContWidth = 100*$itemsCount;
$strItemWidth = 100/$itemsCount;
?>
<div class="bx_slider_section" id="<? echo $strContID; ?>">
	<div class="bx_slider_container" style="width:<? echo $strContWidth; ?>%;" id="bx_catalog_slider_cont_<?=$randID?>">
<?foreach($arResult["ITEMS"] as $key => $arItem):
	$strRowID = 'cat-top-'.$key.'_'.$randID;
	$arRowIDs[] = $strRowID;
	$strTitle = (
		isset($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]) && '' != isset($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"])
		? $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]
		: $arItem['NAME']
	);
	?>
		<div id="<? echo $strRowID; ?>" class="bx_slider_block<?echo ($boolFirst ? ' active' : ''); ?>" style="width:<? echo $strItemWidth; ?>%;">
			<div class="bx_slider_photo_container">
				<div class="bx_slider_photo_background"></div>
				<a
					href="<?=$arItem["DETAIL_PAGE_URL"]?>"
					class="bx_slider_photo_element"
					style="background: #fff url('<?=$arItem["DETAIL_PICTURE"]["SRC"]?>') no-repeat center;"
					title="<? echo $strTitle; ?>"
				>
					<!--<div class="bx_stick_disc">-25%</div>
					<div class="bx_stick new">New</div>-->
				</a>
			</div>
			<div class="bx_slider_content_container">
				<h1 class="bx_slider_title"><a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a></h1>
				<div class="bx_slider_content_description" style="padding-top: 10px;"><?=$arItem["PREVIEW_TEXT"] ? $arItem["PREVIEW_TEXT"] : $arItem["DETAILTEXT"]?></div>
				<div class="bx_slider_price_container">
					<div class="bx_slider_price_leftblock">
					<?if(is_array($arItem["OFFERS"]) && !empty($arItem["OFFERS"])):?>
						<div class="bx_slider_current_price bx_no_oldprice"><? echo GetMessage('CATALOG_FROM'); ?> <?=$arItem["PRINT_MIN_OFFER_PRICE"]?></div>
					<?else:?>
						<?
						if (isset($arItem['MIN_PRICE']) && !empty($arItem['MIN_PRICE']))
						{
							if ($arItem['MIN_PRICE']["DISCOUNT_VALUE"] < $arItem['MIN_PRICE']["VALUE"]):?>
								<div class="bx_slider_current_price"><?=$arItem['MIN_PRICE']["PRINT_DISCOUNT_VALUE"]?></div>
								<div class="bx_slider_old_price"><?=$arItem['MIN_PRICE']["PRINT_VALUE"]?></div>
							<?else:?>
								<div class="bx_slider_current_price bx_no_oldprice"><?=$arItem['MIN_PRICE']["PRINT_VALUE"]?></div>
							<?endif;
						}
						else
						{
							foreach($arItem["PRICES"] as $priceCode=>$arPrices):?>
							<?if ($arPrices["DISCOUNT_VALUE"] < $arPrices["VALUE"]):?>
								<div class="bx_slider_current_price"><?=$arPrices["PRINT_DISCOUNT_VALUE"]?></div>
								<div class="bx_slider_old_price"><?=$arPrices["PRINT_VALUE"]?></div>
							<?else:?>
								<div class="bx_slider_current_price bx_no_oldprice"><?=$arPrices["PRINT_VALUE"]?></div>
							<?endif?>
							<?endforeach;
						}
					endif?>
						<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="bt_blue big shadow cart"><span></span><strong><?=GetMessage("CATALOG_MORE")?></strong></a>
					</div>
					<div class="bx_slider_price_rightblock"></div>
				</div>
			</div>
		</div>
<?
	$boolFirst = false;
endforeach;?>
	</div>
</div>
<?
if (1 < $itemsCount)
{
	$arJSParams = array(
		'cont' => $strContID,
		'arrows' => array(
			'id' => $strContID.'_arrows',
			'className' => 'bx_slider_controls'
		),
		'left' => array(
			'id' => $strContID.'_left_arr',
			'className' => 'bx_slider_arrow_left'
		),
		'right' => array(
			'id' => $strContID.'_right_arr',
			'className' => 'bx_slider_arrow_right'
		),
		'pagination' => array(
			'id' => $strContID.'_pagination',
			'className' => 'bx_slider_pagination'
		),
		'items' => $arRowIDs,
		'rotate' => (0 < $arParams['ROTATE_TIMER']),
		'rotateTimer' => $arParams['ROTATE_TIMER']
	);
?>
<script type="text/javascript">
	var ob<? echo $strContID; ?> = new JCCatalogTopBannerList(<? echo CUtil::PhpToJSObject($arJSParams, false, true); ?>);
</script>
<?
}
?>