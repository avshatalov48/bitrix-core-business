<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

foreach ($arResult["BASKET"] as $product)
{
?>
<div class="order_detail_container">
	<div class="order_infoblock_title"><?=$product["INFO"]["NAME"]?></div>
		<table class="order_detail_table">
			<tr>
				<td class="order_detail_table_td_img">
					<?
					$productImg = getSaleProductImage($product);
					if($productImg):?>
						<img src="<?=$productImg["src"]?>" alt="<?=$product["INFO"]["NAME"]?>">
					<?else:?>
						<div class="no_foto"><?=GetMessage('SMPL_IMAGE_ABSENT');?></div>
					<?endif;?>
				</td>
				<td>
					<table class="order_detail_table_td_table">
						<thead>
							<tr>
								<td><?=GetMessage('SMPL_AMOUNT');?></td>
								<td><?=GetMessage('SMPL_BALANCE');?></td>
								<td><?=GetMessage('SMPL_PRICE');?></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?=$product["QUANTITY"]?></td>
								<td><?=$product["BALANCE"]?></td>
								<td>
											<span class="price"><?=$product["PRICE_STRING"]?></span>
									<?if(isset($product["OLD_PRICE_STRING"])):?>
										<br />	<span class="price_old"><?=$product["OLD_PRICE_STRING"]?></span>
										<br />	<span class="price_sale"><?=GetMessage('SMPL_DISCOUNT');?>: <?=$product["DISCOUNT_STRING"]?></span>
									<?endif;?>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
	<?if (is_array($product["PROPS"]) && !empty($product["PROPS"])):?>
		<div class="order_detail_list_description_text">
			<div class="order_detail_list_description_title"><?=GetMessage('SMPL_PROPERTIES');?>:</div>
			<ul>
				<?
						foreach($product["PROPS"] as $vv)
							if($vv["VALUE"] <> '')
								echo "<li>".$vv["NAME"].": ".$vv["VALUE"]."</li>";
				?>
			</ul>
			<div class="clb"></div>
		</div>
	<?endif;?>
</div>
<?
}

function getSaleProductImage($product)
{
	$productImg = '';

	if($product["INFO"]["PREVIEW_PICTURE"] != "")
		$productImg = $product["INFO"]["PREVIEW_PICTURE"];
	elseif($product["INFO"]["DETAIL_PICTURE"] != "")
		$productImg = $product["INFO"]["DETAIL_PICTURE"];

	if (empty($productImg) && CModule::IncludeModule("catalog"))
	{
		$arParent = CCatalogSku::GetProductInfo($product["PRODUCT_ID"]);

		if(intval($arParent["ID"]) > 0)
		{
			$arProductData = getProductProps(array($arParent["ID"]), array("ID", "PREVIEW_PICTURE", "DETAIL_PICTURE", "IBLOCK_TYPE_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID"));

			if(!empty($arProductData[$arParent["ID"]]["PREVIEW_PICTURE"]))
				$productImg = $arProductData[$arParent["ID"]]["PREVIEW_PICTURE"];
			elseif(!empty($arProductData[$arParent["ID"]]["DETAIL_PICTURE"]))
				$productImg = $arProductData[$arParent["ID"]]["DETAIL_PICTURE"];
		}
	}

	if ($productImg != "")
	{
		$arFile = CFile::GetFileArray($productImg);
		$productImg = CFile::ResizeImageGet(
											$arFile,
											array('width'=>80, 'height'=>80),
											BX_RESIZE_IMAGE_PROPORTIONAL,
											false,
											false
		);
	}

	return $productImg;
}
/*
<!--<div class="order_detail_container_itogi">
	<table class="order_detail_container_itogi_coupon">
		<tr>
			<td><?=GetMessage('SMPL_COUPON');?></td>
			<td>
				<div class="order_detail_container_coupon_input_container">
					<input type="text">
				</div>
			</td>
			<td><input type="button" class="order_detail_container_coupon_button" value="<?=GetMessage('SMPL_RECALCULATION');?>"></td>
		</tr>
	</table>
</div>-->
*/
?>