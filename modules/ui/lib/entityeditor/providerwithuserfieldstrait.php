<?php

namespace Bitrix\UI\EntityEditor;

use Bitrix\Main\UserField\Dispatcher;
use Bitrix\Main\UserField\Internal\UserFieldHelper;
use CUserTypeManager;

/**
 * A trait to expand the functionality of the provider and the ability to work with UF fields.
 *
 * For start, you need:
 * 1. implement method `getUfEntityId`
 * 2. append UF info to entity fields - see method ::fillUfEntityFields
 * 3. append UF values to entity data - see method ::fillUfEntityData
 */
trait ProviderWithUserFieldsTrait
{
	protected $userFieldInfos = null;

	/**
	 * User field ENTITY_ID
	 *
	 * @return string
	 */
	abstract public function getUfEntityId(): string;

	/**
	 * The prefix for UF codes
	 *
	 * @return string
	 */
	abstract public function getUfPrefix(): string;

	/**
	 * User field dispatcher.
	 *
	 * @return Dispatcher
	 */
	private function getUfDispatcher(): Dispatcher
	{
		return Dispatcher::instance();
	}

	/**
	 * User field type manager.
	 *
	 * @return CUserTypeManager
	 */
	private function getUfTypeManager(): CUserTypeManager
	{
		return UserFieldHelper::getInstance()->getManager();
	}

	/**
	 * Override BaseProvider method.
	 *
	 * @see \Bitrix\UI\EntityEditor\BaseProvider
	 *
	 * @return array
	 */
	public function getFields(): array
	{
		$fields = parent::getFields();
		$fields += $this->getUfComponentFields();

		return $fields;
	}

	/**
	 * Component params.
	 *
	 * @return array
	 */
	protected function getUfComponentFields(): array
	{
		$fields = [];

		$fields['ENABLE_USER_FIELD_CREATION'] = true;
		$fields['ENABLE_USER_FIELD_MANDATORY_CONTROL'] = true;
		$fields['USER_FIELD_ENTITY_ID'] = $this->getUfEntityId();
		$fields['USER_FIELD_PREFIX'] = $this->getUfPrefix();
		$fields['USER_FIELD_CREATE_SIGNATURE'] = $this->getUfDispatcher()->getCreateSignature([
			'ENTITY_ID' => $this->getUfEntityId(),
		]);
		$fields['USER_FIELD_CREATE_PAGE_URL'] = null;

		return $fields;
	}

	/**
	 * Entity fields info of user fields.
	 *
	 * @see \Bitrix\UI\EntityEditor\BaseProvider method `getEntityFields`
	 *
	 * @return array
	 */
	protected function getUfEntityFields(): array
	{
		if (!is_null($this->userFieldInfos))
		{
			return $this->userFieldInfos;
		}

		$result = [];

		$userFieldsInfos = $this->getUfTypeManager()->GetUserFields(
			$this->getUfEntityId(),
			0,
			LANGUAGE_ID
		);
		foreach ($userFieldsInfos as $userFieldInfo)
		{
			$fieldName = $userFieldInfo['FIELD_NAME'];
			$fieldInfo = [
				'USER_TYPE_ID' => $userFieldInfo['USER_TYPE_ID'],
				'ENTITY_ID' => $this->getUfEntityId(),
				'ENTITY_VALUE_ID' => $this->getEntityId() ?? 0,
				'FIELD' => $fieldName,
				'MULTIPLE' => $userFieldInfo['MULTIPLE'],
				'MANDATORY' => $userFieldInfo['MANDATORY'],
				'SETTINGS' => $userFieldInfo['SETTINGS'] ?? null,
			];

			// required for the enum fields to work on mobile
			if ($fieldInfo['USER_TYPE_ID'] === 'enumeration')
			{
				$fieldInfo['ENUM'] = [];
				$enumDbResult = \CUserFieldEnum::GetList(
					[],
					[
						'USER_FIELD_ID' => $userFieldInfo['ID'],
					]
				);
				while ($enum = $enumDbResult->Fetch())
				{
					$fieldInfo['ENUM'][] = [
						'ID' => $enum['ID'],
						'VALUE' => $enum['VALUE'],
					];
				}
			}

			$result[$fieldName] = [
				'name' => $fieldName,
				'title' => $userFieldInfo['EDIT_FORM_LABEL'] ?? $fieldName,
				'type' => 'userField',
				'data' => ['fieldInfo' => $fieldInfo],
				'editable' => $userFieldInfo['EDIT_IN_LIST'] === 'Y',
				'required' => isset($userFieldInfo['MANDATORY']) && $userFieldInfo['MANDATORY'] === 'Y',
			];
		}

		$this->userFieldInfos = $result;
		return $result;
	}

	/**
	 * Filling in the entity fields of UF data.
	 *
	 * Example:
	 * ```php
		public function getEntityFields(): array
		{
			$fields = [
				[
					'name' => 'TITLE',
				],
				// ...
			];

			$fields = $this->fillUfEntityFields($fields);

			return $fields;
		}
	 * ```
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	protected function fillUfEntityFields(array $fields): array
	{
		array_push($fields, ...array_values($this->getUfEntityFields()));

		return $fields;
	}

	/**
	 * Filling in entity data of UF data.
	 *
	 * Example:
	 * ```php
		public function getEntityData(): array
		{
			$result = [];

			// ...fill values of entity fields...

			$result = $this->fillUfEntityData($result);

			return $result;
		}
	 * ```
	 *
	 * @param array $entityData
	 *
	 * @return array
	 */
	protected function fillUfEntityData(array $entityData): array
	{
		return array_merge($entityData, $this->getUfEntityData());
	}

	/**
	 * Returns formatted values for the ENTITY_DATA parameter.
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	protected function getUfEntityData(): array
	{
		$userFields =
			$this
				->getUfTypeManager()
				->GetUserFields(
					$this->getUfEntityId(),
					$this->getEntityId() ?? 0,
					LANGUAGE_ID
				)
		;
		$userFieldInfos = $this->getUfEntityFields();

		$userFieldValues = [];
		foreach($userFields as $fieldName => $userField)
		{
			$fieldValue = $userField['VALUE'] ?? '';
			$fieldData = $userFieldInfos[$fieldName] ?? null;

			if (!is_array($fieldData))
			{
				continue;
			}

			$isEmptyField = true;
			$fieldParams = $fieldData['data']['fieldInfo'];
			if (
				(is_string($fieldValue) && $fieldValue !== '')
				|| (is_array($fieldValue) && !empty($fieldValue))
			)
			{
				$fieldParams['VALUE'] = $fieldValue;
				$isEmptyField = false;
			}

			$fieldSignature = $this->getUfDispatcher()->getSignature($fieldParams);
			if ($isEmptyField)
			{
				$userFieldValues[$fieldName] = array(
					'SIGNATURE' => $fieldSignature,
					'IS_EMPTY' => true
				);
			}
			else
			{
				$userFieldValues[$fieldName] = array(
					'VALUE' => $fieldValue,
					'SIGNATURE' => $fieldSignature,
					'IS_EMPTY' => false
				);
			}
		}

		return $userFieldValues;
	}
}
