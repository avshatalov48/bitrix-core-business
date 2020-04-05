<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<div class="view-list">
	<div class="view-header"><?=GetMessage("VIEW_HEADER");?></div>
	<?foreach($arResult as $arItem):?>
		<div class="view-item">
			<?if($arParams["VIEWED_IMAGE"]=="Y" && is_array($arItem["PICTURE"])):?>
				<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><img src="<?=$arItem["PICTURE"]["src"]?>" alt="<?=$arItem["NAME"]?>" title="<?=$arItem["NAME"]?>"></a>
			<?endif?>
			<?if($arParams["VIEWED_NAME"]=="Y"):?>
				<div><a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a></div>
			<?endif?>
			<?if($arParams["VIEWED_PRICE"]=="Y" && $arItem["CAN_BUY"]=="Y"):?>
				<div><?=$arItem["PRICE_FORMATED"]?></div>
			<?endif?>
			<?if($arParams["VIEWED_CANBUY"]=="Y" && $arItem["CAN_BUY"]=="Y"):?>
				<noindex>
					<a href="<?=$arItem["BUY_URL"]?>" rel="nofollow"><?=GetMessage("PRODUCT_BUY")?></a>
				</noindex>
			<?endif?>
			<?if($arParams["VIEWED_CANBASKET"]=="Y" && $arItem["CAN_BUY"]=="Y"):?>
				<noindex>
					<a href="<?=$arItem["ADD_URL"]?>" rel="nofollow"><?=GetMessage("PRODUCT_BASKET")?></a>
				</noindex>
			<?endif?>
		</div>
	<?endforeach;?>
</div>
<?endif;?>