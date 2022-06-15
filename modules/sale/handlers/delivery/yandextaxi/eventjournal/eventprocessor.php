<?php

namespace Sale\Handlers\Delivery\YandexTaxi\EventJournal;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Delivery\Requests\RequestTable;
use Bitrix\Sale\Repository\ShipmentRepository;
use Sale\Handlers\Delivery\YandexTaxi\Api\Api;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal\Event;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal\PriceChanged;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal\StatusChanged;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Claim;
use Sale\Handlers\Delivery\YandexTaxi\Api\StatusDictionary;
use Sale\Handlers\Delivery\YandexTaxi\Internals\ClaimsTable;
use Bitrix\Sale\Delivery\Services;
use Bitrix\Sale\Delivery\Requests;
use Bitrix\Sale\Delivery\Requests\Message;
use Bitrix\Sale\Internals\Analytics\Storage;
use Bitrix\Sale\Delivery\Internals\Analytics\Provider;

/**
 * Class EventProcessor
 * @package Sale\Handlers\Delivery\YandexTaxi\EventJournal
 * @internal
 */
final class EventProcessor
{
	private const DELIVERY_ANALYTICS_CODE = 'yandex_taxi';

	/** @var Api */
	protected $api;

	/** @var array */
	private $claims = [];

	/** @var Event[] */
	private $events = [];

	/**
	 * EventProcessor constructor.
	 * @param Api $api
	 */
	public function __construct(Api $api)
	{
		$this->api = $api;
	}

	/**
	 * @param int $serviceId
	 * @param array $events
	 */
	public function process(int $serviceId, array $events)
	{
		$this->prepareClaims($events);
		$this->prepareEvents($events);
		$this->findChanges();
		$this->applyChanges($serviceId);
	}

	/**
	 * @param Event[] $events
	 */
	private function prepareClaims(array $events)
	{
		$claimsIds = [];

		foreach ($events as $event)
		{
			$claimsIds[$event->getClaimId()] = true;
		}

		$claims = ClaimsTable::getList(
			[
				'filter' => ['=EXTERNAL_ID' => array_keys($claimsIds)],
			]
		)->fetchAll();

		$eventTypes = $this->getKnownEventTypes();
		foreach ($claims as $claim)
		{
			$this->claims[$claim['EXTERNAL_ID']] = [
				'CURRENT_ITEM' => $claim,
				'CHANGES' => array_combine($eventTypes, array_fill(0, count($eventTypes), null)),
			];
		}
	}

	/**
	 * @param Event[] $events
	 */
	private function prepareEvents(array $events)
	{
		foreach ($events as $event)
		{
			$claimId = $event->getClaimId();
			$eventCode = $event->getCode();

			if (!isset($this->events[$claimId]))
			{
				$this->events[$claimId] = [];
			}


			if (!isset($this->events[$claimId][$eventCode]))
			{
				$this->events[$claimId][$eventCode] = [];
			}
			$this->events[$claimId][$eventCode][] = $event;
		}
	}

	/**
	 * @return array
	 */
	private function getKnownEventTypes()
	{
		return [
			PriceChanged::EVENT_CODE,
			StatusChanged::EVENT_CODE,
		];
	}

	/**
	 * @return void
	 */
	private function findChanges()
	{
		$eventTypes = $this->getKnownEventTypes();
		foreach ($this->claims as $claimId => $claimItem)
		{
			$claim = $claimItem['CURRENT_ITEM'];

			if (!isset($this->events[$claimId]))
			{
				continue;
			}

			foreach ($eventTypes as $eventType)
			{
				if (!isset($this->events[$claimId][$eventType]))
				{
					continue;
				}

				/** @var Event[] $events */
				$events = $this->events[$claimId][$eventType];

				foreach ($events as $event)
				{
					$eventUpdatedAt = $this->readDate($event->getUpdatedTs());
					$currentUpdatedAt = $this->readDate($claim['EXTERNAL_UPDATED_TS']);

					if ($currentUpdatedAt >= $eventUpdatedAt)
					{
						continue;
					}

					/** @var Event|null $latestEvent */
					$latestEvent = $claimItem['CHANGES'][$eventType];
					if (is_null($latestEvent) ||
						$this->readDate($latestEvent->getUpdatedTs()) < $eventUpdatedAt
					)
					{
						$this->claims[$claimId]['CHANGES'][$eventType] = $event;
					}
				}
			}
		}
	}

	/**
	 * @param int $serviceId
	 */
	private function applyChanges(int $serviceId): void
	{
		foreach ($this->claims as $claimId => $claimItem)
		{
			$newUpdatedTs = null;
			$fields = [];
			/*** @var Event $event */
			foreach ($claimItem['CHANGES'] as $eventType => $event)
			{
				if (is_null($event))
				{
					continue;
				}

				$fields += $event->provideUpdateFields();

				if (is_null($newUpdatedTs)
					|| $this->readDate($event->getUpdatedTs()) > $this->readDate($newUpdatedTs))
				{
					$newUpdatedTs = $event->getUpdatedTs();
				}
			}

			if (!$fields)
			{
				continue;
			}

			$this->updateClaim(
				$serviceId,
				$claimItem['CURRENT_ITEM']['ID'],
				$newUpdatedTs,
				$fields
			);
		}
	}

	/**
	 * @param int $serviceId
	 * @param int $id
	 * @param string $newUpdatedTs
	 * @param array $fields
	 */
	private function updateClaim(int $serviceId, int $id, string $newUpdatedTs, array $fields)
	{
		$fields = array_merge(
			$fields,
			[
				'UPDATED_AT' => new DateTime(),
				'EXTERNAL_UPDATED_TS' => $newUpdatedTs,
			]
		);

		$updateResult = ClaimsTable::update($id, $fields);
		if ($updateResult->isSuccess())
		{
			$this->onClaimUpdated($serviceId, $id, $fields);
		}
	}

	/**
	 * @param int $serviceId
	 * @param int $id
	 * @param array $fields
	 */
	private function onClaimUpdated(int $serviceId, int $id, array $fields)
	{
		$claim = ClaimsTable::getById($id)->fetch();
		if (!$claim)
		{
			return;
		}

		$deliveryServiceIds = array_column(
			Services\Manager::getList([
				'select' => ['ID'],
				'filter' => ['PARENT_ID' => $serviceId]
			])->fetchAll(),
			'ID'
		);
		$deliveryServiceIds[] = $serviceId;

		$request = RequestTable::getList([
			'filter' => [
				'=DELIVERY_ID' => $deliveryServiceIds,
				'=EXTERNAL_ID' => $claim['EXTERNAL_ID'],
			],
		])->fetch();
		if (!$request)
		{
			return;
		}

		$shipment = ShipmentRepository::getInstance()->getById($claim['SHIPMENT_ID']);
		if (!$shipment)
		{
			return;
		}

		switch ($fields['EXTERNAL_STATUS'])
		{
			case StatusDictionary::READY_FOR_APPROVAL:
				$remoteClaim = $this->requestClaim($claim['EXTERNAL_ID']);
				if (
					$remoteClaim
					&& ($pricing = $remoteClaim->getPricing())
					&& ($offer = $pricing->getOffer())
				)
				{
					$price = $offer->getPrice();
					$currency = $pricing->getCurrency();
				}
				else
				{
					$price = null;
					$currency = null;
				}

				$result = $this->api->acceptClaim($claim['EXTERNAL_ID'], 1);
				$message = (new Message\Message())->setSubject(Loc::getMessage('SALE_YANDEX_TAXI_ACCEPTING_CLAIM'));
				$deleteRequest = false;

				if ($result->isSuccess())
				{
					if ($price && $currency)
					{
						$message
							->setBody(
								sprintf(
									'%s: %s',
									Loc::getMessage('SALE_YANDEX_TAXI_DELIVERY_CALCULATION_RECEIVED_SUCCESSFULLY'),
									Message\Message::MESSAGE_TEXT_MONEY_PLACEHOLDER
								)
							)
							->setCurrency($currency)
							->addMoneyValue($price);
					}
					else
					{
						$message->setBody(Loc::getMessage('SALE_YANDEX_TAXI_DELIVERY_CALCULATION_FAILED'));
						$message->setStatus(new Message\Status(
							Loc::getMessage('SALE_YANDEX_TAXI_ERROR_STATUS'),
							Message\Status::getErrorSemantic()
						));
						$deleteRequest = true;
					}
				}
				else
				{
					$message
						->setBody(Loc::getMessage('SALE_YANDEX_TAXI_DELIVERY_ACCEPT_CLAIM_ERROR'))
						->setStatus(new Message\Status(
							Loc::getMessage('SALE_YANDEX_TAXI_ERROR_STATUS'),
							Message\Status::getErrorSemantic()
						));
					$deleteRequest = true;
				}

				Requests\Manager::sendMessage(
					Requests\Manager::MESSAGE_MANAGER_ADDRESSEE,
					$message,
					$request['ID'],
					$shipment->getId()
				);
				if ($deleteRequest)
				{
					Requests\Manager::deleteDeliveryRequest($request['ID']);
				}
				break;
			case StatusDictionary::PERFORMER_FOUND:
				Requests\Manager::updateDeliveryRequest(
					$request['ID'],
					[
						'EXTERNAL_STATUS' => Loc::getMessage('SALE_YANDEX_TAXI_DELIVERY_IN_PROCESS'),
						'EXTERNAL_STATUS_SEMANTIC' => Requests\Manager::EXTERNAL_STATUS_SEMANTIC_PROCESS,
						'EXTERNAL_PROPERTIES' => $this->getPerformer($claim),
					]
				);
				break;
			case StatusDictionary::PICKUPED:
				Requests\Manager::sendMessage(
					Requests\Manager::MESSAGE_RECIPIENT_ADDRESSEE,
					(new Message\Message())
						->setBody(Loc::getMessage('SALE_YANDEX_TAXI_YOUR_ORDER_IS_ON_ITS_WAY'))
						->setType(Message\Message::TYPE_SHIPMENT_PICKUPED),
					$request['ID'],
					$shipment->getId()
				);
				break;
			case StatusDictionary::PERFORMER_NOT_FOUND:
			case StatusDictionary::FAILED:
			case StatusDictionary::ESTIMATING_FAILED:
				Requests\Manager::sendMessage(
					Requests\Manager::MESSAGE_MANAGER_ADDRESSEE,
					(new Message\Message())
						->setSubject(Loc::getMessage('SALE_YANDEX_TAXI_PERFORMER_NOT_FOUND'))
						->setStatus(
							new Message\Status(
								Loc::getMessage('SALE_YANDEX_TAXI_ERROR_STATUS'),
								Message\Status::getErrorSemantic()
							)
						),
					$request['ID'],
					$shipment->getId()
				);
				Requests\Manager::deleteDeliveryRequest($request['ID']);
				break;
			case StatusDictionary::CANCELLED_BY_TAXI:
				Requests\Manager::sendMessage(
					Requests\Manager::MESSAGE_MANAGER_ADDRESSEE,
					(new Message\Message())
						->setSubject(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLED_BY_PERFORMER'))
						->setStatus(
							new Message\Status(
								Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION'),
								Message\Status::getProcessSemantic()
							)
						),
					$request['ID'],
					$shipment->getId()
				);
				Requests\Manager::deleteDeliveryRequest($request['ID']);
				break;
			case StatusDictionary::RETURNED_FINISH:
				Requests\Manager::sendMessage(
					Requests\Manager::MESSAGE_MANAGER_ADDRESSEE,
					(new Message\Message())
						->setSubject(Loc::getMessage('SALE_YANDEX_TAXI_PERFORMER_RETURNED_CARGO'))
						->setStatus(
							new Message\Status(
								Loc::getMessage('SALE_YANDEX_TAXI_RETURN'),
								Message\Status::getProcessSemantic()
							)
						),
					$request['ID'],
					$shipment->getId()
				);
				Requests\Manager::deleteDeliveryRequest($request['ID']);
				break;
			case StatusDictionary::DELIVERED_FINISH:
				Requests\Manager::updateDeliveryRequest(
					$request['ID'],
					[
						'STATUS' => Requests\Manager::STATUS_PROCESSED,
						'EXTERNAL_STATUS' => Loc::getMessage('SALE_YANDEX_TAXI_DELIVERY_FINISHED'),
						'EXTERNAL_STATUS_SEMANTIC' => Requests\Manager::EXTERNAL_STATUS_SEMANTIC_SUCCESS,
					]
				);
				break;
		}

		/**
		 * Finalize
		 */
		if (
			!is_null($claim['EXTERNAL_RESOLUTION'])
			|| $claim['EXTERNAL_STATUS'] === StatusDictionary::PERFORMER_NOT_FOUND)
		{
			ClaimsTable::update($claim['ID'], ['FURTHER_CHANGES_EXPECTED' => 'N']);

			if (
				isset($claim['EXTERNAL_RESOLUTION'])
				&& $claim['EXTERNAL_RESOLUTION'] === ClaimsTable::EXTERNAL_STATUS_SUCCESS
			)
			{
				$this->saveOrderForAnalytics($claim);
				if ($shipment->setField('DEDUCTED', 'Y')->isSuccess())
				{
					$shipment->getOrder()->save();
				}
			}
		}
	}

	/**
	 * @param array $claim
	 */
	private function saveOrderForAnalytics(array $claim): void
	{
		$order = [
			'id' => $claim['EXTERNAL_ID'],
			'is_successful' => 'Y',
			'status' => $claim['EXTERNAL_STATUS'],
			'created_at' => $claim['CREATED_AT']->getTimestamp(),
		];

		if ($claim['EXTERNAL_FINAL_PRICE'] && $claim['EXTERNAL_CURRENCY'])
		{
			$order['amount'] = $claim['EXTERNAL_FINAL_PRICE'];
			$order['currency'] = $claim['EXTERNAL_CURRENCY'];
		}

		(new Storage(new Provider(self::DELIVERY_ANALYTICS_CODE, [$order])))->save();
	}

	/**
	 * @param array $claim
	 * @return array
	 */
	private function getPerformer(array $claim): array
	{
		$result = [];

		$remoteClaim = $this->requestClaim($claim['EXTERNAL_ID']);
		if ($remoteClaim)
		{
			$performerInfo = $remoteClaim->getPerformerInfo();
			if ($performerInfo)
			{
				$result[] = [
					'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_PERFORMER'),
					'VALUE' => $performerInfo->getCourierName(),
				];
				$result[] = [
					'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_CAR'),
					'VALUE' => sprintf(
						'%s %s',
						$performerInfo->getCarModel(),
						$performerInfo->getCarNumber()
					),
				];
			}
		}

		$getPhoneResult = $this->api->getPhone($claim['EXTERNAL_ID']);
		if ($getPhoneResult->isSuccess())
		{
			$result[] = [
				'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_DRIVER_PHONE'),
				'VALUE' => $getPhoneResult->getPhone(),
				'TAGS' => ['phone'],
			];
			$result[] = [
				'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_DRIVER_PHONE_EXT'),
				'VALUE' => $getPhoneResult->getExt(),
			];
		}

		return $result;
	}

	/**
	 * @param string $externalId
	 * @return Claim|null
	 */
	private function requestClaim(string $externalId): ?Claim
	{
		$getClaimResult = $this->api->getClaim($externalId);
		$remoteClaim = $getClaimResult->getClaim();

		if ($getClaimResult->isSuccess() && !is_null($remoteClaim))
		{
			return $remoteClaim;
		}

		return null;
	}

	/**
	 * @param string $dateTime
	 * @return bool|\DateTime
	 */
	private function readDate(string $dateTime)
	{
		return \DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $dateTime);
	}
}
