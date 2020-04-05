<?php

namespace Sale\Handlers\Delivery\Additional\RusPost\Reliability;

use Bitrix\Sale\Delivery\Requests;
use Sale\Handlers\Delivery\Additional;

/**
 * Class Requester
 * @package Sale\Handlers\Delivery\Additional\RusPost\Reliability
 * @internal
 */
class Requester
{
	private $deliveryRequestsHandler = null;

	/**
	 * Requester constructor.
	 * @param Additional\DeliveryRequests\RusPost\Handler $deliveryRequestsHandler
	 */
	public function __construct(Additional\DeliveryRequests\RusPost\Handler $deliveryRequestsHandler)
	{
		$this->deliveryRequestsHandler = $deliveryRequestsHandler;
	}

	/**
	 * @param ReliabilityCollection $collection
	 * @return bool|ReliabilityCollection
	 */
	public function request(ReliabilityCollection $collection)
	{
		return $this->obtainDataFromRequestResult(
			$this->deliveryRequestsHandler->send(
				'UNRELIABLE_RECIPIENT',
				$this->createRequestData($collection)
			),
			$collection
		);
	}

	private function obtainDataFromRequestResult(Requests\Result $result, ReliabilityCollection $collection)
	{
		if(!$result->isSuccess())
		{
			return false;
		}

		foreach ($result->getData() as $resultItem)
		{
			$reliability = Service::UNKNOWN;

			if (isset($resultItem['unreliability']))
			{
				if ($resultItem['unreliability'] === 'RELIABLE')
				{
					$reliability = Service::RELIABLE;
				}
				elseif ($resultItem['unreliability'] === 'FRAUD')
				{
					$reliability = Service::FRAUD;
				}
			}

			$hash = Service::createHash($resultItem['raw-full-name'], $resultItem['raw-address'], $resultItem['raw-telephone']);

			/** @var Reliability $askedItem */
			if($askedItem = $collection->getByPrimary($hash))
			{
				$askedItem->setReliability($reliability);
			}
		}

		return $collection;
	}

	/**
	 * @param ReliabilityCollection $collection
	 * @return array
	 */
	private function createRequestData(ReliabilityCollection $collection)
	{
		$result = [];

		/** @var Reliability $item */
		foreach ($collection as $item)
		{
			$result [] = [
				'raw-address' => $item->getAddress(),
				'raw-full-name' => $item->getFullName(),
				'raw-telephone' => $item->getPhone()
			];
		}

		return $result;
	}
}