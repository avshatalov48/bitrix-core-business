<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Page\Asset;

\Bitrix\Main\UI\Extension::load("ui.bootstrap4");

if ($arParams['GUEST_MODE'] !== 'Y')
{
	Asset::getInstance()->addJs("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/script.js");
	Asset::getInstance()->addCss("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/style.css");
}

CJSCore::Init(array('clipboard', 'fx'));

$APPLICATION->SetTitle("");
?><div class="container-fluid"><?php
if (!empty($arResult['ERRORS']['FATAL']))
{
	$component = $this->__component;
	foreach($arResult['ERRORS']['FATAL'] as $code => $error)
	{
		if ($code !== $component::E_NOT_AUTHORIZED)
			ShowError($error);
	}

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
		foreach ($arResult['ERRORS']['NONFATAL'] as $error)
		{
			ShowError($error);
		}
	}
	?>
	<div class="row personal-order-detail-container">
		<div class="col">
			<div class="personal-order-detail-header">
				<h1 class="personal-order-detail-header-title"><?=Loc::getMessage('SPOD_LIST_MY_ORDER', array(
						'#ACCOUNT_NUMBER#' => htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"]),
						'#DATE_ORDER_CREATE#' => $arResult["DATE_INSERT_FORMATED"]
					)) ?></h1>
				<div class="personal-order-detail-header-info">
					<div>
						<?
						if ($arResult['STATUS_ID'] === "F")
						{
							?><div class="personal-order-detail-paid-status-restricted"><?=Loc::getMessage('SPOD_TPL_ORDER_FINISHED');?></div><?
						}
						elseif ($arResult["CANCELED"] === 'Y')
						{
							?><div class="personal-order-detail-order-status-canceled"><?=Loc::getMessage('SPOD_TPL_ORDER_CANCELED');?></div><?
						}
						elseif ($arResult['PAYED'] !== 'Y')
						{
							?><div class="personal-order-detail-paid-status-alert"><?=Loc::getMessage('SPOD_PAYMENT_UNPAID').", ".$arResult['SUM_REST_FORMATED'];?></div><?
						}
						elseif ($arResult['DEDUCTED'] === 'Y')
						{
							?><div class="personal-order-detail-shipment-status-success"><?=Loc::getMessage('SPOD_TPL_LOADED');?></div><?
						}
						else
						{
							?><div class="personal-order-detail-paid-status-restricted"><?=Loc::getMessage('SPOD_TPL_SHIPING_STATUS_'.$arResult['STATUS_ID']);?></div><?
						}
						?>
					</div>
					<div class="personal-order-detail-header-order-id"><?=Loc::getMessage('SPOD_NUM_SIGN') . htmlspecialcharsbx($arResult['ACCOUNT_NUMBER'])?></div>
				</div>
			</div>

			<div class="personal-order-detail-info-container">

				<? //region SHIPMENT PARAMETERS
				if (count($arResult['SHIPMENT']))
				{
					?>
					<div class="personal-order-detail-info-shipment">
						<h2 class="personal-order-detail-info-shipment-title"><?= Loc::getMessage('SPOD_ORDER_SHIPMENT') ?></h2>
						<? foreach ($arResult['SHIPMENT'] as $shipment)
						{
							?>
							<div class="personal-order-detail-info-shipment-description">
								<?
								//change date
								if ($shipment['PRICE_DELIVERY_FORMATED'] === '')
								{
									$shipment['PRICE_DELIVERY_FORMATED'] = 0;
								}

								echo Loc::getMessage('SPOD_SUB_PRICE_DELIVERY', array(
									'#PRICE_DELIVERY#' => $shipment['PRICE_DELIVERY_FORMATED']
								));

								if($shipment["DELIVERY_NAME"] <> '')
								{
									echo "<br />".Loc::getMessage('SPOD_ORDER_DELIVERY').": ".htmlspecialcharsbx($shipment["DELIVERY_NAME"]);
								}

								$visibleShipmentProps = [];
								$shipmentCodes = ['ZIP', 'LOCATION', 'CITY', 'ADDRESS'];
								foreach ($arResult["ORDER_PROPS"] as $property)
								{
									if (in_array($property['CODE'], $shipmentCodes))
									{
										$visibleShipmentProps[] = htmlspecialcharsbx($property["VALUE"]);
									}
								}

								if (count($visibleShipmentProps) > 0)
								{
									echo '<br />' . implode(', ', $visibleShipmentProps);
								}

								if ($shipment['TRACKING_NUMBER'] <> '')
								{
									echo "<br />" . Loc::getMessage('SPOD_ORDER_TRACKING_NUMBER') . ': ' . htmlspecialcharsbx($shipment['TRACKING_NUMBER']);

									if ($shipment['TRACKING_URL'] <> '')
									{
										?>
										<div class="personal-order-detail-info-shipment-description-btn-container">
											<a href="" onclick="return false"
											   class="personal-order-detail-order-btn-track"
											   href="<?= htmlspecialcharsbx($shipment['TRACKING_URL']) ?>"><?= Loc::getMessage('SPOD_ORDER_CHECK_TRACKING') ?></a>
										</div>
										<?
									}
								}
								?>
							</div>

							<?
						}
						?>
					</div>

					<?
				}
				//endregion ?>

				<!--region DELIVERY AND PAYMENT-->
				<div class="personal-order-detail-info-payment">
					<h2 class="personal-order-detail-info-payment-title"><?= Loc::getMessage('SPOD_ORDER_PAYMENT') ?></h2>
					<div class="personal-order-detail-info-payment-description">
						<?= Loc::getMessage('SPOD_SUB_ORDER_TITLE', array(
							"#ACCOUNT_NUMBER#"=> htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"]),
							"#DATE_ORDER_CREATE#"=> $arResult["DATE_INSERT_FORMATED"]
						));

						if ($arResult['CANCELED'] !== 'Y')
						{
							echo htmlspecialcharsbx($arResult["STATUS"]["NAME"]);
						}
						else
						{
							echo Loc::getMessage('SPOD_ORDER_CANCELED');
						}

						foreach ($arResult['PAYMENT'] as $payment)
						{
							if ($payment['PAID'] === 'Y')
							{
								echo "<br />".Loc::getMessage('SPOD_PAYMENT_PAID');
							}
							elseif ($arResult['IS_ALLOW_PAY'] === 'N')
							{
								echo "<br />".Loc::getMessage('SPOD_TPL_RESTRICTED_PAID');
							}
							else
							{
								echo "<br />".Loc::getMessage('SPOD_PAYMENT_UNPAID');
							}

							echo " - ".$payment["PRICE_FORMATED"];

							if ($payment['PAID'] === 'Y')
							{
								echo " - " . $payment['PAY_SYSTEM_NAME'] . " - " . $payment['DATE_PAID_FORMATTED'];
							}
						}

						if (($arResult['STATUS_ID'] === "F") || ($arResult["CANCELED"] === 'Y'))
						{
							?>
							<div class="personal-order-detail-info-payment-description-btn-container">
								<a href="<?=$arResult['URL_TO_COPY']?>" class="personal-order-detail-order-btn-reorder">
									<?= Loc::getMessage('SPOD_ORDER_REPEAT'); ?>
								</a>
							</div>
							<?
						}
						elseif  (($arResult['PAID'] !== 'Y') && ($arResult['SUM_REST']))
						{
							?>
							<div class="personal-order-detail-info-payment-description-btn-container">
								<span class="personal-order-detail-order-btn-pay">
									<?= Loc::getMessage('SPOD_ORDER_PAY') ?>, <?=$arResult['SUM_REST_FORMATED']?>
								</span>
							</div>
							<?
						}

						?>

					</div>

				</div>
				<!--endregion -->

				<!--region customer-->
				<div class="personal-order-detail-info-customer">
					<h2 class="personal-order-detail-info-customer-title">
						<?= Loc::getMessage('SPOD_TPL_ORDER_CUSTOMER') ?>
					</h2>
					<div class="personal-order-detail-info-customer-description">
						<?
						$i = 0;
						foreach ($arResult["ORDER_PROPS"] as $property)
						{
							if ($property["TYPE"] === "Y/N")
							{
								echo Loc::getMessage('SPOD_' . ($property["VALUE"] == "Y" ? 'YES' : 'NO'));
							}
							else
							{
								if ($property['MULTIPLE'] === 'Y'
									&& $property['TYPE'] !== 'FILE'
									&& $property['TYPE'] !== 'LOCATION')
								{
									$propertyList = unserialize($property["VALUE"], ['allowed_classes' => false]);
									foreach ($propertyList as $propertyElement)
									{
										echo $propertyElement . '</br>';
									}
								}
								elseif ($property['TYPE'] == 'FILE')
								{
									echo $property["VALUE"];
								}
								else
								{
									echo ($i > 0 ? ', ' : '').htmlspecialcharsbx($property["VALUE"]);
								}
								$i++;
							}
						}
						?>
					</div>
				</div>
				<!--endregion -->

				<? //region USER_DESCRIPTION
				if($arResult["USER_DESCRIPTION"] <> '')
				{
					?>
					<div class="personal-order-detail-info-products">
						<h2 class="personal-order-detail-info-products-title"><?= Loc::getMessage('SPOD_ORDER_DESC') ?></h2>
						<div class="personal-order-detail-info-products-description"><?= nl2br(htmlspecialcharsbx($arResult["USER_DESCRIPTION"])) ?></div>
					</div>
					<?
				}
				//endregion

				//region COMMENTS
				if($arResult["COMMENTS"] <> '')
				{
					?>
					<div class="personal-order-detail-info-products">
						<h2 class="personal-order-detail-info-products-title"><?= Loc::getMessage('SPOD_ORDER_DESC') ?></h2>
						<div class="personal-order-detail-info-products-description"><?= nl2br(htmlspecialcharsbx($arResult["COMMENTS"])) ?></div>
					</div>
					<?
				}
				//endregion ?>

				<!--region products-->
				<div class="personal-order-detail-info-products">
					<h2 class="personal-order-detail-info-products-title"><?=Loc::getMessage('SPOD_ORDER_BASKET')?></h2>
					<div class="personal-order-detail-info-products-description"></div>
				</div>
				<!--endregion -->

				<div class="personal-order-detail-products-total-item-list" id="summaryList">
					<?
					$subtotalitems = 0;
					foreach ($arResult['BASKET'] as $basketItem)
					{
						?>
						<div class="personal-order-detail-products-item">
							<div>
								<a href="<?=$basketItem['DETAIL_PAGE_URL']?>" target="_blank" class="personal-order-detail-products-item-image-link">
									<?
									if($basketItem['PICTURE']['SRC'] <> '')
									{
										$imageSrc = htmlspecialcharsbx($basketItem['PICTURE']['SRC']);
									}
									else
									{
										$imageSrc = $this->GetFolder().'/images/no_photo.png';
									}
									?>
									<img src="<?= $imageSrc ?>" class="personal-order-detail-products-item-image" alt="">
								</a>
							</div>
							<div class="personal-order-detail-products-item-name">
								<a class="personal-order-detail-products-item-name-link" href="<?=htmlspecialcharsbx($basketItem['DETAIL_PAGE_URL'])?>">
									<?= htmlspecialcharsbx($basketItem['NAME']) ?>
								</a>
							</div>
							<div class="personal-order-detail-products-item-quantity">
								<strong><?=$basketItem['QUANTITY']?></strong>
								<span>
									<?
									if($basketItem['MEASURE_NAME'] <> '')
									{
										echo htmlspecialcharsbx($basketItem['MEASURE_NAME']);
									}
									else
									{
										echo Loc::getMessage('SPOD_DEFAULT_MEASURE');
									}
									?>
								</span>
							</div>
							<div class="personal-order-detail-products-item-price-container">
								<?
								if ($basketItem['PRICE'] !== $basketItem['BASE_PRICE'])
								{
									?>
									<div class="personal-order-detail-products-item-price-discount-container">
										<div class="personal-order-detail-products-item-price-base"><?=$basketItem['BASE_PRICE_FORMATED']?></div>
										<div class="personal-order-detail-products-item-price-discount">-<?=$basketItem['FORMATED_DISCOUNT_SUM']?></div>
									</div>
									<?
								}
								?>
								<div class="personal-order-detail-products-item-price-current"><?=$basketItem['FORMATED_BASE_SUM']?></div>
							</div>
						</div>
						<?

						$subtotalitems += $basketItem['QUANTITY'];
					}

					if ($arResult['PRODUCT_SUM_FORMATED'] !== $arResult['PRICE_FORMATED'] && !empty($arResult['PRODUCT_SUM_FORMATED']))
					{
						?>
						<div class="personal-order-detail-products-total-item">
							<div class="personal-order-detail-products-item-name"><?=Loc::getMessage('SPOD_COMMON_SUM')?> (<?=$subtotalitems;?>)</div>
							<?
							if ($arResult['PRODUCT_SUM_FORMATED'] !== $arResult['PRICE_FORMATED'] && !empty($arResult['PRODUCT_SUM_FORMATED']))
							{
								?>
								<div class="personal-order-detail-products-item-price-container">
									<?
									if ($arResult['PRODUCT_SUM'] !== $arResult['BASE_PRODUCT_SUM'])
									{
										?>
										<div class="personal-order-detail-products-item-price-discount-container">
											<div class="personal-order-detail-products-item-price-base"><?=$arResult['BASE_PRODUCT_SUM_FORMATED']?></div>
											<div class="personal-order-detail-products-item-price-discount">-<?=$arResult['PRODUCT_SUM_DISCOUNT_FORMATED']?></div>
										</div>
										<?
									}
									?>
									<div class="personal-order-detail-products-item-price-current"><?=$arResult['PRODUCT_SUM_FORMATED']?></div>
								</div>
								<?
							}
							?>
						</div>
						<?
					}

					if($arResult["PRICE_DELIVERY_FORMATED"] <> '')
					{
						?>
						<div class="personal-order-detail-products-total-item">
							<div class="personal-order-detail-products-item-name"><?=Loc::getMessage('SPOD_DELIVERY')?></span></div>
							<div class="personal-order-detail-products-item-price-container">
								<div class="personal-order-detail-products-item-price-current"><?= $arResult["PRICE_DELIVERY_FORMATED"] ?></div>
							</div>
						</div>
						<?
					}

					if($arResult["PRODUCT_SUM_DISCOUNT_FORMATED"] <> '')
					{
						?>
						<div class="personal-order-detail-products-discount-item">
							<div class="personal-order-detail-products-item-name"><?=Loc::getMessage('SPOD_DISCOUNT')?></div>
							<div class="personal-order-detail-products-item-price-container">
								<div class="personal-order-detail-products-item-price-current">-<?= $arResult["PRODUCT_SUM_DISCOUNT_FORMATED"] ?></div>
							</div>
						</div>
						<?
					}

					if ((float)$arResult["TAX_VALUE"] > 0)
					{
						?>
						<div class="personal-order-detail-products-total-item">
							<div class="personal-order-detail-products-item-name"><?=Loc::getMessage('SPOD_TAX')?> </div>
							<div class="personal-order-detail-products-item-price-container">
								<div class="personal-order-detail-products-item-price-current"><?= $arResult["TAX_VALUE_FORMATED"] ?></div>
							</div>
						</div>
						<?
					}
					?>

					<div class="personal-order-detail-products-total-item-summary">
						<div class="personal-order-detail-products-item-summary-name"><?=Loc::getMessage('SPOD_SUMMARY')?></div>
						<div class="personal-order-detail-products-item-price-container">
							<div class="personal-order-detail-products-item-price-summary"><?= $arResult["PRICE_FORMATED"] ?></div>
						</div>
					</div>
				</div>

			</div>

			<div class="personal-order-detail-footer">
				<div class="personal-order-detail-footer-btn-container text-center">
					<? if ($arResult["CAN_CANCEL"] === "Y")
					{
						?>
						<a href="<?= htmlspecialcharsbx($arResult["URL_TO_CANCEL"]) ?>" class="btn btn-danger btn-lg rounded-pill py-1 w-100" style="max-width: 310px;"><?= Loc::getMessage('SPOD_ORDER_CANCEL') ?></a>
						<?
					}
					?>
				</div>
			</div>

		</div>
	</div>
	<?
	$javascriptParams = array(
		"url" => CUtil::JSEscape($this->__component->GetPath().'/ajax.php'),
		"templateFolder" => CUtil::JSEscape($templateFolder),
		"templateName" => $this->__component->GetTemplateName(),
		"paymentList" => $paymentData,
		"returnUrl" => $arResult['RETURN_URL'],
	);
	$javascriptParams = CUtil::PhpToJSObject($javascriptParams);
	?>
	<script>
		BX.Sale.PersonalOrderComponent.PersonalOrderDetail.init(<?=$javascriptParams?>);
	</script>
<?
}
?></div>