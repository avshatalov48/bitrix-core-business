<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Text;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Entity\User\NullUser;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Im\V2\Common\ContextCustomer;

class PushFormat
{
	use ContextCustomer;

	protected Message $message;
	private const MAX_CHAT_MESSAGE_TIME = 600;
	private const DND_USER_STATUS = 'dnd';

	public function __construct(Message $message)
	{
		$this->message = $message;
	}

	public function format(): array
	{
		$message = $this->message;
		$chat = $this->message->getChat();
		$chatLegacyFormat = $chat->toPullFormat();
		$users = $this->getUsers();

		return [
			'chatId' => $chat->getChatId(),
			'dateLastActivity' => $message->getDateCreate(), // todo test it
			'dialogId' => $chat instanceof PrivateChat ? 0 : $chat->getDialogId(),
			'chat' => $chat instanceof PrivateChat ? [] : [$chat->getId() => $chatLegacyFormat],
			'copilot' => $message->getCopilotData(),
			'lines' => $chat instanceof Chat\OpenLineChat ? $chat->getLineData() : null,
			'multidialog' => $chat->getMultidialogData(),
			'userInChat' => $this->getUserInChat(),
			'userBlockChat' => [$chat->getId() => $chatLegacyFormat['mute_list'] ?? []],
			'users' => $users ?: null,
			'message' => [
				'id' => $message->getMessageId(),
				'templateId' => $message->getUuid(),
				'templateFileId' => $message->getFileUuid(),
				'prevId' => $message->getPrevId(),
				'chatId' => $chat->getChatId(),
				'senderId' => $message->getAuthorId(),
				'recipientId' => $chat->getDialogId(),
				'system' => ($message->isSystem() ? 'Y': 'N'),
				'date' => DateTime::createFromTimestamp(time()), // DATE_CREATE
				'text' => Text::parse($message->getMessage()),
				'textLegacy' => Text::parseLegacyFormat($message->getMessage()),
				'params' => $message->getEnrichedParams()->toPullFormat(),
				'counter' => 0,
				'isImportant' => $message->isImportant(),
				'importantFor' => $message->getImportantFor(),
				'additionalEntities' => $this->getAdditionalEntities(),
				'forward' => $message->getForwardInfo(),
			],
			'counterType' => $chat->getCounterType()->value,
			'files' => $message->getFiles()->toRestFormat(['IDS_AS_KEY' => true]),
			'notify' => $chat instanceof Chat\CommentChat ? false : true,
		];
	}

	protected function getUserInChat(): array
	{
		if ($this->message->getChat() instanceof PrivateChat)
		{
			return [];
		}

		$userIds = $this->message->getChat()->getRelations()->getUserIds();

		return [$this->message->getChatId() => array_values($userIds)];
	}

	protected function getUsers(): array
	{
		$pushParams = $this->message->getPushParams();
		$extraParamContext = $pushParams['CONTEXT'] ?? null;

		if ($extraParamContext === Chat::ENTITY_TYPE_LIVECHAT && Loader::includeModule('imopenlines'))
		{
			return $this->getUserForLiveChat();
		}

		if ($this->message->getChat() instanceof PrivateChat)
		{
			$toUser = $this->message->getChat()->getCompanion($this->message->getAuthorId());

			return $this->getUsersLegacyFormat([$this->message->getAuthor(), $toUser]);
		}

		return $this->getUsersLegacyFormat([$this->message->getAuthor()]);
	}

	protected function getUserForLiveChat(): array
	{
		[$lineId, $userId] = explode('|', $this->message->getChat()->getEntityId() ?? '');
		$userCode = 'livechat|' . $lineId . '|' . $this->message->getChatId() . '|' . $userId;
		$operatorInfo = \Bitrix\ImOpenLines\Connector::getOperatorInfo(
			$this->message->getPushParams()['LINE_ID'],
			$this->message->getAuthor()?->getId() ?? 0,
			$userCode
		);

		return [$this->message->getAuthor()?->getId() => $operatorInfo];
	}

	/**
	 * @param User[] $users
	 * @return array
	 */
	protected function getUsersLegacyFormat(array $users): array
	{
		$result = [];
		foreach ($users as $user)
		{
			if (!$user)
			{
				continue;
			}

			$userLegacy = $this->getUserLegacyFormat($user);

			if ($userLegacy)
			{
				$result[$user?->getId()] = $userLegacy;
			}
		}

		return $result;
	}

	protected function getUserLegacyFormat(User $user): ?array
	{
		if ($user instanceof NullUser)
		{
			return null;
		}

		$converter = new Converter(Converter::KEYS | Converter::TO_LOWER | Converter::RECURSIVE);
		$result = $user->getArray();
		$result = $converter->process($result);
		$services = $user->getServices();
		$result['avatar_id'] = $user->getAvatarId();
		$result['phone_device'] = $user->getPhoneDevice();
		$result['tz_offset'] = (int)$user->getTzOffset();
		$result['services'] = empty($services) ? null : $services;
		$result['profile'] = \CIMContactList::GetUserPath($user->getId());

		return $result;
	}

	protected function getAdditionalEntities(): array
	{
		$message = $this->message;
		$additionalEntitiesAdapter = new \Bitrix\Im\V2\Rest\RestAdapter();
		$additionalPopupData = new \Bitrix\Im\V2\Rest\PopupData([]);

		if ($message->getParams()->isSet(Message\Params::FORWARD_CONTEXT_ID))
		{
			$additionalUserId = (int)$message->getParams()->get(Message\Params::FORWARD_USER_ID)->getValue();
			$additionalPopupData->add(new \Bitrix\Im\V2\Entity\User\UserPopupItem([$additionalUserId]));
		}

		$replyIds = [];
		if ($message->getParams()->isSet(Message\Params::REPLY_ID))
		{
			$replyIds[] = (int)$message->getParams()->get(Message\Params::REPLY_ID)->getValue();
		}
		$messages = new MessageCollection($replyIds);
		$messages->fillAllForRest();
		$additionalEntitiesAdapter->addEntities($messages);
		$additionalEntitiesAdapter->setAdditionalPopupData($additionalPopupData);
		return $additionalEntitiesAdapter->toRestFormat([
			'WITHOUT_OWN_REACTIONS' => true,
			'MESSAGE_ONLY_COMMON_FIELDS' => true,
		]);
	}

	public static function formatStartRecordVoice(Chat $chat): array
	{
		$userId = $chat->getContext()->getUserId();
		return [
			'module_id' => 'im',
			'command' => 'startRecordVoice',
			'expiry' => 60,
			'params' => [
				'dialogId' => $chat instanceof PrivateChat ? (string)$userId : $chat->getDialogId(),
				'userId' => $userId,
				'userName' => $chat->getContext()->getUser()->getName()
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];
	}

	public function formatMessageUpdate(): array
	{
		$message = $this->message;

		return [
			'module_id' => 'im',
			'command' => 'messageUpdate',
			'params' => [
				'id' => $message->getId(),
				'type' => $message->getChat()->getType() === Chat::IM_TYPE_PRIVATE ? 'private' : 'chat',
				'text' => $message->getParsedMessage(),
				'textLegacy' => Text::parseLegacyFormat($message->getMessage()),
				'chatId' => $message->getChatId(),
				'senderId' => $message->getAuthorId(),
				'params' => $message->getEnrichedParams()->toPullFormat(['IS_EDITED', 'URL_ID', 'ATTACH', 'DATE_TEXT', 'DATE_TS']),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];
	}

	public function validateDataForInform(): Result
	{
		$result = new Result();

		$message = $this->message;
		$toUser = $message->getChat()->getCompanion();
		$toUserStatus = $toUser->getStatus(true);

		if (!($message->getAuthorId() === $this->getContext()->getUserId()))
		{
			$result->addError(new Message\MessageError(Message\MessageError::INFORM_USER_CONTEXT_ERROR));
		}

		if ($message->isViewedByOthers())
		{
			$result->addError(new Message\MessageError(Message\MessageError::INFORM_VIEWED_ERROR));
		}

		$timestampTimeNow = DateTime::createFromTimestamp(time())->getTimestamp();
		if (!($timestampTimeNow - $message->getDateCreate()->getTimestamp() <= self::MAX_CHAT_MESSAGE_TIME))
		{
			$result->addError(new Message\MessageError(Message\MessageError::INFORM_TIMEOUT_ERROR));
		}

		if (!($toUserStatus === self::DND_USER_STATUS))
		{
			$result->addError(new Message\MessageError(Message\MessageError::INFORM_USER_STATUS_ERROR));
		}

		return $result;
	}
}