<?php

namespace Bitrix\Iblock\Controller\Property\Directory\Action;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Random;
use CFile;
use CIBlockProperty;
use CIBlockPropertyDirectory;
use CIBlockRights;
use CUserTypeEntity;
use Throwable;

/**
 * Action to save the values of properties with the user type `Directory`.
 */
final class SaveSettingsAction extends Action
{
	/**
	 * Handler.
	 *
	 * @param int $propertyId
	 * @param array $fields
	 *
	 * @return bool
	 */
	public function run(int $propertyId, array $fields): bool
	{
		Loader::requireModule('highloadblock');

		if (!$this->checkWritePermissions($propertyId))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('IBLOCK_CONTROLLER_PROPERTY_DIRECTION_ACTION_SAVE_ERROR_ACCESS_DENIED'))
			);

			return false;
		}

		$fields = $this->cleanEntityFields($fields);
		if (empty($fields))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('IBLOCK_CONTROLLER_PROPERTY_DIRECTION_ACTION_SAVE_ERROR_BAD_REQUEST'))
			);

			return false;
		}

		$name = isset($fields['DIRECTORY_NAME']) && is_scalar($fields['DIRECTORY_NAME']) ? (string)$fields['DIRECTORY_NAME'] : null;
		$tableName = isset($fields['DIRECTORY_TABLE_NAME']) && is_scalar($fields['DIRECTORY_TABLE_NAME']) ? (string)$fields['DIRECTORY_TABLE_NAME'] : null;
		if (!$tableName && !$name)
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('IBLOCK_CONTROLLER_PROPERTY_DIRECTION_ACTION_SAVE_ERROR_BAD_REQUEST'))
			);

			return false;
		}

		$db = Application::getConnection();
		try
		{
			$db->startTransaction();

			$result = $this->save($propertyId, $name, $tableName, $fields);
			if ($result->isSuccess())
			{
				$db->commitTransaction();
			}
			else
			{
				$db->rollbackTransaction();
			}
		}
		catch (Throwable $e)
		{
			$db->rollbackTransaction();

			throw $e;
		}

		foreach ($result->getErrors() as $error)
		{
			$this->errorCollection->setError($error);
		}

		return $result->isSuccess();
	}

	private function save(int $propertyId, ?string $name, ?string $tableName, array $fields): Result
	{
		$result = new Result();

		$isNewHighload = !empty($name);
		if ($isNewHighload)
		{
			$result = $this->createHighload($fields);
			if (!$result->isSuccess() || count($this->errorCollection) > 0)
			{
				return $result;
			}

			$highloadBlock = HighloadBlockTable::getById($result->getId())->fetch();
		}
		else
		{
			$highloadBlock = HighloadBlockTable::getRow([
				'filter' => [
					'=TABLE_NAME' => $tableName,
				],
			]);
		}

		if (!$highloadBlock)
		{
			$result->addError(
				new Error(Loc::getMessage('IBLOCK_CONTROLLER_PROPERTY_DIRECTION_ACTION_SAVE_ERROR_BAD_REQUEST'))
			);

			return $result;
		}

		/**
		 * @var Entity $entity
		 */
		$entity = HighloadBlockTable::compileEntity($highloadBlock);
		$result = $this->saveHighloadValues($entity, $fields, $isNewHighload);

		if ($result->isSuccess())
		{
			$result = $this->updatePropertyTable($propertyId, $highloadBlock['TABLE_NAME'], $isNewHighload);
		}

		return $result;
	}

	/**
	 * Check rights.
	 *
	 * @param int $propertyId
	 *
	 * @return bool
	 */
	private function checkWritePermissions(int $propertyId): bool
	{
		$property = CIBlockProperty::GetByID($propertyId)->Fetch();
		if ($property)
		{
			$iblockId = (int)$property['IBLOCK_ID'];

			return CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'iblock_edit');
		}

		return false;
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
			'DIRECTORY_NAME',
			'DIRECTORY_TABLE_NAME',
			'DIRECTORY_ITEMS',
		], true);

		return array_intersect_key($fields, $availableFields);
	}

	/**
	 * Create highload.
	 *
	 * @param array $fields
	 *
	 * @return AddResult
	 */
	private function createHighload(array $fields): AddResult
	{
		$result = new AddResult();

		$title = trim($fields['DIRECTORY_NAME']);
		if (empty($title))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage("IBLOCK_CONTROLLER_PROPERTY_DIRECTION_ACTION_SAVE_HIGHLOAD_ERROR_NAME_IS_ABSENT"))
			);

			return $result;
		}

		$name = mb_strtoupper(mb_substr($title, 0, 1)).mb_substr($title, 1);
		if (!preg_match('/^[A-Z][A-Za-z0-9]*$/', $name))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage("IBLOCK_CONTROLLER_PROPERTY_DIRECTION_ACTION_SAVE_HIGHLOAD_ERROR_NAME_IS_INVALID"))
			);

			return $result;
		}

		$tableName = CIBlockPropertyDirectory::createHighloadTableName($title);
		if ($tableName === false)
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage("IBLOCK_CONTROLLER_PROPERTY_DIRECTION_ACTION_SAVE_HIGHLOAD_ERROR_NAME_IS_ABSENT"))
			);

			return $result;
		}

		$data = array(
			'NAME' => $name,
			'TABLE_NAME' => $tableName,
		);

		$result = HighloadBlockTable::add($data);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$highloadBlockId = $result->getId();
		if (is_array($fields['DIRECTORY_ITEMS']))
		{
			$fieldsNames = reset($fields['DIRECTORY_ITEMS']);
			$fieldsNames['UF_DEF'] = '';
			$fieldsNames['UF_FILE'] = '';

			$sort = 100;
			$userTypeEntity = new CUserTypeEntity();
			$userFieldEntityId = 'HLBLOCK_' . $highloadBlockId;

			foreach($fieldsNames as $fieldName => $fieldValue)
			{
				if ('UF_DELETE' === $fieldName)
				{
					continue;
				}

				$fieldMandatory = 'N';
				switch($fieldName)
				{
					case 'UF_NAME':
					case 'UF_XML_ID':
						$fieldType = 'string';
						$fieldMandatory = 'Y';
						break;

					case 'UF_LINK':
					case 'UF_DESCRIPTION':
					case 'UF_FULL_DESCRIPTION':
						$fieldType = 'string';
						break;

					case 'UF_SORT':
						$fieldType = 'integer';
						break;

					case 'UF_FILE':
						$fieldType = 'file';
						break;

					case 'UF_DEF':
						$fieldType = 'boolean';
						break;

					default:
						$fieldType = 'string';
				}

				$userFieldValues = [
					'ENTITY_ID' => $userFieldEntityId,
					'FIELD_NAME' => $fieldName,
					'USER_TYPE_ID' => $fieldType,
					'XML_ID' => '',
					'SORT' => $sort,
					'MULTIPLE' => 'N',
					'MANDATORY' => $fieldMandatory,
					'SHOW_FILTER' => 'N',
					'SHOW_IN_LIST' => 'Y',
					'EDIT_IN_LIST' => 'Y',
					'IS_SEARCHABLE' => 'N',
					'SETTINGS' => [],
				];

				$userTypeEntity->Add($userFieldValues);
				$sort += 100;
			}
		}

		return $result;
	}

	/**
	 * Save highload items values.
	 *
	 * @param Entity $entity
	 * @param array $fields
	 * @param bool $isNewHighload
	 *
	 * @return Result
	 */
	private function saveHighloadValues(Entity $entity, array $fields, bool $isNewHighload): Result
	{
		$result = new Result();

		$directoryValues = $fields['DIRECTORY_ITEMS'] ?? null;
		if (!is_array($directoryValues))
		{
			return $result;
		}

		$entityDataClass = $entity->getDataClass();
		$fieldsList = $entityDataClass::getMap();
		if (count($fieldsList) === 1 && isset($fieldsList['ID']))
		{
			$fieldsList = $entity->getFields();
		}

		$images = $this->getRequestImages();

		foreach($directoryValues as $itemKey => $item)
		{
			if (!is_array($item))
			{
				continue;
			}

			$itemId = mb_substr($itemKey, 0, 1) === 'n' ? 0 : (int)$itemKey;
			if (isset($item["UF_DELETE"]) && $item["UF_DELETE"] === 'Y')
			{
				if ($itemId > 0)
				{
					$entityDataClass::delete($itemId);
				}
				continue;
			}
			elseif (!isset($item['UF_NAME']) || empty(trim($item['UF_NAME'])))
			{
				continue;
			}

			if (isset($images[$itemKey]) && is_array($images[$itemKey]))
			{
				$item['UF_FILE'] = $images[$itemKey];
			}
			elseif (isset($item['UF_FILE']) && $item['UF_FILE'] === 'null')
			{
				$item['UF_FILE'] = null;
			}
			else
			{
				// to prevent deletion
				unset($item['UF_FILE']);
			}

			if (empty($item["UF_XML_ID"]))
			{
				$item['UF_XML_ID'] = Random::getString(8, true);
			}

			if (isset($item['UF_DEF']))
			{
				$value = (string)$item['UF_DEF'];
				$item['UF_DEF'] = $value === '1' || $value === 'Y' ? 1 : 0;
			}

			// clear fields list
			$item = array_filter(
				$item,
				static fn($name) => isset($fieldsList[$name]),
				ARRAY_FILTER_USE_KEY
			);

			if ($isNewHighload)
			{
				$entityDataClass::add($item);
			}
			else
			{
				if ($itemId > 0)
				{
					$existRow = $entityDataClass::getRowById($itemId);
					if ($existRow)
					{
						$result->addErrors(
							$entityDataClass::update($itemId, $item)->getErrors()
						);
					}
				}
				else
				{
					$result->addErrors(
						$entityDataClass::add($item)->getErrors()
					);
				}
			}
		}

		return $result;
	}

	/**
	 * Files of HL items from the current request.
	 *
	 * @return array
	 */
	private function getRequestImages(): array
	{
		$result = [];

		$files = Context::getCurrent()->getRequest()->getFileList();
		$requestFiles = $files['fields'] ?? null;
		if (is_array($requestFiles))
		{
			CFile::ConvertFilesToPost($requestFiles, $result);

			// remove UF_FILE key
			$result = $result['DIRECTORY_ITEMS'] ?? [];
			foreach ($result as $rowId => $data)
			{
				$result[$rowId] = $data['UF_FILE'];
			}
		}

		return $result;
	}

	private function updatePropertyTable(int $propertyId, string $tableName, bool $isNewHighload): Result
	{
		$result = new Result();

		$ibp = new CIBlockProperty();
		$oldFields = $ibp::GetByID($propertyId)->Fetch();
		if ($oldFields)
		{
			$settings = (array)($oldFields['USER_TYPE_SETTINGS'] ?? []);

			$isChangeTableName = isset($settings['TABLE_NAME']) && $settings['TABLE_NAME'] !== $tableName;
			if ($isNewHighload || $isChangeTableName)
			{
				$settings['TABLE_NAME'] = $tableName;

				$ibp->Update($propertyId, [
					'USER_TYPE_SETTINGS' => serialize($settings),
				]);
				if ($ibp->LAST_ERROR)
				{
					$result->addError(
						new Error($ibp->LAST_ERROR)
					);
				}
			}
		}

		return $result;
	}
}
