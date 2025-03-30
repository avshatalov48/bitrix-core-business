<?php

namespace Bitrix\Im\V2;

use ArrayAccess;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Message\Reaction\ReactionMessage;
use Bitrix\Im\V2\TariffLimit\DateFilterable;
use Bitrix\Im\V2\TariffLimit\FilterResult;
use Bitrix\Im\V2\TariffLimit\Limit;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ObjectException;
use Bitrix\Main\UrlPreview\UrlPreview;
use Bitrix\Im;
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
use Bitrix\Im\V2\Message\MessageParameter;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;

/**
 * Chat version #2
 */
class Message implements ArrayAccess, RegistryEntry, ActiveRecord, RestEntity, PopupDataAggregatable, DateFilterable
{
	use FieldAccessImplementation;
	use ActiveRecordImplementation
	{
		save as defaultSave;
	}
	use RegistryEntryImplementation;
	use ContextCustomer;

	public const MESSAGE_MAX_LENGTH = 20000;
	public const REST_FIELDS = ['ID', 'CHAT_ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE', 'NOTIFY_EVENT', 'NOTIFY_READ'];

	protected ?int $messageId = null;

	protected ?int $chatId = null;

	protected ?Chat $chat = null;

	/** Created by Id */
	protected int $authorId = 0;
	protected array $userIdsFromMention;

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

	protected ?UrlItem $url;

	protected int $botId = 0;

	/** Message UUID.*/
	protected ?string $uuid = null;

	protected ?string $forwardUuid = null;

	/** File UUID.*/
	protected ?string $fileUuid = null;

	protected bool $isUuidFilled = false;
	protected bool $isUrlFilled = false;
	protected bool $isMessageOutFilled = false;

	protected ?string $pushMessage = null;
	protected ?array $pushParams = null;
	protected ?string $pushAppId = null;

	protected ?bool $isImportant = false;

	protected ?array $importantFor = null;
	protected ?string $dialogId = null;
	protected ?int $prevId = null;

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
		$checkParamsIsValid = $this->getParams()->isValid();

		if (!$checkParamsIsValid->isSuccess())
		{
			return $checkParamsIsValid;
		}

		$result = $this->defaultSave();

		if ($result->isSuccess())
		{
			$this->params->setMessageId($this->getMessageId());

			$paramsSaveResult = $this->params->save();
			if (!$paramsSaveResult->isSuccess())
			{
				$result->addErrors($paramsSaveResult->getErrors());
			}

			$this->params = new Params();
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
		return $this->isSystem;
	}

	public function isDisappearing(): bool
	{
		return (bool)$this->getDisappearingTime();
	}

	public function getDisappearingTime(): ?DateTime
	{
		if ($this->getMessageId())
		{
			$row = Im\Model\MessageDisappearingTable::getRowById($this->getMessageId());

			return $row['DATE_REMOVE'];
		}

		return null;
	}

	public function isImportant(): ?bool
	{
		return $this->isImportant;
	}

	public function markAsImportant(?bool $isImportant = true): self
	{
		$this->isImportant = $isImportant;

		return $this;
	}

	public function getImportantFor(): array
	{
		return $this->importantFor ?? array_values($this->getUserIdsFromMention());
	}

	public function setImportantFor(array $importantFor): self
	{
		$this->importantFor = $importantFor;

		return $this;
	}

	public function getForwardUuid(): ?string
	{
		return $this->forwardUuid;
	}

	public function setForwardUuid(?string $forwardUuid): self
	{
		if ($forwardUuid && Im\Message\Uuid::validate($forwardUuid))
		{
			$this->forwardUuid = $forwardUuid;
		}

		return $this;
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
	 * @param array $params
	 * @return $this
	 */
	public function resetParams($params): self
	{
		$this->getParams()->delete();

		return $this->setParams($params);
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
		$this->getParams()->get(Params::ATTACH)->setValue($attach);
		return $this;
	}

	/**
	 * @return AttachArray|MessageParameter
	 */
	public function getAttach(): AttachArray
	{
		return $this->getParams()->get(Params::ATTACH);
	}

	public function setUrl(?UrlItem $url): self
	{
		$this->url = $url;
		$this->isUrlFilled = true;

		return $this;
	}

	public function getUrl(): ?UrlItem
	{
		if (isset($this->url))
		{
			return $this->url;
		}

		$urlId = $this->getParams()->get(Params::URL_ID)->getValue()[0] ?? null;
		if (isset($urlId) && !$this->isUrlFilled)
		{
			return UrlItem::initByPreviewUrlId($urlId, false);
		}

		return null;
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

	public function isViewedByOthers(): bool
	{
		return $this->isNotifyRead() ?? false;
	}

	public function setBotId(int $botId): self
	{
		$this->botId = $botId;

		return $this;
	}

	/**
	 * @param array|Param|Keyboard $keyboard
	 * @return $this
	 */
	public function setKeyboard($keyboard): self
	{
		if (is_array($keyboard))
		{
			$value = [];
			if (!isset($keyboard['BUTTONS']))
			{
				$value['BUTTONS'] = $keyboard;
			}
			else
			{
				$value = $keyboard;
			}
			if (!isset($value['BOT_ID']))
			{
				$value['BOT_ID'] = $this->botId;
			}
			$keyboard = $value;
		}

		$this->getParams()->get(Params::KEYBOARD)->setValue($keyboard);
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
		if (is_array($menu))
		{
			$value = [];
			if (!isset($menu['ITEMS']))
			{
				$value['ITEMS'] = $menu;
			}
			else
			{
				$value = $menu;
			}
			if (!isset($value['BOT_ID']))
			{
				$value['BOT_ID'] = $this->botId;
			}
			$menu = $value;
		}

		$this->getParams()->get(Params::MENU)->setValue($menu);
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
		$this->uuid = $uuid;

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

	public function fillFiles(FileCollection $files): self
	{
		$this->files = $files;

		return $this;
	}

	public function addFile(Im\V2\Entity\File\FileItem $file): self
	{
		$this->getFiles()[] = $file;
		$this->getParams()->get(Params::FILE_ID)->addValue($file->getId());

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

	public function getPrevId(): int
	{
		if ($this->prevId !== null)
		{
			return $this->prevId;
		}

		$result = \Bitrix\Im\Model\MessageTable::query()
			->setSelect(['ID'])
			->where('CHAT_ID', $this->getChatId() ?? 0)
			->where('ID', '<', $this->getId() ?? 0)
			->setOrder(['DATE_CREATE' => 'DESC', 'ID' => 'DESC'])
			->setLimit(1)
			->fetch() ?: []
		;
		$this->prevId = (int)($result['ID'] ?? 0);

		return $this->prevId;
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
	 *
	 * @return array
	 */
	public function uploadFileFromText(): array
	{
		$files = [];
		$message = $this->getMessage();
		$chatId = $this->getChatId();

		if (!$message || !$chatId)
		{
			return $files;
		}

		$diskFileIds = Im\V2\Entity\File\FileItem::getDiskFileIdsFromBbCodesInText($message);

		foreach ($diskFileIds as $fileId)
		{
			$newFile = \CIMDisk::SaveFromLocalDisk($this->getChatId(), $fileId, false, $this->getContext()->getUserId());
			if (!$newFile)
			{
				continue;
			}
			$files[] = $newFile;
			$file = new Im\V2\Entity\File\FileItem($newFile, $this->getChatId());
			$this->addFile($file);
		}

		if (!empty($diskFileIds))
		{
			$this->setMessage(Im\V2\Entity\File\FileItem::removeDiskBbCodesFromText($message));
		}

		return $files;
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

	//endregion

	public function getReminder(): ?Link\Reminder\ReminderItem
	{
		return Link\Reminder\ReminderItem::getByMessageAndUserId($this, $this->getContext()->getUserId());
	}

	public function getAdditionalMessageIds(): array
	{
		$ids = [];

		if ($this->getParams()->isSet(Params::REPLY_ID))
		{
			$ids[] = $this->getParams()->get(Params::REPLY_ID)->getValue();
		}

		return $ids;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$data = new PopupData([
			new Im\V2\Entity\User\UserPopupItem($this->getUserIds()),
			new Im\V2\Entity\File\FilePopupItem(),
			new Im\V2\Link\Reminder\ReminderPopupItem(),
			new Im\V2\Message\Reaction\ReactionPopupItem($this->getReactions()),
		], $excludedList);

		if (!in_array(Im\V2\Entity\File\FilePopupItem::class, $excludedList, true))
		{
			$data->add(new Im\V2\Entity\File\FilePopupItem($this->getFiles()));
		}

		/*if (!in_array(Im\V2\Link\Reminder\ReminderPopupItem::class, $excludedList, true))
		{
			$data->add(new Im\V2\Link\Reminder\ReminderPopupItem($this->getReminder()));
		}*/

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

	public function setAuthorId(int $authorId): self
	{
		$this->authorId = $authorId;

		$this->processChangeAuthorId($authorId);

		return $this;
	}

	public function processChangeAuthorId(int $authorId): int
	{
		if ($authorId === 0)
		{
			$this->markAsSystem(true);
		}

		if ($this->context && $authorId)
		{
			$this->context->setUserId($authorId);
		}

		return $authorId;
	}

	public function getAuthorId(): int
	{
		return $this->authorId;
	}

	public function getAuthor(): ?Entity\User\User
	{
		if ($this->getAuthorId())
		{
			return Im\V2\Entity\User\User::getInstance($this->getAuthorId());
		}

		return null;
	}

	public function setChatId(int $value): self
	{
		$this->chatId = $value;
		return $this;
	}

	public function setChat(Chat $chat): self
	{
		$this->chat = $chat;
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

		$this->message = $value ?? '';
		unset($this->parsedMessage, $this->formattedMessage, $this->url);
		return $this;
	}

	public function getMessage(): ?string
	{
		return $this->message;
	}

	public function getParsedMessage(): string
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
		$userName = $user?->getName() ?? '';
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
		if ($this->isMessageOutFilled)
		{
			return $this->messageOut;
		}

		$this->fillMessageOut();

		return $this->messageOut;
	}

	public function fillMessageOut(): ?string
	{
		if ($this->isMessageOutFilled)
		{
			return $this->messageOut;
		}

		if ($this->getChatId() && $this->hasFiles())
		{
			$messageFiles = $this->getFiles()->getMessageOut();
			if (!empty($messageFiles))
			{
				$messageOut = $this->messageOut ?: $this->message;
				$messageOut .= "\n";
				$messageOut .= implode("\n", $messageFiles);
				$this->setMessageOut($messageOut);
			}
		}

		$this->isMessageOutFilled = true;

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
	public function setNotifyEvent(?string $notifyEvent): self
	{
		$this->notifyEvent = $notifyEvent;

		$this->processChangeNotifyEvent($notifyEvent);

		return $this;
	}

	public function processChangeNotifyEvent(?string $notifyEvent): ?string
	{
		if ($notifyEvent === Notify::EVENT_PRIVATE_SYSTEM)
		{
			$this->markAsSystem(true);
		}

		return $notifyEvent;
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
				'loadFilter' => 'processChangeAuthorId', /** @see Message::processChangeAuthorId */
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
				'saveFilter' => 'fillMessageOut', /** @see Message::fillMessageOut */
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
				'default' => 'getDefaultNotifyEvent', /** @see Message::getDefaultNotifyEvent */
				'loadFilter' => 'processChangeNotifyEvent', /** @see Message::processChangeNotifyEvent */
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
			'TO_CHAT_ID' => [
				'alias' => 'CHAT_ID',
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
			$result->addError(new MessageError(MessageError::MARK_FAILED));
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

	public function checkAccess(?int $userId = null): Result
	{
		$userId ??= $this->getContext()->getUserId();
		$chat = $this->getChat();
		$result = new Result();

		if (!$this->getId())
		{
			return $result->addError(new MessageError(MessageError::NOT_FOUND));
		}

		$chatAccess = $chat->checkAccess($userId);
		if (!$chatAccess->isSuccess())
		{
			return $chatAccess;
		}

		if ($chat->getStartId($userId) > $this->getId())
		{
			return $result->addError(new MessageError(MessageError::ACCESS_DENIED));
		}

		if (!Limit::getInstance()->hasAccessByDate($this, $this->getDateCreate() ?? new DateTime()))
		{
			return $result->addError(new MessageError(MessageError::MESSAGE_ACCESS_DENIED_BY_TARIFF));
		}

		return $result;
	}

	public static function getRestEntityName(): string
	{
		return 'message';
	}

	public function getUserIds(): array
	{
		$userIds = $this->getUserIdsFromMention();

		if ($this->getAuthorId() !== 0)
		{
			$userIds[$this->getAuthorId()] = $this->getAuthorId();
		}

		if ($this->getParams()->isSet(Params::FORWARD_USER_ID))
		{
			$userId = (int)$this->getParams()->get(Params::FORWARD_USER_ID)->getValue();
			$userIds[$userId] = $userId;
		}

		return $userIds;
	}

	public function getUserIdsFromMention(): array
	{
		if (isset($this->userIdsFromMention))
		{
			return $this->userIdsFromMention;
		}

		$this->userIdsFromMention = [];
		if (preg_match_all("/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/i", $this->getParsedMessage(), $matches))
		{
			foreach ($matches[1] as $userId)
			{
				$this->userIdsFromMention[(int)$userId] = (int)$userId;
			}
		}

		return $this->userIdsFromMention;
	}

	public function getUserIdsToSendMentions(): array
	{
		$mentionedUsers = $this->getUserIdsFromMention();

		return $this->getChat()->filterUsersToMention($mentionedUsers);
	}

	public function getEnrichedParams(bool $withUrl = true): Params
	{
		$params = clone $this->getParams();

		if ($withUrl)
		{
			$url = $this->getUrl();
			if (isset($url))
			{
				$params->get(Params::ATTACH)->addValue($url->getUrlAttach());
			}
		}

		if ($this->isCompletelyEmpty())
		{
			$params->get(Params::IS_DELETED)->setValue(true);
		}

		return $params;
	}

	public function isCompletelyEmpty(): bool
	{
		return (
			$this->getParsedMessage() === ''
			&& !$this->getParams()->isSet(Params::FILE_ID)
			&& !$this->getParams()->isSet(Params::KEYBOARD)
			&& !$this->getParams()->isSet(Params::ATTACH)
		);
	}

	public function getContextId(): string
	{
		$chat = $this->getChat();

		if ($chat instanceof Im\V2\Chat\PrivateChat)
		{
			$userIds = $chat->getRelations()->getUserIds();
			$implodeUserIds = implode(':', $userIds);

			return "{$implodeUserIds}/{$this->getMessageId()}";
		}

		return "{$chat->getDialogId()}/{$this->getMessageId()}";
	}

	protected function getContextTag(): string
	{
		return "#{$this->getContextId()}";
	}

	public function isForward(): bool
	{
		return $this->getParams()->isSet(Params::FORWARD_ID)
			&& $this->getParams()->isSet(Params::FORWARD_CONTEXT_ID)
		;
	}

	public function getForwardInfo(): ?array
	{
		if (!$this->isForward())
		{
			return null;
		}

		$contextId = $this->getParams()->get(Params::FORWARD_CONTEXT_ID)->getValue();

		return [
			'id' => $contextId,
			'userId' => (int)$this->getParams()->get(Params::FORWARD_USER_ID)->getValue(),
			'chatTitle' => $this->getParams()->get(Params::FORWARD_CHAT_TITLE)->getValue() ?? null,
			'chatType' => Im\V2\Message\Forward\ForwardService::getChatTypeByContextId($contextId),
		];
	}

	/**
	 * @param array $option
	 * @return array
	 */
	public function toRestFormat(array $option = []): array
	{
		$dateCreate = $this->getDateCreate();
		$authorId = $this->getNotifyEvent() === Notify::EVENT_SYSTEM ? 0 : $this->getAuthorId();
		$messageShortInfo = $option['MESSAGE_SHORT_INFO'] ?? false;
		$onlyCommonRest = [
			'id' => $this->getId(),
			'chat_id' => $this->getChatId(),
			'author_id' => $authorId,
			'date' => isset($dateCreate) ? $dateCreate->format('c') : null,
			'text' => $this->getFormattedMessage(),
			'isSystem' => $this->isSystem(),
			'replaces' => $this->getReplaceMap(),
			'uuid' => $this->getUuid(),
			'forward' => $this->getForwardInfo(),
			'params' => $this->getEnrichedParams(!$messageShortInfo)->toRestFormat(),
			'viewedByOthers' => $this->isViewedByOthers(),
		];
		$rest = $onlyCommonRest;

		if (!isset($option['MESSAGE_ONLY_COMMON_FIELDS']) || $option['MESSAGE_ONLY_COMMON_FIELDS'] === false)
		{
			$rest = array_merge($onlyCommonRest, [
				'unread' => $this->isUnread(),
				'viewed' => $this->isViewed(),
			]);
		}

		return $rest;
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

	public function autocompleteParams(Im\V2\Message\Send\SendingConfig $config): self
	{
		$this->getParams()->get(Params::LARGE_FONT)->setValue(Text::isOnlyEmoji($this->getMessage() ?? ''));
		$dateText = [];
		$dateTs = [];
		$urlIds = [];
		$isUrlOnly = false;
		if ($config->generateUrlPreview())
		{
			$results = Text::getDateConverterParams($this->getMessage() ?? '');
			foreach ($results as $result)
			{
				$dateText[] = $result->getText();
				$dateTs[] = $result->getDate()->getTimestamp();
			}

			$url = UrlItem::getByMessage($this);
			if (isset($url))
			{
				if ($url->getId() !== null)
				{
					$urlIds[] = $url->getId();
				}
				$this->setUrl($url);
				$isUrlOnly = $this->isUrlOnly($url);
			}
		}
		if ($config->keepConnectorSilence())
		{
			$this->getParams()->get(Params::STYLE_CLASS)->setValue('bx-messenger-content-item-system');
			if ($this->chat instanceof Im\V2\Chat\OpenLineChat)
			{
				$this->getParams()->get(Params::COMPONENT_ID)->setValue('HiddenMessage');
			}
		}
		$this->getParams()->get(Params::DATE_TEXT)->setValue($dateText);
		$this->getParams()->get(Params::DATE_TS)->setValue($dateTs);
		$this->getParams()->get(Params::URL_ID)->setValue($urlIds);
		$this->getParams()->get(Params::URL_ONLY)->setValue($isUrlOnly);

		return $this;
	}

	public function getCopilotData(): ?array
	{
		$chat = $this->getChat();
		$roleManager = new \Bitrix\Im\V2\Integration\AI\RoleManager();

		if (
			!$this->getParams()->isSet(Params::COPILOT_ROLE)
			&& !$chat instanceof Im\V2\Chat\CopilotChat
		)
		{
			return null;
		}

		$roles = [];
		$messageRole = $this->getParams()->get(Params::COPILOT_ROLE)->getValue() ?? $this->getDefaultCopilotRole();
		$roles[] = $messageRole;
		$chatRoleInfo = null;

		if ($chat instanceof Im\V2\Chat\CopilotChat)
		{
			$chatRole = $roleManager->getMainRole($this->getChatId());
			$roles[] = $chatRole;
			$chatRoleInfo = [['dialogId' => $this->getChat()->getDialogId(), 'role' => $chatRole]];
		}

		return [
			'chats' => $chatRoleInfo,
			'messages' => $messageRole ? [['id' => $this->getId(), 'role' => $messageRole]] : null,
			'roles' => $roleManager->getRoles($roles, $this->getAuthorId()),
		];
	}

	protected function getDefaultCopilotRole(): ?string
	{
		if (\Bitrix\Main\Loader::includeModule('imbot')
			&& $this->getAuthorId() === \Bitrix\Imbot\Bot\CopilotChatBot::getBotId()
		)
		{
			return \Bitrix\Im\V2\Integration\AI\RoleManager::getDefaultRoleCode();
		}

		return null;
	}

	private function isUrlOnly(?UrlItem $url): bool
	{
		if ($url === null)
		{
			return false;
		}

		if (!$url->isStaticUrl())
		{
			return false;
		}

		$messageWithoutUrl = str_replace($url->getUrl(), '', $this->getMessage() ?? '');

		return trim($messageWithoutUrl) === '';
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

	public function deleteSoft(): Result
	{
		$service = new Im\V2\Message\Delete\DeleteService($this);
		$service->setMode(Im\V2\Message\Delete\DeleteService::MODE_SOFT);
		return $service->delete();
	}

	public function deleteHard(): Result
	{
		$service = new Im\V2\Message\Delete\DeleteService($this);
		$service->setMode(Im\V2\Message\Delete\DeleteService::MODE_HARD);
		return $service->delete();
	}

	public function deleteComplete(): Result
	{
		$service = new Im\V2\Message\Delete\DeleteService($this);
		$service->setMode(Im\V2\Message\Delete\DeleteService::MODE_COMPLETE);
		return $service->delete();
	}

	public function filterByDate(DateTime $date): FilterResult
	{
		$result = new FilterResult();

		if ($this->getDateCreate()?->getTimestamp() > $date->getTimestamp())
		{
			return $result->setResult($this);
		}

		return $result->setResult(null)->setFiltered(true);
	}

	public function getRelatedChatId(): ?int
	{
		return $this->getChatId();
	}

	public function filterMessageText(): void
	{
		if (!$this->isSystem && $this->getMessage() !== null)
		{
			$this->setMessage(Text::filterUserBbCodes($this->getMessage(), $this->getContext()->getUserId()));
		}
	}
}
