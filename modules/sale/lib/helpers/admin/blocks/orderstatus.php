<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks;

use Bitrix\Main\Type\Date;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Sale\Order;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\TradingPlatform\OrderTable;

Loc::loadMessages(__FILE__);

class OrderStatus
{
	public static function getEdit(Order $order, \CUser $user, $showCancel, $showSaveButton)
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$data = self::prepareData($order);
		$orderLocked = $orderClass::isLocked($order->getId());

		$allowCancel = false;

		if($showCancel)
		{
			/** @var Sale\OrderStatus $orderClass */
			$orderStatusClass = $registry->getOrderStatusClassName();

			$allowedStatusesCancel = $orderStatusClass::getStatusesUserCanDoOperations($user->GetID(), array('cancel'));
			if (is_array($allowedStatusesCancel))
			{
				$allowCancel = in_array($order->getField("STATUS_ID"), $allowedStatusesCancel);
			}
		}

		$orderSiteId = $order->getSiteId();
		$result = '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
				<tbody>
					<tr>
						<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage("SALE_ORDER_STATUS_CREATED").':</td>
						<td class="adm-detail-content-cell-r">
							<div>'.
								$data["DATE_INSERT"].
								'&nbsp;'.static::renderCreatorLink($data).'
							</div>
						</td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_STATUS_LAST_MODIF").':</td>
						<td class="adm-detail-content-cell-r"><div>'. $data["DATE_UPDATE"].'</div></td>
					</tr>
					<tr>
						<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_STATUS_SITE").':</td>
						<td class="adm-detail-content-cell-r"><div>'.
								htmlspecialcharsbx(
									\Bitrix\Sale\Helpers\Admin\OrderEdit::getSiteName($orderSiteId)
								).
							'</div></td>
					</tr>';

		if(!empty($data["AFFILIATE_NAME"]))
		{
			$data['AFFILIATE_NAME'] = htmlspecialcharsbx($data["AFFILIATE_NAME"]);

			if(intval($data['AFFILIATE_ID']) > 0)
			{
				$data["AFFILIATE_NAME"] = '<a href="/bitrix/admin/sale_affiliate_edit.php?lang='.LANGUAGE_ID.'&ID='.$data['AFFILIATE_ID'].'">'.
						$data["AFFILIATE_NAME"].
					'</a>';
			}

			$result .= '
				<tr>
					<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_STATUS_AFFILIATE").':</td>
					<td class="adm-detail-content-cell-r"><div>'.$data["AFFILIATE_NAME"].'</div></td>
				</tr>';
		}

		if($data['SOURCE_NAME'] <> '')
		{
			$result .=	'<tr>'.
							'<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_STATUS_SOURCE").':</td>'.
							'<td class="adm-detail-content-cell-r">'.htmlspecialcharsbx($data['SOURCE_NAME']).'</td>'.
						'</tr>';
		}

		$attr = array(
				"class" => "adm-bus-select",
				"id" => "STATUS_ID"
		);

		if($orderLocked)
			$attr["disabled"] = "disabled";

		$result .= '<tr>
						<td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_STATUS").':</td>
						<td class="adm-detail-content-cell-r">'.
								\Bitrix\Sale\Helpers\Admin\OrderEdit::makeSelectHtml(
								"STATUS_ID",
								self::getStatusesList($user->GetID(), $data["STATUS_ID"]),
								$data["STATUS_ID"],
								false,
								$attr
							);

		if($showSaveButton && !$orderLocked)
		{
			$result .= '
				&nbsp;
				<span id="save_status_button" class="adm-btn" onclick="BX.Sale.Admin.OrderEditPage.onSaveStatusButton(\''.$order->getId().'\',\'STATUS_ID\');">
					'.Loc::getMessage("SALE_ORDER_STATUS_SAVE").'
				</span>
				<span id="save_status_result_ok" class="adm-sale-green-check-mark" style="display:none;" title="'.Loc::getMessage('SALE_ORDER_STATUS_CHANGED_SUCCESS').'"></span>
				';
		}

		$result .= '</td>
			</tr>';

		if (\Bitrix\Sale\Helpers\Order::isAllowGuestView($order))
		{
			$result .='<tr><td class="adm-detail-content-cell-l">'.Loc::getMessage("SALE_ORDER_GUEST_VIEW").':</td><td class="adm-detail-content-cell-r">';

			$publicLink = \Bitrix\Sale\Helpers\Order::getPublicLink($order);
			if (empty($publicLink))
			{
				$result .= Loc::getMessage("SALE_ORDER_WRONG_GUEST_PATH", array("#LANGUAGE_ID#" => LANGUAGE_ID));
			}
			else
			{
				$result .= "<a href='{$publicLink}' target='_blank'>".
					Loc::getMessage("SALE_ORDER_GUEST_PATH", array('#ID#' => $order->getId(),"#ACCOUNT_NUMBER#" => $order->getField('ACCOUNT_NUMBER'))).
					"</a>";
			}

			$result .='</td></tr>';
		}

		if($showCancel && $allowCancel)
			$result .= self::getCancelBlockHtml($order, $data, $orderLocked);

		$result .= '</tbody>
			</table>
			';

		return $result;
	}

	protected static function getCancelBlockHtml(Order $order, array $data, $orderLocked = false)
	{
		$isCanceled = $order->getField('CANCELED') === "Y";

		if($isCanceled)
		{
			$text = '
				<div class="adm-s-select-popup-element-selected" id="sale-adm-status-cancel-blocktext">
					<div class="adm-s-select-popup-element-selected-bad">
						<span>'.Loc::getMessage("SALE_ORDER_STATUS_CANCELED").'</span>
						'.$order->getField('DATE_CANCELED').
							static::renderUserCanceledLink([
							'EMP_CANCELED_ID'=>$order->getField("EMP_CANCELED_ID"),
							'EMP_CANCELED_NAME'=>$data["EMP_CANCELED_NAME"]
						]).'
					</div>
				</div>';
		}
		else
		{
			$text = '
				<div class="adm-s-select-popup-element-selected" style="text-align:center;" id="sale-adm-status-cancel-blocktext">
					<a href="javascript:void(0);" onclick="'.static::getJsObjName().'.toggleCancelDialog();">
						'.Loc::getMessage("SALE_ORDER_STATUS_CANCELING").'
					</a>
				</div>';
		}

		$reasonCanceled = trim((string)$order->getField("REASON_CANCELED"));

		if(!\CSaleYMHandler::isOrderFromYandex($order->getId()))
		{
			$reasonHtml = '
				<div class="adm-s-select-popup-modal-title">'.Loc::getMessage("SALE_ORDER_STATUS_COMMENT").'</div>
				<textarea style="width:400px;min-height:100px;" name="FORM_REASON_CANCELED" id="FORM_REASON_CANCELED"'.($isCanceled ? ' disabled' : '' ).'>'.($reasonCanceled <> '' ? htmlspecialcharsbx($reasonCanceled) : '').'</textarea>
			';
		}
		else
		{
			$reasonHtml = '
				<div class="adm-s-select-popup-modal-title">'.Loc::getMessage("SALE_ORDER_STATUS_CANCELING_REASON").'</div>
				<select name="FORM_REASON_CANCELED" style="max-width: 400px;" id="FORM_REASON_CANCELED" class="adm-bus-select"'.($isCanceled ? ' disabled' : '' ).'>';

			foreach (\CSaleYMHandler::getOrderSubstatuses() as $statusId => $statusName)
				$reasonHtml .= '<option value="'.$statusId.'"'.($statusId == $reasonCanceled ? " selected" : "").'>'.htmlspecialcharsbx($statusName).'</option>';

			$reasonHtml .= '</select>';
		}

		return '
			<tr id="sale-adm-status-cancel-row">
				<td class="adm-detail-content-cell-l">&nbsp;</td>
				<td class="adm-detail-content-cell-r">
					<div class="adm-s-select-popup-box">
						<div class="adm-s-select-popup-container">'.
							($orderLocked ? '' : '<div class="adm-s-select-popup-element-selected-control" onclick="'.static::getJsObjName().'.toggleCancelDialog();"></div>').
							$text.
						'</div>
						<div class="adm-s-select-popup-modal /*active*/" id="sale-adm-status-cancel-dialog">
							<div class="adm-s-select-popup-modal-content">
								'.$reasonHtml.'
								<div class="adm-s-select-popup-modal-desc">'.Loc::getMessage("SALE_ORDER_STATUS_USER_CAN_VIEW").'</div>
								<span class="adm-btn" id="sale-adm-status-cancel-dialog-btn" onclick="'.static::getJsObjName().'.onCancelStatusButton(\''.$order->getId().'\',\''.$data["CANCELED"].'\');">
									'.($data["CANCELED"] == "N" ? Loc::getMessage("SALE_ORDER_STATUS_CANCEL") : Loc::getMessage("SALE_ORDER_STATUS_CANCEL_CANCEL")).'
								</span>
								<span class="adm-s-select-popup-modal-close" onclick="'.static::getJsObjName().'.toggleCancelDialog();">'.Loc::getMessage("SALE_ORDER_STATUS_TOGGLE").'</span>
							</div>
						</div>
					</div>
				</td>
			</tr>';
	}

	public static function getUserInfo($userId)
	{
		static $users = array();

		$userId = intval($userId);
		if ($userId <= 0)
			return array( "ID"=>0, "NAME" => "", "LOGIN"=>"");

		if (isset($users[$userId]))
			return $users[$userId];

		$dbRes = \CUser::GetList("id", "asc", array("ID" => $userId), array("FIELDS" => array("ID", "NAME", "LOGIN")));
		$user = $dbRes->Fetch();

		if ($user)
			$users[$userId] = $user;
		else
			$user = array( "ID"=>0, "NAME" => "", "LOGIN"=>"");

		return $user;
	}

	protected static function prepareData(Order $order)
	{
		static $result = null;

		if($result === null)
		{
			$creator = static::getUserInfo($order->getField("CREATED_BY"));

			if($order->getField("CREATED_BY") <> '')
				$creatorName = OrderEdit::getUserName($order->getField("CREATED_BY"), $order->getSiteId());
			else
				$creatorName = "";

			if($order->getField("EMP_CANCELED_ID") <> '')
				$cancelerName = OrderEdit::getUserName($order->getField("EMP_CANCELED_ID"), $order->getSiteId());
			else
				$cancelerName = "";

			$sourceName = "";

			if($order->getField('XML_ID') <> '')
			{
				$dbRes = OrderTable::getList(array(
					'filter' => array(
						'ORDER_ID' => $order->getId()
					),
					'select' => array('SOURCE_NAME' => 'TRADING_PLATFORM.NAME')
				));

				if($tpOrder = $dbRes->fetch())
					$sourceName = $tpOrder['SOURCE_NAME'];
			}

			$result = array(
				"DATE_INSERT" => ($order->getDateInsert() instanceof Date) ? $order->getDateInsert()->toString() : '',
				"DATE_UPDATE" => ($order->getField('DATE_UPDATE') instanceof Date) ? $order->getField('DATE_UPDATE')->toString() : '',
				"CREATOR_USER_NAME" => $creatorName,
				"CREATOR_USER_ID" => $creator["ID"],
				"STATUS_ID" => $order->getField('STATUS_ID'),
				"CANCELED" => $order->getField("CANCELED"),
				"EMP_CANCELED_NAME" => $cancelerName,
				"SOURCE_NAME" => $sourceName
			);

			if(intval($order->getField('AFFILIATE_ID')) > 0)
			{
				$result["AFFILIATE_ID"] = intval($order->getField('AFFILIATE_ID'));

				$dbAffiliate = \CSaleAffiliate::GetList(
					array(),
					array("ID" => $result["AFFILIATE_ID"]),
					false,
					false,
					array("ID", "USER_ID")
				);

				if($arAffiliate = $dbAffiliate->Fetch())
				{
					$result["AFFILIATE_ID"] = $arAffiliate["ID"];
					$result["AFFILIATE_NAME"] = OrderEdit::getUserName($arAffiliate["USER_ID"], $order->getSiteId());
				}
				else
				{
					$result["AFFILIATE_ID"] = 0;
					$result["AFFILIATE_NAME"] = "-";
				}
			}
		}

		return $result;
	}

	public static function getScripts(Order $order, $userId)
	{
		$langPhrases = array("SALE_ORDER_STATUS_SAVE", "SALE_ORDER_STATUS_CANCEL", "SALE_ORDER_STATUS_CHANGE_ERROR",
			"SALE_ORDER_STATUS_CANCEL_ERROR", "SALE_ORDER_STATUS_CANCEL_CANCEL", "SALE_ORDER_STATUS_CHANGED_SUCCESS",
			"SALE_ORDER_STATUS_CANCELED", "SALE_ORDER_STATUS_CANCELING");

		$result = '
			<script>
				BX.ready(function(){
					BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters({
						STATUS_ID: {
							callback: BX.Sale.Admin.OrderEditPage.setStatus,
							context: this
						}
					});
					BX.Sale.Admin.OrderEditPage.statusesNames = '.\CUtil::PhpToJSObject(self::getStatusesList($userId, $order->getField('STATUS_ID'))).';
					BX.Sale.Admin.OrderEditPage.callFieldsUpdaters('.\CUtil::PhpToJSObject(self::prepareData($order)).');';

		foreach($langPhrases as $phrase)
			$result .= ' BX.message({'.$phrase.': "'.\CUtil::JSEscape(Loc::getMessage($phrase)).'"});';

		$result .= '});	</script>';
		return $result;
	}

	public static function getStatusesList($userId, $orderStatus = false)
	{
		if($orderStatus === false)
			$orderStatus = \Bitrix\Sale\OrderStatus::getInitialStatus();

		$result = \Bitrix\Sale\OrderStatus::getAllowedUserStatuses($userId, $orderStatus);

		if(empty($result[$orderStatus]))
		{
			$dbRes = \Bitrix\Sale\Internals\StatusTable::getList(array(
				'select' => array('ID', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'),
				'filter' => array(
					'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID,
					'=ID' => $orderStatus
				)
			));

			if($status = $dbRes->fetch())
				$result = array($orderStatus => $status['NAME']) + $result;
		}

		return $result;
	}

	public static function getEditSimple($userId, $fieldName, $status)
	{
		return '
			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
				<tbody>
					<tr>
						<td class="adm-detail-content-cell-l" width="40%">'.
							Loc::getMessage("SALE_ORDER_STATUS").':'.
						'</td>
						<td class="adm-detail-content-cell-r">'.
							\Bitrix\Sale\Helpers\Admin\OrderEdit::makeSelectHtml(
								$fieldName,
								self::getStatusesList($userId, $status),
								$status,
								false,
								array(
									"class" => "adm-bus-select",
									"id" => "STATUS_ID"
								)
							).
						'</td>
					</tr>
				</tbody>
			</table>';
	}

	protected static function renderCreatorLink($data)
	{
		return '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='. $data["CREATOR_USER_ID"].'">'.htmlspecialcharsbx($data["CREATOR_USER_NAME"]).'</a>';
	}

	protected static function renderUserCanceledLink($data)
	{
		return '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$data["EMP_CANCELED_ID"].'">'.htmlspecialcharsbx($data["EMP_CANCELED_NAME"]).'</a>';
	}

	protected static function getJsObjName()
	{
		return 'BX.Sale.Admin.OrderEditPage';
	}
}
