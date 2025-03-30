<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!empty($arResult['CATEGORIES']) && $arResult['CATEGORIES_ITEMS_EXISTS']):?>
	<table class="title-search-result">
		<?php foreach ($arResult['CATEGORIES'] as $category_id => $arCategory):?>
			<tr>
				<th class="title-search-separator">&nbsp;</th>
				<td class="title-search-separator">&nbsp;</td>
			</tr>
			<?php foreach ($arCategory['ITEMS'] as $i => $arItem):?>
			<tr>
				<?php if ($i == 0):?>
					<th>&nbsp;<?php echo $arCategory['TITLE']?></th>
				<?php else:?>
					<th>&nbsp;</th>
				<?php endif?>

				<?php if ($category_id === 'all'):?>
					<td class="title-search-all"><a href="<?php echo $arItem['URL']?>"><?php echo $arItem['NAME']?></a></td>
				<?php elseif (isset($arItem['ICON'])):?>
					<td class="title-search-item"><a href="<?php echo $arItem['URL']?>"><img src="<?php echo $arItem['ICON']?>"><?php echo $arItem['NAME']?></a></td>
				<?php else:?>
					<td class="title-search-more"><a href="<?php echo $arItem['URL']?>"><?php echo $arItem['NAME']?></a></td>
				<?php endif;?>
			</tr>
			<?php endforeach;?>
		<?php endforeach;?>
		<tr>
			<th class="title-search-separator">&nbsp;</th>
			<td class="title-search-separator">&nbsp;</td>
		</tr>
	</table><div class="title-search-fader"></div>
<?php endif;
