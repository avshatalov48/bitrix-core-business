<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Loader;
use Bitrix\Main\Security;
use Bitrix\Main\IO;
use Bitrix\Main\Result;

final class TempFile extends EO_TempFile
{
	private ?\CCloudStorageBucket $bucket = null;

	public static function create(Chunk $chunk, UploaderController $controller): Result
	{
		$result = new Result();
		$file = $chunk->getFile();
		if (!$file->isExists())
		{
			return $result->addError(new UploaderError(UploaderError::CHUNK_NOT_FOUND));
		}

		if (mb_strpos($file->getPhysicalPath(), \CTempFile::getAbsoluteRoot()) !== 0)
		{
			// A chunk file could be saved in any folder.
			// Copy it to the temporary directory. We need to normalize the absolute path.
			$tempFilePath = self::generateLocalTempFile();
			if (!copy($chunk->getFile()->getPhysicalPath(), $tempFilePath))
			{
				return $result->addError(new UploaderError(UploaderError::CHUNK_COPY_FAILED));
			}

			$newFile = new IO\File($tempFilePath);
			if (!$newFile->isExists())
			{
				return $result->addError(new UploaderError(UploaderError::CHUNK_COPY_FAILED));
			}

			$chunk->setFile($newFile);
		}

		$tempFile = null;
		if ($chunk->isOnlyOne())
		{
			// Cloud and local files are processed by CFile::SaveFile.
			$tempFile = self::createTempFile($chunk, $controller);
		}
		else
		{
			// Multipart upload
			$bucket = self::findBucketForFile($chunk, $controller);
			if ($bucket)
			{
				// cloud file
				$tempFile = self::createTempFile($chunk, $controller, $bucket);
				$appendResult = $tempFile->appendToCloud($chunk);
				if (!$appendResult->isSuccess())
				{
					$chunk->getFile()->delete();
					$tempFile->delete();

					return $result->addErrors($appendResult->getErrors());
				}
			}
			else
			{
				// local file
				$localTempDir = self::generateLocalTempDir();
				if (!$file->rename($localTempDir))
				{
					return $result->addError(new UploaderError(UploaderError::FILE_MOVE_FAILED));
				}

				$tempFile = self::createTempFile($chunk, $controller);
			}
		}

		$result->setData(['tempFile' => $tempFile]);

		return $result;
	}

	protected static function createTempFile(Chunk $chunk, UploaderController $controller, $bucket = null): TempFile
	{
		$tempFile = new TempFile();
		$tempFile->setFilename($chunk->getName());
		$tempFile->setMimetype($chunk->getType());
		$tempFile->setSize($chunk->getFileSize());
		$tempFile->setReceivedSize($chunk->getSize());
		$tempFile->setWidth($chunk->getWidth());
		$tempFile->setHeight($chunk->getHeight());
		$tempFile->setModuleId($controller->getModuleId());
		$tempFile->setController($controller->getName());

		if ($bucket)
		{
			$path = self::generateCloudTempDir($bucket);
		}
		else
		{
			$path = $chunk->getFile()->getPhysicalPath();
			$tempRoot = \CTempFile::getAbsoluteRoot();
			$path = mb_substr($path, mb_strlen($tempRoot));
		}

		$tempFile->setPath($path);

		if ($bucket)
		{
			$tempFile->setCloud(true);
			$tempFile->setBucketId($bucket->ID);
		}

		$tempFile->save();

		return $tempFile;
	}

	public function append(Chunk $chunk): Result
	{
		$result = new Result();

		if ($chunk->getEndRange() < $this->getReceivedSize())
		{
			// We already have this part of the file
			return $result;
		}

		if ($this->getReceivedSize() !== $chunk->getStartRange())
		{
			return $result->addError(new UploaderError(UploaderError::INVALID_CHUNK_OFFSET));
		}

		if ($this->getReceivedSize() + $chunk->getSize() > $this->getSize())
		{
			return $result->addError(new UploaderError(UploaderError::CHUNK_TOO_BIG));
		}

		$result = $this->isCloud() ? $this->appendToCloud($chunk) : $this->appendToFile($chunk);
		if ($result->isSuccess())
		{
			$this->increaseReceivedSize($chunk->getSize());
		}

		// Remove a temporary chunk file immediately
		$chunk->getFile()->delete();

		return $result;
	}

	public function commit(CommitOptions $commitOptions): Result
	{
		$fileAbsolutePath = $this->getAbsolutePath();

		$fileId = \CFile::saveFile(
			[
				'name' => $this->getFilename(),
				'tmp_name' => $fileAbsolutePath,
				'type' => $this->getMimetype(),
				'MODULE_ID' => $commitOptions->getModuleId(),
				'width' => $this->getWidth(),
				'height' => $this->getHeight(),
				'size' => $this->getSize(),
			],
			$commitOptions->getSavePath(),
			$commitOptions->isForceRandom(),
			$commitOptions->isSkipExtension(),
			$commitOptions->getAddDirectory()
		);

		$result = new Result();
		if (!$fileId)
		{
			$this->delete();

			return $result->addError(new UploaderError(UploaderError::SAVE_FILE_FAILED));
		}

		$this->setFileId($fileId);
		$this->setUploaded(true);
		$this->save();
		$this->fillFile();

		$this->removeActualTempFile();

		return $result;
	}

	public function isCloud(): bool
	{
		return $this->getCloud() && $this->getBucketId() > 0;
	}

	public function makePersistent(): void
	{
		$this->customData->set('deleteBFile', false);
		$this->delete();
	}

	public function deleteContent($deleteBFile = true): void
	{
		$this->removeActualTempFile();
		if ($deleteBFile)
		{
			\CFile::delete($this->getFileId());
		}
	}

	private function removeActualTempFile(): bool
	{
		if ($this->getDeleted())
		{
			return true;
		}

		$success = false;
		if ($this->isCloud())
		{
			$bucket = $this->getBucket();
			if ($bucket)
			{
				$success = $bucket->deleteFile($this->getPath());
			}
		}
		else
		{
			$success = IO\File::deleteFile($this->getAbsolutePath());
		}

		if ($success)
		{
			$this->setDeleted(true);
			$this->save();
		}

		return $success;
	}

	private function getAbsoluteCloudPath(): ?string
	{
		$bucket = $this->getBucket();
		if (!$bucket)
		{
			return null;
		}

		return $bucket->getFileSRC($this->getPath());
	}

	private function getAbsoluteLocalPath(): string
	{
		return \CTempFile::getAbsoluteRoot() . $this->getPath();
	}

	private function getAbsolutePath(): ?string
	{
		if ($this->isCloud())
		{
			return $this->getAbsoluteCloudPath();
		}

		return $this->getAbsoluteLocalPath();
	}

	private function appendToFile(Chunk $chunk): Result
	{
		$result = new Result();
		$file = new IO\File($this->getAbsoluteLocalPath());

		if ($chunk->isFirst() || !$file->isExists())
		{
			return $result->addError(new UploaderError(UploaderError::FILE_APPEND_NOT_FOUND));
		}

		if ($chunk->getEndRange() < $file->getSize())
		{
			// We already have this part of the file
			return $result;
		}

		if (!$chunk->getFile()->isExists())
		{
			return $result->addError(new UploaderError(UploaderError::CHUNK_APPEND_NOT_FOUND));
		}

		if ($file->putContents($chunk->getFile()->getContents(), IO\File::APPEND) === false)
		{
			return $result->addError(new UploaderError(UploaderError::CHUNK_APPEND_FAILED));
		}

		return $result;
	}

	private function appendToCloud(Chunk $chunk): Result
	{
		$result = new Result();
		$bucket = $this->getBucket();
		if (!$bucket)
		{
			return $result->addError(new UploaderError(UploaderError::CLOUD_EMPTY_BUCKET));
		}

		$minUploadSize = $bucket->getService()->getMinUploadPartSize();
		if ($chunk->getSize() < $minUploadSize && !$chunk->isLast())
		{
			$postMaxSize = \CUtil::unformat(ini_get('post_max_size'));
			$uploadMaxFileSize = \CUtil::unformat(ini_get('upload_max_filesize'));

			return $result->addError(
				new UploaderError(
					UploaderError::CLOUD_INVALID_CHUNK_SIZE,
					[
						'chunkSize' => $chunk->getSize(),
						'minUploadSize' => $minUploadSize,
						'postMaxSize' => $postMaxSize,
						'uploadMaxFileSize' => $uploadMaxFileSize,
					]
				)
			);
		}

		$cloudUpload = new \CCloudStorageUpload($this->getPath());
		if (!$cloudUpload->isStarted() && !$cloudUpload->start($bucket->ID, $chunk->getFileSize(), $chunk->getType()))
		{
			return $result->addError(new UploaderError(UploaderError::CLOUD_START_UPLOAD_FAILED));
		}

		if ($cloudUpload->getPos() === doubleval($chunk->getEndRange() + 1))
		{
			// We already have this part of the file.
			if ($chunk->isLast() && !$cloudUpload->finish())
			{
				return $result->addError(new UploaderError(UploaderError::CLOUD_FINISH_UPLOAD_FAILED));
			}

			return $result;
		}

		$fileContent = $chunk->getFile()->isExists() ? $chunk->getFile()->getContents() : false;
		if ($fileContent === false)
		{
			return $result->addError(new UploaderError(UploaderError::CLOUD_GET_CONTENTS_FAILED));
		}

		$fails = 0;
		$success = false;
		while ($cloudUpload->hasRetries())
		{
			if ($cloudUpload->next($fileContent))
			{
				$success = true;
				break;
			}

			$fails++;
		}

		if (!$success)
		{
			// TODO: CCloudStorageUpload::CleanUp
			return $result->addError(new UploaderError(UploaderError::CLOUD_UPLOAD_FAILED, ['fails' => $fails]));
		}

		if ($chunk->isLast() && !$cloudUpload->finish())
		{
			// TODO: CCloudStorageUpload::CleanUp
			return $result->addError(new UploaderError(UploaderError::CLOUD_FINISH_UPLOAD_FAILED));
		}

		return $result;
	}

	private function increaseReceivedSize(int $bytes): void
	{
		$receivedSize = $this->getReceivedSize();
		$this->setReceivedSize($receivedSize + $bytes);
		$this->save();
	}

	private static function findBucketForFile(Chunk $chunk, UploaderController $controller): ?\CCloudStorageBucket
	{
		if (!Loader::includeModule('clouds'))
		{
			return null;
		}

		$bucket = \CCloudStorage::findBucketForFile(
			[
				'FILE_SIZE' => $chunk->getFileSize(),
				'MODULE_ID' => $controller->getCommitOptions()->getModuleId(),
			],
			$chunk->getName()
		);

		if (!$bucket || !$bucket->init())
		{
			return null;
		}

		return $bucket;
	}

	public static function generateLocalTempDir(int $hoursToKeepFile = 12): string
	{
		$directory = \CTempFile::getDirectoryName(
			$hoursToKeepFile,
			[
				'file-uploader',
				Security\Random::getString(32),
			]
		);

		if (!IO\Directory::isDirectoryExists($directory))
		{
			IO\Directory::createDirectory($directory);
		}

		$tempName = md5(mt_rand() . mt_rand());

		return $directory . $tempName;
	}

	public static function generateLocalTempFile(): string
	{
		$tmpFilePath = \CTempFile::getFileName('file-uploader' . uniqid(md5(mt_rand() . mt_rand()), true));
		$directory = IO\Path::getDirectory($tmpFilePath);
		if (!IO\Directory::isDirectoryExists($directory))
		{
			IO\Directory::createDirectory($directory);
		}

		return $tmpFilePath;
	}

	public static function generateCloudTempDir(\CCloudStorageBucket $bucket, int $hoursToKeepFile = 12): string
	{
		$directory = \CCloudTempFile::getDirectoryName(
			$bucket,
			$hoursToKeepFile,
			[
				'file-uploader',
				Security\Random::getString(32),
			]
		);

		$tempName = md5(mt_rand() . mt_rand());

		return $directory . $tempName;
	}

	private function getBucket(): ?\CCloudStorageBucket
	{
		if ($this->bucket !== null)
		{
			return $this->bucket;
		}

		if (!$this->getBucketId() || !Loader::includeModule('clouds'))
		{
			return null;
		}

		$bucket = new \CCloudStorageBucket($this->getBucketId());
		if ($bucket->init())
		{
			$this->bucket = $bucket;
		}

		return $this->bucket;
	}
}
