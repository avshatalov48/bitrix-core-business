<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Delivery\Requests;
use Bitrix\Main\Localization\Loc;
use Sale\Handlers\Delivery\AdditionalHandler;
use Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Reference;
use Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Handler;

Loc::loadMessages(__FILE__);

/**
 * Class BatchCreate
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Creates batch from shipments
 * https://otpravka.pochta.ru/specification#/batches-create_batch_from_N_orders
 */
class BatchCreate extends Base
{
	protected $path = "/1.0/user/shipment";
	protected $type = HttpClient::HTTP_POST;
	/** @var AdditionalHandler */
	protected $deliveryService = null;
	protected $shipmentIdsMap = array(); // array( internalId => externalId, ....)

	/**
	 * @param array $rawData
	 * @param array $requestData
	 * @return Requests\Result
	 */
	protected function convertResponse($rawData, $requestData)
	{
		$result = new Requests\Result();
		$errorPositions = array();
		$flippedIdsMap = array_flip($this->shipmentIdsMap);

		/** @var Requests\RequestResult[] $batchesResults */
		$batchesResults = array();
		/** @var Requests\ShipmentResult[] $shipmentResults */
		$shipmentResults = array();

		if(is_array($rawData['errors']))
		{
			foreach($rawData['errors'] as $error)
			{
				if(!isset($requestData[$error['position']]))
					continue;

				$externalShipmentId = $requestData[$error['position']];
				$internalShipmentId = $flippedIdsMap[$externalShipmentId];

				if(!isset($shipmentResults[$internalShipmentId]))
					$shipmentResults[$internalShipmentId] = new Requests\ShipmentResult($internalShipmentId, $externalShipmentId);

				$errorPositions[] = $error['position'];
				$message = Reference::getErrorDescription($error['error-code'], 'POST /1.0/user/shipment');

				if(!empty($error['error-details']))
					$message .= ' ('.$error['error-details'].')';

				$shipmentResults[$internalShipmentId]->addError(new Main\Error($message));
			}
		}

		if(is_array($rawData['batches']) && !empty($rawData['batches']))
		{
			foreach($rawData['batches'] as $id => $batch)
			{
				$shpResults = array();
				$batchesResults[$id] = new Requests\RequestResult();
				$resOrders = $this->getBatchOrders($batch['batch-name']);

				if(!$resOrders->isSuccess())
				{
					$batchesResults[$id]->addErrors($resOrders->getErrors());
					continue;
				}

				foreach($resOrders->getData() as $order)
				{
					/** @var \Bitrix\Sale\Delivery\Requests\ShipmentResult[] $shpResults*/
					$shpResults[$order['order-num']] = new Requests\ShipmentResult($order['order-num'], intval($order['id']));
					$shpResults[$order['order-num']]->setTrackingNumber($order['barcode']);
					$shpResults[$order['order-num']]->setDeliveryDocNum($order['list-number']);
					$shpResults[$order['order-num']]->setDeliveryDocDate($order['ist-number-date']);
				}

				$batchesResults[$id]->setResults($shpResults);
				$batchesResults[$id]->setExternalId($batch['batch-name']);
				$batchesResults[$id]->setData($batch);
			}
		}

		$result->setResults(
			array_merge(
				array_values($batchesResults),
				array_values($shipmentResults)
			)
		);

		return $result;
	}

	/**
	 * @param string $batchName
	 * @return Requests\Result
	 */
	protected function getBatchOrders($batchName)
	{
		/** @var Handler $deliveryRequest */
		$deliveryRequest = $this->deliveryService->getDeliveryRequestHandler();
		return $deliveryRequest->send('BATCH_ORDERS', array('BATCH_NAME' => $batchName));
	}

	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function createBody(array $shipmentIds, array $additional = array())
	{
		$result = new Requests\Result();

		if(empty($shipmentIds))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCHC_01')));
			return $result;
		}

		/** @var  \Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Handler $deliveryRequest */
		$deliveryRequest = $this->deliveryService->getDeliveryRequestHandler();
		$request = $deliveryRequest->getRequestObject('ORDER_CREATE');
		$res = $request->process($shipmentIds, $additional);

		$addedShipments = array();

		if(!$res->isSuccess())
			return $res;

		/** @var Requests\ShipmentResult[] $shipmentCreateResults */
		$shipmentCreateResults = $res->getShipmentResults();

		if(!empty($shipmentCreateResults))
		{
			foreach($shipmentCreateResults as $createResult)
			{
				$shipmentId = $createResult->getInternalId();

				if($createResult->isSuccess() && strlen($createResult->getExternalId()) > 0)
					$this->shipmentIdsMap[$shipmentId] = $createResult->getExternalId();
			}

			$addedShipments = array_values($this->shipmentIdsMap);
			$result->setResults($shipmentCreateResults);
		}

		if(!empty($addedShipments))
			$result->setData($addedShipments);
		else
			$result->addError(new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCHC_03')));

		return $result;
	}

	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function process(array $shipmentIds, array $additional = array())
	{
		$result = new Requests\Result();

		if(!empty($additional['DATE']))
		{
			try
			{
				$date = new Main\Type\Date($additional['DATE']);
			}
			catch (Main\ObjectException $exception)
			{
				$result->addError(new Main\Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_RBATCHC_04')));
				return $result;
			}

			$this->path = $this->path.'?sending-date='.$date->format('Y').'-'.$date->format('m').'-'.$date->format('d');
		}

		return parent::process($shipmentIds, $additional);
	}
}