<?php

namespace Bitrix\UI\FileUploader;

class PendingFileCollection implements \IteratorAggregate
{
	/**
	 * @var array<string, PendingFile>
	 */
	private array $files = [];

	public function add(PendingFile $pendingFile): void
	{
		$this->files[$pendingFile->getId()] = $pendingFile;
	}

	public function get(string $tempFileId): ?PendingFile
	{
		return $this->files[$tempFileId] ?? null;
	}

	public function getByFileId(int $fileId): ?PendingFile
	{
		foreach ($this->files as $file)
		{
			if ($file->getFileId() === $fileId)
			{
				return $file;
			}
		}

		return null;
	}

	/**
	 * @return int[]
	 */
	public function getFileIds(): array
	{
		$ids = [];
		foreach ($this->files as $file)
		{
			$id = $file->getFileId();
			if ($id !== null)
			{
				$ids[] = $id;
			}
		}

		return $ids;
	}

	public function makePersistent(): void
	{
		foreach ($this->files as $file)
		{
			$file->makePersistent();
		}
	}

	public function remove(): void
	{
		foreach ($this->files as $file)
		{
			$file->remove();
		}
	}

	/**
	 * @return array<string, PendingFile>
	 */
	public function getAll(): array
	{
		return $this->files;
	}

	/**
	 * @return \ArrayIterator|array<string, PendingFile>
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->files);
	}
}