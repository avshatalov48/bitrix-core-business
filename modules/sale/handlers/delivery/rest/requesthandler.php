<?php

namespace Sale\Handlers\Delivery\Rest;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests\HandlerBase;
use Bitrix\Sale\Delivery\Requests\RequestResult;
use Bitrix\Sale\Delivery\Requests\Result;
use Bitrix\Sale;
use Bitrix\Sale\Delivery\Requests\ShipmentResult;
use Sale\Handlers\Delivery\Rest\DataProviders;

Loc::loadMessages(__FILE__);

/***
 * Class RequestHandler
 * @package Sale\Handlers\Delivery
 */
class RequestHandler extends HandlerBase
{
	/** @var string */
	private $createRequestUrl;

	/** @var string */
	private $cancelRequestUrl;

	/** @var string */
	private $cancelActionName;

	/** @var string */
	private $deleteRequestUrl;

	/** @var bool */
	private $hasCallbackTrackingSupport = false;

	/**
	 * @param string $createRequestUrl
	 * @return RequestHandler
	 */
	public function setCreateRequestUrl(string $createRequestUrl): RequestHandler
	{
		$this->createRequestUrl = $createRequestUrl;
		return $this;
	}

	/**
	 * @param string $cancelRequestUrl
	 * @return RequestHandler
	 */
	public function setCancelRequestUrl(string $cancelRequestUrl): RequestHandler
	{
		$this->cancelRequestUrl = $cancelRequestUrl;
		return $this;
	}

	/**
	 * @param string $cancelActionName
	 * @return RequestHandler
	 */
	public function setCancelActionName(string $cancelActionName): RequestHandler
	{
		$this->cancelActionName = $cancelActionName;
		return $this;
	}

	/**
	 * @param string $deleteRequestUrl
	 * @return RequestHandler
	 */
	public function setDeleteRequestUrl(string $deleteRequestUrl): RequestHandler
	{
		$this->deleteRequestUrl = $deleteRequestUrl;
		return $this;
	}

	/**
	 * @param bool $hasCallbackTrackingSupport
	 * @return RequestHandler
	 */
	public function setHasCallbackTrackingSupport(bool $hasCallbackTrackingSupport): RequestHandler
	{
		$this->hasCallbackTrackingSupport = $hasCallbackTrackingSupport;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function create(array $shipmentIds, array $additional = [])
	{
		$result = new Result();

		if (!$this->createRequestUrl)
		{
			return $result->addError(new Main\Error(
				Loc::getMessage('SALE_DELIVERY_REST_REQUEST_HANDLER_ACTION_NOT_SUPPORTED'))
			);
		}

		$shipments = Sale\Repository\ShipmentRepository::getInstance()->getByIds($shipmentIds);
		if (empty($shipments))
		{
			return $result->addError(new Main\Error(
				Loc::getMessage('SALE_DELIVERY_REST_REQUEST_SHIPMENTS_NOT_FOUND'))
			);
		}

		$responseResult = Sale\Helpers\Rest\Http::sendRequest(
			$this->createRequestUrl,
			[
				'SHIPMENTS' => array_map(
					function ($shipment) { return DataProviders\Shipment::getData($shipment); },
					$shipments
				),
			],
			[
				'JSON_REQUEST' => true,
			]
		);
		if (!$responseResult->isSuccess())
		{
			return $result->addError(new Main\Error(Loc::getMessage('SALE_DELIVERY_REST_REQUEST_NETWORK_ERROR')));
		}

		$responseData = $responseResult->getData();
		self::preProcessResponse($responseData, $result);

		if (
			!isset($responseData['REQUEST_ID'])
			|| !is_string($responseData['REQUEST_ID'])
			|| empty($responseData['REQUEST_ID'])
		)
		{
			return $result->addError(
				new Main\Error(Loc::getMessage('SALE_DELIVERY_REST_REQUEST_REQUEST_ID_NOT_SPECIFIED'))
			);
		}

		$requestResult = new RequestResult();
		$requestResult->setExternalId($responseData['REQUEST_ID']);
		foreach ($shipments as $shipment)
		{
			$requestResult->addResult(new ShipmentResult($shipment->getId()));
		}
		$result->addResult($requestResult);

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getActions($requestId)
	{
		return [
			$this->getCancelActionCode() => $this->getCancelActionName()
		];
	}

	/**
	 * @inheritDoc
	 */
	public function executeAction($requestId, $actionType, array $additional)
	{
		if ($actionType === $this->getCancelActionCode())
		{
			return $this->cancelRequest($requestId);
		}

		return parent::executeAction($requestId, $actionType, $additional);
	}

	/**
	 * @param $requestId
	 * @return Result
	 */
	private function cancelRequest($requestId): Result
	{
		$result = new Result();

		if (!$this->cancelRequestUrl)
		{
			return $result->addError(new Main\Error(
				Loc::getMessage('SALE_DELIVERY_REST_REQUEST_HANDLER_ACTION_NOT_SUPPORTED'))
			);
		}

		$request = Sale\Delivery\Requests\RequestTable::getById($requestId)->fetch();
		if (!$request)
		{
			return $result->addError(
				new Main\Error(Loc::getMessage('SALE_DELIVERY_REST_REQUEST_HANDLER_REQUEST_NOT_FOUND'))
			);
		}

		$responseResult = Sale\Helpers\Rest\Http::sendRequest(
			$this->cancelRequestUrl,
			[
				'DELIVERY_ID' => $request['DELIVERY_ID'],
				'REQUEST_ID' => $request['EXTERNAL_ID'],
			],
			[
				'JSON_REQUEST' => true,
			]
		);
		if (!$responseResult->isSuccess())
		{
			return $result->addError(new Main\Error(Loc::getMessage('SALE_DELIVERY_REST_REQUEST_NETWORK_ERROR')));
		}

		self::preProcessResponse($responseResult->getData(), $result);

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function delete($requestId)
	{
		$result = new Result();

		if (!$this->deleteRequestUrl)
		{
			return $result;
		}

		$request = Sale\Delivery\Requests\RequestTable::getById($requestId)->fetch();
		if (!$request)
		{
			return $result->addError(
				new Main\Error(Loc::getMessage('SALE_DELIVERY_REST_REQUEST_HANDLER_REQUEST_NOT_FOUND'))
			);
		}

		$responseResult = Sale\Helpers\Rest\Http::sendRequest(
			$this->deleteRequestUrl,
			[
				'DELIVERY_ID' => $request['DELIVERY_ID'],
				'REQUEST_ID' => $request['EXTERNAL_ID'],
			],
			[
				'JSON_REQUEST' => true,
			]
		);
		if (!$responseResult->isSuccess())
		{
			return $result->addError(new Main\Error(Loc::getMessage('SALE_DELIVERY_REST_REQUEST_NETWORK_ERROR')));
		}

		self::preProcessResponse($responseResult->getData(), $result);

		return $result;
	}

	/**
	 * @param array $responseData
	 * @param Main\Result $result
	 */
	private static function preProcessResponse(array $responseData, \Bitrix\Main\Result $result): void
	{
		if (!(isset($responseData['SUCCESS']) && $responseData['SUCCESS'] === 'Y'))
		{
			$errorText = (
				isset($responseData['REASON']['TEXT'])
				&& is_string($responseData['REASON']['TEXT'])
				&& !empty($responseData['REASON']['TEXT'])
			)
				? $responseData['REASON']['TEXT']
				: Loc::getMessage('SALE_DELIVERY_REST_REQUEST_UNKNOWN_ERROR');

			$result->addError(new Main\Error($errorText));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function hasCallbackTrackingSupport(): bool
	{
		return $this->hasCallbackTrackingSupport;
	}
}
