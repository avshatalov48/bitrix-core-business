<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\Response\Zip;

class FileEntry implements EntryInterface
{
	private string $path;
	private string $serverRelativeUrl;
	private int $size;
	private string $crc32;

	public function __construct(string $path, string $serverRelativeUrl, int $size, string $crc32 = '-')
	{
		$this->path = $path;
		$this->serverRelativeUrl = $serverRelativeUrl;
		$this->size = $size;
		$this->crc32 = $crc32;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getSize(): int
	{
		return $this->size;
	}

	public function getServerRelativeUrl(): string
	{
		return $this->serverRelativeUrl;
	}

	public function getCrc32(): string
	{
		return $this->crc32;
	}
}