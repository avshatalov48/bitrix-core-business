<?php

namespace Bitrix\Im\V2\Message\Send;

use Bitrix\Im\Text;
use Bitrix\Im\V2\Anchor\AnchorFeature;
use Bitrix\Im\V2\Anchor\DI\AnchorContainer;
use Bitrix\Main\Localization\Loc;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Pull\Push;
use CIMNotify;

class MentionService
{
	use ContextCustomer;

	private SendingConfig $sendingConfig;

	/**
	 * @param SendingConfig|null $sendingConfig
	 */
	public function __construct(?SendingConfig $sendingConfig = null)
	{
		if ($sendingConfig === null)
		{
			$sendingConfig = new SendingConfig();
		}
		$this->sendingConfig = $sendingConfig;
	}

	public function isPullEnable(): bool
	{
		static $enable;
		if ($enable === null)
		{
			$enable = \Bitrix\Main\Loader::includeModule('pull');
		}
		return $enable;
	}

	public function processMentions(Message $message): void
	{
		$userName = $message->getAuthor()?->getName();
		if (!$userName)
		{
			return;
		}

		$this->processMessageAnchors($message);

		$chat = $this->getChat($message);
		if ($this->canMention($message, $chat))
		{
			$this->sendMentions($message);
		}
	}

	public function updateMentions(Message $previousMessage, Message $newMessage): void
	{
		if ($previousMessage->getMessage() === $newMessage->getMessage())
		{
			return;
		}

		$this->updateMessageAnchors($previousMessage, $newMessage);

		$chat = $this->getChat($newMessage);
		if ($this->canMention($newMessage, $chat))
		{
			$this->updateUsersMentions($previousMessage, $newMessage);
		}
	}

	public function sendMentions(Message $message): void
	{
		$chat = $this->getChat($message);

		$mentionedUserIds = $message->getUserIdsToSendMentions();
		unset($mentionedUserIds[$message->getAuthorId()]);

		$this->sendMentionsToUsers($message, $chat, $mentionedUserIds);
	}

	protected function needToSendPull(): bool
	{
		return true;
	}

	protected function getChat(Message $message): Chat
	{
		return $message->getChat();
	}

	private function preparePushForMentionInChat(Message $message): array
	{
		$chat = $this->getChat($message);
		$avatarUser = $message->getAuthor()?->getAvatar();
		$avatarChat = $chat->getAvatar(false, true);
		$pushText = $this->preparePushMessage($message);
		$chatTitle = htmlspecialcharsbx(Text::decodeEmoji($chat->getTitle() ?? ''));

		$result = [];
		$result['push'] = [];

		$result['module_id'] = 'im';
		$result['push']['params'] = [
			'TAG' => 'IM_CHAT_' . $chat->getId(),
			'CHAT_TYPE' => $chat->getType(),
			'CATEGORY' => 'ANSWER',
			'URL' => SITE_DIR . 'mobile/ajax.php?mobile_action=im_answer',
			'PARAMS' => [
				'RECIPIENT_ID' => 'chat' . $chat->getId(),
			],
		];
		$result['push']['type'] = ($chat->getType() === Chat::IM_TYPE_OPEN ? 'openChat' : 'chat');
		$result['push']['tag'] = 'IM_CHAT_' . $chat->getId();
		$result['push']['sub_tag'] = 'IM_MESS';
		$result['push']['app_id'] = 'Bitrix24';
		$result['push']['message'] = $pushText;
		$result['push']['advanced_params'] = [
			'group' => ($chat->getEntityType() === Chat::ENTITY_TYPE_LINE ? 'im_lines_message' : 'im_message'),
			'avatarUrl' => $avatarChat ?: $avatarUser,
			'senderName' => $chatTitle,
			'senderMessage' => $pushText,
		];

		return $result;
	}

	private function preparePushMessage(Message $message): string
	{
		Message::loadPhrases();
		\CIMMessenger::loadLoc();
		$chat = $this->getChat($message);
		$chatTitle = mb_substr(Text::decodeEmoji($chat->getTitle() ?? ''), 0, 32);
		$author = $message->getAuthor();
		$userName = $author?->getName() ?? '';
		$userGender = $author?->getGender() ?? 'M';

		$pushMessage = $message->getMessage();

		$pushFiles = '';
		if ($message->hasFiles())
		{
			foreach ($message->getFiles() as $file)
			{
				$pushFiles .= " [".Loc::getMessage('IM_MESSAGE_FILE').": ".$file->getDiskFile()->getName()."]";
			}
			$pushMessage .= $pushFiles;
		}

		$hasAttach = mb_strpos($pushMessage, '[ATTACH=') !== false;

		$pushMessage = preg_replace("/\[CODE\](.*?)\[\/CODE\]/si", " [".Loc::getMessage('IM_MESSAGE_CODE')."] ", $pushMessage);
		$pushMessage = preg_replace("/\[s\].*?\[\/s\]/i", "-", $pushMessage);
		$pushMessage = preg_replace("/\[[bui]\](.*?)\[\/[bui]\]/i", "$1", $pushMessage);
		$pushMessage = preg_replace("/\\[url\\](.*?)\\[\\/url\\]/iu", "$1", $pushMessage);
		$pushMessage = preg_replace("/\\[url\\s*=\\s*((?:[^\\[\\]]++|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\])+)\\s*\\](.*?)\\[\\/url\\]/ixsu", "$2", $pushMessage);
		$pushMessage = preg_replace_callback("/\[USER=([0-9]+|all)\]\[\/USER\]/i", ['\Bitrix\Im\Text', 'modifyShortUserTag'], $pushMessage);
		$pushMessage = preg_replace("/\[USER=([0-9]+|all)( REPLACE)?](.+?)\[\/USER]/i", "$3", $pushMessage);
		$pushMessage = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $pushMessage);
		$pushMessage = preg_replace_callback("/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/i", ['\Bitrix\Im\Text', 'modifySendPut'], $pushMessage);
		$pushMessage = preg_replace_callback("/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/i", ['\Bitrix\Im\Text', 'modifySendPut'], $pushMessage);
		$pushMessage = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $pushMessage);
		$pushMessage = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", "$2", $pushMessage);
		$pushMessage = preg_replace_callback("/\[ICON\=([^\]]*)\]/i", ['\Bitrix\Im\Text', 'modifyIcon'], $pushMessage);
		$pushMessage = preg_replace_callback('/\[TIMESTAMP=(\d+) FORMAT=([^\]]*)\]/i', [Text::class, 'modifyTimestampCode'], $pushMessage);
		$pushMessage = preg_replace_callback('/\[IMG SIZE=([a-z]+)\](.*?)\[\/IMG\]/i', [Text::class, 'modifyImageCode'], $pushMessage);
		$pushMessage = preg_replace('#\-{54}.+?\-{54}#s', " [".Loc::getMessage('IM_MESSAGE_QUOTE')."] ", str_replace('#BR#', ' ', $pushMessage));
		$pushMessage = preg_replace('/^(>>(.*)(\n)?)/mi', " [".Loc::getMessage('IM_MESSAGE_QUOTE')."] ", str_replace('#BR#', ' ', $pushMessage));

		if (!$pushFiles && !$hasAttach && $message->getParams()->isSet('ATTACH'))
		{
			$pushMessage .= " [".Loc::getMessage('IM_MESSAGE_ATTACH')."]";
		}

		return
			Loc::getMessage('IM_MESSAGE_MENTION_PUSH_2_'.$userGender, ['#USER#' => $userName, '#TITLE#' => $chatTitle])
			. ': '
			. $pushMessage;
	}

	private function prepareNotifyMail(string $chatTitle, string $userGender): callable
	{
		return fn (?string $languageId = null) => Loc::getMessage(
			$this->getNotifyTextCode($userGender),
			['#TITLE#' => $chatTitle],
			$languageId
		);
	}

	private function prepareNotifyMessage(string $chatTitle, Message $message, string $userGender): callable
	{
		return fn (?string $languageId = null) => Loc::getMessage(
			$this->getNotifyTextCode($userGender),
			['#TITLE#' => $this->getTitleWithContext($chatTitle, $message)],
			$languageId
		);
	}

	protected function getTitleWithContext(string $title, Message $message): string
	{
		$chat = $this->getChat($message);

		return "[CHAT={$chat->getId()}]{$title}[/CHAT]";
	}

	protected function getNotifyTextCode(string $userGender): string
	{
		return "IM_MESSAGE_MENTION_{$userGender}";
	}

	private function canMention(Message $message, Chat $chat): bool
	{
		if (
			!$chat->allowMention()
			|| !$chat->getChatId()
			|| !$message->getMessage()
			|| !$message->getAuthorId()
			|| $message->isSystem()
		)
		{
			return false;
		}

		return true;
	}

	private function sendMentionsToUsers(Message $message, Chat $chat, array $mentionedUserIds): void
	{
		$userGender = $message->getAuthor()?->getGender() === 'F' ? 'F' : 'M';
		$chatTitle = mb_substr(Text::decodeEmoji($chat->getTitle()), 0, 32);

		foreach ($mentionedUserIds as $userId)
		{
			if($this->shouldSendMentionNotification($chat, $userId))
			{
				$messageFields = [
					"TO_USER_ID" => $userId,
					"FROM_USER_ID" => $message->getAuthorId(),
					"NOTIFY_TYPE" => \IM_NOTIFY_FROM,
					"NOTIFY_MODULE" => "im",
					"NOTIFY_EVENT" => "mention",
					"NOTIFY_TAG" => 'IM|MENTION|' . $chat->getChatId(),
					"NOTIFY_SUB_TAG" => 'IM_MESS_' . $chat->getChatId() . '_' . $userId,
					"NOTIFY_MESSAGE" => $this->prepareNotifyMessage($chatTitle, $message, $userGender),
					"NOTIFY_MESSAGE_OUT" => $this->prepareNotifyMail($chatTitle, $userGender),
				];
				CIMNotify::Add($messageFields);
			}

			if ($this->isPullEnable() && $this->needToSendPull())
			{
				Push::add($userId, $this->preparePushForMentionInChat($message));
			}
		}
	}

	protected function shouldSendMentionNotification(Chat $chat, int $userId): bool
	{
		$relation = $chat->getRelationByUserId($userId);

		return ($chat instanceof Chat\OpenChannelChat || $chat instanceof Chat\OpenChat) && !$relation;
	}

	private function deleteMentionNotifications(Message $message, array $unmentionedUserIds): void
	{
		foreach ($unmentionedUserIds as $userId)
		{
			CIMNotify::DeleteBySubTag('IM_MESS_' . $message->getChatId() . '_' . $userId);
		}
	}

	private function processMessageAnchors(Message $message): void
	{
		$anchorMentionedUserIds = $message->getUserIdsToSendMentionAnchors();
		unset($anchorMentionedUserIds[$message->getAuthorId()]);

		$this->addAnchors($message, $anchorMentionedUserIds);
	}

	private function updateMessageAnchors(Message $previousMessage, Message $newMessage): void
	{
		$previousMentionedUserIds = $previousMessage->getUserIdsToSendMentionAnchors();
		$newMentionedUserIds = $newMessage->getUserIdsToSendMentionAnchors();

		unset(
			$previousMentionedUserIds[$previousMessage->getAuthorId()],
			$newMentionedUserIds[$newMessage->getAuthorId()]
		);

		$toDelete = array_diff_key($previousMentionedUserIds, $newMentionedUserIds);
		$this->deleteAnchors($newMessage, $toDelete);

		$toAdd = array_diff_key($newMentionedUserIds, $previousMentionedUserIds);
		$this->addAnchors($newMessage, $toAdd);
	}

	private function updateUsersMentions(Message $previousMessage, Message $newMessage): void
	{
		$chat = $this->getChat($newMessage);

		$previousMentionedUserIds = $previousMessage->getUserIdsToSendMentions();
		$newMentionedUserIds =  $newMessage->getUserIdsToSendMentions();

		unset(
			$previousMentionedUserIds[$previousMessage->getAuthorId()],
			$newMentionedUserIds[$newMessage->getAuthorId()]
		);

		$toDelete = array_diff_key($previousMentionedUserIds, $newMentionedUserIds);
		$this->deleteMentionNotifications($newMessage, $toDelete);

		$toAdd = array_diff_key($newMentionedUserIds, $previousMentionedUserIds);
		$this->sendMentionsToUsers($newMessage, $chat, $toAdd);
	}

	private function addAnchors(Message $message, array $mentionedUserIds): void
	{
		if (!AnchorFeature::isOn())
		{
			return;
		}

		$anchorService = AnchorContainer::getInstance()
			->getAnchorService($message)
			->setContext($this->getContext());

		$anchorService->addMentionAnchor($mentionedUserIds);
	}

	private function deleteAnchors(Message $message, array $unmentionedUserIds): void
	{
		$anchorService = AnchorContainer::getInstance()
			->getAnchorService($message)
			->setContext($this->getContext());

		$anchorService->deleteUsersMentionAnchors($unmentionedUserIds);
	}
}
