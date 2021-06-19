<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm;

use Bitrix\Crm\Activity\Provider\Delivery;
use Bitrix\Sale\Delivery\Services\Taxi\StatusDictionary;
use Bitrix\Main\Loader;
use Bitrix\Voximplant\Security\Helper;

/**
 * Class ActivityManager
 * @package Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm
 * @internal
 */
final class ActivityManager
{
	/** @var BindingsMaker */
	protected $bindingsMaker;

	/**
	 * ActivityManager constructor.
	 * @param BindingsMaker $bindingsMaker
	 */
	public function __construct(BindingsMaker $bindingsMaker)
	{
		$this->bindingsMaker = $bindingsMaker;
	}

	/**
	 * @param int $shipmentId
	 * @param array $fields
	 * @param int|null $requestId
	 */
	public function updateActivity(int $shipmentId, array $fields, int $requestId = null)
	{
		$row = $this->getByShipmentId($shipmentId, $requestId);
		if (!$row)
		{
			return;
		}

		\CCrmActivity::update(
			$row['ID'],
			[
				'SETTINGS' => [
					'FIELDS' => array_merge(
						$row['SETTINGS']['FIELDS'],
						$fields
					)
				]
			],
			false
		);
	}

	/**
	 * @param int $shipmentId
	 * @param int $requestId
	 */
	public function resetActivity(int $shipmentId, int $requestId)
	{
		$this->updateActivity(
			$shipmentId,
			[
				'STATUS' => StatusDictionary::INITIAL,
				'CAN_USE_TELEPHONY' => (
					Loader::includeModule('voximplant')
					&& Helper::canCurrentUserPerformCalls()
				),
				'REQUEST_ID' => null,
				'REQUEST_CANCELLATION_AVAILABLE' => false,
				'PERFORMER_PHONE' => null,
				'PERFORMER_CAR' => null,
				'PERFORMER_NAME' => null,
				'TRACKING_LINK' => null,
			],
			$requestId
		);
	}

	/**
	 * @param int $shipmentId
	 * @param int|null $requestId
	 */
	public function completeActivity(int $shipmentId, int $requestId = null)
	{
		$row = $this->getByShipmentId($shipmentId, $requestId);
		if (!$row)
		{
			return;
		}

		\CCrmActivity::update(
			$row['ID'],
			[
				'COMPLETED' => 'Y',
				'STATUS' => \CCrmActivityStatus::AutoCompleted,
			],
			false
		);
	}

	/**
	 * @param int $shipmentId
	 * @param int|null $requestId
	 * @return array|null
	 */
	private function getByShipmentId(int $shipmentId, int $requestId = null)
	{
		$activity = \CCrmActivity::getlist(
			[],
			[
				'CHECK_PERMISSIONS' => 'N',
				'TYPE_ID' => \CCrmActivityType::Provider,
				'ASSOCIATED_ENTITY_ID' => $shipmentId,
				'PROVIDER_ID' => Delivery::getId(),
			]
		)->fetch();

		if (is_null($requestId))
		{
			return $activity;
		}

		if (isset($activity['SETTINGS']['FIELDS']['REQUEST_ID'])
			&& $requestId == (int)$activity['SETTINGS']['FIELDS']['REQUEST_ID'])
		{
			return $activity;
		}

		return null;
	}
}
