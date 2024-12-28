<?php

namespace Bitrix\Im\V2\Message\Send;

use Bitrix\Main\Localization\Loc;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;

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

	public function sendMentions(Message $message): void
	{
		$chat = $this->getChat($message);

		if (
			!$chat->allowMention()
			|| !$chat->getChatId()
			|| !$message->getMessage()
			|| !$message->getAuthorId()
			|| $message->isSystem()
		)
		{
			return;
		}

		$userName = $message->getAuthor()?->getName();
		if (!$userName)
		{
			return;
		}

		$userGender = $message->getAuthor()?->getGender() === 'F' ? 'F' : 'M';
		$chatTitle = mb_substr(\Bitrix\Im\Text::decodeEmoji($chat->getTitle()), 0, 32);

		foreach ($message->getUserIdsToSendMentions() as $userId)
		{
			if ($message->getAuthorId() == $userId)
			{
				continue;
			}

			$arMessageFields = array(
				"TO_USER_ID" => $userId,
				"FROM_USER_ID" => $message->getAuthorId(),
				"NOTIFY_TYPE" => \IM_NOTIFY_FROM,
				"NOTIFY_MODULE" => "im",
				"NOTIFY_EVENT" => "mention",
				"NOTIFY_TAG" => 'IM|MENTION|'.$chat->getChatId(),
				"NOTIFY_SUB_TAG" => 'IM_MESS_'.$chat->getChatId().'_'.$userId,
				"NOTIFY_MESSAGE" => $this->prepareNotifyMessage($chatTitle, $message, $userGender),
				"NOTIFY_MESSAGE_OUT" => $this->prepareNotifyMail($chatTitle, $userGender),
			);
			\CIMNotify::Add($arMessageFields);//todo: Replace with new sending functional

			if ($this->isPullEnable() && $this->needToSendPull())
			{
				\Bitrix\Pull\Push::add($userId, $this->preparePushForMentionInChat($message));
			}
		}
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
		$chatTitle = htmlspecialcharsbx(\Bitrix\Im\Text::decodeEmoji($chat->getTitle() ?? ''));

		$result = [];
		$result['push'] = [];

		$result['module_id'] = 'im';
		$result['push']['params'] = [
			'TAG' => 'IM_CHAT_' . $chat->getId(),
			'CHAT_TYPE' => $chat->getType(),
			'CATEGORY' => 'ANSWER',
			'URL' => SITE_DIR . 'mobile/ajax.php?mobile_action=im_answer',
			'PARAMS' => [
				'RECIPIENT_ID' => 'chat' . $chat->getId()
			]
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
		$chatTitle = mb_substr(\Bitrix\Im\Text::decodeEmoji($chat->getTitle() ?? ''), 0, 32);
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
		$pushMessage = preg_replace_callback("/\[USER=([0-9]{1,})\]\[\/USER\]/i", ['\Bitrix\Im\Text', 'modifyShortUserTag'], $pushMessage);
		$pushMessage = preg_replace("/\[USER=([0-9]+)( REPLACE)?](.+?)\[\/USER]/i", "$3", $pushMessage);
		$pushMessage = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $pushMessage);
		$pushMessage = preg_replace_callback("/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/i", ['\Bitrix\Im\Text', 'modifySendPut'], $pushMessage);
		$pushMessage = preg_replace_callback("/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/i", ['\Bitrix\Im\Text', 'modifySendPut'], $pushMessage);
		$pushMessage = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $pushMessage);
		$pushMessage = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", "$2", $pushMessage);
		$pushMessage = preg_replace_callback("/\[ICON\=([^\]]*)\]/i", ['\Bitrix\Im\Text', 'modifyIcon'], $pushMessage);
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
}
