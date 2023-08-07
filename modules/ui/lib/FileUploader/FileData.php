<?php

namespace Bitrix\UI\FileUploader;

class FileData
{
	protected string $name;
	protected string $contentType = '';
	protected int $size = 0;
	protected int $width = 0;
	protected int $height = 0;

	public function __construct(string $name, string $contentType, int $size)
	{
		$this->name = $name;
		$this->contentType = $contentType;
		$this->size = $size;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getContentType(): string
	{
		return $this->contentType;
	}

	public function getSize(): int
	{
		return $this->size;
	}

	public function getWidth(): int
	{
		return $this->width;
	}

	public function setWidth(int $width): void
	{
		if ($width > 0)
		{
			$this->width = $width;
		}
	}

	public function getHeight(): int
	{
		return $this->height;
	}

	public function setHeight(int $height): void
	{
		if ($height > 0)
		{
			$this->height = $height;
		}
	}

	public function isImage(): bool
	{
		return \CFile::isImage($this->getName()) && $this->getWidth() > 0 && $this->getHeight() > 0;
	}
}
