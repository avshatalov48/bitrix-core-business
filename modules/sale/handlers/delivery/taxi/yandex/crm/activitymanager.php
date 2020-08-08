<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Crm;

use Bitrix\Crm\Activity\Provider\Delivery;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\Taxi\Status\Initial;

/**
 * Class ActivityManager
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Crm
 */
class ActivityManager
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
	 * @param Shipment $shipment
	 * @param int $responsibleUserId
	 * @param array $fields
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function createActivity(Shipment $shipment, int $responsibleUserId, array $fields)
	{
		$fields = [
			'TYPE_ID' => \CCrmActivityType::Provider,
			'ASSOCIATED_ENTITY_ID' => $shipment->getId(),
			'PROVIDER_ID' => 'CRM_DELIVERY',
			'PROVIDER_TYPE_ID' => 'DELIVERY',
			'SUBJECT' => Loc::getMessage('SALE_YANDEX_TAXI_ACTIVITY_NAME'),
			'IS_HANDLEABLE' => 'Y',
			'COMPLETED' => 'N',
			'STATUS' => \CCrmActivityStatus::Waiting,
			'RESPONSIBLE_ID' => $responsibleUserId,
			'PRIORITY' => \CCrmActivityPriority::Medium,
			'AUTHOR_ID' => $responsibleUserId,
			'BINDINGS' => $this->bindingsMaker->makeByShipment($shipment, 'OWNER'),
			'SETTINGS' => [
				'FIELDS' => $fields
			],
		];

		$activityId = \CCrmActivity::add($fields, false);

		if ($activityId)
		{
			AddEventToStatFile(
				'sale',
				'deliveryActivityCreation',
				$activityId,
				($shipment->getDelivery() ? $shipment->getDelivery()->getName() : ''),
				'delivery_service_name'
			);
		}
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
	 * @param array $request
	 */
	public function resetActivity(array $request)
	{
		$this->updateActivity(
			$request['SHIPMENT_ID'],
			[
				'STATUS' => (new Initial())->getCode(),
				'REQUEST_ID' => null,
				'REQUEST_CANCELLATION_AVAILABLE' => false,
				'PERFORMER_PHONE' => null,
				'PERFORMER_CAR' => null,
				'PERFORMER_NAME' => null,
				'TRACKING_LINK' => null,
			],
			$request['ID']
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
