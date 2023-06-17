<?php

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Disk\Document\OnlyOffice\Templates\CreateDocumentByCallTemplateScenario;
use Bitrix\Disk\File;
use Bitrix\Disk\Driver;
use Bitrix\Disk\Folder;
use Bitrix\Disk\Storage;
use Bitrix\Disk\TypeFile;
use Bitrix\Disk\Ui\FileAttributes;
use Bitrix\Im\Model\FileTemporaryTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Localization\Loc;

class FileItem implements RestEntity, PopupDataAggregatable
{
	use ContextCustomer;

	public const MEDIA_SUBTYPE = 'MEDIA';
	public const AUDIO_SUBTYPE = 'AUDIO';
	public const BRIEF_SUBTYPE = 'BRIEF';
	public const OTHER_SUBTYPE = 'OTHER';
	public const DOCUMENT_SUBTYPE = 'DOCUMENT';

	public const ALLOWED_SUBTYPE = [
		self::MEDIA_SUBTYPE,
		self::AUDIO_SUBTYPE,
		self::BRIEF_SUBTYPE,
		self::OTHER_SUBTYPE,
		self::DOCUMENT_SUBTYPE,
	];

	public const BRIEF_CODE = 'resume';

	protected string $subtype = self::OTHER_SUBTYPE;
	protected ?int $chatId = null;
	protected ?int $diskFileId = null;
	protected ?File $diskFile = null;

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

		$this->setSubtype(static::getFileSubtype($this->diskFile));

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

	public static function isSubtypeValid(string $subtype): bool
	{
		return in_array($subtype, static::ALLOWED_SUBTYPE, true);
	}

	public static function getSubtypeFromJsonFormat(string $subtypeInJsonFormat): string
	{
		return mb_strtoupper($subtypeInJsonFormat);
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

	public function setSubtype(string $subtype): self
	{
		$this->subtype = $subtype;
		return $this;
	}

	public function getSubtype(): string
	{
		return $this->subtype;
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

	public function addToTmp(string $source): Result
	{
		$addResult = FileTemporaryTable::add(['DISK_FILE_ID' => $this->getId(), 'SOURCE' => $source]);

		if (!$addResult->isSuccess())
		{
			return (new Result())->addErrors($addResult->getErrors());
		}

		return new Result();
	}

	public static function getFileSubtype(File $diskFile): string
	{
		$realFile = $diskFile->getRealObject() ?? $diskFile;

		if ($realFile->getCode() === static::BRIEF_CODE)
		{
			return static::BRIEF_SUBTYPE;
		}

		$diskFileType = $diskFile->getTypeFile();

		return static::getFileSubtypeByDiskFileType($diskFileType);
	}

	protected static function getFileSubtypeByDiskFileType(string $diskFileType): string
	{
		switch ($diskFileType)
		{
			case TypeFile::IMAGE:
			case TypeFile::VIDEO:
				return static::MEDIA_SUBTYPE;

			case TypeFile::DOCUMENT:
			case TypeFile::PDF:
				return static::DOCUMENT_SUBTYPE;

			case TypeFile::AUDIO:
				return static::AUDIO_SUBTYPE;

			default:
				return static::OTHER_SUBTYPE;
		}
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
			$viewerType = FileAttributes::buildByFileId($diskFile->getFileId(), $this->getDownloadLink())
				->setObjectId($diskFile->getId())
				->setGroupBy($this->getChatId() ?? $diskFile->getParentId())
				->setAttribute('data-im-chat-id', $this->getChatId())
				->setTitle($diskFile->getName())
				->addAction([
					'type' => 'download',
				])
				->addAction([
					'type' => 'copyToMe',
					'text' => Loc::getMessage('IM_DISK_ACTION_SAVE_TO_OWN_FILES'),
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