<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Entity\File\Param;

abstract class BaseParam implements Param
{
	protected int $fileId;
	protected ParamName $name;
	protected string $value;

	public function __construct(int $fileId, ParamName $paramName, string $value)
	{
		$this->name = $paramName;
		$this->fileId = $fileId;
		$this->value = $value;
	}

	public function getFileId(): int
	{
		return $this->fileId;
	}

	public static function getInstance(int $fileId, ParamName $paramName, string $value): Param
	{
		return match ($paramName)
		{
			ParamName::IsTranscribable => (new Transcribable($fileId, $paramName, $value)),
		};
	}
}
