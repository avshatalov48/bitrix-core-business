<?php

namespace Bitrix\UI\FileUploader;

class CommitOptions
{
	protected string $moduleId = '';
	protected string $savePath = '';
	protected bool $forceRandom = false;
	protected bool $skipExtension = false;
	protected string $addDirectory = '';

	public function __construct(array $options = [])
	{
		$optionNames = [
			'moduleId',
			'savePath',
			'forceRandom',
			'skipExtension',
			'addDirectory',
		];

		foreach ($optionNames as $optionName)
		{
			if (array_key_exists($optionName, $options))
			{
				$optionValue = $options[$optionName];
				$setter = 'set' . ucfirst($optionName);
				$this->$setter($optionValue);
			}
		}
	}

	public function getModuleId(): string
	{
		return $this->moduleId;
	}

	public function setModuleId(string $moduleId): self
	{
		$this->moduleId = $moduleId;

		return $this;
	}

	public function getSavePath(): string
	{
		return $this->savePath;
	}

	public function setSavePath(string $savePath): self
	{
		$this->savePath = $savePath;

		return $this;
	}

	public function isForceRandom(): bool
	{
		return $this->forceRandom;
	}

	public function setForceRandom(bool $forceRandom): self
	{
		$this->forceRandom = $forceRandom;

		return $this;
	}

	public function isSkipExtension(): bool
	{
		return $this->skipExtension;
	}

	public function setSkipExtension(bool $skipExtension): self
	{
		$this->skipExtension = $skipExtension;

		return $this;
	}

	public function getAddDirectory(): string
	{
		return $this->addDirectory;
	}

	public function setAddDirectory(string $addDirectory): self
	{
		$this->addDirectory = $addDirectory;

		return $this;
	}
}
