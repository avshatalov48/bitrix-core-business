<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->SetPageProperty("BodyClass", "detail");?>
<div class="account_item_description">
	<h1><?=GetMessage("SALE_ACCOUNT_TITLE")?></h1>
</div>
<div class="account_item_description">
	<h3><?=GetMessage("SALE_ACCOUNT")?>:</h3>
	<div class="account_item_description_text">
<?if(strlen($arResult["ERROR_MESSAGE"])<=0):
	?>
		<p class="p_small"><?echo $arResult["DATE"]; ?></p>
	<?
	foreach($arResult["ACCOUNT_LIST"] as $val)
	{
		?>
		<?=$val["INFO"]?>
		<?
	}
	?>

	<?
else:
	echo ShowError($arResult["ERROR_MESSAGE"]);
endif;?>
	</div>
</div>