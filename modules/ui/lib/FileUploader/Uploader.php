<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\File\Image;
use Bitrix\Main\File\Image\Rectangle;
use Bitrix\Main\ORM\Objectify\State;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Json;

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

			if (!$controller->canUpload())
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

			$result = $controller->onUploadStart($tempFile);
			if ($result !== null && !$result->isSuccess())
			{
				return $this->handleUploadError($uploadResult->addErrors($result->getErrors()), $controller);
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

			$uploadResult->setDone(true);

			$result = $controller->onUploadComplete($uploadResult->getTempFile());
			if ($result !== null && !$result->isSuccess())
			{
				return $this->handleUploadError($uploadResult->addErrors($result->getErrors()), $controller);
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
		[$guid, $signature] = explode('.', $token);
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

		return md5(serialize(
			array_merge(
				$params,
				[
					$controller->getName(),
					$options,
					$controller->getFingerprint(),
				]
			)
		));
	}

	public function load(array $ids): LoadResultCollection
	{
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
			// TODO
			// $canUpload = $this->getController()->canUpload();
			foreach ($tempFileIds as $tempFileId)
			{
				$loadResult = $this->loadTempFile($tempFileId);
				$results->add($loadResult);
			}
		}

		return $results;
	}

	public function remove(array $ids): RemoveResultCollection
	{
		$results = new RemoveResultCollection();
		[$bfileIds, $tempFileIds] = $this->splitIds($ids);

		$controller = $this->getController();
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
			$canUpload = $controller->canUpload();
			foreach ($tempFileIds as $tempFileId)
			{
				$removeResult = new RemoveResult($tempFileId);
				$results->add($removeResult);

				if (!$canUpload)
				{
					$removeResult->addError(new UploaderError(UploaderError::FILE_REMOVE_ACCESS_DENIED));
					continue;
				}

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

		$fileInfo = $this->getFileInfo($fileId);
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

		$fileInfo = $this->getFileInfo($tempFileId);
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

	public function getFileInfo($fileId): ?FileInfo
	{
		$fileInfo = is_int($fileId) ? FileInfo::getFileInfo($fileId) : FileInfo::getTempFileInfo($fileId);

		if ($fileInfo)
		{
			$fileInfo->setDownloadUrl($this->getFileActionUrl($fileInfo, 'download'));
			$fileInfo->setRemoveUrl($this->getFileActionUrl($fileInfo, 'remove'));

			if ($fileInfo->getWidth() && $fileInfo->getHeight())
			{
				$fileInfo->setPreviewUrl($this->getFileActionUrl($fileInfo, 'preview'));

				// Sync with \Bitrix\UI\Controller\FileUploader::previewAction
				$sourceRectangle = new Rectangle($fileInfo->getWidth(), $fileInfo->getHeight());
				$destinationRectangle = new Rectangle(300, 300);
				$needResize = $sourceRectangle->resize($destinationRectangle, Image::RESIZE_PROPORTIONAL);
				if ($needResize)
				{
					$fileInfo->setPreviewWidth($destinationRectangle->getWidth());
					$fileInfo->setPreviewHeight($destinationRectangle->getHeight());
				}
				else
				{
					$fileInfo->setPreviewWidth($sourceRectangle->getWidth());
					$fileInfo->setPreviewHeight($sourceRectangle->getHeight());
				}

			}
		}

		return $fileInfo;
	}

	private function getFileActionUrl(FileInfo $fileInfo, string $actionName): string
	{
		$controller = $this->getController();

		return (string)UrlManager::getInstance()->create(
			"ui.fileuploader.{$actionName}",
			[
				'controller' => $controller->getName(),
				'controllerOptions' => Json::encode($controller->getOptions()),
				'fileId' => $fileInfo->getId(),
			]
		);
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
