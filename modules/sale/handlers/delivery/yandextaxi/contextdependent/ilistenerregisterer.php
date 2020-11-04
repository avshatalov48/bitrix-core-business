<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ContextDependent;

use Sale\Handlers\Delivery\YandextaxiHandler;

/**
 * Interface IListenerRegisterer
 * @package Sale\Handlers\Delivery\YandexTaxi\ContextDependent
 * @internal
 */
interface IListenerRegisterer
{
	/**
	 * @param YandextaxiHandler $deliveryService
	 * @return IListenerRegisterer
	 */
	public function registerOnClaimCreated(YandextaxiHandler $deliveryService): IListenerRegisterer;

	/**
	 * @param YandextaxiHandler $deliveryService
	 * @return IListenerRegisterer
	 */
	public function registerOnClaimCancelled(YandextaxiHandler $deliveryService): IListenerRegisterer;

	/**
	 * @param YandextaxiHandler $deliveryService
	 * @return IListenerRegisterer
	 */
	public function registerOnClaimUpdated(YandextaxiHandler $deliveryService): IListenerRegisterer;

	/**
	 * @param YandextaxiHandler $deliveryService
	 * @return IListenerRegisterer
	 */
	public function registerOnNeedContactTo(YandextaxiHandler $deliveryService): IListenerRegisterer;
}
