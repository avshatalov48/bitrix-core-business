<?php

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Disk\Document\OnlyOffice\Templates\CreateDocumentByCallTemplateScenario;
use Bitrix\Disk\File;
use Bitrix\Disk\Driver;
use Bitrix\Disk\Folder;
use Bitrix\Disk\Storage;
use Bitrix\Disk\TypeFile;
use Bitrix\Disk\Ui\FileAttributes;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Localization\Loc;

class FileItem implements RestEntity, PopupDataAggregatable
{
	use ContextCustomer;

	protected ?int $chatId = null;
	protected ?int $diskFileId = null;
	protected ?File $diskFile = null;
	protected ?string $contentType = null;

	/**
	 * @param int|File $diskFile
	 * @param int|null $chatId
	 */
	public function __construct($diskFile, ?int $chatId = null)
	{
		if ($diskFile instanceof File)
		{
			$this->setDiskFile($diskFile);
		}
		elseif (is_numeric($diskFile))
		{
			$this->diskFileId = (int)$diskFile;
		}
		if ($chatId)
		{
			$this->setChatId($chatId);
		}
	}

	public static function getRestEntityName(): string
	{
		return 'file';
	}

	public static function initByDiskFileId(int $diskFileId, ?int $chatId = null): ?self
	{
		$diskFile = File::getById($diskFileId);

		if ($diskFile === null)
		{
			return null;
		}

		return new static($diskFile, $chatId);
	}

	public function setDiskFile(File $diskFile): self
	{
		$this->diskFile = $diskFile;
		$this->diskFileId = $diskFile->getId();

		return $this;
	}

	public function getDiskFile(): File
	{
		if (!$this->diskFile instanceof File)
		{
			$this->diskFile = File::getById($this->diskFileId);
		}

		return $this->diskFile;
	}

	public function getDiskFileId(): int
	{
		if ($this->diskFileId)
		{
			return $this->diskFileId;
		}

		return $this->getDiskFile()->getId();
	}

	public function getChatId(): ?int
	{
		return $this->chatId;
	}

	public function setChatId(?int $chatId): self
	{
		$this->chatId = $chatId;
		return $this;
	}

	public function getCopy(?Storage $storage = null): ?self
	{
		$userId = $this->getContext()->getUserId();
		$storage = $storage ?? Driver::getInstance()->getStorageByUserId($userId);

		if ($storage === null)
		{
			return null;
		}

		$folder = $storage->getFolderForUploadedFiles();

		if ($folder === null)
		{
			return null;
		}

		return new static($this->getDiskFile()->copyTo($folder, $userId, true), $this->getChatId());
	}

	public function getSymLink(): ?self
	{
		$folderModel = \CIMDisk::GetFolderModel($this->chatId);
		if (!($folderModel instanceof Folder))
		{
			return null;
		}

		$newFileLink = $folderModel->addFileLink(
			$this->getDiskFile(),
			[
				'CREATED_BY' => $this->getContext()->getUserId(),
				'GLOBAL_CONTENT_VERSION' => 1
			],
			[],
			true
		);

		if (!isset($newFileLink))
		{
			return null;
		}

		return new static($newFileLink, $this->chatId);
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return new PopupData([new UserPopupItem([$this->getDiskFile()->getCreatedBy()])], $excludedList);
	}

	public function toRestFormat(array $option = []): array
	{
		$diskFile = $this->getDiskFile();
		return [
			'id' => (int)$diskFile->getId(),
			'chatId' => (int)$this->getChatId(),
			'date' => $diskFile->getCreateTime()->format('c'),
			'type' => $this->getContentType(),
			'name' => $diskFile->getName(),
			'extension' => mb_strtolower($diskFile->getExtension()),
			'size' => (int)$diskFile->getSize(),
			'image' => $this->getPreviewSizes() ?? false,
			'status' => $diskFile->getGlobalContentVersion() > 1? 'done': 'upload',
			'progress' => $diskFile->getGlobalContentVersion() > 1? 100: -1,
			'authorId' => (int)$diskFile->getCreatedBy(),
			'authorName' => \Bitrix\Im\User::formatFullNameFromDatabase($diskFile->getCreateUser()),
			'urlPreview' => $this->getPreviewLink(),
			'urlShow' => $this->getShowLink(),
			'urlDownload' => $this->getDownloadLink(),
			'viewerAttrs' => $this->getViewerAttributes(),
		];
	}

	/**
	 * Method for getting file type like in old api
	 * @see \CIMDisk::GetFileParams
	 * @return string
	 */
	public function getContentType(): string
	{
		if (isset($this->contentType))
		{
			return $this->contentType;
		}

		if ($this->getDiskFile()->getCode() === \Bitrix\Im\V2\Link\File\FileItem::MEDIA_ORIGINAL_CODE)
		{
			return 'file';
		}

		$diskTypeFile = $this->getDiskFile()->getTypeFile();

		switch ($diskTypeFile)
		{
			case TypeFile::IMAGE:
				return 'image';
			case TypeFile::VIDEO:
				return 'video';
			case TypeFile::AUDIO:
				return 'audio';
			default:
				return 'file';
		}
	}

	public function setContentType(string $contentType): self
	{
		$this->contentType = $contentType;

		return $this;
	}

	private function getPreviewSizes(): ?array
	{
		$previewParameters = [];
		$diskFile = $this->getDiskFile();

		if (TypeFile::isImage($diskFile->getName()))
		{
			$previewParameters = $diskFile->getFile();
		}
		if (TypeFile::isVideo($diskFile->getName()))
		{
			$previewParameters = $diskFile->getView()->getPreviewData();
		}

		if (empty($previewParameters))
		{
			return null;
		}

		return [
			'height' => (int)$previewParameters['HEIGHT'],
			'width' => (int)$previewParameters['WIDTH'],
		];
	}

	private function getPreviewLink(): string
	{
		$urlManager = UrlManager::getInstance();
		$diskFile = $this->getDiskFile();

		if ($diskFile->getView()->getPreviewData())
		{
			$linkType = 'disk.api.file.showPreview';
			$fileName = 'preview.jpg';
		}
		elseif (TypeFile::isImage($diskFile->getName()))
		{
			$linkType = 'disk.api.file.showImage';
			$fileName = $diskFile->getName();
		}
		else
		{
			return '';
		}

		return \Bitrix\Im\Common::getPublicDomain() . $urlManager->create($linkType, [
			'humanRE' => 1,
			'width' => 640,
			'height' => 640,
			'signature' => \Bitrix\Disk\Security\ParameterSigner::getImageSignature($diskFile->getId(), 640, 640),
			'fileId' => $diskFile->getId(),
			'fileName' => $fileName
		])->getUri();
	}

	private function getShowLink(): string
	{
		$urlManager = UrlManager::getInstance();
		$diskFile = $this->getDiskFile();

		if (TypeFile::isImage($diskFile->getName()))
		{
			$linkType = 'disk.api.file.showImage';
		}
		else
		{
			$linkType = 'disk.api.file.download';
		}

		return \Bitrix\Im\Common::getPublicDomain() . $urlManager->create($linkType, [
			'humanRE' => 1,
			'fileId' => $diskFile->getId(),
			'fileName' => $diskFile->getName()
		])->getUri();
	}

	private function getDownloadLink(): string
	{
		$urlManager = UrlManager::getInstance();
		$diskFile = $this->getDiskFile();

		return \Bitrix\Im\Common::getPublicDomain() . $urlManager->create('disk.api.file.download', [
			'humanRE' => 1,
			'fileId' => $diskFile->getId(),
			'fileName' => $diskFile->getName()
		])->getUri();
	}

	private function getViewerAttributes(): ?array
	{
		$diskFile = $this->getDiskFile();
		try
		{
			$viewerType = FileAttributes::buildByFileData($diskFile->getFile() ?? [], $this->getDownloadLink())
				->setObjectId($diskFile->getId())
				->setGroupBy($this->getChatId() ?? $diskFile->getParentId())
				->setAttribute('data-im-chat-id', $this->getChatId())
				->setTitle($diskFile->getName())
				->addAction([
					'type' => 'download',
				])
				->addAction([
					'type' => 'copyToMe',
					'text' => Loc::getMessage('IM_FILE_ITEM_ACTION_SAVE_TO_OWN_FILES'),
					'action' => 'BXIM.disk.saveToDiskAction',
					'params' => [
						'fileId' => $diskFile->getId(),
					],
					'extension' => 'disk.viewer.actions',
					'buttonIconClass' => 'ui-btn-icon-cloud',
				])
			;

			if ($viewerType->getTypeClass() === FileAttributes::JS_TYPE_CLASS_ONLYOFFICE)
			{
				$viewerType->setTypeClass('BX.Messenger.Integration.Viewer.OnlyOfficeChatItem');
				if (
					$diskFile->getCode() === CreateDocumentByCallTemplateScenario::CODE_RESUME
					|| $diskFile->getRealObject()->getCode() === CreateDocumentByCallTemplateScenario::CODE_RESUME
				)
				{
					$viewerType->setTypeClass('BX.Messenger.Integration.Viewer.OnlyOfficeResumeItem');
				}

				$viewerType->setExtension('im.integration.viewer');
			}
			if ($viewerType->getViewerType() !== \Bitrix\Main\UI\Viewer\Renderer\Renderer::JS_TYPE_UNKNOWN)
			{
				return $viewerType->toDataSet();
			}
		}
		catch (\Bitrix\Main\ArgumentException $exception)
		{
			return null;
		}

		return null;
	}

	public function getId(): int
	{
		return $this->getDiskFileId();
	}
}