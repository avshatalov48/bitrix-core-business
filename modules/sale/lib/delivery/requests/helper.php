<?
namespace Bitrix\Sale\Delivery\Requests;

use Bitrix\Sale\Delivery\Services;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Shipment;
use Bitrix\Sale;


/**
 * Class Helper
 * @package Bitrix\Sale\Delivery\Requests
 * @internal
 */
class Helper
{
	/**
	 * @param int $shipmentId
	 * @param string $text Link text
	 * @param int $orderId
	 * @param string $languageId
	 * @return string <a...>...</> Link to shipment edit form
	 */
	public static function getShipmentEditLink($shipmentId, $text = '', $orderId = 0, $languageId = LANGUAGE_ID)
	{
		if($text == '')
			$text = strval($shipmentId);

		if(intval($orderId) <= 0)
		{
			$res = Internals\ShipmentTable::getList(array(
				'filter' => array(
					'=ID' => $shipmentId
				),
				'select' => array('ID', 'ORDER_ID')
			));

			if($row = $res->fetch())
				$orderId = $row['ORDER_ID'];
		}

		return '<a href="/bitrix/admin/sale_order_shipment_edit.php'.
			'?order_id='.intval($orderId).
			'&shipment_id='.intval($shipmentId).
			'&lang='.htmlspecialcharsbx($languageId).
			'">'.
				htmlspecialcharsbx($text).
			'</a>';
	}

	/**
	 * @param int $deliveryId
	 * @param string $deliveryName
	 * @param string $languageId
	 * @return string <a...>...</> Link to delivery edit form
	 */
	public static function getDeliveryEditLink($deliveryId, $deliveryName = '', $languageId = LANGUAGE_ID)
	{
		if($deliveryName == '')
		{
			$delivery = Services\Manager::getObjectById($deliveryId);
			$deliveryName = !!$delivery ? $delivery->getNameWithParent().' ['.intval($deliveryId).']' : intval($deliveryId);
		}

		return '<a href="/bitrix/admin/sale_delivery_service_edit.php'.
			'?ID='.intval($deliveryId).
			'&lang='.htmlspecialcharsbx($languageId).
			'">'.
				htmlspecialcharsbx($deliveryName).
			'</a>';
	}

	/**
	 * @param int $requestId
	 * @param string $text
	 * @param string $languageId
	 * @return string <a...>...</> Link to request view form
	 */
	public static function getRequestViewLink($requestId, $text = '', $languageId = LANGUAGE_ID)
	{
		if($text == '')
			$text = strval($requestId);

		return '<a href="/bitrix/admin/sale_delivery_request_view.php'.
			'?ID='.intval($requestId).
			'&lang='.htmlspecialcharsbx($languageId).
			'">'.
				htmlspecialcharsbx($text).
			'</a>';
	}

	/**
	 * @param int[] $shipmentIds
	 * @return Shipment[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getShipmentsByIds(array $shipmentIds)
	{
		if(empty($shipmentIds))
			return array();

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$result = array();

		$res = Internals\ShipmentTable::getList(array(
			'filter' => array(
				'=ID' => $shipmentIds
			),
			'select' => array('ID', 'ORDER_ID')
		));

		while($shp = $res->fetch())
		{
			$order = $orderClass::load($shp['ORDER_ID']);

			foreach($order->getShipmentCollection() as $shipment)
			{
				if($shp['ID'] != $shipment->getId())
					continue;

				if(!in_array($shp['ID'], $shipmentIds))
					continue;

				$result[$shp['ID']] = $shipment;
				break;
			}
		}

		return $result;
	}
}