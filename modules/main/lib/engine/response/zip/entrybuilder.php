<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\Response\Zip;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Uri;

final class EntryBuilder
{
	public function __construct()
	{
	}

	public function createEmptyDirectory(string $path): DirectoryEntry
	{
		return DirectoryEntry::createEmptyDirectory($path);
	}

	public function createFromFileArray(array $fileArray, string $path = null): FileEntry
	{
		$isFromCloud = $this->isFromCloud($fileArray);
		$path = $this->getPath($fileArray, $path);
		$size = $this->getSize($fileArray);
		$fileSrc = $this->getFileSrc($fileArray);

		if ($isFromCloud)
		{
			$fileSrc = $this->prepareCloudUrl($fileSrc);
			$serverRelativeUrl = $this->getCloudUploadPath() . $fileSrc;
		}
		else
		{
			$serverRelativeUrl = $fileSrc;
		}

		$serverRelativeUrl = Uri::urnEncode($serverRelativeUrl, 'UTF-8');

		return new FileEntry($path, $serverRelativeUrl, $size);
	}

	public function createFromFileId($fileId, string $path = null): ?FileEntry
	{
		$fileArray = \CFile::getFileArray($fileId);
		if ($this->isValidFileArray($fileArray) === false)
		{
			return null;
		}

		return $this->createFromFileArray($fileArray, $path);
	}

	private function isValidFileArray($fileArray): bool
	{
		return \is_array($fileArray) && !empty($fileArray['SRC']);
	}

	private function isFromCloud(array $fileArray): bool
	{
		return !empty($fileArray['HANDLER_ID']);
	}

	private function getPath(array $fileArray, $path): string
	{
		return $path ? : $fileArray['ORIGINAL_NAME'];
	}

	private function getSize(array $fileArray): int
	{
		return (int)$fileArray['FILE_SIZE'];
	}

	private function getFileSrc(array $fileArray): ?string
	{
		$fileSrc = $fileArray['SRC'] ?? null;

		return $fileSrc ? : \CFile::getFileSrc($fileArray);
	}

	private function prepareCloudUrl(string $fileSrc): string
	{
		return preg_replace('~^(http[s]?)(\://)~i', '\\1.', $fileSrc);
	}

	private function getCloudUploadPath(): string
	{
		return Option::get('main', 'bx_cloud_upload', '/upload/bx_cloud_upload/');
	}
}