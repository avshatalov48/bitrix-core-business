<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="catalog-list">
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>


<?
foreach($arResult["ITEMS"] as $cell=>$arElement):
	$width = 0;
	$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CATALOG_ELEMENT_DELETE_CONFIRM')));
?>
<div class="catalog-item" id="<?=$this->GetEditAreaId($arElement['ID']);?>">
<?
	if(is_array($arElement["PREVIEW_PICTURE"])):
		$width = $arElement["PREVIEW_PICTURE"]["WIDTH"];
?>
	<div class="catalog-item-image">
		<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img src="<?=$arElement["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arElement["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arElement["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>" /></a>
	</div>
<?
	elseif(is_array($arElement["DETAIL_PICTURE"])):
		$width = $arElement["DETAIL_PICTURE"]["WIDTH"];
?>
	<div class="catalog-item-image">
		<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img src="<?=$arElement["DETAIL_PICTURE"]["SRC"]?>" width="<?=$arElement["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arElement["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>" /></a>
	</div>
<?
	endif;
?>
	<div class="catalog-item-title"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a></div>
<?
	foreach($arElement["DISPLAY_PROPERTIES"] as $pid=>$arProperty):
		if ($pid != 'PRICECURRENCY'):
?>
		<?=$arProperty["NAME"]?>:&nbsp;<?
			if(is_array($arProperty["DISPLAY_VALUE"]))
				echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
			else
				echo $arProperty["DISPLAY_VALUE"];?><br />
<?
		endif;
	endforeach;
?>
	<div class="catalog-item-desc<?=$width < 300 ? '-float' : ''?>">
		<?=$arElement["PREVIEW_TEXT"]?>
	</div>

<?
	foreach($arElement["PRICES"] as $code=>$arPrice):
		if($arPrice["CAN_ACCESS"]):
?>
	<div class="catalog-item-price"><span><?=$arResult["PRICES"][$code]["TITLE"];?>:</span> <?=$arPrice["PRINT_VALUE"]?></div>
<?
		endif;
	endforeach;
?>
</div>
<?
endforeach; // foreach($arResult["ITEMS"] as $arElement):
?>

<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>
</div>
