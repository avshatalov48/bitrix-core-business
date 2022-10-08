<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

class PendingFile
{
	private string $id;
	private ?TempFile $tempFile = null;
	private ErrorCollection $errors;
	private string $status = PendingFileStatus::INIT;

	public function __construct(string $id)
	{
		$this->id = $id;
		$this->errors = new ErrorCollection();
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getGuid(): ?string
	{
		return $this->tempFile !== null ? $this->tempFile->getGuid() : null;
	}

	public function getFileId(): ?int
	{
		return $this->isValid() && $this->tempFile !== null ? $this->tempFile->getFileId() : null;
	}

	public function setTempFile(TempFile $tempFile): void
	{
		$this->status = PendingFileStatus::PENDING;
		$this->tempFile = $tempFile;
	}

	protected function getTempFile(): ?TempFile
	{
		return $this->tempFile;
	}

	public function getStatus(): string
	{
		return $this->status;
	}

	public function makePersistent(): void
	{
		if ($this->getStatus() === PendingFileStatus::PENDING)
		{
			$this->getTempFile()->makePersistent();
			$this->status = PendingFileStatus::COMMITTED;
		}
	}

	public function remove(): void
	{
		if ($this->getStatus() === PendingFileStatus::PENDING)
		{
			$this->getTempFile()->delete();
			$this->status = PendingFileStatus::REMOVED;
		}
	}

	public function addError(Error $error): void
	{
		$this->status = PendingFileStatus::ERROR;
		$this->errors[] = $error;
	}

	public function getErrors(): array
	{
		return $this->errors->toArray();
	}

	public function isValid(): bool
	{
		return (
			$this->getStatus() === PendingFileStatus::PENDING
			|| $this->getStatus() === PendingFileStatus::COMMITTED
		);
	}
}