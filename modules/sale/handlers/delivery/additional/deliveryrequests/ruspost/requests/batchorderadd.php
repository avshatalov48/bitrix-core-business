<?
namespace Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests;

use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Delivery\Requests;
use Bitrix\Main\Localization\Loc;
use Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Reference;

Loc::loadMessages(__FILE__);

/**
 * Class BatchOrderAdd
 * @package Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Requests
 * Add shipment to the batch
 * https://otpravka.pochta.ru/specification#/batches-add_orders_to_batch
 */
class BatchOrderAdd extends Base
{
	protected $path = "/1.0/batch/{name}/shipment";
	protected $type = HttpClient::HTTP_PUT;
	protected $name = '';
	protected $internalRequestId = 0;

	/**
	 * @param array $rawData
	 * @param array $requestData
	 * @return Requests\Result
	 */
	protected function convertResponse($rawData, $requestData)
	{
		$result = new Requests\Result();
		$reqRes = new Requests\RequestResult();
		$reqRes->setExternalId($this->name);
		$reqRes->setInternalId($this->internalRequestId);

		/** @var Requests\ShipmentResult[] $shpResults */
		$shpResults = array();
		$errorPositions = array();

		if(is_array($rawData['errors']))
		{
			foreach($rawData['errors'] as $error)
			{
				if(!isset($requestData[$error['position']]))
					continue;

				$internalId = $requestData[$error['position']]['order-num'];
				$errorPositions[$error['position']] = true;

				if(!isset($shpResults[$internalId]))
					$shpResults[$internalId] = new Requests\ShipmentResult($internalId);

				if(is_array($error['error-codes']))
				{
					$message = '';

					foreach($error['error-codes'] as $errorCode)
					{
						$message = Reference::getErrorDescription($errorCode['code'], 'POST /1.0/user/shipment');

						if(!empty($errorCode['details']) && $message.'.' != $errorCode['details'])
						{
							if($errorCode['code'] == 'ILLEGAL_MAIL_CATEGORY')
								$message = str_replace('%s', Reference::getRpoCategory($errorCode['details']), $message);
							else
								$message .= ' ('.$errorCode['details'].')';
						}
					}

					if(strlen($message) > 0)
						$shpResults[$internalId]->addError(new Error($message));
				}
			}
		}

		if(is_array($rawData['result-ids']) && is_array($requestData))
		{
			$resultIdsPosition = 0;
			$deliveryRequest = $this->deliveryService->getDeliveryRequestHandler();

			/** @var Requests\Result $res */
			$res = $deliveryRequest->getRequestObject('BATCH')->process(array('BATCH_NAME' => $this->name));
			$listNumber = '';
			$listDate = '';

			if($res->isSuccess())
			{
				$batchData = $res->getData();

				if(!empty($batchData['list-number']))
					$listNumber = $batchData['list-number'];

				if(!empty($batchData['list-number-date']))
					$listDate = $batchData['list-number-date'];
			}

			/** @var Requests\Result $res */
			$res =  $deliveryRequest->send('BATCH_ORDERS', array('BATCH_NAME' => $this->name));
			$batchOrders = array();

			if($res->isSuccess())
				$batchOrders = $res->getData();

			for($i = 0, $l = count($requestData); $i < $l; $i++)
			{
				if(isset($errorPositions[$i]))
					continue;

				$internalId = $requestData[$i]['order-num'];
				$externalId = $rawData['result-ids'][$resultIdsPosition];
				$resultIdsPosition++;

				if(!isset($shpResults[$internalId]))
				{
					$shpResults[$internalId] = new Requests\ShipmentResult($internalId, $externalId);

					foreach($batchOrders as $order)
					{
						if($order['order-num'] == $internalId)
						{
							$shpResults[$internalId]->setTrackingNumber($order['barcode']);
							break;
						}
					}

					$shpResults[$internalId]->setDeliveryDocNum($listNumber);
					$shpResults[$internalId]->setDeliveryDocDate($listDate);
				}
			}
		}

		if(!empty($shpResults))
		{
			$reqRes->addResults(array_values($shpResults));
			$result->addResult($reqRes);
		}

		return $result;
	}

	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function createBody(array $shipmentIds, array $additional = array())
	{
		/** @var  \Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Handler $deliveryRequest */
		$deliveryRequest = $this->deliveryService->getDeliveryRequestHandler();
		$orderCreateRequest = $deliveryRequest->getRequestObject('ORDER_CREATE');
		$result = $orderCreateRequest->createBody($shipmentIds);

		if(!$result->isSuccess())
			return $result;

		$data = $result->getData();

		if(empty($data))
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_BOA_DATA_EMPTY')));

		return $result;

	}

	/**
	 * @param int[] $shipmentIds
	 * @param array $additional
	 * @return Requests\Result
	 */
	public function process(array $shipmentIds, array $additional = array())
	{
		if(empty($additional['BATCH_NAME']))
		{
			$result = new Requests\Result();
			$result->addError(new Error(Loc::getMessage('SALE_DLVRS_ADD_DREQ_BOA_BNAME_EMPTY')));
		}
		$this->path = str_replace('{name}', $additional['BATCH_NAME'], $this->path);
		$this->name = $additional['BATCH_NAME'];
		$this->internalRequestId = $additional['REQUEST_ID'];
		return parent::process($shipmentIds, $additional);
	}
}