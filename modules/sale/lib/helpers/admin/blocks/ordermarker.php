<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks;

use Bitrix\Main\Event;
use Bitrix\Sale\EntityMarker;
use Bitrix\Sale\Order;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\Helpers\Admin\OrderEdit;

Loc::loadMessages(__FILE__);

class OrderMarker
{
	
	/**
	 * @param int $orderId
	 *
	 * @return array
	 */
	public static function getView($orderId)
	{
		return static::getViewList($orderId);
	}

	/**
	 * @param int $orderId
	 * @param int $entityId
	 *
	 * @return array
	 */
	public static function getViewForEntity($orderId, $entityId)
	{
		return static::getViewList($orderId, $entityId);
	}

	/**
	 * @param int $orderId
	 * @param null|int $entityId
	 *
	 * @return array
	 */
	protected static function getViewList($orderId, $entityId = null)
	{
		$markerListHtml = '';

		$entityId = intval($entityId);

		$filter = array(
			'filter' => array(
				'=ORDER_ID' => $orderId,
				'!=SUCCESS' => EntityMarker::ENTITY_SUCCESS_CODE_DONE
			),
			'select' => array('ID', 'ORDER_ID', 'MESSAGE', 'TYPE', 'ENTITY_ID', 'ENTITY_TYPE'),
			'order' => array('ID' => 'ASC')
		);

		if (intval($entityId) > 0)
		{
			$filter['filter']['=ENTITY_ID'] = intval($entityId);
		}

		$res = EntityMarker::getList($filter);
		while($data = $res->fetch())
		{
			if ($data['ENTITY_TYPE'] == EntityMarker::ENTITY_TYPE_SHIPMENT)
			{
				$markerListHtml .= static::getShipmentBlockHtml($data['ORDER_ID'], $data['ID'], $data['MESSAGE'], $data['ENTITY_ID'], $data['TYPE'], (intval($entityId) > 0));
			}
			elseif ($data['ENTITY_TYPE'] == EntityMarker::ENTITY_TYPE_PAYMENT)
			{
				$markerListHtml .= static::getPaymentBlockHtml($data['ORDER_ID'], $data['ID'], $data['MESSAGE'], $data['ENTITY_ID'], $data['TYPE'], (intval($entityId) > 0));
			}
			else
			{
				$markerListHtml .=  static::getOrderBlockHtml($data['ORDER_ID'], $data['ID'], $data['MESSAGE'], $data['TYPE']);
			}
		}

		return $markerListHtml;
	}

	/**
	 * @param $orderId
	 * @param $id
	 * @param $text
	 * @param $entityId
	 * @param null $type
	 * @param bool $forEntity
	 *
	 * @return string
	 */
	protected static function getShipmentBlockHtml($orderId, $id, $text, $entityId, $type = null, $forEntity = false)
	{
		if(strval($text) === '')
		{
			$result = "";
		}
		else
		{
			if ($type === null || $type != EntityMarker::ENTITY_MARKED_TYPE_AUTO)
			{
				$type = EntityMarker::ENTITY_MARKED_TYPE_MANUAL;
			}

			$result = '
				<div class="adm-bus-orderproblem" id="sale-adm-shipment-problem-block-'.$id.'">
					<div class="adm-bus-orderproblem-container">
						<table>
							<tr>
								<td class="adm-bus-orderproblem-title">'.Loc::getMessage("SALE_ORDER_MARKER_BLOCK_SHIPMENT_PROBLEM", array(
					'#ENTITY_ID#' => $entityId
				)).':</td>
								<td class="adm-bus-orderproblem-text">'.$text. '<br/>
								<div class="adm-bus-orderproblem-separator"></div>
								';
							if ($type == EntityMarker::ENTITY_MARKED_TYPE_AUTO)
							{
								$result .= '<br/><div><span class="adm-btn adm-btn-green" onclick="BX.Sale.Admin.OrderEditPage.onMarkerFixErrorClick(\''. $id. '\', \''.$orderId.'\', \'sale-adm-shipment-problem-block-'.$id.'\', \''.$entityId.'\', '.($forEntity ? 'true' : 'false').');">'.Loc::getMessage("SALE_ORDER_MARKER_FIX").'</span></div>';
							}
							
			$result .= '</td>
					</tr>
				</table>';


			$result .= '
						<span class="adm-bus-orderproblem-close" title="'.Loc::getMessage("SALE_ORDER_MARKER_CLOSE").'" onclick="BX.Sale.Admin.OrderEditPage.onMarkerCloseClick(\''. $id. '\', \''.$orderId.'\', \'sale-adm-shipment-problem-block-'.$id.'\', \''.$entityId.'\', '.($forEntity ? 'true' : 'false').');"></span>
					</div>
				</div>';
		}
		
		return $result;
	}
	/**
	 * @param $orderId
	 * @param $id
	 * @param $text
	 * @param $entityId
	 * @param $type
	 * @param bool $forEntity
	 *
	 * @return string
	 */
	protected static function getPaymentBlockHtml($orderId, $id, $text, $entityId, $type = null, $forEntity = false)
	{
		if(strval($text) === '')
		{
			$result = "";
		}
		else
		{

			if ($type === null || $type != EntityMarker::ENTITY_MARKED_TYPE_AUTO)
			{
				$type = EntityMarker::ENTITY_MARKED_TYPE_MANUAL;
			}

			$result = '
				<div class="adm-bus-orderproblem" id="sale-adm-payment-problem-block-'.$id.'">
					<div class="adm-bus-orderproblem-container">
						<table>
							<tr>
								<td class="adm-bus-orderproblem-title">'.Loc::getMessage("SALE_ORDER_MARKER_BLOCK_PAYMENT_PROBLEM", array(
					'#ENTITY_ID#' => $entityId
				)).':</td>
								<td class="adm-bus-orderproblem-text">'.$text. '<br/>
								<div class="adm-bus-orderproblem-separator"></div>
								';
			$result .= '</td>
					</tr>
				</table>';

			if ($type == EntityMarker::ENTITY_MARKED_TYPE_AUTO)
			{
				$result .= '<br/><div><span class="adm-btn adm-btn-green" onclick="BX.Sale.Admin.OrderEditPage.onMarkerFixErrorClick(\''. $id. '\', \''.$orderId.'\', \'sale-adm-payment-problem-block-'.$id.'\', \''.$entityId.'\', '.($forEntity ? 'true' : 'false').');">'.Loc::getMessage("SALE_ORDER_MARKER_FIX").'</span></div>';
			}

			$result .= '
						<span class="adm-bus-orderproblem-close" title="'.Loc::getMessage("SALE_ORDER_MARKER_CLOSE").'" onclick="BX.Sale.Admin.OrderEditPage.onMarkerCloseClick(\''. $id. '\', \''.$orderId.'\', \'sale-adm-payment-problem-block-'.$id.'\', \''.$entityId.'\', '.($forEntity ? 'true' : 'false').');"></span>
					</div>
				</div>';
		}

		return $result;
	}

	/**
	 * @param $orderId
	 * @param $id
	 * @param $text
	 * @param null $type
	 *
	 * @return string
	 */
	protected static function getOrderBlockHtml($orderId, $id, $text, $type = null)
	{
		if(strval($text) === '')
		{
			$result = "";
		}
		else
		{
			if ($type === null || $type != EntityMarker::ENTITY_MARKED_TYPE_AUTO)
			{
				$type = EntityMarker::ENTITY_MARKED_TYPE_MANUAL;
			}

			$result = '
				<div class="adm-bus-orderproblem" id="sale-adm-order-problem-block-'.$id.'">
					<div class="adm-bus-orderproblem-container">
						<table>
							<tr>
								<td class="adm-bus-orderproblem-title">'.Loc::getMessage("SALE_ORDER_MARKER_BLOCK_ORDER_PROBLEM").':</td>
								<td class="adm-bus-orderproblem-text">'.$text. '<br/>
								<div class="adm-bus-orderproblem-separator"></div>
								';
			if ($type == EntityMarker::ENTITY_MARKED_TYPE_AUTO)
			{
				$result .= '<br/><div><span class="adm-btn adm-btn-green" onclick="BX.Sale.Admin.OrderEditPage.onMarkerFixErrorClick(\''. $id. '\', \''.$orderId.'\', \'sale-adm-order-problem-block-'.$id.'\');">'.Loc::getMessage("SALE_ORDER_MARKER_FIX").'</span></div>';
			}
			$result .= '</td>
					</tr>
				</table>';


			$result .= '
						<span class="adm-bus-orderproblem-close" title="'.Loc::getMessage("SALE_ORDER_MARKER_CLOSE").'" onclick="BX.Sale.Admin.OrderEditPage.onMarkerCloseClick(\''. $id. '\', \''.$orderId.'\', \'sale-adm-order-problem-block-'.$id.'\');"></span>
					</div>
				</div>';
		}
		
		return $result;
	}
}