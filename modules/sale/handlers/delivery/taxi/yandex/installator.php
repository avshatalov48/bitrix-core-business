<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Sale\Delivery\ExtraServices\Table;
use Bitrix\Sale\Internals\OrderPropsGroupTable;
use Bitrix\Sale\Internals\OrderPropsRelationTable;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\PersonTypeTable;

/**
 * Class Installator
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class Installator
{
	/** @var EntityProvider */
	protected $entityProvider;

	/**
	 * Installator constructor.
	 * @param EntityProvider $entityProvider
	 */
	public function __construct(EntityProvider $entityProvider)
	{
		$this->entityProvider = $entityProvider;
	}

	/**
	 * @param int $serviceId
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function install(int $serviceId): Result
	{
		$result = new Result();

		$this->installClaimsDbTable();

		$orderPropsResult = $this->installOrderProperties($serviceId);
		if (!$orderPropsResult->isSuccess())
		{
			return $result->addErrors($orderPropsResult->getErrors());
		}

		$extraServicesResult = $this->installExtraServices($serviceId);
		if (!$extraServicesResult->isSuccess())
		{
			return $result->addErrors($extraServicesResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param int $serviceId
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function installOrderProperties(int $serviceId): Result
	{
		$result = new Result();

		$properties = [
			$this->entityProvider->getPropertyFrom(),
			$this->entityProvider->getPropertyTo(),
			$this->entityProvider->getCommentProperty(),
		];

		/**
		 * Install property groups
		 */
		$payerTypeGroupsMapping = [];
		$payerTypes = PersonTypeTable::getList(
			['filter' => ['ENTITY_REGISTRY_TYPE' => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER]]
		)->fetchAll();

		$propertyGroup = $this->entityProvider->getGroup();
		foreach ($payerTypes as $payerType)
		{
			$existingGroup = OrderPropsGroupTable::getList(
				[
					'filter' => [
						'PERSON_TYPE_ID' => $payerType['ID'],
						'CODE' => $propertyGroup->getCode(),
					]
				]
			)->fetch();

			if ($existingGroup)
			{
				$payerTypeGroupsMapping[$payerType['ID']] = $existingGroup['ID'];
				continue;
			}

			$persistResult = OrderPropsGroupTable::add(
				[
					'PERSON_TYPE_ID' => $payerType['ID'],
					'NAME' => $propertyGroup->getName(),
					'CODE' => $propertyGroup->getCode(),
				]
			);
			if (!$persistResult->isSuccess())
			{
				return $result->addError(new Error('Can not add property group'));
			}

			$payerTypeGroupsMapping[$payerType['ID']] = $persistResult->getId();
		}

		/**
		 * Install properties
		 */
		$propertyIds = [];
		foreach ($payerTypes as $payerType)
		{
			foreach ($properties as $property)
			{
				$propertyCode = $property->getCode();
				$propertyType = $property->getType();
				$payerTypeId = $payerType['ID'];

				if (!isset($payerTypeGroupsMapping[$payerTypeId]))
				{
					return $result->addError(new Error('Property group not found'));
				}

				$propertyGroupId = $payerTypeGroupsMapping[$payerTypeId];

				$existingProperty = OrderPropsTable::getList(
					[
						'filter' => [
							'PERSON_TYPE_ID' => $payerTypeId,
							'PROPS_GROUP_ID' => $propertyGroupId,
							'CODE' => $propertyCode,
							'TYPE' => $propertyType,
						]
					]
				)->fetch();

				$propertyFields = [
					'NAME' => $property->getName(),
					'ACTIVE' => 'Y',
					'USER_PROPS' => 'N',
					'IS_FILTERED' => 'N',
					'REQUIRED' => $property->isRequired() ? 'Y' : 'N',
					'MULTIPLE' => $property->isMultiple() ? 'Y' : 'N',
					'SETTINGS' => $property->getSettings(),
					'ENTITY_REGISTRY_TYPE' => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER,
				];

				if ($existingProperty)
				{
					$persistResult = OrderPropsTable::update(
						$existingProperty['ID'],
						$propertyFields
					);
					if (!$persistResult->isSuccess())
					{
						return $result->addError(new Error('Property can not be installed'));
					}

					$propertyIds[] = $existingProperty['ID'];
				}
				else
				{
					$persistResult = OrderPropsTable::add(
						array_merge(
							$propertyFields,
							[
								'PERSON_TYPE_ID' => $payerTypeId,
								'PROPS_GROUP_ID' => $propertyGroupId,
								'CODE' => $propertyCode,
								'TYPE' => $propertyType,


							]
						)
					);
					if (!$persistResult->isSuccess())
					{
						return $result->addError(new Error('Property can not be installed'));
					}

					$propertyIds[] = $persistResult->getId();
				}
			}
		}

		/**
		 * Attach properties to delivery services
		 */
		foreach ($propertyIds as $propertyId)
		{
			$fields = [
				'PROPERTY_ID' => $propertyId,
				'ENTITY_TYPE' => 'D',
				'ENTITY_ID' => $serviceId,
			];

			$existingRecord = OrderPropsRelationTable::getList(
				[
					'filter' => [
						'PROPERTY_ID' => $propertyId,
						'ENTITY_TYPE' => 'D',
						'ENTITY_ID' => $serviceId,
					]
				]
			)->fetch();

			if (!$existingRecord)
			{
				$relationAddResult = OrderPropsRelationTable::add($fields);
				if (!$relationAddResult->isSuccess())
				{
					$result->addErrors($relationAddResult->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param int $serviceId
	 * @return Result
	 * @throws \Exception
	 */
	protected function installExtraServices(int $serviceId): Result
	{
		$result = new Result();

		$extraServices = [
			$this->entityProvider->getVehicleTypeExtraService(),
			$this->entityProvider->getDoorDeliveryExtraService()
		];
		foreach ($extraServices as $extraService)
		{
			$persistResult = Table::add([
				'ACTIVE' => 'Y',
				'RIGHTS' => 'YYY',
				'DELIVERY_ID' => $serviceId,
				'CODE' => $extraService->getCode(),
				'NAME' => $extraService->getName(),
				'CLASS_NAME' => $extraService->getClassName(),
				'INIT_VALUE' => $extraService->getInitValue(),
				'PARAMS' => $extraService->getParams(),
			]);

			if (!$persistResult->isSuccess())
			{
				return $result->addErrors($persistResult->getErrors());
			}
		}

		return $result;
	}

	protected function installClaimsDbTable()
	{
		$GLOBALS['DB']->Query("
			create table if not exists b_sale_delivery_yandex_taxi_claims(
				ID INT NOT NULL AUTO_INCREMENT,
				CREATED_AT DATETIME NOT NULL,
				UPDATED_AT DATETIME NOT NULL,
				FURTHER_CHANGES_EXPECTED char(1) NOT NULL DEFAULT 'Y',
				SHIPMENT_ID INT NOT NULL,
				INITIAL_CLAIM TEXT NOT NULL,
				EXTERNAL_ID VARCHAR(255) NOT NULL,
				EXTERNAL_STATUS VARCHAR(255) NOT NULL,
				EXTERNAL_RESOLUTION VARCHAR(20) DEFAULT NULL,
				EXTERNAL_CREATED_TS VARCHAR(255) NOT NULL,
				EXTERNAL_UPDATED_TS VARCHAR(255) NOT NULL,
				EXTERNAL_CURRENCY char(3) DEFAULT NULL,
				EXTERNAL_FINAL_PRICE decimal(19,4) DEFAULT NULL,
				UNIQUE IX_UNIQUE_EXTERNAL_ID (EXTERNAL_ID),
				KEY IX_FURTHER_CHANGES_EXPECTED (FURTHER_CHANGES_EXPECTED),
				PRIMARY KEY (ID)
			);
		");
	}
}
