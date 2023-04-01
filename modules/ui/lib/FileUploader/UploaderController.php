<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Result;

abstract class UploaderController
{
	protected array $options = [];

	private string $moduleId;
	private string $name;
	private ?string $filePath = null;

	protected function __construct(array $options)
	{
		// You have to validate $options in a derived class constructor
		$this->options = $options;

		$this->moduleId = getModuleId($this->getFilePath());
		$this->name = ControllerResolver::getNameByController($this);
	}

	abstract public function isAvailable(): bool;

	abstract public function getConfiguration(): Configuration;

	abstract public function canUpload(): bool;

	abstract public function canView(): bool;

	abstract public function verifyFileOwner(FileOwnershipCollection $files): void;

	abstract public function canRemove(): bool;

	// Events
	public function onUploadStart(TempFile $tempFile): ?Result
	{
		return null;
	}

	public function onUploadComplete(TempFile $tempFile): ?Result
	{
		return null;
	}

	public function onUploadError(UploadResult $uploadResult): void
	{

	}

	public function getCommitOptions(): CommitOptions
	{
		// Default commit options
		return new CommitOptions([
			'moduleId' => $this->getModuleId(),
			'savePath' => $this->getModuleId(),
		]);
	}

	public function getFingerprint(): string
	{
		return (string)\bitrix_sessid();
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getModuleId(): string
	{
		return $this->moduleId;
	}

	final protected function getFilePath(): string
	{
		if (!$this->filePath)
		{
			$reflector = new \ReflectionClass($this);
			$this->filePath = preg_replace('#[\\\/]+#', '/', $reflector->getFileName());
		}

		return $this->filePath;
	}
}
