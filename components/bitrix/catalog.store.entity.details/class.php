<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\v2\Integration\UI\EntityEditor\StoreProvider;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\UI\FileInputUtility;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loader::requireModule('catalog');

/**
 * Store view and edit form component.
 *
 * Params:
 * - ID - id entity (if new record can be empty or `0`);
 * - PATH_TO_DETAIL - URL template for detail page (variable with id must be named `#ID#`);
 */
class CatalogStoreEntityDetails extends CBitrixComponent implements Controllerable
{
	private int $entityId;
	private ?array $entityFields;
	private AccessController $accessController;

	private function init(): void
	{
		$this->entityId = (int)($this->arParams['ID'] ?? 0);
		$this->accessController = AccessController::getCurrent();
	}

	/**
	 * Is new entity.
	 *
	 * @return bool
	 */
	private function isNew(): bool
	{
		return !$this->entityId;
	}

	/**
	 * @inheritDoc
	 */
	public function executeComponent()
	{
		$this->init();

		if (!$this->checkAccess() || !$this->loadEntity())
		{
			$this->includeComponentTemplate();
			return;
		}

		$this->initForm();
		$this->includeComponentTemplate();
	}

	/**
	 * Init `arResult` form values.
	 *
	 * @return void
	 */
	private function initForm(): void
	{
		$provider = $this->getEditorProvider();

		$this->arResult['FORM'] = $provider->getFields();
		$this->arResult['FORM']['ENABLE_CONFIGURATION_UPDATE'] = ! $provider->isReadOnly();
	}

	/**
	 * Form fields provider.
	 *
	 * @return StoreProvider
	 */
	private function getEditorProvider(): StoreProvider
	{
		return new StoreProvider($this->entityFields ?? []);
	}

	/**
	 * Load entity if exists.
	 *
	 * @return bool
	 */
	private function loadEntity(): bool
	{
		if ($this->isNew())
		{
			return true;
		}

		$entity = StoreTable::getRowById($this->entityId);
		if (!$entity)
		{
			$this->arResult['ERROR']['TITLE'] = Loc::getMessage('CATALOG_STORE_DETAIL_NOT_FOUND_ERROR');
			$this->arResult['ERROR']['DESCRIPTION'] = '';
			return false;
		}

		$this->entityFields = $entity;
		return true;
	}

	/**
	 * Checks access and add errors.
	 *
	 * @return bool
	 */
	private function checkAccess(): bool
	{
		$can =
			$this->accessController->check(ActionDictionary::ACTION_STORE_MODIFY)
			|| $this->accessController->check(ActionDictionary::ACTION_STORE_VIEW)
		;
		if (!$can)
		{
			$this->arResult['ERROR']['TITLE'] = Loc::getMessage('CATALOG_STORE_DETAIL_ACCESS_DENIED_ERROR');
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * Save AJAX action.
	 *
	 * Data loaded from field 'data' of request.
	 *
	 * @return array
	 */
	public function saveAction(): array
	{
		global $APPLICATION;

		/**
		 * @var \CMain $APPLICATION
		 */

		$this->init();

		if (!$this->accessController->check(ActionDictionary::ACTION_STORE_MODIFY))
		{
			return [
				'ERROR' => Loc::getMessage('CATALOG_STORE_DETAIL_ACCESS_DENIED_ERROR'),
			];
		}

		$fields = (array)($this->request->get('data') ?: []);
		$fields = $this->prepareFileFields($fields);
		if (empty($fields))
		{
			return [
				'ERROR' => Loc::getMessage('CATALOG_STORE_DETAIL_EMPTY_REQUEST_ERROR'),
			];
		}

		$result = $this->validateFields($fields);
		if (!$result->isSuccess())
		{
			return [
				'ERROR' => join(', ', $result->getErrorMessages()),
			];
		}

		$id = $this->entityId;
		if ($id > 0)
		{
			$ret =  CCatalogStore::Update($id, $fields);
			if ($ret === false)
			{
				$ex = $APPLICATION->GetException();
				$error =
					$ex instanceof \CApplicationException
						? $ex->GetString()
						: 'Unknown error'
				;
				$result->addError(new Error($error));
			}
		}
		else
		{
			$id =  CCatalogStore::Add($fields);
			if ($id === false)
			{
				$ex = $APPLICATION->GetException();
				$error =
					$ex instanceof \CApplicationException
						? $ex->GetString()
						: 'Unknown error'
				;
				$result->addError(new Error($error));
			}
		}

		if (!$result->isSuccess())
		{
			return [
				'ERROR' => join(' ', $result->getErrorMessages()),
			];
		}

		$this->saveUserFields($id, $fields);

		return [
			'ENTITY_ID' => $id,
			'REDIRECT_URL' => $this->getDetailsUrl($id),
		];
	}

	/**
	 * Validate fields with editor provider info.
	 *
	 * @param array $fields
	 *
	 * @return Result
	 */
	private function validateFields(array $fields): Result
	{
		$result = new Result();

		$editorFields = $this->getEditorProvider()->getEntityFields();
		foreach ($editorFields as $field)
		{
			$name = $field['name'] ?? null;
			if (!$name)
			{
				continue;
			}

			if (($field['required'] ?? false) && empty($fields[$name]))
			{
				$isSetEmptyField = array_key_exists($name, $fields);
				if ($this->isNew() || $isSetEmptyField)
				{
					$message = Loc::getMessage('CATALOG_STORE_DETAIL_FIELD_REQUIRED_ERROR', [
						'#NAME#' => $field['title'] ?? $name,
					]);
					$result->addError(new Error($message));
				}
			}
		}

		return $result;
	}

	/**
	 * Zeroing fields with files if they are marked for deletion.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	private function prepareFileFields(array $fields): array
	{
		$imageFields = [
			'IMAGE_ID',
		];
		foreach ($imageFields as $name)
		{
			$fileId = (int)($fields[$name] ?? 0);
			if ($fileId <= 0)
			{
				continue;
			}

			// mark as deleted
			$delName = "{$name}_del";
			if (
				isset($fields[$delName])
				&& (string)$fields[$name] === (string)$fields[$delName]
			)
			{
				$fields[$name] = null;
				continue;
			}

			// check as real uploaded file
			$uploaderControlId = mb_strtolower($name . '_uploader');
			$checkedFiles = FileInputUtility::instance()->checkFiles(
				$uploaderControlId,
				[$fileId]
			);
			if (empty($checkedFiles))
			{
				unset($fields[$name]);
				continue;
			}

			$fields[$name] = CFile::MakeFileArray($fileId);
		}

		return $fields;
	}

	/**
	 * @inheritDoc
	 */
	protected function listKeysSignedParameters()
	{
		return [
			'ID',
			'PATH_TO_DETAIL',
		];
	}

	/**
	 * Detail URL for store with id.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	private function getDetailsUrl(int $id): string
	{
		$url = (string)($this->arParams['PATH_TO_DETAIL'] ?? '');

		return str_replace('#ID#', $id, $url);
	}

	/**
	 * Save store user fields.
	 *
	 * @param int $storeId
	 * @param array $fields
	 *
	 * @return void
	 */
	private function saveUserFields(int $storeId, array $fields): void
	{
		$userFields = array_filter($fields, fn($key) => strpos($key, 'UF_') === 0, ARRAY_FILTER_USE_KEY);
		if (empty($userFields))
		{
			return;
		}

		$manager = new CUserTypeManager();
		$manager->Update(StoreProvider::USER_FIELD_TYPE, $storeId, $userFields);
	}
}
