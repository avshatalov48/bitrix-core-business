<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Entity\File\Param;

class Transcribable extends BaseParam
{
	public function getValue(): bool
	{
		return $this->value === 'Y';
	}

	public static function formatForDb(int $fileId, bool $value): array
	{
		return [
			'DISK_FILE_ID' => $fileId,
			'PARAM_NAME' => ParamName::IsTranscribable->value,
			'PARAM_VALUE' => $value ? 'Y' : 'N',
		];
	}

	public function toArray(): array
	{
		return [
			'DISK_FILE_ID' => $this->fileId,
			'PARAM_NAME' => ParamName::IsTranscribable->value,
			'PARAM_VALUE' => $this->value,
		];
	}
}
