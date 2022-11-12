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
	/**
	 * User field ENTITY_ID
	 *
	 * @return string
	 */
	abstract public function getUfEntityId(): string;

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
		$fields['USER_FIELD_PREFIX'] = $this->getUfEntityId();
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
		$result = [];

		$userFieldsInfos = $this->getUfTypeManager()->GetUserFields(
			$this->getUfEntityId(),
			0,
			LANGUAGE_ID
		);
		foreach ($userFieldsInfos as $userFieldInfo)
		{
			$fieldName = $userFieldInfo['FIELD_NAME'];
			$result[] = [
				'name' => $fieldName,
				'title' => $userFieldInfo['EDIT_FORM_LABEL'] ?? $fieldName,
				'type' => 'userField',
				'data' => [
					'fieldInfo' => [
						'USER_TYPE_ID' => $userFieldInfo['USER_TYPE_ID'],
						'ENTITY_ID' => $this->getUfEntityId(),
						'ENTITY_VALUE_ID' => $this->getEntityId(),
						'FIELD' => $fieldName,
						'MULTIPLE' => $userFieldInfo['MULTIPLE'],
						'MANDATORY' => $userFieldInfo['MANDATORY'],
						'SETTINGS' => $userFieldInfo['SETTINGS'] ?? null,
					],
				],
			];
		}

		return $result;
	}

	/**
	 * Value user field for `ENTITY_DATA` component param.
	 *
	 * @param array $field
	 *
	 * @return void
	 */
	protected function getUfEntityDataValue(array $field)
	{
		$fieldInfo = $field['data']['fieldInfo'];
		$fieldName = $fieldInfo['FIELD'] ?? null;
		if (!$fieldName)
		{
			$value = null;
		}
		else
		{
			$value = $this->getUfTypeManager()->GetUserFieldValue(
				$this->getUfEntityId(),
				$fieldName,
				$this->getEntityId()
			);
		}

		if (!empty($value))
		{
			$fieldInfo['VALUE'] = $value;
		}

		$userFieldDispatcher = \Bitrix\Main\UserField\Dispatcher::instance();
		$signatire = $userFieldDispatcher->getSignature($fieldInfo);

		if (empty($value))
		{
			return [
				'IS_EMPTY' => true,
				'SIGNATURE' => $signatire,
			];
		}

		return [
			'IS_EMPTY' => false,
			'VALUE' => $value,
			'SIGNATURE' => $signatire,
		];
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
		array_push($fields, ... $this->getUfEntityFields());

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
		$ufFields = $this->getUfEntityFields();
		foreach ($ufFields as $item)
		{
			$fieldName = $item['name'];
			$entityData[$fieldName] = $this->getUfEntityDataValue($item);
		}

		return $entityData;
	}
}
