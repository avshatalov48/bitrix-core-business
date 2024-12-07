<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\ORM\Objectify\State;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\UI\FileUploader\Contracts\CustomFingerprint;
use Bitrix\UI\FileUploader\Contracts\CustomLoad;
use Bitrix\UI\FileUploader\Contracts\CustomRemove;

class Uploader
{
	protected UploaderController $controller;

	public function __construct(UploaderController $controller)
	{
		$this->controller = $controller;
	}

	public function getController(): UploaderController
	{
		return $this->controller;
	}

	public function upload(Chunk $chunk, string $token = null): UploadResult
	{
		$controller = $this->getController();
		$uploadResult = new UploadResult();
		if ($chunk->isFirst())
		{
			// Common file validation (uses in CFile::SaveFile)
			$commitOptions = $controller->getCommitOptions();
			$error = \CFile::checkFile(
				[
					'name' => $chunk->getName(),
					'size' => $chunk->getFileSize(),
					'type' => $chunk->getType()
				],
				0,
				false,
				false,
				$commitOptions->isForceRandom(),
				$commitOptions->isSkipExtension()
			);

			if ($error !== '')
			{
				return $this->handleUploadError(
					$uploadResult->addError(new UploaderError('CHECK_FILE_FAILED', $error)),
					$controller
				);
			}

			// Controller Validation
			$validationResult = $chunk->validate($controller->getConfiguration());
			if (!$validationResult->isSuccess())
			{
				return $this->handleUploadError($uploadResult->addErrors($validationResult->getErrors()), $controller);
			}

			['width' => $width, 'height' => $height] = $validationResult->getData();
			$chunk->setWidth((int)$width);
			$chunk->setHeight((int)$height);

			$uploadRequest = new UploadRequest($chunk->getName(), $chunk->getType(), $chunk->getSize());
			$uploadRequest->setWidth($chunk->getWidth());
			$uploadRequest->setHeight($chunk->getHeight());

			// Temporary call for compatibility
			// $canUploadResult = $controller->canUpload($uploadRequest);
			$canUploadResult = call_user_func([$controller, 'canUpload'], $uploadRequest);
			if (($canUploadResult instanceof CanUploadResult) && !$canUploadResult->isSuccess())
			{
				return $this->handleUploadError($uploadResult->addErrors($canUploadResult->getErrors()), $controller);
			}
			else if (!is_bool($canUploadResult) || $canUploadResult === false)
			{
				return $this->handleUploadError(
					$uploadResult->addError(new UploaderError(UploaderError::FILE_UPLOAD_ACCESS_DENIED)),
					$controller
				);
			}

			$createResult = TempFile::create($chunk, $controller);
			if (!$createResult->isSuccess())
			{
				return $this->handleUploadError($uploadResult->addErrors($createResult->getErrors()), $controller);
			}

			/** @var TempFile $tempFile */
			$tempFile = $createResult->getData()['tempFile'];
			$uploadResult->setTempFile($tempFile);
			$uploadResult->setToken($this->generateToken($tempFile));

			$controller->onUploadStart($uploadResult);
			if (!$uploadResult->isSuccess())
			{
				return $this->handleUploadError($uploadResult, $controller);
			}
		}
		else
		{
			if (empty($token))
			{
				return $this->handleUploadError(
					$uploadResult->addError(new UploaderError(UploaderError::EMPTY_TOKEN)),
					$controller
				);
			}

			$guid = $this->getGuidFromToken($token);
			if (!$guid)
			{
				return $this->handleUploadError(
					$uploadResult->addError(new UploaderError(UploaderError::INVALID_SIGNATURE)),
					$controller
				);
			}

			$tempFile = TempFileTable::getList([
				'filter' => [
					'=GUID' => $guid,
					'=UPLOADED' => false,
				],
			])->fetchObject();

			if (!$tempFile)
			{
				return $this->handleUploadError(
					$uploadResult->addError(new UploaderError(UploaderError::UNKNOWN_TOKEN)),
					$controller
				);
			}

			$uploadResult->setTempFile($tempFile);
			$uploadResult->setToken($token);

			$appendResult = $tempFile->append($chunk);
			if (!$appendResult->isSuccess())
			{
				return $this->handleUploadError($uploadResult->addErrors($appendResult->getErrors()), $controller);
			}
		}

		if ($uploadResult->isSuccess() && $chunk->isLast())
		{
			$commitResult = $tempFile->commit($controller->getCommitOptions());
			if (!$commitResult->isSuccess())
			{
				return $this->handleUploadError($uploadResult->addErrors($commitResult->getErrors()), $controller);
			}

			$fileInfo = $this->createFileInfo($uploadResult->getToken());
			$uploadResult->setFileInfo($fileInfo);
			$uploadResult->setDone(true);

			$controller->onUploadComplete($uploadResult);
			if (!$uploadResult->isSuccess())
			{
				return $this->handleUploadError($uploadResult, $controller);
			}
		}

		return $uploadResult;
	}

	private function handleUploadError(UploadResult $uploadResult, UploaderController $controller): UploadResult
	{
		$controller->onUploadError($uploadResult);

		if (!$uploadResult->isSuccess())
		{
			$tempFile = $uploadResult->getTempFile();
			if ($tempFile !== null && $tempFile->state !== State::DELETED)
			{
				$tempFile->delete();
			}
		}

		return $uploadResult;
	}

	public function generateToken(TempFile $tempFile): string
	{
		$guid = $tempFile->getGuid();
		$salt = $this->getTokenSalt([$guid]);
		$signer = new Signer();

		return $signer->sign($guid, $salt);
	}

	private function getGuidFromToken(string $token): ?string
	{
		$parts = explode('.', $token, 2);
		if (count($parts) !== 2)
		{
			return null;
		}

		[$guid, $signature] = $parts;
		if (empty($guid) || empty($signature))
		{
			return null;
		}

		$salt = $this->getTokenSalt([$guid]);
		$signer = new Signer();

		if (!$signer->validate($guid, $signature, $salt))
		{
			return null;
		}

		return $guid;
	}

	private function getTokenSalt($params = []): string
	{
		$controller = $this->getController();
		$options = $controller->getOptions();
		ksort($options);

		$fingerprint =
			$controller instanceof CustomFingerprint
				? $controller->getFingerprint()
				: (string)\bitrix_sessid()
		;

		return md5(serialize(
			array_merge(
				$params,
				[
					$controller->getName(),
					$options,
					$fingerprint,
				]
			)
		));
	}

	public function load(array $ids): LoadResultCollection
	{
		$controller = $this->getController();
		if ($controller instanceof CustomLoad)
		{
			return $controller->load($ids);
		}

		$results = new LoadResultCollection();
		[$bfileIds, $tempFileIds] = $this->splitIds($ids);
		$fileOwnerships = new FileOwnershipCollection($bfileIds);

		// Files from b_file
		if ($fileOwnerships->count() > 0)
		{
			$controller = $this->getController();
			if ($controller->canView())
			{
				$controller->verifyFileOwner($fileOwnerships);
			}

			foreach ($fileOwnerships as $fileOwnership)
			{
				if ($fileOwnership->isOwn())
				{
					$loadResult = $this->loadFile($fileOwnership->getId());
				}
				else
				{
					$loadResult = new LoadResult($fileOwnership->getId());
					$loadResult->addError(new UploaderError(UploaderError::FILE_LOAD_ACCESS_DENIED));
				}

				$results->add($loadResult);
			}
		}

		// Temp Files
		if (count($tempFileIds) > 0)
		{
			foreach ($tempFileIds as $tempFileId)
			{
				$loadResult = $this->loadTempFile($tempFileId);
				$results->add($loadResult);
			}
		}

		return $results;
	}

	public function getFileInfo(array $ids): array
	{
		$result = [];
		$loadResults = $this->load(array_unique($ids));
		foreach ($loadResults as $loadResult)
		{
			if ($loadResult->isSuccess() && $loadResult->getFile() !== null)
			{
				$result[] = $loadResult->getFile()->jsonSerialize();
			}
		}

		return $result;
	}

	public function remove(array $ids): RemoveResultCollection
	{
		$controller = $this->getController();
		if ($controller instanceof CustomRemove)
		{
			return $controller->remove($ids);
		}

		$results = new RemoveResultCollection();
		[$bfileIds, $tempFileIds] = $this->splitIds($ids);

		// Files from b_file
		if (count($bfileIds) > 0)
		{
			$fileOwnerships = new FileOwnershipCollection($bfileIds);
			if ($controller->canRemove())
			{
				$controller->verifyFileOwner($fileOwnerships);
			}

			foreach ($fileOwnerships as $fileOwnership)
			{
				$removeResult = new RemoveResult($fileOwnership->getId());
				if ($fileOwnership->isOwn())
				{
					// TODO:  remove file
				}
				else
				{
					$removeResult->addError(new UploaderError(UploaderError::FILE_REMOVE_ACCESS_DENIED));
				}

				$results->add($removeResult);
			}
		}

		// Temp Files
		if (count($tempFileIds) > 0)
		{
			foreach ($tempFileIds as $tempFileId)
			{
				$removeResult = new RemoveResult($tempFileId);
				$results->add($removeResult);

				$guid = $this->getGuidFromToken($tempFileId);
				if (!$guid)
				{
					$removeResult->addError(new UploaderError(UploaderError::INVALID_SIGNATURE));
					continue;
				}

				$tempFile = TempFileTable::getList([
					'filter' => [
						'=GUID' => $guid,
					],
				])->fetchObject();

				if ($tempFile)
				{
					$tempFile->delete();
				}
			}
		}

		return $results;
	}

	public function getPendingFiles(array $tempFileIds): PendingFileCollection
	{
		$pendingFiles = new PendingFileCollection();
		foreach ($tempFileIds as $tempFileId)
		{
			if (!is_string($tempFileId) || empty($tempFileId))
			{
				continue;
			}

			$pendingFile = new PendingFile($tempFileId);
			$pendingFiles->add($pendingFile);

			$guid = $this->getGuidFromToken($tempFileId);
			if (!$guid)
			{
				$pendingFile->addError(new UploaderError(UploaderError::INVALID_SIGNATURE));

				continue;
			}

			$tempFile = TempFileTable::getList([
				'filter' => [
					'=GUID' => $guid,
					'=UPLOADED' => true,
				],
			])->fetchObject();

			if (!$tempFile)
			{
				$pendingFile->addError(new UploaderError(UploaderError::UNKNOWN_TOKEN));

				continue;
			}

			$pendingFile->setTempFile($tempFile);
		}

		return $pendingFiles;
	}

	private function loadFile(int $fileId): LoadResult
	{
		$result = new LoadResult($fileId);
		if ($fileId < 1)
		{
			return $result->addError(new UploaderError(UploaderError::FILE_LOAD_FAILED));
		}

		$fileInfo = $this->createFileInfo($fileId);
		if ($fileInfo)
		{
			$result->setFile($fileInfo);
		}
		else
		{
			return $result->addError(new UploaderError(UploaderError::FILE_LOAD_FAILED));
		}

		return $result;
	}

	private function loadTempFile(string $tempFileId): LoadResult
	{
		$result = new LoadResult($tempFileId);
		$guid = $this->getGuidFromToken($tempFileId);
		if (!$guid)
		{
			return $result->addError(new UploaderError(UploaderError::INVALID_SIGNATURE));
		}

		$tempFile = TempFileTable::getList([
			'filter' => [
				'=GUID' => $guid,
				'=UPLOADED' => true,
			],
		])->fetchObject();

		if (!$tempFile)
		{
			return $result->addError(new UploaderError(UploaderError::UNKNOWN_TOKEN));
		}

		$fileInfo = $this->createFileInfo($tempFileId);
		if ($fileInfo)
		{
			$result->setFile($fileInfo);
		}
		else
		{
			return $result->addError(new UploaderError(UploaderError::FILE_LOAD_FAILED));
		}

		return $result;
	}

	private function createFileInfo($fileId): ?FileInfo
	{
		$fileInfo = is_int($fileId) ? FileInfo::createFromBFile($fileId) : FileInfo::createFromTempFile($fileId);
		if ($fileInfo)
		{
			$downloadUrl = (string)UrlManager::getDownloadUrl($this->getController(), $fileInfo);
			$fileInfo->setDownloadUrl($downloadUrl);
			if ($fileInfo->isImage())
			{
				$config = $this->getController()->getConfiguration();
				if ($config->shouldTreatOversizeImageAsFile())
				{
					$treatImageAsFile = $config->shouldTreatImageAsFile($fileInfo);
					$fileInfo->setTreatImageAsFile($treatImageAsFile);
				}

				if (!$fileInfo->shouldTreatImageAsFile())
				{
					$rectangle = PreviewImage::getSize($fileInfo);
					$previewUrl = (string)UrlManager::getPreviewUrl($this->getController(), $fileInfo);
					$fileInfo->setPreviewUrl($previewUrl, $rectangle->getWidth(), $rectangle->getHeight());
				}
			}
		}

		return $fileInfo;
	}

	private function splitIds(array $ids): array
	{
		$fileIds = [];
		$tempFileIds = [];
		foreach ($ids as $id)
		{
			if (is_numeric($id))
			{
				$fileIds[] = (int)$id;
			}
			else
			{
				$tempFileIds[] = (string)$id;
			}
		}

		return [$fileIds, $tempFileIds];
	}
}
