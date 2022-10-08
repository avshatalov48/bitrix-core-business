<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\ImConnector\Input;
use Bitrix\ImConnector\Library;
use Bitrix\Main\Text\Emoji;
use Bitrix\MessageService\IncomingMessage;
use Bitrix\Messageservice\Internal\Entity\IncomingMessageTable;

class EdnaRuIncomingMessage extends IncomingMessage
{

	public static function sendMessageToChat(array $message): void
	{
		$message = self::prepareMessageFields($message);

		$portal = new Input($message);
		$portal->reception();

		self::confirmSendingMessage($message['internalId']);
	}

	public static function prepareMessageFields(array $messageFields): array
	{
		$messageFields['CONNECTOR'] = Library::ID_EDNA_WHATSAPP_CONNECTOR;

		$messageFields['imSubject'] = $messageFields['subjectId'];
		$messageFields['address'] = $messageFields['subscriber']['identifier'];
		$messageFields['userName'] = $messageFields['userInfo']['userName'];
		$messageFields['firstName'] = $messageFields['userInfo']['firstName'];
		$messageFields['lastName'] = $messageFields['userInfo']['lastName'];
		$messageFields['avatarUrl'] = $messageFields['userInfo']['avatarUrl'];
		$messageFields['imType'] = 'whatsapp';

		$messageFields['contentType'] = mb_strtolower($messageFields['messageContent']['type']);
		if (isset($messageFields['messageContent']['text']))
		{
			$messageFields['text'] = Emoji::decode($messageFields['messageContent']['text']);
		}

		if (isset($messageFields['messageContent']['attachment']) && is_array($messageFields['messageContent']['attachment']))
		{
			$messageFields['attachmentUrl'] = $messageFields['messageContent']['attachment']['url'];
			$messageFields['attachmentName'] = $messageFields['messageContent']['attachment']['name'];
		}

		if (isset($messageFields['messageContent']['caption']) && !is_null($messageFields['messageContent']['caption']))
		{
			$messageFields['caption'] = Emoji::decode($messageFields['messageContent']['caption']);
		}
		
		return $messageFields;
	}

	protected static function prepareBodyForSave(array $body): array
	{
		$body['messageContent']['text'] = Emoji::encode($body['messageContent']['text']);
		$body['messageContent']['caption'] = Emoji::encode($body['messageContent']['caption']);

		return $body;
	}
}