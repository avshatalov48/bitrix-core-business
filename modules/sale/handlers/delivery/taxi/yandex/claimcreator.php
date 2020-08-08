<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\Api;

/**
 * Class ClaimCreator
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class ClaimCreator
{
	/** @var Api */
	protected $api;

	/** @var ClaimBuilder */
	protected $claimBuilder;

	/** @var StatusMapper */
	protected $statusMapper;

	/**
	 * ClaimCreator constructor.
	 * @param Api $api
	 * @param ClaimBuilder $claimBuilder
	 * @param StatusMapper $statusMapper
	 */
	public function __construct(Api $api, ClaimBuilder $claimBuilder, StatusMapper $statusMapper)
	{
		$this->api = $api;
		$this->claimBuilder = $claimBuilder;
		$this->statusMapper = $statusMapper;
	}

	/**
	 * @param Shipment $shipment
	 * @return CreateClaimResult
	 */
	public function createClaim(Shipment $shipment): CreateClaimResult
	{
		$result = new CreateClaimResult();

		$claimBuildingResult = $this->claimBuilder->build($shipment);

		if (!$claimBuildingResult->isSuccess())
		{
			return $result->addErrors($claimBuildingResult->getErrors());
		}

		$claim = $claimBuildingResult->getClaim();

		$createClaimResult = $this->api->createClaim($claim);

		if (!$createClaimResult->isSuccess())
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_CREATE_ERROR')));
		}

		$createdClaim = $createClaimResult->getClaim();
		if (is_null($createdClaim))
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_PERSIST_ERROR')));
		}

		$persistResult = ClaimsTable::add(
			[
				'SHIPMENT_ID' => $shipment->getId(),
				'CREATED_AT' => new DateTime(),
				'UPDATED_AT' => new DateTime(),
				'EXTERNAL_ID' => $createdClaim->getId(),
				'EXTERNAL_STATUS' => $createdClaim->getStatus(),
				'EXTERNAL_CREATED_TS' => $createdClaim->getCreatedTs(),
				'EXTERNAL_UPDATED_TS' => $createdClaim->getUpdatedTs(),
				'INITIAL_CLAIM' => Json::encode($createdClaim),
			]
		);
		if (!$persistResult->isSuccess())
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_ORDER_PERSIST_ERROR')));
		}

		\CAgent::AddAgent(
			'\\' . YandexTaxi::class . sprintf('::readEventsJournal(%s);', $shipment->getDeliveryId()),
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

		return $result
			->setStatus($this->statusMapper->getMappedStatus($createdClaim->getStatus()))
			->setRequestId($createdClaim->getId());
	}
}
