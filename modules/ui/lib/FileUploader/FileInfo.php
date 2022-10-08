<?php

namespace Bitrix\UI\FileUploader;

class FileInfo implements \JsonSerializable
{
	protected $id;
	protected string $contentType;
	protected string $filename;
	protected int $fileSize;
	protected int $fileId = 0;
	protected ?string $originalName = null;
	protected int $width = 0;
	protected int $height = 0;
	protected ?string $downloadUrl = null;
	protected ?string $removeUrl = null;
	protected ?string $previewUrl = null;
	protected int $previewWidth = 0;
	protected int $previewHeight = 0;

	/**
	 * @param {string | int} $id
	 * @param string $filename
	 * @param string $contentType
	 * @param int $fileSize
	 */
	public function __construct($id, string $filename, string $contentType, int $fileSize)
	{
		$this->id = $id;
		$this->contentType = $contentType;
		$this->filename = $filename;
		$this->fileSize = $fileSize;
	}

	public static function getFileInfo(int $id): ?FileInfo
	{
		$file = \CFile::getFileArray($id);
		if (!is_array($file))
		{
			return null;
		}

		$fileInfo = new static($id, $file['FILE_NAME'], $file['CONTENT_TYPE'], (int)$file['FILE_SIZE']);
		$fileInfo->setOriginalName($file['ORIGINAL_NAME']);
		$fileInfo->setWidth($file['WIDTH']);
		$fileInfo->setHeight($file['HEIGHT']);
		$fileInfo->setFileId($id);

		return $fileInfo;
	}

	public static function getTempFileInfo(string $tempFileId): ?FileInfo
	{
		[$guid, $signature] = explode('.', $tempFileId);

		$tempFile = TempFileTable::getList([
			'filter' => [
				'=GUID' => $guid,
				'=UPLOADED' => true,
			],
		])->fetchObject();

		if (!$tempFile)
		{
			return null;
		}

		$fileInfo = new static($tempFileId, $tempFile->getFilename(), $tempFile->getMimetype(), $tempFile->getSize());
		$fileInfo->setOriginalName($tempFile->getFilename());
		$fileInfo->setWidth($tempFile->getWidth());
		$fileInfo->setHeight($tempFile->getHeight());
		$fileInfo->setFileId($tempFile->getFileId());

		return $fileInfo;
	}

	/**
	 * @return int|string
	 */
	public function getId()
	{
		return $this->id;
	}

	public function getFileId(): int
	{
		return $this->fileId;
	}

	public function setFileId(int $fileId): void
	{
		$this->fileId = $fileId;
	}

	public function getContentType(): string
	{
		return $this->contentType;
	}

	public function getFilename(): string
	{
		return $this->filename;
	}

	public function getFileSize(): int
	{
		return $this->fileSize;
	}

	public function getOriginalName(): ?string
	{
		return $this->originalName;
	}

	public function setOriginalName(string $originalName): void
	{
		$this->originalName = $originalName;
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

	public function getDownloadUrl(): ?string
	{
		return $this->downloadUrl;
	}

	public function setDownloadUrl(string $downloadUrl): void
	{
		$this->downloadUrl = $downloadUrl;
	}

	public function getRemoveUrl(): ?string
	{
		return $this->removeUrl;
	}

	public function setRemoveUrl(string $removeUrl): void
	{
		$this->removeUrl = $removeUrl;
	}

	public function getPreviewUrl(): ?string
	{
		return $this->previewUrl;
	}

	public function setPreviewUrl(string $previewUrl): void
	{
		$this->previewUrl = $previewUrl;
	}

	public function getPreviewWidth(): int
	{
		return $this->previewWidth;
	}

	public function setPreviewWidth(int $previewWidth): void
	{
		$this->previewWidth = $previewWidth;
	}

	public function getPreviewHeight(): int
	{
		return $this->previewHeight;
	}

	public function setPreviewHeight(int $previewHeight): void
	{
		$this->previewHeight = $previewHeight;
	}

	public function jsonSerialize(): array
	{
		return [
			'serverId' => $this->getId(),
			'type' => $this->getContentType(),
			'name' => $this->getFilename(),
			'originalName' => $this->getOriginalName(),
			'size' => $this->getFileSize(),
			'width' => $this->getWidth(),
			'height' => $this->getHeight(),
			'downloadUrl' => $this->getDownloadUrl(),
			'removeUrl' => $this->getRemoveUrl(),
			'serverPreviewUrl' => $this->getPreviewUrl(),
			'serverPreviewWidth' => $this->getPreviewWidth(),
			'serverPreviewHeight' => $this->getPreviewHeight(),
		];
	}
}
