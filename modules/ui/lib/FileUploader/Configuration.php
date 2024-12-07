<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Config\Ini;
use Bitrix\Main\Result;

class Configuration
{
	protected ?int $maxFileSize = 256 * 1024 * 1024;
	protected int $minFileSize = 0;
	protected bool $acceptOnlyImages = false;
	protected array $acceptedFileTypes = [];
	protected array $ignoredFileNames = ['.ds_store', 'thumbs.db', 'desktop.ini'];
	protected int $imageMinWidth = 1;
	protected int $imageMinHeight = 1;
	protected int $imageMaxWidth = 7000;
	protected int $imageMaxHeight = 7000;
	protected ?int $imageMaxFileSize = 48 * 1024 * 1024;
	protected int $imageMinFileSize = 0;
	protected bool $treatOversizeImageAsFile = false;
	protected bool $ignoreUnknownImageTypes = false;

	public function __construct(array $options = [])
	{
		$optionNames = [
			'maxFileSize',
			'minFileSize',
			'imageMinWidth',
			'imageMinHeight',
			'imageMaxWidth',
			'imageMaxHeight',
			'imageMaxFileSize',
			'imageMinFileSize',
			'acceptOnlyImages',
			'acceptedFileTypes',
			'ignoredFileNames',
		];

		$globalSettings = static::getGlobalSettings();
		foreach ($optionNames as $optionName)
		{
			$setter = 'set' . ucfirst($optionName);
			if (array_key_exists($optionName, $options))
			{
				$optionValue = $options[$optionName];
				$this->$setter($optionValue);
			}
			else if (array_key_exists($optionName, $globalSettings))
			{
				$optionValue = $globalSettings[$optionName];
				if (is_string($optionValue) && preg_match('/FileSize/i', $optionName))
				{
					$optionValue = Ini::unformatInt($optionValue);
				}

				$this->$setter($optionValue);
			}
		}

		if (isset($options['ignoreUnknownImageTypes']) && is_bool($options['ignoreUnknownImageTypes']))
		{
			$this->setIgnoreUnknownImageTypes($options['ignoreUnknownImageTypes']);
		}

		if (isset($options['treatOversizeImageAsFile']) && is_bool($options['treatOversizeImageAsFile']))
		{
			$this->setTreatOversizeImageAsFile($options['treatOversizeImageAsFile']);
		}
	}

	public static function getGlobalSettings(): array
	{
		$settings = [];
		$configuration = \Bitrix\Main\Config\Configuration::getValue('ui');
		if (isset($configuration['uploader']['settings']) && is_array($configuration['uploader']['settings']))
		{
			$settings = $configuration['uploader']['settings'];
		}

		return $settings;
	}

	public function shouldTreatImageAsFile(FileData | array $fileData): bool
	{
		if (!$this->shouldTreatOversizeImageAsFile())
		{
			return false;
		}

		if (!$fileData->isImage())
		{
			return true;
		}

		$result = $this->validateImage($fileData);

		return !$result->isSuccess();
	}

	public function validateImage(FileData $fileData): Result
	{
		$result = new Result();

		if (($fileData->getWidth() === 0 || $fileData->getHeight() === 0) && !$this->getIgnoreUnknownImageTypes())
		{
			return $result->addError(new UploaderError(UploaderError::IMAGE_TYPE_NOT_SUPPORTED));
		}

		if ($this->getImageMaxFileSize() !== null && $fileData->getSize() > $this->getImageMaxFileSize())
		{
			return $result->addError(
				new UploaderError(
					UploaderError::IMAGE_MAX_FILE_SIZE_EXCEEDED,
					[
						'imageMaxFileSize' => \CFile::formatSize($this->getImageMaxFileSize()),
						'imageMaxFileSizeInBytes' => $this->getImageMaxFileSize(),
					]
				)
			);
		}

		if ($fileData->getSize() < $this->getImageMinFileSize())
		{
			return $result->addError(
				new UploaderError(
					UploaderError::IMAGE_MIN_FILE_SIZE_EXCEEDED,
					[
						'imageMinFileSize' => \CFile::formatSize($this->getImageMinFileSize()),
						'imageMinFileSizeInBytes' => $this->getImageMinFileSize(),
					]
				)
			);
		}

		if ($fileData->getWidth() < $this->getImageMinWidth() || $fileData->getHeight() < $this->getImageMinHeight())
		{
			return $result->addError(
				new UploaderError(
					UploaderError::IMAGE_IS_TOO_SMALL,
					[
						'minWidth' => $this->getImageMinWidth(),
						'minHeight' => $this->getImageMinHeight(),
					]
				)
			);
		}

		if ($fileData->getWidth() > $this->getImageMaxWidth() || $fileData->getHeight() > $this->getImageMaxHeight())
		{
			return $result->addError(
				new UploaderError(
					UploaderError::IMAGE_IS_TOO_BIG,
					[
						'maxWidth' => $this->getImageMaxWidth(),
						'maxHeight' => $this->getImageMaxHeight(),
					]
				)
			);
		}

		return $result;
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

	public function getMinFileSize(): int
	{
		return $this->minFileSize;
	}

	public function setMinFileSize(int $minFileSize): self
	{
		$this->minFileSize = $minFileSize;

		return $this;
	}

	public function shouldAcceptOnlyImages(): bool
	{
		return $this->acceptOnlyImages;
	}

	public function getAcceptedFileTypes(): array
	{
		return $this->acceptedFileTypes;
	}

	public function setAcceptedFileTypes(array $acceptedFileTypes): self
	{
		$this->acceptedFileTypes = $acceptedFileTypes;
		$this->acceptOnlyImages = false;

		return $this;
	}

	public function setAcceptOnlyImages(bool $flag = true): self
	{
		$this->acceptOnlyImages = $flag;

		if ($flag)
		{
			$this->acceptOnlyImages();
		}

		return $this;
	}

	public function acceptOnlyImages(): self
	{
		$imageExtensions = static::getImageExtensions();
		$this->setAcceptedFileTypes($imageExtensions);
		$this->acceptOnlyImages = true;

		return $this;
	}

	public static function getImageExtensions(bool $withDot = true): array
	{
		$imageExtensions = explode(',', \CFile::getImageExtensions());

		return array_map(function($extension) use($withDot) {
			return ($withDot ? '.' : '') . trim($extension);
		}, $imageExtensions);
	}

	public static function getVideoExtensions(bool $withDot = true): array
	{
		$extensions = [
			'avi',
			'wmv',
			'mp4',
			'mov',
			'webm',
			'flv',
			'm4v',
			'mkv',
			'vob',
			'3gp',
			'ogv',
			'h264',
		];

		if ($withDot)
		{
			return array_map(function($extension) {
				return '.' . $extension;
			}, $extensions);
		}

		return $extensions;
	}

	public function getIgnoredFileNames(): array
	{
		return $this->ignoredFileNames;
	}

	public function setIgnoredFileNames(array $fileNames): self
	{
		$this->ignoredFileNames = [];
		foreach ($fileNames as $fileName)
		{
			if (is_string($fileName) && mb_strlen($fileName) > 0)
			{
				$this->ignoredFileNames[] = mb_strtolower($fileName);
			}
		}

		return $this;
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

	public function getImageMinFileSize(): int
	{
		return $this->imageMinFileSize;
	}

	public function setImageMinFileSize(int $imageMinFileSize): self
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

	public function shouldTreatOversizeImageAsFile(): bool
	{
		return $this->treatOversizeImageAsFile;
	}

	public function setTreatOversizeImageAsFile(bool $flag): self
	{
		$this->treatOversizeImageAsFile = $flag;

		return $this;
	}
}
