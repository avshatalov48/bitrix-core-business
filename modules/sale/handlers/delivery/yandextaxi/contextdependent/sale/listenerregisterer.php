<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Sale;

use Sale\Handlers\Delivery\YandexTaxi\ContextDependent\IListenerRegisterer;
use Sale\Handlers\Delivery\YandextaxiHandler;

/**
 * Class ListenerRegisterer
 * @package Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Sale
 * @internal
 */
final class ListenerRegisterer implements IListenerRegisterer
{
	/**
	 * @inheritDoc
	 */
	public function registerOnClaimCreated(YandextaxiHandler $deliveryService): IListenerRegisterer
	{
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function registerOnClaimCancelled(YandextaxiHandler $deliveryService): IListenerRegisterer
	{
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function registerOnClaimUpdated(YandextaxiHandler $deliveryService): IListenerRegisterer
	{
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function registerOnNeedContactTo(YandextaxiHandler $deliveryService): IListenerRegisterer
	{
		return $this;
	}
}
