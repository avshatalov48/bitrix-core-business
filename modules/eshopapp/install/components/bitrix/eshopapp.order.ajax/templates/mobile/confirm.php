<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->SetPageProperty("BodyClass", "detail");
if (!empty($arResult["ORDER"]))
{
	?>
	<div class="order_item_description">
		<h3><?=GetMessage("SOA_TEMPL_ORDER_COMPLETE")?></h3>
		<div class="ordering_container">
			<p><?
			$orderID = ($arParams["SHOW_ACCOUNT_NUMBER"] == "Y") ? $arResult["ORDER"]["ACCOUNT_NUMBER"] : $arResult["ORDER_ID"];
			echo GetMessage("SOA_TEMPL_ORDER_SUC", Array("#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"], "#ORDER_ID#" => $orderID))?></p>
			<p><?= GetMessage("SOA_TEMPL_ORDER_SUC1", Array("#LINK#" => $arParams["PATH_TO_PERSONAL"])) ?></p>
		</div>
	</div>
	<?
	if (!empty($arResult["PAY_SYSTEM"]))
	{
		?>
	<div class="order_item_description">
		<h3><?=GetMessage("SALE_PAY")?></h3>
		<div class="ordering_container">
		<p><?=GetMessage("SOA_TEMPL_PAY")?>: <?= $arResult["PAY_SYSTEM"]["NAME"] ?></p>
		<?
		if (strlen($arResult["PAY_SYSTEM"]["ACTION_FILE"]) > 0)
		{
			?>
			<?
			if ($arResult["PAY_SYSTEM"]["NEW_WINDOW"] == "Y")
			{
				$orderID = ($arParams["SHOW_ACCOUNT_NUMBER"] == "Y") ? urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"])) : $arResult["ORDER_ID"];
				?>
				<p><?= GetMessage("SOA_TEMPL_PAY_LINK", Array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderID)) ?></p>
				<?
			}
			else
			{
				if (strlen($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"])>0)
				{
					include($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"]);
				}
			}
			?>
			<?
		}
		?>
		</div>
	</div>
	<?
	}
}
else
{
	?>
	<div class="order_item_description">
		<h3><?=GetMessage("SOA_TEMPL_ERROR_ORDER")?></h3>
		<div class="ordering_container">
			<p><?=GetMessage("SOA_TEMPL_ERROR_ORDER_LOST", Array("#ORDER_ID#" => $arResult["ORDER_ID"]))?></p>
			<p><?=GetMessage("SOA_TEMPL_ERROR_ORDER_LOST1")?></p>
		</div>
	</div>
	<?
}
?>