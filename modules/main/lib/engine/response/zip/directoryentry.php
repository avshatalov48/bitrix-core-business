<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\Response\Zip;

final class DirectoryEntry implements EntryInterface
{
	private const DIRECTORY_PATH = '@directory';
	private string $path;

	private function __construct(string $path)
	{
		$this->path = $path;
	}

	public static function createEmptyDirectory(string $path): self
	{
		return new self($path);
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getSize(): int
	{
		return 0;
	}

	public function getServerRelativeUrl(): string
	{
		return self::DIRECTORY_PATH;
	}

	public function getCrc32(): string
	{
		return '0';
	}
}