<?php

namespace Bitrix\Main\UserField\File;

use Bitrix\Main\UI\FileInputUtility;

final class ManualUploadRegistry
{
	private static ?self $instance = null;

	private array $storage = [];

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function isFileRegistered(array $userField, int $fileId): bool
	{
		$key = $this->getUserFieldStorageKey($userField);
		if (!$key)
		{
			return false;
		}

		return isset($this->storage[$key][$fileId]);
	}

	public function registerFile(array $userField, int $fileId): void
	{
		$key = $this->getUserFieldStorageKey($userField);
		if (!$key)
		{
			return;
		}

		$this->storage[$key] ??= [];
		$this->storage[$key][$fileId] = $fileId;
	}

	private function getUserFieldStorageKey(array $userField): ?string
	{
		if (!$this->isUserField($userField))
		{
			return null;
		}

		return FileInputUtility::instance()->getUserFieldCid($userField);
	}

	private function isUserField(array $userFieldCandidate): bool
	{
		return isset($userFieldCandidate['ID'], $userFieldCandidate['ENTITY_ID'], $userFieldCandidate['FIELD_NAME']);
	}
}
