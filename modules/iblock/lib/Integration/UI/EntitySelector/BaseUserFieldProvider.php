<?php

namespace Bitrix\Iblock\Integration\UI\EntitySelector;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\UserField\SignatureHelper;
use Bitrix\Main\UserField\SignatureManager;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

abstract class BaseUserFieldProvider extends BaseProvider
{
	private array $userField;
	protected ?array $list = null;

	public function __construct(array $options = [])
	{
		parent::__construct();

		$fieldInfo = $options['fieldInfo'] ?? [];

		if (
			empty($fieldInfo)
			|| !is_array($fieldInfo)
			|| empty($fieldInfo['ENTITY_ID'])
			|| empty($fieldInfo['FIELD'])
		)
		{
			throw new ArgumentException('Option "fieldInfo" is must be a valid array.');
		}

		if (!$this->validateSignature($fieldInfo))
		{
			throw new ArgumentException('Invalid signature.');
		}

		$this->userField = $this->loadUserField($fieldInfo);
		if (empty($this->userField))
		{
			throw new ArgumentException('User field not found.');
		}
	}

	private function validateSignature(array $fieldInfo): bool
	{
		$signature = $fieldInfo['SIGNATURE'] ?? '';
		$signatureManager = new SignatureManager();

		return SignatureHelper::validateSignature($signatureManager, $fieldInfo, $signature);
	}

	private function loadUserField(array $fieldInfo): array
	{
		$userFieldResult = \CUserTypeEntity::GetList([], [
			'ENTITY_ID' => $fieldInfo['ENTITY_ID'],
			'FIELD_NAME' => $fieldInfo['FIELD'],
		]);

		return $userFieldResult->Fetch();
	}

	protected function getUserField(): array
	{
		return $this->userField;
	}

	abstract protected function getEntityId(): string;

	public function isAvailable(): bool
	{
		global $USER;

		if (!$USER->isAuthorized())
		{
			return false;
		}

		return true;
	}

	private function getEnumList(): array
	{
		$result = [];

		$this->fillEnumList();

		foreach ($this->list as $id => $name)
		{
			if (!empty($id))
			{
				$result[] = [
					'ID' => $id,
					'NAME' => $name,
				];
			}
		}

		return $result;
	}

	protected function fillEnumList(): void
	{
		if ($this->list === null)
		{
			$userField = $this->getUserField();
			$this->getEnumTypeClass()::getEnumList($userField);

			$this->list = $userField['USER_TYPE']['FIELDS'];
		}
	}

	/**
	 * @return string|EnumType
	 */
	abstract protected function getEnumTypeClass(): string;

	public function getItems(array $ids): array
	{
		$result = [];

		foreach ($this->getEnumList() as $userFieldItem)
		{
			$result[] = $this->makeItem($userFieldItem);
		}

		return $result;
	}

	public function getSelectedItems(array $ids): array
	{
		return $this->getItems($ids);
	}

	public function fillDialog(Dialog $dialog): void
	{
		if ($dialog->getItemCollection()->count() > 0)
		{
			foreach ($dialog->getItemCollection() as $item)
			{
				$dialog->addRecentItem($item);
			}
		}

		foreach ($this->getEnumList() as $item)
		{
			$dialog->addRecentItem(
				$this->makeItem($item)
			);
		}
	}

	protected function makeItem(array $item): Item
	{
		return new Item([
			'id' => $item['ID'],
			'entityId' => $this->getEntityId(),
			'title' => $item['NAME'],
		]);
	}
}
