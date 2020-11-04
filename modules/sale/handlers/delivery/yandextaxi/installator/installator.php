<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Installator;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sale\Delivery\ExtraServices\Checkbox;
use Bitrix\Sale\Delivery\ExtraServices\Enum;
use Bitrix\Sale\Delivery\ExtraServices\Table;
use Bitrix\Sale\Delivery\Restrictions\ByPublicMode;
use Bitrix\Sale\Delivery\Services\OrderPropsDictionary;
use Bitrix\Sale\Internals\OrderPropsGroupTable;
use Bitrix\Sale\Internals\OrderPropsRelationTable;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\PersonTypeTable;
use Bitrix\Sale\Registry;
use Sale\Handlers\Delivery\YandexTaxi\Common\OrderEntitiesCodeDictionary;

/**
 * Class Installator
 * @package Sale\Handlers\Delivery\YandexTaxi\Installator
 * @internal
 */
final class Installator
{
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

		$this->installRestriction($serviceId);

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

		/**
		 * Install property groups
		 */
		$payerTypeGroupsMapping = [];
		$payerTypes = PersonTypeTable::getList(
			['filter' => ['ENTITY_REGISTRY_TYPE' => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER]]
		)->fetchAll();

		foreach ($payerTypes as $payerType)
		{
			$existingGroup = OrderPropsGroupTable::getList(
				[
					'filter' => [
						'PERSON_TYPE_ID' => $payerType['ID'],
						'CODE' => OrderPropsDictionary::PROPERTY_GROUP_CODE,
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
					'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_DELIVERY_PROP_GROUP_NAME'),
					'CODE' => OrderPropsDictionary::PROPERTY_GROUP_CODE,
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
			$properties = [
				[
					'CODE' => OrderPropsDictionary::ADDRESS_FROM_PROPERTY_CODE,
					'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_PROP_FROM_NAME'),
					'TYPE' => 'ADDRESS',
					'REQUIRED' => 'Y',
					'MULTIPLE' => 'N',
					'SETTINGS' => [],
				],
				[
					'CODE' => OrderPropsDictionary::ADDRESS_TO_PROPERTY_CODE,
					'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_PROP_TO_NAME'),
					'TYPE' => 'ADDRESS',
					'REQUIRED' => 'Y',
					'MULTIPLE' => 'N',
					'SETTINGS' => [],
				],
				[
					'CODE' => OrderEntitiesCodeDictionary::COMMENT_FOR_DRIVER_PROPERTY_CODE,
					'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_COMMENT_FOR_DRIVER'),
					'TYPE' => 'STRING',
					'REQUIRED' => 'N',
					'MULTIPLE' => 'N',
					'SETTINGS' => ['MULTILINE' => 'Y'],
				],
			];

			foreach ($properties as $property)
			{
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
							'CODE' => $property['CODE'],
							'TYPE' => $property['TYPE'],
						]
					]
				)->fetch();

				$propertyFields = [
					'NAME' => $property['NAME'],
					'ACTIVE' => 'Y',
					'USER_PROPS' => 'N',
					'IS_FILTERED' => 'N',
					'REQUIRED' => $property['REQUIRED'],
					'MULTIPLE' => $property['MULTIPLE'],
					'SETTINGS' => $property['SETTINGS'],
					'ENTITY_REGISTRY_TYPE' => Registry::REGISTRY_TYPE_ORDER,
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
								'CODE' => $property['CODE'],
								'TYPE' => $property['TYPE'],
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

		/**
		 * Door Delivery
		 */
		$addResult = Table::add(
			[
				'ACTIVE' => 'Y',
				'RIGHTS' => 'YYY',
				'DELIVERY_ID' => $serviceId,
				'CODE' => OrderEntitiesCodeDictionary::DOOR_DELIVERY_EXTRA_SERVICE_CODE,
				'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_TO_DOOR_DELIVERY'),
				'CLASS_NAME' => '\\' . Checkbox::class,
				'INIT_VALUE' => 'Y',
				'PARAMS' => ['PRICE' => 0.00],
			]
		);
		if (!$addResult->isSuccess())
		{
			return $result->addErrors($addResult->getErrors());
		}

		/**
		 * Vehicle Type
		 */
		$expressValue = 'express';
		$addResult = Table::add(
			[
				'ACTIVE' => 'Y',
				'RIGHTS' => 'YYY',
				'DELIVERY_ID' => $serviceId,
				'CODE' => OrderEntitiesCodeDictionary::VEHICLE_TYPE_EXTRA_SERVICE_CODE,
				'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_VEHICLE_TYPE'),
				'CLASS_NAME' => '\\' . Enum::class,
				'INIT_VALUE' => $expressValue,
				'PARAMS' => [
					'PRICES' => [
						'express' => [
							'TITLE' => Loc::getMessage('SALE_YANDEX_TAXI_VEHICLE_TYPE_CAR'),
							'PRICE' => 0.00,
							'CODE' => $expressValue,
						],
					],
				],
			]
		);
		if (!$addResult->isSuccess())
		{
			return $result->addErrors($addResult->getErrors());
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

	protected function installRestriction(int $serviceId)
	{
		ServiceRestrictionTable::add(
			[
				'SORT' => 100,
				'SERVICE_ID' => $serviceId,
				'PARAMS' => ['PUBLIC_SHOW' => 'N'],
				'SERVICE_TYPE' => '0',
				'CLASS_NAME' => '\\' . ByPublicMode::class
			]
		);
	}
}
