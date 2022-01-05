<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="catalog-top">

<table cellpadding="0" cellspacing="0" border="0">
	<?foreach($arResult["ROWS"] as $arItems):?>
		<tr valign="top">
		<?foreach($arItems as $arElement):?>
		<?
		$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arElement["IBLOCK_ID"], "ELEMENT_DELETE"));
		?>
		<?if(is_array($arElement)):?>
			<td width="<?=$arResult["TD_WIDTH"]?>" id="<?=$this->GetEditAreaId($arElement['ID']);?>">
				<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td valign="top">
					<?if(is_array($arElement["PREVIEW_PICTURE"])):?>
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><img border="0" src="<?=$arElement["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arElement["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arElement["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>" /></a>
					<?endif?>
					</td>
					<td valign="top">
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a><br />
						<?foreach($arElement["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
							<small><?=$arProperty["NAME"]?>:&nbsp;<?
								if(is_array($arProperty["DISPLAY_VALUE"]))
									echo implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
								else
									echo $arProperty["DISPLAY_VALUE"];?></small><br />
						<?endforeach?>
						<br />
						<?=$arElement["PREVIEW_TEXT"]?>
					</td>
				</tr>
				</table>
			</td>
		<?else:?>
			<td width="<?=$arResult["TD_WIDTH"]?>" rowspan="<?=$arResult["nRowsPerItem"]?>">
				&nbsp;
			</td>
		<?endif;?>
		<?endforeach?>
		</tr>
		<?if($arResult["bDisplayPrices"]):?>
			<tr valign="top">
			<?foreach($arItems as $arElement):?>
			<?if(is_array($arElement)):?>
				<td width="<?=$arResult["TD_WIDTH"]?>" class="data-cell">
				<?foreach($arElement["PRICES"] as $code=>$arPrice):?>
					<?if($arPrice["CAN_ACCESS"]):?>
						<p><?=$arResult["PRICES"][$code]["TITLE"];?>:&nbsp;&nbsp;
						<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
							<s><?=$arPrice["PRINT_VALUE"]?></s> <span class="catalog-price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
						<?else:?>
							<span class="catalog-price"><?=$arPrice["PRINT_VALUE"]?></span>
						<?endif?>
						</p>
					<?endif;?>
				<?endforeach;?>
				<?if(is_array($arElement["PRICE_MATRIX"])):?>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="data-table">
				<thead>
				<tr>
					<?if(count($arElement["PRICE_MATRIX"]["ROWS"]) >= 1 && ($arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
						<td><?=GetMessage("CATALOG_QUANTITY") ?></td>
					<?endif;?>
					<?foreach($arElement["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
						<td><?=$arType["NAME_LANG"] ?></td>
					<?endforeach?>
				</tr>
				</thead>
				<?foreach ($arElement["PRICE_MATRIX"]["ROWS"] as $ind => $arQuantity):?>
				<tr>
					<?if(count($arElement["PRICE_MATRIX"]["ROWS"]) > 1 || count($arElement["PRICE_MATRIX"]["ROWS"]) == 1 && ($arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_FROM"] > 0 || $arElement["PRICE_MATRIX"]["ROWS"][0]["QUANTITY_TO"] > 0)):?>
						<th nowrap>
						<?if(intval($arQuantity["QUANTITY_FROM"]) > 0 && intval($arQuantity["QUANTITY_TO"]) > 0)
								echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_FROM_TO")));
							elseif(intval($arQuantity["QUANTITY_FROM"]) > 0)
								echo str_replace("#FROM#", $arQuantity["QUANTITY_FROM"], GetMessage("CATALOG_QUANTITY_FROM"));
							elseif(intval($arQuantity["QUANTITY_TO"]) > 0)
								echo str_replace("#TO#", $arQuantity["QUANTITY_TO"], GetMessage("CATALOG_QUANTITY_TO"));
							?>
						</th>
					<?endif;?>
					<?foreach($arElement["PRICE_MATRIX"]["COLS"] as $typeID => $arType):?>
						<td>
							<?if($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"] < $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"])
								echo '<s>'.FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"]).'</s> <span class="catalog-price">'.FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["DISCOUNT_PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])."</span>";
							else
								echo '<span class="catalog-price">'.FormatCurrency($arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["PRICE"], $arElement["PRICE_MATRIX"]["MATRIX"][$typeID][$ind]["CURRENCY"])."</span>";
							?>&nbsp;
						</td>
					<?endforeach?>
				</tr>
				<?endforeach?>
				</table>
				<?endif?>
				</td>
			<?endif;?>
			<?endforeach?>
			</tr>
		<?endif;?>
		<?if($arResult["bDisplayButtons"]):?>
			<tr valign="top">
			<?foreach($arItems as $arElement):?>
			<?if(is_array($arElement)):?>
				<td>
				<?if($arParams["DISPLAY_COMPARE"]):?>
					<noindex>
					<a href="<?echo $arElement["COMPARE_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_COMPARE")?></a>&nbsp;
					</noindex>
				<?endif?>
				<?if($arElement["CAN_BUY"]):?>
					<?if($arParams["USE_PRODUCT_QUANTITY"] || count($arElement["PRODUCT_PROPERTIES"])):?>
						<form action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
						<table border="0" cellspacing="0" cellpadding="2">
						<?if($arParams["USE_PRODUCT_QUANTITY"]):?>
							<tr valign="top">
								<td><?echo GetMessage("CT_BCT_QUANTITY")?>:</td>
								<td>
									<input type="text" name="<?echo $arParams["PRODUCT_QUANTITY_VARIABLE"]?>" value="1" size="5"/>
								</td>
							</tr>
						<?endif;?>
						<?foreach($arElement["PRODUCT_PROPERTIES"] as $pid => $product_property):?>
							<tr valign="top">
								<td><?echo $arElement["PROPERTIES"][$pid]["NAME"]?>:</td>
								<td>
								<?if(
									$arElement["PROPERTIES"][$pid]["PROPERTY_TYPE"] == "L"
									&& $arElement["PROPERTIES"][$pid]["LIST_TYPE"] == "C"
								):?>
									<?foreach($product_property["VALUES"] as $k => $v):?>
										<label><input type="radio" name="<?echo $arParams["PRODUCT_PROPS_VARIABLE"]?>[<?echo $pid?>]" value="<?echo $k?>" <?if($k == $product_property["SELECTED"]) echo '"checked"'?>/><?echo $v?></label><br/>
									<?endforeach;?>
								<?else:?>
									<select name="<?echo $arParams["PRODUCT_PROPS_VARIABLE"]?>[<?echo $pid?>]">
										<?foreach($product_property["VALUES"] as $k => $v):?>
											<option value="<?echo $k?>" <?if($k == $product_property["SELECTED"]) echo '"selected"'?>><?echo $v?></option>
										<?endforeach;?>
									</select>
								<?endif;?>
								</td>
							</tr>
						<?endforeach;?>
						</table>
						<input type="hidden" name="<?echo $arParams["ACTION_VARIABLE"]?>" value="BUY"/>
						<input type="hidden" name="<?echo $arParams["PRODUCT_ID_VARIABLE"]?>" value="<?echo $arElement["ID"]?>"/>
						<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."BUY"?>" value="<?echo GetMessage("CATALOG_BUY")?>"/>
						<input type="submit" name="<?echo $arParams["ACTION_VARIABLE"]."ADD2BASKET"?>" value="<?echo GetMessage("CATALOG_ADD")?>"/>
						</form>
					<?else:?>
						<noindex>
						<a href="<?echo $arElement["BUY_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_BUY")?></a>
						&nbsp;<a href="<?echo $arElement["ADD_URL"]?>" rel="nofollow"><?echo GetMessage("CATALOG_ADD")?></a>
						</noindex>
					<?endif;?>
				<?elseif((count($arResult["PRICES"]) > 0) || is_array($arElement["PRICE_MATRIX"])):?>
					<?=GetMessage("CATALOG_NOT_AVAILABLE")?>
				<?endif?>
				</td>
			<?endif;?>
			<?endforeach?>
			</tr>
		<?endif;?>
	<?endforeach?>
</table>
</div>
