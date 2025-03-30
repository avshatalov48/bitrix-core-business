<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
if (empty($arResult['CATEGORIES']) || !$arResult['CATEGORIES_ITEMS_EXISTS'])
{
	return;
}
?>
<div class="bx_searche">
<?php foreach ($arResult['CATEGORIES'] as $category_id => $arCategory):?>
	<?php foreach ($arCategory['ITEMS'] as $i => $arItem):?>
		<?php //echo $arCategory["TITLE"]?>
		<?php if ($category_id === 'all'):?>
			<div class="bx_item_block" style="min-height:0">
				<div class="bx_img_element"></div>
				<div class="bx_item_element"><hr></div>
			</div>
			<div class="bx_item_block all_result">
				<div class="bx_img_element"></div>
				<div class="bx_item_element">
					<span class="all_result_title"><a href="<?php echo $arItem['URL']?>"><?php echo $arItem['NAME']?></a></span>
				</div>
				<div style="clear:both;"></div>
			</div>
		<?php elseif (isset($arResult['ELEMENTS'][$arItem['ITEM_ID']])):
			$arElement = $arResult['ELEMENTS'][$arItem['ITEM_ID']];?>
			<div class="bx_item_block">
				<?php if (is_array($arElement['PICTURE'])):?>
				<div class="bx_img_element">
					<div class="bx_image" style="background-image: url('<?php echo $arElement['PICTURE']['src']?>')"></div>
				</div>
				<?php endif;?>
				<div class="bx_item_element">
					<a href="<?php echo $arItem['URL']?>"><?php echo $arItem['NAME']?></a>
					<?php
					foreach ($arElement['PRICES'] as $code => $arPrice)
					{
						if ($arPrice['MIN_PRICE'] != 'Y')
						{
							continue;
						}

						if ($arPrice['CAN_ACCESS'])
						{
							if ($arPrice['DISCOUNT_VALUE'] < $arPrice['VALUE']):?>
								<div class="bx_price">
									<?=$arPrice['PRINT_DISCOUNT_VALUE']?>
									<span class="old"><?=$arPrice['PRINT_VALUE']?></span>
								</div>
							<?php else:?>
								<div class="bx_price"><?=$arPrice['PRINT_VALUE']?></div>
							<?php endif;
						}
						if ($arPrice['MIN_PRICE'] == 'Y')
						{
							break;
						}
					}
					?>
				</div>
				<div style="clear:both;"></div>
			</div>
		<?php else:?>
			<div class="bx_item_block others_result">
				<div class="bx_img_element"></div>
				<div class="bx_item_element">
					<a href="<?php echo $arItem['URL']?>"><?php echo $arItem['NAME']?></a>
				</div>
				<div style="clear:both;"></div>
			</div>
		<?php endif;?>
	<?php endforeach;?>
<?php endforeach;?>
</div>
