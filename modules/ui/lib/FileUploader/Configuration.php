<?php

namespace Bitrix\UI\FileUploader;

class Configuration
{
	protected ?int $maxFileSize = null;
	protected ?int $minFileSize = null;
	protected array $acceptedFileTypes = [];
	protected int $imageMinWidth = 1;
	protected int $imageMinHeight = 1;
	protected int $imageMaxWidth = 10000;
	protected int $imageMaxHeight = 10000;
	protected ?int $imageMaxFileSize = null;
	protected ?int $imageMinFileSize = null;
	protected bool $ignoreUnknownImageTypes = false;

	public function __construct(array $options = [])
	{
		if (isset($options['acceptOnlyImages']) && $options['acceptOnlyImages'] === true)
		{
			$this->acceptOnlyImages();
		}

		$optionNames = [
			'maxFileSize',
			'minFileSize',
			'acceptedFileTypes',
			'imageMinWidth',
			'imageMinHeight',
			'imageMaxWidth',
			'imageMaxHeight',
			'imageMaxFileSize',
			'imageMinFileSize',
			'ignoreUnknownImageTypes',
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

	public function getMaxFileSize(): ?int
	{
		return $this->maxFileSize;
	}

	public function setMaxFileSize(?int $maxFileSize): self
	{
		$this->maxFileSize = $maxFileSize;

		return $this;
	}

	public function getMinFileSize(): ?int
	{
		return $this->minFileSize;
	}

	public function setMinFileSize(?int $minFileSize): self
	{
		$this->minFileSize = $minFileSize;

		return $this;
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

	public function acceptOnlyImages(): self
	{
		$imageExtensions = static::getImageExtensions();
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

	public function getImageMinFileSize(): ?int
	{
		return $this->imageMinFileSize;
	}

	public function setImageMinFileSize(?int $imageMinFileSize): self
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
