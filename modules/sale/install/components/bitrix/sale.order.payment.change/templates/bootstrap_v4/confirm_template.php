<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Localization\Loc;

if (!empty($arResult["errorMessage"]))
{
	if (!is_array($arResult["errorMessage"]))
	{
		ShowError($arResult["errorMessage"]);
	}
	else
	{
		foreach ($arResult["errorMessage"] as $errorMessage)
		{
			ShowError($errorMessage);
		}
	}
}
else
{
	if ($arResult['IS_ALLOW_PAY'] == 'N')
	{
		?>
		<div class="sale-paysystem-wrapper">
			<p><b><?=Loc::getMessage("SOPC_PAY_SYSTEM_CHANGED")?></b></p>
			<p><?=Loc::getMessage("SOPC_PAY_SYSTEM_NOT_ALLOW_PAY")?></p>
		</div>
		<?
	}
	elseif ($arResult['SHOW_INNER_TEMPLATE'] == 'Y')
	{
		?>
		<div class="bx-sopc" id="bx-sopc<?=$wrapperId?>">
			<div class="sale-paysystem-wrapper">
				<div class="col">
					<div class="sale-order-payment-change-pp row">
						<div class="row sale-order-payment-change-inner-row">
							<div class="sale-order-payment-change-inner-row-body">
								<div class="col-xs-12 sale-order-inner-padding-bottom">
									<div class="sale-order-payment-change-payment-title sale-order-inner-padding-bottom">
										<?
											$paymentSubTitle = Loc::getMessage('SOPC_TPL_BILL')." ".Loc::getMessage('SOPC_TPL_NUMBER_SIGN').$arResult['PAYMENT']['ACCOUNT_NUMBER'];
											if(isset($arResult['PAYMENT']['DATE_BILL']))
											{
												$paymentSubTitle .= " ".Loc::getMessage('SOPC_TPL_FROM_DATE')." ".$arResult['PAYMENT']['DATE_BILL']->format("d.m.Y");
											}
											echo $paymentSubTitle;
										?>
									</div>
									<div class="sale-order-payment-change-payment-price">
										<span class="sale-order-payment-change-payment-element"><?=Loc::getMessage('SOPC_TPL_SUM_TO_PAID')?>:</span>

										<span class="sale-order-payment-change-payment-number"><?=SaleFormatCurrency($arResult['PAYMENT']["SUM"], $arResult['PAYMENT']["CURRENCY"])?></span>
									</div>
									<div class="sale-order-payment-change-payment-price sale-order-inner-padding-bottom">
										<span class="sale-order-payment-change-payment-element"><?=Loc::getMessage('SOPC_INNER_BALANCE')?>:</span>

										<span class="sale-order-payment-change-payment-number"><?=SaleFormatCurrency($arResult['INNER_PAYMENT_INFO']['CURRENT_BUDGET'], $arResult['INNER_PAYMENT_INFO']["CURRENCY"])?></span>
									</div>

									<?
									$inputSum = $arResult['INNER_PAYMENT_INFO']['CURRENT_BUDGET'] > $arResult['PAYMENT']["SUM"] ?  $arResult['PAYMENT']["SUM"] : $arResult['INNER_PAYMENT_INFO']['CURRENT_BUDGET'];

									if (
										($arParams['ONLY_INNER_FULL'] !== 'Y' &&(float)$arResult['INNER_PAYMENT_INFO']['CURRENT_BUDGET'] > 0)
										|| ($arParams['ONLY_INNER_FULL'] === 'Y' && $arResult['INNER_PAYMENT_INFO']['CURRENT_BUDGET'] >= $arResult['PAYMENT']["SUM"])
									)
									{
										if ($arParams['ONLY_INNER_FULL'] !== 'Y')
										{
											?>
											<div class="sale-order-payment-change-payment-price sale-order-inner-padding-bottom">
												<span class="sale-order-payment-change-payment-element"><?=Loc::getMessage('SOPC_SUM_OF_PAYMENT')?>:</span>
												<div class="row" style="max-width: 200px;">
													<div class="sale-order-payment-change-payment-form-group" style="margin-bottom: 0;">
														<div class="col-sm-12 sale-order-payment-change-payment-form-cell">
															<input type="text" placeholder="0.00" class="inner-payment-form-control form-control input-md" value="<?=(float)$inputSum?>" name="payInner">
														</div>
														<label class="sale-order-payment-change-payment-form-cell">
															<?=$arResult['INNER_PAYMENT_INFO']['FORMATED_CURRENCY']?>
														</label>
													</div>
												</div>
											</div>
											<?
										}
										?>
										<div class="sale-order-payment-change-payment-price">
											<a class="sale-order-inner-payment-button">
												<?=Loc::getMessage('SOPC_TPL_PAY_BUTTON')?>
											</a>
										</div>
										<?
									}
									?>
								</div>
							<?
							if (
								($arParams['ONLY_INNER_FULL'] !== 'Y' &&(float)$arResult['INNER_PAYMENT_INFO']['CURRENT_BUDGET'] > 0)
								|| ($arParams['ONLY_INNER_FULL'] === 'Y' && $arResult['INNER_PAYMENT_INFO']['CURRENT_BUDGET'] >= $arResult['PAYMENT']["SUM"])
							)
							{
								?>
								<div class="col-xs-12">
									<span class="tablebodytext sale-paysystem-description">
										<?=Loc::getMessage('SOPC_HANDLERS_PAY_SYSTEM_WARNING_RETURN');?>
									</span>
								</div>
								<?
							}
							else
							{
								?>
								<div class="col-xs-12">
									<?ShowError(Loc::getMessage('SOPC_LOW_BALANCE'));?>
								</div>
								<?
							}
							?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?
		if ((float)$arResult['INNER_PAYMENT_INFO']['CURRENT_BUDGET'] > 0)
		{
			$javascriptParams = array(
				"url" => CUtil::JSEscape($this->__component->GetPath() . '/ajax.php'),
				"templateFolder" => CUtil::JSEscape($templateFolder),
				"accountNumber" => $arParams['ACCOUNT_NUMBER'],
				"paymentNumber" => $arParams['PAYMENT_NUMBER'],
				"valueLimit" => $inputSum,
				"onlyInnerFull" => $arParams['ONLY_INNER_FULL'],
				"wrapperId" => $wrapperId
			);
			$javascriptParams = CUtil::PhpToJSObject($javascriptParams);
			?>
			<script>
				var sc = new BX.Sale.OrderInnerPayment(<?=$javascriptParams?>);
			</script>
			<?
		}
	}
	elseif (empty($arResult['PAYMENT_LINK']) && !$arResult['IS_CASH'] && mb_strlen($arResult['TEMPLATE']))
	{
		echo $arResult['TEMPLATE'];
	}
	else
	{
		?>
		<div class='col'>
			<div class='col-xs-12'>
				<p><?=Loc::getMessage("SOPC_ORDER_SUC", array("#ORDER_ID#"=>$arResult['ORDER_ID'],"#ORDER_DATE#"=>$arResult['ORDER_DATE']))?></p>
				<p><?=Loc::getMessage("SOPC_PAYMENT_SUC", array("#PAYMENT_ID#"=>$arResult['PAYMENT_ID']))?></p>
				<p><?=Loc::getMessage("SOPC_PAYMENT_SYSTEM_NAME", array("#PAY_SYSTEM_NAME#"=>$arResult['PAY_SYSTEM_NAME']))?></p>
				<?
				if (!$arResult['IS_CASH'] && mb_strlen($arResult['PAYMENT_LINK']))
				{
					?>
					<p><?=Loc::getMessage("SOPC_PAY_LINK", array("#LINK#"=>$arResult['PAYMENT_LINK']))?></p>
					<?
				}
				?>
			</div>
		</div>
		<?
		if (!$arResult['IS_CASH'] && mb_strlen($arResult['PAYMENT_LINK']))
		{
			?>
			<script type="text/javascript">
				window.open("<?=$arResult['PAYMENT_LINK']?>");
			</script>
			<?
		}
	}
}
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
?>