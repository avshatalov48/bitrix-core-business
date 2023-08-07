<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\File\Image;

class PreviewImageOptions
{
	protected int $width = 300;
	protected int $height = 300;
	protected int $mode = Image::RESIZE_PROPORTIONAL;

	public function __construct(array $options = [])
	{
		$optionNames = [
			'width',
			'height',
			'mode',
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

	public function getWidth(): int
	{
		return $this->width;
	}

	public function setWidth(int $width): self
	{
		$this->width = $width;

		return $this;
	}

	public function getHeight(): int
	{
		return $this->height;
	}

	public function setHeight(int $height): self
	{
		$this->height = $height;

		return $this;
	}

	public function getMode(): int
	{
		return $this->mode;
	}

	public function setMode(int $mode): self
	{
		$this->mode = $mode;

		return $this;
	}
}