<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Error;
use Bitrix\Main\File;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\IO;
use Bitrix\Main\Result;
use Bitrix\Main\Text\Encoding;

class Chunk
{
	protected int $size = 0;
	protected ?int $fileSize = null;
	protected ?int $startRange = null;
	protected ?int $endRange = null;
	protected string $type = '';
	protected string $name = '';
	protected int $width = 0;
	protected int $height = 0;

	protected IO\File $file;

	protected function __construct(IO\File $file)
	{
		$this->setFile($file);
	}

	public static function createFromRequest(HttpRequest $request): Result
	{
		$result = new Result();

		$fileMimeType = (string)$request->getHeader('Content-Type');
		if (!preg_match('~\w+/[-+.\w]+~', $fileMimeType))
		{
			return $result->addError(new UploaderError(UploaderError::INVALID_CONTENT_TYPE));
		}

		$contentLength = $request->getHeader('Content-Length');
		if ($contentLength === null)
		{
			return $result->addError(new UploaderError(UploaderError::INVALID_CONTENT_LENGTH));
		}

		$contentLength = (int)$contentLength;
		$filename = static::normalizeFilename((string)$request->getHeader('X-Upload-Content-Name'));
		if (empty($filename))
		{
			return $result->addError(new UploaderError(UploaderError::INVALID_CONTENT_NAME));
		}

		if (!static::isValidFilename($filename))
		{
			return $result->addError(new UploaderError(UploaderError::INVALID_FILENAME));
		}

		$contentRangeResult = static::getContentRange($request);
		if (!$contentRangeResult->isSuccess())
		{
			return $result->addErrors($contentRangeResult->getErrors());
		}

		$file = static::getFileFromHttpInput();
		$contentRange = $contentRangeResult->getData();
		$rangeChunkSize = empty($contentRange) ? 0 : ($contentRange['endRange'] - $contentRange['startRange'] + 1);

		if ($rangeChunkSize && $contentLength !== $rangeChunkSize)
		{
			return $result->addError(new UploaderError(
				UploaderError::INVALID_RANGE_SIZE,
				[
					'rangeChunkSize' => $rangeChunkSize,
					'contentLength' => $contentLength,
				]
			));
		}

		$chunk = new Chunk($file);
		if ($chunk->getSize() !== $contentLength)
		{
			return $result->addError(new UploaderError(
				UploaderError::INVALID_CHUNK_SIZE,
				[
					'chunkSize' => $chunk->getSize(),
					'contentLength' => $contentLength,
				]
			));
		}

		$chunk->setName($filename);
		$chunk->setType($fileMimeType);

		if (!empty($contentRange))
		{
			$chunk->setStartRange($contentRange['startRange']);
			$chunk->setEndRange($contentRange['endRange']);
			$chunk->setFileSize($contentRange['fileSize']);
		}

		$result->setData(['chunk' => $chunk]);

		return $result;
	}

	private static function getFileFromHttpInput(): IO\File
	{
		// This file will be automatically removed on shutdown
		$tmpFilePath = TempFile::generateLocalTempFile();
		$file = new IO\File($tmpFilePath);
		$file->putContents(HttpRequest::getInput());

		return $file;
	}

	private static function getContentRange(HttpRequest $request): Result
	{
		$contentRange = $request->getHeader('Content-Range');
		if ($contentRange === null)
		{
			return new Result();
		}

		$result = new Result();
		if (!preg_match('/(\d+)-(\d+)\/(\d+)$/', $contentRange, $match))
		{
			return $result->addError(new UploaderError(UploaderError::INVALID_CONTENT_RANGE));
		}

		[$startRange, $endRange, $fileSize] = [(int)$match[1], (int)$match[2], (int)$match[3]];

		if ($startRange > $endRange)
		{
			return $result->addError(new UploaderError(UploaderError::INVALID_CONTENT_RANGE));
		}

		if ($fileSize <= $endRange)
		{
			return $result->addError(new UploaderError(UploaderError::INVALID_CONTENT_RANGE));
		}

		$result->setData([
			'startRange' => $startRange,
			'endRange' => $endRange,
			'fileSize' => $fileSize,
		]);

		return $result;
	}

	private static function normalizeFilename(string $filename): string
	{
		$filename = urldecode($filename);
		$filename = Encoding::convertEncodingToCurrent($filename);

		return \getFileName($filename);
	}

	private static function isValidFilename(string $filename): bool
	{
		if (mb_strlen($filename) > 255)
		{
			return false;
		}

		if (mb_strpos($filename, '\0') !== false)
		{
			return false;
		}

		return true;
	}

	public function getFile(): IO\File
	{
		return $this->file;
	}

	/**
	 * @internal
	 */
	public function setFile(IO\File $file): void
	{
		$this->file = $file;
		$this->size = $file->getSize();
	}

	public function getSize(): int
	{
		return $this->size;
	}

	public function getFileSize(): int
	{
		return $this->fileSize ?? $this->size;
	}

	protected function setFileSize(int $fileSize): void
	{
		$this->fileSize = $fileSize;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): void
	{
		$this->type = $type;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
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

	public function getStartRange(): ?int
	{
		return $this->startRange;
	}

	protected function setStartRange(int $startRange): void
	{
		$this->startRange = $startRange;
	}

	public function getEndRange(): ?int
	{
		return $this->endRange;
	}

	protected function setEndRange(int $endRange): void
	{
		$this->endRange = $endRange;
	}

	public function isFirst(): bool
	{
		return $this->startRange === null || $this->startRange === 0;
	}

	public function isLast(): bool
	{
		return $this->endRange === null || ($this->endRange + 1) === $this->fileSize;
	}

	public function isOnlyOne(): bool
	{
		return (
			$this->startRange === null
			|| ($this->startRange === 0 && ($this->endRange - $this->startRange + 1) === $this->fileSize)
		);
	}

	public function validate(Configuration $config): Result
	{
		$result = new Result();

		if (in_array(mb_strtolower($this->getName()), $config->getIgnoredFileNames()))
		{
			return $result->addError(new UploaderError(UploaderError::FILE_NAME_NOT_ALLOWED));
		}

		if ($config->getMaxFileSize() !== null && $this->getFileSize() > $config->getMaxFileSize())
		{
			return $result->addError(
				new UploaderError(
					UploaderError::MAX_FILE_SIZE_EXCEEDED,
					[
						'maxFileSize' => \CFile::formatSize($config->getMaxFileSize()),
						'maxFileSizeInBytes' => $config->getMaxFileSize(),
					]
				)
			);
		}

		if ($this->getFileSize() < $config->getMinFileSize())
		{
			return $result->addError(
				new UploaderError(
					UploaderError::MIN_FILE_SIZE_EXCEEDED,
					[
						'minFileSize' => \CFile::formatSize($config->getMinFileSize()),
						'minFileSizeInBytes' => $config->getMinFileSize(),
					]
				)
			);
		}

		if (!$this->validateFileType($config->getAcceptedFileTypes()))
		{
			return $result->addError(new UploaderError(UploaderError::FILE_TYPE_NOT_ALLOWED));
		}

		$width = 0;
		$height = 0;
		if (\CFile::isImage($this->getName(), $this->getType()))
		{
			$image = new File\Image($this->getFile()->getPhysicalPath());
			$imageInfo = $image->getInfo(false);
			if (!$imageInfo)
			{
				if ($config->getIgnoreUnknownImageTypes())
				{
					$result->setData(['width' => $width, 'height' => $height]);

					return $result;
				}
				else
				{
					return $result->addError(new UploaderError(UploaderError::IMAGE_TYPE_NOT_SUPPORTED));
				}
			}

			$width = $imageInfo->getWidth();
			$height = $imageInfo->getHeight();
			if ($imageInfo->getFormat() === File\Image::FORMAT_JPEG)
			{
				$exifData = $image->getExifData();
				if (isset($exifData['Orientation']) && $exifData['Orientation'] >= 5 && $exifData['Orientation'] <= 8)
				{
					[$width, $height] = [$height, $width];
				}
			}

			if (!$config->shouldTreatOversizeImageAsFile())
			{
				$imageData = new FileData($this->getName(), $this->getType(), $this->getSize());
				$imageData->setWidth($width);
				$imageData->setHeight($height);

				$validationResult = $config->validateImage($imageData);
				if (!$validationResult->isSuccess())
				{
					return $result->addErrors($validationResult->getErrors());
				}
			}
		}

		$result->setData(['width' => $width, 'height' => $height]);

		return $result;
	}

	private function validateFileType(array $fileTypes): bool
	{
		if (count($fileTypes) === 0)
		{
			return true;
		}

		$mimeType = $this->getType();
		$baseMimeType = preg_replace('/\/.*$/', '', $mimeType);

		foreach ($fileTypes as $type)
		{
			if (!is_string($type) || mb_strlen($type) === 0)
			{
				continue;
			}

			$type = mb_strtolower(trim($type));
			if ($type[0] === '.') // extension case
			{
				$filename = mb_strtolower($this->getName());
				$offset = mb_strlen($filename) - mb_strlen($type);
				if (mb_strpos($filename, $type, $offset) !== false)
				{
					return true;
				}
			}
			elseif (preg_match('/\/\*$/', $type)) // image/* mime type case
			{
				if ($baseMimeType === preg_replace('/\/.*$/', '', $type))
				{
					return true;
				}
			}
			elseif ($mimeType === $type)
			{
				return true;
			}
		}

		return false;
	}
}
