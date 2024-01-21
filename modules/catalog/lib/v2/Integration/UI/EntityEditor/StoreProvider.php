<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntityEditor;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Config\Ini;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntityEditor\BaseProvider;
use Bitrix\UI\EntityEditor\ProviderWithUserFieldsTrait;

/**
 * Provider for store (warehouse) entity.
 */
class StoreProvider extends BaseProvider
{
	use ProviderWithUserFieldsTrait {
		getUfComponentFields as getUfComponentFieldsParent;
	}

	private ?string $userFieldCreatePageUrl;

	/**
	 * @inheritDoc
	 */
	public function getUfEntityId(): string
	{
		return StoreTable::getUfId();
	}

	public function getUfPrefix(): string
	{
		return $this->getUfEntityId();
	}

	/**
	 * Entity field values.
	 *
	 * @var array
	 */
	private array $entity;

	/**
	 * @param array $entityFields
	 * @param string|null $userFieldCreatePageUrl
	 */
	public function __construct(array $entityFields, ?string $userFieldCreatePageUrl = null)
	{
		$this->entity = $entityFields;
		$this->userFieldCreatePageUrl = $userFieldCreatePageUrl;
	}

	/**
	 * @inheritDoc
	 */
	public function getEntityId(): ?int
	{
		return $this->entity['ID'] ?? null;
	}

	/**
	 * @inheritDoc
	 */
	public function getGUID(): string
	{
		$id = $this->getEntityId() ?? 0;

		return "store_{$id}_details";
	}

	/**
	 * @inheritDoc
	 *
	 * @return string
	 */
	public function getConfigId(): string
	{
		return 'store_details';
	}

	/**
	 * @inheritDoc
	 */
	public function getEntityTypeName(): string
	{
		return 'store';
	}

	/**
	 * @inheritDoc
	 */
	public function getEntityFields(): array
	{
		$isDefaultStore = isset($this->entity['IS_DEFAULT']) && $this->entity['IS_DEFAULT'] === 'Y';

		$fields = [
			[
				'name' => 'TITLE',
				'title' => static::getFieldTitle('TITLE'),
				'type' => 'text',
				'showAlways' => true,
			],
			[
				'name' => 'CODE',
				'title' => static::getFieldTitle('CODE'),
				'type' => 'text',
				'visibilityPolicy' => 'edit',
			],
			[
				'name' => 'ADDRESS',
				'title' => static::getFieldTitle('ADDRESS'),
				'type' => 'textarea',
				'required' => true,
				'showAlways' => true,
			],
			[
				'name' => 'DESCRIPTION',
				'title' => static::getFieldTitle('DESCRIPTION'),
				'type' => 'textarea',
				'visibilityPolicy' => 'edit',
			],
			[
				'name' => 'PHONE',
				'title' => static::getFieldTitle('PHONE'),
				'type' => 'text',
				'showAlways' => true,
			],
			[
				'name' => 'SCHEDULE',
				'title' => static::getFieldTitle('SCHEDULE'),
				'type' => 'text',
				'showAlways' => true,
			],
			[
				'name' => 'EMAIL',
				'title' => static::getFieldTitle('EMAIL'),
				'type' => 'email',
				'visibilityPolicy' => 'edit',
			],
			[
				'name' => 'GPS_N',
				'title' => static::getFieldTitle('GPS_N'),
				'type' => 'text',
				'visibilityPolicy' => 'edit',
			],
			[
				'name' => 'GPS_S',
				'title' => static::getFieldTitle('GPS_S'),
				'type' => 'text',
				'visibilityPolicy' => 'edit',
			],
			[
				'name' => 'XML_ID',
				'title' => static::getFieldTitle('XML_ID'),
				'type' => 'text',
				'visibilityPolicy' => 'edit',
			],
			[
				'name' => 'SORT',
				'title' => static::getFieldTitle('SORT'),
				'type' => 'number',
				'default_value' => 100,
				'visibilityPolicy' => 'edit',
			],
			[
				'name' => 'ACTIVE',
				'title' => static::getFieldTitle('ACTIVE'),
				'type' => 'boolean',
				'default_value' => 'Y',
				'editable' => !$isDefaultStore,
				'showAlways' => true,
			],
			[
				'name' => 'ISSUING_CENTER',
				'title' => static::getFieldTitle('ISSUING_CENTER'),
				'type' => 'boolean',
				'visibilityPolicy' => 'edit',
			],
			[
				'name' => 'IMAGE_ID',
				'title' => static::getFieldTitle('IMAGE_ID'),
				'type' => 'file',
				'showAlways' => true,
				'data' => [
					'multiple' => false,
					'maxFileSize' => Ini::unformatInt(ini_get('upload_max_filesize')),
				],
			],
		];

		$fields = $this->fillUfEntityFields($fields);

		return $fields;
	}

	/**
	 * @inheritDoc
	 */
	public function getEntityConfig(): array
	{
		$elements = [];
		foreach ($this->getEntityFields() as $item)
		{
			$elements[] = [
				'name' => $item['name'],
			];
		}

		$sectionElements = [
			[
				'name' => 'main',
				'title' => Loc::getMessage('CATALOG_STORE_DETAIL_MAIN_SECTION'),
				'type' => 'section',
				'elements' => $elements,
				'data' => [
					'isRemovable' => 'false',
				],
				'sort' => 100,
			],
		];

		return [
			[
				'name' => 'left',
				'type' => 'column',
				'elements' => $sectionElements,
			],
		];
	}

	/**
	 * @inheritDoc
	 *
	 * @return array
	 */
	protected function getUfComponentFields(): array
	{
		$result = $this->getUfComponentFieldsParent();

		if (isset($this->userFieldCreatePageUrl))
		{
			$result['USER_FIELD_CREATE_PAGE_URL'] = $this->userFieldCreatePageUrl;
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getEntityData(): array
	{
		$result = [];

		foreach ($this->getEntityFields() as $item)
		{
			$field = $item['name'];
			$type = $item['type'] ?? 'text';
			$value = $this->entity[$field] ?? $item['default_value'] ?? null;
			$result[$field] = $this->prepareValue($type, $value);
		}

		$result = $this->fillUfEntityData($result);

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getEntityControllers(): array
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function isReadOnly(): bool
	{
		return ! AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_MODIFY);
	}

	/**
	 * Get field title for editor.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	private static function getFieldTitle(string $fieldName): string
	{
		static $managerFields;

		if (!isset($managerFields))
		{
			$managerFields = [];
			foreach (StoreTable::getMap() as $field)
			{
				/**
				 * @var \Bitrix\Main\ORM\Fields\Field $field
				 */

				$name = $field->getName();
				$managerFields[$name] =
					Loc::getMessage("CATALOG_STORE_DETAIL_FIELD_TITLE_{$name}")
					?? $field->getTitle()
				;
			}
		}

		return $managerFields[$fieldName] ?? $fieldName;
	}

	private function prepareValue(string $type, $value)
	{
		if (!isset($value))
		{
			return null;
		}

		return (string)$value;
	}
}
