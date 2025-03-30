<?php

namespace Bitrix\Main\UserField\File;

use Bitrix\Main\UI\FileInputUtility;

class UploaderContextGenerator
{
	public function __construct(
		private readonly FileInputUtility $fileInputUtility,
		private readonly array $userField
	)
	{
	}

	public function getContextInEditMode(string $cid): array
	{
		return array_merge(
			$this->getBaseOptions(),
			[
				'cid' => $cid,
			]
		);
	}

	public function getContextForFileInViewMode(int $fileId): array
	{
		return array_merge(
			$this->getBaseOptions(),
			[
				'signedFileId' => (new UploaderFileSigner(
					(string)($this->userField['ENTITY_ID'] ?? ''),
					(string)($this->userField['FIELD_NAME'] ?? '')
				))->sign($fileId),
			]
		);
	}

	public function getControlId(): string
	{
		return $this->fileInputUtility->getUserFieldCid($this->userField);
	}

	private function getBaseOptions(): array
	{
		return [
			'id' => (int)($this->userField['ID'] ?? 0),
			'entityId' => (string)($this->userField['ENTITY_ID'] ?? ''),
			'fieldName' => (string)($this->userField['FIELD_NAME'] ?? ''),
			'multiple' => ($this->userField['MULTIPLE'] ?? '') === 'Y',
		];
	}
}
