<?php

namespace Bitrix\UI\FileUploader;

class UploadRequest
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
		$this->width = $width;
	}

	public function getHeight(): int
	{
		return $this->height;
	}

	public function setHeight(int $height): void
	{
		$this->height = $height;
	}
}