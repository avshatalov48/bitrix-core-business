<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Installer;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sale\Delivery\ExtraServices\Checkbox;
use Bitrix\Sale\Delivery\ExtraServices\Enum;
use Bitrix\Sale\Delivery\ExtraServices\Table;
use Bitrix\Sale\Delivery\Restrictions\ByPublicMode;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Internals\OrderPropsGroupTable;
use Bitrix\Sale\Internals\OrderPropsRelationTable;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\PersonTypeTable;
use Bitrix\Sale\Registry;
use Sale\Handlers\Delivery\YandexTaxi\Api\Tariffs\Repository;
use Sale\Handlers\Delivery\YandexTaxi\Common\OrderEntitiesCodeDictionary;
use Sale\Handlers\Delivery\YandexTaxi\Common\TariffNameBuilder;

/**
 * Class Installer
 * @package Sale\Handlers\Delivery\YandexTaxi\Installer
 * @internal
 */
final class Installer
{
	/** @var Repository */
	private $tariffsRepository;

	/** @var TariffNameBuilder */
	private $tariffNameBuilder;

	/**
	 * Installer constructor.
	 * @param Repository $tariffsRepository
	 * @param TariffNameBuilder $tariffNameBuilder
	 */
	public function __construct(Repository $tariffsRepository, TariffNameBuilder $tariffNameBuilder)
	{
		$this->tariffsRepository = $tariffsRepository;
		$this->tariffNameBuilder = $tariffNameBuilder;
	}

	/**
	 * @param int $serviceId
	 * @return Result
	 */
	public function install(int $serviceId): Result
	{
		$result = new Result();

		$orderPropsResult = $this->installOrderProperties();
		if (!$orderPropsResult->isSuccess())
		{
			return $result->addErrors($orderPropsResult->getErrors());
		}
		$propertyIds = $orderPropsResult->getData()['PROPERTY_IDS'];

		$tariffs = $this->tariffsRepository->getTariffs();
		$profileSort = 100;
		foreach ($tariffs as $tariff)
		{
			$installProfileResult = $this->installProfile($serviceId, $tariff, $profileSort);
			if (!$installProfileResult->isSuccess())
			{
				return $result->addErrors($installProfileResult->getErrors());
			}
			$profileSort += 100;

			$profileId = $installProfileResult->getData()['ID'];

			$attachPropertiesResult = $this->attachOrderProperties($profileId, $propertyIds);
			if (!$attachPropertiesResult->isSuccess())
			{
				return $result->addErrors($attachPropertiesResult->getErrors());
			}

			$extraServicesResult = $this->installExtraServices($profileId, $tariff);
			if (!$extraServicesResult->isSuccess())
			{
				return $result->addErrors($extraServicesResult->getErrors());
			}
		}

		$this->installRestriction($serviceId);

		return $result;
	}

	/**
	 * @param int $serviceId
	 * @param array $tariff
	 * @param int $sort
	 * @return Result
	 */
	protected function installProfile(int $serviceId, array $tariff, int $sort = 100): Result
	{
		$result = new Result();

		$parentServiceFields = Manager::getById($serviceId);
		if (!$parentServiceFields)
		{
			return $result->addError(new Error('Parent service not found'));
		}

		$addResult = Manager::add(
			[
				'CODE' => sprintf(
					'YANDEX_TAXI_%s',
					mb_strtoupper($tariff['name'])
				),
				'NAME' => $this->tariffNameBuilder->getTariffName($tariff),
				'DESCRIPTION' => Loc::getMessage(
					sprintf(
						'SALE_YANDEX_TAXI_TARIFF_%s_DESCRIPTION',
						mb_strtoupper($tariff['name'])
					)
				),
				'LOGOTIP' => \CFile::SaveFile(
					\CFile::MakeFileArray(
						sprintf(
							'%s/bitrix/modules/sale/handlers/delivery/yandextaxi/logos/%s.png',
							Application::getDocumentRoot(),
							mb_strtolower($tariff['name'])
						)
					),
					'sale/delivery/logotip'
				),
				'PARENT_ID' => $serviceId,
				'CLASS_NAME' => '\Sale\Handlers\Delivery\YandextaxiProfile',
				'SORT' => $sort,
				'ACTIVE' => 'Y',
				'CONFIG' => [
					'MAIN' => [
						'PROFILE_TYPE' => $tariff['name'],
					]
				],
				'XML_ID' => Manager::generateXmlId(),
				'CURRENCY' => $parentServiceFields['CURRENCY'],
				'ALLOW_EDIT_SHIPMENT' => $parentServiceFields['ALLOW_EDIT_SHIPMENT'],
				'VAT_ID' => $parentServiceFields['VAT_ID'],
			]
		);

		if (!$addResult->isSuccess())
		{
			return $result->addErrors($addResult->getErrors());
		}

		return $result->setData(['ID' => $addResult->getId()]);
	}

	/**
	 * @return Result
	 */
	protected function installOrderProperties(): Result
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
						'CODE' => 'DELIVERY_SERVICE',
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
					'CODE' => 'DELIVERY_SERVICE',
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
					'ATTRIBUTE' => 'IS_ADDRESS_FROM',
					'CODE' => 'DELIVERY_SERVICE_ADDRESS_FROM',
					'NAME' => Loc::getMessage('SALE_YANDEX_TAXI_PROP_FROM_NAME'),
					'TYPE' => 'ADDRESS',
					'REQUIRED' => 'Y',
					'MULTIPLE' => 'N',
					'SETTINGS' => [],
				],
				[
					'ATTRIBUTE' => 'IS_ADDRESS_TO',
					'CODE' => 'DELIVERY_SERVICE_ADDRESS_TO',
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

				$filter = [
					'PERSON_TYPE_ID' => $payerTypeId,
					'PROPS_GROUP_ID' => $propertyGroupId,
					'TYPE' => $property['TYPE'],
					'ENTITY_TYPE' => Registry::ENTITY_SHIPMENT,
				];
				if (isset($property['ATTRIBUTE']))
				{
					$filter[$property['ATTRIBUTE']] = 'Y';
				}
				else
				{
					$filter['CODE'] = $property['CODE'];
				}

				$existingProperty = OrderPropsTable::getList(['filter' => $filter])->fetch();

				$propertyFields = [
					'NAME' => $property['NAME'],
					'ACTIVE' => 'Y',
					'USER_PROPS' => 'N',
					'IS_FILTERED' => 'N',
					'REQUIRED' => $property['REQUIRED'],
					'MULTIPLE' => $property['MULTIPLE'],
					'SETTINGS' => $property['SETTINGS'],
					'ENTITY_REGISTRY_TYPE' => Registry::REGISTRY_TYPE_ORDER,
					'DEFAULT_VALUE' => '',
					'DESCRIPTION' => '',
				];

				if (isset($property['ATTRIBUTE']))
				{
					$propertyFields[$property['ATTRIBUTE']] = 'Y';
				}

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
								'ENTITY_TYPE' => Registry::ENTITY_SHIPMENT,
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

		return $result->setData(
			[
				'PROPERTY_IDS' => $propertyIds
			]
		);
	}

	/**
	 * @param int $serviceId
	 * @param array $propertyIds
	 * @return Result
	 */
	protected function attachOrderProperties(int $serviceId, array $propertyIds): Result
	{
		$result = new Result();

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
	 * @param array $tariff
	 * @return Result
	 */
	protected function installExtraServices(int $serviceId, array $tariff): Result
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

		$listNullValue = 'null';
		foreach ($tariff['supported_requirements'] as $supportedRequirement)
		{
			if ($supportedRequirement['type'] === 'multi_select')
			{
				foreach ($supportedRequirement['options'] as $option)
				{
					$addResult = Table::add(
						[
							'ACTIVE' => 'Y',
							'RIGHTS' => 'YYY',
							'DELIVERY_ID' => $serviceId,
							'CODE' => $option['value'],
							'NAME' => Loc::getMessage(
								sprintf(
									'SALE_YANDEX_TAXI_EXTRA_SERVICE_%s',
									mb_strtoupper($option['value'])
								)
							),
							'CLASS_NAME' => '\\' . Checkbox::class,
							'INIT_VALUE' => 'N',
							'PARAMS' => ['PRICE' => 0.00],
						]
					);
					if (!$addResult->isSuccess())
					{
						return $result->addErrors($addResult->getErrors());
					}
				}
			}
			elseif ($supportedRequirement['type'] === 'select')
			{
				$params = [
					$listNullValue => [
						'TITLE' => Loc::getMessage('SALE_YANDEX_TAXI_EXTRA_SERVICE_LIST_NOT_SELECTED'),
						'PRICE' => 0.00,
						'CODE' => $listNullValue,
					]
				];

				foreach ($supportedRequirement['options'] as $option)
				{
					$value = (string)$option['value'];

					$titleLang = Loc::getMessage(
						sprintf(
							'SALE_YANDEX_TAXI_EXTRA_SERVICE_%s_OPTION_%s',
							mb_strtoupper($supportedRequirement['name']),
							mb_strtoupper($value)
						)
					);

					$params[$value] = [
						'TITLE' => $titleLang ?: $value,
						'PRICE' => 0.00,
						'CODE' => $value,
					];
				}

				$addResult = Table::add(
					[
						'ACTIVE' => 'Y',
						'RIGHTS' => 'YYY',
						'DELIVERY_ID' => $serviceId,
						'CODE' => $supportedRequirement['name'],
						'NAME' => Loc::getMessage(
							sprintf(
								'SALE_YANDEX_TAXI_EXTRA_SERVICE_%s',
								mb_strtoupper($supportedRequirement['name'])
							)
						),
						'CLASS_NAME' => '\\' . Enum::class,
						'INIT_VALUE' => $listNullValue,
						'PARAMS' => [
							'PRICES' => $params,
						],
					]
				);
				if (!$addResult->isSuccess())
				{
					return $result->addErrors($addResult->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param int $serviceId
	 */
	protected function installRestriction(int $serviceId): void
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
