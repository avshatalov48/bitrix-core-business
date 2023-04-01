<?php

namespace Sale\Handlers\Delivery\YandexTaxi;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Delivery\Requests\HandlerBase;
use Bitrix\Sale\Delivery\Requests\Manager;
use Bitrix\Sale\Delivery\Requests\Message;
use Bitrix\Sale\Delivery\Requests\RequestResult;
use Bitrix\Sale\Delivery\Requests\RequestTable;
use Bitrix\Sale\Delivery\Requests\Result;
use Bitrix\Sale\Delivery\Requests\ShipmentResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Repository\ShipmentRepository;
use Sale\Handlers\Delivery\YandexTaxi\Api\Api;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Claim;
use Sale\Handlers\Delivery\YandexTaxi\EventJournal\JournalProcessor;
use Sale\Handlers\Delivery\YandexTaxi\Internals\ClaimsTable;

/**
 * Class RequestHandler
 * @package Sale\Handlers\Delivery\YandexTaxi
 */
class RequestHandler extends HandlerBase
{
	/** @var Api */
	private $api;

	/** @var ClaimBuilder\ClaimBuilder */
	private $claimBuilder;

	/** @var JournalProcessor */
	private $journalProcessor;

	/**
	 * @inheritDoc
	 */
	public function __construct(Base $deliveryService)
	{
		parent::__construct($deliveryService);

		$this->api = ServiceContainer::getApi();
		$this->claimBuilder = ServiceContainer::getClaimBuilder();
		$this->journalProcessor = ServiceContainer::getJournalProcessor();
	}

	/**
	 * @inheritDoc
	 */
	public function create(array $shipmentIds, array $additional = array())
	{
		$result = new Result();

		$isShipmentError = false;
		if (empty($shipmentIds) || count($shipmentIds) !== 1)
		{
			$isShipmentError = true;
		}
		else
		{
			$shipment = ShipmentRepository::getInstance()->getById((int)$shipmentIds[0]);
			if (is_null($shipment))
			{
				$isShipmentError = true;
			}
		}
		if ($isShipmentError)
		{
			return $result->addErrors([Loc::getMessage('SALE_YANDEX_TAXI_REQUEST_HANDLER_SHIPMENT_ERROR')]);
		}

		$claimBuildingResult = $this->claimBuilder->build($shipment);
		if (!$claimBuildingResult->isSuccess())
		{
			return $result->addErrors($claimBuildingResult->getErrors());
		}

		/** @var Claim $claim */
		$claim = $claimBuildingResult->getData()['RESULT'];

		$claimCreationResult = $this->api->createClaim($claim);

		if (!$claimCreationResult->isSuccess())
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_CREATE_ERROR')));
		}

		$createdClaim = $claimCreationResult->getClaim();
		if (is_null($createdClaim))
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_PERSIST_ERROR')));
		}

		$addResult = ClaimsTable::add([
			'SHIPMENT_ID' => $shipment->getId(),
			'CREATED_AT' => new DateTime(),
			'UPDATED_AT' => new DateTime(),
			'EXTERNAL_ID' => $createdClaim->getId(),
			'EXTERNAL_STATUS' => $createdClaim->getStatus(),
			'EXTERNAL_CREATED_TS' => $createdClaim->getCreatedTs(),
			'EXTERNAL_UPDATED_TS' => $createdClaim->getUpdatedTs(),
			'INITIAL_CLAIM' => Json::encode($createdClaim),
			'IS_SANDBOX_ORDER' => $this->api->getTransport()->isTestEnvironment() ? 'Y' : 'N',
		]);
		if (!$addResult->isSuccess())
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_PERSIST_ERROR')));
		}

		\CAgent::AddAgent(
			$this->journalProcessor->getAgentName(
				$this->deliveryService->getParentId()
			),
			'sale',
			'N',
			30,
			'',
			'Y',
			'',
			100,
			false,
			false
		);

		$requestResult = new RequestResult();
		$requestResult->setExternalId($createdClaim->getId());
		$requestResult->addResult(new ShipmentResult($shipment->getId()));
		$result->addResult($requestResult);
		$result->setData([
			'STATUS' => Loc::getMessage('SALE_YANDEX_TAXI_REQUEST_HANDLER_STATUS_SEARCHING_PERFORMER_DESCRIPTION'),
			'STATUS_SEMANTIC' => Manager::EXTERNAL_STATUS_SEMANTIC_PROCESS,
		]);

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
	public function cancelRequest($requestId): Result
	{
		$result = new Result();

		$request = RequestTable::getById($requestId)->fetch();
		if (!$request)
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_REQUEST_HANDLER_REQUEST_NOT_FOUND'))
			);
		}

		$getClaimResult = $this->api->getClaim($request['EXTERNAL_ID']);
		if (!$getClaimResult->isSuccess())
		{
			return $result->addErrors($getClaimResult->getErrors());
		}

		$claim = $getClaimResult->getClaim();
		if (is_null($claim))
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION_TMP_ERROR'))
			);
		}

		$getCancelInfoResult = $this->api->getCancelInfo($request['EXTERNAL_ID']);
		if (!$getCancelInfoResult->isSuccess())
		{
			return $result->addErrors($getCancelInfoResult->getErrors());
		}
		$availableCancelState = $getCancelInfoResult->getCancelState();

		$cancellationResult = ServiceContainer::getApi()->cancelClaim(
			$request['EXTERNAL_ID'],
			$claim->getVersion(),
			$availableCancelState
		);

		if (!$cancellationResult->isSuccess())
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION_FATAL_ERROR'))
			);
		}

		if ($availableCancelState === 'paid')
		{
			$result->addMessage(
				new Message(Loc::getMessage(
					'SALE_YANDEX_TAXI_DELIVERY_PAID_CANCELLATION',
					[
						'#SERVICE_NAME#' =>
							$this->deliveryService->getParentService()
								? $this->deliveryService->getParentService()->getName()
								: $this->deliveryService->getName()
						,
					]
				))
			);
		}
		else
		{
			$result->addMessage(
				new Message(
					Loc::getMessage('SALE_YANDEX_TAXI_DELIVERY_FREE_CANCELLATION')
				)
			);
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function delete($requestId)
	{
		return new Result();
	}

	/**
	 * @inheritDoc
	 */
	public function hasCallbackTrackingSupport(): bool
	{
		return true;
	}
}
