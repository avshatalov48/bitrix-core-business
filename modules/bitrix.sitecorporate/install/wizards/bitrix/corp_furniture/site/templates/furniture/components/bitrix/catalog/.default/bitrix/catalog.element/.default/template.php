<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="catalog-detail">
	<div class="catalog-item">
<?
$width = 0;
if($arParams['DETAIL_SHOW_PICTURE'] != 'N' && (is_array($arResult["PREVIEW_PICTURE"]) || is_array($arResult["DETAIL_PICTURE"]))):
?>
		<div class="catalog-item-image">
<?
	if(is_array($arResult["DETAIL_PICTURE"])):
		$width = $arResult["DETAIL_PICTURE"]["WIDTH"];
?>
			<img border="0" src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" width="<?=$arResult["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arResult["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" />
<?
	elseif(is_array($arResult["PREVIEW_PICTURE"])):
		$width = $arResult["PREVIEW_PICTURE"]["WIDTH"];
?>
			<img border="0" src="<?=$arResult["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arResult["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arResult["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" />
<?
	endif;
?>
		</div>
<?
endif;
?>
		<div class="catalog-item-desc<?=$width < 300 ? '-float' : ''?>">
<?
if($arResult["DETAIL_TEXT"]):
	echo $arResult["DETAIL_TEXT"];
elseif($arResult["PREVIEW_TEXT"]):
	echo $arResult["PREVIEW_TEXT"];
endif;
?>
		</div>
<?
foreach($arResult["PRICES"] as $code=>$arPrice):
?>
	<?if($arPrice["PRINT_VALUE"] > 0):?>
		<div class="catalog-item-price"><span><?=GetMessage('CR_PRICE')?>:</span> <?=$arPrice["PRINT_VALUE"]?></div>
	<?endif;?>
<?
endforeach;
?>

<?
if (is_array($arResult['DISPLAY_PROPERTIES']) && count($arResult['DISPLAY_PROPERTIES']) > 0):
	$cnt = 0;
	foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):
		if ($pid != 'PRICE' && $pid != 'PRICECURRENCY'):
			if ($cnt == 0):
				$cnt++;
?>
		<div class="catalog-item-properties">
			<div class="catalog-item-properties-title"><?=GetMessage("CATALOG_CHAR")?></div>
<?
			endif;
?>

			<div class="catalog-item-property">
				<span><?=$arProperty["NAME"]?></span>
				<b><?
			if(is_array($arProperty["DISPLAY_VALUE"])):
				echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
			elseif($pid=="MANUAL"):
?>
					<a href="<?=$arProperty["VALUE"]?>"><?=GetMessage("CATALOG_DOWNLOAD")?></a>
<?
			else:
				echo $arProperty["DISPLAY_VALUE"];
			endif;
				?></b>
			</div>
<?
		endif;
	endforeach;
	
	if ($cnt > 0):
?>
		</div>
<?
	endif;
endif;

if(is_array($arResult["SECTION"])):
?>
		<br /><a href="<?=$arResult["SECTION"]["SECTION_PAGE_URL"]?>">&larr; <?=GetMessage("CATALOG_BACK")?></a>
<?
elseif (is_array($arResult['IBLOCK'])):
?>
		<br /><a href="<?=$arResult["IBLOCK"]["LIST_PAGE_URL"]?>">&larr; <?=GetMessage("CATALOG_BACK")?></a>
<?
endif;
?>
	</div>
</div>
