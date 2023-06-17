<?php

namespace Bitrix\Bizproc;

class File
{
	private array $file;

	private function __construct(array $file)
	{
		$this->file = $file;
	}

	public function getFileArray(): array
	{
		return $this->file;
	}

	public static function openById(int $fileId): Result
	{
		$file = \CFile::MakeFileArray($fileId);
		if(!is_array($file))
		{
			return Result::createFromErrorCode(Error::FILE_NOT_FOUND);
		}

		return Result::createOk(['file' => new static($file)]);
	}
}