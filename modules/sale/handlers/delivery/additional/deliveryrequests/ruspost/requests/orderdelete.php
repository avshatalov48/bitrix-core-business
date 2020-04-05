<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests;
use Bitrix\Sale\Delivery\Requests\ShipmentTable;
use Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Reference;

Loc::loadMessages(__FILE__);

/**
 * Class OrderDelete
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Deletes order
 * https://otpravka.pochta.ru/specification#/orders-delete_new_order
 */
class OrderDelete extends Base
{
	protected $path = "/1.0/shipment";
	protected $type = 'DELETE';
	protected $idsMap = array(); // array('internalId' => 'externalId', ....)

	/**
	 * @param array $rawData
	 * @param array $requestData
	 * @return Requests\Result
	 */
	protected function convertResponse($rawData, $requestData)
	{
		$result = new Requests\Result();
		$deleteResults = array();
		$idsMapFlipped = array_flip($this->idsMap);

		if(is_array($rawData['errors']))
		{
			foreach($rawData['errors'] as $error)
			{
				if(!isset($requestData[$error['position']]))
					continue;

				$externalId = $requestData[$error['position']];
				$internalId = $idsMapFlipped[$externalId];

				if(!isset($deleteResults[$internalId]))
					$deleteResults[$internalId] = new Requests\ShipmentResult($internalId, $externalId);

				if($error['error-code'] == 'NOT_FOUND')
					continue;

				$message = Reference::getErrorDescription($error['error-code'], 'DELETE /1.0/backlog');

				if(!empty($error['error-details']))
					$message .= ' ('.$error['error-details'].')';

				$deleteResults[$internalId]->addError(new Main\Error($message));
			}
		}

		if(is_array($rawData['result-ids']))
		{
			foreach($rawData['result-ids'] as $externalId)
			{
				$internalId = $idsMapFlipped[$externalId];

				if(!isset($deleteResults[$internalId]))
					$deleteResults[$internalId] = new Requests\ShipmentResult($internalId, $externalId);
			}
		}

		$result->setResults($deleteResults);
		return $result;
	}

	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function createBody(array $shipmentIds, array $additional = array())
	{
		$result = new Requests\Result();

		$res = ShipmentTable::getList(array(
			'filter' => array(
				'=SHIPMENT_ID' => $shipmentIds
			)
		));

		while($row = $res->fetch())
			if(strlen($row['EXTERNAL_ID']) > 0)
				$this->idsMap[$row['SHIPMENT_ID']] = $row['EXTERNAL_ID'];

		if(!empty($this->idsMap))
			$result->setData(array_values($this->idsMap));
		else
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_ROD_01').' "'.implode('", "',$shipmentIds).'"'));

		return $result;
	}
}