<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\Requests;
use Bitrix\Sale\Repository\ShipmentRepository;

/**
 * Class DeliveryRequest
 * @package Bitrix\Sale\Controller
 */
class DeliveryRequest extends \Bitrix\Main\Engine\Controller
{
	/**
	 * @param array $shipmentIds
	 * @param array $additional
	 * @param int|null $deliveryId
	 * @return Requests\Result[]|null
	 */
	public function createAction(array $shipmentIds, array $additional = [], int $deliveryId = null)
	{
		if (!$this->checkPermission('U'))
		{
			return null;
		}

		if (is_null($deliveryId))
		{
			if ($shipmentIds && $shipmentIds[0])
			{
				$shipment = ShipmentRepository::getInstance()->getById((int)$shipmentIds[0]);
				if ($shipment && $shipment->getDelivery())
				{
					$deliveryId = $shipment->getDelivery()->getId();
				}
			}
		}

		/** @var Requests\Result $result */
		$result = Requests\Manager::createDeliveryRequest($deliveryId, $shipmentIds, $additional);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return [
			'result' => true,
			'message' => $this->getMessageFromResult($result)
		];
	}

	/**
	 * @param int $requestId
	 * @param string $actionType
	 * @param array $additional
	 * @return array|null
	 */
	public function executeAction(int $requestId, string $actionType, array $additional = [])
	{
		if (!$this->checkPermission('U'))
		{
			return null;
		}

		/** @var Requests\Result $result */
		$result = Requests\Manager::executeDeliveryRequestAction($requestId, $actionType, $additional);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return [
			'result' => true,
			'message' => $this->getMessageFromResult($result)
		];
	}

	/**
	 * @param int $requestId
	 * @return array|null
	 */
	public function deleteAction(int $requestId)
	{
		if (!$this->checkPermission('U'))
		{
			return null;
		}

		/** @var Requests\Result $result */
		$result = Requests\Manager::deleteDeliveryRequest($requestId);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return [
			'result' => true,
			'message' => $this->getMessageFromResult($result)
		];
	}

	/**
	 * @param Requests\Result $result
	 * @return string
	 */
	private function getMessageFromResult(Requests\Result $result): string
	{
		return implode(
			'; ',
			array_map(
				function ($message)
				{
					return $message->getMessage();
				},
				$result->getMessages()
			)
		);
	}

	/**
	 * @param string $permissionType
	 * @return bool
	 */
	private function checkPermission(string $permissionType): bool
	{
		$result =  $GLOBALS['APPLICATION']->GetGroupRight('sale') >= $permissionType;

		if (!$result)
		{
			$this->addError(new Error('Access denied'));
		}

		return $result;
	}
}
