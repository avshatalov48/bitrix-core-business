<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Chat\Ai;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Chat\Add\AddResult;

class AiAssistantPrivateChat extends PrivateChat
{
	public function add(array $params, ?Context $context = null): AddResult
	{
		$params['ENTITY_TYPE'] = Chat::ENTITY_TYPE_PRIVATE_AI_ASSISTANT;

		return parent::add($params, $context);
	}
}
