<?php

namespace Bitrix\Sale\Delivery\Services\Taxi;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Internals\LocalDeliveryRequestTable;
use Bitrix\Sale\Shipment;

/**
 * Class Taxi
 * @package Bitrix\Sale\Delivery\Services\Taxi
 * @internal
 */
abstract class Taxi extends Base implements ITaxiDeliveryService
{
	const TAXI_REQUEST_CREATED_EVENT_CODE = 'OnDeliveryTaxiRequestCreated';
	const TAXI_REQUEST_CANCELLED_EVENT_CODE = 'OnDeliveryTaxiRequestCancelled';

	/**
	 * @inheritdoc
	 */
	public function createTaxiRequest(Shipment $shipment): CreationRequestResult
	{
		$result = new CreationRequestResult();

		$creationExternalRequestResult = $this->createTaxiExternalRequest($shipment);
		if (!$creationExternalRequestResult->isSuccess())
		{
			return $result->addErrors($creationExternalRequestResult->getErrors());
		}

		$addResult = LocalDeliveryRequestTable::add(
			[
				'DELIVERY_SERVICE_ID' => $shipment->getDeliveryId(),
				'SHIPMENT_ID' => $shipment->getId(),
				'CREATED_AT' => new DateTime(),
				'EXTERNAL_ID' => $creationExternalRequestResult->getExternalRequestId(),
			]
		);
		if (!$addResult->isSuccess())
		{
			return $result->addError(new Error('db error'));
		}

		$result
			->setStatus($creationExternalRequestResult->getStatus())
			->setRequestId($addResult->getId());

		(new Event(
			'sale',
			static::TAXI_REQUEST_CREATED_EVENT_CODE,
			[
				'SHIPMENT' => $shipment,
				'RESULT' => $result,
			]
		))->send();

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @return CreationExternalRequestResult
	 */
	abstract protected function createTaxiExternalRequest(Shipment $shipment): CreationExternalRequestResult;

	/**
	 * @inheritdoc
	 */
	public function cancelTaxiRequest(int $requestId): CancellationRequestResult
	{
		$result = new CancellationRequestResult();

		$request = LocalDeliveryRequestTable::getById($requestId)->fetch();
		if (!$request)
		{
			return $result->addError(new Error('Request has not been found'));
		}

		$this->cancelTaxiExternalRequest($request['EXTERNAL_ID']);

		(new Event(
			'sale',
			static::TAXI_REQUEST_CANCELLED_EVENT_CODE,
			[
				'REQUEST_ID' => $request['ID'],
				'SHIPMENT_ID' => $request['SHIPMENT_ID'],
				'CANCELLATION_RESULT' => $result,
			]
		))->send();

		return $result;
	}

	/**
	 * @param string $externalRequestId
	 * @return CancellationRequestResult
	 */
	abstract protected function cancelTaxiExternalRequest(string $externalRequestId): CancellationRequestResult;
}
