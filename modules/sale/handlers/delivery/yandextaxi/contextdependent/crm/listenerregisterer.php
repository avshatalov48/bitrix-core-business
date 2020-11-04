<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm;

use Bitrix\Main\EventManager;
use Bitrix\Sale\Delivery\Services\Taxi\Taxi;
use Sale\Handlers\Delivery\YandexTaxi\ClaimBuilder\ClaimBuilder;
use Sale\Handlers\Delivery\YandexTaxi\ContextDependent\IListenerRegisterer;
use Sale\Handlers\Delivery\YandexTaxi\EventJournal\EventProcessor;
use Sale\Handlers\Delivery\YandextaxiHandler;

/**
 * Class ListenerRegisterer
 * @package Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm
 * @internal
 */
final class ListenerRegisterer implements IListenerRegisterer
{
	/** @var ClaimCreatedListener */
	private $claimCreatedListener;

	/** @var ClaimCancelledListener */
	private $claimCancelledListener;

	/** @var ClaimUpdatesListener */
	private $claimUpdatesListener;

	/** @var ContactToRequiredListener */
	private $contactToRequiredListener;

	/**
	 * ListenerRegisterer constructor.
	 * @param ClaimCreatedListener $claimCreatedListener
	 * @param ClaimCancelledListener $claimCancelledListener
	 * @param ClaimUpdatesListener $claimUpdatesListener
	 * @param ContactToRequiredListener $needContactToListener
	 */
	public function __construct(
		ClaimCreatedListener $claimCreatedListener,
		ClaimCancelledListener $claimCancelledListener,
		ClaimUpdatesListener $claimUpdatesListener,
		ContactToRequiredListener $needContactToListener
	)
	{
		$this->claimCreatedListener = $claimCreatedListener;
		$this->claimCancelledListener = $claimCancelledListener;
		$this->claimUpdatesListener = $claimUpdatesListener;
		$this->contactToRequiredListener = $needContactToListener;
	}

	/**
	 * @inheritDoc
	 */
	public function registerOnClaimCreated(YandextaxiHandler $deliveryService): IListenerRegisterer
	{
		EventManager::getInstance()->addEventHandler(
			'sale',
			Taxi::TAXI_REQUEST_CREATED_EVENT_CODE,
			[$this->claimCreatedListener, 'listen']
		);
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function registerOnClaimCancelled(YandextaxiHandler $deliveryService): IListenerRegisterer
	{
		EventManager::getInstance()->addEventHandler(
			'sale',
			Taxi::TAXI_REQUEST_CANCELLED_EVENT_CODE,
			[$this->claimCancelledListener, 'listen']
		);
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function registerOnClaimUpdated(YandextaxiHandler $deliveryService): IListenerRegisterer
	{
		EventManager::getInstance()->addEventHandler(
			'sale',
			EventProcessor::CLAIM_UPDATED_EVENT_CODE,
			[$this->claimUpdatesListener->setDeliveryService($deliveryService), 'listen']
		);
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function registerOnNeedContactTo(YandextaxiHandler $deliveryService): IListenerRegisterer
	{
		EventManager::getInstance()->addEventHandler(
			'sale',
			ClaimBuilder::NEED_CONTACT_TO_EVENT_CODE,
			[$this->contactToRequiredListener, 'listen']
		);
		return $this;
	}
}
