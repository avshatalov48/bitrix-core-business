<?php

namespace Bitrix\Main\FileUploader;

use Bitrix\Main\Application;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Main\UserField\File\UploadedFilesRegistry;
use Bitrix\UI\FileUploader\Configuration;
use Bitrix\UI\FileUploader\FileOwnershipCollection;
use Bitrix\UI\FileUploader\UploaderController;
use Bitrix\UI\FileUploader\UploadResult;
use CUser;
use Bitrix\Main\UserField\File\UploaderFileSigner;

class FieldFileUploaderController extends UploaderController
{
	protected function isAuthorized(): bool
	{
		$currentUser = (isset($USER) && $USER instanceof CUser) ? $USER : new CUser();

		return $currentUser->IsAuthorized();
	}

	public function __construct(array $options)
	{
		$options = [
			'id' => (isset($options['id']) && $options['id'] > 0) ? (int)$options['id'] : 0,
			'cid' => (
			(
				isset($options['cid'])
				&& is_string($options['cid'])
				&& preg_match('/^[a-f01-9]{32}$/', $options['cid'])
			)
				? $options['cid']
				: ''
			),
			'entityId' => (isset($options['entityId']) && is_string($options['entityId'])) ? $options['entityId'] : '',
			'fieldName' =>
				(isset($options['fieldName']) && is_string($options['fieldName']))
					? $options['fieldName']
					: ''
			,
			'multiple' => (isset($options['multiple']) && is_bool($options['multiple']) && $options['multiple']),
			'signedFileId' => (isset($options['signedFileId']) && is_string($options['signedFileId']))
				? $options['signedFileId']
				: ''
		];

		parent::__construct($options);
	}

	public function isAvailable(): bool
	{
		return $this->isAuthorized();
	}

	protected function parseFileExtensions(array $extensionsSetting): array
	{
		$result = [];
		foreach($extensionsSetting as $key => $extension)
		{
			if($extension === true)
			{
				$extension = trim((string)$key);
			}
			else
			{
				$extension = trim((string)$extension);
			}
			$dotPos = mb_strrpos($extension, '.');
			if ($dotPos !== false)
			{
				$extension = mb_substr($extension, $dotPos + 1);
			}
			if($extension !== '')
			{
				$result[".$extension"] = true;
			}
		}
		if (!empty($result))
		{
			$result = array_keys($result);
		}

		return $result;
	}

	public function getConfiguration(): Configuration
	{
		/** @global \CUserTypeManager $USER_FIELD_MANAGER */
		global $USER_FIELD_MANAGER;

		$configuration = new Configuration();

		$isSetMaxAllowedSize = false;
		$fieldSettings = null;
		$fieldName = $this->getOption('fieldName', '');
		$fieldInfo =
			$USER_FIELD_MANAGER->GetUserFields(
				$this->getOption('entityId', ''),
				0,
				LANGUAGE_ID,
				false,
				[$this->getOption('fieldName', '')]
			)
		;
		if (
			isset($fieldInfo[$fieldName])
			&& is_array($fieldInfo[$fieldName])
			&& isset($fieldInfo[$fieldName]['SETTINGS'])
			&& is_array($fieldInfo[$fieldName]['SETTINGS'])
		)
		{
			$fieldSettings = $fieldInfo[$fieldName]['SETTINGS'];
			if (
				isset($fieldSettings['MAX_ALLOWED_SIZE'])
				&& $fieldSettings['MAX_ALLOWED_SIZE'] > 0
			)
			{
				$configuration->setMaxFileSize((int)$fieldSettings['MAX_ALLOWED_SIZE']);
				$isSetMaxAllowedSize = true;
			}
			if (
				isset($fieldSettings['EXTENSIONS'])
				&& is_array($fieldSettings['EXTENSIONS'])
				&& !empty($fieldSettings['EXTENSIONS'])
			)
			{
				$fileExtensions = $this->parseFileExtensions($fieldSettings['EXTENSIONS']);
				if (!empty($fileExtensions))
				{
					$configuration->setAcceptedFileTypes($fileExtensions);
				}
			}
		}

		if (!$isSetMaxAllowedSize)
		{
			$configuration->setMaxFileSize(null);
		}

		$configuration->setTreatOversizeImageAsFile(true);

		return $configuration;
	}

	public function canUpload()
	{
		$cid = $this->getOptions()['cid'] ?? null;

		return $cid && FileInputUtility::instance()->isCidRegistered($cid);
	}

	protected function isFileInputUtilityAccessible(): bool
	{
		return FileInputUtility::instance()->isAccessible();
	}

	protected function getControlId(): string
	{
		$options = $this->getOptions();
		$userField = [
			'ID' => $options['id'] ?? 0,
			'ENTITY_ID' => $options['entityId'] ?? '',
			'FIELD_NAME' => $options['fieldName'] ?? '',
			'MULTIPLE' => $options['multiple'] ? 'Y' : 'N',
		];

		return FileInputUtility::instance()->getUserFieldCid($userField);
	}

	protected function registerControl(string $controlId): string
	{
		return FileInputUtility::instance()->registerControl($this->getOption('cid', ''), $controlId);
	}

	protected function registerFile(string $cid, int $fileId)
	{
		FileInputUtility::instance()->registerFile($cid, $fileId);
	}

	protected function registerUploaderFile(int $fileId, string $tempFileToken): void
	{
		if ($this->isFileInputUtilityAccessible())
		{
			$controlId = $this->getControlId();
			$cid = $this->registerControl($controlId);

			if ($fileId > 0)
			{
				$this->registerFile($cid, $fileId);
				$this->registerTemporaryFileData($fileId, $controlId, $cid, $tempFileToken);
			}
		}
	}

	protected function checkFiles(array $fileIds): array
	{
		return FileInputUtility::instance()->checkFiles($this->getControlId(), $fileIds);
	}

	public function canView(): bool
	{
		return true;
	}

	public function verifyFileOwner(FileOwnershipCollection $files): void
	{
		$options = $this->getOptions();

		if ($options['signedFileId']) // view mode
		{
			foreach ($files as $file)
			{
				$fileSigner = (new UploaderFileSigner($options['entityId'], $options['fieldName']));

				if ($fileSigner->verify($options['signedFileId'], $file->getId()))
				{
					$file->markAsOwn();
				}
			}
		}

		if ($options['cid']) // edit mode
		{
			foreach ($files as $file)
			{
				$fileIds[] = $file->getId();
			}

			$fileIds = $this->checkFiles($fileIds);

			foreach ($files as $file)
			{
				if (in_array($file->getId(), $fileIds, true))
				{
					$file->markAsOwn();
				}
			}
		}
	}

	public function canRemove(): bool
	{
		return true;
	}

	public function onUploadComplete(UploadResult $uploadResult): void
	{
		$session = Application::getInstance()->getSession();
		$session->save();
		$session->start();

		$fileInfo = $uploadResult->getFileInfo();
		$fileId = $fileInfo->getFileId();
		$downloadUrl = $fileInfo->getPreviewUrl();
		if (is_string($downloadUrl) && $downloadUrl !== '')
		{
			$fileInfo->setDownloadUrl('');
		}
		$previewUrl = $fileInfo->getPreviewUrl();
		if (is_string($previewUrl) && $previewUrl !== '')
		{
			$fileInfo->setPreviewUrl('', 0, 0);
		}
		$this->registerUploaderFile($fileId, $uploadResult->getToken());
		$fileInfo->setCustomData(['realFileId' => $fileId]);
	}

	private function registerTemporaryFileData(int $fileId, string $controlId, string $cid, string $tempFileToken): void
	{
		UploadedFilesRegistry::getInstance()->registerFile($fileId, $controlId, $cid, $tempFileToken);
	}
}
