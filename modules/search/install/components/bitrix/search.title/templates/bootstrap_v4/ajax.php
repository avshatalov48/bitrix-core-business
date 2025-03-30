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
<div class="search-title">
<?php foreach ($arResult['CATEGORIES'] as $category_id => $arCategory):?>
	<?php foreach ($arCategory['ITEMS'] as $i => $arItem):?>
		<?php if ($category_id === 'all'):
			?>
			<div class="search-title-result-item py-2 d-flex align-items-center">
				<div class="search-title-result-item-info flex-grow-1">
					<a class="search-title-result-item-link" href="<?php echo $arItem['URL']?>"><?php echo $arItem['NAME']?></a>
				</div>
			</div>
		<?php elseif (isset($arResult['ELEMENTS'][$arItem['ITEM_ID']])):
			$arElement = $arResult['ELEMENTS'][$arItem['ITEM_ID']];?>
			<div class="search-title-result-item py-2 d-flex align-items-center">

				<?php if (is_array($arElement['PICTURE'])):?>
					<div class="search-title-result-item-image-container pr-2">
						<div class="search-title-result-item-image"
							style="
								background-image: url('<?php echo $arElement['PICTURE']['src']?>');
								width:<?=$arElement['PICTURE']['width']?>px;
								height:<?=$arElement['PICTURE']['height']?>px;
								">
						</div>
					</div>
				<?php endif;?>

				<div class="search-title-result-item-info flex-grow-1">
					<a class="search-title-result-item-link" href="<?php echo $arItem['URL']?>"><?php echo $arItem['NAME']?></a>
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
								<div class="search-title-result-item-price">
									<span class="search-title-result-item-current-price text-primary"><?=$arPrice['PRINT_DISCOUNT_VALUE']?></span>
									<span class="search-title-result-item-old-price text-muted"><?=$arPrice['PRINT_VALUE']?></span>
								</div>
							<?php else:?>
								<div class="search-title-result-item-price">
									<span class="search-title-result-item-current-price text-primary"><?=$arPrice['PRINT_VALUE']?></span>
								</div>
							<?php endif;
						}
						if ($arPrice['MIN_PRICE'] == 'Y')
						{
							break;
						}
					}
					?>
				</div>
			</div>
		<?php else:?>
			<div class="search-title-result-item pt-2 search-title-result-last-item">
				<div class="search-title-result-item-image"></div>
				<div class="bx_item_element">
					<a class="search-title-result-item-link" href="<?php echo $arItem['URL']?>"><?php echo $arItem['NAME']?></a>
				</div>
			</div>
		<?php endif;?>
	<?php endforeach;?>
<?php endforeach;?>
</div>
