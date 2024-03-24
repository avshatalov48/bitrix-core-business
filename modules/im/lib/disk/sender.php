<?php
namespace Bitrix\Im\Disk;

use Bitrix\Disk\File;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CPullOptions;

Loc::loadMessages(__FILE__);

class Sender
{
	public const SOURCE_DEFAULT = 'default';
	public const SOURCE_CALL_RECORDING = 'call-recording';
	public const SOURCE_CALL_DOCUMENT = 'call-document';

	/** @var ErrorCollection */
	private $errorCollection;
	/** @var integer */
	private $chat;
	/** @var File */
	private $file;
	/** @var integer|null */
	private $userId;
	/** @var string */
	private $text;
	/** @var array */
	private $params;
	/** @var string */
	private $fileSource;

	/**
	 * @param File $file
	 * @param int $chatId
	 * @param int $messageInterval
	 * @return bool
	 */
	public static function hasFileInLastMessages(File $file, int $chatId, int $messageInterval = 10): bool
	{
		$result = \Bitrix\Im\Model\MessageTable::getList([
			'select' => [
				'ID',
				'MESSAGE_FILE_ID' => 'FILE.PARAM_VALUE',
			],
			'filter' => [
				'=CHAT_ID' => $chatId
			],
			'runtime' => [
				new \Bitrix\Main\Entity\ReferenceField(
					'FILE',
					'\Bitrix\Im\Model\MessageParamTable',
					[
						"=ref.MESSAGE_ID" => "this.ID",
						"=ref.PARAM_NAME" => new \Bitrix\Main\DB\SqlExpression("?s", "FILE_ID")
					],
					["join_type" => "LEFT"]
				),
			],
			'order' => ['ID' => 'DESC'],
			'limit' => $messageInterval,
		]);
		while ($row = $result->fetch())
		{
			if ($row['MESSAGE_FILE_ID'] == $file->getId())
			{
				return false;
			}
		}

		return true;
	}
	/**
	 * Upload file to chat storage and send message with it
	 *
	 * @param File $file
	 * @param int $chatId
	 * @param string $text
	 * @param array $params
	 * @param null $userId
	 * @param string $fileSource
	 * @return Result
	 */
	public static function sendFileToChat(
		File $file,
		int $chatId,
		string $text,
		$params = [],
		$userId = null,
		$fileSource = self::SOURCE_DEFAULT
	): Result
	{
		$result = new Result();
		$sender = new self();

		$initResult = $sender->init($file, $chatId, $text, $params, $userId, $fileSource);
		if (!$initResult)
		{
			return $result->addErrors($sender->errorCollection->getValues());
		}

		$accessResult = $sender->checkAccess();
		if (!$accessResult)
		{
			return $result->addErrors($sender->errorCollection->getValues());
		}

		$uploadResult = $sender->uploadFileToChatStorage();
		if (!$uploadResult)
		{
			return $result->addErrors($sender->errorCollection->getValues());
		}
		$result->setData(['IM_FILE' => $uploadResult]);

		$sender->sendEvent();

		return $result;
	}

	/**
	 * Send message with file from chat storage to chat
	 *
	 * @param File $file
	 * @param int $chatId
	 * @param string $text
	 * @param array $params
	 * @param int $userId
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function sendExistingFileToChat(
		File $file,
		int $chatId,
		string $text,
		$params = [],
		int $userId
	): Result
	{
		$result = new Result();

		$chat = ChatTable::getByPrimary($chatId, [
			'select' => ['TYPE']
		])->fetch();
		if (!$chat)
		{
			return $result->addError(new Error("Getting chat error"));
		}

		$attach = new \CIMMessageParamAttach(null, \CIMMessageParamAttach::CHAT);
		$attach->AddMessage($text);
		$addResult = \CIMMessenger::Add([
			"TO_CHAT_ID" => $chatId,
			"FROM_USER_ID" => $userId,
			"FILES" => [(int)$file->getId()],
			"MESSAGE_TYPE" => $chat['TYPE'],
			"ATTACH" => $attach,
			"PARAMS" => $params
		]);

		if (!$addResult)
		{
			return $result->addError(new Error("Adding message error"));
		}

		return $result;
	}

	private function __construct()
	{
		$this->errorCollection = new ErrorCollection();
	}

	private function init($file, $chatId, $text, $params, $userId, $fileSource): bool
	{
		$this->file = $file;
		if ($chatId <= 0 || $this->file->getId() <= 0)
		{
			$this->errorCollection[] = new Error("Wrong CHAT_ID or FILE_ID");

			return false;
		}

		$chat = ChatTable::getByPrimary($chatId, [
			'select' => ['TITLE', 'ENTITY_TYPE', 'ENTITY_ID']
		])->fetch();
		if (!$chat)
		{
			$this->errorCollection[] = new Error("Getting chat error");

			return false;
		}

		$this->chat = $chat;
		$this->chat['ID'] = $chatId;
		$this->text = $text;
		$this->params = $params;
		$this->userId = $userId;
		$this->fileSource = $fileSource;

		if (!$this->loadModules())
		{
			$this->errorCollection[] = new Error("Loading modules error");

			return false;
		}

		if (!\CIMChat::GetRelationById($this->chat['ID'], $this->userId, true, false))
		{
			$this->errorCollection[] = new Error("Getting chat relation error");

			return false;
		}

		return true;
	}

	private function loadModules(): bool
	{
		if (!Loader::includeModule('pull') || !CPullOptions::GetNginxStatus())
		{
			return false;
		}


		if (!Loader::includeModule('disk'))
		{
			return false;
		}


		if (!\Bitrix\Disk\Driver::isSuccessfullyConverted())
		{
			return false;
		}

		return true;
	}

	private function uploadFileToChatStorage()
	{
		$fileIdWithPrefix = 'disk' . $this->file->getId();

		$attach = new \CIMMessageParamAttach(null, \CIMMessageParamAttach::CHAT);
		$attach->AddMessage($this->text);

		$uploadResult = \CIMDisk::UploadFileFromDisk(
			$this->chat['ID'],
			[$fileIdWithPrefix],
			'',
			[
				'SYMLINK' => true,
				'PARAMS' => $this->params,
				'ATTACH' => $attach
			]
		);

		if (!$uploadResult || !isset($uploadResult['FILE_MODELS'][$fileIdWithPrefix]))
		{
			$this->errorCollection[] = new Error("Uploading file to chat error");

			return false;
		}

		return $uploadResult['FILE_MODELS'][$fileIdWithPrefix];
	}

	private function checkAccess(): bool
	{
		$storageModel = $this->file->getStorage();

		$securityContext = null;
		if (is_null($this->userId))
		{
			$securityContext = $storageModel->getCurrentUserSecurityContext();
		}
		else if ($this->userId > 0)
		{
			$securityContext = $storageModel->getSecurityContext($this->userId);
		}

		if ($securityContext && !$this->file->canRead($securityContext))
		{
			$this->errorCollection[] = new Error("Access denied");

			return false;
		}

		return true;
	}

	private function sendEvent(): bool
	{
		if (empty($this->chat['ENTITY_TYPE']) || empty($this->chat['ENTITY_ID']))
		{
			return false;
		}

		$event = new Event('im', 'onDiskShare', [
			'FILE_SOURCE' => $this->fileSource,
			'DISK_ID' => $this->file->getId(),
			'CHAT' => [
				'ID' => $this->chat['ID'],
				'TITLE' => $this->chat['TITLE'],
				'ENTITY_TYPE' => $this->chat['ENTITY_TYPE'],
				'ENTITY_ID' => $this->chat['ENTITY_ID'],
			],
			'USER_ID' => $this->userId,
		]);
		$event->send();

		return true;
	}
}