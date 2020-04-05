<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks;

use Bitrix\Main;
use Bitrix\Sale\Company;
use Bitrix\Sale\Delivery\Requests\ShipmentTable;
use Bitrix\Sale\Internals;
use	Bitrix\Sale\Order,
	Bitrix\Sale\Payment,
	Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Services\Company\Manager;

Loc::loadMessages(__FILE__);

class OrderAnalysis
{
	public static function getScripts()
	{
		return '';
	}

	private static function sortDocumentsByDate($doc1, $doc2)
	{
		$date1 = $doc1->getField($doc1 instanceof Payment ? 'DATE_PAID' : 'DATE_INSERT');
		$date2 = $doc2->getField($doc2 instanceof Payment ? 'DATE_PAID' : 'DATE_INSERT');
		return $date1 > $date2 ? 1 : -1;
	}

	public static function getView(Order $order, OrderBasket $orderBasket, $selectPayment = null, $selectId = null)
	{
		global $APPLICATION, $USER;
		// prepare data

		$orderId   = $order->getId();
		$data      = $orderBasket->prepareData();
		$items     = $data['ITEMS'];
		$documents = array();
		$documentAllowList = array();
		static $userCompanyList = array();
		$isUserResponsible = false;
		$isAllowCompany = false;
		$itemNo    = 0;



		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if($saleModulePermissions == "P")
		{
			if (empty($userCompanyList))
			{
				$userCompanyList = Manager::getUserCompanyList($USER->GetID());
			}

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}
		}

		/** @var \Bitrix\Sale\Payment $payment */
		foreach ($order->getPaymentCollection() as $payment)
		{
			$documents []= $payment;

			$documentAllowList['PAYMENT'][$payment->getId()] = true;

			if ($saleModulePermissions == "P")
			{
				$isPaymentUserResponsible = ($isUserResponsible || $payment->getField('RESPONSIBLE_ID') == $USER->GetID());
				$isPaymentAllowCompany = ($isAllowCompany || in_array($payment->getField('COMPANY_ID'), $userCompanyList));
				$documentAllowList['PAYMENT'][$payment->getId()] = ($isPaymentUserResponsible || $isPaymentAllowCompany);
			}

		}

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($order->getShipmentCollection() as $shipment)
		{
			if (! $shipment->isSystem())
			{
				if (! $shipment->isCanceled() && $shipment->isShipped())
				{
					/** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
					foreach ($shipment->getShipmentItemCollection() as $shipmentItem)
					{
						$basketCode = $shipmentItem->getBasketCode();

						if ($basketCode && isset($items[$basketCode]))
						{
							$item = &$items[$basketCode];
							if ($shippedQuantity = &$item['SHIPPED_QUANTITY'])
								$shippedQuantity += (float) $shipmentItem->getField('QUANTITY');
							else
								$shippedQuantity = (float) $shipmentItem->getField('QUANTITY');
						}
					}
				}

				$documents []= $shipment;

				$documentAllowList['SHIPMENT'][$shipment->getId()] = true;

				if ($saleModulePermissions == "P")
				{
					$isShipmentUserResponsible = ($isUserResponsible || $shipment->getField('RESPONSIBLE_ID') == $USER->GetID());
					$isShipmentAllowCompany = ($isAllowCompany || in_array($shipment->getField('COMPANY_ID'), $userCompanyList));
					$documentAllowList['SHIPMENT'][$shipment->getId()] = ($isShipmentUserResponsible || $isShipmentAllowCompany);
				}
			}
		}

		unset($item, $shippedQuantity);

		usort($documents, array(__CLASS__, 'sortDocumentsByDate'));

		// render view

		ob_start();

		?>
		<div class="adm-s-order-table-ddi">
			<table class="adm-s-order-table-ddi-table adm-s-bus-ordertable-option" style="width: 100%;">
				<thead>
				<tr>
					<td class="tac"><?=Loc::getMessage('SALE_OANALYSIS_ITEM_NUMBER')?></td>
					<td><?=Loc::getMessage('SALE_OANALYSIS_ITEM_NAME')?></td>
					<td class="tac"><?=Loc::getMessage('SALE_OANALYSIS_ITEM_PROPERTIES')?></td>
					<td class="tac"><?=Loc::getMessage('SALE_OANALYSIS_ITEM_PLANNED')?></td>
					<td class="tac"><?=Loc::getMessage('SALE_OANALYSIS_ITEM_SHIPPED')?></td>
					<td class="tac"><?=Loc::getMessage('SALE_OANALYSIS_ITEM_TO_SHIP')?></td>
				</tr>
				</thead>
				<tbody>
					<?foreach ($items as $item):
						$properties = '<table style="margin: auto; width: 50%;">';
						if (is_array($item['SKU_PROPS']))
						{
							foreach ($item['SKU_PROPS'] as $skuProp)
							{
								$properties .= '<tr>';
								$properties .= '<td style="text-align: left; padding-left: 0;">'. htmlspecialcharsbx($skuProp['NAME']).' : '.'</td>';

								if (isset($skuProp['VALUE']['PICT']) && $skuProp['VALUE']['PICT'])
									$properties .= '<td><span class="color"><img src="'.$skuProp['VALUE']['PICT'].'" alt=""></span></td>';
								else
									$properties .= '<td><span>'.htmlspecialcharsbx($skuProp['VALUE']['NAME']).'</span></td>';

								$properties .= '</tr>';
							}
						}
						$properties .= '</table>';

						if (! $quantity = (float) $item['QUANTITY'])
							$quantity = 0;

						if (! $shippedQuantity = $item['SHIPPED_QUANTITY'])
							$shippedQuantity = 0;

						?>
						<tr class="bdb-line">
							<td class="tac"><?=++$itemNo?></td>
							<td style="text-align: left;"><a class="fwb" href="<?=htmlspecialcharsbx($item['EDIT_PAGE_URL']);?>"><?=htmlspecialcharsEx($item['NAME']);?></a></td>
							<td class="tac"><?=$properties;?></td>
							<td class="tac" style="text-align: right !important;"><?=$quantity.' '.htmlspecialcharsEx($item['MEASURE_TEXT'])?></td>
							<td class="tac" style="text-align: right !important;;"><?=$shippedQuantity.' '.htmlspecialcharsEx($item['MEASURE_TEXT'])?></td>
							<td class="tac" style="text-align: right !important;;"><?=($quantity - $shippedQuantity).' '.htmlspecialcharsEx($item['MEASURE_TEXT'])?></td>
						</tr>
					<?endforeach?>
					<tr><td colspan="8" style="padding: 16px; background: #f7fafa; text-align: right;" class="fwb"><?=Loc::getMessage('SALE_OANALYSIS_ITEMS_QUANTITY').': '.count($items)?></td></tr>
				</tbody>
			</table>
			<div class="adm-bus-table-contaier-white caption border" style="margin-top: 25px;">
				<div class="adm-bus-table-caption-white-title"><?=Loc::getMessage('SALE_OANALYSIS_DOCUMENTS')?>:</div>
				<div class="adm-bus-orderdocs-threelist-container">
					<div class="adm-bus-orderdocs-threelist-block-top<?=$selectPayment === null ? ' adm-bus-orderdocs-threelist-block-children-open' : ''?>">
						<div class="adm-bus-orderdocs-threelist-block-img adm-bus-orderdocs-threelist-block-img-order"></div>
						<div class="adm-bus-orderdocs-threelist-block-content">
							<div class="adm-bus-orderdocs-threelist-block-title">
								<a class="adm-bus-orderdocs-threelist-block-title-link fwb" href="/bitrix/admin/sale_order_edit.php?lang=ru&ID=<?=$orderId;?>">
									<?=Loc::getMessage('SALE_OANALYSIS_ORDER_TITLE', array(
										'#USER_ID#'  => $order->getField('USER_ID'),
										'#ORDER_ID#' => $orderId)
									)?>
								</a>
							</div>
							<?self::renderBottomBlocks($order->getField('DATE_INSERT'), $order->getField('RESPONSIBLE_ID'))?>
						</div>
						<div class="clb"></div>
					</div>
					<?foreach ($documents as $document): $isPayment = $document instanceof Payment; $documentId = $document->getId()?>
						<div class="adm-bus-orderdocs-threelist-block-children<?=$selectPayment === $isPayment && $selectId == $documentId ? ' adm-bus-orderdocs-threelist-block-children-open' : ''?>">
							<div class="adm-bus-orderdocs-threelist-block-img adm-bus-orderdocs-threelist-block-img-doc_<?=$isPayment ? 'payment' : 'shipping'?>"></div>
							<div class="adm-bus-orderdocs-threelist-block-content">
								<div class="adm-bus-orderdocs-threelist-block-title">
									<?if ($isPayment):
										$isAllowCompany = $documentAllowList['PAYMENT'][$documentId];
										?>
										<?if ($document->isPaid()):?>
											<span class="adm-bus-orderdocs-docstatus adm-bus-orderdocs-docstatus-paid"><?=Loc::getMessage('SALE_OANALYSIS_PAYMENT_PAID')?></span>
										<?elseif ($document->isReturn()):?>
											<span class="adm-bus-orderdocs-docstatus"><?=Loc::getMessage('SALE_OANALYSIS_PAYMENT_RETURN')?></span>
										<?endif?>
											<? if ($isAllowCompany): ?>
											<a href="/bitrix/admin/sale_order_payment_edit.php?order_id=<?=$orderId?>&payment_id=<?=$documentId?>" class="adm-bus-orderdocs-threelist-block-title-link">
											<?=Loc::getMessage('SALE_OANALYSIS_PAYMENT_TITLE', array(
												'#SYSTEM_NAME#' => htmlspecialcharsbx($document->getField('PAY_SYSTEM_NAME')),
												'#PAYMENT_ID#'  => $documentId,
												'#SUM#'         => SaleFormatCurrency($document->getField('SUM'), $document->getField('CURRENCY')),
											))?>
											</a>
											<?else:?>
												<?= Loc::getMessage('SALE_OANALYSIS_HIDDEN');?>
											<? endif; ?>
									<?else:/* shipment*/
										$isAllowCompany = $documentAllowList['SHIPMENT'][$documentId];
									?>
										<?if ($document->isShipped()):?>
											<span class="adm-bus-orderdocs-docstatus adm-bus-orderdocs-docstatus-shippingallowed"><?=Loc::getMessage('SALE_OANALYSIS_SHIPMENT_SHIPPED')?></span>
										<?elseif ($document->isCanceled()):?>
											<span class="adm-bus-orderdocs-docstatus adm-bus-orderdocs-docstatus-canceled"><?=Loc::getMessage('SALE_OANALYSIS_SHIPMENT_CANCELED')?></span>
										<?elseif ($document->isAllowDelivery()):?>
											<span class="adm-bus-orderdocs-docstatus adm-bus-orderdocs-docstatus-shippingallowed"><?=Loc::getMessage('SALE_OANALYSIS_SHIPMENT_ALLOWED')?></span>
										<?endif?>
										<? if ($isAllowCompany): ?>
										<a href="/bitrix/admin/sale_order_shipment_edit.php?order_id=<?=$orderId?>&shipment_id=<?=$documentId?>"
										   class="adm-bus-orderdocs-threelist-block-title-link<?=$document->isCanceled() ? 'adm-bus-orderdocs-threelist-block-title-link-canceled' : ''?>">
											<?=Loc::getMessage('SALE_OANALYSIS_SHIPMENT_TITLE', array(
												'#SHIPMENT_ID#' => $documentId,
												'#ORDER_ID#'    => $orderId,
											))?>
										</a>
										<?else:?>
											<?= Loc::getMessage('SALE_OANALYSIS_HIDDEN');?>
										<? endif; ?>
									<?endif?>
								</div>
								<?self::renderBottomBlocks($document->getField($isPayment ? 'DATE_BILL' : 'DATE_INSERT'), $document->getField('RESPONSIBLE_ID'))?>
							</div>
							<div class="clb"></div>
						</div>
						<?if(!$isPayment):?>
							<?self::printDeliveryRequestBlock($document->getId());?>
						<?endif;?>
					<?endforeach?>
				</div>
			</div>
		</div>
		<?

		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	private static function printDeliveryRequestBlock($shipmentId)
	{
		$res = ShipmentTable::getList(
			array(
				'filter' => array('=SHIPMENT_ID' => $shipmentId),
				'select' => array(
					'*',
					'REQUEST_DATE' => 'REQUEST.DATE'
				)
			)
		);

		$request = $res->fetch();

		if(intval($request['REQUEST_ID']) > 0)
		{
			?>
				<div class="adm-bus-orderdocs-threelist-block-children" style="padding-left: 60px;">
					<div class="adm-bus-orderdocs-threelist-block-img adm-bus-orderdocs-threelist-block-img-doc_shipping"></div>
						<div class="adm-bus-orderdocs-threelist-block-content">
							<div class="adm-bus-orderdocs-threelist-block-title">
								<a href="/bitrix/admin/sale_delivery_request_view.php?lang=<?=LANGUAGE_ID?>&ID=<?=$request['REQUEST_ID']?>" class="adm-bus-orderdocs-threelist-block-title-link">
									<?=Loc::getMessage('SALE_OANALYSIS_DELIVERY_REQUEST', array('#REQUEST_ID#' => $request['REQUEST_ID']))?>
								</a>
							</div>
						<div class="adm-bus-orderdocs-threelist-block-date-block">
							<?=Loc::getMessage('SALE_OANALYSIS_CREATED_AT')?>: <span class="adm-bus-orderdocs-threelist-block-date"><?=$request['REQUEST_DATE']?></span>
						</div>
					</div>
					<div class="clb"></div>
				</div>
			<?
		}
	}

	private static function renderBottomBlocks($creationDate, $userId)
	{
		$userName = '';

		if ($userId && ($user = \CUser::GetByID($userId)->Fetch()))
		{
			if ($user['NAME'])
				$userName = $user['NAME'];
			if ($user['LAST_NAME'])
				$userName .= ($userName ? ' ' : '').$user['LAST_NAME'];
			if (! $userName)
				$userName = $user['LOGIN'];
		}

		?>
		<div class="adm-bus-orderdocs-threelist-block-date-block">
			<?=Loc::getMessage('SALE_OANALYSIS_CREATED_AT')?>: <span class="adm-bus-orderdocs-threelist-block-date"><?=$creationDate?></span>
		</div>
		<?if ($userName) :?>
			<div class="adm-bus-orderdocs-threelist-block-responsible-block">
				<?=Loc::getMessage('SALE_OANALYSIS_RESPONSIBLE')?>:
				<a class="adm-bus-orderdocs-threelist-block-responsible-name"
				   href="/bitrix/admin/user_edit.php?ID=<?=$userId?>"><?=htmlspecialcharsbx($userName)?></a>
			</div>
		<?endif;?>
		<?
	}
}




