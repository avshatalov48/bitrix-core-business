<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Error;

class UploadResult extends \Bitrix\Main\Result
{
	protected ?TempFile $tempFile = null;
	protected ?string $token = null;
	protected bool $done = false;

	public static function reject(Error $error): self
	{
		$result = new static();
		$result->addError($error);

		return $result;
	}

	public function getTempFile(): ?TempFile
	{
		return $this->tempFile;
	}

	public function setTempFile(TempFile $tempFile)
	{
		$this->tempFile = $tempFile;
	}

	public function getToken(): ?string
	{
		return $this->token;
	}

	public function setToken(string $token)
	{
		$this->token = $token;
	}

	public function setDone(bool $done): void
	{
		$this->done = $done;
	}

	public function isDone(): bool
	{
		return $this->done;
	}
}