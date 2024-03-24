<?php

namespace Bitrix\Im\V2\Controller\Call;

use Bitrix\Im\V2\Call\CallError;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Main\Engine\CurrentUser;
use CIMChat;

class Zoom extends BaseController
{
	/**
	 * @restMethod im.v2.Call.Zoom.create
	 */
	public function createAction(Chat $chat, CurrentUser $user): ?array
	{
		if (!\Bitrix\Im\Call\Integration\Zoom::isActive())
		{
			$this->addError(new CallError(CallError::ZOOM_ACTIVE_ERROR));

			return null;
		}

		if (!\Bitrix\Im\Call\Integration\Zoom::isAvailable())
		{
			$this->addError(new CallError(CallError::ZOOM_AVAILABLE_ERROR));

			return null;
		}

		if (!\Bitrix\Im\Call\Integration\Zoom::isConnected($user->getId()))
		{
			$this->addError(new CallError(CallError::ZOOM_CONNECTED_ERROR));

			return null;
		}

		if (!$chat->canDo(Chat\Permission::ACTION_SEND))
		{
			$this->addError(new Chat\ChatError(Chat\ChatError::ACCESS_DENIED));

			return null;
		}

		$zoom = new \Bitrix\Im\Call\Integration\Zoom($user->getId(), $chat->getDialogId());
		$link = $zoom->getImChatConferenceUrl();

		if (empty($link))
		{
			$this->addError(new CallError(CallError::ZOOM_CREATE_ERROR));

			return null;
		}

		$messageFields = $zoom->getRichMessageFields($chat->getDialogId(), $link, $user->getId());
		$messageFields['PARAMS']['COMPONENT_ID'] = 'ZoomInviteMessage';
		$messageFields['PARAMS']['COMPONENT_PARAMS'] = ['LINK' => $link];

		$messageId = \CIMMessenger::Add($messageFields);

		if (!$messageId)
		{
			$this->addError(new MessageError(MessageError::SENDING_FAILED));

			return null;
		}

		return [
			'link' => $link,
			'messageId' => $messageId,
		];
	}
}