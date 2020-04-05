<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["ELEMENTS_LIST"]) || !is_array($arResult["ELEMENTS_LIST"])):
	return true;
endif;
if (!$this->__component->__parent || strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
?>
<style>
div.photo-gallery-avatar{
	position: relative;
	width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;
	height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;}
div.photo-gallery-avatar a {
	position: absolute;
	display: block;
	width:100%; 
	height: 100%;}
</style>
<?
endif;
/********************************************************************
				Input params
********************************************************************/
$temp = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBNAIL_SIZE"]));
list($temp["WIDTH"], $temp["HEIGHT"]) = explode("/", $temp["STRING"]);
$arParams["THUMBNAIL_SIZE"] = (intVal($temp["WIDTH"]) > 0 ? intVal($temp["WIDTH"]) : 120);
if ($arParams["PICTURES_SIGHT"] != "standart" && intVal($arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"]) > 0)
	$arParams["THUMBNAIL_SIZE"] = $arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"];

$arParams["SHOW_PAGE_NAVIGATION"] = (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("none", "top", "bottom", "both")) ? 
		$arParams["SHOW_PAGE_NAVIGATION"] : "bottom");
$arParams["FIXED_PARAMS"] = ($arParams["FIXED_PARAMS"] == "Y" ? "Y" : "N");
$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");
$arParams["SHOW_SHOWS"] = ($arParams["SHOW_SHOWS"] == "Y" ? "Y" : "N");
$arParams["SHOW_COMMENTS"] = ($arParams["SHOW_COMMENTS"] == "Y" ? "Y" : "N");

$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ?  $arParams["GALLERY_AVATAR_SIZE"] : 50);
/********************************************************************
				Input params
********************************************************************/

if (!empty($arResult["ERROR_MESSAGE"])):
?>
<div class="photo-info-box photo-error">
	<?=ShowError($arResult["ERROR_MESSAGE"])?>
</div>
<?
endif;

if (($arParams["SHOW_PAGE_NAVIGATION"] == "top" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation photo-navigation-top">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;

?>
<div class="photo-items-list photo-photo-list photo-simple-photo-list">
<?
foreach ($arResult["ELEMENTS_LIST"]	as $key => $arItem):
	if (!is_array($arItem))
		continue;
	if ($arItem["PICTURE"]["WIDTH"] > 0 || $arItem["PICTURE"]["HEIGHT"] > 0)
	{
		$coeff = $arParams["THUMBNAIL_SIZE"] / max($arItem["PICTURE"]["WIDTH"], $arItem["PICTURE"]["HEIGHT"]);
		if ($coeff < 1)
		{
			$arItem["PICTURE"]["WIDTH"] = intVal($coeff * $arItem["PICTURE"]["WIDTH"]);
			$arItem["PICTURE"]["HEIGHT"] = intVal($coeff * $arItem["PICTURE"]["HEIGHT"]);
		}
	}
	$sTitle = htmlspecialcharsEx($arItem["~NAME"]);
?>
	<div class="photo-photo-item photo-photo-item-simple">
		<table cellpadding="0" border="0" class="photo-table photo-photo-item-simple">
			<tr>
				<td>
<?
	if ($arParams["FIXED_PARAMS"] == "Y")
	{
?>
					<div class="photo-simple-photo"><?
						?><a href="<?=$arItem["URL"]?>" style="display:block;width:<?=$arItem["PICTURE"]["WIDTH"]?>px;height:<?=$arItem["PICTURE"]["HEIGHT"]?>px;"><?
							?><img src="<?=$arItem["PICTURE"]["SRC"]?>" width="<?=$arItem["PICTURE"]["WIDTH"]?>" height="<?=$arItem["PICTURE"]["HEIGHT"]?>" <?
								?>alt="<?=$sTitle?>" title="<?=$sTitle?>" border="0" /><?
						?></a><?
					?></div>
<?
	}
	else
	{
?>
					<div class="photo-simple-photo">
						<div class="photo-simple-photo" style="max-width:<?=$arItem["PICTURE"]["WIDTH"]?>px; <?
							?>width:expression(this.nextSibling.offsetWidth><?=$arItem["PICTURE"]["WIDTH"]?>?'<?=$arItem["PICTURE"]["WIDTH"]?>px':'auto');">
							<a href="<?=$arItem["URL"]?>" class="photo-simple-href" style="display:block;position:relative;overflow:hidden; <?
									?>height:<?=$arItem["PICTURE"]["HEIGHT"]?>px;">
								<img src="<?=$arItem["PICTURE"]["SRC"]?>" width="<?=$arItem["PICTURE"]["WIDTH"]?>" height="<?=$arItem["PICTURE"]["HEIGHT"]?>" <?
									?>alt="<?=$sTitle?>" title="<?=$sTitle?>" border="0" <?
									?>style="position:absolute;margin-left:-<?=intVal($arItem["PICTURE"]["WIDTH"]/2)?>px;left:50%;" />
							</a>
						</div>
						<div></div>
					</div>
<?
	}
?>
				</td>
			</tr>
			<tr>
				<td>
					<div class="photo-simple-info"<?
						if ($arParams["FIXED_PARAMS"] == "Y")
						{
							?> style="width:<?=$arItem["PICTURE"]["WIDTH"]?>px; overflow:hidden;"<?
						}
					?>><?
						?><div class="photo-photo-name"><a href="<?=$arItem["URL"]?>"><?=$arItem["NAME"]?></a></div><?
				if ($arParams["BEHAVIOUR"] == "USER")
				{
						?><div class="photo-gallery-info">
							<div class="photo-gallery-avatar" <?
						if (!empty($arItem["GALLERY"]["PICTURE"]["SRC"])):
								?>style="background-image:url('<?=$arItem["GALLERY"]["PICTURE"]["SRC"]?>');"<?
						endif;
							?> title="<?=GetMessage("P_VIEW_PHOTO")?>">
								<a href="<?=$arItem["GALLERY"]["URL"]?>" class="photo-gallery-avatar"><span></span></a>
							</div>
							<div class="photo-simple-gallery">
								<label><?=GetMessage("P_BY_AUTHOR")?></label>
								<span class="photo-gallery-name"><a href="<?=$arItem["GALLERY"]["URL"]?>"><?=$arItem["GALLERY"]["NAME"]?></a></span>
							</div>
						</div><?
				}
				
				if ($arParams["SHOW_SHOWS"] == "Y"):
?>
						<div class="photo-photo-shows"><?=GetMessage("P_SHOWS")?>: <?=intVal($arItem["SHOW_COUNTER"])?></div>
<?
				endif;
				if ($arParams["SHOW_COMMENTS"] == "Y"):
					$comments = intVal($arParams["COMMENTS_TYPE"] == "FORUM" ? $arItem["PROPERTIES"]["FORUM_MESSAGE_CNT"]["VALUE"] : 
					$arItem["PROPERTIES"]["BLOG_COMMENTS_CNT"]["VALUE"]); 
					if ($comments > 0 ):
?>
						<div class="photo-photo-comments"><?=GetMessage("P_COMMENTS")?>: <?=$comments?></div>
<?
					endif;
				endif;
				if ($arParams["SHOW_RATING"] == "Y"):
?>
						<div class="photo-rating">
							<?$APPLICATION->IncludeComponent(
								"bitrix:iblock.vote",
								"ajax",
								Array(
									"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
									"IBLOCK_ID" => $arParams["IBLOCK_ID"],
									"ELEMENT_ID" => $arItem["ID"],
									"MAX_VOTE" => $arParams["MAX_VOTE"],
									"VOTE_NAMES" => $arParams["VOTE_NAMES"],
									"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"], 
									"CACHE_TYPE" => $arParams["CACHE_TYPE"],
									"CACHE_TIME" => $arParams["CACHE_TIME"]
								),
								($this->__component->__parent ? $this->__component->__parent : $component),
								array("HIDE_ICONS" => "Y")
							);?>
						</div>
<?
				endif;
?>
					</div>
				</td>
			</tr>
		</table>
	</div>
<?
endforeach;
?>
	<div class="empty-clear"></div>
</div>
<?
if (($arParams["SHOW_PAGE_NAVIGATION"] == "bottom" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation photo-navigation-bottom">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;
?>