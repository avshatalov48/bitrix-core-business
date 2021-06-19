<?

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("ui.bootstrap4");

CJSCore::Init(array('clipboard', 'fx'));

Loc::loadMessages(__FILE__);
?><div class="container-fluid"><?php
if (!empty($arResult['ERRORS']['FATAL']))
{
	foreach($arResult['ERRORS']['FATAL'] as $code => $error)
	{
		if ($code !== $component::E_NOT_AUTHORIZED)
			ShowError($error);
	}
	$component = $this->__component;
	if ($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED]))
	{
		?>
		<div class="row">
			<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
				<div class="alert alert-danger"><?=$arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED]?></div>
			</div>
			<? $authListGetParams = array(); ?>
			<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
				<?$APPLICATION->AuthForm('', false, false, 'N', false);?>
			</div>
		</div>
		<?
	}

}
else
{
	if (!empty($arResult['ERRORS']['NONFATAL']))
	{
		foreach($arResult['ERRORS']['NONFATAL'] as $error)
		{
			ShowError($error);
		}
	}

	if (!count($arResult['ORDERS']))
	{
		if ($_REQUEST["filter_history"] == 'Y')
		{
			if ($_REQUEST["show_canceled"] == 'Y')
			{
				?>
				<div class="mt-3 alert alert-secondary"><?= Loc::getMessage('SPOL_TPL_EMPTY_CANCELED_ORDER')?></div>
				<?
			}
			else
			{
				?>
				<div class="mt-3 alert alert-secondary"><?= Loc::getMessage('SPOL_TPL_EMPTY_HISTORY_ORDER_LIST')?></div>
				<?
			}
		}
		else
		{
			?>
			<div class="mt-3 alert alert-secondary"><?= Loc::getMessage('SPOL_TPL_EMPTY_ORDER_LIST')?></div>
			<?
		}
	}

	?>
		<div class="">
			<?
			$nothing = !isset($_REQUEST["filter_history"]) && !isset($_REQUEST["show_all"]);
			$clearFromLink = array("filter_history","filter_status","show_all", "show_canceled");

			if ($nothing || $_REQUEST["filter_history"] == 'N')
			{
				?>
				<a class="mr-4" href="<?=$APPLICATION->GetCurPageParam("filter_history=Y", $clearFromLink, false)?>"><?echo Loc::getMessage("SPOL_TPL_VIEW_ORDERS_HISTORY")?></a>
				<?
			}
			if ($_REQUEST["filter_history"] == 'Y')
			{
				?>
				<a class="mr-4" href="<?=$APPLICATION->GetCurPageParam("", $clearFromLink, false)?>"><?echo Loc::getMessage("SPOL_TPL_CUR_ORDERS")?></a>
				<?
				if ($_REQUEST["show_canceled"] == 'Y')
				{
					?>
					<a class="mr-4" href="<?=$APPLICATION->GetCurPageParam("filter_history=Y", $clearFromLink, false)?>"><?echo Loc::getMessage("SPOL_TPL_VIEW_ORDERS_HISTORY")?></a>
					<?
				}
				else
				{
					?>
					<a class="mr-4" href="<?=$APPLICATION->GetCurPageParam("filter_history=Y&show_canceled=Y", $clearFromLink, false)?>"><?echo Loc::getMessage("SPOL_TPL_VIEW_ORDERS_CANCELED")?></a>
					<?
				}
			}
			?>
		</div>
	<?
	if (!count($arResult['ORDERS']))
	{
		?>
			<div class="">
				<a href="<?=htmlspecialcharsbx($arParams['PATH_TO_CATALOG'])?>" class="mr-4"><?=Loc::getMessage('SPOL_TPL_LINK_TO_CATALOG')?></a>
			</div>
		<?
	}

	if ($_REQUEST["filter_history"] !== 'Y')
	{
		$paymentChangeData = array();
		$orderHeaderStatus = null;

		foreach ($arResult['ORDERS'] as $key => $order)
		{
			?>
			<div class="row personal-order-item-container">
				<div class="col">
					<div class="personal-order-item-header">
						<h2 class="personal-order-item-title">
							<?=Loc::getMessage('SPOL_TPL_ORDER')?>
							<?=Loc::getMessage('SPOL_TPL_NUMBER_SIGN') . htmlspecialcharsbx($order['ORDER']['ACCOUNT_NUMBER'])?>
							<?=Loc::getMessage('SPOL_TPL_FROM_DATE')?>
							<?=$order['ORDER']['DATE_INSERT_FORMATED']?>
						</h2>
						<div class="personal-order-item-order-cost">
							<?=count($order['BASKET_ITEMS']);?>
							<?
							$count = count($order['BASKET_ITEMS']) % 10;
							if ($count == '1')
							{
								echo Loc::getMessage('SPOL_TPL_GOOD');
							}
							elseif ($count >= '2' && $count <= '4')
							{
								echo Loc::getMessage('SPOL_TPL_TWO_GOODS');
							}
							else
							{
								echo Loc::getMessage('SPOL_TPL_GOODS');
							}
							?>
							<?=Loc::getMessage('SPOL_TPL_SUMOF')?>
							<?=$order['ORDER']['FORMATED_PRICE']?>
						</div>
					</div>
					<div class="personal-order-item-content">
						<div class="personal-order-item-status-container">
							<?
							if ($order['ORDER']['DEDUCTED'] === 'Y')
							{
								?><div class="personal-order-item-shipment-status-success"><?=Loc::getMessage('SPOL_TPL_LOADED');?></div><?
							}
							elseif ($order['ORDER']['CANCELED'] !== 'Y')
							{
								?><div class="personal-order-item-shipment-status-alert"><?=Loc::getMessage('SPOL_TPL_NOTLOADED');?></div><?
							}
							elseif ($order['ORDER']['CANCELED'] === 'Y')
							{
								?><div class="personal-order-item-order-status-canceled"><?= Loc::getMessage('SPOL_TPL_ORDER_CANCELED')?></div><?
							}

							if ($order['ORDER']['PAID'] === 'Y')
							{
								?><div class="personal-order-item-paid-status-success"><?=Loc::getMessage('SPOL_TPL_PAID').", ".$order['ORDER']['FORMATED_PRICE']?></div><?
							}
							elseif ($order['ORDER']['IS_ALLOW_PAY'] == 'N')
							{
								?><div class="personal-order-item-paid-status-restricted"><?=Loc::getMessage('SPOL_TPL_RESTRICTED_PAID')?></div><?
							}
							else
							{
								if ($order['ORDER']['CANCELED'] !== 'Y')
								{
									foreach ($order['PAYMENT'] as $payment)
									{
										if ($payment['PAID'] === 'N' && $payment['IS_CASH'] !== 'Y' && $payment['ACTION_FILE'] !== 'cash')
										{
											?>
											<div class="personal-order-item-paid-status-alert"><?=Loc::getMessage('SPOL_TPL_NOTPAID')?>, <?=$payment['FORMATED_SUM']?></div>
											<?

											if (($payment['NEW_WINDOW'] === 'Y') && ($order['ORDER']['IS_ALLOW_PAY'] != 'N'))
											{
												?><a class="personal-order-item-order-btn-pay" target="_blank" href="<?=htmlspecialcharsbx($payment['PSA_ACTION_FILE'])?>"><?=Loc::getMessage('SPOL_TPL_PAY')?></a><?
											}
											else
											{
												?><a class="personal-order-item-order-btn-pay ajax_reload" href="<?=htmlspecialcharsbx($payment['PSA_ACTION_FILE'])?>"><?=Loc::getMessage('SPOL_TPL_PAY')?></a><?
											}
										}

										if ($order['ORDER']['LOCK_CHANGE_PAYSYSTEM'] !== 'Y')
										{
											$paymentChangeData[$payment['ACCOUNT_NUMBER']] = array(
												"order" => htmlspecialcharsbx($order['ORDER']['ACCOUNT_NUMBER']),
												"payment" => htmlspecialcharsbx($payment['ACCOUNT_NUMBER']),
												"allow_inner" => $arParams['ALLOW_INNER'],
												"refresh_prices" => $arParams['REFRESH_PRICES'],
												"path_to_payment" => $arParams['PATH_TO_PAYMENT'],
												"only_inner_full" => $arParams['ONLY_INNER_FULL'],
												"return_url" => $arResult['RETURN_URL'],
											);
										}
									}
								}

								if ($order['ORDER']['PAID'] !== 'Y' && $order['ORDER']['LOCK_CHANGE_PAYSYSTEM'] !== 'Y')
								{
									?>
									<a href="#" class="personal-order-item-order-change-payment" id="<?= htmlspecialcharsbx($payment['ACCOUNT_NUMBER']) ?>"><?= Loc::getMessage('SPOL_TPL_CHANGE_PAY_TYPE') ?></a>
									<?
								}
							}

							foreach ($order['SHIPMENT'] as $shipment)
							{
								?>
								<div class="personal-order-item-status-container">
									<?
									if ($order['ORDER']['CAN_CANCEL'] !== 'Y')
									{
										?><a class="personal-order-item-order-btn-reorder" href="<?=htmlspecialcharsbx($order["ORDER"]["URL_TO_COPY"])?>"><?=Loc::getMessage('SPOL_TPL_REPEAT_ORDER')?></a><?
									}

									if ($shipment['TRACKING_URL'] <> '')
									{
										?><a class="personal-order-item-order-btn-track" target="_blank" href="<?= htmlspecialcharsbx($shipment['TRACKING_URL']) ?>"><?=Loc::getMessage('SPOL_TPL_CHECK_POSTID')?></a><?
									}
									?>
								</div>
								<?
								//region CHECK_DATA
								if (!empty($payment['CHECK_DATA']))
								{
									$listCheckLinks = "";
									foreach ($payment['CHECK_DATA'] as $checkInfo)
									{
										$title = Loc::getMessage('SPOL_CHECK_NUM', array('#CHECK_NUMBER#' => $checkInfo['ID']))." - ". htmlspecialcharsbx($checkInfo['TYPE_NAME']);
										if($checkInfo['LINK'] <> '')
										{
											$link = $checkInfo['LINK'];
											$listCheckLinks .= "<div><a href='$link' target='_blank'>$title</a></div>";
										}
									}

									if ($listCheckLinks <> '')
									{
										?>
										<div class="sale-order-list-payment-check">
											<div class="sale-order-list-payment-check-left"><?=Loc::getMessage('SPOL_CHECK_TITLE')?>:</div>
											<div class="sale-order-list-payment-check-left"><?=$listCheckLinks?></div>
										</div>
										<?
									}
								}
								//endregion
							}
							?>
						</div>
						<div class="personal-order-item-product-container">
							<div class="personal-order-item-product-image-list">
								<img class="personal-order-item-product-pict" src="http://store.solj.bx/upload/iblock/631/631c95680c87d7b185ead9f163315e08.jpg" alt="">
							</div>
						</div>
					</div>
					<div class="personal-order-item-additional-info">
 						<div>
							<?
							if (!empty($order['SHIPMENT'][0]['DELIVERY_ID']))
							{
								echo "<div>".Loc::getMessage('SPOL_TPL_DELIVERY_SERVICE').": " . htmlspecialcharsbx($arResult['INFO']['DELIVERY'][$order['SHIPMENT'][0]['DELIVERY_ID']]['NAME']) . "</div>";
							}
							?>
						</div>
						<div class="personal-order-item-additional-info-more-block">
							<a class="personal-order-item-additional-info-more-link" href="<?=htmlspecialcharsbx($order["ORDER"]["URL_TO_DETAIL"])?>"><?=Loc::getMessage('SPOL_TPL_MORE_ON_ORDER')?></a>
						</div>
					</div>
				</div>
			</div>
			<?
		}
	}
	else
	{
		$orderHeaderStatus = null;

		foreach ($arResult['ORDERS'] as $key => $order)
		{
			?>
			<div class="row personal-order-item-container">
				<div class="col">
					<div class="personal-order-item-header">
						<h2 class="personal-order-item-title">
							<?=Loc::getMessage('SPOL_TPL_ORDER')?>
							<?=Loc::getMessage('SPOL_TPL_NUMBER_SIGN') . htmlspecialcharsbx($order['ORDER']['ACCOUNT_NUMBER'])?>
							<?=Loc::getMessage('SPOL_TPL_FROM_DATE')?>
							<?=$order['ORDER']['DATE_INSERT_FORMATED']?>
						</h2>
						<div class="personal-order-item-order-cost">
							<?=count($order['BASKET_ITEMS']);?>
							<?
							$count = count($order['BASKET_ITEMS']) % 10;
							if ($count == '1')
							{
								echo Loc::getMessage('SPOL_TPL_GOOD');
							}
							elseif ($count >= '2' && $count <= '4')
							{
								echo Loc::getMessage('SPOL_TPL_TWO_GOODS');
							}
							else
							{
								echo Loc::getMessage('SPOL_TPL_GOODS');
							}
							?>
							<?=Loc::getMessage('SPOL_TPL_SUMOF')?>
							<?=$order['ORDER']['FORMATED_PRICE']?>
						</div>
					</div>
					<div class="personal-order-item-content">
						<div class="personal-order-item-status-container">
							<?
							if ($_REQUEST["show_canceled"] !== 'Y')
							{
								?><div class="personal-order-item-order-status-success"><?= Loc::getMessage('SPOL_TPL_ORDER_FINISHED')?></div><?
							}
							else
							{
								?><div class="personal-order-item-order-status-canceled"><?= Loc::getMessage('SPOL_TPL_ORDER_CANCELED')?></div><?
							}

							if ($order['PAYMENT'][0]['PAID'] === 'Y')
							{
								?><div class="personal-order-item-order-status-success"><?=Loc::getMessage('SPOL_TPL_PAID')?>, <?=$order['PAYMENT'][0]['FORMATED_SUM']?></div><?
							}

							if ($order['ORDER']['CAN_CANCEL'] !== 'N')
							{
								?><a class="g-font-size-15 sale-order-list-cancel-link" href="<?=htmlspecialcharsbx($order["ORDER"]["URL_TO_CANCEL"])?>"><?=Loc::getMessage('SPOL_TPL_CANCEL_ORDER')?></a><?
							}
							else
							{
								?><a class="personal-order-item-order-btn-reorder" href="<?=htmlspecialcharsbx($order["ORDER"]["URL_TO_COPY"])?>"><?=Loc::getMessage('SPOL_TPL_REPEAT_ORDER')?></a><?
							}
							?>
						</div>
						<!-- endregion -->
					</div>
					<div class="personal-order-item-additional-info">
						<div>
							<?
							if ($_REQUEST["show_canceled"] !== 'Y')
							{
								?>
								<span class="sale-order-list-accomplished-date">
									<?=Loc::getMessage('SPOL_TPL_ORDER_FINISHED')?>
								</span>
								<?
							}
							else
							{
								?>
								<span class="sale-order-list-accomplished-date canceled-order">
									<?=Loc::getMessage('SPOL_TPL_ORDER_CANCELED')?>
								</span>
								<?
							}
							?>
							<span class="sale-order-list-accomplished-date"><?= $order['ORDER']['DATE_STATUS_FORMATED'] ?></span>
						</div>
						<div class="personal-order-item-additional-info-more-block">
							<a class="personal-order-item-additional-info-more-link" href="<?=htmlspecialcharsbx($order["ORDER"]["URL_TO_DETAIL"])?>"><?=Loc::getMessage('SPOL_TPL_MORE_ON_ORDER')?></a>
						</div>
					</div>
				</div>
			</div>
			<?
		}
	}

	echo $arResult["NAV_STRING"];

	if ($_REQUEST["filter_history"] !== 'Y')
	{
		$javascriptParams = array(
			"url" => CUtil::JSEscape($this->__component->GetPath().'/ajax.php'),
			"templateFolder" => CUtil::JSEscape($templateFolder),
			"templateName" => $this->__component->GetTemplateName(),
			"paymentList" => $paymentChangeData,
			"returnUrl" => CUtil::JSEscape($arResult["RETURN_URL"]),
		);
		$javascriptParams = CUtil::PhpToJSObject($javascriptParams);
		?>
		<script>
			BX.Sale.PersonalOrderComponent.PersonalOrderList.init(<?=$javascriptParams?>);
		</script>
		<?
	}
}
?>
</div>
