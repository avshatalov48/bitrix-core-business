<?php

namespace Bitrix\UI\FileUploader;

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

	/**
	 * @param UploadRequest $uploadRequest
	 *
	 * @return bool | CanUploadResult
	 */
	abstract public function canUpload();

	abstract public function canView(): bool;

	abstract public function verifyFileOwner(FileOwnershipCollection $files): void;

	abstract public function canRemove(): bool;

	// Events
	public function onUploadStart(UploadResult $uploadResult): void {}
	public function onUploadComplete(UploadResult $uploadResult): void {}
	public function onUploadError(UploadResult $uploadResult): void {}

	public function getCommitOptions(): CommitOptions
	{
		// Default commit options
		return new CommitOptions([
			'moduleId' => $this->getModuleId(),
			'savePath' => $this->getModuleId(),
		]);
	}

	final public function getOptions(): array
	{
		return $this->options;
	}

	final public function getOption(string $option, $defaultValue = null)
	{
		return array_key_exists($option, $this->options) ? $this->options[$option] : $defaultValue;
	}

	final public function getName(): string
	{
		return $this->name;
	}

	final public function getModuleId(): string
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
