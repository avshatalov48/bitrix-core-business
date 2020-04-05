<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<div class="bx_small_cart">

	<span class="icon_cart"></span>

	<?if ($arResult['NUM_PRODUCTS'] > 0 && $arParams['SHOW_NUM_PRODUCTS'] == 'N' && $arParams['SHOW_TOTAL_PRICE'] == 'N'):?>
		<a href="<?=$arParams['PATH_TO_BASKET']?>"><?=GetMessage('TSB1_CART')?></a>
	<?else: echo GetMessage('TSB1_CART'); endif?>

	<?if($arParams['SHOW_NUM_PRODUCTS'] == 'Y'):?>
		<?if ($arResult['NUM_PRODUCTS'] > 0):?>
			<a href="<?=$arParams['PATH_TO_BASKET']?>"><?=$arResult['NUM_PRODUCTS'].' '.$arResult['PRODUCT(S)']?></a>
		<?else:?>
			<?=$arResult['NUM_PRODUCTS'].' '.$arResult['PRODUCT(S)']?>
		<?endif?>
	<?endif?>

	<?if($arParams['SHOW_TOTAL_PRICE'] == 'Y'):?>
		<br>
		<span class="icon_spacer"></span> <?=GetMessage('TSB1_TOTAL_PRICE')?>
		<?if ($arResult['NUM_PRODUCTS'] > 0):?>
			<a href="<?=$arParams['PATH_TO_BASKET']?>"><?=$arResult['TOTAL_PRICE']?></a>
		<?else:?>
			<?=$arResult['TOTAL_PRICE']?>
		<?endif?>
	<?endif?>


	<?if($arParams["SHOW_PERSONAL_LINK"] == "Y"):?>
		<br>
		<span class="icon_profile"></span>
		<a class="link_profile" href="<?=$arParams["PATH_TO_PERSONAL"]?>"><?=GetMessage("TSB1_PERSONAL")?></a>
	<?endif?>

	<?if ($arParams["SHOW_PRODUCTS"] == "Y" && $arResult['NUM_PRODUCTS'] > 0):?>
		<div class="bx_item_hr" style="margin-bottom:0"></div>
	<?endif?>

</div>