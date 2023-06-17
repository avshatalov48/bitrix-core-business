<?php

namespace Bitrix\Im\V2;

use ArrayAccess;
use Bitrix\Im\V2\Message\Reaction\ReactionMessage;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ObjectException;
use Bitrix\Main\UrlPreview\UrlPreview;
use Bitrix\Im;
use Bitrix\Im\User;
use Bitrix\Im\Text;
use Bitrix\Im\Notify;
use Bitrix\Im\Recent;
use Bitrix\Im\Model\EO_Message;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Common\FieldAccessImplementation;
use Bitrix\Im\V2\Common\ActiveRecordImplementation;
use Bitrix\Im\V2\Common\RegistryEntryImplementation;
use Bitrix\Im\V2\Entity\File\FileCollection;
use Bitrix\Im\V2\Entity\Url\UrlItem;
use Bitrix\Im\V2\Link\Pin\PinService;
use Bitrix\Im\V2\Link\Favorite\FavoriteService;
use Bitrix\Im\V2\Message\Param;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Im\V2\Message\Param\Menu;
use Bitrix\Im\V2\Message\Param\Keyboard;
use Bitrix\Im\V2\Message\Param\AttachArray;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Im\V2\Message\ViewedService;
use Bitrix\Im\V2\Message\MessageParameter;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;

/**
 * Chat version #2
 */
class Message implements ArrayAccess, RegistryEntry, ActiveRecord, RestEntity, PopupDataAggregatable
{
	use FieldAccessImplementation;
	use ActiveRecordImplementation
	{
		save as defaultSave;
	}
	use RegistryEntryImplementation;
	use ContextCustomer;

	public const MESSAGE_MAX_LENGTH = 20000;
	public const REST_FIELDS = ['ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE', 'NOTIFY_EVENT'];

	protected ?int $messageId = null;

	protected ?int $chatId = null;

	protected ?Chat $chat = null;

	/** Created by Id */
	protected int $authorId = 0;

	/** Message to send */
	protected ?string $message = null;

	protected ?string $parsedMessage = null;

	protected ?string $formattedMessage = null;

	/** Formatted rich message */
	protected ?string $messageOut = null;

	/** message creation date. */
	protected ?DateTime $dateCreate = null;

	/** E-mail template code. */
	protected ?string $emailTemplate = null;

	/**
	 *	Notification type:
	 * 	@see \IM_NOTIFY_MESSAGE = 0 - message,
	 * 	@see \IM_NOTIFY_CONFIRM = 1 - confirm,
	 * 	@see \IM_NOTIFY_FROM = 2 - notify single from,
	 * 	@see \IM_NOTIFY_SYSTEM = 4 - notify single.
	 */
	protected int $notifyType = \IM_NOTIFY_MESSAGE;

	/** Source module id (ex: xmpp, main, etc). */
	protected ?string $notifyModule = null;

	/** Source module event id for search (ex: IM_GROUP_INVITE). */
	protected ?string $notifyEvent = null;

	/** Field for group in JS notification and search in table. */
	protected ?string $notifyTag = null;

	/** Second TAG for search in table. */
	protected ?string $notifySubTag = null;

	/** Notify title for sending email. */
	protected ?string $notifyTitle = null;

	/** Url to dislplay in notification balloon. */
	protected ?string $notifyLink = null;

	/** Serialized button's data available with NOTIFY_TYPE = 1
	 * 	Array(
	 * 		Array('TITLE' => 'OK', 'VALUE' => 'Y', 'TYPE' => 'accept', 'URL' => '/test.php?CONFIRM=Y'),
	 * 		Array('TITLE' => 'Cancel', 'VALUE' => 'N', 'TYPE' => 'cancel', 'URL' => '/test.php?CONFIRM=N'),
	 * 	)
	 */
	protected ?array $notifyButtons = null;

	/** Message seen flag */
	protected ?bool $notifyRead = null;

	/** Allow answering right in notification balloon. */
	protected ?bool $notifyAnswer = null;

	/** Display only balloon without adding message into notification list. */
	protected ?bool $notifyFlash = null;

	/** The ID of the message to be imported. */
	protected ?int $importId = null;

	protected ?bool $isUnread = null;

	protected ?bool $isViewed = null;

	protected ?bool $isViewedByOthers = null;

	/** Message additional parameters. */
	protected Params $params;

	/**
	 * Message file attachments
	 * @var FileCollection|null
	 */
	protected ?FileCollection $files = null;

	protected ?Im\V2\Message\Reaction\ReactionMessage $reactions;

	/** Display message as a system notification. */
	protected bool $isSystem = false;

	protected ?array $linkAttachments = null;

	/** Message UUID.*/
	protected ?string $uuid = null;

	/** File UUID.*/
	protected ?string $fileUuid = null;

	protected bool $isUuidFilled = false;

	protected ?string $pushMessage = null;
	protected ?array $pushParams = null;
	protected ?string $pushAppId = null;

	/**
	 * @param int|array|EO_Message|null $source
	 */
	public function __construct($source = null)
	{
		$this->params = new Params;

		$this->initByDefault();

		if (!empty($source))
		{
			$this->load($source);
		}
	}

	public function save(): Result
	{
		$result = $this->defaultSave();

		if ($result->isSuccess())
		{
			$this->params->setMessageId($this->getMessageId());

			$paramsSaveResult = $this->params->save();
			if (!$paramsSaveResult->isSuccess())
			{
				$result->addErrors($paramsSaveResult->getErrors());
			}
		}

		return $result;
	}

	//region Setters & Getters

	public function getId(): ?int
	{
		return $this->getMessageId();
	}

	public function markAsSystem(bool $flag): self
	{
		$this->isSystem = $flag;

		return $this;
	}

	public function isSystem(): bool
	{
		$notifyEvent = $this->getNotifyEvent();
		if (!isset($notifyEvent))
		{
			return $this->isSystem;
		}

		return $this->getAuthorId() === 0 || $notifyEvent === 'private_system';
	}

	/**
	 * @param array $params
	 * @return $this
	 */
	public function setParams($params): self
	{
		$this->getParams()->load($params);

		return $this;
	}

	/**
	 * @param bool $disallowLazyLoad
	 * @return Params
	 */
	public function getParams(bool $disallowLazyLoad = false): Params
	{
		if (
			$disallowLazyLoad != true
			&& !$this->params->isLoaded()
			&& $this->getMessageId()
		)
		{
			// lazyload
			$this->params->loadByMessageId($this->getMessageId());
		}

		return $this->params;
	}

	/**
	 * @param array|Param $attach
	 * @return $this
	 */
	public function setAttach($attach): self
	{
		$this->getParams()->set(Params::ATTACH, $attach);
		return $this;
	}

	/**
	 * @return AttachArray|MessageParameter
	 */
	public function getAttach(): AttachArray
	{
		return $this->getParams()->get(Params::ATTACH);
	}

	public function setLinkAttachments(array $linkAttachments): self
	{
		$this->linkAttachments = $linkAttachments;

		return $this;
	}

	public function getLinkAttachments(): array
	{
		if (!isset($this->linkAttachments))
		{
			$this->linkAttachments = [];
			if ($this->getParams()->isSet(Params::URL_ID))
			{
				$urlIds = $this->getParams()->get(Params::URL_ID)->getValue();
				if ($urlIds)
				{
					$this->linkAttachments = array_values(\CIMMessageLink::getAttachments($urlIds, true));
				}
			}
		}

		return $this->linkAttachments;
	}

	public function setUnread(bool $isUnread): self
	{
		$this->isUnread = $isUnread;

		return $this;
	}

	public function isUnread(): bool
	{
		if (isset($this->isUnread))
		{
			return $this->isUnread;
		}

		$messageIds = [$this->getMessageId()];
		$this->isUnread = !(new ReadService())->getReadStatusesByMessageIds($messageIds)[$this->getMessageId()];

		return $this->isUnread;
	}

	public function setViewed(bool $isViewed): self
	{
		$this->isViewed = $isViewed;

		return $this;
	}

	public function isViewed(): bool
	{
		if (isset($this->isViewed))
		{
			return $this->isViewed;
		}

		if ($this->authorId === $this->getContext()->getUserId())
		{
			$this->isViewed = true;

			return $this->isViewed;
		}

		$messageIds = [$this->getMessageId()];
		$this->isViewed = (new ReadService())->getViewStatusesByMessageIds($messageIds)[$this->getMessageId()];

		return $this->isViewed;
	}

	public function setViewedByOthers(bool $isViewedByOthers): self
	{
		$this->isViewedByOthers = $isViewedByOthers;

		return $this;
	}

	public function isViewedByOthers(): bool
	{
		if (isset($this->isViewedByOthers))
		{
			return $this->isViewedByOthers;
		}

		$this->isViewedByOthers = (new ViewedService())->getMessageStatus($this->getMessageId()) === \IM_MESSAGE_STATUS_DELIVERED;

		return $this->isViewedByOthers;
	}

	/**
	 * @param array|Param|Keyboard $keyboard
	 * @return $this
	 */
	public function setKeyboard($keyboard): self
	{
		$this->getParams()->set(Params::KEYBOARD, $keyboard);
		return $this;
	}

	/**
	 * @return Keyboard|MessageParameter
	 */
	public function getKeyboard(): Keyboard
	{
		return $this->getParams()->get(Params::KEYBOARD);
	}

	/*
	 * @param array|Parameter|Menu $menu
	 */
	public function setMenu($menu): self
	{
		$this->getParams()->set(Params::MENU, $menu);
		return $this;
	}

	/**
	 * @return Menu|MessageParameter
	 */
	public function getMenu(): Menu
	{
		return $this->getParams()->get(Params::MENU);
	}

	//region UUID

	/**
	 * @param string|null $uuid
	 * @return self
	 */
	public function setUuid(?string $uuid): self
	{
		$this->isUuidFilled = true;

		if ($uuid && Im\Message\Uuid::validate($uuid))
		{
			$this->uuid = $uuid;
		}

		return $this;
	}

	public function getUuid(): ?string
	{
		if ($this->isUuidFilled)
		{
			return $this->uuid;
		}

		$this->isUuidFilled = true;
		$this->uuid = null;

		if ($this->getMessageId())
		{
			$row = Im\Model\MessageUuidTable::query()
				->setSelect(['UUID'])
				->where('MESSAGE_ID', $this->getMessageId())
				->fetch() ?: [];

			$this->uuid = $row['UUID'] ?? null;
		}

		return $this->uuid;
	}

	public function setFileUuid(?string $uuid): self
	{
		$this->fileUuid = $uuid;
		return $this;
	}

	public function getFileUuid(): ?string
	{
		return $this->fileUuid;
	}

	//endregion

	//region Files

	/**
	 * @return int[]
	 */
	public function getFileIds(): array
	{
		if ($this->getParams()->isSet(Params::FILE_ID))
		{
			return $this->getParams()->get(Params::FILE_ID)->getValue();
		}

		return [];
	}

	/**
	 * @return bool
	 */
	public function hasFiles(): bool
	{
		return
			$this->getParams()->isSet(Params::FILE_ID)
			&& ($this->getParams()->get(Params::FILE_ID)->count() > 0);
	}

	/**
	 * @param int[]|FileCollection $files
	 * @return static
	 */
	public function setFiles($files): self
	{
		$fileIds = [];
		if ($files instanceof FileCollection)
		{
			$this->files = $files;
			foreach ($this->files as $fileItem)
			{
				$fileIds[] = $fileItem->getDiskFileId();
			}
		}
		elseif (is_array($files))
		{
			$fileIds = array_filter(array_map('intval', array_values($files)));
		}

		$this->getParams()->get(Params::FILE_ID)->setValue($fileIds);

		return $this;
	}

	/**
	 * @return FileCollection
	 */
	public function getFiles(): FileCollection
	{
		if (!$this->files instanceof FileCollection)
		{
			$fileIds = $this->getFileIds();
			if (!empty($fileIds))
			{
				$this->files = FileCollection::initByDiskFilesIds($fileIds, $this->getChatId());
			}
			else
			{
				$this->files = new FileCollection;
			}
		}

		return $this->files;
	}

	/**
	 * @param ReactionMessage $reactions
	 * @return $this
	 */
	public function setReactions(Im\V2\Message\Reaction\ReactionMessage $reactions): self
	{
		$this->reactions = $reactions;

		return $this;
	}

	/**
	 * @return ReactionMessage
	 */
	public function getReactions(): Im\V2\Message\Reaction\ReactionMessage
	{
		$this->reactions ??= Im\V2\Message\Reaction\ReactionMessage::getByMessageId($this->getMessageId());

		return $this->reactions;
	}

	/**
	 * Extracts and saves files from message text.
	 * @return self
	 */
	public function uploadFileFromText(): self
	{
		if ($this->getMessage() && $this->getChatId())
		{
			$message = $this->getMessage();
			if (preg_match_all("/\[DISK=([0-9]+)\]/i", $message, $matches))
			{
				$fileFound = false;
				foreach ($matches[1] as $fileId)
				{
					$newFile = \CIMDisk::SaveFromLocalDisk($this->getChatId(), $fileId, false, $this->getContext()->getUserId());
					if ($newFile)
					{
						$fileFound = true;
						$this->getParams()->get(Params::FILE_ID)->addValue($newFile->getId());
					}
				}
				if ($fileFound)
				{
					$message = preg_replace("/\[DISK\=([0-9]+)\]/i", '', $message);
				}
				$this->setMessage($message);
			}
		}

		return $this;
	}

	public function formatFilesMessageOut(): self
	{
		if ($this->getChatId() && $this->hasFiles())
		{
			$messageFiles = $this->formatFileLinks();
			if (!empty($messageFiles))
			{
				$messageOut = $this->getMessageOut() ? $this->getMessageOut() . "\n" : '';
				$messageOut .= implode("\n", $messageFiles);
				$this->setMessageOut($messageOut);
			}
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFilesDiskData(): array
	{
		if ($this->hasFiles())
		{
			return $this->getFiles()->getFileDiskAttributes($this->getChatId());
		}

		return  [];
	}

	private function formatFileLinks(): array
	{
		$messageFiles = [];

		$filesDataList = $this->getFilesDiskData();
		if (!empty($filesDataList))
		{
			$urlManager = UrlManager::getInstance();
			$hostUrl = $urlManager->getHostUrl();
			foreach ($filesDataList as $fileData)
			{
				if ($fileData['status'] == 'done')
				{
					$messageFiles[] =
						$fileData['name'] . ' (' . \CFile::formatSize($fileData['size']) . ')'
						. "\n" . Loc::getMessage('IM_MESSAGE_FILE_DOWN')
						. ' ' . $hostUrl . $fileData['urlDownload']
						. "\n";
				}
			}
		}

		return $messageFiles;
	}

	//endregion

	public function getReminder(): ?Link\Reminder\ReminderItem
	{
		return Link\Reminder\ReminderItem::getByMessageAndUserId($this, $this->getContext()->getUserId());
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$data = new PopupData([
			new Im\V2\Entity\User\UserPopupItem($this->getUserIds()),
			new Im\V2\Entity\File\FilePopupItem(),
			new Im\V2\Link\Reminder\ReminderPopupItem(),
			new Im\V2\Message\Reaction\ReactionPopupItem($this->getReactions())
		], $excludedList);

		if (!in_array(Im\V2\Entity\File\FilePopupItem::class, $excludedList, true))
		{
			$data->add(new Im\V2\Entity\File\FilePopupItem($this->getFiles()));
		}

		if (!in_array(Im\V2\Link\Reminder\ReminderPopupItem::class, $excludedList, true))
		{
			$data->add(new Im\V2\Link\Reminder\ReminderPopupItem($this->getReminder()));
		}

		return $data->mergeFromEntity($this->getReactions());
	}

	public function setMessageId(int $messageId): self
	{
		if (!$this->messageId)
		{
			$this->messageId = $messageId;
			$this->params->setMessageId($messageId);
		}
		return $this;
	}

	public function getMessageId(): ?int
	{
		return $this->messageId;
	}

	public function setAuthorId(int $value): self
	{
		$this->authorId = $value > 0 ? $value : 0;

		return $this;
	}

	public function getAuthorId(): int
	{
		return $this->authorId;
	}

	public function getAuthor(): ?User
	{
		if ($this->getAuthorId())
		{
			return User::getInstance($this->getAuthorId());
		}

		return null;
	}

	public function setChatId(int $value): self
	{
		$this->chatId = $value;
		return $this;
	}

	public function getChatId(): ?int
	{
		return $this->chatId;
	}

	public function getChat(): Chat
	{
		if (!$this->chat)
		{
			if ($this->getChatId())
			{
				$this->chat = Chat::getInstance($this->getChatId());
			}
			if ($this->chat)
			{
				$this->setRegistry($this->chat->getMessageRegistry());
			}
			else
			{
				return new Im\V2\Chat\NullChat();
			}
		}

		return $this->chat;
	}

	// message
	public function setMessage(?string $value): self
	{
		if ($value)
		{
			$value = \trim(\str_replace(['[BR]', '[br]', '#BR#'], "\n", $value));

			if (\mb_strlen($value) > self::MESSAGE_MAX_LENGTH + 6)
			{
				$value = \mb_substr($value, 0, self::MESSAGE_MAX_LENGTH). ' (...)';
			}
		}

		$this->message = $value ?: '';
		return $this;
	}

	public function getMessage(): ?string
	{
		return $this->message;
	}

	private function getParsedMessage(): string
	{
		$this->parsedMessage ??= Im\Text::parse($this->getMessage() ?? '');

		return $this->parsedMessage;
	}

	public function getFormattedMessage(): string
	{
		if (isset($this->formattedMessage))
		{
			return $this->formattedMessage;
		}

		$this->formattedMessage =
			$this->isCompletelyEmpty()
				? Loc::getMessage('IM_MESSAGE_DELETED')
				: $this->getParsedMessage()
		;

		return $this->formattedMessage;
	}

	public function getQuotedMessage(?int $messageSize = null): string
	{
		$user = $this->getAuthor();
		$userName = isset($user) ? $user->getFullName(false) : '';
		$date = FormatDate('X', $this->getDateCreate(), time() + \CTimeZone::GetOffset());
		$contextTag = $this->getContextTag();
		$quoteDelimiter = '------------------------------------------------------';
		$messageContent = $this->getPreviewMessage($messageSize);

		$quotedMessage =
			$quoteDelimiter
			. "\n"
			. "{$userName} [{$date}] $contextTag\n"
			. $messageContent
			. "\n"
			. $quoteDelimiter
		;

		return $quotedMessage;
	}

	public function getReplaceMap(): array
	{
		return Im\Text::getReplaceMap($this->getFormattedMessage());
	}

	// formatted rich message to output
	public function setMessageOut(?string $value): self
	{
		$this->messageOut = $value ? trim($value) : '';
		return $this;
	}

	public function getMessageOut(): ?string
	{
		return $this->messageOut;
	}

	// date create

	/**
	 * @param DateTime|string|null $value
	 * @return static
	 */
	public function setDateCreate($value): self
	{
		if (!empty($value) && !($value instanceof DateTime))
		{
			try
			{
				$value = new DateTime($value);
			}
			catch (ObjectException $exception)
			{}
		}
		$this->dateCreate = $value ?: null;
		return $this;
	}

	public function getDateCreate(): ?DateTime
	{
		return $this->dateCreate;
	}

	public function getDefaultDateCreate(): DateTime
	{
		return new DateTime;
	}

	public function setEmailTemplate(?string $value): self
	{
		$this->emailTemplate = $value ?: '';
		return $this;
	}

	public function getEmailTemplate(): ?string
	{
		return $this->emailTemplate;
	}

	public function setNotifyType(?int $value): self
	{
		if (in_array($value, [\IM_NOTIFY_MESSAGE, \IM_NOTIFY_CONFIRM, \IM_NOTIFY_SYSTEM, \IM_NOTIFY_FROM], true))
		{
			$this->notifyType = $value;
		}
		return $this;
	}

	public function getNotifyType(): int
	{
		return $this->notifyType;
	}

	public function getDefaultNotifyType(): int
	{
		return \IM_NOTIFY_MESSAGE;
	}

	public function setNotifyModule(?string $value): self
	{
		$this->notifyModule = $value;
		return $this;
	}

	public function getNotifyModule(): ?string
	{
		return $this->notifyModule;
	}

	public function getDefaultNotifyModule(): ?string
	{
		return 'im';
	}

	/**
	 * Sets source module event id.
	 * @see \Bitrix\Im\Notify
	 * @return string|null
	 */
	public function setNotifyEvent(?string $value): self
	{
		$this->notifyEvent = $value;
		return $this;
	}

	/**
	 * Returns source module event id.
	 * @see \Bitrix\Im\Notify
	 * @return string|null
	 */
	public function getNotifyEvent(): ?string
	{
		return $this->notifyEvent;
	}

	public function getDefaultNotifyEvent(): ?string
	{
		return Notify::EVENT_DEFAULT;
	}

	public function setNotifyTag(?string $value): self
	{
		$this->notifyTag = $value;
		return $this;
	}

	public function getNotifyTag(): ?string
	{
		return $this->notifyTag;
	}

	public function setNotifySubTag(?string $value): self
	{
		$this->notifySubTag = $value;
		return $this;
	}

	public function getNotifySubTag(): ?string
	{
		return $this->notifySubTag;
	}

	public function setNotifyTitle(?string $value): self
	{
		$this->notifyTitle = $value ? mb_substr(trim($value), 0, 255) : null;
		return $this;
	}

	public function getNotifyTitle(): ?string
	{
		return $this->notifyTitle;
	}

	public function setNotifyLink(?string $value): self
	{
		$this->notifyLink = $value;
		return $this;
	}

	public function getNotifyLink(): ?string
	{
		return $this->notifyLink;
	}

	public function setNotifyButtons($value): self
	{
		if (is_string($value))
		{
			$value = $this->unserializeNotifyButtons($value);
		}
		$this->notifyButtons = $value;
		return $this;
	}

	public function getNotifyButtons(): ?array
	{
		return $this->notifyButtons;
	}

	protected function serializeNotifyButtons($value)
	{
		return $value ? serialize($value) : null;
	}

	protected function unserializeNotifyButtons($value)
	{
		return $value ? unserialize($value, ['allowed_classes' => false]) : null;
	}

	public function markNotifyRead(?bool $value): self
	{
		$this->notifyRead = $value ?: false;
		return $this;
	}

	public function isNotifyRead(): ?bool
	{
		return $this->notifyRead;
	}

	public function getDefaultNotifyRead(): bool
	{
		return false;
	}

	public function markNotifyAnswer(?bool $value): self
	{
		$this->notifyAnswer = $value ?: false;
		return $this;
	}

	public function allowNotifyAnswer(): ?bool
	{
		return $this->notifyAnswer;
	}

	public function markNotifyFlash(?bool $value): self
	{
		$this->notifyFlash = $value;
		return $this;
	}

	public function isNotifyFlash(): ?bool
	{
		return $this->notifyFlash;
	}

	public function setImportId(?int $value): self
	{
		$this->importId = $value;
		return $this;
	}

	public function getImportId(): ?int
	{
		return $this->importId;
	}

	//endregion

	//region Push

	/**
	 * @todo Move it into special push message class.
	 * @param string|null $message
	 * @return self
	 */
	public function setPushMessage(?string $message): self
	{
		$this->pushMessage = $message;
		return $this;
	}

	public function getPushMessage(): ?string
	{
		return $this->pushMessage;
	}

	/**
	 * @todo Move it into special push message class.
	 * @param array|null $params
	 * @return self
	 */
	public function setPushParams(?array $params): self
	{
		$this->pushParams = $params;
		return $this;
	}

	public function getPushParams(): ?array
	{
		return $this->pushParams;
	}

	/**
	 * @todo Move it into special push message class.
	 * @param string|null $message
	 * @return self
	 */
	public function setPushAppId(?string $message): self
	{
		$this->pushAppId = $message;
		return $this;
	}

	public function getPushAppId(): ?string
	{
		return $this->pushAppId;
	}


	//endregion

	//region Data storage

	/**
	 * @return array
	 */
	protected static function mirrorDataEntityFields(): array
	{
		return [
			'ID' => [
				'primary' => true,
				'field' => 'messageId', /** @see Message::$messageId */
				'set' => 'setMessageId', /** @see Message::setMessageId */
				'get' => 'getMessageId', /** @see Message::getMessageId */
			],
			'CHAT_ID' => [
				'field' => 'chatId', /** @see Message::$chatId */
				'set' => 'setChatId', /** @see Message::setChatId */
				'get' => 'getChatId', /** @see Message::getChatId */
			],
			'AUTHOR_ID' => [
				'field' => 'authorId', /** @see Message::$authorId */
				'set' => 'setAuthorId', /** @see Message::setAuthorId */
				'get' => 'getAuthorId', /** @see Message::getAuthorId */
			],
			'FROM_USER_ID' => [
				'alias' => 'AUTHOR_ID',
			],
			'MESSAGE' => [
				'field' => 'message', /** @see Message::$message */
				'set' => 'setMessage', /** @see Message::setMessage */
				'get' => 'getMessage', /** @see Message::getMessage */
			],
			'MESSAGE_OUT' => [
				'field' => 'messageOut', /** @see Message::$messageOut */
				'set' => 'setMessageOut', /** @see Message::setMessageOut */
				'get' => 'getMessageOut', /** @see Message::getMessageOut */
			],
			'DATE_CREATE' => [
				'field' => 'dateCreate', /** @see Message::$dateCreate */
				'set' => 'setDateCreate', /** @see Message::setDateCreate */
				'get' => 'getDateCreate', /** @see Message::getDateCreate */
				'default' => 'getDefaultDateCreate', /** @see Message::getDefaultDateCreate */
			],
			'MESSAGE_DATE' =>
				[
					'alias' => 'DATE_CREATE',
				],
			'EMAIL_TEMPLATE' => [
				'field' => 'emailTemplate', /** @see Message::$emailTemplate */
				'set' => 'setEmailTemplate', /** @see Message::setEmailTemplate */
				'get' => 'getEmailTemplate', /** @see Message::getEmailTemplate */
			],
			'NOTIFY_TYPE' => [
				'field' => 'notifyType', /** @see Message::$notifyType */
				'set' => 'setNotifyType', /** @see Message::setNotifyType */
				'get' => 'getNotifyType', /** @see Message::getNotifyType */
				'default' => 'getDefaultNotifyType',/** @see Message::getDefaultNotifyType */
			],
			'NOTIFY_MODULE' => [
				'field' => 'notifyModule', /** @see Message::$notifyModule */
				'set' => 'setNotifyModule', /** @see Message::setNotifyModule */
				'get' => 'getNotifyModule', /** @see Message::getNotifyModule */
				'default' => 'getDefaultNotifyModule',/** @see Message::getDefaultNotifyModule */
			],
			'NOTIFY_EVENT' => [
				'field' => 'notifyEvent', /** @see Message::$notifyEvent */
				'set' => 'setNotifyEvent', /** @see Message::setNotifyEvent */
				'get' => 'getNotifyEvent', /** @see Message::getNotifyEvent */
				'default' => 'getDefaultNotifyEvent',/** @see Message::getDefaultNotifyEvent */
			],
			'NOTIFY_TAG' => [
				'field' => 'notifyTag', /** @see Message::$notifyTag */
				'set' => 'setNotifyTag', /** @see Message::setNotifyTag */
				'get' => 'getNotifyTag', /** @see Message::getNotifyTag */
			],
			'NOTIFY_SUB_TAG' => [
				'field' => 'notifySubTag', /** @see Message::$notifySubTag */
				'set' => 'setNotifySubTag', /** @see Message::setNotifySubTag */
				'get' => 'getNotifySubTag', /** @see Message::getNotifySubTag */
			],
			'NOTIFY_TITLE' => [
				'field' => 'notifyTitle', /** @see Message::$notifyTitle */
				'set' => 'setNotifyTitle', /** @see Message::setNotifyTitle */
				'get' => 'getNotifyTitle', /** @see Message::getNotifyTitle */
			],
			'NOTIFY_LINK' => [
				'set' => 'setNotifyLink', /** @see Message::setNotifyLink */
				'get' => 'getNotifyLink', /** @see Message::getNotifyLink */
			],
			'TITLE' => [
				'alias' => 'NOTIFY_TITLE',
			],
			'NOTIFY_MESSAGE' => [
				'alias' => 'MESSAGE',
			],
			'NOTIFY_MESSAGE_OUT' => [
				'alias' => 'MESSAGE_OUT',
			],
			'NOTIFY_BUTTONS' => [
				'field' => 'notifyButtons', /** @see Message::$notifyButtons */
				'set' => 'setNotifyButtons', /** @see Message::setNotifyButtons */
				'get' => 'getNotifyButtons', /** @see Message::getNotifyButtons */
				'saveFilter' => 'serializeNotifyButtons', /** @see Message::serializeNotifyButtons */
				'loadFilter' => 'unserializeNotifyButtons', /** @see Message::unserializeNotifyButtons */
			],
			'NOTIFY_READ' => [
				'field' => 'notifyRead', /** @see Message::$notifyRead */
				'set' => 'markNotifyRead', /** @see Message::markNotifyRead */
				'get' => 'isNotifyRead', /** @see Message::isNotifyRead */
				'default' => 'getDefaultNotifyRead',/** @see Message::getDefaultNotifyRead */
			],
			'NOTIFY_ANSWER' => [
				'set' => 'markNotifyAnswer', /** @see Message::markNotifyAnswer */
				'get' => 'allowNotifyAnswer', /** @see Message::allowNotifyAnswer */
			],
			'NOTIFY_FLASH' => [
				'set' => 'markNotifyFlash', /** @see Message::markNotifyFlash */
				'get' => 'isNotifyFlash', /** @see Message::isNotifyFlash */
			],
			'NOTIFY_ONLY_FLASH' => [
				'alias' => 'NOTIFY_FLASH',
			],
			'IMPORT_ID' => [
				'field' => 'importId', /** @see Message::$importId */
				'set' => 'setImportId', /** @see Message::setImportId */
				'get' => 'getImportId', /** @see Message::getImportId */
			],
			'SYSTEM' => [
				'set' => 'markAsSystem', /** @see Message::markAsSystem */
				'get' => 'isSystem', /** @see Message::isSystem */
			],
			'PARAMS' => [
				'set' => 'setParams', /** @see Message::setParams */
				'get' => 'getParams', /** @see Message::getParams */
			],
			'ATTACH' => [
				'set' => 'setAttach', /** @see Message::setAttach */
				'get' => 'getAttach', /** @see Message::getAttach */
			],
			'FILES' => [
				'set' => 'setFiles', /** @see Message::setFiles */
				'get' => 'getFiles', /** @see Message::getFiles */
			],
			'KEYBOARD' => [
				'set' => 'setKeyboard', /** @see Message::setKeyboard */
				'get' => 'getKeyboard', /** @see Message::getKeyboard */
			],
			'MENU' => [
				'set' => 'setMenu', /** @see Message::setMenu */
				'get' => 'getMenu', /** @see Message::getMenu */
			],
			'UUID' => [
				'set' => 'setUuid', /** @see Message::setUuid */
				'get' => 'getUuid', /** @see Message::getUuid */
			],
			'MESSAGE_UUID' => [
				'alias' => 'UUID',
			],
			'TEMPLATE_ID' => [
				'alias' => 'UUID',
			],
			'FILE_TEMPLATE_ID' => [
				'set' => 'setFileUuid', /** @see Message::setFileUuid */
				'get' => 'getFileUuid', /** @see Message::getFileUuid */
			],
			'PUSH_MESSAGE' => [
				'set' => 'setPushMessage', /** @see Message::setPushMessage */
				'get' => 'getPushMessage', /** @see Message::getPushMessage */
			],
			'MESSAGE_PUSH' => [
				'alias' => 'PUSH_MESSAGE'
			],
			'PUSH_PARAMS' => [
				'set' => 'setPushParams', /** @see Message::setPushParams */
				'get' => 'getPushParams', /** @see Message::getPushParams */
			],
			'EXTRA_PARAMS' => [
				'alias' => 'PUSH_PARAMS'
			],
			'PUSH_APP_ID' => [
				'set' => 'setPushAppId', /** @see Message::setPushAppId */
				'get' => 'getPushAppId', /** @see Message::getPushAppId */
			],
		];
	}

	/**
	 * @return string|DataManager;
	 */
	public static function getDataClass(): string
	{
		return MessageTable::class;
	}

	/**
	 * @return int|null
	 */
	public function getPrimaryId(): ?int
	{
		return $this->getMessageId();
	}

	/**
	 * @param int $primaryId
	 * @return self
	 */
	public function setPrimaryId(int $primaryId): self
	{
		return $this->setMessageId($primaryId);
	}

	//endregion

	public function markAsFavorite(): Result
	{
		$favoriteMessageService = new FavoriteService();
		$favoriteMessageService->setContext($this->context);

		return $favoriteMessageService->markMessageAsFavorite($this);
	}

	public function unmarkAsFavorite(): Result
	{
		$favoriteMessageService = new FavoriteService();
		$favoriteMessageService->setContext($this->context);

		return $favoriteMessageService->unmarkMessageAsFavorite($this);
	}

	public function pin(): Result
	{
		$pinService = new PinService();
		$pinService->setContext($this->context);

		return $pinService->pinMessage($this);
	}

	public function unpin(): Result
	{
		$pinService = new PinService();
		$pinService->setContext($this->context);

		return $pinService->unpinMessage($this);
	}

	public function mark(): Result
	{
		$result = new Result();

		$isSuccessMark = Recent::unread(
			$this->getChat()->getDialogId(),
			true,
			$this->getContext()->getUserId(),
			$this->getId()
		);

		if (!$isSuccessMark)
		{
			$result->addError(new Im\V2\Message\MessageError(Im\V2\Message\MessageError::MARK_FAILED));
		}

		return $result;
	}

	public function addToReminder(DateTime $dateRemind): Result
	{
		$reminderService = new Link\Reminder\ReminderService();
		$reminderService->setContext($this->context);

		return $reminderService->addMessageToReminders($this, $dateRemind);
	}

	public function getPreviewMessage(?int $messageSize = 200): string
	{
		$previewMessage = trim($this->getFormattedMessage());
		$hasFiles = $this->hasFiles();
		$hasAttach = mb_strpos($previewMessage, '[ATTACH=') !== false;

		if ($this->getRegistry() instanceof MessageCollection)
		{
			$this->getRegistry()->fillFiles();
		}

		if ($hasFiles)
		{
			$files = $this->getFiles();
			foreach ($files as $file)
			{
				$hasFiles = true;
				$previewMessage .= " [{$file->getDiskFile()->getName()}]";
			}
		}

		$previewMessage = preg_replace(
			"/\[ATTACH=([0-9]{1,})\]/i",
			" [".Loc::getMessage('IM_MESSAGE_ATTACH')."] ",
			$previewMessage
		);
		$previewMessage = preg_replace(
			'#\-{54}.+?\-{54}#s',
			" [".Loc::getMessage('IM_MESSAGE_QUOTE')."] ",
			str_replace(["#BR#"], [" "], $previewMessage)
		);
		$previewMessage = preg_replace(
			'/^(>>(.*)(\n)?)/mi',
			" [".Loc::getMessage('IM_MESSAGE_QUOTE')."] ",
			str_replace(["#BR#"], [" "], $previewMessage)
		);

		if (!$hasFiles && !$hasAttach)
		{
			if ($this->getParams()->isSet(Params::ATTACH))
			{
				$previewMessage .= " [".Loc::getMessage('IM_MESSAGE_ATTACH')."]";
			}
		}

		if ($messageSize !== null)
		{
			$dots = mb_strlen($previewMessage) >= $messageSize ? '...' : '';
			$previewMessage = mb_substr($previewMessage, 0, $messageSize - 1) . $dots;
		}

		return $previewMessage;
	}

	public function getForPush(?int $messageSize = 200): string
	{
		if ($this->getRegistry() instanceof MessageCollection)
		{
			$this->getRegistry()->fillFiles();
		}

		$files = [];

		foreach ($this->getFiles() as $file)
		{
			$files[] = ['name' => $file->getDiskFile()->getName()];
		}

		$message = ['MESSAGE' => $this->getMessage(), 'FILES' => $files];
		$text = \CIMMessenger::PrepareParamsForPush($message);

		if ($messageSize !== null)
		{
			$dots = mb_strlen($text) >= $messageSize ? '...' : '';
			$text = mb_substr($text, 0, $messageSize - 1) . $dots;
		}

		return $text;
	}

	public static function getRestEntityName(): string
	{
		return 'message';
	}

	public function getUserIds(): array
	{
		if ($this->getAuthorId() === 0)
		{
			return [];
		}

		return [$this->getAuthorId()];
	}

	/**
	 * Enrich the parameters with data that is displayed only in the rest
	 * @return array
	 */
	protected function getParamsForRest(): array
	{
		$params = $this->getParams()->toRestFormat();

		$attach = $this->getLinkAttachments();
		if (!empty($attach))
		{
			$params[Params::ATTACH] = array_merge($params[Params::ATTACH] ?? [], $attach);
		}

		if ($this->isCompletelyEmpty())
		{
			$params[Params::IS_DELETED] = 'Y';
		}

		return $params;
	}

	protected function isCompletelyEmpty(): bool
	{
		return (
			$this->getParsedMessage() === ''
			&& !$this->getParams()->isSet(Params::FILE_ID)
			&& !$this->getParams()->isSet(Params::KEYBOARD)
			&& !$this->getParams()->isSet(Params::ATTACH)
		);
	}

	protected function getContextTag(): string
	{
		$chat = $this->getChat();

		if ($chat instanceof Im\V2\Chat\PrivateChat)
		{
			$userIds = $chat->getRelations()->getUserIds();
			$implodeUserIds = implode(':', $userIds);

			return "#{$implodeUserIds}/{$this->getMessageId()}";
		}

		return "#{$chat->getDialogId()}/{$this->getMessageId()}";
	}

	/**
	 * @param array $option
	 * @return array
	 */
	public function toRestFormat(array $option = []): array
	{
		$dateCreate = $this->getDateCreate();
		$authorId = $this->getNotifyEvent() === Notify::EVENT_SYSTEM ? 0 : $this->getAuthorId();

		return [
			'id' => $this->getId(),
			'chat_id' => $this->getChatId(),
			'author_id' => $authorId,
			'date' => isset($dateCreate) ? $dateCreate->format('c') : null,
			'text' => $this->getFormattedMessage(),
			'isSystem' => $this->isSystem(),
			'replaces' => $this->getReplaceMap(),
			'unread' => $this->isUnread(),
			'viewed' => $this->isViewed(),
			'viewedByOthers' => $authorId === $this->getContext()->getUserId() && $this->isViewedByOthers(),
			'uuid' => $this->getUuid(),
			'params' => $this->getParamsForRest(),
		];
	}

	/**
	 * Appends message with an url preview attachment.
	 * @return void
	 */
	public function generateUrlPreview(): void
	{
		if ($this->getMessage())
		{
			$urls = UrlItem::getUrlsFromText($this->getMessage());
			foreach ($urls as $url)
			{
				$metadata = UrlPreview::getMetadataByUrl($url, true, false);
				if ($metadata !== false)
				{
					$urlItem = UrlItem::initByMetadata($metadata);
					if ($urlItem->getId())
					{
						$this->getParams()->get(Params::URL_ID)->addValue($urlItem->getId());
						$this->getParams()->get(Params::ATTACH)->addValue($urlItem->getUrlAttach());

						// check if message contains only link
						if ($urlItem->isStaticUrl())
						{
							$staticUrl = [$url];
							if (mb_substr($url, -1) == '/')
							{
								$staticUrl[] = mb_substr($url, 0, -1);
							}
							$checkMessage = trim(str_replace($staticUrl, '', $this->getMessage()));

							if (empty($checkMessage))
							{
								$this->getParams()->get(Params::URL_ONLY)->setValue(true);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Parse dates from message.
	 * @return self
	 */
	public function parseDates(): self
	{
		if ($this->getMessage())
		{
			$dateConvertResult = Text::getDateConverterParams($this->getMessage());
			foreach ($dateConvertResult as $row)
			{
				$this->getParams()->get(Params::DATE_TEXT)->addValue($row->getText());
				$this->getParams()->get(Params::DATE_TS)->addValue($row->getDate()->getTimestamp());
			}
		}

		return $this;
	}

	/**
	 * Parse dates from message.
	 * @return self
	 */
	public function checkEmoji(): self
	{
		if ($this->getMessage())
		{
			if (Text::isOnlyEmoji($this->getMessage()))
			{
				$this->getParams()->get(Params::LARGE_FONT)->setValue(true);
			}
		}

		return $this;
	}

	/**
	 * Update search index record.
	 * @return void
	 */
	public function updateSearchIndex(): void
	{
		if ($this->getMessageId())
		{
			MessageTable::indexRecord($this->getMessageId());
		}
	}

	/**
	 * Lazy load message's context phrases.
	 * @return void
	 */
	public static function loadPhrases(): void
	{
		Loc::loadMessages(__FILE__);
	}
}
