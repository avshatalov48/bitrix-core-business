<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Crm;

use Bitrix\Main\SystemException;
use Sale\Handlers\Delivery\Taxi\Yandex\ClaimBuilder;
use Sale\Handlers\Delivery\Taxi\Yandex\ContextBase;
use Sale\Handlers\Delivery\Taxi\Yandex\ContextContract;
use Bitrix\Main\EventManager;
use Sale\Handlers\Delivery\Taxi\Yandex\EventJournal\Processor;
use Sale\Handlers\Delivery\Taxi\Yandex\YandexTaxi;
use Bitrix\Sale\Delivery\Services\Base;

/**
 * Class Context
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Crm
 */
class Context extends ContextBase implements ContextContract
{
	/** @var NewOrderListener */
	private $newOrderListener;

	/** @var ClaimCreatedListener */
	private $claimCreatedListener;

	/** @var ClaimCancelledListener */
	private $claimCancelledListener;

	/** @var ClaimUpdatesListener */
	private $claimUpdatesListener;

	/** @var NeedContactToListener */
	private $needContactToListener;

	/**
	 * Context constructor.
	 * @param NewOrderListener $newOrderListener
	 * @param ClaimCreatedListener $claimCreatedListener
	 * @param ClaimCancelledListener $claimCancelledListener
	 * @param ClaimUpdatesListener $claimUpdatesListener
	 * @param NeedContactToListener $needContactToListener
	 */
	public function __construct(
		NewOrderListener $newOrderListener,
		ClaimCreatedListener $claimCreatedListener,
		ClaimCancelledListener $claimCancelledListener,
		ClaimUpdatesListener $claimUpdatesListener,
		NeedContactToListener $needContactToListener
	)
	{
		$this->newOrderListener = $newOrderListener;
		$this->claimCreatedListener = $claimCreatedListener;
		$this->claimCancelledListener = $claimCancelledListener;
		$this->claimUpdatesListener = $claimUpdatesListener;
		$this->needContactToListener = $needContactToListener;
	}

	/**
	 * @inheritDoc
	 */
	public function subscribeToEvents()
	{
		if (!$this->deliveryService)
		{
			throw new SystemException('Delivery service is not specified');
		}

		$eventManager = EventManager::getInstance();

		$eventManager->addEventHandler(
			'sale',
			YandexTaxi::NEW_ORDER_EVENT_CODE,
			[$this->newOrderListener, 'listen']
		);

		$eventManager->addEventHandler(
			'sale',
			YandexTaxi::CLAIM_CREATED_EVENT_CODE,
			[$this->claimCreatedListener, 'listen']
		);

		$eventManager->addEventHandler(
			'sale',
			YandexTaxi::CLAIM_CANCELLED_EVENT_CODE,
			[$this->claimCancelledListener, 'listen']
		);

		$eventManager->addEventHandler(
			'sale',
			Processor::CLAIM_UPDATED_EVENT_CODE,
			[$this->claimUpdatesListener->setDeliveryService($this->deliveryService), 'listen']
		);

		$eventManager->addEventHandler(
			'sale',
			ClaimBuilder::NEED_CONTACT_TO_EVENT_CODE,
			[$this->needContactToListener, 'listen']
		);
	}
}
