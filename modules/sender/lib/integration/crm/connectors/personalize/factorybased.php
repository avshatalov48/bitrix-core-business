<?php

namespace Bitrix\Sender\Integration\Crm\Connectors\Personalize;

use Bitrix\Crm\Field;
use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Service\Factory;
use Bitrix\Main\Loader;

class FactoryBased extends BasePersonalize
{
	protected static function getFactory(string $entityType): ?Factory
	{
		if (!Loader::includeModule('crm'))
		{
			return null;
		}

		$entityTypeId = \CCrmOwnerType::ResolveID($entityType);
		return Container::getInstance()->getFactory($entityTypeId);
	}

	public static function getEntityFields($entityType): array
	{
		$factory = static::getFactory($entityType);
		if (!$factory)
		{
			return [];
		}

		$entityFields = [];
		$fieldsCollection = $factory->getFieldsCollection();
		foreach ($fieldsCollection as $field)
		{
			$type = static::getFieldType($field->getType());
			if (
				!$type
				|| !$field->isDisplayed()
				|| $field->isHidden()
			)
			{
				continue;
			}
			$fieldName = $field->getName();

			$entityFields[$fieldName] = [
				'Name' => $field->getTitle(),
				'Type' => $type,
				'Filterable' => !$field->isUserField(),
				'Editable' => \CCrmFieldInfoAttr::isFieldHasAttribute($field->getSettings(), \CCrmFieldInfoAttr::ReadOnly),
				'Required' => $field->isRequired(),
				// 'personalizeCode' => $field->getName(),
			];
		}

		return $entityFields + static::getAssignedByFields();
	}

	protected static function getFieldType(string $type): ?string
	{
		$map = [
			Field::TYPE_STRING => Field::TYPE_STRING,
			Field::TYPE_BOOLEAN => Field::TYPE_BOOLEAN,
			Field::TYPE_CRM_STATUS => 'select',
			Field::TYPE_DATE => Field::TYPE_DATETIME,
			Field::TYPE_DATETIME => Field::TYPE_DATETIME,
			Field::TYPE_TEXT => Field::TYPE_TEXT,
			Field::TYPE_INTEGER => 'int',
		];

		return $map[$type] ?? null;
	}

	public static function getData(
		string $entityType,
		array $entityIds,
		array $usedFields = ['*'],
		string $sortBy = 'id',
		string $sortOrder = 'asc'
	): array
	{
		$hasIncorrectFields = false;

		if (empty($usedFields))
		{
			return [];
		}
		$factory = static::getFactory($entityType);
		if (!$factory)
		{
			return [];
		}

		$fields = array_values($usedFields);

		$oldIncorrectFields = [
			'ASSIGNED_BY_EMAIL' => 'ASSIGNED_BY.EMAIL',
			'ASSIGNED_BY_WORK_PHONE' => 'ASSIGNED_BY.WORK_PHONE',
			'ASSIGNED_BY_PERSONAL_MOBILE' => 'ASSIGNED_BY.PERSONAL_MOBILE',
		];
		foreach ($fields as &$field)
		{
			if (array_key_exists($field, $oldIncorrectFields))
			{
				$field = $oldIncorrectFields[$field];
				if (!$hasIncorrectFields)
				{
					$hasIncorrectFields = true;
				}
			}
		}

		$result = [];
		$items = $factory->getItems([
			'select' => array_merge(
				$fields,
				['UF_*', 'ASSIGNED_BY_ID']
			),
			'filter' => [
				'@ID' => $entityIds,
			],
		]);
		foreach ($items as $item)
		{
			$data = $item->getCompatibleData();

			if ($item->getAssignedById() > 0)
			{
				self::addAssignedByFieldsValue($item->getAssignedById(), $data);
			}
			static::processUserFieldValues($factory->getUserFields(), $data);
			$result[$data['ID']] = $data + static::getCommunicationFieldsValues($factory->getEntityTypeId(), $data['ID']);
			if ($hasIncorrectFields)
			{
				foreach ($oldIncorrectFields as $incorrectField => $correctField)
				{
					if (isset($data[$correctField]) && !empty($data[$correctField]))
					{
						$result[$data['ID']] += [
							$incorrectField => $data[$correctField]
						];
					}
				}
			}
		}

		return $result;	}
}
