<?php

namespace Bitrix\Iblock\Controller\Property\Action;

use Bitrix\Iblock\Model\PropertyFeature;
use Bitrix\Iblock\PropertyFeatureTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use CIBlockProperty;
use CIBlockRights;

/**
 * Save iblock property handler.
 */
final class SaveAction extends Action
{
	/**
	 * Handler.
	 *
	 * @param int $propertyId set `0` if need to create a property
	 * @param int $iblockId
	 * @param array $fields
	 *
	 * @return array|null in format `[ ENTITY_ID ]`. Returns `null` if has errors.
	 */
	public function run(int $propertyId, int $iblockId, array $fields): ?array
	{
		if (!$this->checkWritePermissions($iblockId))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('IBLOCK_CONTROLLER_PROPERTY_ACTION_SAVE_ERROR_ACCESS_DENIED'))
			);

			return null;
		}

		$fields = $this->cleanEntityFields($fields);
		if (empty($fields))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('IBLOCK_CONTROLLER_PROPERTY_ACTION_SAVE_ERROR_EMPTY_REQUEST'))
			);

			return null;
		}

		$oldFields = null;
		if ($propertyId > 0)
		{
			$oldFields = $this->getPropertyFields($propertyId, $iblockId);
			if (!$oldFields)
			{
				$this->errorCollection->setError(
					new Error(Loc::getMessage('IBLOCK_CONTROLLER_PROPERTY_ACTION_SAVE_ERROR_NOT_FOUND'))
				);

				return null;
			}
		}

		$fields['IBLOCK_ID'] = $iblockId;
		$fields = $this->processUserTypeSettingsFields($fields, $oldFields);
		$fields = $this->processFeatureFields($fields, $oldFields);
		$fields = $this->processEnumFields($fields, $oldFields);

		$entity = new CIBlockProperty();
		if ($propertyId > 0)
		{
			$result = $entity->Update($propertyId, $fields);
		}
		else
		{
			$result = $entity->Add($fields);
			if ($result !== false)
			{
				$propertyId = (int)$result;
			}
		}

		if (!$result)
		{
			if ($entity->LAST_ERROR)
			{
				$this->errorCollection->setError(
					new Error($entity->LAST_ERROR)
				);
			}

			return null;
		}

		return [
			'id' => $propertyId,
		];
	}

	/**
	 * Check rights.
	 *
	 * @param int $iblockId
	 *
	 * @return bool
	 */
	private function checkWritePermissions(int $iblockId): bool
	{
		return CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'iblock_edit');
	}

	/**
	 * Process features fields.
	 *
	 * @param array $fields
	 * @param array|null $oldFields
	 *
	 * @return array
	 */
	private function processFeatureFields(array $fields, ?array $oldFields): array
	{
		$features = $fields['FEATURES'] ?? null;
		if (!isset($features) || !is_array($features))
		{
			return $fields;
		}

		$result = [];

		$currentFeatures = [];
		if (isset($oldFields['FEATURES']))
		{
			foreach ($oldFields['FEATURES'] as $index => $isEnabled)
			{
				$currentFeatures[$index] = $isEnabled;
			}
		}

		foreach ($features as $index => $isEnabled)
		{
			if (isset($currentFeatures[$index]))
			{
				unset($currentFeatures[$index]);
			}

			$feature = PropertyFeature::parseIndex((string)$index);
			if (isset($feature))
			{
				$feature['IS_ENABLED'] = $isEnabled === 'Y' ? 'Y' : 'N';
				$result[] = $feature;
			}
		}

		foreach ($currentFeatures as $index => $isEnabled)
		{
			$feature = PropertyFeature::parseIndex($index);
			if (isset($feature))
			{
				$feature['IS_ENABLED'] = $isEnabled === 'Y' ? 'Y' : 'N';
				$result[] = $feature;
			}
		}

		$fields['FEATURES'] = $result;

		return $fields;
	}

	/**
	 * Process fields of user type settings.
	 *
	 * @param array $fields
	 * @param array|null $oldFields
	 *
	 * @return array
	 */
	private function processUserTypeSettingsFields(array $fields, ?array $oldFields): array
	{
		$fields = $this->parseUserType($fields);

		if (!isset($fields['PROPERTY_TYPE']) && isset($oldFields))
		{
			$fields['USER_TYPE'] = $oldFields['USER_TYPE'];
			$fields['PROPERTY_TYPE'] = $oldFields['PROPERTY_TYPE'];
		}

		if (empty($fields['USER_TYPE']))
		{
			$fields['USER_TYPE_SETTINGS'] = false;
		}
		// append other settings
		elseif (
			isset($fields['USER_TYPE_SETTINGS'], $oldFields['USER_TYPE_SETTINGS'])
			&& is_array($fields['USER_TYPE_SETTINGS'])
		)
		{
			$fields['USER_TYPE_SETTINGS'] += $oldFields['USER_TYPE_SETTINGS'];
		}

		return $fields;
	}

	/**
	 * Process enum fields.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	private function processEnumFields(array $fields): array
	{
		// set one DEF value
		if (
			$fields['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST
			&& isset($fields['VALUES'])
			&& is_array($fields['VALUES'])
		)
		{
			$existDef = false;
			foreach ($fields['VALUES'] as &$item)
			{
				if (isset($item['DEF']) && $item['DEF'] === 'Y')
				{
					if ($existDef)
					{
						$item['DEF'] = 'N';
					}
					else
					{
						$existDef = true;
					}
				}
			}
			unset($item);
		}

		return $fields;
	}

	/**
	 * Divides the composite PROPERTY_TYPE into parts: PROPERTY_TYPE and USER_TYPE, respectively.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	private function parseUserType(array $fields): array
	{
		if (isset($fields['PROPERTY_TYPE']))
		{
			$parts = explode(':', $fields['PROPERTY_TYPE']);
			if (count($parts) === 2)
			{
				$fields['PROPERTY_TYPE'] = $parts[0];
				$fields['USER_TYPE'] = $parts[1];
			}
			else
			{
				$fields['USER_TYPE'] = false; // `NULL` for old $DB
			}
		}

		return $fields;
	}

	/**
	 * Removes all invalid fields.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	private function cleanEntityFields(array $fields): array
	{
		$availableFields = array_fill_keys([
			'PROPERTY_TYPE',
			'ACTIVE',
			'SORT',
			'NAME',
			'CODE',
			'MULTIPLE',
			'IS_REQUIRED',
			'SEARCHABLE',
			'FILTERABLE',
			'WITH_DESCRIPTION',
			'MULTIPLE_CNT',
			'HINT',
			'SECTION_PROPERTY',
			'SMART_FILTER',
			'DISPLAY_TYPE',
			'DISPLAY_EXPANDED',
			'FILTER_HINT',
			'ROW_COUNT',
			'COL_COUNT',
			'FILE_TYPE',
			'LINK_IBLOCK_ID',
			'LIST_TYPE',
			'DEFAULT_VALUE',
			'XML_ID',
			'VALUES',
			'FEATURES',
			'USER_TYPE_SETTINGS',
		], true);

		return array_intersect_key($fields, $availableFields);
	}

	/**
	 * Get property fields.
	 *
	 * @param int $propertyId
	 * @param int $iblockId
	 *
	 * @return array|null
	 */
	private function getPropertyFields(int $propertyId, int $iblockId): ?array
	{
		$fields = PropertyTable::getRow([
			'filter' => [
				'=ID' => $propertyId,
				'=IBLOCK_ID' => $iblockId,
			],
		]);
		if (!$fields)
		{
			return null;
		}

		// arrayable user settings
		$fields['USER_TYPE_SETTINGS'] = $fields['USER_TYPE_SETTINGS_LIST'];
		unset($fields['USER_TYPE_SETTINGS_LIST']);

		// append section link
		$sectionProperty = SectionPropertyTable::getRow([
			'select' => [
				'SMART_FILTER',
				'DISPLAY_TYPE',
				'DISPLAY_EXPANDED',
				'FILTER_HINT',
			],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'=PROPERTY_ID' => $propertyId,
			],
		]);
		if (is_array($sectionProperty))
		{
			$fields += $sectionProperty;
		}

		// append features
		$fields['FEATURES'] = [];
		$rows = PropertyFeatureTable::getList([
			'select' => [
				'ID',
				'MODULE_ID',
				'FEATURE_ID',
				'IS_ENABLED',
			],
			'filter' => [
				'=PROPERTY_ID' => $propertyId,
			]
		]);
		foreach ($rows as $row)
		{
			$index = PropertyFeature::getIndex($row);
			$fields["FEATURES"][$index] = $row['IS_ENABLED'];
		}

		return $fields;
	}
}
