<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!empty($arResult["CATEGORIES"]) && $arResult['CATEGORIES_ITEMS_EXISTS']):?>
	<table class="title-search-result">
		<?foreach($arResult["CATEGORIES"] as $category_id => $arCategory):?>
			<tr>
				<th class="title-search-separator">&nbsp;</th>
				<td class="title-search-separator">&nbsp;</td>
			</tr>
			<?foreach($arCategory["ITEMS"] as $i => $arItem):?>
			<tr>
				<?if($i == 0):?>
					<th>&nbsp;<?echo $arCategory["TITLE"]?></th>
				<?else:?>
					<th>&nbsp;</th>
				<?endif?>

				<?if($category_id === "all"):?>
					<td class="title-search-all"><a href="<?echo $arItem["URL"]?>"><?echo $arItem["NAME"]?></a></td>
				<?elseif(isset($arResult["ELEMENTS"][$arItem["ITEM_ID"]])):
					$arElement = $arResult["ELEMENTS"][$arItem["ITEM_ID"]];
				?>
					<td class="title-search-item"><a href="<?echo $arItem["URL"]?>"><?
						if (is_array($arElement["PICTURE"])):?>
							<img align="left" src="<?echo $arElement["PICTURE"]["src"]?>" width="<?echo $arElement["PICTURE"]["width"]?>" height="<?echo $arElement["PICTURE"]["height"]?>">
						<?endif;?><?echo $arItem["NAME"]?></a>
						<p class="title-search-preview"><?echo $arElement["PREVIEW_TEXT"];?></p>
						<?foreach($arElement["PRICES"] as $code=>$arPrice):?>
							<?if($arPrice["CAN_ACCESS"]):?>
								<p class="title-search-price"><?=$arResult["PRICES"][$code]["TITLE"];?>:&nbsp;&nbsp;
								<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
									<s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
								<?else:?><span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span><?endif;?>
								</p>
							<?endif;?>
						<?endforeach;?>
					</td>
				<?elseif(isset($arItem["ICON"])):?>
					<td class="title-search-item"><a href="<?echo $arItem["URL"]?>"><?echo $arItem["NAME"]?></a></td>
				<?else:?>
					<td class="title-search-more"><a href="<?echo $arItem["URL"]?>"><?echo $arItem["NAME"]?></a></td>
				<?endif;?>
			</tr>
			<?endforeach;?>
		<?endforeach;?>
		<tr>
			<th class="title-search-separator">&nbsp;</th>
			<td class="title-search-separator">&nbsp;</td>
		</tr>
	</table><div class="title-search-fader"></div>
<?endif;
?>