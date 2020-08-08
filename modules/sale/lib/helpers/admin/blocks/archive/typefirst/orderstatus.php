<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive\TypeFirst;

use Bitrix\Sale\Helpers\Admin\OrderEdit,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Helpers\Admin\Blocks\Archive\Template,
	Bitrix\Sale\Helpers\Admin\Blocks,
	Bitrix\Sale;

Loc::loadMessages(__FILE__);

class OrderStatus extends Template
{
	protected $name = "statusorder";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		$data = $this->order->getFieldValues();
		$data["DATE_ARCHIVED"] = $this->order->getDateArchived();
		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					<tr>
						<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage("SALE_ORDER_STATUS_CREATED").':</td>
						<td class="adm-detail-content-cell-r">
							<div>'.
								$data["DATE_INSERT"].
								'&nbsp;<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='. $data["USER_ID"].'">'.
									htmlspecialcharsbx(OrderEdit::getUserName($data["USER_ID"], $data["LID"])).
								'</a>
							</div>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_STATUS_ARCHIVED").':</td>
						<td class="adm-detail-content-cell-r"><div>'. $data["DATE_ARCHIVED"].'</div></td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_STATUS_SITE").':</td>
						<td class="adm-detail-content-cell-r">
							<div>'.
								htmlspecialcharsbx(OrderEdit::getSiteName($data["LID"])).
							'</div>
						</td>
					</tr>';

					$statuses = Blocks\OrderStatus::getStatusesList($data["USER_ID"], $data["STATUS_ID"]);

		$result .= '<tr>
						<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_STATUS").':</td>
						<td class="adm-detail-content-cell-r">'.htmlspecialcharsbx($statuses[$data["STATUS_ID"]]).
					'</tr>';

		$result .= self::getCancelBlockHtml($this->order, $data);

		$result .= '</tbody>
				</table>';

		return $result;
	}

	/**
	 * @param Sale\Order $order
	 * @param array $data
	 *
	 * @return string
	 */
	protected static function getCancelBlockHtml(Sale\Order $order, array $data)
	{
		if ($order->getField('CANCELED') !== "Y")
			return "";

		$text = '
			<div class="adm-s-select-popup-element-selected" id="sale-adm-status-cancel-blocktext">
				<div class="adm-s-select-popup-element-selected-bad">
					<span>'.Loc::getMessage("SALE_ORDER_STATUS_CANCELED").'</span>
					'.$order->getField('DATE_CANCELED').'
					<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='. $order->getField("EMP_CANCELED_ID").'">'
						.htmlspecialcharsbx(OrderEdit::getUserName($order->getField("EMP_CANCELED_ID"), $order->getSiteId())).
					'</a>
				</div>
			</div>';

		$reasonCanceled = htmlspecialcharsbx(trim($order->getField("REASON_CANCELED")));
		if(!\CSaleYMHandler::isOrderFromYandex($order->getId()))
		{
			$reasonHtml = '
				<div class="adm-s-select-popup-modal-title">'.Loc::getMessage("SALE_ORDER_STATUS_COMMENT").'</div>
				<textarea style="width:400px;min-height:100px;" name="FORM_REASON_CANCELED" id="FORM_REASON_CANCELED"  disabled>'.($reasonCanceled <> '' ? $reasonCanceled : '').'</textarea>
			';
		}
		else
		{
			$reasonHtml = '
				<div class="adm-s-select-popup-modal-title">'.Loc::getMessage("SALE_ORDER_STATUS_CANCELING_REASON").'</div>
				<select name="FORM_REASON_CANCELED" id="FORM_REASON_CANCELED" class="adm-bus-select" disabled>';

			foreach (\CSaleYMHandler::getOrderSubstatuses() as $statusId => $statusName)
				$reasonHtml .= '<option value="'.$statusId.'"'.($statusId == $reasonCanceled ? " selected" : "").'>'.$statusName.'</option>';

			$reasonHtml .= '</select>';
		}

		return '
			<tr id="sale-adm-status-cancel-row">
				<td class="adm-detail-content-cell-l">&nbsp;</td>
				<td class="adm-detail-content-cell-r">
					<div class="adm-s-select-popup-box">
						<div class="adm-s-select-popup-container">'.
							'<div class="adm-s-select-popup-element-selected-control" onclick="BX.Sale.Admin.OrderEditPage.toggleCancelDialog();"></div>'
							.$text.
						'</div>
						<div class="adm-s-select-popup-modal /*active*/" id="sale-adm-status-cancel-dialog">
							<div class="adm-s-select-popup-modal-content">
								'.$reasonHtml.'
								<div class="adm-s-select-popup-modal-desc"></div>
								</span>
								<span class="adm-s-select-popup-modal-close" onclick="BX.Sale.Admin.OrderEditPage.toggleCancelDialog();">'.Loc::getMessage("SALE_ORDER_STATUS_TOGGLE").'</span>
							</div>
						</div>
					</div>
				</td>
			</tr>';
	}
}