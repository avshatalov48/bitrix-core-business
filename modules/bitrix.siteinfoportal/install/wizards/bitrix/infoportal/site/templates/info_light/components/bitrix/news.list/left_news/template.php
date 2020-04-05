<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(count($arResult["ITEMS"])):?>
<div class="news-list national-news">
<div class="main-news-title"><h2><?=$arParams["NAME_BLOCK"]?></h2></div>
<table cellpadding="0" cellspacing="0" border="0">
<?foreach($arResult["ITEMS"] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"));
	?>
	<?if($cell%$arParams["LINE_NEWS_COUNT"] == 0):?>
	<tr>
	<?endif;?>

	<td valign="top" width="<?=round(100/$arParams["LINE_NEWS_COUNT"])?>%" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
		<div class="news-item <?if($cell%$arParams["LINE_NEWS_COUNT"] == 0):?>news-item-left<?endif;?>">
			<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_IMG_SMALL"])):?>
			<div class="news-picture">
				<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
					<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><img class="preview_picture" border="0" src="<?=$arItem["PREVIEW_IMG_SMALL"]["SRC"]?>" width="<?=$arItem["PREVIEW_IMG_SMALL"]["WIDTH"]?>" height="<?=$arItem["PREVIEW_IMG_SMALL"]["HEIGHT"]?>" alt="<?=$arItem["NAME"]?>" title="<?=$arItem["NAME"]?>" /></a>
				<?else:?>
					<img class="preview_picture" border="0" src="<?=$arItem["PREVIEW_IMG_SMALL"]["SRC"]?>" width="<?=$arItem["PREVIEW_IMG_SMALL"]["WIDTH"]?>" height="<?=$arItem["PREVIEW_IMG_SMALL"]["HEIGHT"]?>" alt="<?=$arItem["NAME"]?>" title="<?=$arItem["NAME"]?>"/>
				<?endif;?>
			</div>
			<?endif?>
			<div class="news-text">
			<?if($arParams["DISPLAY_DATE"]!="N" && $arItem["DISPLAY_ACTIVE_FROM"]):?>
				<span class="news-date-time"><?=$arItem["DISPLAY_ACTIVE_FROM"]?></span><br/>
			<?endif?>
			<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
				<div class="news-name">
				<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
					<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a><br />
				<?else:?>
					<b><?echo $arItem["NAME"]?></b><br />
				<?endif;?>
				</div>
			<?endif;?>
			<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
				<span class="news-preview-text"><?=$arItem["PREVIEW_TEXT"];?></span>
			<?endif;?>
			<?//print_r($arItem);?>
			<?if(isset($arItem["SHOW_COUNTER"])):?>
			<?if($arItem["SHOW_COUNTER"] == '') $arItem["SHOW_COUNTER"] = 0;?>
			<span class="news-show-counter"><?=GetMessage("SHOW_COUNTER_TITLE")?><?=$arItem["SHOW_COUNTER"]?></span>
			<?endif;?>
			
			</div>
		</div>
	<?$cell++;
	if($cell%$arParams["LINE_NEWS_COUNT"] == 0):?>
		</tr>
	<?endif?>

<?endforeach; // foreach($arResult["ITEMS"] as $arElement):?>

<?if($cell%$arParams["LINE_NEWS_COUNT"] != 0):?>
	<?while(($cell++)%$arParams["LINE_NEWS_COUNT"] != 0):?>
		<td>&nbsp;</td>
	<?endwhile;?>
	</tr>
<?endif?>
</table>
</div>
<?endif?>