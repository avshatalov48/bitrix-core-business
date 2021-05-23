<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="notetext">
<?
if (!empty($arResult["ORDER"]))
{
	?>
	<b><?=GetMessage("SOA_TEMPL_ORDER_COMPLETE")?></b><br /><br />
	<table class="sale_order_full_table">
		<tr>
			<td>
				<?= GetMessage("SOA_TEMPL_ORDER_SUC", Array("#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"], "#ORDER_ID#" => $arResult["ORDER_BASKET"]["ORDER_ID"]))?><br /><br />
				<?= GetMessage("SOA_TEMPL_ORDER_SUC1", Array("#LINK#" => $arParams["PATH_TO_PERSONAL"])) ?>
			</td>
		</tr>
	</table>
	<?
	if (!empty($arResult["PAY_SYSTEM"]))
	{
		?>
		<br /><br />

		<table class="sale_order_full_table">
			<tr>
				<td>
					<?=GetMessage("SOA_TEMPL_PAY")?>: <?= $arResult["PAY_SYSTEM"]["NAME"] ?>
				</td>
			</tr>
			<?
			if ($arResult["PAY_SYSTEM"]["ACTION_FILE"] <> '')
			{
				?>
				<tr>
					<td>
						<?
						if ($arResult["PAY_SYSTEM"]["NEW_WINDOW"] == "Y")
						{
							?>
							<script language="JavaScript">
								window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?= $arResult["ORDER_BASKET"]["ORDER_ID"] ?>');
							</script>
							<?= GetMessage("SOA_TEMPL_PAY_LINK", Array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$arResult["ORDER_BASKET"]["ORDER_ID"])) ?>
							<?
						}
						else
						{
							$service = \Bitrix\Sale\PaySystem\Manager::getObjectById($arResult['PAY_SYSTEM_ID']);

							if ($service)
							{
								$orderId = $arResult['ORDER_ID'];
								$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

								/** @var \Bitrix\Sale\Order $orderClass */
								$orderClass = $registry->getOrderClassName();

								/** @var \Bitrix\Sale\Order $order */
								$order = $orderClass::load($orderId);

								if ($order === null)
								{
									$data = \Bitrix\Sale\Internals\OrderTable::getRow(array(
										'select' => array('ID'),
										'filter' => array('ACCOUNT_NUMBER' => $orderId)
									));

									$order = $orderClass::load($data['ID']);
								}

								/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
								$paymentCollection = $order->getPaymentCollection();

								/** @var \Bitrix\Sale\Payment $payment */
								foreach ($paymentCollection as $payment)
								{
									if (!$payment->isInner())
									{
										$context = \Bitrix\Main\Application::getInstance()->getContext();
										$service->initiatePay($payment, $context->getRequest());
										break;
									}
								}
							}
							else
							{
								echo '<span style="color:red;">'.GetMessage("SOA_TEMPL_ORDER_PS_ERROR").'</span>';
							}
						}
						?>
					</td>
				</tr>
				<?
			}
			?>
		</table>

		<?
		if ($arResult["ERR_ACCOUNT"] == "Y")
			echo "<div>".GetMessage("NEWO_PAY_FROM_ACCOUNT_ERR")."</div>";
	}
}
else
{
	?>
	<b><?=GetMessage("SOA_TEMPL_ERROR_ORDER")?></b><br /><br />

	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=GetMessage("SOA_TEMPL_ERROR_ORDER_LOST", Array("#ORDER_ID#" => $arResult["ORDER_ID"]))?>
				<?=GetMessage("SOA_TEMPL_ERROR_ORDER_LOST1")?>
			</td>
		</tr>
	</table>
	<?
}
?>
</div>