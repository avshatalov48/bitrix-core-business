<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Sale;

class PaymentMarker
{
	private Service $service;
	private Sale\Payment $payment;
	private Sale\Order $order;

	/**
	 * @param Service $service
	 * @param Sale\Payment $payment
	 */
	public function __construct(Service $service, Sale\Payment $payment)
	{
		$this->service = $service;
		$this->payment = $payment;

		$this->order = $payment->getOrder();
	}

	/**
	 * Marks payment using errors from \Bitrix\Sale\PaySystem\ServiceResult
	 *
	 * @param ServiceResult $result
	 * @return $this
	 */
	public function mark(ServiceResult $result): PaymentMarker
	{
		if ($result->isSuccess())
		{
			return $this;
		}

		$markerResult = new Sale\Result();
		$markerResult->addWarnings($result->getErrors());

		$this->addMarker($markerResult);

		$this->payment->setField('MARKED', 'Y');

		return $this;
	}

	/**
	 * Saves order
	 *
	 * @return Sale\Result
	 */
	public function save(): Sale\Result
	{
		return $this->order->save();
	}

	private function addMarker(Sale\Result $markerResult): void
	{
		/** @var Sale\EntityMarker $markerClassName */
		$markerClassName = $this->getEntityMarkerClassName();
		$markerClassName::addMarker($this->order, $this->payment, $markerResult);
	}

	private function getEntityMarkerClassName()
	{
		$registry = Sale\Registry::getInstance($this->service->getField('ENTITY_REGISTRY_TYPE'));
		return $registry->getEntityMarkerClassName();
	}
}
