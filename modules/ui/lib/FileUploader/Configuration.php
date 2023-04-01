<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Config\Ini;

class Configuration
{
	protected ?int $maxFileSize = 256 * 1024 * 1024;
	protected int $minFileSize = 0;
	protected bool $acceptOnlyImages = false;
	protected array $acceptedFileTypes = [];
	protected array $ignoredFileNames = ['.ds_store', 'thumbs.db', 'desktop.ini'];
	protected int $imageMinWidth = 1;
	protected int $imageMinHeight = 1;
	protected int $imageMaxWidth = 7000;
	protected int $imageMaxHeight = 7000;
	protected ?int $imageMaxFileSize = 48 * 1024 * 1024;
	protected int $imageMinFileSize = 0;
	protected bool $ignoreUnknownImageTypes = false;

	public function __construct(array $options = [])
	{
		$optionNames = [
			'maxFileSize',
			'minFileSize',
			'imageMinWidth',
			'imageMinHeight',
			'imageMaxWidth',
			'imageMaxHeight',
			'imageMaxFileSize',
			'imageMinFileSize',
			'acceptOnlyImages',
			'acceptedFileTypes',
			'ignoredFileNames',
		];

		$globalSettings = static::getGlobalSettings();
		foreach ($optionNames as $optionName)
		{
			$setter = 'set' . ucfirst($optionName);
			if (array_key_exists($optionName, $options))
			{
				$optionValue = $options[$optionName];
				$this->$setter($optionValue);
			}
			else if (array_key_exists($optionName, $globalSettings))
			{
				$optionValue = $globalSettings[$optionName];
				if (is_string($optionValue) && preg_match('/FileSize/i', $optionName))
				{
					$optionValue = Ini::unformatInt($optionValue);
				}

				$this->$setter($optionValue);
			}
		}

		if (isset($options['ignoreUnknownImageTypes']) && is_bool($options['ignoreUnknownImageTypes']))
		{
			$this->setIgnoreUnknownImageTypes($options['ignoreUnknownImageTypes']);
		}
	}

	public static function getGlobalSettings(): array
	{
		$settings = [];
		$configuration = \Bitrix\Main\Config\Configuration::getValue('ui');
		if (isset($configuration['uploader']['settings']) && is_array($configuration['uploader']['settings']))
		{
			$settings = $configuration['uploader']['settings'];
		}

		return $settings;
	}

	public function getMaxFileSize(): ?int
	{
		return $this->maxFileSize;
	}

	public function setMaxFileSize(?int $maxFileSize): self
	{
		$this->maxFileSize = $maxFileSize;

		return $this;
	}

	public function getMinFileSize(): int
	{
		return $this->minFileSize;
	}

	public function setMinFileSize(int $minFileSize): self
	{
		$this->minFileSize = $minFileSize;

		return $this;
	}

	public function shouldAcceptOnlyImages(): bool
	{
		return $this->acceptOnlyImages;
	}

	public function getAcceptedFileTypes(): array
	{
		return $this->acceptedFileTypes;
	}

	public function setAcceptedFileTypes(array $acceptedFileTypes): self
	{
		$this->acceptedFileTypes = $acceptedFileTypes;

		return $this;
	}

	public function setAcceptOnlyImages(bool $flag = true): self
	{
		return $this->acceptOnlyImages($flag);
	}

	public function acceptOnlyImages(bool $flag = true): self
	{
		$imageExtensions = $flag ? static::getImageExtensions() : [];
		$this->acceptOnlyImages = $flag;
		$this->setAcceptedFileTypes($imageExtensions);

		return $this;
	}

	public static function getImageExtensions(): array
	{
		$imageExtensions = explode(',', \CFile::getImageExtensions());

		return array_map(function($extension) {
			return '.' . ltrim($extension);
		}, $imageExtensions);
	}

	public function getIgnoredFileNames(): array
	{
		return $this->ignoredFileNames;
	}

	public function setIgnoredFileNames(array $fileNames): self
	{
		$this->ignoredFileNames = [];
		foreach ($fileNames as $fileName)
		{
			if (is_string($fileName) && mb_strlen($fileName) > 0)
			{
				$this->ignoredFileNames[] = mb_strtolower($fileName);
			}
		}

		return $this;
	}

	public function getImageMinWidth(): int
	{
		return $this->imageMinWidth;
	}

	public function setImageMinWidth(int $imageMinWidth): self
	{
		$this->imageMinWidth = $imageMinWidth;

		return $this;
	}

	public function getImageMinHeight(): int
	{
		return $this->imageMinHeight;
	}

	public function setImageMinHeight(int $imageMinHeight): self
	{
		$this->imageMinHeight = $imageMinHeight;

		return $this;
	}

	public function getImageMaxWidth(): int
	{
		return $this->imageMaxWidth;
	}

	public function setImageMaxWidth(int $imageMaxWidth): self
	{
		$this->imageMaxWidth = $imageMaxWidth;

		return $this;
	}

	public function getImageMaxHeight(): int
	{
		return $this->imageMaxHeight;
	}

	public function setImageMaxHeight(int $imageMaxHeight): self
	{
		$this->imageMaxHeight = $imageMaxHeight;

		return $this;
	}

	public function getImageMaxFileSize(): ?int
	{
		return $this->imageMaxFileSize;
	}

	public function setImageMaxFileSize(?int $imageMaxFileSize): self
	{
		$this->imageMaxFileSize = $imageMaxFileSize;

		return $this;
	}

	public function getImageMinFileSize(): int
	{
		return $this->imageMinFileSize;
	}

	public function setImageMinFileSize(int $imageMinFileSize): self
	{
		$this->imageMinFileSize = $imageMinFileSize;

		return $this;
	}

	public function getIgnoreUnknownImageTypes(): bool
	{
		return $this->ignoreUnknownImageTypes;
	}

	public function setIgnoreUnknownImageTypes(bool $flag): self
	{
		$this->ignoreUnknownImageTypes = $flag;

		return $this;
	}
}
