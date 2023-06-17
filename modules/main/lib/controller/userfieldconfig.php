<?php

namespace Bitrix\Main\Controller;

use Bitrix\Intranet\ActionFilter;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserField\UserFieldAccess;
use Bitrix\Main\UserFieldTable;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Engine\Response\DataType\Page;

class UserFieldConfig extends Controller
{
	protected function getDefaultPreFilters()
	{
		$defaultPreFilters = parent::getDefaultPreFilters();

		if (Loader::includeModule('intranet'))
		{
			$defaultPreFilters[] = new ActionFilter\IntranetUser();
		}

		return $defaultPreFilters;
	}

	protected function getAccess(string $moduleId, ?\CRestServer $restServer = null): ?UserFieldAccess
	{
		try
		{
			$access = UserFieldAccess::getInstance($moduleId);
		}
		catch (ObjectNotFoundException $e)
		{
			if ($restServer)
			{
				$this->addError(new Error($e->getMessage()));

				return null;
			}

			throw $e;
		}

		if ($restServer)
		{
			$scopes = $restServer->getAuthScope();
			if (!in_array($moduleId, $scopes, true))
			{
				$this->addError(
					new Error(
						'The current method required more scopes. (' . $moduleId . ')',
						\Bitrix\Rest\Engine\ActionFilter\Scope::ERROR_INSUFFICIENT_SCOPE
					)
				);

				return null;
			}
		}

		return $access;
	}

	protected function processAfterAction(Action $action, $result)
	{
		$this->fillErrorsFromApplication();

		return null;
	}

	protected function getEmptyModuleIdError(): Error
	{
		return new Error(Loc::getMessage('MAIN_USER_FIELD_CONTROLLER_EMPTY_MODULE_ID_ERROR'));
	}

	protected function getReadAccessDeniedError(): Error
	{
		return new Error(Loc::getMessage('MAIN_USER_FIELD_CONTROLLER_ACCESS_VIEW_ERROR'));
	}

	protected function getAddAccessDeniedError(): Error
	{
		return new Error(loc::getMessage('MAIN_USER_FIELD_CONTROLLER_ACCESS_CREATE_ERROR'));
	}

	protected function getUpdateAccessDeniedError(): Error
	{
		return new Error(loc::getMessage('MAIN_USER_FIELD_CONTROLLER_ACCESS_MODIFY_ERROR'));
	}

	protected function getDeleteAccessDeniedError(): Error
	{
		return new Error(loc::getMessage('MAIN_USER_FIELD_CONTROLLER_ACCESS_DELETE_ERROR'));
	}

	protected function getCommonError(): Error
	{
		return new Error(Loc::getMessage('MAIN_USER_FIELD_CONTROLLER_ERROR'));
	}

	public function getTypesAction(string $moduleId, \CRestServer $restServer = null): ?array
	{
		$access = $this->getAccess($moduleId, $restServer);
		if (!$access)
		{
			return null;
		}

		$result = [];

		$restrictedTypes = array_flip($access->getRestrictedTypes());

		global $USER_FIELD_MANAGER;
		$types = $USER_FIELD_MANAGER->GetUserType();

		if (empty($restrictedTypes))
		{
			return $types;
		}

		foreach ($types as $id => $type)
		{
			if (!isset($restrictedTypes[$id]))
			{
				$result[$id] = [
					'userTypeId' => $type['USER_TYPE_ID'],
					'description' => $type['DESCRIPTION'],
				];
			}
		}

		return [
			'types' => $result,
		];
	}

	protected function prepareFields(array $fields): array
	{
		$allowedKeys = [
			'editFormLabel' => true,
			'multiple' => true,
			'userTypeId' => true,
			'fieldName' => true,
			'enum' => true,
			'entityId' => true,
			'xmlId' => true,
			'sort' => true,
			'mandatory' => true,
			'showFilter' => true,
			'isSearchable' => true,
			'settings' => true,
		];

		$fields = array_intersect_key($fields, $allowedKeys);

		$converter = new Converter(Converter::TO_UPPER | Converter::KEYS | Converter::TO_SNAKE);

		if ($fields['showFilter'] === 'Y')
		{
			$fields['showFilter'] = 'E';
		}

		return $converter->process($fields);
	}

	public function preparePublicData(array $field): array
	{
		foreach (UserFieldTable::getLabelFields() as $labelName)
		{
			if (isset($field[$labelName]) && !is_array($field[$labelName]))
			{
				$field[$labelName] = [
					Loc::getCurrentLang() => $field[$labelName],
				];
			}
		}
		$settings = $field['SETTINGS'];

		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$field = (
			new Converter(
				Converter::KEYS
				| Converter::TO_CAMEL
				| Converter::LC_FIRST
				| Converter::RECURSIVE
			)
		)->process($field);

		$field['settings'] = $settings;

		return $field;
	}

	protected function fillErrorsFromApplication(): void
	{
		global $APPLICATION;

		$exception = $APPLICATION->GetException();
		if (($exception instanceof \CAdminException) && is_array($exception->messages))
		{
			foreach ($exception->messages as $message)
			{
				if (isset($message['text']))
				{
					$message = $message['text'];
				}
				$this->addError(new Error($message));
			}
		}

		$APPLICATION->ResetException();
	}

	protected function prepareEnums(array $newEnums, array $currentEnums): array
	{
		$deletedEnum = [];
		$storedEnum = [];
		$updatedEnum = [];

		foreach ($currentEnums as $enumItem)
		{
			$storedEnum[$enumItem['ID']] = $enumItem;
			$deletedEnum[$enumItem['ID']] = true;
		}

		$countAdded = 0;
		foreach ($newEnums as $enumItem)
		{
			if (is_array($enumItem))
			{
				if (!empty($enumItem['id']))
				{
					if (empty($enumItem['xmlId']))
					{
						$enumItem['xmlId'] = $storedEnum[$enumItem['id']]['XML_ID'];
					}
					if (empty($enumItem['def']))
					{
						$enumItem['def'] = $storedEnum[$enumItem['id']]['DEF'];
					}

					unset($deletedEnum[$enumItem['id']]);
				}
				$itemKey = ($enumItem['id'] > 0 ? $enumItem['id'] : 'n' . ($countAdded++));

				$itemDescription = [
					'VALUE' => $enumItem['value'],
					'DEF' => $enumItem['def'] === 'Y' ? 'Y' : 'N',
					'SORT' => $enumItem['sort'],
				];

				if (!empty($enumItem['xmlId']))
				{
					$itemDescription['XML_ID'] = $enumItem['xmlId'];
				}

				$enumItem['sort'] = (int)$enumItem['sort'];
				if ($enumItem['sort'] > 0)
				{
					$itemDescription['SORT'] = $enumItem['sort'];
				}

				$updatedEnum[$itemKey] = $itemDescription;
			}
		}

		foreach ($deletedEnum as $deletedId => $t)
		{
			$updatedEnum[$deletedId] = [
				'ID' => $deletedId,
				'DEL' => 'Y',
			];
		}

		return $updatedEnum;
	}

	protected function updateEnums(int $id, array $enums, array $currentEnums = []): void
	{
		$updatedEnum = $this->prepareEnums($enums, $currentEnums);

		$enumValuesManager = new \CUserFieldEnum();
		$setEnumResult = $enumValuesManager->setEnumValues($id, $updatedEnum);

		if (!$setEnumResult)
		{
			$this->fillErrorsFromApplication();
		}
	}

	public function getAction(string $moduleId, int $id, \CRestServer $restServer = null): ?array
	{
		$access = $this->getAccess($moduleId, $restServer);
		if (!$access)
		{
			return null;
		}
		if ($this->getErrors())
		{
			return null;
		}
		if (!$access->canRead($id))
		{
			$this->addError($this->getReadAccessDeniedError());
			return null;
		}

		$field = UserFieldTable::getFieldData($id);
		if (is_array($field))
		{
			return [
				'field' => $this->preparePublicData($field),
			];
		}

		return null;
	}

	public function listAction(
		string $moduleId,
		array $select = ['*'],
		array $order = [],
		array $filter = [],
		PageNavigation $pageNavigation = null,
		\CRestServer $restServer = null
	): ?Page
	{
		$access = $this->getAccess($moduleId, $restServer);
		if (!$access)
		{
			return null;
		}

		$converter = new Converter(Converter::TO_UPPER | Converter::KEYS | Converter::TO_SNAKE);
		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$filter = $converter->process($filter);
		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$order = $converter->process($order);
		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$select = $converter->process($select);

		if (!$access->canReadWithFilter($filter))
		{
			$this->addError($this->getReadAccessDeniedError());
			return null;
		}
		/** @noinspection CallableParameterUseCaseInTypeContextInspection */
		$filter = $access->prepareFilter($filter);

		$runtime = [];
		if (!empty($select['LANGUAGE']) && is_string($select['LANGUAGE']))
		{
			$runtime[] = UserFieldTable::getLabelsReference('LABELS', $select['LANGUAGE']);
			unset($select['LANGUAGE']);

			$select = array_merge($select, UserFieldTable::getLabelsSelect());
		}

		$fields = [];
		$list = UserFieldTable::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'offset' => $pageNavigation ? $pageNavigation->getOffset() : null,
			'limit' => $pageNavigation ? $pageNavigation->getLimit() : null,
			'runtime' => $runtime,
		]);
		while ($field = $list->fetch())
		{
			$fields[] = $this->preparePublicData($field);
		}

		return new Page('fields', $fields, static function () use ($filter) {
			return UserFieldTable::getCount($filter);
		});
	}

	public function addAction(string $moduleId, array $field, \CRestServer $restServer = null): ?array
	{
		$access = $this->getAccess($moduleId, $restServer);
		if (!$access)
		{
			return null;
		}
		$field = $this->prepareFields($field);
		if (!$access->canAdd($field))
		{
			$this->addError($this->getAddAccessDeniedError());
			return null;
		}

		$fieldName = $field['FIELD_NAME'] ?? '';
		$entityId = $field['ENTITY_ID'] ?? '';
		$prefix = 'UF_' . $entityId . '_';
		if (strpos($fieldName, $prefix) !== 0)
		{
			$this->addError(new Error(Loc::getMessage('MAIN_USER_FIELD_CONTROLLER_FIELD_NAME_ERROR')));
			return null;
		}

		$userTypeEntity = new \CUserTypeEntity();
		$id = $userTypeEntity->Add($field);
		if ($id > 0)
		{
			if ($field['USER_TYPE_ID'] === 'enumeration')
			{
				$this->updateEnums($id, (array)$field['ENUM']);
			}

			return [
				'field' => $this->preparePublicData(UserFieldTable::getFieldData($id)),
			];
		}

		$this->fillErrorsFromApplication();
		if (!$this->getErrors())
		{
			$this->addError($this->getCommonError());
		}

		return null;
	}

	public function updateAction(string $moduleId, int $id, array $field, \CRestServer $restServer = null): ?array
	{
		$access = $this->getAccess($moduleId, $restServer);
		if (!$access)
		{
			return null;
		}
		if (!$access->canUpdate($id))
		{
			$this->addError($this->getUpdateAccessDeniedError());
			return null;
		}

		$field = $this->prepareFields($field);
		$userTypeEntity = new \CUserTypeEntity();
		$isUpdated = $userTypeEntity->Update($id, $field);
		if ($isUpdated)
		{
			if ($field['USER_TYPE_ID'] === 'enumeration')
			{
				$this->updateEnums($id, (array)$field['ENUM'], (array)UserFieldTable::getFieldData($id)['ENUM']);
			}

			return [
				'field' => $this->preparePublicData(UserFieldTable::getFieldData($id)),
			];
		}

		$this->fillErrorsFromApplication();
		if (!$this->getErrors())
		{
			$this->addError($this->getCommonError());
		}

		return null;
	}

	public function deleteAction(string $moduleId, int $id, \CRestServer $restServer = null): void
	{
		$access = $this->getAccess($moduleId, $restServer);
		if (!$access)
		{
			return;
		}
		if (!$access->canDelete($id))
		{
			$this->addError($this->getDeleteAccessDeniedError());
			return;
		}

		$userTypeEntity = new \CUserTypeEntity();
		$userTypeEntity->Delete($id);
	}
}
