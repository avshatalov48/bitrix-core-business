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

	public function sendMentions(Chat $chat, Message $message): void
	{
		if (
			!$chat->allowMention()
			|| !$chat->getChatId()
			|| !$message->getMessage()
			|| !$message->getAuthorId()
		)
		{
			return;
		}

		$userName = $message->getAuthor()->getFullName(false);
		if (!$userName)
		{
			return;
		}

		$userGender = $message->getAuthor()->getGender() == 'F' ? 'F' : 'M';
		$chatTitle = mb_substr(htmlspecialcharsback($chat->getTitle()), 0, 32);


		$relations = [];
		foreach ($chat->getRelations() as $relation)
		{
			$relations[$relation->getUserId()] = $relation->getNotifyBlock();
		}

		$forUsers = [];
		if (preg_match_all("/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/i", $message->getMessage(), $matches))
		{
			if ($chat->getType() == Chat::IM_TYPE_OPEN)
			{
				foreach ($matches[1] as $userId)
				{
					if (!\CIMSettings::GetNotifyAccess($userId, 'im', 'mention', \CIMSettings::CLIENT_SITE))
					{
						continue;
					}

					if (
						!isset($relations[$userId])
						|| $relations[$userId] === true
					)
					{
						$forUsers[$userId] = $userId;
					}
				}
			}
			else
			{
				foreach ($matches[1] as $userId)
				{
					if (!\CIMSettings::GetNotifyAccess($userId, 'im', 'mention', \CIMSettings::CLIENT_SITE))
					{
						continue;
					}

					if (
						isset($relations[$userId])
						&& $relations[$userId] === true
					)
					{
						$forUsers[$userId] = $userId;
					}
				}
			}
		}

		foreach ($forUsers as $userId)
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
				"NOTIFY_MESSAGE" => $this->prepareNotifyMessage($chatTitle, $chat->getChatId(), $userGender),
				"NOTIFY_MESSAGE_OUT" => $this->prepareNotifyMail($chatTitle, $userGender),
			);
			\CIMNotify::Add($arMessageFields);//todo: Replace with new sending functional

			if ($this->isPullEnable())
			{
				\Bitrix\Pull\Push::add(
					$userId,
					$this->preparePushForMentionInChat(
						$this->preparePushMessage($message, $chatTitle, $userName, $userGender),
						$message,
						$chat,
						$chatTitle
					)
				);
			}
		}
	}


	private function preparePushForMentionInChat(string $pushText, Message $message, Chat $chat, string $chatTitle): array
	{
		$avatarUser = $message->getAuthor()->getAvatar();
		if ($avatarUser && mb_strpos($avatarUser, 'http') !== 0)
		{
			$avatarUser = \Bitrix\Im\Common::getPublicDomain(). $avatarUser;
		}

		$avatarChat = \CIMChat::GetAvatarImage($chat->getAvatarId(), 200, false);
		if ($avatarChat && mb_strpos($avatarChat, 'http') !== 0)
		{
			$avatarChat = \Bitrix\Im\Common::getPublicDomain(). $avatarChat;
		}

		$result = [];
		$result['push'] = [];

		$result['module_id'] = 'im';
		$result['push']['params'] = [
			'TAG' => 'IM_CHAT_'.$chat->getChatId(),
			'CHAT_TYPE' => $chat->getType(),
			'CATEGORY' => 'ANSWER',
			'URL' => SITE_DIR.'mobile/ajax.php?mobile_action=im_answer',
			'PARAMS' => [
				'RECIPIENT_ID' => 'chat'.$chat->getChatId()
			]
		];
		$result['push']['type'] = ($chat->getType() == Chat::IM_TYPE_OPEN ? 'openChat' : 'chat');
		$result['push']['tag'] = 'IM_CHAT_'.$chat->getChatId();
		$result['push']['sub_tag'] = 'IM_MESS';
		$result['push']['app_id'] = 'Bitrix24';
		$result['push']['message'] = $pushText;
		$result['push']['advanced_params'] = [
			'group' => ($chat->getEntityType() == Chat::ENTITY_TYPE_LINE ? 'im_lines_message' : 'im_message'),
			'avatarUrl' => $avatarChat ?: $avatarUser,
			'senderName' => $chatTitle,
			'senderMessage' => $pushText,
		];

		return $result;
	}

	private function preparePushMessage(Message $message, string $chatTitle, string $userName, string $userGender): string
	{
		Message::loadPhrases();

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
			'IM_MESSAGE_MENTION_'.$userGender,
			['#TITLE#' => $chatTitle],
			$languageId
		);
	}

	private function prepareNotifyMessage(string $chatTitle, int $chatId, string $userGender): callable
	{
		return fn (?string $languageId = null) => Loc::getMessage(
			'IM_MESSAGE_MENTION_'.$userGender,
			['#TITLE#' => '[CHAT='.$chatId.']'.$chatTitle.'[/CHAT]'],
			$languageId
		);
	}
}