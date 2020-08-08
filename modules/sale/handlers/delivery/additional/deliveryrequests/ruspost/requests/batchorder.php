<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests;

Loc::loadMessages(__FILE__);

/**
 * Class BatchOrder
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Searching for shipment by id
 * https://otpravka.pochta.ru/specification#/batches-find_order_by_id
 */
class BatchOrder extends Base
{
	protected $path = "/1.0/shipment/{id}";
	protected $type = HttpClient::HTTP_GET;

	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function process(array $shipmentIds, array $additional = array())
	{
		$result = new Requests\Result();

		if(count($shipmentIds) !== 1)
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCHO_01')));
			return $result;
		}

		$shipmentId = current($shipmentIds);

		if(intval($shipmentId) <= 0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCHO_01')));
			return $result;
		}

		$res =Requests\ShipmentTable::getList(array(
			'filter' => array(
				'=SHIPMENT_ID' => $shipmentId
			)
		));

		$row = $res->fetch();

		if(!$row || $row['EXTERNAL_ID'] == '')
		{
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCHO_03', array('#SHIPMENT_ID#' => $shipmentId))));
			return $result;
		}

		$this->path = str_replace('{id}', $row['EXTERNAL_ID'], $this->path);
		return $this->send();
	}
}